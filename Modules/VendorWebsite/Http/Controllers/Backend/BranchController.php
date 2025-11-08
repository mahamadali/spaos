<?php

namespace Modules\VendorWebsite\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;
use App\Models\Branch;
use Modules\Employee\Models\EmployeeRating;
use Modules\Employee\Models\BranchEmployee;
use Yajra\DataTables\DataTables;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::all();
        return view('vendorwebsite::branch', compact('branches'));
    }

    /**
     * Handle branch selection via AJAX
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectBranch(Request $request)
    {
        \Log::info('Branch selection request received', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->method()
        ]);

        $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);

        try {
            Session::put('selected_branch_id', $request->branch_id);

            Session::put('selected_branch', $request->branch_id);

            return response()->json([
                'success' => true,
                'message' => 'Branch selected successfully',
                'branch_id' => $request->branch_id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to select branch: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function BranchDetail($id, Request $request)
    {
        $branch = Branch::with([
            'address',
            'branchEmployee.employee',
            'gallerys',
            'businessHours',
            'services'
        ])->findOrFail($id);

        // Get ratings for this branch through employees
        $employeeIds = BranchEmployee::where('branch_id', $id)->pluck('employee_id');
        $branchRatings = EmployeeRating::whereIn('employee_id', $employeeIds)->with('user')->orderBy('updated_at', 'desc');
        $employeeIds = BranchEmployee::where('branch_id', $id)
            ->distinct()
            ->pluck('employee_id');

        $branch->averageRating = EmployeeRating::whereIn('employee_id', $employeeIds)->avg('rating');
        $branch->totalReviews = EmployeeRating::whereIn('employee_id', $employeeIds)->count();


        $perPage = 5;

        $branch->branchRatings = $branchRatings->paginate($perPage, ['*'], 'ratings_page');

        $services = $branch->services()->where('status', 1)->paginate($perPage, ['*'], 'services_page');

        // Get employee IDs for this branch
        $employeeIds = BranchEmployee::where('branch_id', $id)->pluck('employee_id');

        $reviews = collect();
        if ($employeeIds->count() > 0) {
            $reviews = EmployeeRating::with('user')
                ->whereIn('employee_id', $employeeIds)
                ->orderBy('updated_at', 'desc')
                ->paginate($perPage);
        }

        return view('vendorwebsite::branch-details', compact('branch', 'services', 'reviews'));
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
    public function show($id)
    {
        $branch = Branch::findOrFail($id); // Fetch by ID or fail
        return view('vendorwebsite::branch-details', compact('branch'));
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

    // public function index1()
    // {
    //      $branches = Branch::all();
    //     return view('vendorwebsite::branch', compact('branches'));
    // }

    // /**
    //  * Display the details of a single branch.
    //  */
    // public function show1($id)
    // {
    //     $branch = Branch::findOrFail($id); // Fetch by ID or fail
    //     return view('vendorwebsite::branch-details', compact('branch'));
    // }

    /**
     * Yajra DataTable for branches (4 per page)
     */
    public function branchesData(Request $request)
    {
        $query = Branch::query();
        return DataTables::of($query)
            ->addColumn('card', function ($branch) {
                return view('vendorwebsite::components.card.branch_card', compact('branch'))->render();
            })
            ->addColumn('name', function ($branch) {
                return $branch->name;
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->rawColumns(['card'])
            ->make(true);
    }
}
