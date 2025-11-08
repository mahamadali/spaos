<?php

namespace Modules\VendorWebsite\Http\Controllers\Backend;


use App\Http\Controllers\Controller;
use Yajra\DataTables\DataTables;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Blog;


class BlogController extends Controller
{
    public function blogsList()
    {
        $blogs = Blog::with('user')->where('created_by', session('current_vendor_id'))->latest()->get();

        return view('vendorwebsite::blog', compact('blogs'));
    }

    public function index_data(Request $request)
    {

        $query = Blog::with('user')->where('status', 1)->where('created_by', session('current_vendor_id'));

        return \Yajra\DataTables\DataTables::of($query)
            ->addColumn('card', function ($blog) {
                return view('vendorwebsite::components.card.blog_card', compact('blog'))->render();
            })
            ->addColumn('title', function ($blog) {
                return $blog->title;
            })
            ->filterColumn('title', function ($query, $keyword) {
                $query->where('title', 'like', "%{$keyword}%");
            })
            ->rawColumns(['card'])
            ->make(true);
    }

    public function blogDetails($id)
    {
        $blog = Blog::with('user')->where('created_by', session('current_vendor_id'))->where('id', $id)->firstOrFail();

        $previous_blog = Blog::where('created_by', session('current_vendor_id'))->where('id', '<', $id)->latest()->first();

        $next_blog = Blog::where('created_by', session('current_vendor_id'))->where('id', '>', $id)->oldest()->first();

        $related_blogs = Blog::where('created_by', session('current_vendor_id'))->where('id', '!=', $id)->latest()->take(6)->get();

        return view('vendorwebsite::blog-details', compact('blog', 'previous_blog', 'next_blog', 'related_blogs'));
    }
}
