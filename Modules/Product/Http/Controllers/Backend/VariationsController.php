<?php

namespace Modules\Product\Http\Controllers\Backend;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Modules\Product\Http\Requests\ProductVariationsRequest;
use Modules\Product\Models\Variations;
use Modules\Product\Models\VariationValue;
use Yajra\DataTables\DataTables;

class VariationsController extends Controller
{

    public function __construct()
    {
        // Page Title
        $this->module_title = __('variations.title');
        // module name
        $this->module_name = 'variations';

        // module icon
        $this->module_icon = 'fa-solid fa-clipboard-list';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);

        $this->middleware(['permission:view_product'])->only('index');
        $this->middleware(['permission:edit_product'])->only('edit', 'update');
        $this->middleware(['permission:add_product'])->only('store');
        $this->middleware(['permission:delete_product'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');
        switch ($actionType) {
            case 'change-status':
                $customer = Variations::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_customer_update');
                break;

            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                Variations::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_customer_delete');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('branch.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => __('messages.bulk_update')]);
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $module_title = __('variations.title');
        $module_action = __('messages.list');
        $columns = CustomFieldGroup::columnJsonValues(new Variations());
        $customefield = CustomField::exportCustomFields(new Variations());

        $export_import = true;
        $export_columns = [
            [
                'value' => 'name',
                'text' => __('messages.name'),
            ],
        ];
        $export_url = route('backend.variations.export');

        return view('product::backend.variations.index_datatable', compact('module_action', 'filter', 'columns', 'customefield', 'export_import', 'export_columns', 'export_url', 'module_title'));
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $query = Variations::with(['values' => function ($q) {
            $q->where('status', 1);
        }])->where('status', 1);

        // Vendor should only see their own variations
        if (auth()->check()) {
            $query->where('created_by', auth()->id());
        }

        $query_data = $query->get();

        $data = [];

        foreach ($query_data as $row) {
            $values = [];

            foreach ($row->values as $value) {
                $values[] = [
                    'id' => $value->id,
                    'name' => $value->name,
                ];
            }

            $data[] = [
                'id' => $row->id,
                'name' => $row->name,
                'values' => $values,
            ];
        }

        return response()->json($data);
    }

    public function index_data(Request $request)
    {
        $query = Variations::query()->where('created_by', auth()->id());

        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $data->id . '"  name="datatable_ids[]" value="' . $data->id . '" onclick="dataTableRowCheck(' . $data->id . ')">';
            })
            ->addColumn('action', function ($data) {
                return view('product::backend.variations.action_column', compact('data'));
            })
            ->editColumn('status', function ($data) {
                $checked = '';
                if ($data->status) {
                    $checked = 'checked="checked"';
                }

                return '
                              <div class="form-check form-switch">
                                  <input type="checkbox" data-url="' . route('backend.variations.update_status', $data->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $data->id . '"  name="status" value="' . $data->id . '" ' . $checked . '>
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
            ->rawColumns(['action', 'status', 'check'])
            ->orderColumns(['id'], '-:column $1')
            ->make(true);
    }

    public function update_status(Request $request, Variations $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => 'Status Updated']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(ProductVariationsRequest $request)
    {
        \Log::debug('Variations.store request start', [
            'input' => $request->all(),
            'values_count' => is_array($request->values ?? null) ? count($request->values) : null,
            'user_id' => auth()->id(),
        ]);
        $data = Variations::create($request->all());

        foreach ($request->values as $key => $value) {
            // normalize and guard
            $val = $value['value'] ?? null;
            $name = $value['name'] ?? null;
            if ((is_null($val) || $val === '') && (is_null($name) || $name === '')) {
                continue; // skip completely empty rows
            }
            // backfill missing fields
            if ($val === null || $val === '') {
                $val = $name;
            }
            if ($name === null || $name === '') {
                $name = $val;
            }
            $value['value'] = $val;
            $value['name'] = $name;
            $value['variation_id'] = $data->id;
            VariationValue::updateOrCreate(['id' => $value['id'] ?? null], $value);
        }

        $message = __('messages.new_variation');
        \Log::debug('Variations.store request end', [
            'variation_id' => $data->id,
            'values_saved' => $data->values()->count(),
        ]);

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
        $data = Variations::with('values')->findOrFail($id);

        return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(ProductVariationsRequest $request, $id)
    {
        \Log::debug('Variations.update request start', [
            'id' => $id,
            'input' => $request->all(),
            'values_count' => is_array($request->values ?? null) ? count($request->values) : null,
            'user_id' => auth()->id(),
        ]);
        $data = Variations::findOrFail($id);

        $data->update($request->all());

        $values = collect($request->values);

        $incomingIds = $values->pluck('id')->filter()->toArray();
        // Delete only values that belong to this variation and are not present anymore
        VariationValue::where('variation_id', $data->id)
            ->whereNotIn('id', $incomingIds)
            ->delete();

        foreach ($values as $key => $value) {
            $val = $value['value'] ?? null;
            $name = $value['name'] ?? null;
            if ((is_null($val) || $val === '') && (is_null($name) || $name === '')) {
                continue; // skip empty rows
            }
            if ($val === null || $val === '') {
                $val = $name;
            }
            if ($name === null || $name === '') {
                $name = $val;
            }
            $value['value'] = $val;
            $value['name'] = $name;
            $value['variation_id'] = $data->id;
            VariationValue::updateOrCreate(['id' => $value['id'] ?? null], $value);
        }

        $message = __('messages.update_variation');
        \Log::debug('Variations.update request end', [
            'variation_id' => $data->id,
            'values_saved' => $data->values()->count(),
        ]);

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
        $data = Variations::findOrFail($id);

        $data->values()->delete();

        $data->delete();

        $message = __('messages.delete_variation');

        return response()->json(['message' => $message, 'status' => true], 200);
    }
}
