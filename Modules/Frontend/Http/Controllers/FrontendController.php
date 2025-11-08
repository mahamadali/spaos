<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Faq;
use App\Models\WebsiteFeature;
use App\Models\WebsiteHomepage;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Employee\Models\EmployeeRating;
use Modules\Page\Models\Page;
use Modules\Service\Models\Service;
use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Models\Plan;
use App\Models\User;
use Modules\Subscriptions\Trait\SubscriptionTrait;
use Modules\Subscriptions\Transformers\SubscriptionResource;

class FrontendController extends Controller
{
    use SubscriptionTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user() && auth()->user()->hasRole('admin')) {
            return redirect()->route('app.home');
        }

        // Fetch the required data
        $features = WebsiteFeature::take(4)->get();
        $nav_feature_list = WebsiteFeature::all();

        $plan = Plan::with('features')->whereNotIn('name', ['Free'])->first();
        // Fetch only active plans and filter out inactive ones
        $data['plan'] = Plan::with('features')
            ->where('status', 1)
            ->get()
            ->filter(function ($plan) {
                return $plan->status == 1;
            })
            ->values();  // Reset array keys after filtering

        $activeSubscriptions = Subscription::where('user_id', auth()->id())
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->orderBy('id', 'desc')
            ->first();
        $currentPlanId = $activeSubscriptions ? $activeSubscriptions->plan_id : null;
        $data['currentPlanId'] = $currentPlanId;
        $subscriptions = Subscription::where('user_id', auth()->id())
            ->with('subscription_transaction')
            ->where('end_date', '<', now())
            ->get();

        $data['currentPlanId'] = $currentPlanId;

        $homepages = WebsiteHomepage::all();
        $homepages = $homepages->map(function ($homepage) {
            $value = json_decode($homepage->value, true);
            $homepage->value = $value !== null ? $value : $homepage->value;
            return $homepage;
        });
        $reviews = EmployeeRating::with('user')->take(10)->get();
        $blogs = Blog::with('user')->where('created_by', 1)->take(4)->get();
        $data['service'] = Service::with('employees')->where('status', 1)->first();
        return view('frontend::index', compact('features', 'plan', 'data', 'homepages', 'reviews', 'blogs', 'nav_feature_list'));
    }


    public function feature()
    {
        $features = WebsiteFeature::all();
        $data = [
            'features' => $features,
            'bread_crumb' => "Features",
        ];
        return view('frontend::feature', compact('data'));
    }
    public function resource()
    {
        return view('frontend::resource');
    }
    public function allfeature()
    {
        return view('frontend::allfeatures');
    }
    public function aboutus()
    {
        $data = WebsiteSetting::first();
        $aboutUs = isset($data) ? $data->about_us : null;
        $data = [
            'about_us' => $aboutUs,
            'bread_crumb' => "About Us",
        ];
        return view('frontend::aboutus', compact('data'));
    }

    public function pageSlugs($slug)
    {
        $page = Page::where('slug', $slug)->where('status', 1)->first();

        if (!$page) {
            abort(404, 'Page not found');
        }

        $data = [
            'page_title' => $page->name,
            'page_content' => $page->description,
        ];

        return view('frontend::page', compact('data'));
    }

    public function contactus()
    {
        $data = WebsiteSetting::first();
        $contactUs = isset($data) ? $data->contactUs : null;
        $superadminEmail = User::where('user_type', 'super admin')->first()->email ?? 'admin@salon.com';
        $data = [
            'contact_us' => $contactUs,
            'bread_crumb' => "Contact us",
            'superadmin_email' => $superadminEmail,
        ];
        return view('frontend::contactus', compact('data'));
    }

    public function faqs()
    {
        $faqs = Faq::where('status', 1)->where('created_by', 1)->get();
        $data = [
            'faqs' => $faqs,
            'bread_crumb' => "FAQs",
        ];
        return view('frontend::faq', compact('data'));
    }

    public function blogs()
    {
        $blogs = Blog::with('user')->where('status', 1)->where('created_by', 1)->get();
        $data = [
            'blogs' => $blogs,
            'bread_crumb' => "Blogs",
        ];
        return view('frontend::blog', compact('data'));
    }
    public function author_blogs($author_id)
    {

        $blogs = Blog::with('user')->where('auther_id', $author_id)->where('created_by', 1)->get();
        $data = [
            'blogs' => $blogs,
            'bread_crumb' => "Blogs",
        ];
        return view('frontend::blog', compact('blogs', 'data'));
    }
    public function PaymentHistory()
    {
        $subscriptions = Subscription::where('user_id', auth()->id())
            ->with('plan', 'subscription_transaction')
            ->get();

        $activeSubscriptions =  Subscription::where('user_id', auth()->id())
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->orderBy('id', 'desc')
            ->first();


        return view('frontend::payment_history', compact('activeSubscriptions', 'subscriptions'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('frontend::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('frontend::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('frontend::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
    public function blogDetail($id)
    {
        $blog = Blog::with('user')->find($id);

        $previousBlog = Blog::where('id', '<', $id)
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->first();

        // Get next blog from all blogs
        $nextBlog = Blog::where('id', '>', $id)
            ->where('status', 1)
            ->orderBy('id', 'asc')
            ->first();

        // Get 3 related blogs excluding current blog
        $relatedBlogs = Blog::where('id', '!=', $id)
            ->where('status', 1)
            ->take(3)
            ->get();

        $data = "Blog Detail";
        return view('frontend::blogdetail', compact('blog', 'previousBlog', 'nextBlog', 'data', 'relatedBlogs'));
    }

    public function subscriptiondetail()
    {
        $user = auth()->user();

        $subscriptions = Subscription::with('plan')->where('user_id', auth()->id())
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->first();

        $planDetails = null;
        if ($subscriptions) {
            $planDetails = json_decode($subscriptions->plan_details);
        }


        return view('frontend::cancel_subscription', compact('subscriptions', 'user', 'planDetails'));
    }

    public function cancelSubscription(Request $request)
    {
        try {
            $planId = $request->input('plan_id');
            $cancelsubscriptions = Subscription::where('user_id', auth()->id())
                ->where('id', $request->id)
                ->where('status', 'active')
                ->update(['status' => 'cancel']);

            $cancelsubscription = Subscription::where('user_id', auth()->id())->where('id', $request->id)->first();
            $response = new SubscriptionResource($cancelsubscription);


            $user = User::where('id', auth()->id())->first();

            $user->update(['is_subscribe' => 0]);


            try {
                $type = 'cancel_subscription';
                $messageTemplate = 'User [[plan_id]] has been cancel subscription.';
                $notify_message = str_replace('[[plan_id]]', $cancelsubscription->name, $messageTemplate);
                $this->sendNotificationOnsubscription($type, $notify_message, $response);
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }


            return response()->json(['success' => true, 'message' => __('messages.cancel_subscription_msg')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
