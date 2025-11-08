<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Currency;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\Request;
use Modules\Booking\Models\Booking;
use Modules\Earning\Models\EmployeeEarning;
use Modules\Product\Models\Order;
use Modules\Product\Models\OrderGroup;
use Yajra\DataTables\DataTables;
use DB;

class ReportsController extends Controller
{
    public function __construct()
    {
        // Page Title
        $this->module_title = __('messages.reports');

        // module name
        $this->module_name = 'reports';

        // module icon
        $this->module_icon = 'fa-solid fa-chart-line';

        view()->share([
            'module_icon' => $this->module_icon,
        ]);
    }

    public function daily_booking_report(Request $request)
    {
        $module_title = __('report.title_daily_report');

        $module_name = 'daily-booking-report';
        $export_import = true;
        $export_columns = [
            [
                'value' => 'date',
                'text' => __('report.lbl_date'),
            ],
            [
                'value' => 'total_booking',
                'text' => __('report.lbl_no_booking'),
            ],
            [
                'value' => 'total_service',
                'text' => __('report.lbl_no_services'),
            ],
            [
                'value' => 'total_service_amount',
                'text' => __('report.lbl_service_amount'),
            ],
            [
                'value' => 'total_tax_amount',
                'text' => __('report.lbl_tax_amt'),
            ],
            [
                'value' => 'total_tip_amount',
                'text' => __('report.lbl_tips_amt'),
            ],
            [
                'value' => 'total_amount',
                'text' => __('report.lbl_final_amt'),
            ],
        ];
        $export_url = route('backend.reports.daily-booking-report-review');

        return view('backend.reports.daily-booking-report', compact('module_title', 'module_name', 'export_import', 'export_columns', 'export_url'));
    }

    public function order_report(Request $request)
    {
        $module_title = __('order_report.title');

        $module_name = '.order-report';
        $export_import = true;
        $export_columns = [
            [
                'value' => 'order_code',
                'text' => __('messages.order_code'),
            ],
            [
                'value' => 'customer_name',
                'text' => __('booking.lbl_customer_name'),
            ],
            [
                'value' => 'placed_on',
                'text' => __('messages.placed_on'),
            ],
            [
                'value' => 'items',
                'text' => __('messages.items'),
            ],
            [
                'value' => 'payment',
                'text' => __('messages.payment'),
            ],
            [
                'value' => 'status',
                'text' => __('messages.status'),
            ],
            [
                'value' => 'total_admin_earnings',
                'text' => __('messages.total_amount'),
            ]

        ];
        $export_url = route('backend.reports.order_booking_report_review');

        $orders = Order::with('orderGroup', 'orderItems.product_variation.product');
        if (auth()->user()->hasRole('admin')) {
            $orders = $orders->whereHas('orderItems', function ($q) {
                $q->whereHas('product_variation', function ($qry) {
                    $qry->whereHas('product', function ($query) {
                        $query->where('created_by', auth()->user()->id);
                    });
                });
            });
        }

        $orders = $orders->get();

        // Only count paid orders in the total amount
        $totalAdminEarnings = $orders->where('payment_status', 'paid')->sum('total_admin_earnings');

        return view('backend.reports.order-report', compact('module_title', 'module_name', 'export_import', 'export_columns', 'export_url', 'totalAdminEarnings'));
    }

    public function order_report_index_data(Datatables $datatable, Request $request)
    {
        $orders = Order::with('orderGroup', 'orderItems.product_variation.product');
        if (auth()->user()->hasRole('admin')) {
            $orders = $orders->whereHas('orderItems', function ($q) {
                $q->whereHas('product_variation', function ($qry) {
                    $qry->whereHas('product', function ($query) {
                        $query->where('created_by', auth()->user()->id);
                    });
                });
            });
        }
        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['code'])) {
                $orders = $orders->where(function ($q) use ($filter) {
                    $orderGroup = OrderGroup::where('order_code', $filter['code'])->pluck('id');
                    $q->orWhereIn('order_group_id', $orderGroup);
                });
            }

            if (isset($filter['delivery_status'])) {
                $orders = $orders->where('delivery_status', $filter['delivery_status']);
            }

            if (isset($filter['payment_status'])) {
                $orders = $orders->where('payment_status', $filter['payment_status']);
            }
            if (isset($filter['order_date'][0])) {
                $startDate = $filter['order_date'][0];
                $endDate = $filter['order_date'][1] ?? null;

                if (isset($endDate)) {
                    $orders->whereDate('created_at', '>=', date('Y-m-d', strtotime($startDate)));
                    $orders->whereDate('created_at', '<=', date('Y-m-d', strtotime($endDate)));
                } else {
                    $orders->whereDate('created_at', date('Y-m-d', strtotime($startDate)));
                }
            }
        }

        $orders = $orders->where(function ($q) {
            $orderGroup = OrderGroup::pluck('id');
            $q->orWhereIn('order_group_id', $orderGroup);
        });

        return $datatable->eloquent($orders)
            ->addIndexColumn()
            ->editColumn('order_code', function ($data) {
                return setting('inv_prefix') . $data->orderGroup->order_code;
            })
            ->editColumn('customer_name', function ($data) {
                $Profile_image = optional($data->user)->profile_image ?? default_user_avatar();
                $name = optional($data->user)->full_name ?? default_user_name();
                $email = optional($data->user)->email ?? '--';
                return view('booking::backend.bookings.datatable.user_id', compact('Profile_image', 'name', 'email'));
            })
            ->addColumn('phone', function ($data) {
                return optional($data->user)->mobile ?? '-';
            })
            ->editColumn('placed_on', function ($data) {
                return formatDateOrTime($data->created_at, 'date');
            })
            ->editColumn('items', function ($data) {
                return $data->orderItems()->count();
            })
            ->addColumn('products', function ($data) {
                // Build distinct product names list
                $names = $data->orderItems
                    ->map(function ($item) {
                        return optional(optional($item->product_variation)->product)->name;
                    })
                    ->filter()
                    ->unique()
                    ->values();

                if ($names->count() <= 1) {
                    $first = $names->first();
                    return $first ? '<small class="badge bg-primary">'.e($first).'</small>' : '-';
                }

                // Multiple products -> return a trigger link; a global modal will render details on click
                $dataProducts = e(json_encode($names->values()->toArray()));
                return '<a href="#" class="badge bg-info text-white show-products" data-products="'.$dataProducts.'">'.__('messages.multiple').'</a>';
            })
            ->editColumn('payment', function ($data) {
                return view('product::backend.order.columns.payment_column', compact('data'));
            })
            ->editColumn('status', function ($data) {
                return view('product::backend.order.columns.status_column', compact('data'));
            })
            ->editColumn('total_admin_earnings', function ($data) {
                return Currency::format($data->total_admin_earnings);
            })
            ->filterColumn('customer_name', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('first_name', 'like', '%' . $keyword . '%');
                        $q->orWhere('last_name', 'like', '%' . $keyword . '%');
                        $q->orWhere('email', 'like', '%' . $keyword . '%');
                    });
                }
            })
            ->filterColumn('phone', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('mobile', 'like', '%' . $keyword . '%');
                    });
                }
            })
            ->editColumn('updated_at', function ($data) {
                $diff = Carbon::now()->diffInHours($data->updated_at);
                if ($diff < 25) {
                    return $data->updated_at->diffForHumans();
                } else {
                    return $data->updated_at->isoFormat('llll');
                }
            })
            ->orderColumns(['id'], '-:column $1')
            ->rawColumns(['phone','products','payment','status'])
            ->toJson();
    }

    // public function daily_booking_report_index_data(Datatables $datatable, Request $request)
    // {
    //     $query = Booking::dailyReport();

    //     if (auth()->user()->hasRole('admin')) {
    //         $query = $query->whereHas('branch', function ($q) {
    //             $q->where('created_by', auth()->id());
    //         });
    //                 // dd($query->get());
    //     }
    //     $data = $request->all();

    //     $filter = $request->filter;
    //     if (isset($filter['booking_date'])) {
    //         $bookingDates = explode(' to ', $filter['booking_date']);

    //         if (count($bookingDates) >= 2) {
    //             $startDate = date('Y-m-d 00:00:00', strtotime($bookingDates[0]));
    //             $endDate = date('Y-m-d 23:59:59', strtotime($bookingDates[1]));

    //             $query->where('bookings.start_date_time', '>=', $startDate)
    //                 ->where('bookings.start_date_time', '<=', $endDate);
    //         } elseif (count($bookingDates) === 1) {
    //             $singleDate = date('Y-m-d', strtotime($bookingDates[0]));
    //             $startDate = $singleDate . ' 00:00:00';
    //             $endDate = $singleDate . ' 23:59:59';
    //             $query->whereBetween('bookings.start_date_time', [$startDate, $endDate]);
    //         }
    //     }

    //     return $datatable->eloquent($query)
    //         ->editColumn('start_date_time', function ($data) {
    //             return formatDateOrTime($data->start_date_time);
    //         })
    //         ->editColumn('total_booking', function ($data) {
    //             return $data->total_booking;
    //         })
    //         ->editColumn('total_service', function ($data) {
    //             $totalServiceCount = $data->total_service ?? 0;
    //             $totalPackageCount = $data->total_package_count ?? 0;
    //             return $totalServiceCount + $totalPackageCount;
    //         })
    //         ->editColumn('total_service_amount', function ($data) {
    //             $totalServiceAmount = Booking::totalservice($data->total_tax_amount ?? 0, $data->total_tip_amount ?? 0)
    //                 ->whereDate('bookings.start_date_time', '=', $data->start_date_time)
    //                 ->first();

    //             return Currency::format($totalServiceAmount->total_service_amount ?? 0);
    //         })
    //         ->orderColumn('total_service_amount', function ($query, $order) {
    //             $query->orderByRaw(
    //                 'COALESCE(SUM(booking_services.service_price), 0)  ' . $order
    //             );
    //         })
    //         ->editColumn('total_tax_amount', function ($data) {
    //                 $totaltax = Booking::getTotalTaxAmountAttribute()
    //                     ->whereHas('branch', function ($q) {
    //                         $q->where('created_by', auth()->id());
    //                     })
    //                     ->whereDate('bookings.start_date_time', '=', $data->start_date_time)
    //                     ->first();

    //                 // Safely fetch the numeric column
    //                 $amount = $totaltax->total_tax_amount ?? 0;

    //                 return Currency::format($amount);
    //             })

    //         ->editColumn('total_tip_amount', function ($data) {
    //             $totalTipAmount = Booking::tipamount()
    //                 ->whereDate('bookings.start_date_time', '=', $data->start_date_time)
    //                 ->first();

    //             return Currency::format($totalTipAmount->total_tip_amount ?? 0);
    //         })
    //         ->editColumn('total_amount', function ($data) {
    //             $totalTipAmount = Booking::tipamount()
    //                 ->whereDate('bookings.start_date_time', '=', $data->start_date_time)
    //                 ->first();
    //             $totaltax = Booking::totaltax()
    //                     ->whereHas('branch', function ($q) {
    //                         $q->where('created_by', auth()->id());
    //                     })
    //                     ->whereDate('bookings.start_date_time', '=', $data->start_date_time)
    //                     ->first();

    //             $totalServiceAmount = Booking::totalservice($totaltax->total_tax_amount ?? 0, $totalTipAmount->total_tip_amount ?? 0)
    //                 ->whereHas('branch', function ($q) {
    //                     $q->where('created_by', auth()->id());
    //                 })
    //                 ->whereDate('bookings.start_date_time', '=', $data->start_date_time)
    //                 ->first();

    //             return Currency::format($totalServiceAmount->total_amount ?? 0);
    //         })
    //         ->addIndexColumn()
    //         ->rawColumns([])
    //         ->toJson();
    // }
    public function daily_booking_report_index_data(Datatables $datatable, Request $request)
    {
        $query = Booking::with(['services', 'packages', 'payment'])
            ->where('status', 'completed')
            ->whereHas('payment', function($q) {
                $q->where('payment_status', 1);
            });

        if (auth()->user()->hasRole('admin')) {
            $query->whereHas('branch', fn($q) => $q->where('created_by', auth()->id()));
        }

        if ($request->filled('filter.booking_date')) {
            $dates = explode(' to ', $request->filter['booking_date']);
            $start = date('Y-m-d 00:00:00', strtotime($dates[0]));
            $end = count($dates) > 1
                ? date('Y-m-d 23:59:59', strtotime($dates[1]))
                : date('Y-m-d 23:59:59', strtotime($dates[0]));
            $query->whereBetween('bookings.start_date_time', [$start, $end]);
        }



        $bookings = $query->get()->groupBy(fn($b) => Carbon::parse($b->start_date_time)->format('Y-m-d'));

        \Log::info('ReportsController::daily_booking_report_index_data - Total bookings found', [
            'total_bookings' => $bookings->flatten()->count(),
            'grouped_dates' => $bookings->keys()->toArray(),
        ]);

        $data = collect();
        foreach ($bookings as $date => $dailyBookings) {
            // Log individual booking tax amounts for debugging
            foreach ($dailyBookings as $booking) {
                \Log::info('ReportsController::daily_booking_report_index_data - Booking details', [
                    'booking_id' => $booking->id,
                    'date' => $date,
                    'has_payment' => $booking->payment ? 'yes' : 'no',
                    'payment_id' => $booking->payment->id ?? null,
                    'total_service_amount' => $booking->total_service_amount,
                    'total_tax_amount' => $booking->total_tax_amount,
                    'total_tip_amount' => $booking->total_tip_amount,
                    'grand_total_amount' => $booking->grand_total_amount,
                ]);
            }

            $totalTaxAmount = $dailyBookings->sum(fn($b) => $b->total_tax_amount);
            
            \Log::info('ReportsController::daily_booking_report_index_data - Daily totals', [
                'date' => $date,
                'total_booking' => $dailyBookings->count(),
                'total_service_amount' => $dailyBookings->sum(fn($b) => $b->total_service_amount),
                'total_tax_amount' => $totalTaxAmount,
                'total_tip_amount' => $dailyBookings->sum(fn($b) => $b->total_tip_amount),
                'grand_total_amount' => $dailyBookings->sum(fn($b) => $b->grand_total_amount),
            ]);

            $data->push((object)[
                'start_date_time'      => $date,
                'total_booking'        => $dailyBookings->count(),
                'total_service'        => $dailyBookings->sum(fn($b) => $b->services->count() + $b->packages->count()),
                'total_service_amount' => $dailyBookings->sum(fn($b) => $b->total_service_amount),
                'total_tax_amount'     => $totalTaxAmount,
                'total_tip_amount'     => $dailyBookings->sum(fn($b) => $b->total_tip_amount),
                'grand_total_amount'   => $dailyBookings->sum(fn($b) => $b->grand_total_amount),
            ]);
        }

        return Datatables::of($data)
            ->editColumn('start_date_time', fn($row) => formatDateOrTime($row->start_date_time))
            ->editColumn('total_service_amount', fn($row) => Currency::format($row->total_service_amount))
            ->editColumn('total_tip_amount', fn($row) => Currency::format($row->total_tip_amount))
            ->editColumn('total_tax_amount', fn($row) => Currency::format($row->total_tax_amount))
            ->editColumn('total_amount', fn($row) => Currency::format($row->grand_total_amount))
            ->addIndexColumn()
            ->toJson();
    }


    public function overall_booking_report(Request $request)
    {
        $module_title = __('report.title_overall_report');

        $module_name = 'overall-booking-report';
        $export_import = true;
        $export_columns = [
            [
                'value' => 'date',
                'text' => __('report.lbl_date'),
            ],
            [
                'value' => 'inv_id',
                'text' => __('report.lbl_inv_id'),
            ],
            [
                'value' => 'employee',
                'text' => __('report.lbl_staff'),
            ],
            [
                'value' => 'total_service',
                'text' => __('report.lbl_tot_service'),
            ],
            [
                'value' => 'total_service_amount',
                'text' => __('report.lbl_tot_service_amt'),
            ],
            [
                'value' => 'total_tax_amount',
                'text' => __('report.lbl_taxes'),
            ],
            [
                'value' => 'total_tip_amount',
                'text' => __('report.lbl_tips'),
            ],
            [
                'value' => 'total_amount',
                'text' => __('report.lbl_tot_amt'),
            ],
        ];
        $export_url = route('backend.reports.overall-booking-report-review');

        return view('backend.reports.overall-booking-report', compact('module_title', 'module_name', 'export_import', 'export_columns', 'export_url'));
    }

    public function overall_booking_report_index_data(Datatables $datatable, Request $request)
    {
        $query = Booking::overallReport();
        if (auth()->user()->hasRole('admin')) {
            $query = $query->whereHas('branch', function ($q) {
                $q->where('created_by', auth()->id());
            });
        }
        if ($request->has('booing_id')) {
            $query->where('bookings.id', $request->booing_id);
        }

        if ($request->has('date_range')) {
            $dateRange = explode(' to ', $request->date_range);
            if (isset($dateRange[1])) {
                $startDate = $dateRange[0] ?? date('Y-m-d');
                $endDate = $dateRange[1] ?? date('Y-m-d');
                $query->whereDate('start_date_time', '>=', $startDate)
                    ->whereDate('start_date_time', '<=', $endDate);
            }
        }

        $filter = $request->filter;

        $filter = $request->filter;
        if (isset($filter['booking_date'])) {
            $bookingDates = explode(' to ', $filter['booking_date']);

            if (count($bookingDates) >= 2) {
                $startDate = date('Y-m-d 00:00:00', strtotime($bookingDates[0]));
                $endDate = date('Y-m-d 23:59:59', strtotime($bookingDates[1]));

                $query->where('bookings.start_date_time', '>=', $startDate)
                    ->where('bookings.start_date_time', '<=', $endDate);
            } elseif (count($bookingDates) === 1) {
                $singleDate = date('Y-m-d', strtotime($bookingDates[0]));
                $startDate = $singleDate . ' 00:00:00';
                $endDate = $singleDate . ' 23:59:59';
                $query->whereBetween('bookings.start_date_time', [$startDate, $endDate]);
            }
        }

        if (isset($filter['employee_id'])) {
            $query->whereHas('services', function ($q) use ($filter) {
                $q->where('employee_id', $filter['employee_id']);
            });
        }


        return $datatable->eloquent($query)
            ->editColumn('start_date_time', function ($data) {
                return formatDateOrTime($data->start_date_time);
            })
            ->editColumn('id', function ($data) {
                return setting('booking_invoice_prifix') . $data->id;
            })
            ->editColumn('employee_id', function ($data) {
                $employee = optional($data->services->first()->employee);
                $Profile_image = $employee->profile_image ?? default_user_avatar();
                $name = $employee->full_name ?? default_user_name();
                $email = $employee->email ?? '--';

                return view('booking::backend.bookings.datatable.employee_id', compact('Profile_image', 'name', 'email'));
            })
            ->editColumn('total_service', function ($data) {
                return $data->total_service;
            })
            ->editColumn('total_service_amount', function ($data) {
                return Currency::format($data->total_service_amount ?? 0);
            })
            ->editColumn('total_tax_amount', function ($data) {
                // Load booking with payment to get accurate tax amount
                $booking = Booking::with('payment')->find($data->id);
                return Currency::format($booking ? $booking->total_tax_amount : ($data->total_tax_amount ?? 0));
            })
            ->editColumn('total_tip_amount', function ($data) {
                return Currency::format($data->total_tip_amount ?? 0);
            })
            ->editColumn('total_amount', function ($data) {
                // Use accessor to calculate total amount correctly (service + tax + tip)
                $booking = Booking::with(['payment', 'services', 'packages'])->find($data->id);
                if ($booking) {
                    // Use grand_total_amount accessor which includes service + tax + tip
                    return Currency::format($booking->grand_total_amount);
                }
                // Fallback to SQL calculated value
                return Currency::format($data->total_amount ?? 0);
            })
            ->orderColumn('employee_id', function ($query, $order) {
                $query->orderBy(new Expression('(SELECT employee_id FROM booking_services WHERE booking_id = bookings.id LIMIT 1)'), $order);
            }, 1)
            ->addIndexColumn()
            ->rawColumns([])
            ->toJson();
    }


    public function payout_report(Request $request)
    {
        $module_title = __('report.title_staff_report');

        $module_name = 'payout-report-review';
        $export_import = true;
        $export_columns = [
            [
                'value' => 'date',
                'text' => __('report.lbl_payment_date'),
            ],
            [
                'value' => 'employee',
                'text' => __('report.lbl_staff'),
            ],
            [
                'value' => 'commission_amount',
                'text' => __('report.lbl_commission_amt'),
            ],
            [
                'value' => 'tip_amount',
                'text' => __('report.lbl_tips_amt'),
            ],
            [
                'value' => 'payment_type',
                'text' => __('report.lbl_payment_type'),
            ],
            [
                'value' => 'total_pay',
                'text' => __('report.lbl_tot_pay'),
            ],
        ];
        $export_url = route('backend.reports.payout-report-review');

        return view('backend.reports.payout-report', compact('module_title', 'module_name', 'export_import', 'export_columns', 'export_url'));
    }

    public function payout_report_index_data(Datatables $datatable, Request $request)
    {
        $query = EmployeeEarning::with('employee');
        if (auth()->user()->hasRole('admin')) {
            $query = $query->whereHas('employee', function ($q) {
                $q->whereHas('mainBranch', function ($qry) {
                    $qry->where('created_by', auth()->id());
                });
            });
        }

        $filter = $request->filter;

        if (isset($filter['booking_date'])) {
            $bookingDates = explode(' to ', $filter['booking_date']);

            if (count($bookingDates) >= 2) {
                $startDate = date('Y-m-d 00:00:00', strtotime($bookingDates[0]));
                $endDate = date('Y-m-d 23:59:59', strtotime($bookingDates[1]));

                $query->where('payment_date', '>=', $startDate)
                    ->where('payment_date', '<=', $endDate);
            } elseif (count($bookingDates) === 1) {
                $singleDate = date('Y-m-d', strtotime($bookingDates[0]));
                $startDate = $singleDate . ' 00:00:00';
                $endDate = $singleDate . ' 23:59:59';
                $query->whereBetween('payment_date', [$startDate, $endDate]);
            }
        }

        if (isset($filter['employee_id'])) {
            $query->whereHas('employee', function ($q) use ($filter) {
                $q->where('employee_id', $filter['employee_id']);
            });
        }

        return $datatable->eloquent($query)
            ->editColumn('payment_date', function ($data) {
                return formatDateOrTime($data->payment_date, 'date') ?? '-';
            })
            ->editColumn('first_name', function ($data) {
                $Profile_image = optional($data->employee)->profile_image ?? default_user_avatar();
                $name = optional($data->employee)->full_name ?? default_user_name();
                $email = optional($data->employee)->email ?? '--';
                return view('booking::backend.bookings.datatable.employee_id', compact('Profile_image', 'name', 'email'));
            })
            ->editColumn('commission_amount', function ($data) {
                return Currency::format($data->commission_amount ?? 0);
            })
            ->editColumn('tip_amount', function ($data) {
                return Currency::format($data->tip_amount ?? 0);
            })
            ->editColumn('total_pay', function ($data) {
                return Currency::format($data->total_amount ?? 0);
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

            ->orderColumn('first_name', function ($query, $direction) {
                $query->leftJoin('users', 'users.id', '=', 'employee_id')
                    ->orderBy('users.first_name', $direction)
                    ->orderBy('users.last_name', $direction);
            })

            ->orderColumn('total_pay', function ($query, $order) {
                $query->orderBy(new Expression('(SELECT total_amount FROM users WHERE id = employee_id LIMIT 1)'), $order);
            }, 1)

            ->addIndexColumn()
            ->rawColumns([])
            ->orderColumns(['id'], '-:column $1')
            ->rawColumns(['products','payment','status'])
            ->toJson();
    }

    public function staff_report(Request $request)
    {
        $module_title = __('report.title_staff_service_report');

        $module_name = 'staff-report-review';
        $export_import = true;
        $export_columns = [
            [
                'value' => 'employee',
                'text' => __('report.lbl_staff'),
            ],
            [
                'value' => 'total_services',
                'text' => __('report.lbl_no_services'),
            ],
            [
                'value' => 'total_service_amount',
                'text' => __('report.lbl_tot_amt'),
            ],
            [
                'value' => 'total_commission_earn',
                'text' => __('report.lbl_commissions_earn'),
            ],
            [
                'value' => 'total_tip_earn',
                'text' => __('report.lbl_tips_earn'),
            ],
            [
                'value' => 'total_earning',
                'text' => __('report.lbl_total_earning'),
            ],
        ];
        $export_url = route('backend.reports.staff-report-review');

        return view('backend.reports.staff-report', compact('module_title', 'module_name', 'export_import', 'export_columns', 'export_url'));
    }

    public function staff_report_index_data(Datatables $datatable, Request $request)
    {
        // Debug logging
        \Log::info('🔍 Staff Report Debug', [
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->getRoleNames()->toArray(),
            'is_admin' => auth()->user()->hasRole('admin'),
            'filter' => $request->filter ?? null,
        ]);
        
        $query = User::staffReportWithCommissionStatus();
        
        // Debug: Check if user 31 passes the initial query
        $testUser31BeforeFilter = User::role(['manager', 'employee'])->where('id', 31)->first();
        \Log::info('🔍 User 31 Before Filter Check', [
            'exists' => $testUser31BeforeFilter ? true : false,
            'has_role_manager' => $testUser31BeforeFilter ? $testUser31BeforeFilter->hasRole('manager') : false,
            'has_role_employee' => $testUser31BeforeFilter ? $testUser31BeforeFilter->hasRole('employee') : false,
        ]);
        
        if (auth()->user()->hasRole('admin')) {
            // Filter users who either:
            // 1. Have mainBranch created by vendor, OR
            // 2. Have bookings in branches created by vendor
            $query = $query->where(function($q) {
                $q->whereHas('mainBranch', function ($subQ) {
                    $subQ->where('created_by', auth()->id());
                })
                ->orWhereHas('employeeBooking', function ($bookingQ) {
                    $bookingQ->whereHas('booking.branch', function ($branchQ) {
                        $branchQ->where('created_by', auth()->id());
                    });
                })
                ->orWhereHas('bookingPackages', function ($packageQ) {
                    $packageQ->whereHas('booking.branch', function ($branchQ) {
                        $branchQ->where('created_by', auth()->id());
                    });
                });
            });
            
            // Debug: Check if user 31 passes the filter
            $testUser31AfterFilter = clone $query;
            $testUser31Result = $testUser31AfterFilter->where('users.id', 31)->first();
            \Log::info('🔍 User 31 After Filter Check', [
                'exists' => $testUser31Result ? true : false,
                'employee_booking_count' => $testUser31Result->employee_booking_count ?? null,
            ]);
        }
        
        // Log sample data before filtering
        $sampleQuery = clone $query;
        $sampleData = $sampleQuery->limit(3)->get();
        \Log::info('📊 Sample Staff Data (before filter)', [
            'count' => $sampleData->count(),
            'sample' => $sampleData->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'employee_booking_count' => $user->employee_booking_count ?? 0,
                    'booking_packages_count' => $user->booking_packages_count ?? 0,
                    'employee_booking_sum_service_price' => $user->employee_booking_sum_service_price ?? 0,
                    'booking_packages_sum_package_price' => $user->booking_packages_sum_package_price ?? 0,
                ];
            })->toArray(),
        ]);
        
        // Check if user 31 is in the full query
        $allUsers = clone $query;
        $allUserIds = $allUsers->pluck('id')->toArray();
        $user31InResults = in_array(31, $allUserIds);
        \Log::info('🔍 User 31 In Full Query Check', [
            'user_31_in_results' => $user31InResults,
            'total_users_count' => count($allUserIds),
            'first_10_user_ids' => array_slice($allUserIds, 0, 10),
        ]);
        
        $filter = $request->filter;

        if (isset($filter['employee_id'])) {
            $query->where('id', $filter['employee_id']);
        }
        
        return $datatable->eloquent($query)

            ->editColumn('first_name', function ($data) {
                $Profile_image = optional($data)->profile_image ?? default_user_avatar();
                $name = optional($data)->full_name ?? default_user_name();
                $email = optional($data)->email ?? '--';
                return view('booking::backend.bookings.datatable.employee_id', compact('Profile_image', 'name', 'email'));
            })
            ->orderColumn('first_name', function ($query, $order) {
                $query->orderBy('users.first_name', $order) // Ordering by first name
                    ->orderBy('users.last_name', $order); // Ordering by first name
            }, 1)
            ->editColumn('total_services', function ($data) {
                $totalServices = ($data->employee_booking_count ?? 0) + ($data->booking_packages_count ?? 0);
                \Log::info('📊 Total Services Calculation', [
                    'user_id' => $data->id,
                    'user_name' => $data->full_name ?? 'Unknown',
                    'employee_booking_count' => $data->employee_booking_count ?? 0,
                    'booking_packages_count' => $data->booking_packages_count ?? 0,
                    'total_services' => $totalServices,
                ]);
                return $totalServices;
            })
            ->editColumn('total_service_amount', function ($data) {
                $serviceSum = $data->employee_booking_sum_service_price ?? 0;
                $packageSum = $data->booking_packages_sum_package_price ?? 0;
                $totalAmount = $serviceSum + $packageSum;
                \Log::info('💰 Total Service Amount Calculation', [
                    'user_id' => $data->id,
                    'user_name' => $data->full_name ?? 'Unknown',
                    'employee_booking_sum_service_price' => $serviceSum,
                    'booking_packages_sum_package_price' => $packageSum,
                    'total_amount' => $totalAmount,
                ]);
                return Currency::format($totalAmount);
            })
            ->editColumn('total_commission_earn', function ($data) {
                return Currency::format($data->commission_earning_sum_commission_amount ?? 0);
            })
            ->editColumn('total_tip_earn', function ($data) {
                $tipAmount = calculateEmployeeTipTotal($data->id);
                return Currency::format($tipAmount ?? 0);
            })
           ->editColumn('total_earning', function ($data) {
                return Currency::format($data->commission_earning_sum_commission_amount +  calculateEmployeeTipTotal($data->id) );
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
            ->orderColumn('total_services', function ($data, $order) {
                $data->selectRaw('(SELECT COUNT(service_id) FROM booking_services WHERE employee_id = users.id) as total_services')
                    ->orderBy('total_services', $order);
            })

            ->orderColumn('total_service_amount', function ($data, $order) {
                $data->selectRaw('(SELECT SUM(service_price) FROM booking_services WHERE employee_id = users.id) as total_service_amount')
                    ->orderBy('total_service_amount', $order);
            })

            ->orderColumn('total_service_amount', function ($data, $order) {
                $data->selectRaw('(SELECT SUM(service_price) FROM booking_services WHERE employee_id = users.id) as total_service_amount')
                    ->orderBy('total_service_amount', $order);
            })

            ->orderColumn('total_commission_earn', function ($data, $order) {
                $data->selectRaw('(SELECT SUM(commission_amount) FROM commission_earnings WHERE employee_id = users.id) as total_commission_earn')
                    ->orderBy('total_commission_earn', $order);
            })

            ->orderColumn('total_tip_earn', function ($data, $order) {
                $data->selectRaw('(SELECT SUM(tip_amount) FROM tip_earnings WHERE employee_id = users.id) as total_tip_earn')
                    ->orderBy('total_tip_earn', $order);
            })

            ->orderColumn('total_earning', function ($data, $order) {
                $data->selectRaw('(SELECT SUM(service_price) FROM booking_services WHERE employee_id = users.id) as total_earning')
                    ->orderBy('total_earning', $order);
            })

            ->addIndexColumn()
            ->rawColumns([])
            ->orderColumns(['id'], '-:column $1')
            ->toJson();
    }

    public function daily_booking_report_review(Request $request)
    {
        $this->exportClass = '\App\Exports\DailyReportsExport';

        return $this->export($request);
    }

    public function overall_booking_report_review(Request $request)
    {
        $this->exportClass = '\App\Exports\OverallReportsExport';

        return $this->export($request);
    }

    public function payout_report_review(Request $request)
    {
        $this->exportClass = '\App\Exports\StaffPayoutReportExport';

        return $this->export($request);
    }

    public function staff_report_review(Request $request)
    {
        $this->exportClass = '\App\Exports\StaffServiceReportExport';

        return $this->export($request);
    }
    public function order_booking_report_review(Request $request)
    {
        $this->exportClass = '\App\Exports\OrderReportsExport';

        return $this->export($request);
    }
}
