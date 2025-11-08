<?php

namespace Modules\VendorWebsite\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Category\Models\Category;
use Yajra\DataTables\Facades\DataTables; // use Yajra DataTables

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

       if(checkVendorMenuPermission('category','header-menu-setting')){

            $query = Category::whereNull('parent_id')->orwhere('parent_id',0)->where('status', 1)->where('created_by', session('current_vendor_id'));

            if (request()->has('search')) {
                $searchTerm = request()->get('search');
                $query->where('name', 'LIKE', "%{$searchTerm}%");
            }

            $categories = $query->paginate(10);

            if (request()->ajax()) {
                return view('vendorwebsite::category', compact('categories'))->render();
            }

            return view('vendorwebsite::category', compact('categories'));

        }else{

            abort(403);
        }



    }



    public function categoriesData(Request $request)
    {
        $query = Category::whereNull('parent_id')->orWhere('parent_id', 0)->where('created_by', session('current_vendor_id'))->where('status', 1);

        return DataTables::of($query)
            ->addColumn('card', function ($category) {
                return view('vendorwebsite::components.card.category_card', compact('category'))->render();
            })
            ->addColumn('name', function ($category) {
                return $category->name;
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->rawColumns(['card'])
            ->make(true);
    }

    public function SubCategory()
    {
        return view('vendorwebsite::subcategory');
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
        return view('vendorwebsite::show');
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
}
