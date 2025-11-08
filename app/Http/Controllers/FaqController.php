<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class FaqController extends Controller
{
    public $module_title;
    public $module_name;
    public $module_icon;

    public function __construct()
    {
        // Page Title
        $this->module_title = __('messages.faqs');

        // module name
        $this->module_name = 'faq';

        // module icon
        $this->module_icon = 'fa-solid fa-question-circle';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index()
    {
        $module_action = __('messages.faqs');
        $module_title = __('messages.faqs');
        return view('faqs.index', compact('module_action', 'module_title'));
    }

    public function index_data(Datatables $datatable)
    {
        $query = Faq::where('created_by', auth()->user()->id);

        return $datatable->eloquent($query)
            ->editColumn('question', function ($row) {
                return $this->formatTextWithShowMore($row->question, $row->id, 'question');
            })
            ->editColumn('answer', function ($row) {
                return $this->formatTextWithShowMore($row->answer, $row->id, 'answer');
            })
            ->editColumn('status', function ($row) {
                $checked = '';
                if ($row->status) {
                    $checked = 'checked="checked"';
                }

                return '
                <div class="form-check form-switch ">
                    <input type="checkbox" data-url="' . route('backend.faq.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $row->id . '"  name="status" value="' . $row->id . '" ' . $checked . '>
                </div>
            ';
            })
            ->rawColumns(['question', 'answer', 'status'])
            ->orderColumns(['id'], '-:column $1')
            ->toJson();
    }

    private function formatTextWithShowMore($text, $id, $type)
    {
        $formattedText = $this->formatTextWithLineBreaks($text);

        if (strlen($text) <= 150) {
            return '<div class="faq-text-' . $type . '-' . $id . '">' . $formattedText . '</div>';
        }

        $shortText = $this->formatTextWithLineBreaks(substr($text, 0, 150)) . '...';
        $fullText = $this->formatTextWithLineBreaks($text);

        return '
        <div class="faq-text-' . $type . '-' . $id . '">
            <span class="faq-short-' . $type . '-' . $id . '">' . $shortText . '</span>
            <span class="faq-full-' . $type . '-' . $id . '" style="display: none;">' . $fullText . '</span>
            <br>
            <a href="#" class="btn btn-link btn-sm p-0 show-more-link" data-id="' . $id . '" data-type="' . $type . '" onclick="toggleFaqText(' . $id . ', \'' . $type . '\'); return false;">
                <small>Read more</small>
            </a>
        </div>';
    }

    private function formatTextWithLineBreaks($text)
    {
        // Split text into chunks of 50 characters and join with <br>
        $chunks = str_split($text, 50);
        return implode('<br>', array_map('e', $chunks));
    }

    public function create()
    {
        $module_action = __('messages.create');
        $module_title = __('messages.faqs');
        return view('faqs.create', compact('module_action', 'module_title'));
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'question' => 'required|string|max:500', // 500 character limit
            'answer' => 'required|string|max:500',
            'status' => 'nullable|boolean'
        ]);

        $faq = Faq::find($request->id);
        $faq = ($faq) ? $faq : new Faq;

        $faq->question = $request->question;
        $faq->answer = $request->answer;
        $faq->status = $request->status ? 1 : 0;
        $faq->save();

        return redirect()->route('backend.faq.index')->with('success', ($request->id) ?  __('messages.faqs') . ' ' . __('messages.updated_successfully') :  __('messages.faqs') . ' ' . __('messages.added_successfully'));
    }

    public function edit($id)
    {
        $module_title = __('messages.faqs');
        $module_action = __('messages.edit');
        $faq = Faq::find($id);

        return view('faqs.create', compact('module_action', 'faq', 'module_title'));
    }

    public function delete($id)
    {
        $faq = Faq::find($id);
        $faq->delete();


        $message = __('messages.delete_form', ['form' => __('messages.faqs')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function updateStatus(Request $request, Faq $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('branch.status_update')]);
    }
}
