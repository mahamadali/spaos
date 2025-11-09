<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\Currency\Models\Currency;
use Modules\Subscriptions\Models\Plan;
use Modules\Subscriptions\Models\Subscription;
use Yajra\DataTables\DataTables;

class PaymentController extends Controller
{
    public $module_title;
    public $module_name;
    public $module_icon;

    public function __construct()
    {
        // Page Title
        $this->module_title = __('sidebar.payments');

        // module name
        $this->module_name = 'payment';

        // module icon
        $this->module_icon = 'fa-solid fa-clipboard-list';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

     /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index()
    {
        $module_action =  __('sidebar.payments');
        $module_title = __('sidebar.payments');
        $plans = Plan::where('is_free_plan',0)->get();
        $approve_payment_count = Payment::where('status','Approved')->count();

        return view('payments.index', compact('module_action','plans','approve_payment_count','module_title'));
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        // Optimize: Only load necessary relationships and add select to reduce data transfer
        $query = Payment::select([
            'payments.id',
            'payments.user_id', 
            'payments.plan_id',
            'payments.amount',
            'payments.currency',
            'payments.payment_method',
            'payments.payment_date',
            'payments.status',
            'payments.created_at',
            'payments.updated_at'
        ])
        ->with([
            'user:id,first_name,last_name,email,deleted_at',
            'plan:id,name,type,duration,max_appointment,max_branch,max_service,max_staff,max_customer,tax,has_discount,discounted_price,price,status'
        ])
        ->where('payment_method',1)
        ->whereHas('user', function($q) {
            $q->whereNull('deleted_at'); // Exclude deleted users
        });

        // Apply filters
        if ($request->filled('plan_id') && $request->plan_id !=null ) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->filled('search') && $request->filled('search.value')) {
            $search = $request->input('search');

            // Ensure $search is a string
            if (is_array($search)) {
                $search = implode(' ', $search);
            }

            $query->where(function ($q) use ($search) {
                $q->orWhereHas('plan', function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%");
                });

                $q->orWhereHas('user', function ($subQuery) use ($search) {
                    $subQuery->where('first_name', 'like', "%{$search}%");
                });

                $q->orWhereHas('user', function ($subQuery) use ($search) {
                    $subQuery->where('last_name', 'like', "%{$search}%");
                });

                $q->orWhere('amount', 'like', "%{$search}%");

                $q->orWhereDate('payment_date', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_range') &&  $request->date_range !=null ) {
            $dates = explode(' to ', $request->date_range); // Ensure correct delimiter
            $startDate = Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', trim($dates[1]))->endOfDay();

            $query->whereBetween('payment_date', [$startDate, $endDate]);
        }

        return $datatable->eloquent($query)
            ->editColumn('amount', function ($data) {
                return \Currency::format($data->amount);
            })
            ->filterColumn('amount', function ($query, $keyword) {
                if (is_numeric($keyword)) {
                    $query->where('amount', '=', $keyword);
                    // dd($query->get());
                }
            })
            ->addColumn('check', function ($data) {
                return ($data->status == 0) ? '<div class="checkbox"><input type="checkbox" class="select-table-row " name="select_payment" value="'.$data->id.'" data-id="'.$data->id.'" id="datatable-row-' . $data->id . '"><label for="datatable-row-' . $data->id . '"></label></div>' : '';
            })
            ->addColumn('image', function ($data) {
                // Check if the image exists and return an img tag or a placeholder
                return $data->image ? asset($data->image) : default_feature_image();
            })
            ->editColumn('payment_date', function ($data) {
                return formatDateOrTime($data->payment_date,'date') ?? '-';
            })
            ->editColumn('payment_method', function ($data) {
                return $data->payment_method == 1 ? 'Online' : 'Offline';
            })
            ->editColumn('plan_name', function ($data) {

                return $data->plan->name ?? '-';
            })

            ->filterColumn('plan_name', function($query, $keyword) {
                $query->whereHas('plan', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
           
            ->editColumn('duration', function ($data) {
            
                $durationSuffix = match($data->plan->type ?? '') {
                    'Monthly' => 'Month',
                    'Yearly' => 'Year',
                    'Weekly' => 'Week',
                    default => 'Day'
                };
            
                return ($data->plan->duration ?? 0) . ' ' . $durationSuffix;
            })
            ->filterColumn('duration', function ($query, $keyword) {
                $query->whereHas('plan', function ($q) use ($keyword) {
                    $q->whereRaw("CONCAT(duration, ' ', 
                        CASE type 
                            WHEN 'Monthly' THEN 'Month'
                            WHEN 'Yearly' THEN 'Year'
                            WHEN 'Weekly' THEN 'Week'
                            ELSE 'Day'
                        END
                    ) LIKE ?", ["%{$keyword}%"]);
                });
            })
            
            ->orderColumn('duration', function ($query, $order) {
                $query->select('payments.*')
                    ->leftJoin('plan', 'plan.id', '=', 'payments.plan_id')
                    ->groupBy([
                        'payments.id',
                        'payments.user_id',
                        'payments.plan_id',
                        'payments.amount',
                        'payments.currency',
                        'payments.payment_method',
                        'payments.payment_date',
                        'payments.status'
                    ])
                    ->orderByRaw("CAST(COALESCE(plan.duration, 0) AS UNSIGNED) $order");
            })

            ->editColumn('status', function ($data) {
                return $data->status == 0 ? 'Pending' : ($data->status == 1 ? 'Approved' : 'Rejected');
            })

            ->orderColumn('plan_name', function ($query, $order) {
                $query->select('payments.*')
                    ->leftJoin('plan', 'plan.id', '=', 'payments.plan_id')
                    ->groupBy('payments.id')  // Add grouping by primary key
                    ->orderBy('plan.name', $order);
            })
            ->rawColumns(['action', 'status','check'])
            ->orderColumns(['id'], '-:column $1')
            ->toJson();
    }


    public function create()
    {
        // dd('create payment');
        $module_action = __('messages.create_payment');
        $plans = Plan::where('status',1)->where('is_free_plan',0)->get();
        $users = User::role('admin')->where('status',1)->get();

        return view('payments.create', compact('module_action','plans','users'));
    }

    public function store(Request $request)
    {

        $payment = Payment::find($request->id);
        $user_id = $request->user_id;

        if ($request->id && $request->id != null) {
            $user_details = is_string($request->user_id) ? json_decode($request->user_id) : $request->user_id;


            $user_id = $user_details->id ?? null;
        }


        $currency = Currency::where('is_primary',1)->where('created_by',Auth::id())->first();
        $currency_code = $currency ? strtolower($currency->currency_code) : 'inr';

        $payment = ($payment) ? $payment : new Payment;

        $payment->user_id =  $user_id;
        $payment->plan_id = $request->plan_id;
        $payment->amount = $request->amount ?? 0;
        $payment->currency = $currency_code;
        $payment->payment_method = 1; // By default Offline payment set
        $payment->payment_date = $request->payment_date;

        $plan = Plan::where('id', $request->plan_id)->first();

        $payment->plan_details = json_encode($plan);

        if($request->hasFile('image'))
        {
            $image = $request->file('image');
            $img_name = 'pay_img'.rand(100000, 999999).time().$image->getClientOriginalName();
            $img_path = 'payments/images/'.$img_name;
            $image->move(public_path('payments/images'),$img_name);
            $payment->image = $img_path;
        }

        $payment->save();

        return redirect()->route('backend.payment.index')->with('success',($request->id) ? __('messages.payment_updated_successfully') : __('messages.payment_created_successfully'));
    }

    public function edit($id)
    {
        $module_action = __('messages.edit_payment');
        $payment = Payment::with('user')->find($id);

        $plans = Plan::where('status',1)->where('is_free_plan',0)->get();
        $users = User::role('admin')
        ->where(function ($query) use ($payment){
            $query->where('status', 1)
                  ->orWhere('id', $payment->user_id); // Include user with id 1 even if inactive
        })
        ->get();
        $payment->amount_display = getCurrencySymbolByCurrency($payment->currency) . ' ' . $payment->amount;

        return view('payments.create', compact('module_action','plans','users','payment'));
    }

    public function delete(Request $request)
    {
        $ids = Arr::wrap($request->ids);

        // Delete payments where the ID is in the array of IDs
        Payment::whereIn('id', $ids)->delete();

        return redirect()->route('backend.payment.index')->with('success',__('messages.payment_deleted'));
    }

public function approve(Request $request)
{
    // Validate the incoming request
    $request->validate([
        'ids' => 'required', // Ensure 'ids' is an array
        'ids.*' => 'exists:payments,id', // Ensure each ID exists in the payments table
    ]);

    // Retrieve the IDs from the request
    $ids = Arr::wrap($request->ids);

    // Optimize: Load all data in one query with relationships
    $payments = Payment::with(['plan', 'user'])->whereIn('id', $ids)->get();
    
    // Get all unique user IDs for batch operations
    $userIds = $payments->pluck('user_id')->unique();
    
    // Batch deactivate existing subscriptions for all users
    Subscription::whereIn('user_id', $userIds)
        ->where('status', 'active')
        ->update(['status' => 'inactive', 'is_active' => 0]);

    // Prepare subscription data for batch insert
    $subscriptionsData = [];
    $paymentUpdates = [];
    $userPermissions = [];

    foreach ($payments as $payment) {
        $plan = $payment->plan;
        
        // If plan is null, try to reload it
        if (!$plan) {
            $plan = Plan::find($payment->plan_id);
            if (!$plan) {
                \Log::error("Plan not found for payment ID: {$payment->id}, Plan ID: {$payment->plan_id}");
                continue;
            }
        }
        
        // Additional validation
        if (!$plan->type || !$plan->duration) {
            continue;
        }
        
        // Calculate subscription dates once
        $startDate = now();
        $endDate = $this->calculateEndDate($startDate, $plan->type, $plan->duration);
        
        // Prepare subscription data
        $subscriptionsData[] = [
            'plan_id' => $payment->plan_id,
            'user_id' => $payment->user_id,
            'transaction_id' => 'MANUAL_' . $payment->id . '_' . time(),
            'amount' => $plan->has_discount ? $plan->discounted_price : $plan->price,
            'discount_amount' => 0,
            'tax_amount' => $plan->tax,
            'total_amount' => $payment->amount,
            'currency' => $payment->currency ?? 'usd',
            'payment_method' => $payment->payment_method ?? 'cash',
            'gateway_type' => 'Manual',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'plan_details' => json_encode($plan),
            'gateway_response' => null,
            'is_active' => 1,
            'max_appointment' => $plan->max_appointment,
            'max_branch' => $plan->max_branch,
            'max_service' => $plan->max_service,
            'max_staff' => $plan->max_staff,
            'max_customer' => $plan->max_customer,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // Prepare payment updates
        $paymentUpdates[] = [
            'id' => $payment->id,
            'status' => 1,
            'updated_at' => now(),
        ];
        
        // Collect user permissions for batch processing
        $userPermissions[$payment->user_id] = $plan;
    }

    // Batch insert subscriptions
    Subscription::insert($subscriptionsData);
    
    // Batch update payments
    foreach ($paymentUpdates as $update) {
        Payment::where('id', $update['id'])->update(['status' => $update['status']]);
    }
    
    // Batch assign permissions (optimize this method too)
    foreach ($userPermissions as $userId => $plan) {
        $plan->givePermissionToUser($userId);
    }
    
    // Update is_subscribe flag for all users with approved payments
    $userIds = array_keys($userPermissions);
    User::whereIn('id', $userIds)->update(['is_subscribe' => 1]);

    // Return a response
    return response()->json(['message' => __('messages.payments_approved_successfully')]);
}

/**
 * Calculate end date based on plan type and duration
 */
private function calculateEndDate($startDate, $type, $duration)
{
    // Ensure duration is an integer and handle edge cases
    $duration = (int) $duration;
    if ($duration <= 0) {
        $duration = 1; // Default to 1 if duration is invalid
    }
    
    switch ($type) {
        case 'Monthly':
            return $startDate->copy()->addMonths($duration);
        case 'Yearly':
            return $startDate->copy()->addYears($duration);
        case 'Weekly':
            return $startDate->copy()->addWeeks($duration);
        case 'Daily':
            return $startDate->copy()->addDays($duration);
        default:
            return $startDate->copy()->addDays($duration);
    }
}



}
