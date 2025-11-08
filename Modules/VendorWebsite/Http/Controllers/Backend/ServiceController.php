<?php

namespace Modules\VendorWebsite\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DataTables;
use Modules\Service\Models\Service; // Correct Service Model
use Modules\Category\Models\Category; // Include Category Model if needed for future logic
use Illuminate\Support\Facades\View;
use Modules\Employee\Models\BranchEmployee;
use Modules\Employee\Models\Branch;
use Modules\Subscriptions\Models\Subscription;
use Modules\Booking\Models\Booking;
use DB;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index($category = null)
    {

        if(checkVendorMenuPermission('service','header-menu-setting')){

        $category = $category;

        $vendorId = session('current_vendor_id');

        $booking_limit=0;

        $subscription = Subscription::where('user_id', $vendorId)->where('status', 'active')->where('end_date', '>', now())->orderBy('id', 'desc')->first();

        if($subscription){
            $booking_limit = $subscription->plan->max_appointment ?? 0;
        }

        $total_booking_count= Booking::whereHas('branch', function ($query) {
            $query->where('branch_id', session('selected_branch_id'));
        })->count();


        if (session()->has('selected_branch_id')) {
            $categories = Category::where('status', 1)
                ->where(function ($q) {
                    $q->whereNull('parent_id')->orWhere('parent_id', 0);
                })
                ->withCount(['services' => function ($query) {
                    $query->whereHas('branchServices', function ($q) {
                        $q->where('branch_id', session('selected_branch_id'));
                    });
                }])
                ->whereHas('services', function ($query) {
                    $query->whereHas('branchServices', function ($q) {
                        $q->where('branch_id', session('selected_branch_id'));
                    });
                })
                ->orderBy('services_count', 'desc')
                ->get();
        } else {
            $categories = Category::where('status', 1)
                ->where(function ($q) {
                    $q->whereNull('parent_id')->orWhere('parent_id', 0);
                })
                ->withCount('services')
                ->orderBy('services_count', 'desc')
                ->get();
        }

        // Remove it from the list if it already exists
        $categories = $categories->reject(function ($cat) use ($category) {
            return $cat->slug === $category;
        })->unique('id')->values();

        if (session('selected_branch_id')) {
            $selected = Category::where('slug', $category)
                ->where('status', 1)
                ->where(function ($q) {
                    $q->whereNull('parent_id')->orWhere('parent_id', 0);
                })
                ->where('created_by', session('current_vendor_id'))
                ->withCount(['services' => function ($query) {
                    $query->whereHas('branchServices', function ($q) {
                        $q->where('branch_id', session('selected_branch_id'));
                    });
                }])
                ->get();
        } else {
            $selected = Category::where('slug', $category)
                ->where('status', 1)
                ->where(function ($q) {
                    $q->whereNull('parent_id')->orWhere('parent_id', 0);
                })
                ->where('created_by', session('current_vendor_id'))
                ->withCount('services')
                ->get();
        }

        // Insert at second position
        foreach ($selected as $selected) {
            $categories->prepend($selected);
        }
        // if ($selected) {
        //     $categories->prepend($selected);
        // }

        $query = Service::query()
            ->with(['employee', 'branches'])
            ->where('status', 1)
            ->whereHas('employee');

        if (session()->has('selected_branch_id')) {
            $branchId = session('selected_branch_id');

            $barnchEmployee = BranchEmployee::where('branch_id', $branchId)->get();

            $query->whereHas('employee', function ($query) use ($barnchEmployee) {
                $query->whereIn('employee_id', $barnchEmployee->pluck('employee_id'));
            });
        }

        if (session('selected_branch_id')) {
            $allServicesCount = Service::where('status', 1)->whereHas('branchServices', function ($q) {
                $q->where('branch_id', session('selected_branch_id'));
            })->count();
        } else {

            $allServicesCount = Service::where('status', 1)->count();
        }


        return view('vendorwebsite::service', compact('categories', 'allServicesCount', 'category','booking_limit','total_booking_count'));
    }else{

        abort(403);
    }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('vendorwebsite::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        $services = $branch->services; // or your actual relationship
        return view('vendorwebsite::branch-details', compact('branch', 'services'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('vendorwebsite::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function getServiceCardsData(Request $request)
    {
        // $query = Service::query()
        //     ->select('id', 'name', 'category_id', 'default_price', 'description', 'duration_min') // Added duration_min
        //     ->where('status', 1)
        //     ->withCount(['employee as staff_count', 'branches as branch_count']); // Eager load counts

        $branch_id = session('selected_branch_id');


        if ($branch_id) {
            $employee_id = BranchEmployee::where('branch_id', $branch_id)
                ->pluck('employee_id')
                ->toArray();
        } else {
            $employee_id = BranchEmployee::pluck('employee_id')->toArray();
        }

        if (session('selected_branch_id')) {

            $query = Service::with(['branchServices' => function ($query) {
                $query->where('branch_id', session('selected_branch_id'))->limit(1);
            }])->whereHas('branchServices', function ($query) {
                $query->where('branch_id', session('selected_branch_id'));
            })
                ->select('id', 'name', 'category_id', 'default_price', 'description', 'duration_min')
                ->where('status', 1)
                ->withCount([
                    'employee as staff_count' => function ($q) use ($employee_id) {
                        $q->whereIn('employee_id', $employee_id);
                    },
                    'branches as branch_count'
                ]);
        } else {

            $query = Service::query()
                ->select('id', 'name', 'category_id', 'default_price', 'description', 'duration_min')
                ->where('status', 1)
                ->withCount([
                    'employee as staff_count' => function ($q) use ($employee_id) {
                        $q->whereIn('employee_id', $employee_id);
                    },
                    'branches as branch_count'
                ]);
        }



        // Apply category filter if provided
        if ($request->has('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        if (session()->has('selected_branch_id')) {
            $branchId = session('selected_branch_id');

            $barnchEmployee = BranchEmployee::where('branch_id', $branchId)->get();

            // $query->whereHas('employee', function ($query) use ($barnchEmployee) {
            //     $query->whereIn('employee_id', $barnchEmployee->pluck('employee_id'));
            // });
        }

        // Apply search
        if ($request->has('search') && !empty($request->search)) {
            $searchValue = $request->search;
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('description', 'like', '%' . $searchValue . '%')
                    ->orWhereHas('category', function ($q) use ($searchValue) {
                        $q->where('name', 'like', '%' . $searchValue . '%');
                    });
            });
        }

        // Apply sort filter
        if ($request->has('sort_filter') && !empty($request->sort_filter)) {
            switch ($request->sort_filter) {
                case 'newest':
                    // Only show services from last 10 days
                    $query->whereDate('created_at', '>=', now()->subDays(10))
                        ->orderBy('created_at', 'desc');
                    break;
                case 'trending':
                    $query->where('name', '>=', 10);
                    $query->orderBy('name', 'desc');
                    break;
            }
        }

        // Log the query here before DataTables processes it


        return DataTables::of($query)
            ->addColumn('card', function ($service) {
                // Add 'price' attribute to the service object before passing to view
                $service->price = $service->default_price;

                // Add duration, staff, and branch info for display
                $service->duration_text = $service->duration_min ? $service->duration_min . ' Minutes' : null;
                $service->staff_info = $service->staff_count > 0 ? $service->staff_count . '+ Staff' : null;
                $service->branch_info = $service->branch_count > 0 ? $service->branch_count . ' Branch(es)' : null;

                return View::make('vendorwebsite::components.card.service_card', compact('service'))->render();
            })
            ->rawColumns(['card'])
            ->make(true);
    }
}
