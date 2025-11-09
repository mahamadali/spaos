<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Artisan;

class BlogController extends Controller
{
    public $module_title;
    public $module_name;
    public $module_icon;

    public function __construct()
    {
        // Page Title
        $this->module_title = __('messages.blogs');

        // module name
        $this->module_name = 'blog';

        // module icon
        $this->module_icon = 'fa-solid fa-blog';

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
        $module_action = __('messages.blogs');
        $module_title = __('messages.blogs');
        return view('blogs.index', compact('module_action', 'module_title'));
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        // dd('index_data');
        $query = Blog::with('user')->where('created_by', auth()->user()->id);

        return $datatable->eloquent($query)
            ->addColumn('image', function ($data) {
                // Check if the image exists and return an img tag or a placeholder
                return $data->image ? asset($data->image) : null;
            })
            ->editColumn('title', function ($data) {
                return $this->formatTextWithShowMore($data->title, $data->id, 'title');
            })
            ->editColumn('auther_id', function ($data) {
                if (empty($data->user))
                    return '-';

                return $data->user->first_name . ' ' . $data->user->last_name;
            })
            ->filterColumn('auther_id', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('first_name', 'like', '%' . $keyword . '%')
                        ->orWhere('last_name', 'like', '%' . $keyword . '%');
                });
            })
            ->editColumn('status', function ($row) {
                $checked = '';
                if ($row->status) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="' . route('backend.blog.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $row->id . '"  name="status" value="' . $row->id . '" ' . $checked . '>
                    </div>
                ';
            })
            ->rawColumns(['title', 'status'])
            ->orderColumns(['id'], '-:column $1')
            ->toJson();
    }

    public function create()
    {
        // dd('create');
        $module_title = __('messages.blogs');
        $module_action = __('messages.create');
        $currentUser = auth()->user();
        if ($currentUser && isset($currentUser->user_type) && $currentUser->user_type === 'admin') {
            $users = User::where('id', $currentUser->id)->get();
        } else {
            // Super admin (or others): show all admins (vendor admins)
            $users = User::where('user_type', 'admin')->get();
        }

        return view('blogs.create', compact('module_action', 'users', 'module_title'));
    }

    public function store(Request $request)
    {
        \Log::info('Blog store method called', [
            'request_data' => $request->all(),
            'request_method' => $request->method(),
            'request_url' => $request->url()
        ]);

        // Validate the request
        try {
            $request->validate([
                'title' => 'required|string|max:65535',
                'description' => 'required|string',
                'auther_id' => 'required|exists:users,id',
                'status' => 'nullable',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);
            \Log::info('Validation passed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        \Log::info('Validation passed, proceeding with blog creation');

        // Prevent duplicate submissions by checking for recent identical submissions
        if (!$request->id) { // Only check for new blog creation, not updates
            $recentBlog = Blog::where('title', $request->title)
                ->where('auther_id', $request->auther_id)
                ->where('created_at', '>', now()->subMinutes(1)) // Check within last 1 minute only
                ->first();

            if ($recentBlog) {
                \Log::info('Duplicate submission detected', ['recent_blog_id' => $recentBlog->id]);
                return redirect()->route('backend.blog.index')
                    ->with('warning', __('messages.duplicate_submission_prevented'));
            }
        }

        \Log::info('Duplicate check passed, proceeding with save');

        $blog = Blog::find($request->id);
        $blog = ($blog) ? $blog : new Blog;

        // Handle image upload/update
        if($request->hasFile('image'))
        {
            $image = $request->file('image');
            $img_name = 'blog_img'.rand(100000, 999999).time().$image->getClientOriginalName();
            $img_path = 'blog/images/'.$img_name;
            $image->move(public_path('blog/images'),$img_name);

            // Update image path only if new image is uploaded
            $blog->image = $img_path;
        }
        // If no new image is uploaded and this is an update, keep the existing image
        // If this is a new blog and no image is uploaded, image will remain null

        $blog->title = $request->title;
        $blog->auther_id = $request->auther_id;
        $blog->status = $request->has('status') ? 1 : 0;
        $blog->description = $request->description;

        \Log::info('About to save blog', [
            'blog_data' => $blog->toArray(),
            'is_new' => $blog->id === null,
            'title_length' => strlen($request->title)
        ]);

        try {
            $result = $blog->save();
            \Log::info('Blog save result', [
                'result' => $result,
                'blog_id' => $blog->id,
                'saved_at' => $blog->updated_at
            ]);
        } catch (\Exception $e) {
            \Log::error('Blog save failed', [
                'error' => $e->getMessage(),
                'blog_data' => $blog->toArray()
            ]);
            return redirect()->back()->with('error', 'Failed to save blog: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('backend.blog.index')->with('success', ($request->id) ?  __('messages.blogs') . ' ' . __('messages.updated_successfully') :  __('messages.blogs') . ' ' . __('messages.added_successfully'));
    }

    public function edit($id)
    {
        $module_title = __('messages.blogs');
        $module_action = __('messages.edit');
        $blog = Blog::find($id);
        $currentUser = auth()->user();
        if ($currentUser && isset($currentUser->user_type) && $currentUser->user_type === 'admin') {
            $users = User::where('id', $currentUser->id)->get();
        } else {
            $users = User::where('user_type', 'admin')->get();
        }

        return view('blogs.create', compact('module_action', 'blog', 'users', 'module_title'));
    }

    public function delete($id)
    {
        $blog = Blog::find($id);
        $blog->delete();

        $message = __('messages.delete_form', ['form' => __('messages.blogs')]);

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function updateStatus(Request $request, Blog $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('branch.status_update')]);
    }
    public function migration()
    {
        set_time_limit(0);
        Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
        return redirect('login');
    }

    private function formatTextWithShowMore($text, $id, $type)
    {
        $formattedText = $this->formatTextWithLineBreaks($text);

        if (strlen($text) <= 150) {
            return '<div class="blog-text-' . $type . '-' . $id . '">' . $formattedText . '</div>';
        }

        $shortText = $this->formatTextWithLineBreaks(substr($text, 0, 150)) . '...';
        $fullText = $this->formatTextWithLineBreaks($text);

        return '
        <div class="blog-text-' . $type . '-' . $id . '">
            <span class="blog-short-' . $type . '-' . $id . '">' . $shortText . '</span>
            <span class="blog-full-' . $type . '-' . $id . '" style="display: none;">' . $fullText . '</span>
            <br>
            <a href="#" class="btn btn-link btn-sm p-0 show-more-link" data-id="' . $id . '" data-type="' . $type . '" onclick="toggleBlogText(' . $id . ', \'' . $type . '\'); return false;">
                <small>Read more</small>
            </a>
        </div>';
    }

    private function formatTextWithLineBreaks($text)
    {
        // Break text into lines of approximately 50 characters, respecting word boundaries
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';
        $targetLength = 50;
        $tolerance = 10; // Allow 10 characters variation

        foreach ($words as $word) {
            $potentialLine = $currentLine . ($currentLine === '' ? '' : ' ') . $word;

            // If adding this word would exceed target + tolerance, start a new line
            if (strlen($potentialLine) > $targetLength + $tolerance && $currentLine !== '') {
                $lines[] = trim($currentLine);
                $currentLine = $word;
            } else {
                $currentLine = $potentialLine;
            }
        }

        // Add the last line if it's not empty
        if ($currentLine !== '') {
            $lines[] = trim($currentLine);
        }

        // Balance the lines for better visual appearance
        $balancedLines = $this->balanceLines($lines, $targetLength);

        return implode('<br>', array_map('e', $balancedLines));
    }

    private function balanceLines($lines, $targetLength)
    {
        if (count($lines) <= 1) {
            return $lines;
        }

        $balanced = [];
        $i = 0;

        while ($i < count($lines)) {
            $currentLine = $lines[$i];

            // If current line is too short and next line exists, try to balance
            if (strlen($currentLine) < $targetLength - 15 && $i + 1 < count($lines)) {
                $nextLine = $lines[$i + 1];
                $combined = $currentLine . ' ' . $nextLine;

                // If combined line is reasonable length, merge them
                if (strlen($combined) <= $targetLength + 10) {
                    $balanced[] = $combined;
                    $i += 2; // Skip next line since we merged it
                } else {
                    $balanced[] = $currentLine;
                    $i++;
                }
            } else {
                $balanced[] = $currentLine;
                $i++;
            }
        }

        return $balanced;
    }
}
