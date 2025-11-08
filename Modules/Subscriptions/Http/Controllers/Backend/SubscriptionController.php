<?php

namespace Modules\Subscriptions\Http\Controllers\Backend;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Subscriptions\Models\Plan;
use Modules\Subscriptions\Models\Subscription;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use App\Exports\SubscriptionsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public $module_title;
    public $module_name;
    public $module_icon;

    public function __construct()
    {
        // Page Title
        $this->module_title = __('messages.subscriptions');

        // module name
        $this->module_name = 'subscriptions';

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
        $module_title = __('messages.subscriptions');
        $module_action = __('messages.active') . ' ' . __('messages.subscriptions');
        $plans = Plan::all();
        return view('subscriptions::backend.subscriptions.index', compact('module_action','plans','module_title'));
    }

    public function expired()
    {
        $module_title = __('messages.subscriptions');

        $module_action = __('promotion.lbl_expired') . ' ' . __('messages.subscriptions');
        $plans = Plan::all();
        $subscription_type = 'expired';
        return view('subscriptions::backend.subscriptions.index', compact('module_action','plans','subscription_type','module_title'));
    }

    public function pending()
    {
        $module_title = __('messages.subscriptions');
        $module_action = __('order_report.pending') . ' ' . __('messages.subscriptions');
        $plans = Plan::all();
        $approve_payment_count = Payment::where('status','Approved')->count();

        return view('payments.index', compact('module_action','plans','approve_payment_count','module_title'));
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $subscriptionType = $request->input('subscription_type');
        $query = auth()->user()->hasRole('super admin')
            ? Subscription::with(['user' => function ($q) {
                $q->withTrashed();
            }, 'subscription_transaction'])
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.name')) != 'Free'") // Exclude Free plans
            : Subscription::with(['user' => function ($q) {
                $q->withTrashed();
            }, 'subscription_transaction'])
            ->where('subscriptions.user_id', auth()->id())
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.name')) != 'Free'");

        if ($subscriptionType === 'expired') {
            $query->whereIn('subscriptions.status', ['Inactive', 'cancel']);
        } elseif ($subscriptionType == 'expired') {
            $query->whereDate('subscriptions.end_date', '<', Carbon::today())->where('status', 'inactive');
        } elseif ($subscriptionType == 'active') {
            $query->whereDate('subscriptions.end_date', '>=', Carbon::today())->where('status', 'active');
        } else {
            $query->whereDate('subscriptions.end_date', '>=', Carbon::today())->where('status', 'active');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');

            // Ensure $search is a string
            if (is_array($search)) {
                $search = implode(' ', $search);
            }

            $query->where(function ($q) use ($search) {
                $q->where('subscriptions.id', 'LIKE', "%{$search}%")
                    ->orWhere('subscriptions.transaction_id', 'LIKE', "%{$search}%")
                    ->orWhere('subscriptions.currency', 'LIKE', "%{$search}%")
                    ->orWhere('subscriptions.status', 'LIKE', "%{$search}%")
                    ->orWhere('subscriptions.gateway_type', 'LIKE', "%{$search}%")
                    ->orWhere('subscriptions.payment_method', 'LIKE', "%{$search}%")
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.name')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.type')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.identifier')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.price')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.description')) LIKE ?", ["%{$search}%"])
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('first_name', 'LIKE', "%{$search}%")
                                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                                  ->orWhere('email', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range); // Ensure correct delimiter
            $startDate = Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', trim($dates[1]))->endOfDay();

            $query->whereBetween('start_date', [$startDate, $endDate]);
        }

        $query->orderBy('subscriptions.updated_at', 'desc');

        return $datatable->eloquent($query)
            ->editColumn('payment_method', function ($data) {
                return $data->gateway_type ?? '-';
            })
            ->editColumn('plan_name', function ($data) {
                return json_decode($data->plan_details, true)['name'] ?? '-';
            })
            ->editColumn('plan_type', function ($data) {
                return json_decode($data->plan_details, true)['type'] ?? '-';
            })
            ->editColumn('amount', function ($data) {
                try {
                    return \Currency::format($data->total_amount ?? 0);
                } catch (\Exception $e) {
                    \Log::error('Error getting transaction amount: ' . $e->getMessage());
                    return \Currency::format(0);
                }
            })
            ->editColumn('created_at', function ($data) {
                return formatDateOrTime($data->created_at,'date');
            })
            ->editColumn('start_date', function ($data) {
                return formatDateOrTime($data->start_date,'date');
            })
            ->editColumn('end_date', function ($data) {
                return formatDateOrTime($data->end_date,'date');
            })
            ->editColumn('status', function ($data) {
                $statusClass = match(strtolower($data->status)) {
                    'active' => 'success',
                    'inactive', 'cancel' => 'danger',
                    'pending' => 'warning',
                    default => 'secondary'
                };
                
                $translatedStatus = __('frontend.' . strtolower($data->status));
                
                return '<span class="badge bg-'.$statusClass.'" data-order="'.$data->status.'">' 
                        . $translatedStatus . 
                       '</span>';
            })
            ->orderColumn('status', function ($query, $order) {
                $query->orderBy('status', $order);
            })
            ->editColumn('user.first_name', function ($data) {
                if ($data->user) {
                    return $data->user->deleted_at ? '<span>' .__('messages.deleted_user').'</span>' : $data->user->first_name;
                }
                return '<span class="text-danger">'.__('messages.deleted_user').'</span>';
            })
            ->editColumn('user.last_name', function ($data) {
                if ($data->user) {
                    return $data->user->deleted_at ? '<span>' .__('messages.deleted_user').'</span>' : $data->user->last_name;
                }
                return '<span class="text-danger">'.__('messages.deleted_user').'</span>';
            })
            ->editColumn('updated_at', function ($data) {
                return $data->updated_at;
            })
            ->editColumn('duration', function ($data) {
                $planDetails = json_decode($data->plan_details, true);
                
                $durationSuffix = match($planDetails['type'] ?? '') {
                    'Monthly' => 'Month',
                    'Yearly' => 'Year',
                    'Weekly' => 'Week',
                    default => 'Day'
                };
            
                return ($planDetails['duration'] ?? 0) . ' ' . $durationSuffix;
            })
            ->rawColumns(['action', 'status', 'user.first_name', 'user.last_name'])
            ->orderColumns(['id'], '-:column $1')
            ->toJson();
    }

    public function pending_subscription(Datatables $datatable, Request $request)
    {
        $query = Payment::query()->with(['user', 'plan', 'subscription'])->where('payments.status',0);

        if ($request->filled('search') && $request->filled('search.value') ) {
            $search = $request->input('search');

            // Ensure $search is a string
            if (is_array($search)) {
                $search = implode(' ', $search);
            }

            $query->where(function ($q) use ($search) {
                $q
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.name')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.type')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.identifier')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.price')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.description')) LIKE ?", ["%{$search}%"])
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('first_name', 'LIKE', "%{$search}%")
                                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                                  ->orWhere('email', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Apply filters
        if ($request->filled('plan_id') && $request->plan_id !=null ) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->filled('date_range') && $request->date_range !=null ) {
            $dates = explode(' to ', $request->date_range); // Ensure correct delimiter
            $startDate = Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', trim($dates[1]))->endOfDay();

            $query->whereBetween('payment_date', [$startDate, $endDate]);
        }

        return $datatable->eloquent($query)
            ->editColumn('amount', function ($data) {
                return \Currency::format($data->amount);
            })
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row " name="select_payment" value="'.$data->id.'" data-id="'.$data->id.'">';
            })
            ->addColumn('image', function ($data) {
                // Check if the image exists and return an img tag or a placeholder
                return $data->image ? asset($data->image) :  default_feature_image();
            })
            ->editColumn('payment_date', function ($data) {
                return formatDateOrTime($data->payment_date,'date');
            })
            ->editColumn('payment_method', function ($data) {
                return $data->payment_method == 1 ? 'Online' : 'Offline';
            })
            ->editColumn('plan_name', function ($data) {
                return json_decode($data->plan_details, true)['name'] ?? '-';
            })
            ->orderColumn('plan_name', function ($query, $order) {
                $query->join('plan', 'plan.id', '=', 'payments.plan_id')
                      ->orderBy('plan.name', $order);
            })
            ->editColumn('status', function ($data) {
                return $data->status == 0 ? 'Pending' : ($data->status == 1 ? 'Approved' : 'Rejected');
            })
            ->editColumn('duration', function ($data) {
                $planDetails = json_decode($data->plan_details, true);
                
                $durationSuffix = match($planDetails['type'] ?? '') {
                    'Monthly' => 'Month',
                    'Yearly' => 'Year',
                    'Weekly' => 'Week',
                    default => 'Day'
                };
            
                return ($planDetails['duration'] ?? 0) . ' ' . $durationSuffix;
            })
            ->orderColumn('duration', function ($query, $order) {
                $query->orderByRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.duration')) AS UNSIGNED) {$order}");
            })
            ->rawColumns(['action', 'status','check'])
            ->orderColumns(['id'], '-:column $1')
            ->toJson();
    }

    public function allSubscription()
    {
        // dd('all subscription');
        $module_title = __('messages.all_subscriptions');
        $module_action = __('messages.all') . ' ' . __('messages.subscriptions');
        $plans = Plan::all();

      
        $activeSubscriptions = Subscription::where('status', 'active')
            // ->whereDate('end_date', '>=', Carbon::today())
            ->whereJsonDoesntContain('plan_details->identifier', 'free')
            ->count();

       $expiredSubscriptions = Subscription::where(function($query) {
      
        $query->Where(function($q) {
            $q->where('status', 'inactive');
            //   ->whereDate('end_date', '<', Carbon::today());
        })
        ->orWhere('status', 'cancel'); 

    })
    ->whereJsonDoesntContain('plan_details->identifier', 'free')
    ->count();


    $pendingSubscriptions = Payment::query()
        ->with(['user', 'plan', 'subscription'])
        ->where('status', 0) // 0 represents pending status
        ->whereJsonDoesntContain('plan_details->identifier', 'free')
        ->count();

        return view('subscriptions::backend.subscriptions.AllSubscriptionIndex', 
            compact('module_action', 'plans', 'module_title', 
                    'activeSubscriptions', 'expiredSubscriptions', 'pendingSubscriptions')
        );
    }

    public function allSubscriptionData(Request $request)
    {
        // Query for subscriptions
        $query = Subscription::with(['user', 'plan'])
            ->select(
                'subscriptions.*',
                DB::raw("'subscription' as record_type"),
                DB::raw("CONCAT('sub_', id) as unique_id")
            )
            ->whereJsonDoesntContain('plan_details->identifier', 'free');
        
        // Query for pending payments
        $pendingQuery = Payment::query()
            ->with(['user', 'plan'])
            ->where('status', 0)
            ->select(
                'payments.*',
                DB::raw("'payment' as record_type"),
                DB::raw("CONCAT('pay_', id) as unique_id")
            )
            ->whereJsonDoesntContain('plan_details->identifier', 'free')
            ->whereDoesntHave('subscription'); // Exclude payments that already have subscriptions

        // Apply filters before merging
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query = $pendingQuery;
            } else {
                $query->where('status', $request->status);
                $pendingQuery = null;
            }
        }

        // Get the data
        $subscriptions = $query->get();
        $pendingPayments = $pendingQuery ? $pendingQuery->get() : collect();

        // Create a collection with unique IDs
        $mergedData = collect();
        
        foreach ($subscriptions as $subscription) {
            $mergedData->push($subscription);
        }
        
        foreach ($pendingPayments as $payment) {
            // Only add payment if there's no subscription with the same underlying ID
            if (!$mergedData->contains('id', $payment->subscription_id)) {
                $mergedData->push($payment);
            }
        }

        // Apply additional filters to merged data
        if ($request->filled('plan_id')) {
            $mergedData = $mergedData->filter(function($item) use ($request) {
                return $item->plan_id == $request->plan_id;
            });
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', trim($dates[1]))->endOfDay();
            
            $mergedData = $mergedData->filter(function($item) use ($startDate, $endDate) {
                $date = $item->start_date ?? $item->payment_date;
                return $date && Carbon::parse($date)->between($startDate, $endDate);
            });
        }

        if ($request->has('search') && $request->search) {
            $search = strtolower($request->search);
            $mergedData = $mergedData->filter(function($item) use ($search) {
                $planDetails = json_decode($item->plan_details, true);
                return 
                    (isset($item->user) && (
                        str_contains(strtolower($item->user->first_name ?? ''), $search) ||
                        str_contains(strtolower($item->user->last_name ?? ''), $search)
                    )) ||
                    (isset($planDetails['name']) && 
                        str_contains(strtolower($planDetails['name']), $search)
                    );
            });
        }

        return DataTables::of($mergedData)
            ->addColumn('full_name', function ($data) {
                if ($data->user) {
                    if ($data->user->deleted_at) {
                        return '<span>' . __('messages.deleted_user') . '</span>';
                    }
                    return $data->user->first_name . ' ' . $data->user->last_name;
                }
                return '<span class="text-danger">' . __('messages.deleted_user') . '</span>';
            })
            ->editColumn('created_at', function ($data) {
                return formatDateOrTime($data->created_at, 'date');
            })
            ->editColumn('start_date', function ($data) {
                // Check if it's a Payment (pending) record
                if ($data instanceof Payment) {
                    return '-';
                }
                return formatDateOrTime($data->start_date, 'date');
            })
            ->editColumn('end_date', function ($data) {
                if ($data instanceof Payment) {
                    return '-';
                }
                return formatDateOrTime($data->end_date, 'date');
            })
            ->editColumn('amount', function ($data) {
                return amountWithCurrencySymbol(number_format($data->total_amount ?? $data->amount, 2), defaultCurrency());
            })
            ->editColumn('plan_name', function ($data) {
                return json_decode($data->plan_details, true)['name'] ?? '-';
            })
            ->editColumn('payment_method', function ($data) {
                return ucfirst($data->gateway_type ?? '-') ?? '-';
            })
            ->editColumn('duration', function ($data) {
                $planDetails = json_decode($data->plan_details, true);
                $durationSuffix = match($planDetails['type'] ?? '') {
                    'Monthly' => 'Month',
                    'Yearly' => 'Year',
                    'Weekly' => 'Week',
                    default => 'Day'
                };
                return ($planDetails['duration'] ?? 0) . ' ' . $durationSuffix;
            })
            ->editColumn('status', function ($data) {
                $status = $data instanceof Payment ? 'pending' : strtolower($data->status ?? 'pending');
                
                $statusClass = match($status) {
                    'active' => 'success',
                    'inactive', 'cancel' => 'danger',
                    'pending' => 'warning',
                    default => 'warning'
                };
                
                $translatedStatus = __('frontend.' . $status);
                
                return '<span class="badge bg-'.$statusClass.'" data-order="'.$status.'">' 
                        . $translatedStatus . 
                       '</span>';
            })
            ->rawColumns(['status', 'full_name'])
            ->make(true);
    }

    /**
     * Remove the specified subscription from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
      
            $subscription = Subscription::findOrFail($id);

            // Delete related payment records
            Payment::where('subscription_id', $id)->delete();

            // Delete the subscription
            $subscription->delete();

            return response()->json([
                'success' => true,
                'message' => __('messages.subscription_deleted_successfully')
            ]);
    }

    public function export(Request $request)
    {
        // Query for subscriptions
        $subscriptions = Subscription::with(['user', 'plan'])
            ->select(
                'subscriptions.*',
                DB::raw("'subscription' as record_type")
            )
            ->whereJsonDoesntContain('plan_details->identifier', 'free');

        // Query for pending payments
        $pendingPayments = Payment::query()
            ->with(['user', 'plan'])
            ->where('status', 0)
            ->select(
                'payments.*',
                DB::raw("'payment' as record_type")
            )
            ->whereJsonDoesntContain('plan_details->identifier', 'free')
            ->whereDoesntHave('subscription');

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $data = $pendingPayments->get();
            } else {
                $data = $subscriptions->where('status', $request->status)->get();
            }
        } else {
            // If no status filter, get all records
            $data = $subscriptions->get();
            $pendingData = $pendingPayments->get();
            $data = $data->concat($pendingData); // Use concat instead of merge
        }

        // Apply plan filter
        if ($request->filled('plan_id')) {
            $data = $data->filter(function($item) use ($request) {
                return $item->plan_id == $request->plan_id;
            })->values(); // Reset array keys
        }

        // Apply date range filter
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            $startDate = Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', trim($dates[1]))->endOfDay();
            
            $data = $data->filter(function($item) use ($startDate, $endDate) {
                $date = $item instanceof Payment ? $item->payment_date : $item->start_date;
                return $date && Carbon::parse($date)->between($startDate, $endDate);
            })->values(); // Reset array keys
        }

        // Handle export format
        $fileName = 'subscriptions_' . now()->format('Y-m-d');
        
        if ($request->get('format') === 'pdf') {
            return (new SubscriptionsExport($data))
                ->downloadPDF($fileName);
        }

        return Excel::download(new SubscriptionsExport($data), $fileName . '.xlsx');
    }
}
