<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingService;
use Modules\Package\Models\BookingPackages;
use Modules\Booking\Models\BookingTransaction;
use Modules\Currency\Models\Currency;
use Modules\Product\Models\Order;
use Modules\Product\Models\OrderGroup;
use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Models\SubscriptionTransactions;
use Modules\Subscriptions\Models\Plan;

class BackendController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function index(Request $request)
    {
        $months = [];
        $payments = [];
        $monthly_revenue = [];

        if (auth()->user()->hasRole('super admin')) {
            $action = $request->action ?? 'reset';
            if (isset($request->date_range) && $action !== 'reset') {

                $dates = explode(' to ', $request->date_range);
                if (count($dates) == 2) { // When both start and end dates are provided
                    $startDate = $dates[0] ?? date('Y-m-d');
                    $endDate = $dates[1] ?? date('Y-m-d');
                } elseif (count($dates) == 1) { // When only a single date is provided
                    $startDate = $dates[0] ?? date('Y-m-d');
                    $endDate = $startDate; // Use the same date for both start and end
                } else { // Default case, fallback to last 10 days
                    $startDate = Carbon::now()->startOfMonth()->toDateString();
                    $endDate = Carbon::now()->toDateString();
                }
            } else {
                $startDate = Carbon::now()->startOfMonth()->toDateString();
                $endDate = Carbon::now()->toDateString();
            }

            $date_range = $startDate . ' to ' . $endDate;

            $s_admin_revenue = Subscription::selectRaw('SUM(amount) as total_amount, MONTH(created_at) as month')
                ->whereYear('created_at', Carbon::now()->year)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('month')
                ->orderBy('month')
                ->get();


            foreach ($s_admin_revenue as $subscription) {
                $months[] = Carbon::create()->month($subscription->month)->format('F');
                $monthly_revenue[] = $subscription->total_amount;
            }
            $today = Carbon::today();
            $total_user_ids = User::role('admin')->pluck('id');

            $total_free_user_count = User::role('admin')->count();

            $total_repeat_user_count = Subscription::where('status', 'active')->whereDate('end_date', '>=', Carbon::today())
                ->whereJsonDoesntContain('plan_details->identifier', 'free')->count();

            // Calculate retention rate
            $retention_rate = ($total_free_user_count > 0)
                ? number_format(($total_repeat_user_count / $total_free_user_count) * 100, 2)
                : 0;

            // Fetch all required data in a single query to minimize database interactions
            $data = [
                'total_active_subscriptions' => Subscription::where('status', 'active')->whereDate('end_date', '>=', Carbon::today())
                    ->whereJsonDoesntContain('plan_details->identifier', 'free')->count(),
                'total_plans' => Plan::where('status', 1)->count(),  // Add this line

                'total_subscriptions_data' => Subscription::whereJsonDoesntContain('plan_details->identifier', 'free')->count(),

                'total_pending_subscriptions_data' => Payment::where('status', 0)
                    ->whereJsonDoesntContain('plan_details->identifier', 'free')
                    ->count(),

                'total_plans' => Plan::where('status', 1)->count(),  // Add this line

                'months' => $months,
                'retention_rate' => $retention_rate,
                'monthly_revenue' => $monthly_revenue ?? [],
                'total_revenue' => \Currency::format(
                    Payment::select(DB::raw('COALESCE(SUM(amount), 0) as total'))
                        ->where('status', 1)
                        ->first()
                        ->total
                ),
                'total_subscribers' => User::role('admin')->count(),
                'top_subscribers' => Subscription::select('user_id', DB::raw('SUM(amount) + COALESCE(SUM(tax_amount), 0) as total_amount'))
                    ->with('user')
                    ->groupBy('user_id')
                    ->havingRaw('SUM(amount) > 0')  // Exclude where total_amount is 0
                    ->where('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate) // Apply date range
                    ->whereHas('user', function ($query) {
                        $query->whereNull('deleted_at');  // Exclude deleted users
                    })
                    ->orderByDesc('total_amount')
                    ->limit(5)
                    ->get()
                    ->map(function ($subscriber) {
                        $subscriber->total_amount = \Currency::format($subscriber->total_amount);
                        return $subscriber;
                    }),
                'date_range' => $date_range,
                'recent_subscribers' => Subscription::with(['user', 'plan'])
                    ->whereHas('user', function ($q) {
                        $q->whereNull('deleted_at');
                    })
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(plan_details, '$.name')) != 'Free'")
                    ->orderBy('id', 'desc')
                    ->take(5)
                    ->get()
                    ->map(function ($subscription) {
                        $planDetails = json_decode($subscription->plan_details, true);

                        $durationSuffix = match ($planDetails['type']) {
                            'Monthly' => 'Month',
                            'Yearly' => 'Year',
                            'Weekly' => 'Week',
                            default => 'Day'
                        };

                        $status = match ($subscription->status) {
                            'paid' => 'active',
                            'cancel' => 'cancelled',
                            default => $subscription->status
                        };

                        return [
                            'id' => $subscription->id,
                            'user_name' => $subscription->user->getFullNameAttribute(),
                            'date' => $subscription->created_at->format('d M Y'),
                            'plan_name' => $planDetails['name'] ?? 'N/A',
                            'duration' => $planDetails['duration'] . ' ' . $durationSuffix,
                            'amount' => \Currency::format($subscription->total_amount),
                            'status' => $status
                        ];
                    }),
                'expiringSoon' => Subscription::where('status', 'active')
                    ->whereJsonDoesntContain('plan_details->identifier', 'free')
                    ->whereDate('end_date', '>', now())
                    ->whereDate('end_date', '<=', now()->addDays(7))
                    ->count(),
            ];

            return view('superadmin.dashboard')->with($data);
        }
        if (auth()->user()->hasRole('employee')) {
            return redirect(RouteServiceProvider::EMPLOYEE_LOGIN_REDIRECT);
        }
        $global_booking = false;
        $today = Carbon::today();
        $action = $request->action ?? 'reset';
        if (isset($request->date_range) && $action !== 'reset') {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $startDate = $dates[0] ?? date('Y-m-d');
                $endDate = $dates[1] ?? date('Y-m-d');
            } elseif (count($dates) == 1) {
                $startDate = $dates[0] ?? date('Y-m-d');
                $endDate = $startDate;
            } else {
                $startDate = Carbon::now()->startOfMonth()->toDateString();
                $endDate = Carbon::now()->toDateString();
            }
        } else {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->toDateString();
        }

        $date_range = $startDate . ' to ' . $endDate;
        $data = [
            'total_appointments' => 0,
            'total_commission' => 0,
            'total_revenue' => 0,
            'total_new_customers' => 0,
            'upcomming_appointments' => [],
            'top_services' => [],
            'revenue_chart' => [],
            'total_orders' => 0,
            'product_sales' => 0,
        ];

        $totalServices = BookingService::whereHas('booking', function ($query) use ($startDate, $endDate) {
            $query->where(function ($query) use ($startDate, $endDate) {
                $query->whereDate('start_date_time', '>=', $startDate)
                    ->whereDate('start_date_time', '<=', $endDate);
            })->where('status', 'completed')->whereHas('branch', function ($branchQuery) {
                $branchQuery->where('created_by', auth()->id());
            });
        });
        $data['total_appointments'] = Booking::where(function ($query) use ($startDate, $endDate) {
            $query->where(function ($query) use ($startDate, $endDate) {
                $query->whereDate('start_date_time', '>=', $startDate)
                    ->whereDate('start_date_time', '<=', $endDate);
            });
        })->where('status', 'completed')
            ->whereHas('branch', function ($query) {
                $query->where('created_by', auth()->id());
            })->count();

        $data['total_commission'] = Booking::with('commission')
            ->whereDate('start_date_time', '>=', $startDate)
            ->whereDate('start_date_time', '<=', $endDate)
            ->where('status', 'completed')
            ->whereHas('branch', function ($query) {
                $query->where('created_by', auth()->id());
            })->where('status', 'completed')->get();

        $data['total_commission'] = \Currency::format($data['total_commission']->sum(function ($booking) {
            return $booking->commission->commission_amount ?? 0;
        }));




        $bookings = BookingTransaction::with('booking')->whereHas('booking', function ($query) use ($startDate, $endDate) {
            $query->where(function ($query) use ($startDate, $endDate) {
                $query->whereDate('start_date_time', '>=', $startDate)
                    ->whereDate('start_date_time', '<=', $endDate)
                    ->where('status', 'completed');
            })->whereHas('branch', function ($branchQuery) {
                $branchQuery->where('created_by', auth()->id());
            });
        })->pluck('booking_id')->toArray();

        $totalServiceAmount = BookingService::whereIn('booking_id', $bookings)->sum('service_price');
        $totalPackageAmount = BookingPackages::whereIn('booking_id', $bookings)->sum('package_price');

        $data['total_revenue'] = \Currency::format($totalServiceAmount + $totalPackageAmount);


        // $data['total_new_customers'] = User::where('created_at', '>=', $startDate)
        //     ->whereDate('created_at', '<=', $endDate)
        //     ->whereHas('roles', function ($query) {
        //         $query->where('name', 'user');
        //     })
        //     ->count();
        $userId = auth()->id();

          $data['total_new_customers'] =User::role('user')->where('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->where(function ($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhereHas('booking.branch', function ($sub) use ($userId) {
                      $sub->where('created_by', $userId);
                  });
            })->count();

        $datetime = Carbon::now()->setTimezone(setting('default_time_zone') ?? 'UTC');


        $data['upcomming_appointments'] = Booking::with('branch', 'user', 'services')
            ->where('start_date_time', '>=', $datetime)->orderBy('start_date_time')
            ->whereHas('user', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->whereHas('branch', function ($q) {
                $q->whereNull('deleted_at')
                    ->where('created_by', auth()->id());
            })
            ->whereNotIn('status', ['completed', 'cancelled']) // Exclude both statuses
            ->take(10)
            ->get();

        $data['top_services'] = $totalServices->with('service')->select(
            'service_id',
            \DB::raw('COUNT(*) as total_service_count'),
            \DB::raw('SUM(service_price) as total_service_price')
        )
            ->groupBy('service_id')
            ->orderByDesc('total_service_price')
            ->limit(5)
            ->get();



      $chartBookingRevenue = Booking::select(
        'bookings.id',
        'bookings.start_date_time',
        'bookings.status',
        'bookings.branch_id',
        'bookings.created_by'
    )
    ->whereDate('bookings.start_date_time', '>=', $startDate)
    ->whereDate('bookings.start_date_time', '<=', $endDate)
    ->where(function ($query) {
        $query->where('bookings.created_by', auth()->id())
            ->orWhereHas('branch', function ($branchQuery) {
                $branchQuery->where('created_by', auth()->id());
            });
    })
    ->whereIn('bookings.status', ['pending', 'confirmed'])
    ->branch()
    ->get();


        $data['upcoming_chart']['xaxis'] = $chartBookingRevenue?->pluck('booking_date')->toArray() ?? [];
        $data['upcoming_chart']['total_bookings'] = $chartBookingRevenue?->pluck('total_booking')->toArray() ?? [];
        $data['upcoming_chart']['total_price'] = $chartBookingRevenue?->pluck('total_price')->toArray() ?? [];



        $chartBookingRevenue = Booking::select(
            \DB::raw('DATE(bookings.start_date_time) AS booking_date'),
            \DB::raw('SUM(booking_services.service_price) AS total_price'),
            \DB::raw('COUNT(DISTINCT booking_services.booking_id) AS total_booking')
        )
            ->leftJoin('booking_services', 'bookings.id', '=', 'booking_services.booking_id')
            ->whereDate('bookings.start_date_time', '>=', $startDate)
            ->whereDate('bookings.start_date_time', '<=', $endDate)
            ->where('status', 'completed')
            ->whereHas('branch', function ($query) {
                $query->where('created_by', auth()->id());
            })
            ->branch()
            ->groupBy(\DB::raw('DATE(bookings.start_date_time)'))
            ->get();

        $data['revenue_chart']['xaxis'] = $chartBookingRevenue?->pluck('booking_date')->toArray() ?? [];
        $data['revenue_chart']['total_bookings'] = $chartBookingRevenue?->pluck('total_booking')->toArray() ?? [];
        $data['revenue_chart']['total_price'] = $chartBookingRevenue?->pluck('total_price')->toArray() ?? [];

        $orders = Order::where(function ($q) {
            $q->orWhereIn('order_group_id', OrderGroup::pluck('id'));
        })->whereHas('orderItems', function ($query) {
            $query->whereHas('product_variation', function ($qry) {
                $qry->whereHas('product', function ($query) {
                    $query->where('created_by', auth()->id());
                });
            });
        });

        $data['total_orders'] = $orders->where('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)->count();

        $data['product_sales'] = \Currency::format(
            $orders->where('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)->sum('total_admin_earnings')
        );
        return view('backend.index', compact('data', 'date_range', 'global_booking'));
    }

    public function setCurrentBranch($branch_id)
    {
        request()->session()->forget('selected_branch');

        request()->session()->put('selected_branch', $branch_id);

        return redirect()->back()->with('success', __('messages.branch_changed'))->withInput();
    }

    public function resetBranch()
    {

        request()->session()->forget('selected_branch');

        request()->session()->put('selected_branch', 0);

        return redirect()->back()->with('success', __('messages.show_all_branch_content'))->withInput();
    }

    public function setUserSetting(Request $request)
    {
        auth()->user()->update(['user_setting' => $request->settings]);

        return response()->json(['status' => true]);
    }

    public function getCurrencySymbol(Request $request)
    {
        $currencyCode = strtoupper($request->currency);
        $currency = Currency::where('currency_code', $currencyCode)->first();
        $currencySymbol = $currency ? $currency->currency_symbol : '₹';  // Default to ₹ if not found

        return response()->json(['symbol' => $currencySymbol]);
    }


    public function getRevenuechartData(Request $request, $type)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        if ($type == 'Year') {

            $monthlyTotals = Payment::selectRaw('MONTH(updated_at) as month, SUM(amount) as total_amount')
                ->where('status', 1)
                ->whereYear('updated_at', $currentYear) // ✅ Only fetch data for the current year
                ->groupByRaw('MONTH(updated_at)')
                ->orderByRaw('MONTH(updated_at)')
                ->get();


            $chartData = [];

            for ($month = 1; $month <= 12; $month++) {
                $found = false;
                foreach ($monthlyTotals as $total) {
                    if ((int)$total->month === $month) {
                        $chartData[] = (float)$total->total_amount;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $chartData[] = 0;
                }
            };

            $category = [
                __('messages.january'),
                __('messages.february'),
                __('messages.march'),
                __('messages.april'),
                __('messages.may'),
                __('messages.june'),
                __('messages.july'),
                __('messages.august'),
                __('messages.september'),
                __('messages.october'),
                __('messages.november'),
                __('messages.december'),
            ];
        } else if ($type == 'Month') {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();


            $dailyTotals = SubscriptionTransactions::selectRaw('DAY(updated_at) as day, COALESCE(SUM(amount), 0) as total_amount')
                ->where('payment_status', 'paid')
                ->whereYear('updated_at', $currentYear)
                ->whereMonth('updated_at', $currentMonth)
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            $chartData = [];


            $weeksInMonth = ceil($endOfMonth->day / 7);


            for ($week = 1; $week <= $weeksInMonth; $week++) {
                $weekTotal = 0;
                $found = false;


                for ($day = ($week - 1) * 7 + 1; $day <= min($week * 7, $endOfMonth->day); $day++) {
                    foreach ($dailyTotals as $total) {
                        if ((int)$total->day === $day) {
                            $weekTotal += (float)$total->total_amount;
                            $found = true;
                        }
                    }
                }


                $chartData[] = $found ? $weekTotal : 0;
            }


            $category = [];
            for ($i = 1; $i <= $weeksInMonth; $i++) {
                $category[] = "Week " . $i;
            }
        } else if ($type == 'Week') {

            $currentWeekStartDate = Carbon::now()->startOfWeek();
            $lastDayOfWeek = Carbon::now()->endOfWeek();

            $weeklyDayTotals = SubscriptionTransactions::selectRaw('DAY(updated_at) as day, COALESCE(SUM(amount), 0) as total_amount')
                ->where('payment_status', 'paid')
                ->whereYear('updated_at', $currentYear)
                ->whereMonth('updated_at', $currentMonth)
                ->whereBetween('updated_at', [$currentWeekStartDate, $currentWeekStartDate->copy()->addDays(6)])
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            $chartData = [];

            for ($day =  $currentWeekStartDate; $day <= $lastDayOfWeek; $day->addDay()) {
                $found = false;

                foreach ($weeklyDayTotals as $total) {
                    if ((int)$total->day === $day->day) {
                        $chartData[] = (float)$total->total_amount;
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $chartData[] = 0;
                }
            };

            $category = [
                __('messages.monday'),
                __('messages.tuesday'),
                __('messages.wednesday'),
                __('messages.thursday'),
                __('messages.friday'),
                __('messages.saturday'),
                __('messages.sunday'),
            ];
        }

        $data = [

            'chartData' => $chartData,
            'category' => $category

        ];

        return response()->json(['data' => $data, 'status' => true]);
    }
}
