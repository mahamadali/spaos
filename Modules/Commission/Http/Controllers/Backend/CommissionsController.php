<?php

namespace Modules\Commission\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Commission\Models\Commission;

class CommissionsController extends Controller
{

    public function __construct()
    {
        // Page Title
        $this->module_title = __('messages.commissions');

        // module name
        $this->module_name = 'commissions';

        // directory path of the module
        $this->module_path = 'commission::backend';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => 'fa-regular fa-sun',
            'module_name' => $this->module_name,
            'module_path' => $this->module_path,
        ]);

        $this->middleware(['permission:view_commission'])->only('index');
        $this->middleware(['permission:edit_commission'])->only('edit', 'update');
        $this->middleware(['permission:add_commission'])->only('store');
        $this->middleware(['permission:delete_commission'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $module_action = __('messages.list');

        return view('commission::backend.commissions.index_datatable', compact('module_action'));
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $term = trim($request->q);
        $user = User::find(auth()->user()->id);

        $query_data = Commission::where('status', 1)
        ->when(!empty($term), function ($q) use ($term) {
            $q->where('name', 'LIKE', "%$term%");
        })
        ->when($user->hasRole('manager'), function ($q) use ($user) {
            $q->where('created_by', $user->created_by);
        }, function ($q) use ($user) {
            $q->where('created_by', $user->id);
        })
        ->get();
        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'name' => $row->title,
                'type' => $row->commission_type,
                'value' => $row->commission_value,

            ];
        }

        return response()->json($data);
    }

    public function index_data()
    {
        $data = Commission::where('created_by', auth()->user()->id)->get();

        return response()->json(['data' => $data, 'status' => true, 'message' => __('messages.custom_field')]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $module_action = __('messages.create');

        return view('commission::backend.commissions.create', compact('module_action'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        
        // Check for duplicate commission title for this vendor
        $existingCommission = Commission::where('title', $request->title)
            ->where('created_by', auth()->id())
            ->first();
        
        if ($existingCommission) {
            return response()->json([
                'message' => __('messages.commission_title_already_exists'),
                'status' => false
            ], 422);
        }
        
        $data = Commission::create($data);

        $message = __('messages.new') . ' ' . __('messages.commission') . ' ' . __('messages.added');

        return response()->json(['message' => $message, 'status' => true], 200);
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

        $data = Commission::findOrFail($id);

        return view('commission::backend.commissions.show', compact('module_action', "$data"));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $data = Commission::findOrFail($id);

        return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $data = Commission::findOrFail($id);

        $data->update($request->all());

        $message = __('messages.commission') . ' ' . __('messages.updated_successfully');

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
        $data = Commission::findOrFail($id);

        $data->delete();

        $message = __('messages.commission') . ' ' . __('messages.deleted_successfully');

        return response()->json(['message' => $message, 'status' => true], 200);

    }



}
