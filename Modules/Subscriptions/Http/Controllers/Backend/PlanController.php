<?php

namespace Modules\Subscriptions\Http\Controllers\Backend;

use App\Models\Permission;
use App\Models\PlanFeature;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Modules\MenuBuilder\Models\MenuBuilder;
use Modules\Subscriptions\Http\Requests\PlanRequest;
use Modules\Subscriptions\Models\Plan;
use Modules\Subscriptions\Models\PlanLimitation;
use Modules\Subscriptions\Models\PlanLimitationMapping;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public $module_title;
    public $module_name;
    public $module_icon;

    public function __construct()
    {
        // Page Title
        $this->module_title = __('messages.plans');

        // module name
        $this->module_name = 'plans';

        // module icon
        $this->module_icon = 'fa-solid fa-clipboard-list';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                $branches = Plan::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_plan_update');
                break;

            case 'delete':
                Plan::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_plan_delete');
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
     * @return Renderable
     */
    public function index()
    {
        $module_title = __('messages.plans');
        $module_action = __('messages.list');
        $module_name = $this->module_name;
        $payment_method_setting = Setting::whereIn('name', ['razor_payment_method', 'str_payment_method', 'paypal_payment_method'])->where('val', 1)->first();

        $payment_method = $payment_method_setting ? $payment_method_setting->name : 'str_payment_method';

        $columns = CustomFieldGroup::columnJsonValues(new Plan());

        $customefield = CustomField::exportCustomFields(new Plan());

        return view('subscriptions::backend.plan.index', compact('module_action', 'module_name', 'columns', 'customefield', 'payment_method', 'module_title'));
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        // dd('hello');
        $query = Plan::query()
            ->withCount('subscriptions');  // Add this line to count subscriptions

        $query = auth()->user()->hasRole('super admin')
            ? $query
            : $query->where('status', 1)->where('is_free_plan', 0);

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
            ->addColumn('action', function ($data) {
                return view('subscriptions::backend.plan.action_column', compact('data'));
            })
            ->editColumn('price', function ($data) {
                if ($data->price == 0 || $data->price == '0.00' || $data->price == '0') {
                    return 'Free';
                }
                return amountWithCurrencySymbol($data->price, defaultCurrency());
            })
            ->editColumn('discount_value', function ($data) {
                if ($data->has_discount) {
                    if ($data->discount_type === 'percentage') {
                        return $data->discount_value . '%';
                    } else {
                        return amountWithCurrencySymbol($data->discount_value, defaultCurrency());
                    }
                }
                return '-';
            })
            ->editColumn('tax', function ($data) {
                return amountWithCurrencySymbol($data->tax, defaultCurrency());
            })
            ->editColumn('total_price', function ($data) {
                $basePrice = $data->has_discount ? $data->discounted_price : $data->price;
                $totalPrice = $basePrice + $data->tax;
                
                // Show "Free" for zero-priced plans
                if ($totalPrice == 0 || $totalPrice == '0.00' || $totalPrice == '0') {
                    return 'Free';
                }
                
                return amountWithCurrencySymbol(number_format($totalPrice, 2), defaultCurrency());
            })
            ->editColumn('status', function ($row) {
                $checked = '';
                if ($row->status) {
                    $checked = 'checked="checked"';
                }

                return '
                <div class="form-check form-switch ">
                    <input type="checkbox" data-url="' . route('backend.subscription.plans.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $row->id . '"  name="status" value="' . $row->id . '" ' . $checked . '>
                </div>
               ';
            })
            ->editColumn('updated_at', function ($data) {
                $diff = Carbon::now()->diffInHours($data->updated_at);

                if ($diff < 25) {
                    return $data->updated_at->diffForHumans();
                } else {
                    return $data->updated_at->isoFormat('llll');
                }
            })
            ->addColumn('subscription_count', function ($data) {
                $count = $data->subscriptions_count ?? 0;
                if ($count > 0) {
                    $url = route('backend.subscriptions.all_subscription', ['plan_id' => $data->id]);
                    return '<a href="' . $url . '" class="text-primary">' . $count . '</a>';
                }
                return '<span>' . $count . '</span>';
            })
            ->orderColumns(['id'], '-:column $1');

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatable, Plan::CUSTOM_FIELD_MODEL, null);

        return $datatable->rawColumns(array_merge(['action', 'status', 'check', 'subscription_count'], $customFieldColumns))
            ->toJson();
    }

    public function index_list(Request $request)
    {
        $term = trim($request->q);

        $query_data = PlanLimitation::where('status', 1)
            ->where(function ($q) {
                if (! empty($term)) {
                    $q->orWhere('name', 'LIKE', "%$term%");
                }
            })->get();

        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'name' => $row->name,
                'limit' => $row->limit,
            ];
        }

        return response()->json($data);
    }
    public function plan_list(Request $request)
    {
        $term = trim($request->q);

        $query_data = Plan::where('status', 1)
            ->where('name', '!=', 'Free')
            ->where(function ($q) {
                if (! empty($term)) {
                    $q->orWhere('name', 'LIKE', "%$term%");
                }
            })->get();

        $data = [];

        foreach ($query_data as $row) {
            $data[] = [

                'id' => $row->id,
                'name' => $row->name,
            ];
        }

        return response()->json($data);
    }




    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */


    public function create()
    {
        // dd('hello');
        $user = auth()->user();
        $excludedTitles = ['sidebar.main', 'sidebar.company', 'sidebar.users', 'sidebar.finance', 'sidebar.reports', 'sidebar.system', 'Plans', 'Payments', 'Subscriptions', 'sidebar.plans', 'sidebar.payments', 'sidebar.product', 'sidebar.variations', 'sidebar.orders', 'sidebar.orders_report', 'sidebar.supply', 'sidebar.reviews',];

        $menus = MenuBuilder::whereNull('parent_id')->where('menu_type', 'vertical')
            ->when($user->user_type == 'super admin', function ($query) use ($excludedTitles) {
                $query->whereNotIn('title', $excludedTitles);
            })
            ->get();


        $freeplan = Plan::where('identifier', 'free')->first();
        $data['freeplan'] = $freeplan;
        $data['module_action'] = 'List';
        $data['module_name'] = $this->module_name;
        $data['module_title'] = __('messages.plans');
        $data['features'] = [];
        $data['menus'] = $menus;

        return view('subscriptions::backend.plan.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if ($request->plan_type !== 'free_plan') {
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('plan')->where(function ($query) use ($request) {
                        return $query->where('type', $request->type);
                    })->ignore($request->id), // Ignore current plan when updating
                ],
                'type' => 'required|string', // Ensure type is required
            ], [
                'name.unique' => 'The plan with this name and interval already exists. Please choose a different name or interval.',
            ]);
        }

        $plan = ($request->id) ? Plan::find($request->id) : new Plan();
        $plan_name = $request->name;

        // Only check for existing free plan if creating a new plan (not updating)
        if (strtolower($plan_name) === 'free' && !$request->id && Plan::where('identifier', 'free')->exists()) {
            return back()->with('error', __('messages.free_plan_already_exists'));
        }


        if ($request->plan_type == 'free_plan') {
            $plan_name = 'Free';
            $plan->is_free_plan = 1;
            $plan->type = $request->free_plan_type;
            $plan->duration = $request->free_plan_duration;
            $plan->status = 1;

            // Set limits to 0 for the free plan
            $plan->max_appointment = 0;
            $plan->max_branch = 0;
            $plan->max_service = 0;
            $plan->max_staff = 0;
            $plan->max_customer = 0;

            // Set predefined permission IDs for free plan
            $defaultPermissions = [2, 3, 5, 6, 7, 11, 12, 14, 15, 17, 18, 19, 21, 22, 23, 24, 26, 27, 28, 31, 32, 70];
            $selectedPermissionIds = $defaultPermissions;
        } else {
            $plan->type = $request->type;
            $plan->duration = $request->duration ?? 1;
            $plan->status = $request->status ? 1 : 0;

            // Assign user-input values for paid plans
            $plan->max_appointment = $request->max_appointment;
            $plan->max_branch = $request->max_branch;
            $plan->max_service = $request->max_service;
            $plan->max_staff = $request->max_staff;
            $plan->max_customer = $request->max_customer;

            // Assign user-selected permissions for paid plans
            $selectedPermissionIds = $request->permission_ids;

            // Add discount handling
            $plan->has_discount = $request->has_discount ? 1 : 0;

            if ($request->has_discount) {
                $plan->discount_type = $request->discount_type;
                $plan->discount_value = $request->discount_value;

                // Calculate discounted price
                if ($request->discount_type === 'percentage') {
                    $discountAmount = ($request->price * $request->discount_value) / 100;
                    $plan->discounted_price = $request->price - $discountAmount;
                } else { // fixed
                    $plan->discounted_price = $request->price - $request->discount_value;
                }

                // Ensure discounted price is not negative
                $plan->discounted_price = max(0, $plan->discounted_price);
            } else {
                // Reset discount fields if discount is disabled
                $plan->discount_type = null;
                $plan->discount_value = null;
                $plan->discounted_price = null;
            }

            $plan->price = $request->price ?? 0;
        }



        $allPermissions = []; // Initialize empty array

        foreach ($selectedPermissionIds as $menuId) {
            $menu = MenuBuilder::find($menuId);
            if ($menu) {
                $permissions = $menu->permission;


                if (is_array($permissions)) {
                    // Merge permissions into allPermissions array while keeping them unique
                    $allPermissions = array_merge($allPermissions, $permissions);
                }
            }
        }

        // Remove duplicates from the permissions array
        $allPermissions = array_unique($allPermissions);

        // Ensure it's a sequential array (fix numeric keys issue)
        $allPermissions = array_values($allPermissions);

        $permission_names = Permission::whereIn('id', $request->permission_ids ?? [])
            ->pluck('name');

        $plan->name = $plan_name;
        $plan->identifier = Str::slug($plan_name, '_');
        $plan->permission_ids = json_encode($allPermissions);
        $plan->currency = defaultCurrency();
        $plan->description = $request->description;

        $plan->created_by = Auth::id();
        $plan->updated_by = Auth::id();
        $plan->save();

        PlanFeature::where('plan_id', $plan->id)->delete();

        if ($request->has('features')) {
            foreach ($request->features as $value) {
                if (!empty(trim($value))) {
                    $feature = new PlanFeature;
                    $feature->plan_id = $plan->id;
                    $feature->title = $value;
                    $feature->save();
                }
            }
        }

        // Update tax and total price calculations to consider discount
        $plan->tax = $plan->calculateTotalTax();

        // Modify total price calculation to use discounted price when applicable
        if ($plan->has_discount && $plan->discounted_price) {
            $plan->total_price = $plan->discounted_price + $plan->tax;
        } else {
            $plan->total_price = $plan->price + $plan->tax;
        }

        $plan->save();

        return redirect(route('backend.subscription.plans.index'))->with('success', ($request->id) ? __('messages.lbl_plan') . ' ' . __('messages.updated_successfully') : __('messages.lbl_plan') . ' ' . __('messages.added_successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit($id)
    {
        $user = auth()->user();

        $excludedTitles = ['sidebar.main', 'sidebar.company', 'sidebar.users', 'sidebar.finance', 'sidebar.reports', 'sidebar.system', 'Plans', 'Payments', 'Subscriptions', 'sidebar.plans', 'sidebar.payments', 'sidebar.product', 'sidebar.variations', 'sidebar.orders', 'sidebar.orders_report', 'sidebar.supply', 'sidebar.reviews',];

        $menus = MenuBuilder::whereNull('parent_id')->where('menu_type', 'vertical')
            ->when($user->user_type == 'super admin', function ($query) use ($excludedTitles) {
                $query->whereNotIn('title', $excludedTitles);
            })
            ->get();

        $plan = Plan::find($id);
        $data['module_action'] = 'List';
        $data['module_name'] = $this->module_name;
        $data['menus'] = $menus;
        $data['plan'] = $plan;
        $data['module_title'] = __('messages.plans');
        if ($plan->identifier === 'free') {
            $data['freeplan'] = null; // Replace 'some_value' with what you want
        } else {
            $data['freeplan'] = 1;
        }
        $data['features'] = $plan->features;

        return view('subscriptions::backend.plan.create')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Renderable
     */
    public function update(PlanRequest $request, $id)
    {
        $data = Plan::findOrFail($id);

        $plan_data = $request->all();

        if ($plan_data['type'] == 2) {
            $plan_data['duration'] = 1;
        }

        $data->update($plan_data);

        $data->tax = $data->calculateTotalTax();
        $data->total_price = $data->totalPrice();
        $data->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $data->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        $limitation_data = json_decode($plan_data['data']);

        PlanLimitationMapping::where('plan_id', $id)->forceDelete();

        if (count($limitation_data) != 0 && $plan_data['planlimitation'] === 'Limited') {
            foreach ($limitation_data as $item) {
                PlanLimitationMapping::create([
                    'plan_id' => $id,
                    'planlimitation_id' => $item->planlimitation_id,
                    'limit' => $item->limit,
                ]);
            }
        }

        $message = __('messages.update_form', ['form' => __('plan.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $plan = Plan::findOrFail($id);

        $plan->delete();

        $message = __('messages.delete_form', ['form' => __('messages.plan')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function trashed()
    {
        $module_name_singular = Str::singular($this->module_name);

        $module_action = __('messages.trash');

        $data = Plan::with('user')->onlyTrashed()->orderBy('deleted_at', 'desc')->paginate();

        return view('subscriptions::backend.plan.trash', compact('data', 'module_name_singular', 'module_action'));
    }

    public function restore($id)
    {
        $data = Plan::withTrashed()->find($id);
        $data->restore();

        $message = __('messages.plan_data');

        return redirect('app/subscription/plans');
    }

    public function update_status(Request $request, Plan $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('branch.status_update')]);
    }

    public function checkPlanStartDate(Request $request)
    {
        $user = User::Find(Auth::id());
        $currentSubscription = $user->currentSubscription;

        if ($currentSubscription) {
            $current_plan_end_date = Carbon::parse($currentSubscription->end_date)->format('d-m-Y');
            $new_plan_start_date = Carbon::now()->format('d-m-Y'); // New plan starts immediately

            return response()->json([
                'status' => true,
                'new_plan_start_date' => $new_plan_start_date,
                'current_plan_end_date' => $current_plan_end_date,
                'message' => __('messages.your_current_plan_will_be_cancelled_upon_purchasing_the_new_plan')
            ]);
        }

        return response()->json(['status' => false]);
    }
}
