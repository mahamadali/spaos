<?php

namespace Modules\Customer\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Booking\Models\Booking;
use Modules\Customer\Http\Requests\CustomerRequest;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Yajra\DataTables\DataTables;

class CustomersController extends Controller
{
    protected string $exportClass = '\App\Exports\CustomerExport';

    public function __construct()
    {
        // Page Title
        $this->module_title = __('customer.title');

        // module name
        $this->module_name = 'customers';

        // directory path of the module
        $this->module_path = 'customer::backend';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => 'fa-regular fa-sun',
            'module_name' => $this->module_name,
            'module_path' => $this->module_path,
        ]);
        $this->middleware(['permission:view_customer'])->only('index');
        $this->middleware(['permission:edit_customer'])->only('edit', 'update');
        $this->middleware(['permission:add_customer'])->only('store');
        $this->middleware(['permission:delete_customer'])->only('destroy');
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                $customer = User::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_customer_update');
                break;

            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                User::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_customer_delete');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('branch.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => __('messages.bulk_update')]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {

        $module_action = __('messages.list');
        $columns = CustomFieldGroup::columnJsonValues(new User());
        $customefield = CustomField::exportCustomFields(new User());
        $module_title = __('customer.title');
        $export_import = true;
        $export_columns = [
            [
                'value' => 'first_name',
                'text' => __('customer.lbl_first_name'),
            ],
            [
                'value' => 'last_name',
                'text' => __('customer.lbl_last_name'),
            ],
            [
                'value' => 'email',
                'text' => __('customer.lbl_Email'),
            ],
            [
                'value' => 'varification_status',
                'text' => __('customer.lbl_verification_status'),
            ],
            // [
            //     'value' => 'is_banned',
            //     'text' => __('customer.lbl_blocked'),
            // ],
            [
                'value' => 'status',
                'text' => __('customer.lbl_status'),
            ],
        ];
        $export_url = route('backend.customers.export');
        return view('customer::backend.customers.index', compact('module_action', 'columns', 'customefield', 'export_import', 'export_columns', 'export_url', 'module_title'));
    }

    public function update_status(Request $request, User $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('branch.status_update')]);
    }
    public function index_data(Datatables $datatable, Request $request)
    {
        $module_name = $this->module_name;
        $userId = auth()->id();

        $query = User::role('user')
            ->with(['media', 'booking.branch']);

        if (auth()->user()->hasRole('admin')) {
            $query->where(function ($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhereHas('booking.branch', function ($sub) use ($userId) {
                      $sub->where('created_by', $userId);
                  });
            });
        }

        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }

        $datatable = $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($data) use ($module_name) {
                return view('customer::backend.customers.action_column', compact('module_name', 'data'));
            })

            ->addColumn('user_id', function ($data) {
                $Profile_image = optional($data)->profile_image ?? default_user_avatar();
                $name = optional($data)->full_name ?? default_user_name();
                $email =  optional($data)->email ?? '--';
                return view('booking::backend.bookings.datatable.user_id', compact('Profile_image', 'name', 'email'));
            })
            ->orderColumn('user_id', function ($query, $order) {
                $query->orderBy('users.first_name', $order) // Ordering by first name
                    ->orderBy('users.last_name', $order); // Optional: also order by last name
            }, 1)
            ->filterColumn('user_id', function ($query, $keyword) {
                if (!empty($keyword)) {
                    // Assuming 'users' table has first_name and last_name
                    $query->where(function ($query) use ($keyword) {
                        $query->where('first_name', 'like', '%' . $keyword . '%')
                            ->orWhere('last_name', 'like', '%' . $keyword . '%') // Filtering by last name
                            ->orWhere('email', 'like', '%' . $keyword . '%');
                    });
                }
            })

            ->editColumn('email_verified_at', function ($data) {
                $checked = '';
                if ($data->email_verified_at) {
                    return '<span class="badge bg-success-subtle text-success"><i class="fa-solid fa-envelope" style="margin-right: 2px"></i> ' . __('customer.msg_verified') . '</span>';
                }

                return '<button  type="button" data-url="' . route('backend.customers.verify-customer', $data->id) . '" data-token="' . csrf_token() . '" class="button-status-change btn btn-text-danger btn-sm  bg-danger-subtle"  id="datatable-row-' . $data->id . '"  name="is_verify" value="' . $data->id . '" ' . $checked . '>Verify</button>';
            })

            ->editColumn('is_banned', function ($data) {
                $checked = '';
                if ($data->is_banned) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="' . route('backend.customers.block-customer', $data->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $data->id . '"  name="is_banned" value="' . $data->id . '" ' . $checked . '>
                    </div>
                 ';
            })

            ->editColumn('status', function ($row) {
                $checked = '';
                if ($row->status) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="' . route('backend.customers.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $row->id . '"  name="status" value="' . $row->id . '" ' . $checked . '>
                    </div>
                ';
            })
            ->editColumn('updated_at', function ($data) {
                $module_name = $this->module_name;

                $diff = Carbon::now()->diffInHours($data->updated_at);

                if ($diff < 25) {
                    return $data->updated_at->diffForHumans();
                } else {
                    return $data->updated_at->isoFormat('llll');
                }
            })
            ->editColumn('gender', function ($row) {
                return ucfirst($row->gender); // Capitalizing the first letter
            })

            ->orderColumns(['id'], '-:column $1');

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatable, User::CUSTOM_FIELD_MODEL, null);

        return $datatable->rawColumns(array_merge(['user_id', 'action', 'status', 'is_banned', 'email_verified_at', 'check', 'image'], $customFieldColumns))
            ->toJson();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CustomerRequest $request)
    {
        $auth_user = User::find(auth()->id());
        if (auth()->user()->hasRole('admin') && !$auth_user->currentSubscription) {
            return response()->json([
                'message' => __('messages.you_cant_add_customer'),
                'status' => false
            ], 422);
        }
        if ($auth_user->currentSubscription && $auth_user->customerLimitReach()) {
            return response()->json(['message' => __('messages.you_cant_add_customer_limit_reached'), 'status' => false], 200);
        }

        $data = $request->all();

        $data['created_by'] = auth()->id();
        $data['user_type'] = 'user';
        $data = User::create($data);

        $data->syncRoles(['user']);

        \Artisan::call('cache:clear');

        if ($request->custom_fields_data) {
            $data->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        if ($request->has('profile_image')) {
            $request->file('profile_image');

            storeMediaFile($data, $request->file('profile_image'), 'profile_image');
        }

        $message = __('messages.create_form', ['form' => __('customer.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function edit($id)
    {
        $data = User::findOrFail($id);

        if (! is_null($data)) {
            $custom_field_data = $data->withCustomFields();
            $data['custom_field_data'] = collect($custom_field_data->custom_fields_data)
                ->filter(function ($value) {
                    return $value !== null;
                })
                ->toArray();
        }

        $data['profile_image'] = $data->profile_image;

            return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CustomerRequest $request, $id)
    {
        $data = User::findOrFail($id);

        $request_data = $request->except('profile_image');

        $data->update($request_data);

        if ($request->custom_fields_data) {
            $data->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        if ($request->hasFile('profile_image')) {
            storeMediaFile($data, $request->file('profile_image'), 'profile_image');
        }
        if ($request->profile_image == null) {
            $data->clearMediaCollection('profile_image');
        }
        $message = __('messages.update_form', ['form' => __('customer.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (env('IS_DEMO')) {
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
        }
        $data = User::findOrFail($id);

        $booking = Booking::where('user_id', $id)->where('status', '!=', 'completed')->update(['status' => 'cancelled']);

        $data->tokens()->delete();

        $data->forceDelete();

        $message = __('messages.delete_form', ['form' => __('customer.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * List of trashed ertries
     * works if the softdelete is enabled.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function trashed()
    {
        $module_name = $this->module_name;

        $module_name_singular = Str::singular($module_name);

        $module_action = __('messages.trash');

        $data = User::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate();

        return view('customer::backend.customers.trash', compact('data', 'module_name_singular', 'module_action'));
    }

    /**
     * Restore a soft deleted entry.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function restore($id)
    {
        $module_action = __('messages.restore');

        $data = User::withTrashed()->find($id);
        $data->restore();

        return redirect('app/customers');
    }

    public function change_password(Request $request)
    {
        $data = $request->all();
        $user_id = $data['user_id'];
        $old_password = $data['old_password'] ?? '';
        $new_password = $data['password'] ?? '';
        $confirm_password = $data['confirm_password'] ?? '';

        $user = User::findOrFail($user_id);

        if (! Hash::check($old_password, $user->password)) {
            return response()->json(['message' => __('messages.old_password_mismatch'), 'errors' => ['old_password' => __('messages.old_password_mismatch')], 'status' => false], 403);
        }

        if ($old_password === $new_password) {
            return response()->json(['message' => __('messages.new_password_mismatch'), 'errors' => ['password' => __('messages.new_password_mismatch')], 'status' => false], 422);
        }

        if ($new_password !== $confirm_password) {
            return response()->json(['message' => __('messages.password_mismatch'), 'errors' => ['confirm_password' => __('messages.password_mismatch')], 'status' => false], 422);
        }
        $request_data = $request->only('password');
        $request_data['password'] = Hash::make($request_data['password']);

        $user->update($request_data);

        $message = __('messages.password_update');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function block_customer(Request $request, User $id)
    {
        $id->update(['is_banned' => $request->status]);

        if ($request->status == 1) {
            $message = __('messages.google_blocked');
        } else {
            $message = __('messages.google_unblocked');
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function verify_customer(Request $request, $id)
    {
        $data = User::findOrFail($id);

        $current_time = Carbon::now();

        $data->update(['email_verified_at' => $current_time]);

        return response()->json(['status' => true, 'message' => __('messages.customer_verify')]);
    }

    public function uniqueEmail(Request $request)
    {
        $email = $request->input('email');
        $userId = $request->input('user_id');

        $isUnique = User::where('email', $email)
            ->where(function ($query) use ($userId) {
                if ($userId) {
                    $query->where('id', '!=', $userId);
                }
            })
            ->doesntExist();

        return response()->json(['isUnique' => $isUnique]);
    }

    public function verifyLimit(Request $request)
    {
        $auth_user = User::find(auth()->id());

        $response = [
            'status' => true,
            'message' => __('messages.action_allowed')
        ];

        // Ensure the request has a valid 'type'
        if (!$request->has('type')) {
            return response()->json([
                'status' => false,
                'message' => __('messages.invalid_request')
            ], 400);
        }

        // Handle different modules using switch-case
        switch ($request->type) {
            case 'customer':
                if (auth()->user()->hasRole('admin')) {
                    $currentSubscription = $auth_user->currentSubscription;

                    if (!$currentSubscription || $currentSubscription->end_date < now()) {
                        return response()->json([
                            'message' => __('messages.you_cant_add_customer'),
                            'status' => false
                        ]);
                    }

                    if ($auth_user->customerLimitReach()) {
                        return response()->json([
                            'message' => __('messages.you_cant_add_customer_limit_reached'),
                            'status' => false
                        ]);
                    }
                }
                break;

            case 'staff':
                if (auth()->user()->hasRole('admin')) {
                    $currentSubscription = $auth_user->currentSubscription;

                    if (!$currentSubscription || $currentSubscription->end_date < now()) {
                        return response()->json([
                            'message' => __('messages.you_cant_add_staff'),
                            'status' => false
                        ]);
                    }

                    if ($auth_user->staffLimitReach()) {
                        return response()->json([
                            'message' => __('messages.you_cant_add_staff_limit_reached'),
                            'status' => false
                        ]);
                    }
                }
                break;

            case 'service':
                if (auth()->user()->hasRole('admin')) {
                    $currentSubscription = $auth_user->currentSubscription;

                    if (!$currentSubscription || $currentSubscription->end_date < now()) {
                        return response()->json([
                            'message' => __('messages.you_cannot_add_a_service_no_active_subscription_found'),
                            'status' => false
                        ]);
                    }

                    if ($auth_user->serviceLimitReach()) {
                        return response()->json([
                            'message' => __('messages.cant_add_service_limit_reached'),
                            'status' => false
                        ]);
                    }
                }
                break;
            case 'branch':
                if (auth()->user()->hasRole('admin')) {
                    $currentSubscription = $auth_user->currentSubscription;

                    if (!$currentSubscription || $currentSubscription->end_date < now()) {
                        return response()->json([
                            'message' => __('branch.no_active_subscription'),
                            'status' => false
                        ]);
                    }

                    if ($auth_user->branchLimitReach()) {
                        return response()->json([
                            'message' => __('branch.branch_limit_reached'),
                            'status' => false
                        ]);
                    }
                }
                break;
            case 'booking':
                if (auth()->user()->hasRole('admin')) {
                    $currentSubscription = $auth_user->currentSubscription;

                    if (!$currentSubscription || $currentSubscription->end_date < now()) {
                        return response()->json([
                            'message' => __('messages.no_active_subscription'),
                            'status' => false
                        ]);
                    }

                    $currentBookingsCount = Booking::withTrashed()
                        ->where('created_by', $auth_user->id)
                        ->where('created_at', '>=', $currentSubscription->start_date) // Only count bookings after the subscription start date
                        ->count();

                    if ($currentBookingsCount >= $currentSubscription->max_appointment) {
                        return response()->json([
                            'message' => __('messages.booking_limit_exceeded'),
                            'status' => false
                        ]);
                    }
                }
                break;

            default:
                return response()->json([
                    'status' => false,
                    'message' => __('messages.invalid_request')
                ]);
        }

        return response()->json($response);
    }
}
