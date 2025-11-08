<?php

namespace Modules\Page\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Page\Models\Page;
use Yajra\DataTables\DataTables;

class PagesController extends Controller
{

    public function __construct()
    {
        // Page Title
        $this->module_title = __('page.title');

        // module name
        $this->module_name = 'pages';

        // directory path of the module
        $this->module_path = 'page::backend';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => 'fa-regular fa-sun',
            'module_name' => $this->module_name,
            'module_path' => $this->module_path,
        ]);
        $this->middleware(['permission:view_page'])->only('index');
        $this->middleware(['permission:edit_page'])->only('edit', 'update');
        $this->middleware(['permission:add_page'])->only('store');
        $this->middleware(['permission:delete_page'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $module_action = __('messages.list');
        $module_title = __('page.title');
        $filter = [
            'status' => $request->status,
        ];

        return view('page::backend.pages.index_datatable', compact('module_action', 'filter', 'module_title'));
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $term = trim($request->q);

        if (empty($term)) {
            return response()->json([]);
        }

        $query_data = Page::where('name', 'LIKE', "%$term%")->orWhere('slug', 'LIKE', "%$term%")->limit(7)->get();

        $data = [];

        foreach ($query_data as $row) {
            $data[] = [
                'id' => $row->id,
                'text' => $row->name . ' (Slug: ' . $row->slug . ')',
            ];
        }

        return response()->json($data);
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $query = Page::query();

        if (auth()->user()->hasRole('admin')) {
            $query->where('created_by', auth()->id());
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
                return view('page::backend.pages.action_column', compact('data'));
            })
            ->addColumn('description', function ($data) {
                $plainText = strip_tags($data->description); // Remove HTML tags
                return \Illuminate\Support\Str::limit($plainText, 30, '...');
            })
            ->editColumn('status', function ($row) {
                $checked = '';
                if ($row->status) {
                    $checked = 'checked="checked"';
                }

                return '
                <div class="form-check form-switch ">
                    <input type="checkbox" data-url="' . route('backend.pages.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $row->id . '"  name="status" value="' . $row->id . '" ' . $checked . '>
                </div>
               ';
            })

            ->editColumn('show_for_booking', function ($row) {
                $checked = '';
                if ($row->show_for_booking) {
                    $checked = 'checked="checked"';
                }

                return '
                <div class="form-check form-switch ">
                    <input type="checkbox" data-url="' . route('backend.pages.update_show_for_booking', $row->id) . '" data-token="' . csrf_token() . '" class="switch-show-for-booking form-check-input"  id="show-for-booking-' . $row->id . '"  name="show_for_booking" value="' . $row->id . '" ' . $checked . '>
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
            ->orderColumns(['id'], '-:column $1');

        return $datatable->rawColumns(array_merge(['action', 'status', 'check', 'show_for_booking']))
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

        return view('page::backend.pages.create', compact('module_action'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^\S+.*\S+$|^\S+$/', // Must not be only spaces
        ], [
            'name.required' => 'Page title is required.',
            'name.regex' => 'Page title cannot contain only spaces.',
        ]);

        $data = $request->all();

        Page::create($data);

        $message = __('messages.create_form', ['form' => __($this->module_title)]);

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

        $data = Page::findOrFail($id);

        return view('page::backend.pages.show', compact('module_action', "$data"));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $data = Page::findOrFail($id);

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
        $request->validate([
            'name' => 'required|string|max:255|regex:/^\S+.*\S+$|^\S+$/', // Must not be only spaces
        ], [
            'name.required' => 'Page title is required.',
            'name.regex' => 'Page title cannot contain only spaces.',
        ]);

        $data = Page::findOrFail($id);

        $data->update($request->all());

        $message = __('messages.update_form', ['form' => __($this->module_title)]);

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
        $data = Page::findOrFail($id);

        $data->delete();

        $message = __('messages.delete_form', ['form' => __($this->module_title)]);

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
        $module_name = $this->module_name;

        $module_name_singular = Str::singular($module_name);

        $module_action = __('messages.trash');

        $data = Page::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate();

        return view('page::backend.pages.trash', compact("$data", 'module_name_singular', 'module_action'));
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
        $module_action = __('messages.restore');

        $data = Page::withTrashed()->find($id);
        $data->restore();

        $message = Str::singular(Pages) . __('messages.data_restore');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function update_status(Request $request, Page $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('branch.status_update')]);
    }

    public function update_show_for_booking(Request $request, Page $id)
    {
        $showForBooking = $request->input('show_for_booking', 0);

        // If setting this page to show_for_booking = 1
        if ($showForBooking == 1) {
            // First, set all other pages with same created_by to show_for_booking = 0
            Page::where('created_by', $id->created_by)
                ->where('id', '!=', $id->id)
                ->update(['show_for_booking' => 0]);
        }

        $message = $showForBooking
            ? __('Page set as default for booking successfully.')
            : __('Page removed from default booking successfully.');



        // Then update the current page
        $id->update(['show_for_booking' => $showForBooking]);


        return response()->json(['status' => true, 'message' => $message]);
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                $branches = Page::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_category_update');
                break;

            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                Page::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_category_update');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('branch.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => __('messages.bulk_update')]);
    }
}
