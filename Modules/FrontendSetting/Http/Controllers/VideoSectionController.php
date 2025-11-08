<?php

namespace Modules\FrontendSetting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\FrontendSetting\Models\VideoSection;

class VideoSectionController extends Controller
{
    public function store(Request $request)
    {


        $auth_user = auth()->user();

        $isSubscribed=CheckSubscription($auth_user->id);

        if(!$isSubscribed){

            return response()->json([
                'success' => false,
                'message' => __('messages.subscription_required')
            ], 500);
  
        }


        try {
            $validated = $request->validate([
                'video_img'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:10000',
                'video_type'  => 'nullable|string',
                'video_url'   => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->validator)->withInput();
        }
    
        try {
            $videoSection = VideoSection::first(); // only one record allowed
            $data = [];
    
            // Handle image upload
            if ($request->hasFile('video_img')) {
                $video_img_path = $request->file('video_img')->store('video_section', 'public');
    
                // delete old image if exists
                if ($videoSection && $videoSection->video_img && \Storage::disk('public')->exists($videoSection->video_img)) {
                    \Storage::disk('public')->delete($videoSection->video_img);
                }
    
                $data['video_img'] = $video_img_path;
            }
    
            // Update only fields provided in request
            if ($request->filled('video_type')) {
                $data['video_type'] = $request->video_type;
            }
    
            if ($request->filled('video_url')) {
                $data['video_url'] = $request->video_url;
            }
    
            if ($videoSection) {
                // Update existing record
                $videoSection->update($data);
            } else {
                // Create new record
                $videoSection = VideoSection::create($data);
            }
    
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Video Section saved successfully!',
                    'data' => $videoSection
                ]);
            }
            
            return redirect()->back()->with('success', 'Video Section saved successfully!')->with('latest', $videoSection);
    
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save Video Section: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to save Video Section: ' . $e->getMessage())->withInput();
        }
    }
    
} 