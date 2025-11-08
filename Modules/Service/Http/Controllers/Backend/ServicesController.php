<?php

namespace Modules\Service\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Modules\Category\Models\Category;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Modules\Employee\Models\BranchEmployee;
use Modules\Service\Http\Requests\ServiceRequest;
use Modules\Service\Models\Service;
use Modules\Service\Models\ServiceBranches;
use Modules\Service\Models\ServiceEmployee;
use Modules\Service\Models\ServiceGallery;
use Yajra\DataTables\DataTables;

class ServicesController extends Controller
{
    protected string $exportClass = '\App\Exports\ServicesExport';

    public function __construct()
    {
        // Page Title
        $this->module_title = __('service.title');
        // module name
        $this->module_name = 'services';

        // module icon
        $this->module_icon = 'fa-solid fa-clipboard-list';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
        $this->middleware(['permission:add_service'])->only('index');
        $this->middleware(['permission:edit_service'])->only('edit', 'update');
        $this->middleware(['permission:add_service'])->only('store');
        $this->middleware(['permission:delete_service'])->only('destroy');
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
        $module_title = __('service.title');
        $module_action = __('messages.list');
        $columns = CustomFieldGroup::columnJsonValues(new Service());
        $customefield = CustomField::exportCustomFields(new Service());

        $categoriesQuery = Category::whereNull('parent_id')->where('status', 1);
        $subcategoriesQuery = Category::whereNotNull('parent_id')->where('status', 1);

        if (auth()->user()->hasRole('admin')) {
            $categoriesQuery->where('created_by', auth()->user()->id);
            $subcategoriesQuery->where('created_by', auth()->user()->id);
        }

        $categories = $categoriesQuery->get();
        $subcategories = $subcategoriesQuery->get();
        $branches = Branch::where('status', 1)->get();

        $export_import = true;
        $export_columns = [
            [
                'value' => 'name',
                'text' => __('service.lbl_name'),
            ],
            [
                'value' => 'default_price',
                'text' => __('service.lbl_default_price'),
            ],
            [
                'value' => 'duration_min',
                'text' => __('service.lbl_duration'),
            ],
            [
                'value' => 'category',
                'text' => __('service.lbl_category_id'),
            ],
            [
                'value' => 'branches',
                'text' => __('service.lbl_branches'),
            ],
            [
                'value' => 'employees',
                'text' => __('service.employee_count'),
            ],
            [
                'value' => 'status',
                'text' => __('service.lbl_status'),

            ],
        ];
        $export_url = route('backend.services.export');
        $employees = User::role('employee')->whereNull('deleted_at')->get()->map(function($emp) {
            return [
                'id' => $emp->id,
                'name' => $emp->first_name,
                'avatar' => $emp->profile_image,
            ];
        });

        return view('service::backend.services.index_datatable', compact('module_action', 'filter', 'categories', 'subcategories', 'branches', 'columns', 'customefield', 'export_import', 'export_columns', 'export_url', 'employees'));
    }
    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $employee_id = $request->employee_id;
        $category_id = $request->category_id;
        $branch_id = $request->branch_id;
        $exclude_assigned = (bool) $request->get('exclude_assigned', false);
        $data = Service::with('employee', 'branches');
        if (auth()->user()->hasRole('admin')) {
            $data->where('created_by', auth()->user()->id);
        }

        if (isset($employee_id)) {
            $branch_id = BranchEmployee::where('employee_id', $employee_id)->value('branch_id');
            if ($exclude_assigned) {
                // Return services NOT already assigned to this employee
                $data = $data->whereDoesntHave('employee', function ($q) use ($employee_id) {
                    $q->where('employee_id', $employee_id);
                });
            } else {
                // Return only services already assigned to this employee
                $data = $data->whereHas('employee', function ($q) use ($employee_id) {
                    $q->where('employee_id', $employee_id);
                });
            }
            $data = $data->whereHas('branches', function ($q) use ($branch_id) {
                $q->where('branch_id', $branch_id);
            });
        }

        if (isset($category_id)) {
            $data->where('category_id', $category_id);
        }

        if (isset($request->branch_id)) {
            $data = $data->whereHas('branches', function ($q) use ($branch_id) {
                $q->where('branch_id', $branch_id);
            });
        }
        if (auth()->user()->hasRole('admin')) {
            $data->where('created_by', auth()->id());
        }

        $data = $data->get();

        return response()->json($data);
    }

    /* category wise service list */
    public function categort_services_list(Request $request)
    {
        $category = $request->category_id;
        $categoryService = Service::where('category_id', $category)->get();

        return $categoryService;
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                $services = Service::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_service_update');
                break;

            case 'delete':

                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }

                Service::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('branch.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => __('messages.bulk_update')]);
    }

    public function update_status(Request $request, Service $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('branch.status_update')]);
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $userId = Auth()->user()->id;
        $module_name = $this->module_name;
        $selectedBranchId = session('selected_branch');
        $query = Service::query()
            ->with(['category', 'sub_category'])
            ->withCount([
                'branches as branches_count' => function ($query) {
                    // Count all branches for the service
                    // For admin users, filter by branch ownership
                    if (auth()->user()->hasRole('admin')) {
                        $query->whereHas('branch', function ($q) {
                            $q->where('created_by', auth()->id());
                        });
                    }
                    // For other roles, count all branches
                },
                'employee as employee_count' => function ($query) {
                    if (auth()->user()->hasRole('admin')) {
                        $query->where('created_by', auth()->id());
                    } elseif (auth()->user()->hasRole('employee') || auth()->user()->hasRole('manager')) {
                        $query->where('employee_id', auth()->id());
                    }
                }
            ]);

          // Filter by session branch if set
          if ($selectedBranchId) {
            $query->whereHas('branches', function ($q) use ($selectedBranchId) {
                $q->where('branch_id', $selectedBranchId);
            });
        }


        // Apply role-based filtering for the services
        if (auth()->user()->hasRole('admin')) {
            $query->where('created_by', auth()->id());
        } elseif (auth()->user()->hasRole('employee') || auth()->user()->hasRole('manager')) {
            $query->whereHas('employee', function ($q) {
                $q->where('employee_id', auth()->id());
            });
        }


        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        if (isset($filter)) {
            if (isset($filter['category_id'])) {
                $query->where('category_id', $filter['category_id']);
            }
        }

        if (isset($filter)) {
            if (isset($filter['sub_category_id'])) {
                $query->where('sub_category_id', $filter['sub_category_id']);
            }
        }

        $datatable = $datatable->eloquent($query)
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $data->id . '"  name="datatable_ids[]" value="' . $data->id . '" onclick="dataTableRowCheck(' . $data->id . ')">';
            })
            ->editColumn('name', function ($data) {
                return view('backend.branch.branch_id', compact('data'));
            })
            ->addColumn('action', function ($data) use ($module_name) {
                return view('service::backend.services.action_column', compact('module_name', 'data'));
            })
            ->editColumn('employee_count', function ($data) {
                return "<b>$data->employee_count</b>  <button type='button' data-assign-module='" . $data->id . "' data-assign-target='#service-employee-assign-form' data-assign-event='employee_assign' class='btn btn-primary btn-sm rounded text-nowrap ' data-bs-toggle='tooltip' title=" . __('service.assign_staff_to_service') . "><i class='fa-solid fa-plus p-0'></i></button>";
            })
            ->editColumn('default_price', function ($data) {
                return \Currency::format($data->default_price);
            })
            ->editColumn('duration_min', function ($data) {
                return $data->duration_min . ' Min';
            })
            ->editColumn('status', function ($row) {
                $checked = '';
                if ($row->status) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="' . route('backend.services.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $row->id . '"  name="status" value="' . $row->id . '" ' . $checked . '>
                    </div>
                ';
            })
            ->editColumn('category_id', function ($data) {
                $category = isset($data->category->name) ? $data->category->name : '-';
                if (isset($data->sub_category->name)) {
                    $category = $category . ' > ' . $data->sub_category->name;
                }

                return $category;
            })
            ->filterColumn('category', function ($query, $keyword) {
                $query->whereHas('category', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%' . $keyword . '%');
                });
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

            ->orderColumns(['id'], '-:column $1');
        if (!request()->is_single_branch) {
            $datatable->editColumn('branches_count', function ($data) {
                return "<b>$data->branches_count</b>  <button type='button' data-assign-module='" . $data->id . "' data-assign-target='#service-branch-assign-form' data-assign-event='branch_assign' class='btn btn-primary btn-sm rounded text-nowrap ' data-bs-toggle='tooltip' title=" . __('branch.assign_branch_to_service') . "><i class='fa-solid fa-plus p-0'></i></button>";
            });
        }

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatable, Service::CUSTOM_FIELD_MODEL, null);

        return $datatable->rawColumns(array_merge(['action', 'image', 'status', 'check', 'branches_count', 'employee_count'], $customFieldColumns))
            ->toJson();
    }

    public function index_list_data(Request $request)
    {
        $term = trim($request->q);

        $query_data = User::role('employee')->where(function ($q) {
            if (!empty($term)) {
                $q->orWhere('name', 'LIKE', "%$term%");
            }
        })->get();

        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'name' => $row->first_name . $row->last_name,
                'avatar' => $row->profile_image,
            ];
        }

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $module_action = __('messages.create');

        $categoriesQuery = Category::whereNull('parent_id')->where('status', 1);
        $subcategoriesQuery = Category::whereNotNull('parent_id')->where('status', 1);

        if (auth()->user()->hasRole('admin')) {
            $categoriesQuery->where('created_by', auth()->user()->id);
            $subcategoriesQuery->where('created_by', auth()->user()->id);
        }

        $categories = $categoriesQuery->get();
        $subcategories = $subcategoriesQuery->get();
        $customefield = CustomField::exportCustomFields(new Service());
        return view('service::backend.services.form_offcanvas', compact('module_action', 'categories', 'subcategories', 'customefield'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(ServiceRequest $request)
    {

        if($request->has('service_id') && $request->service_id >0) {

            $data = Service::where('id', $request->service_id)->first();

            $request_data = $request->except('feature_image');

            // Update branch prices with NEW price if default_price changed
            if ($data->default_price !== floatval($request_data['default_price'])) {
                ServiceBranches::where('service_id', $request->service_id)->update(['service_price' => floatval($request_data['default_price'])]);
            }
            // Update branch duration with NEW duration if duration_min changed
            if ($data->duration_min !== $request_data['duration_min']) {
                ServiceBranches::where('service_id', $request->service_id)->update(['duration_min' => $request_data['duration_min']]);
            }

            $data->update($request_data);

            if ($request->custom_fields_data) {
                $data->updateCustomFieldData(json_decode($request->custom_fields_data));
            }

            // Only clear image if a new file is uploaded (which will replace it) or explicitly removed
            // Don't clear image if no file is uploaded - keep existing image
            if ($request->hasFile('feature_image')) {
                // New file uploaded - replace existing image
                storeMediaFile($data, $request->file('feature_image'), 'feature_image');
            } elseif ($request->has('remove_feature_image') && $request->remove_feature_image == '1') {
                // Explicitly requested to remove image
                $data->clearMediaCollection('feature_image');
            }
            // If neither condition is met, keep the existing image

            $message = __('messages.update_form', ['form' => __('service.singular_title')]);

            return redirect()->route('backend.services.index')->with('success', $message);


        }


        $auth_user = User::find(auth()->id());
        if (auth()->user()->hasRole('admin') && !$auth_user->currentSubscription) {
            return response()->json([
                'message' => __('messages.you_cannot_add_a_service_no_active_subscription_found'),
                'status' => false
            ], 422);
        }
        if($auth_user->currentSubscription && $auth_user->serviceLimitReach()) {
            return response()->json(['message' => __('messages.cant_add_service_limit_reached'), 'status' => false], 422);
        }

        $data = $request->except('feature_image');
        $userId = Auth()->user()->id;

        $query = Service::create($data);
        // Link to branch automatically based on role/selection so it appears in index
        if (auth()->user()->hasAnyRole(['manager'])) {
            $branch_id = auth()->user()->branch->id;
            ServiceBranches::create([
                'service_id' => $query->id,
                'branch_id' => $branch_id,
                'service_price' => $query->default_price ?? 0,
                'duration_min' => $query->duration_min,
            ]);
            $service_data = [
                'employee_id' => Auth()->user()->id,
                'service_id' => $query->id,
            ];
            ServiceEmployee::create($service_data);
        } elseif (auth()->user()->hasRole('admin')) {
            // If a specific branch is selected in session, attach the new service to it
            $selectedBranchId = session('selected_branch');
            if (!empty($selectedBranchId)) {
                ServiceBranches::create([
                    'service_id' => $query->id,
                    'branch_id' => $selectedBranchId,
                    'service_price' => $query->default_price ?? 0,
                    'duration_min' => $query->duration_min,
                ]);
                } else {
                // No explicit branch selection; attach to all branches owned by this vendor so it appears in index
                $vendorBranchIds = Branch::where('created_by', auth()->id())->pluck('id');
                foreach ($vendorBranchIds as $bid) {
                    ServiceBranches::create([
                        'service_id' => $query->id,
                        'branch_id' => $bid,
                        'service_price' => $query->default_price ?? 0,
                        'duration_min' => $query->duration_min,
                    ]);
                }
            }
        }



        if ($request->custom_fields_data) {
            $query->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        if ($request->hasFile('feature_image')) {
            storeMediaFile($query, $request->file('feature_image'));
        }

        $message = __('messages.create_form', ['form' => __('service.singular_title')]);
        // Return JSON for AJAX requests; otherwise redirect
        if ($request->ajax()) {
            return response()->json(['message' => $message, 'status' => true], 200);
        }
        return redirect()->route('backend.services.index')->with('success', $message);
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

        $data = Service::findOrFail($id);

        return view('service::backend.services.show', compact('module_action', "$data"));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $data = Service::findOrFail($id);

        if (!is_null($data)) {
            $custom_field_data = $data->withCustomFields();
            $data['custom_field_data'] = collect($custom_field_data->custom_fields_data)
                ->filter(function ($value) {
                    return $value !== null;
                })
                ->toArray();
        }

        return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(ServiceRequest $request, $id)
    {
        $data = Service::findOrFail($id);

        $request_data = $request->except('feature_image');

        // Update branch prices with NEW price if default_price changed
        if ($data->default_price !== floatval($request_data['default_price'])) {
            ServiceBranches::where('service_id', $id)->update(['service_price' => floatval($request_data['default_price'])]);
        }
        // Update branch duration with NEW duration if duration_min changed
        if ($data->duration_min !== $request_data['duration_min']) {
            ServiceBranches::where('service_id', $id)->update(['duration_min' => $request_data['duration_min']]);
        }

        $data->update($request_data);

        if ($request->custom_fields_data) {
            $data->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        // Only clear image if a new file is uploaded (which will replace it) or explicitly removed
        // Don't clear image if no file is uploaded - keep existing image
        if ($request->hasFile('feature_image')) {
            // New file uploaded - replace existing image
            storeMediaFile($data, $request->file('feature_image'), 'feature_image');
        } elseif ($request->has('remove_feature_image') && $request->remove_feature_image == '1') {
            // Explicitly requested to remove image
            $data->clearMediaCollection('feature_image');
        }
        // If neither condition is met, keep the existing image

        $message = __('messages.update_form', ['form' => __('service.singular_title')]);

        return redirect()->route('backend.services.index')->with('success', $message);

        // return response()->json(['message' => $message, 'status' => true], 200);
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

        $data = Service::findOrFail($id);

        $data->branches()->delete();

        $data->employee()->delete();

        $data->delete();

        $message = __('messages.delete_form', ['form' => __('service.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * List of trashed ertries
     * works if the softdelete is enabled.
     *
     * @return Response
     */
    public function trashed()
    {
        $module_name_singular = Str::singular($this->module_name);

        $module_action = __('messages.trash');

        $data = Service::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate();

        return view('service::backend.services.trash', compact("$data", 'module_name_singular', 'module_action'));
    }

    /**
     * Restore a soft deleted entry.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function restore($id)
    {
        $data = Service::withTrashed()->find($id);
        $data->restore();

        $message = __('messages.service_data');

        return response()->json(['message' => $message, 'status' => true]);
    }

    /**
     * Render the assign employee offcanvas Blade with all required variables.
     */
    public function assign_employee_offcanvas($id)
    {
        $service = Service::findOrFail($id);
        $employees = User::role('employee')->where('created_by',auth()->id())->whereNull('deleted_at')->get()->map(function($emp) {
            return [
                'id' => $emp->id,
                'name' => $emp->first_name,
                'avatar' => $emp->profile_image,
            ];
        });
        $assignedEmployees = ServiceEmployee::where('service_id', $id)->where('created_by',auth()->id())
            ->with('employee')
            ->get()
            ->map(function($data) {
                return [
                    'employee_id' => $data->employee_id,
                    'name' => $data->employee->first_name,
                    'avatar' => $data->employee->profile_image,
                ];
            });
        return view('service::backend.services.assign_employee_offcanvas', compact('service', 'employees', 'assignedEmployees'));
    }
    public function assign_employee_list($id)
    {
        $service_user = ServiceEmployee::whereHas('employee', function ($q) {
            return $q->whereNull('deleted_at');
        })->with('employee')->where('service_id', $id)->get();

        $service_user = $service_user->each(function ($data) {
            $data['name'] = $data->employee->first_name;
            $data['avatar'] = $data->employee->profile_image;

            return $data;
        });

        return response()->json(['status' => true, 'data' => $service_user]);
    }


    public function assign_employee_update($id, Request $request)
    {
        // Always default to an empty array if no employees passed
        $employees = (array) $request->input('employees', []);

        ServiceEmployee::where('service_id', $id)->delete();

        foreach ($employees as $employeeId) {
            $data = [
                'service_id' => $id,
                'employee_id' => $employeeId,
            ];

            if (auth()->user()->hasRole('admin')) {
                $data['created_by'] = auth()->user()->id;
                $data['updated_by'] = auth()->user()->id;
            }

            ServiceEmployee::create($data);
        }

        return response()->json(['status' => true, 'message' => __('messages.service_staff_update')]);
    }

    // =========Service Staff Assign list and Assign update ======= //

    public function assign_branch_list($id)
    {
        // Fetch service branches with branch relationship
        $service_branch_query = ServiceBranches::with('branch')->where('service_id', $id);

        // Apply admin-specific filtering if the user has the 'admin' role
        if (auth()->user()->hasRole('admin')) {
            $service_branch_query->whereHas('branch', function ($query) {
                $query->where('created_by', auth()->id());
            });
        }

        // Fetch the filtered service branches
        $service_branch = $service_branch_query->get();

        // Map additional data to each branch
        $service_branch = $service_branch->map(function ($data) {
            $data['name'] = $data->branch->name ?? null;
            return $data;
        });

        // Return response
        return response()->json(['status' => true, 'data' => $service_branch]);
    }

    public function assign_branch_update($id, Request $request)
    {
        ServiceBranches::where('service_id', $id)->delete();

        $branches = $request->branches;
        if (is_string($branches)) {
            $branches = json_decode($branches, true);
        }
        if (!is_array($branches)) {
            return response()->json(['status' => false, 'message' => 'Invalid branches data.'], 400);
        }
        if (array_keys($branches) !== range(0, count($branches) - 1)) {
            $branches = array_map(function($branchId, $data) {
                $data['branch_id'] = $branchId;
                return $data;
            }, array_keys($branches), $branches);
        }
        $service = Service::findOrFail($id);

        if (is_array($branches)) {
            // Track if we need to update service default_price
            $updateDefaultPrice = false;
            $firstPrice = null;

            foreach ($branches as $key => $value) {
                if (!isset($value['branch_id'])) {
                    continue;
                }
                $servicePrice = $value['service_price'] ?? 0;

                // Use first branch price as default_price if we haven't set one yet
                if ($firstPrice === null && $servicePrice > 0) {
                    $firstPrice = $servicePrice;
                    $updateDefaultPrice = true;
                }

                ServiceBranches::create([
                    'service_id' => $id,
                    'branch_id' => $value['branch_id'],
                    'service_price' => $servicePrice,
                    'duration_min' => $value['duration_min'],
                    'created_by' => auth()->user()->id,
                    'updated_by' => auth()->user()->id,
                ]);
            }

            // Update service default_price with first branch price so it reflects in index
            if ($updateDefaultPrice && $firstPrice !== null) {
                $service->update(['default_price' => $firstPrice]);
            }
        }

        return response()->json(['status' => true, 'message' => __('messages.service_branch_update')]);
    }

    public function getGalleryImages($id)
    {
        $service = Service::findOrFail($id);

        $data = ServiceGallery::where('service_id', $id)->get();

        return response()->json(['data' => $data, 'service' => $service, 'status' => true]);
    }

    public function uploadGalleryImages(Request $request, $id)
    {
        $gallery = collect($request->gallery, true);

        $images = ServiceGallery::where('service_id', $id)->whereNotIn('id', $gallery->pluck('id'))->get();

        foreach ($images as $key => $value) {
            $value->clearMediaCollection('gallery_images');
            $value->delete();
        }

        foreach ($gallery as $key => $value) {
            if ($value['id'] == 'null') {
                $serviceGallery = ServiceGallery::create([
                    'service_id' => $id,
                ]);

                $serviceGallery->addMedia($value['file'])->toMediaCollection('gallery_images');

                $serviceGallery->full_url = $serviceGallery->getFirstMediaUrl('gallery_images');
                $serviceGallery->save();
            }
        }

        return response()->json(['message' => __('messages.service_gallery_update'), 'status' => true]);
    }

    public function uniqueServices(Request $request)
    {
        $service = $request->input('service');
        $serviceId = $request->input('service_id');
        $isUnique = true;
        if (!$serviceId) {
            $isUnique = Service::where('name', $service)->where('created_by', auth()->id())
                ->doesntExist();
        }
        return response()->json(['isUnique' => $isUnique]);
    }

    public function getSubcategories(Request $request)
    {
        $categoryId = $request->input('category_id');

        $subcategoriesQuery = Category::whereNotNull('parent_id')->where('status', 1);

        if ($categoryId) {
            $subcategoriesQuery->where('parent_id', $categoryId);
        }

        // Apply role-based filtering
        if (auth()->user()->hasRole('admin')) {
            $subcategoriesQuery->where('created_by', auth()->user()->id);
        }

        $subcategories = $subcategoriesQuery->select('id', 'name')->get();

        return response()->json($subcategories);
    }

    public function getEditForm($id)
    {
        // dd($id);
        $service = Service::findOrFail($id);

        $categoriesQuery = Category::whereNull('parent_id')->where('status', 1);
        $subcategoriesQuery = Category::whereNotNull('parent_id')->where('status', 1);

        if (auth()->user()->hasRole('admin')) {
            $categoriesQuery->where('created_by', auth()->user()->id);
            $subcategoriesQuery->where('created_by', auth()->user()->id);
        }

        $categories = $categoriesQuery->get();
        $subcategories = $subcategoriesQuery->get();
        $customefield = CustomField::exportCustomFields(new Service());

        return view('service::backend.services.edit_form', compact('service', 'categories', 'subcategories', 'customefield'));
    }

    public function getServiceData($id)
    {
        $service = Service::findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $service->id,
                'name' => $service->name,
                'default_price' => $service->default_price,
                'duration_min' => $service->duration_min,
                'description' => $service->description,
                'category_id' => $service->category_id,
                'sub_category_id' => $service->sub_category_id,
                'status' => $service->status
            ]
        ]);
    }
}
