<?php

namespace App\Http\Controllers;

use App\Models\WebsiteFeature;
use App\Models\WebsiteHomepage;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;

class WebsiteSettingController extends Controller
{
    public $module_title;
    public $module_name;
    public $module_icon;

    public function __construct()
    {
        // Page Title
        $this->module_title = __('messages.landing_page_settings');

        // module name
        $this->module_name = 'landing-page-setting';

        // module icon
        $this->module_icon = 'icon fa-solid fa-cogs';

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
        $module_action = __('messages.landing_page_settings');
        $module_title = __('messages.landing_page_settings');
        $web_setting = WebsiteSetting::first();
        $features = ($web_setting) ? $web_setting->features->sortByDesc('id') : [];
        $homepages =  ($web_setting) ? $web_setting->homepages : [];
        $homepages = $homepages->map(function ($homepage) {
            $value = json_decode($homepage->value, true);
            $homepage->value = $value !== null ? $value : $homepage->value;
            return $homepage;
        });
        $baseurl=config('app.url');
        return view('website-settings.index', compact('module_action','web_setting','features','homepages','baseurl','module_title'));
    }

    public function store(Request $request)
    {

        $homepage_keys = [
            'banner_title',
            'banner_subtitle',
            'banner_badge_text',
            'banner_link',
            'about_title',
            'about_subtitle',
            'about_description',
            'video_type',
            'video_url',
            'chooseUs_title',
            'chooseUs_subtitle',
            'choose_us_feature_list',
            'chooseUs_description',
        ];


            $homepage_validation_rules = array_fill_keys($homepage_keys, 'nullable');
            $request->validate(array_merge([
                'website_title' => 'nullable|string|min:2|max:50',
                'website_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'about_us' => 'nullable|string',
                'feature_title' => 'nullable|array',
                'feature_title.*' => 'nullable|string|max:255',
                'feature_description' => 'nullable|array',
                'feature_description.*' => 'nullable|string|max:250',
                'feature_image' => 'nullable|array',
                'feature_image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'banner_image1' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'banner_image2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'banner_image3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'video_img' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'chooseUs_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'choose_us_feature_list' => 'nullable|array',
            ], $homepage_validation_rules));


            $web_setting = WebsiteSetting::firstOrCreate([], []);


            foreach ($homepage_keys as $key) {
                $value = $request->input($key, null);
                if ($key == 'choose_us_feature_list' && is_array($value)) {
                    $featureIds = $value;
                    $features = WebsiteFeature::whereIn('id', $featureIds)->get();
                    $value = $features->map(function ($feature) {
                        return [
                            'id' => $feature->id,
                            'title' => $feature->title,
                        ];
                    });
                }

                WebsiteHomepage::updateOrCreate(
                    [
                        'key' => $key,
                        'website_setting_id' => $web_setting->id,
                    ],
                    [
                        'value' => json_encode($value),  // Save as JSON
                    ]
                );
            }


            function uploadImage($file, $folder, $prefix)
            {
                $img_name = $prefix . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $img_path = "$folder/$img_name";
                $file->move(public_path($folder), $img_name);
                return $img_path;
            }


            $banner_keys = [
                'banner_image1' => 'banner_image1',
                'banner_image2' => 'banner_image2',
                'banner_image3' => 'banner_image3',
                'video_img' => 'video_img',
                'chooseUs_image' => 'chooseUs_image',
            ];

            foreach ($banner_keys as $key => $prefix) {
                if ($request->hasFile($key)) {
                    $img_path = uploadImage($request->file($key), 'website_homepage', $prefix);
                    WebsiteHomepage::updateOrCreate(
                        [
                            'key' => $key,
                            'website_setting_id' => $web_setting->id,
                        ],
                        [
                            'value' =>json_encode($img_path),
                        ]
                    );
                }
            }


           if ($request->hasFile('website_logo')) {
            $web_setting->website_logo = uploadImage($request->file('website_logo'), 'website_logo', 'website_logo');
            }


        $web_setting->website_title = $request->website_title;
        $web_setting->facebook_link = $request->facebook_link;
        $web_setting->instagram_link = $request->instagram_link;
        $web_setting->youtube_link = $request->youtube_link;
        $web_setting->twitter_link = $request->twitter_link;
        $web_setting->about_us = $request->about_us;
        $web_setting->status = $request->status ?? 0;
        $web_setting->save();

      if ($request->has('feature_title') && count($request->feature_title) > 0) {
    $submittedFeatureIds = $request->input('feature_id', []);


    $web_setting->features->whereNotIn('id', $submittedFeatureIds)->each(function ($feature) {

        $feature->delete();
    });

    foreach ($request->feature_title as $index => $title) {

        if ($title && isset($request->feature_description[$index]) && $request->feature_description[$index]) {
            $featureId = $request->input('feature_id')[$index] ?? null;

            if ($featureId) {

                $feature = WebsiteFeature::updateOrCreate(
                    ['id' => $featureId,
                     'website_setting_id'=>$request->website_setting_id,
                    ],
                    [
                        'title' => $title,
                        'description' => $request->feature_description[$index]
                    ]
                );


                if (isset($request->feature_image[$index]) && $request->feature_image[$index]->isValid()) {
                    $image = $request->feature_image[$index];
                    $img_name = 'website_feature_' . rand(100000, 999999) . time() . '.' . $image->getClientOriginalExtension();
                    $img_path = 'website_feature/' . $img_name;
                    $image->move(public_path('website_feature'), $img_name);
                    $feature->image = $img_path;
                    $feature->save();
                }
            }
        }
    }
}

    $messages = [
        'settings' => __('messages.website_settings'),
        'about-us' => __('messages.about_us'),
        'feature' => __('messages.features'),
        'homepage-setting' => __('messages.homepage_setting'),
    ];

    $message = ($messages[$request->type] ?? __('messages.website_settings')) . ' ' . __('messages.updated_successfully');

        return redirect()->route('backend.website-setting.index')->with('success', $message);
    }



}
