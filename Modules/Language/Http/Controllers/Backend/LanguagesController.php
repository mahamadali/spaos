<?php

namespace Modules\Language\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\Language\Models\Language;

class LanguagesController extends Controller
{

    public function __construct()
    {
        // Page Title
        $this->module_title = __('messages.languages');

        // module name
        $this->module_name = 'languages';

        // directory path of the module
        $this->module_path = 'language::backend';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => 'fa-regular fa-sun',
            'module_name' => $this->module_name,
            'module_path' => $this->module_path,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $module_action = __('messages.list');

        return view('language::backend.languages.index_datatable', compact('module_action'));
    }

    /**
     * Select Options for Select 2 Request/ Response.
     *
     * @return Response
     */
    public function index_list(Request $request)
    {
        $query_data = Config::get('app.available_locales');

        $data = [];

        foreach ($query_data as $key => $value) {
            $data[] = [
                'id' => $key,
                'name' => $value,
            ];
        }

        return response()->json($data);
    }

    public function array_list(Request $request)
    {
        $data = $request->all();
        $language = $data['language_id'];
    
        $langFolderPath = base_path("lang/$language");
        $filePaths = \File::files($langFolderPath);
    
        $user = auth()->user();
    
        $unnecessaryFiles = [
            'booking', 'branch', 'brand', 'category', 'city', 'commission',
            'country', 'customer', 'earning', 'employee', 'export', 'location',
            'logistic_zone', 'logistics', 'menu', 'menu_builder', 'order_report',
            'package', 'product', 'product_tax', 'quick_booking', 'report',
            'service', 'service_package', 'setting_booking_page',
            'setting_business_hours', 'setting_integration_page',
            'setting_invoice', 'setting_meta_page', 'setting_mobile_page',
            'setting_sidebar', 'setting_social_page', 'slider', 'state',
            'sub_categories', 'tags', 'units', 'variations'
        ];
    
        $fileName = [];
    
        foreach ($filePaths as $filePath) {
            $name = pathinfo($filePath, PATHINFO_FILENAME);
    
            if ($user && $user->user_type === 'super admin') {
                if (!in_array(strtolower($name), $unnecessaryFiles)) {
                    $fileName[] = [
                        'id' => $name,
                        'name' => $name,
                    ];
                }
            } else {
                $fileName[] = [
                    'id' => $name,
                    'name' => $name,
                ];
            }
        }
    
        return response()->json($fileName);
    }

    public function get_file_data(Request $request)
    {
        $data = $request->all();

        $file_id = $data['file_id'];
        $language = $data['language_id'];



        $langFolderPath = base_path("lang/{$language}/{$file_id}.php");
        $fileContent = file_get_contents($langFolderPath);

        $langArray = include $langFolderPath;

        foreach ($langArray as $key => $value) {
            $file_value[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        $lang = Language::where(['language' => $language, 'file' => $file_id])->get();

        foreach ($file_value as $key => $value) {
            $file_value[$key]['value'] = $lang->where('key', $value['key'])->last()->value ?? $value['value'];
        }

        return response()->json($file_value);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $module_action = __('messages.create');

        return view('language::backend.languages.create', compact('module_action'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $data_list = $request->all();

        $file_id = $data_list['file_id'];
        $language = $data_list['language_id'];

        $langFolderPath = base_path("lang/{$language}/{$file_id}.php");

        $fileContent = file_get_contents($langFolderPath);

        foreach ($data_list['data'] as $data) {
            $key = $data['key'];
            $value = $data['value'];
            data_set($fileContent, $key, $value);
            $data = Language::updateOrCreate(['key' => $data['key'], 'language' => $data['language']], [
                'key' => $data['key'],
                'value' => $data['value'],
                'language' => $data['language'],
                'file' => $data['file'],
            ]);
        }

        

        $message = __('messages.language_update');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $module_action = __('messages.edit');

        $data = Language::findOrFail($id);

        if (request()->wantsJson()) {
            return response()->json(['data' => $$module_name_singular, 'status' => true]);
        } else {
            return view('language::backend.languages.edit', compact('module_action', "$data"));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $data = Language::findOrFail($id);

        $data->update($request->all());

        $message = __('messages.language_update');

        if (request()->wantsJson()) {
            return response()->json(['message' => $message, 'status' => true], 200);
        } else {
            flash("<i class='fas fa-check'></i> $message")->success()->important();

            return redirect()->route('backend.languages.show', $data->id);
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
        $data = Language::findOrFail($id);

        $data->delete();

        $message = __('messages.language_deleted');

        if (request()->wantsJson()) {
            return response()->json(['message' => $message, 'status' => true], 200);
        } else {
            flash('<i class="fas fa-check"></i> '.label_case($this->module_name).' Deleted Successfully!')->success()->important();

            return redirect("app//notification/$this->module_name");
        }
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

        $data = Language::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate();

        return view('language::backend.languages.trash', compact("$data", 'module_name_singular', 'module_action'));
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

        $data = Language::withTrashed()->find($id);
        $data->restore();

        $message = __('messages.language_data');

        return response()->json(['message' => $message, 'status' => true], 200);
    }
}
