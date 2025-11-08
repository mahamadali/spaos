<?php

namespace Modules\Category\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Category\Http\Requests\CategoryRequest;
use Modules\Category\Models\Category;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Yajra\DataTables\DataTables;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{

    protected string $exportClass = '\App\Exports\CategoryExport';

    public function __construct()
    {
        // Page Title
        $this->module_title = __('category.title');

        // module name
        $this->module_name = 'categories';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => 'fa-regular fa-sun',
            'module_name' => $this->module_name,
        ]);
        $this->middleware(['permission:view_category'])->only('index');
        $this->middleware(['permission:view_subcategory'])->only('index_nested');
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
        $columns = CustomFieldGroup::columnJsonValues(new Category());
        $customefield = CustomField::exportCustomFields(new Category());

        $export_import = true;
        $export_columns = [
            [
                'value' => 'name',
                'text' => __('service.lbl_name'),
            ],
            [
                'value' => 'status',
                'text' => __('service.lbl_status'),
            ],
            [
                'value' => 'Created Date',
                'text' => __('messages.lbl_created_date'),
            ],
        ];
        $export_url = route('backend.categories.export');

        return view('category::backend.categories.index_datatable', compact('module_name', 'filter', 'module_action', 'columns', 'customefield', 'export_import', 'export_columns', 'export_url', 'module_title'));
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $term = trim($request->q);
        $parentID = $request->parent_id;

        $query_data = Category::where(function ($q) use ($parentID) {
            if (! empty($term)) {
                $q->orWhere('name', 'LIKE', "%$term%");
            }
            if (isset($parentID) && $parentID != 0) {
                $q->where('parent_id', $parentID);
            } else {
                $q->where(function ($query) {
                    $query->whereNull('parent_id')
                        ->orWhere('parent_id', 0);
                });
            }
        })
            ->where('status', 1); // Add this line to filter by status
        if (auth()->user()->hasRole('admin')) {
            $query_data->where('created_by', auth()->id());
        }
        $query_data = $query_data->get();
        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'name' => $row->name,
            ];
        }

        return response()->json($data);
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                $branches = Category::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_category_update');
                break;

            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }

                Category::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_category_update');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('branch.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => __('messages.bulk_update')]);
    }

    public function update_status(Request $request, Category $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('branch.status_update')]);
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $module_name = $this->module_name;
        $query = Category::query()->with('media')
            ->where(function($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->where('created_by', auth()->id());

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
            ->addColumn('action', function ($data) use ($module_name) {
                return view('category::backend.categories.action_column', compact('module_name', 'data'));
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
                        <input type="checkbox" data-url="' . route('backend.categories.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $row->id . '"  name="status" value="' . $row->id . '" ' . $checked . '>
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
        $customFieldColumns = CustomField::customFieldData($datatable, Category::CUSTOM_FIELD_MODEL, null);

        return $datatable->rawColumns(array_merge(['action', 'status', 'image', 'check', 'name'], $customFieldColumns))
            ->toJson();
    }

    public function index_nested(Request $request)
    {
        $categories = Category::with('mainCategory')->whereNull('parent_id')->where('status', 1)->where('created_by', auth()->id());
        $categories = $categories->get();
        $filter = [
            'status' => $request->status,
        ];
        $parentID = $request->category_id ?? null;

        $module_action = __('messages.list');

        $module_title = __('category.sub_categories');
        $columns = CustomFieldGroup::columnJsonValues(new Category());
        $customefield = CustomField::exportCustomFields(new Category());

        $export_import = true;
        $export_columns = [
            [
                'value' => 'name',
                'text' => __('service.lbl_name'),
            ],
            [
                'value' => 'category_name',
                'text' => __('category.lbl_category'),
            ],
            [
                'value' => 'status',
                'text' => __('service.lbl_status'),

            ],
            [
                'value' => 'Created Date',
                'text' => __('messages.lbl_created_date'),
            ],
        ];
        $export_url = route('backend.sub-categories.export');
        $isSubCategory = true;
        return view('category::backend.categories.index_nested_datatable', compact('parentID', 'module_action', 'filter', 'categories', 'module_title', 'columns', 'customefield', 'export_import', 'export_columns', 'export_url','isSubCategory'));
    }

    public function index_nested_data(Request $request, Datatables $datatable)
    {
        $module_name = $this->module_name;
        $query = Category::query()
            ->select('categories.*', 'mainCategory.name as mainCategory_name')
            ->where('categories.parent_id', '!=', 0)
            ->leftJoin('categories as mainCategory', 'mainCategory.id', '=', 'categories.parent_id')
            ->whereNotNull('categories.parent_id')
            ->whereNull('categories.deleted_at')
            ->where('categories.created_by', auth()->id());

        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('categories.status', $filter['column_status']);
            }
            if (isset($filter['column_category'])) {
                $query->where('categories.parent_id', $filter['column_category']);
            }
        }

        $datatable = $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $row->id . '" name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($data) use ($module_name) {
                return view('category::backend.categories.sub_action_column', compact('module_name', 'data'));
            })
            ->editColumn('name', function ($data) {
                return view('backend.branch.branch_id', compact('data'));
            })
            ->editColumn('mainCategory.name', function ($data) {
                return $data->mainCategory->name ?? '-';
            })
            ->orderColumn('mainCategory_name', function ($query, $order) {
                $query->orderBy('mainCategory_name', $order);
            })
            ->editColumn('status', function ($row) {
                $checked = '';
                if ($row->status) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="' . route('backend.categories.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input" id="datatable-row-' . $row->id . '" name="status" value="' . $row->id . '" ' . $checked . '>
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
            ->editColumn('created_at', function ($data) {
                $diff = Carbon::now()->diffInHours($data->created_at);

                if ($diff < 25) {
                    return $data->created_at->diffForHumans();
                } else {
                    return $data->created_at->isoFormat('llll');
                }
            })
            ->orderColumns(['id'], '-:column $1');

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatable, Category::CUSTOM_FIELD_MODEL, null);

        return $datatable->rawColumns(array_merge(['action', 'status', 'image', 'check'], $customFieldColumns))
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $module_action = __('messages.create');
        $customefield = CustomField::exportCustomFields(new Category());

        return view('category::backend.categories.create', compact('module_action', 'customefield'));
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

        $query = Category::create($data);

        if ($request->custom_fields_data) {
            $query->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        storeMediaFile($query, $request->file('feature_image'));

        $message = __('messages.create_form', ['form' => __('category.singular_title')]);

        if ($request->wantsJson()) {
            return response()->json(['message' => $message, 'status' => true], 200);
        }
        if($request->parent_id){
            return redirect()->route('backend.categories.index_nested')->with('success', $message);
        }else{
            return redirect()->route('backend.categories.index')->with('success', $message);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $module_action = __('messages.show');

        $data = Category::with('mainCategory')->findOrFail($id);

        return view('category::backend.categories.show', compact('module_action', "$data"));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $module_action = __('messages.edit');
        $category = Category::with('mainCategory')->findOrFail($id);
        $customefield = CustomField::exportCustomFields(new Category());

        if (! is_null($category)) {
            $custom_field_data = $category->withCustomFields();
            $category->custom_field_data = collect($custom_field_data->custom_fields_data)
                ->filter(function ($value) {
                    return $value !== null;
                })
                ->toArray();
        }

        $category->feature_image = $category->feature_image;
        $category->category_name = $category->mainCategory->name ?? null;

        if (request()->ajax()) {
            return response()->json(['data' => $category, 'status' => true]);
        }

        if (request()->wantsJson()) {
            return response()->json(['data' => $category, 'status' => true]);
        }

        return view('category::backend.categories.create', compact('module_action', 'category', 'customefield'));
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
        $query = Category::findOrFail($id);

        $data = $request->except('feature_image');

        $query->update($data);

        if ($request->custom_fields_data) {
            $query->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        // Only clear image if a new file is uploaded (which will replace it) or explicitly removed
        // Don't clear image if no file is uploaded - keep existing image
        if ($request->hasFile('feature_image')) {
            // New file uploaded - replace existing image
            storeMediaFile($query, $request->file('feature_image'), 'feature_image');
        } elseif ($request->has('feature_image_removed') && $request->feature_image_removed == 1) {
            // Explicitly requested to remove image via feature_image_removed flag
            $query->clearMediaCollection('feature_image');
        }
        // If neither condition is met, keep the existing image

        $message = __('messages.update_form', ['form' => __('category.singular_title')]);

        if ($request->wantsJson()) {
            return response()->json(['message' => $message, 'status' => true], 200);
        }

        if ($request->parent_id) {
            return redirect()->route('backend.categories.index_nested')->with('success', $message);
        } else {
            return redirect()->route('backend.categories.index')->with('success', $message);
        }
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

        $data = Category::findOrFail($id);

        $data->delete();

        $message = __('messages.delete_form', ['form' => __('category.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function subCategoryExport(Request $request)
    {
        $this->exportClass = '\App\Exports\SubCategoryExport';

        return $this->export($request);
    }
}
