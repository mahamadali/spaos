<?php

namespace Modules\Employee\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingService;
use Modules\Commission\Models\Commission;
use Modules\Commission\Models\EmployeeCommission;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Modules\Employee\Http\Requests\EmployeeRequest;
use Modules\Employee\Models\BranchEmployee;
use Modules\Employee\Models\EmployeeRating;
use Modules\Service\Models\Service;
use Modules\Service\Models\ServiceEmployee;
use Yajra\DataTables\DataTables;

class EmployeesController extends Controller
{

    protected string $exportClass = '\App\Exports\EmployeeExport';

    public function __construct()
    {
        // Page Title
        $this->module_title = __('employee.title');

        // module name
        $this->module_name = 'employees';

        // directory path of the module
        $this->module_path = 'employee::backend';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => 'fa-regular fa-sun',
            'module_name' => $this->module_name,
            'module_path' => $this->module_path,
        ]);
        $this->middleware(['permission:view_staff'])->only('index');
        $this->middleware(['permission:edit_staff'])->only('edit', 'update');
        $this->middleware(['permission:add_staff'])->only('store');
        $this->middleware(['permission:delete_staff'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $module_action = __('messages.list');
        $columns = CustomFieldGroup::columnJsonValues(new User());
        $customefield = CustomField::exportCustomFields(new User());
        $module_title = __('employee.title');
        $export_import = true;
        $export_columns = [
            [
                'value' => 'first_name',
                'text' => __('employee.lbl_first_name'),
            ],
            [
                'value' => 'last_name',
                'text' => __('employee.lbl_last_name'),
            ],
            [
                'value' => 'email',
                'text' => __('employee.lbl_Email'),
            ],
            [
                'value' => 'branches',
                'text' => __('branch.title'),
            ],
            [
                'value' => 'role',
                'text' => __('employee.lbl_role'),
            ],
            [
                'value' => 'varification_status',
                'text' => __('employee.lbl_verification_status'),
            ],
            // [
            //     'value' => 'is_banned',
            //     'text' => __('employee.lbl_blocked'),
            // ],
            [
                'value' => 'status',
                'text' => __('employee.lbl_status'),
            ],
        ];
        $export_url = route('backend.employees.export');
        $services = Service::select('id', 'name')->where('status', 1)->where('created_by',auth()->user()->id)->get();
        $branches = Branch::select('id', 'name')->where('status', 1)->where('created_by',auth()->user()->id)->get();
        $commissions = Commission::select('id', 'title')->where('status', 1)->where('created_by',auth()->user()->id)->get();
        return view('employee::backend.employees.index', compact('module_action', 'columns', 'customefield', 'export_import', 'export_columns', 'export_url','module_title','services','branches','commissions'));
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $term = trim($request->q);

        $query_data = Branch::where('status', 1)
            ->where(function ($q) use ($term) {
                if (!empty($term)) {
                    $q->orWhere('name', 'LIKE', "%$term%");
                }
            });

        if(auth()->user()->hasRole('admin')) {
            $query_data = $query_data->where('created_by', auth()->id());
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

    public function employee_list(Request $request)
    {
        $term = trim($request->q);

        $branchId = $request->branch_id;

        $role = $request->role;

        // Need To Add Role Base
        // If role is specified, use that role; otherwise default to 'employee'
        $defaultRole = !empty($role) ? $role : 'employee';
        $query_data = User::role($defaultRole)->with('media', 'branches','mainBranch')->where(function ($q) use ($term) {
            if (!empty($term)) {
                $q->orWhere('first_name', 'LIKE', "%$term%");
                $q->orWhere('last_name', 'LIKE', "%$term%");
            }
        });

        if ($request->show_in_calender) {
            $query_data->CalenderResource();
        }

        if (isset($branchId) && !empty($branchId)) {
            $query_data->whereHas('branches', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        // Filter by created_by and status for all users (when role is manager, show only active managers created by logged-in user)
        if (!empty($role) && $role === 'manager') {
            $query_data->where('created_by', auth()->id())
                       ->where('status', 1);
        } elseif(auth()->user()->hasRole('admin')) {
            $query_data->where('created_by', auth()->id());
        }

        $query_data = $query_data->get();

        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'name' => $row->full_name,
                'avatar' => $row->profile_image,
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
                // Need To Add Role Base
                $employee = User::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_employee_update');
                break;

            case 'delete':

                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                ServiceEmployee::whereIn('employee_id', $ids)->delete();
                BranchEmployee::whereIn('employee_id', $ids)->delete();
                User::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_employee_delete');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('branch.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => __('messages.bulk_update')]);
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $module_name = $this->module_name;
        $query = User::select('users.*')->role(['employee', 'manager'])->branch()->with('media', 'mainBranch');

        if(auth()->user()->hasRole('admin')) {
            $query = $query->whereHas('mainBranch', function ($q){
                $q->where('created_by', auth()->id());
            });
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
            ->addColumn('action', function ($data) {
                return view('employee::backend.employees.action_column', compact('data'));
            })

            ->addColumn('employee_id', function ($data) {
                $Profile_image = $data->profile_image ?? default_user_avatar();
                $name = $data->full_name ?? default_user_name();
                $email = $data->email ?? '--';
                return view('booking::backend.bookings.datatable.employee_id', compact('Profile_image', 'name', 'email'));
            })

            ->orderColumn('employee_id', function ($query, $order) {
                $query->orderBy('users.first_name', $order)
                    ->orderBy('users.last_name', $order);
            }, 1)

            ->filterColumn('employee_id', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->where(function ($query) use ($keyword) {
                        $query->where('first_name', 'like', '%' . $keyword . '%')
                            ->orWhere('last_name', 'like', '%' . $keyword . '%')
                            ->orWhere('email', 'like', '%' . $keyword . '%');
                    });
                }
            })

            ->editColumn('email_verified_at', function ($data) {
                $checked = '';
                if ($data->email_verified_at) {
                    return '<span class="badge bg-success-subtle text-success"><i class="fa-solid fa-envelope" style="margin-right: 2px"></i>' . __('employee.msg_verified') . ' </span>';
                }

                return '<button  type="button" data-url="' . route('backend.employees.verify-employee', $data->id) . '" data-token="' . csrf_token() . '" class="button-status-change btn btn-text-danger btn-sm  bg-danger-subtle"  id="datatable-row-' . $data->id . '"  name="is_verify" value="' . $data->id . '" ' . $checked . '>Verify</button>';
            })
            ->editColumn('service', function ($data) {
                return " <button type='button' data-custom-module='{$data->id}' data-assign-module='{$data->id}' data-assign-target='#package-service-form' data-custom-event='custom_form'  data-assign-event='package_service_form' class='btn btn-primary btn-sm rounded'>{$data->services->count()}</button>";
            })
            ->orderColumn('service', function ($query, $direction) {
                $query->select('packages.*')
                    ->leftJoin('package_services', 'package_services.package_id', '=', 'packages.id')
                    ->selectRaw('COUNT(package_services.id) as service_count')
                    ->groupBy('packages.id');
                $query->orderBy('service_count', $direction);
            })
            ->editColumn('is_manager', function ($data) {
                if ($data->is_manager) {
                    return '<span class="badge bg-danger-subtle text-danger">Manager</span>';
                }

                return '<span class="badge bg-info-subtle">Staff</span>';
            })
            ->addColumn('branch_id', function ($data) {
                return optional($data->mainBranch)->pluck('name')->toArray() ?? '-';
            })
            ->editColumn('is_banned', function ($data) {
                $checked = '';
                if ($data->is_banned) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="' . route('backend.employees.block-employee', $data->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $data->id . '"  name="is_banned" value="' . $data->id . '" ' . $checked . '>
                    </div>
                 ';
            })

            ->editColumn('status', function ($data) {
                $checked = '';
                if ($data->status) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="' . route('backend.employees.update_status', $data->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $data->id . '"  name="status" value="' . $data->id . '" ' . $checked . '>
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
            ->rawColumns(['service'])
            ->orderColumns(['id'], '-:column $1');

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatable, User::CUSTOM_FIELD_MODEL, null);

        return $datatable->rawColumns(array_merge(['employee_id', 'action', 'service', 'status', 'is_banned', 'email_verified_at', 'check', 'image', 'is_manager'], $customFieldColumns))
            ->toJson();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(EmployeeRequest $request)
    {
        $auth_user = User::find(auth()->id());

        if (auth()->user()->hasRole('admin') && !$auth_user->currentSubscription) {
            return response()->json([
                'message' => __('messages.you_cant_add_staff'),
                'status' => false
            ], 422);
        }

        if($auth_user->currentSubscription && $auth_user->staffLimitReach()) {
            return response()->json(['message' => __('messages.you_cant_add_staff_limit_reached'), 'status' => false], 422);
        }

        $data = $request->all();

        $data['password'] = Hash::make($data['password']);

        if ($request->confirmed == 1) {
            $data = \Arr::add($data, 'email_verified_at', Carbon::now());
        } else {
            $data = \Arr::add($data, 'email_verified_at', null);
        }
        $data['created_by'] = auth()->id();
        $data = User::create($data);

        $roles = ['employee'];

        if ($request->is_manager) {
            $roles[] = 'manager';

            if ($request->has('branch_id')) {
                $branch = Branch::where('id', $request->branch_id)->first();
                if ($branch) {
                    $branch->update(['manager_id' => $data->id]); // Update the branch with the new manager
                }
            }
        }

        $data->assignRole($roles);
        $data->user_type = implode(', ', $roles);
        $data->save();

        $profile = [
            'about_self' => $request->about_self,
            'expert' => $request->expert,
            'facebook_link' => $request->facebook_link,
            'instagram_link' => $request->instagram_link,
            'twitter_link' => $request->twitter_link,
            'dribbble_link' => $request->dribbble_link,
        ];

        $data->profile()->updateOrCreate([], $profile);

        if ($request->custom_fields_data) {
            $data->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        if ($request->has('profile_image')) {
            $request->file('profile_image');

            storeMediaFile($data, $request->file('profile_image'), 'profile_image');
        }

        $employee_id = $data['id'];


        \Artisan::call('cache:clear');

        if ($request->has('branch_id')) {
            $branch_data = [
                'employee_id' => $employee_id,
                'branch_id' => $request->branch_id,
            ];
            BranchEmployee::create($branch_data);
        }
        if ($request->has('service_id')) {
            if ($request->service_id !== null) {
                $services = is_array($request->service_id) ? $request->service_id : explode(',', $request->service_id);
                foreach ($services as $value) {
                    $service_data = [
                        'employee_id' => $employee_id,
                        'service_id' => $value,
                    ];
                    ServiceEmployee::create($service_data);
                }
            }
        }
        if (isset($request->commission_id) && $request->has('commission_id')) {
            $commission_data = [
                'employee_id' => $employee_id,
                'commission_id' => $request->commission_id,
            ];

            EmployeeCommission::updateOrCreate($commission_data, $commission_data);
        }

        $message = __('messages.create_form', ['form' => __('employee.singular_title_manager')]);

        if ($request->wantsJson()) {
        return response()->json(['message' => $message, 'data' => $data, 'status' => true], 200);
        }else{
            return redirect()->route('backend.employees.index')->with('success', $message);
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

        $data = User::role('employee')->findOrFail($id);

        return view('employee::backend.employees.show', compact('module_action', "$data"));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $data = User::role('employee')->with('branches', 'branch', 'services', 'commissions', 'profile')->findOrFail($id);
        if (!is_null($data)) {
            $custom_field_data = $data->withCustomFields();
            $data['custom_field_data'] = collect($custom_field_data->custom_fields_data)
                ->filter(function ($value) {
                    return $value !== null;
                })
                ->toArray();
        }

        $data['branch_id'] = $data->branch->branch_id ?? null;

        $data['service_id'] = $data->services->pluck('service_id') ?? [];

        $data['commission_id'] = $data->commissions()->first()->commission_id ?? null;

        $data['profile_image'] = $data->profile_image;

        $data['about_self'] = $data->profile->about_self ?? null;

        $data['expert'] = $data->profile->expert ?? null;

        $data['facebook_link'] = $data->profile->facebook_link ?? null;

        $data['instagram_link'] = $data->profile->instagram_link ?? null;

        $data['twitter_link'] = $data->profile->twitter_link ?? null;

        $data['dribbble_link'] = $data->profile->dribbble_link ?? null;

        return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(EmployeeRequest $request, $id)
    {
        $data = User::role('employee')->findOrFail($id);

        $request_data = $request->except('profile_image');

        if (isset($request->password) && $request->password !== 'undefined' && !empty($request->password)) {
            $request_data['password'] = Hash::make($request_data['password']);
        } else {
            $request_data = $request->except('password');
        }

        $data->update($request_data);

        $roles = ['employee'];

        if ($request->is_manager) {
            $roles[] = 'manager';

            if ($request->has('branch_id')) {
                $branch = Branch::where('id', $request->branch_id)->first();
                if ($branch) {
                    $branch->update(['manager_id' => $data->id]); // Update the branch with the new manager
                }
            }
        }

        $data->user_type = implode(', ', $roles);
        $data->save();

        $profile = [
            'about_self' => $request->about_self,
            'expert' => $request->expert,
            'facebook_link' => $request->facebook_link,
            'instagram_link' => $request->instagram_link,
            'twitter_link' => $request->twitter_link,
            'dribbble_link' => $request->dribbble_link,
        ];

        $data->profile()->updateOrCreate([], $profile);

        if ($request->custom_fields_data) {
            $data->updateCustomFieldData(json_decode($request->custom_fields_data));
        }

        if ($request->has('profile_image') && $request->file('profile_image')) {

            storeMediaFile($data, $request->file('profile_image'), 'profile_image');
        }

        BranchEmployee::where('employee_id', $id)->delete();

        ServiceEmployee::where('employee_id', $id)->delete();

        EmployeeCommission::where('employee_id', $id)->delete();


        $employee_id = $data->id;



        \Artisan::call('cache:clear');

        if ($request->has('branch_id')) {
            $branch_data = [
                'employee_id' => $id,
                'branch_id' => $request->branch_id,
            ];

            BranchEmployee::create($branch_data);
        }

        if ($request->has('service_id')) {
            if ($request->service_id !== null) {
                $services = is_array($request->service_id) ? $request->service_id : explode(',', $request->service_id);

            foreach ($services as $value) {
                ServiceEmployee::create([
                    'employee_id' => $employee_id,
                    'service_id'  => $value,
                ]);
            }
            }
        }

        if ($request->commission_id) {
            $commission_data = [

                'employee_id' => $id,
                'commission_id' => $request->commission_id,
            ];

            EmployeeCommission::updateOrCreate($commission_data, $commission_data);
        }

        $message = __('messages.update_form', ['form' => __('employee.singular_title')]);

        if ($request->wantsJson()) {
        return response()->json(['message' => $message, 'status' => true], 200);
        }else{
            return redirect()->route('backend.employees.index')->with('success', $message);
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

        // Find user by ID with role 'employee'
        $data = User::role('employee')->findOrFail($id);

        $bookingIds = BookingService::where('employee_id', $id)->pluck('booking_id');

        $statusUpdate = Booking::whereIn('id', $bookingIds)
            ->where('status', '!=', 'completed')
            ->update(['status' => 'cancelled']);

        $data->services()->forceDelete();
        $data->tokens()->delete();

        $data->forceDelete();

        $message = __('messages.delete_form', ['form' => __('employee.singular_title')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }


    public function update_status(Request $request, $id)
    {
        $data = User::role('employee')->findOrFail($id);
        $data->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('branch.status_update')]);
    }

    public function change_password(Request $request)
    {
        $data = $request->all();
        $old_password = $data['old_password'] ?? '';
        $new_password = $data['password'] ?? '';
        $confirm_password = $data['confirm_password'] ?? '';
        $employee_id = $data['employee_id'];

        $user = User::role('employee')->findOrFail($employee_id);

        if (! Hash::check($old_password, $user->password)) {
            return response()->json(['message' => __('messages.old_password_mismatch'), 'errors' => ['old_password' => __('messages.old_password_mismatch')], 'status' => false], 403);
        }

        if ($old_password === $new_password) {
            return response()->json(['message' => __('messages.new_password_mismatch'), 'errors' => ['password' => __('messages.new_password_mismatch')], 'status' => false], 422);
        }

        if ($new_password !== $confirm_password) {
            return response()->json(['message' => __('messages.password_mismatch'), 'errors' => ['confirm_password' => __('messages.password_mismatch')], 'status' => false], 422);
        }
        $request_data = $request->only('password');
        $request_data['password'] = Hash::make($request_data['password']);

        $user->update($request_data);

        $message = __('messages.password_update');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function block_employee(Request $request, User $id)
    {
        $id->update(['is_banned' => $request->status]);

        if ($request->status == 1) {
            $message = __('messages.employee_block');
        } else {
            $message = __('messages.employee_unblock');
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function verify_employee(Request $request, $id)
    {
        $data = User::role('employee')->findOrFail($id);

        $current_time = Carbon::now();

        $data->update(['email_verified_at' => $current_time]);

        return response()->json(['status' => true, 'message' => __('messages.employee_verify')]);
    }

    public function review(Request $request)
    {
        $module_title = __('employee.review_title');

        $module_name = 'review';

        $filter = $request->filter;
        $export_import = true;
        $export_columns = [
            [
                'value' => 'user_id',
                'text' => __('employee.lbl_client_name'),
            ],
            [
                'value' => 'employee_id',
                'text' => __('employee.lbl_emp_name'),
            ],
            [
                'value' => 'review_msg',
                'text' => __('employee.lbl_message'),
            ],
            [
                'value' => 'rating',
                'text' => __('employee.lbl_rating'),
            ],
            [
                'value' => 'updated_at',
                'text' => __('employee.lbl_updated'),
            ],
        ];
        $export_url = route('backend.employees.reviewExport');

        return view('employee::backend.employees.review', compact('module_title', 'module_name', 'filter', 'export_import', 'export_columns', 'export_url'));
    }

    public function reviewExport(Request $request)
    {
        $this->exportClass = '\App\Exports\ReviewsExport';

        return $this->export($request);
    }

    public function review_data(Datatables $datatable, Request $request)
    {
        $query = EmployeeRating::with('user', 'employee');
        if(auth()->user()->hasRole('admin')){
            $query = $query->whereHas('employee', function($q){
                $q->whereHas('mainBranch', function($qry){
                    $qry->where('created_by',auth()->id());
                });
            });
        }
        $filter = $request->filter;
        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        $datatable = $datatable->eloquent($query)
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $data->id . '"  name="datatable_ids[]" value="' . $data->id . '" onclick="dataTableRowCheck(' . $data->id . ')">';
            })

            ->addColumn('action', function ($data) {
                return view('employee::backend.employees.review_action_column', compact('data'));
            })


            ->editColumn('employee_id', function ($data) {
                $Profile_image = $data->employee ? $data->employee->profile_image : default_user_avatar();
                $name = $data->employee ? $data->employee->full_name : default_user_name();
                $email = $data->employee->email ?? '--';
                return view('booking::backend.bookings.datatable.employee_id', compact('Profile_image', 'name', 'email'));
            })
            ->filterColumn('employee_id', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->whereHas('employee', function ($q) use ($keyword) {
                        $q->where('first_name', 'like', '%' . $keyword . '%');
                        $q->orWhere('last_name', 'like', '%' . $keyword . '%');
                        $q->orWhere('email', 'like', '%' . $keyword . '%');
                    });
                }
            })
            ->orderColumn('employee_id', function ($query, $direction) {
                $query->select('employee_rating.*')
                    ->leftJoin('users', 'users.id', '=', 'employee_rating.employee_id')
                    ->orderBy('users.first_name', $direction)
                    ->orderBy('users.last_name', $direction);
            })

            ->editColumn('user_id', function ($data) {
                $Profile_image = $data->user->profile_image ?? default_user_avatar();
                $name = $data->user->full_name ?? default_user_name();
                $email = $data->user->email ?? '--';
                return view('booking::backend.bookings.datatable.user_id', compact('Profile_image', 'name', 'email'));
            })
            ->filterColumn('user_id', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('first_name', 'like', '%' . $keyword . '%');
                        $q->orWhere('last_name', 'like', '%' . $keyword . '%');
                        $q->orWhere('email', 'like', '%' . $keyword . '%');
                    });
                }
            })
            ->orderColumn('user_id', function ($query, $direction) {
                $query->select('employee_rating.*')
                    ->leftJoin('users', 'users.id', '=', 'employee_rating.user_id')
                    ->orderBy('users.first_name', $direction)
                    ->orderBy('users.last_name', $direction);
            })



            ->editColumn('updated_at', function ($data) {
                $module_name = $this->module_name;

                $diff = Carbon::now()->diffInHours($data->updated_at);

                if ($diff < 25) {
                    return $data->created_at->diffForHumans();
                } else {
                    return $data->created_at->isoFormat('llll');
                }
            })
            ->orderColumns(['id'], '-:column $1');

        return $datatable->rawColumns(array_merge(['action', 'image', 'check']))
            ->toJson();
    }

    public function bulk_action_review(Request $request)
    {
        $ids = explode(',', $request->rowIds);
        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'delete':

                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                EmployeeRating::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_review_delete');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('branch.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => __('messages.bulk_update')]);
    }

    public function destroy_review($id)
    {
        $module_title = __('employee.review');

        if (env('IS_DEMO')) {
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
        }

        $data = EmployeeRating::findOrFail($id);

        $data->delete();

        $message = __('messages.delete_form', ['form' => __($module_title)]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }
    public function employeeServices($id)
    {
        // Find the user with the specified ID and role 'employee'
        $user = User::role('employee')->with('services.service')->findOrFail($id);

        $data = [];

        // Check if the user has any services
        if ($user->services->isEmpty()) {
            return response()->json(['data' => [], 'status' => false, 'message' => 'No services found for this employee.'], 404);
        }

        foreach ($user->services as $serviceEmployee) {
            // Assuming 'service' relationship exists on ServiceEmployee
            $data[] = [
                'service_id' => $serviceEmployee->service->id,
                'service_name' => $serviceEmployee->service->name,
                'duration_min' => $serviceEmployee->service->duration_min,
                'service_price' => $serviceEmployee->service->default_price,
            ];
        }

        return response()->json(['data' => $data, 'status' => true], 200);
    }

    public function destroyEmployeeService($employeeId, $serviceId)
    {
        // Remove service assignment for a given employee
        $deleted = ServiceEmployee::where('employee_id', $employeeId)
            ->where('service_id', $serviceId)
            ->delete();

        if ($deleted) {
            return response()->json([
                'status' => true,
                'message' => __('messages.delete_form', ['form' => __('service.singular_title')]),
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => __('messages.something_went_wrong')
        ], 422);
    }

    public function addEmployeeService(Request $request, $employeeId)
    {
        $serviceIds = $request->input('service_ids');
        if (empty($serviceIds) || !is_array($serviceIds)) {
            return response()->json([
                'status' => false,
                'message' => __('messages.validation_error'),
                'errors' => ['service_ids' => __('validation.required', ['attribute' => __('service.plural_title') ?? 'services'])]
            ], 422);
        }

        $serviceIds = array_unique(array_map('intval', $serviceIds));
        $existing = ServiceEmployee::where('employee_id', $employeeId)
            ->whereIn('service_id', $serviceIds)
            ->pluck('service_id')
            ->toArray();

        $toInsert = array_diff($serviceIds, $existing);
        $payload = [];
        foreach ($toInsert as $sid) {
            $payload[] = ['employee_id' => $employeeId, 'service_id' => $sid];
        }
        if (!empty($payload)) {
            ServiceEmployee::insert($payload);
        }

        return response()->json([
            'status' => true,
            'message' => __('messages.create_form', ['form' => __('service.plural_title') ?? 'Services'])
        ]);
    }

}
