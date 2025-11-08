<?php

namespace App\Http\Controllers\Backend;

use App\Authorizable;
use App\Events\Backend\UserCreated;
use App\Events\Backend\UserUpdated;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\UserAccountCreated;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\Subscriptions\Models\Plan;
use Yajra\DataTables\DataTables;
use Modules\Subscriptions\Trait\SubscriptionTrait;
use Modules\Subscriptions\Models\SubscriptionTransactions;
use Modules\Subscriptions\Models\Subscription;
use Modules\Currency\Models\Currency;
use App\Models\Payment;
use Illuminate\Support\Facades\Notification;
use Modules\NotificationTemplate\Trait\NotificationTemplateTrait;

class UserController extends Controller
{

    public $module_title;
    public $module_name;
    public $module_path;
    public $module_icon;
    public $module_model;
    use NotificationTemplateTrait;
    use SubscriptionTrait;

    public function __construct()
    {
        // Page Title
        $this->module_title = __('messages.lbl_my_profile');

        // module name
        $this->module_name = 'users';

        // directory path of the module
        $this->module_path = 'users';

        // module icon
        $this->module_icon = 'fa-solid fa-users';

        // module model name, path
        $this->module_model = "App\Models\User";

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    public function index()
    {
        $module_action = __('messages.user');
        $module_title =  __('messages.vendors');
        return view('users.index', compact('module_action', 'module_title'));
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $query = User::query()->role('admin');
        $query = $query->orderBy('id', 'desc');
        if ($request->filter && $request->filter['column_status'] != '') {
            $query->where('status', $request->filter['column_status']);
        }
        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<div class="checkbox"><input type="checkbox" class="select-table-row "  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')"><label for="datatable-row-' . $row->id . '"></label></div>';
            })
            ->addColumn('name', function ($data) {
                return $data->getFullNameAttribute();
            })
            ->editColumn('status', function ($row) {
                $checked = '';
                if ($row->status) {
                    $checked = 'checked="checked"';
                }

                return '
                <div class="checkbox">
                    <input type="checkbox" data-url="' . route('backend.users.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="status-datatable-row-' . $row->id . '"  name="status" value="' . $row->id . '" ' . $checked . '>
                    <label for="status-datatable-row-' . $row->id . '"></label>
                </div>
               ';
            })
            ->editColumn('gender', function ($row) {
                return ucfirst($row->gender); // Capitalizing the first letter
            })
            ->editColumn('slug', function ($row) {
                return ucfirst($row->slug); // Capitalizing the first letter
            })
            ->editColumn('created_at', function ($data) {
                return formatDateOrTime($data->created_at, 'date');
            })
            ->rawColumns(['action', 'status', 'check', 'name'])
            ->orderColumns(['id'], '-:column $1')
            ->toJson();
    }

    public function create()
    {
        $module_action = __('messages.create_vendor');
        $module_title =  __('frontend.admin');

        return view('users.create', compact('module_action', 'module_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|min:2|max:50',
            'last_name' => 'required|string|min:2|max:50',
            'email' => [
                'required',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/',
                'unique:users,email,' . $request->id,
            ],
            'mobile' => 'required|unique:users,mobile,' . $request->id,
            'slug' => 'required|unique:users,slug,' . $request->id,
            // 'password' => $request->id ? 'nullable|min:6|confirmed' : 'required|min:6|confirmed',
            'gender' => 'required|in:male,female,other',
        ]);
        // Find existing user or create a new one
        $user = User::find($request->id) ?? new User;

        // Fill the user model with the request data
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->status = $request->status ? 1 : 0;
        $user->mobile = $request->mobile;
        $user->slug = $request->slug;
        $user->gender = $request->gender;
        $user->user_type = 'admin';

        // Only hash and update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Save the user to the database
        $user->save();

        $user->assignRole('admin');
        Artisan::call('cache:clear');

        if(!$request->id) {
            // Copy super admin settings to new user
            \App\Models\Setting::copySuperAdminSettingsToUser($user->id);

            sendNotificationOnUserRegistration($user);
        }

        $plan = Plan::where('identifier', 'free')->where('is_free_plan', 1)->whereNull('deleted_at')->first();
        if (!$plan) {
            Auth::login($user);
            return redirect()->route('pricing')->with('success', $request->id ? __('messages.vendor_updated_successfully') : __('messages.vendor_created_successfully'));
        }
        $subscription = Subscription::create([
            'plan_id' => $plan->id,
            'user_id' => $user->id,
            'start_date' => now(),
            'end_date' => $this->get_plan_expiration_date(now(), $plan->type, $plan->duration),
            'status' => 'active',
            'amount' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'plan_details' => json_encode($plan),
            'gateway_type' => null,
            'transaction_id' => null,
            'name' => $plan->name,
            'identifier' => $plan->identifier,
            'type' => $plan->type,
            'duration' => $plan->duration,
            'payment_id' => null,
            'max_appointment' => $plan->max_appointment,
            'max_branch' => $plan->max_branch,
            'max_service' => $plan->max_service,
            'max_staff' => $plan->max_staff,
            'max_customer' => $plan->max_customer,
            'is_active' => 1,
        ]);

        $plan->givePermissionToUser($user->id);

        $currency = strtolower(GetcurrentCurrency());

        Payment::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => 0,
            'currency' => $currency,
            'payment_method' => 2,
            'payment_date' => now(),
            'subscription_id' => $subscription->id,
            'status' => 2,
        ]);

        SubscriptionTransactions::create([
            'user_id' => $user->id,
            'amount' => 0,
            'payment_type' => null,
            'payment_status' => null,
            'tax_data' => null,
            'discount_data' => null,
            'transaction_id' => 0,
            'subscriptions_id' => $subscription->id,
        ]);

        $user->update(['is_subscribe' => 1]);


        $currencyData = [
            'currency_name' => 'Dollar',
            'currency_symbol' => '$',
            'currency_code' => 'USD',
            'currency_position' => 'left',
            'no_of_decimal' => 2,
            'thousand_separator' => ',',
            'decimal_separator' => '.',
            'is_primary' => 1,
            'created_by' => $user->id,
            'updated_by' => $user->id
        ];
        // Use currency_code and created_by to check for existing currency
        Currency::updateOrCreate(
            [
                'currency_code' => 'USD',
                'created_by' => $user->id
            ],
            $currencyData
        );



        return redirect()->route('backend.users.index')->with('success', $request->id ? __('messages.vendor_updated_successfully') : __('messages.vendor_created_successfully'));
    }

    public function edit($id)
    {
        $module_action = __('messages.edit_vendor');
        $module_title =  __('frontend.admin');

        $user = User::find($id);

        return view('users.create', compact('module_action', 'module_title', 'user'));
    }

    public function delete($id)
    {
        $user = User::find($id);
        $user->forceDelete(); // This will permanently delete the user


        $message = __('messages.delete_form', ['form' => __('messages.lbl_vendor')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $user = auth()->user();

        $user->status = 0;

        $user->save();

        event(new UserUpdated($$module_name_singular));

        return response()->json(['message' => __('messages.account_deactivated')]);
    }

    /**
     * Resend Email Confirmation Code to User.
     *
     * @param [type] $hashid [description]
     * @return [type] [description]
     */
    public function emailConfirmationResend($id)
    {

        $user = User::where('id', '=', $id)->first();

        if ($user) {
            if ($user->email_verified_at == null) {
                // Send Email To Registered User
                $user->sendEmailVerificationNotification();

                flash(__('messages.email_sent'))->success()->important();

                return redirect()->back();
            } else {
                flash(__('messages.email_already_verified', [
                    'name' => $user->name,
                    'date' => $user->email_verified_at->isoFormat('LL')
                ]))->success()->important();

                return redirect()->back();
            }
        }
    }

    public function user_list(Request $request)
    {
        $term = trim($request->q);

        $role = $request->role;

        $query_data = [];

        if ($role == 'employee') {
            $query_data = User::role(['manager', 'employee'])->with('media')->where(function ($q) {
                if (!empty($term)) {
                    $q->orWhere('first_name', 'LIKE', "%$term%")->$q->orWhere('last_name', 'LIKE', "%$term%");
                }
            })->where('is_show_calender', 1)->get();
        } elseif ($role == 'user') {
            $query_data = User::role(['user'])->whereNotNull('email_verified_at')->where(function ($q) use ($term) {
                if (!empty($term)) {
                    $q->orWhere('first_name', 'LIKE', "%{$term}%")
                    ->orWhere('last_name', 'LIKE', "%{$term}%");
                }
            })->active();

            if (auth()->user()->hasRole('admin')) {
                $query_data = $query_data->where(function ($q) {
                    $q->where(function ($subQ) {
                        $subQ->where('user_type', 'user')
                            ->whereHas('booking', function ($query) {
                                $query->whereHas('branch', function ($query) {
                                    $query->where('created_by', auth()->id());
                                });
                            });
                    })
                    ->orWhere(function ($subQ) {
                        $subQ->where('created_by', auth()->id());
                    });
                });
            }

            $query_data = $query_data->get();
        }

        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'full_name' => $row->first_name . ' ' . $row->last_name,
                'email' => $row->email,
                'mobile' => $row->mobile,
                'profile_image' => $row->profile_image,
                'created_at' => $row->created_at,
            ];
        }

        return response()->json($data);
    }

    public function create_customer(Request $request)
    {
        $request->validate([
            'first_name' => 'required|min:3|max:191',
            'last_name' => 'required|min:3|max:191',
            'email' => 'required|email|regex:/(.+)@(.+)\.(.+)/i|max:191|unique:users',
        ]);

        $data_array = $request->except('_token', 'roles', 'permissions', 'password_confirmation');
        $data_array['name'] = $request->first_name . ' ' . $request->last_name;
        $data_array['created_by'] = auth()->user()->id;
        $data_array['user_type'] = 'user';
        if ($request->confirmed == 1) {
            $data_array = Arr::add($data_array, 'email_verified_at', Carbon::now());
        } else {
            $data_array = Arr::add($data_array, 'email_verified_at', null);
        }

        $user = User::create($data_array);

        $roles = $request['roles'];
        $permissions = $request['permissions'];

        // Sync Roles
        $roles = ['user'];
        $user->syncRoles($roles);

        \Artisan::call('cache:clear');

        // Copy super admin settings to new user
        \App\Models\Setting::copySuperAdminSettingsToUser($user->id);

        event(new UserCreated($user));

        $message = __('user.user_created');

        if ($request->email_credentials == 1) {
            $data = [
                'password' => $request->password,
            ];

            try {
                $user->notify(new UserAccountCreated($data));
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }

            $message = __('user.account_crdential');
        }

        return response()->json(['data' => $user, 'message' => $message, 'status' => true]);
    }



    public function myProfile()
    {
        return view('backend.profile.index');
    }

    public function authData()
    {
        $defaultImage = default_user_avatar();
        return response()->json(['data' => auth()->user(), 'defaultImage' => $defaultImage, 'status' => true]);
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                $users = User::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_status_update');
                break;

            case 'delete':
                $users = User::with('bookings')->whereIn('id', $ids)->get();

                foreach ($users as $user) {
                    $user->forceDelete();
                }
                $message = __('messages.bulk_status_delete');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('users.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => __('messages.bulk_update')]);
    }

    public function update(Request $request)
    {
        try {
            $user = Auth::user();
            $data = User::findOrFail($user->id);
            $request_data = $request->except('profile_image');
            $updated = $data->update($request_data);

            if ($request->custom_fields_data) {
                $data->updateCustomFieldData(json_decode($request->custom_fields_data));
            }

            if ($request->hasFile('profile_image')) {
                storeMediaFile($data, $request->file('profile_image'), 'profile_image');
            }
            if ($request->profile_image == null) {
                $data->clearMediaCollection('profile_image');
            }
            if ($request->profile_image_removed == 1) {
                $data->clearMediaCollection('profile_image');
            }
            if ($user->hasRole('admin')) {
                $message = __('messages.update_form', ['form' => __('profile.admin_profile')]);
            } elseif ($user->hasRole('manager')) {
                $message = __('messages.update_form', ['form' => __('profile.manager_profile')]);
            } elseif ($user->hasRole('employee')) {
                $message = __('messages.update_form', ['form' => __('profile.employee_profile')]);
            } else {
                $message = __('messages.update_form', ['form' => __('profile.profile')]);
            }
            return redirect()->back()->with('success', $message);

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                $errorMessage = '';

                if (strpos($e->getMessage(), 'users_email_unique') !== false) {
                    $errorMessage = __('messages.email_already_exists');
                } elseif (strpos($e->getMessage(), 'users_mobile_unique') !== false) {
                    $errorMessage = __('messages.mobile_already_exists');
                } else {
                    $errorMessage = __('messages.duplicate_entry_error');
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', $errorMessage);
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('messages.update_failed'));
        }
        // return response()->json(['message' => $message, 'status' => true], 200);
    }


    public function change_password(Request $request)
    {
        if (env('IS_DEMO')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
            }
            return redirect()->back()->with('error', __('messages.permission_denied'));
        }
        $user = Auth::user(); // Get the currently authenticated user

        $user_id = $user->id; // Retrieve the user's ID

        $data = User::findOrFail($user_id);

        $request_data = $request->only('old_password', 'new_password', 'confirm_password');

        if (!Hash::check($request->old_password, $data->password)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('messages.old_password_mismatch'), 'errors' => ['old_password' => __('messages.old_password_mismatch')], 'status' => false], 403);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', __('messages.old_password_mismatch'));
        }

        if ($request_data['new_password'] === $request_data['old_password']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('messages.new_password_mismatch'), 'errors' => ['new_password' => __('messages.new_password_mismatch')], 'status' => false], 403);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', __('messages.new_password_mismatch'));
        }

        if ($request_data['new_password'] !== $request_data['confirm_password']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('messages.password_mismatch'), 'errors' => ['confirm_password' => __('messages.password_mismatch')], 'status' => false], 403);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', __('messages.password_mismatch'));
        }

        $request_data['password'] = Hash::make($request_data['new_password']);

        $data->update($request_data);

        if ($request->expectsJson()) {
            return response()->json(['message' => __('messages.password_changed_successfully'), 'status' => true], 200);
        }
        return redirect()->back()->with('success', __('messages.password_changed_successfully'));
    }

    public function updateStatus(Request $request, User $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('branch.status_update')]);
    }

    public function checkUnique(Request $request)
    {
        $field = $request->field;
        $value = $request->value;

        $query = User::where($field, $value);

        if ($request->has('id')) {
            $query->where('id', '!=', $request->id);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $field == 'email' ? 'Email already exists.' : 'Mobile number already exists.'
        ]);
    }
}
