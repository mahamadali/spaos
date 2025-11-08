<?php

namespace Modules\Product\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Modules\Product\Http\Requests\CategoryRequest;
use Modules\Product\Models\ProductCategory;
use Modules\Product\Models\ProductCategoryBrand;
use Modules\Product\Models\ProductCategoryMapping;
use Yajra\DataTables\DataTables;

class CategoryController extends Controller
{
    protected string $exportClass = '\App\Exports\CategoryExport';

    public function __construct()
    {
        // Page Title
        $this->module_title = __('category.title');

        // module name
        $this->module_name = 'products-categories';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => 'fa-regular fa-sun',
            'module_name' => $this->module_name,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $module_title = __('category.title');
        $module_name = $this->module_name;
        $module_action = __('messages.list');
        $columns = CustomFieldGroup::columnJsonValues(new ProductCategory());
        $customefield = CustomField::exportCustomFields(new ProductCategory());

        $export_import = true;
        $export_columns = [
            [
                'value' => 'name',
                'text' => __('messages.name'),
            ],
            [
                'value' => 'status',
                'text' => __('messages.status'),
            ],
        ];
        $export_url = route('backend.products-categories.export');

        return view('product::backend.category.index_datatable', compact('module_name', 'filter', 'module_action', 'columns', 'customefield', 'export_import', 'export_columns', 'export_url', 'module_title'));
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $term = trim($request->q);
        $parentId = $request->parent_id;
        $brandId = $request->brand_id;

        // \Log::info('CategoryController index_list called', [
        //     'request_params' => $request->all(),
        //     'user_id' => auth()->user()->id,
        //     'user_roles' => auth()->user()->getRoleNames()->toArray(),
        //     'term' => $term,
        //     'parentId' => $parentId,
        //     'brandId' => $brandId
        // ]);

        $query_data = ProductCategory::Active();

        // Apply search term filter
        if (! empty($term)) {
            $query_data->where('name', 'LIKE', "%$term%");
        }

        // Apply parent_id filter
        if (isset($parentId) && $parentId != 0) {
            $query_data->where('parent_id', $parentId);
        } elseif (isset($parentId) && $parentId == 0) {
            $query_data->whereNull('parent_id');
        }

        // Apply brand filter
        if (isset($brandId) && $brandId !== 'undefined' && ! empty($brandId)) {
            if (isset($parentId)) {
                // When requesting children for a parent, filter children by brand
                $query_data->whereHas('brands', function ($q1) use ($brandId) {
                    $q1->where('brand_id', $brandId);
                });
            } else {
                // For the main category dropdown: show only top-level categories relevant to this brand.
                // Include parents directly mapped to brand OR parents that have at least one child mapped to brand.
                $query_data->where(function ($q) use ($brandId) {
                    $q->where(function ($q1) use ($brandId) {
                        $q1->whereNull('parent_id')
                           ->whereHas('brands', function ($qq) use ($brandId) { $qq->where('brand_id', $brandId); });
                    })
                    ->orWhere(function ($q2) use ($brandId) {
                        $q2->whereNull('parent_id')
                           ->whereHas('children', function ($qc) use ($brandId) {
                               $qc->whereHas('brands', function ($qq) use ($brandId) { $qq->where('brand_id', $brandId); });
                           });
                    });
                });
            }
        }
        // When no brand_id is provided, show all categories (parent and child) - no additional filter

        $query_data->where('status', 1);

        // Only filter by created_by for regular admin users, not super admin
        if (auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super admin')) {
            $query_data->where('created_by', auth()->user()->id);
        }
        
        $query_data = $query_data->get();
        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'name' => $row->name,
            ];
        }

        // \Log::info('CategoryController index_list result', [
        //     'categories_count' => count($data),
        //     'categories' => $data
        // ]);

        return response()->json($data);
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                // If status is being turned off, remove sub-categories from products
                if ($request->status == 0) {
                    // Get all sub-categories (categories with parent_id) being updated
                    $subCategories = ProductCategory::whereIn('id', $ids)
                        ->whereNotNull('parent_id')
                        ->pluck('id');
                    
                    // Remove these sub-categories from all products
                    if ($subCategories->isNotEmpty()) {
                        ProductCategoryMapping::whereIn('category_id', $subCategories)->delete();
                    }
                }
                
                $branches = ProductCategory::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_category_update');
                break;

            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }

                ProductCategory::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_category_update');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('branch.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => __('messages.bulk_update')]);
    }

    public function update_status(Request $request, ProductCategory $id)
    {
        // Check if this is a sub-category (has parent_id) and status is being turned off
        if ($id->parent_id && $request->status == 0) {
            // Remove this sub-category from all products
            ProductCategoryMapping::where('category_id', $id->id)->delete();
        }
        
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('branch.status_update')]);
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $edit_permission = 'edit_categories';
        $delete_permission = 'delete_categories';
        $module_name = $this->module_name;
        $query = ProductCategory::query()->with(['media', 'brands'])->whereNull('parent_id');
        if (auth()->user()->hasRole('admin')) {
            $query->where('created_by', auth()->user()->id);
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

            ->editColumn('name', function ($row) use ($module_name) {
                $link = route('backend.' . $module_name . '.index_nested', ['category_id' => $row->id]);
                $data = $row;
                $image = optional($data)->feature_image ?? default_user_avatar();
                $name = optional($data)->name ?? default_user_name();
                return view('product::backend.category.category_id', compact('image', 'link', 'name'));
            })
            ->addColumn('brand_id', function ($data) {
                if (count($data->brands) == 0) {
                    return '<span class="text-muted">No brands</span>';
                }
                
                $brandsHtml = '';
                $brands = $data->brands;
                $totalBrands = count($brands);
                
                // Show first 3 brands as tags
                $displayBrands = $brands->take(3);
                foreach ($displayBrands as $brand) {
                    $brandsHtml .= '<span class="badge bg-primary me-1 mb-1" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">' . $brand->name . '</span>';
                }
                
                // If there are more than 3 brands, add a "more" button
                if ($totalBrands > 3) {
                    $remainingCount = $totalBrands - 3;
                    $brandsHtml .= '<button type="button" class="btn btn-sm btn-outline-primary ms-1" 
                                    style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#brandModal"
                                    data-category-id="' . $data->id . '"
                                    data-category-name="' . htmlspecialchars($data->name, ENT_QUOTES, 'UTF-8') . '"
                                    data-brands=\'' . htmlspecialchars($brands->toJson(), ENT_QUOTES, 'UTF-8') . '\'
                                    onclick="showAllBrandsFromButton(this)">
                                    +' . $remainingCount . ' more
                                </button>';
                }
                
                return $brandsHtml;
            })
            ->addColumn('action', function ($data) use ($module_name, $edit_permission, $delete_permission) {
                return view('product::backend.category.action_column', compact('module_name', 'data', 'edit_permission', 'delete_permission'));
            })
            ->addColumn('image', function ($data) {
                return "<img src='" . $data->feature_image . "' class='avatar avatar-50 rounded-pill'>";
            })
            ->editColumn('status', function ($row) {
                $checked = '';
                if ($row->status) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="' . route('backend.products-categories.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $row->id . '"  name="status" value="' . $row->id . '" ' . $checked . '>
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
            ->editColumn('created_at', function ($data) {
                $module_name = $this->module_name;

                $diff = Carbon::now()->diffInHours($data->created_at);

                if ($diff < 25) {
                    return $data->created_at->diffForHumans();
                } else {
                    return $data->created_at->isoFormat('llll');
                }
            })
            ->orderColumns(['id'], '-:column $1');

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatable, ProductCategory::CUSTOM_FIELD_MODEL, null);

        return $datatable->rawColumns(array_merge(['action', 'status', 'image', 'check', 'name', 'brand_id'], $customFieldColumns))
            ->toJson();
    }

    public function index_nested(Request $request)
    {
        $query = ProductCategory::with('mainCategory')->whereNull('parent_id')->where('status', 1);

        if (!auth()->user()->hasRole('admin')) {
            $query->where('created_by', auth()->id());
        }

        $categories = $query->where('created_by', auth()->id())->get();

        $filter = [
            'status' => $request->status,
        ];
        $parentID = $request->category_id ?? null;

        $module_action = __('messages.list');

        $module_title = __('category.sub_categories');
        $columns = CustomFieldGroup::columnJsonValues(new ProductCategory());
        $customefield = CustomField::exportCustomFields(new ProductCategory());

        $export_import = true;
        $export_columns = [
            [
                'value' => 'name',
                'text' => __('messages.name'),
            ],
            [
                'value' => 'category_name',
                'text' => __('messages.category_name'),
            ],
            [
                'value' => 'status',
                'text' => __('messages.status'),
            ],
        ];
        $export_url = route('backend.products-sub-categories.export');

        return view('product::backend.category.index_nested_datatable', compact('parentID', 'module_action', 'filter', 'categories', 'module_title', 'columns', 'customefield', 'export_import', 'export_columns', 'export_url'));
    }

    public function index_nested_data(Request $request, Datatables $datatable)
    {
        $edit_permission = 'edit_subcategories';
        $delete_permission = 'delete_subcategories';
        $module_name = $this->module_name;
        $query = ProductCategory::query()->with('media', 'mainCategory')->whereNotNull('parent_id');

        if (auth()->user()->hasRole('admin')) {
            $query->where('created_by', auth()->user()->id);
        }
        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
            // Removed default status filter - show all sub-categories regardless of status
            if (isset($filter['column_category'])) {
                $query->where('parent_id', $filter['column_category']);
            }
        }
        // Removed default status filter - show all sub-categories regardless of status

        $datatable = $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($data) use ($module_name, $edit_permission, $delete_permission) {
                return view('product::backend.category.action_column', compact('module_name', 'data', 'edit_permission', 'delete_permission'));
            })

            ->editColumn('name', function ($data) {
                return view('backend.branch.branch_id', compact('data'));
            })
            ->editColumn('mainCategory.name', function ($data) {
                return $data->mainCategory->name ?? '-';
            })

            ->editColumn('status', function ($row) {
                $checked = '';
                if ($row->status) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="' . route('backend.products-categories.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $row->id . '"  name="status" value="' . $row->id . '" ' . $checked . '>
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

            ->editColumn('created_at', function ($data) {
                $module_name = $this->module_name;

                $diff = Carbon::now()->diffInHours($data->created_at);

                if ($diff < 25) {
                    return $data->created_at->diffForHumans();
                } else {
                    return $data->created_at->isoFormat('llll');
                }
            })

            ->orderColumns(['id'], '-:column $1');

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatable, ProductCategory::CUSTOM_FIELD_MODEL, null);

        return $datatable->rawColumns(array_merge(['action', 'status', 'image', 'check', 'brand_id'], $customFieldColumns))
            ->toJson();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(CategoryRequest $request)
    {
        $data = $request->except('feature_image');

        $query = ProductCategory::create($data);

        if ($request->custom_fields_data) {
            $query->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        if (is_string($request->brand_id)) {
            $brand_id = explode(',', $request->brand_id);
        } else {
            $brand_id = $request->brand_id;
        }
        $query->brands()->sync($brand_id);

        storeMediaFile($query, $request->file('feature_image'));

        $message = __('messages.create_form', ['form' => __('category.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {

        $data = ProductCategory::with(['mainCategory', 'brands'])->findOrFail($id);

        $category_data = ProductCategory::where('id', $data['parent_id'])->where('status', 1)->first();

        if (!$category_data) {

            $data['parent_id'] = '';
        }

        if (! is_null($data)) {
            $custom_field_data = $data->withCustomFields();
            $data['custom_field_data'] = collect($custom_field_data->custom_fields_data)
                ->filter(function ($value) {
                    return $value !== null;
                })
                ->toArray();
        }

        $data['feature_image'] = $data->feature_image;
        $data['category_name'] = $data->mainCategory->name ?? null;
        $data['brand_id'] = $data->brands->pluck('id')->toArray();
        $data['brand_name'] = $data->brands->pluck('name')->toArray();
        return response()->json(['data' => $data, 'status' => true]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(CategoryRequest $request, $id)
    {
        $query = ProductCategory::findOrFail($id);

        $data = $request->except('feature_image');

        $query->update($data);
        if (is_string($request->brand_id)) {
            $brand_id = explode(',', $request->brand_id);
        } else {
            $brand_id = $request->brand_id;
        }
        $query->brands()->sync($brand_id);

        if ($request->custom_fields_data) {
            $query->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        // Only clear image if a new file is uploaded (which will replace it) or explicitly removed
        // Don't clear image if no file is uploaded - keep existing image
        if ($request->hasFile('feature_image')) {
            // New file uploaded - replace existing image
            storeMediaFile($query, $request->file('feature_image'));
        } elseif ($request->has('remove_feature_image') && $request->remove_feature_image == '1') {
            // Explicitly requested to remove image via remove_feature_image flag
            $query->clearMediaCollection('feature_image');
        }
        // If neither condition is met, keep the existing image

        $message = __('messages.update_form', ['form' => __('category.singular_title')]);

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
        if (env('IS_DEMO')) {
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
        }

        $data = ProductCategory::find($id);

        ProductCategoryBrand::where('category_id', $id)->delete();

        ProductCategoryMapping::where('category_id', $id)->delete();

        $data->delete();

        $message = __('messages.delete_form', ['form' => $data->parent_id ? __('category.sub_category') : __('category.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function subCategoryExport(Request $request)
    {
        $this->exportClass = '\App\Exports\SubCategoryExport';

        return $this->export($request);
    }
}