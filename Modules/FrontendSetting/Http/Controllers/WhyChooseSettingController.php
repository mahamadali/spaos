<?php

namespace Modules\FrontendSetting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\FrontendSetting\Models\WhyChoose;
use Modules\FrontendSetting\Models\WhyChooseFeature;

class WhyChooseSettingController extends Controller
{
    /**
     * Show the form for editing the Why Choose Us section.
     */
    public function show()
    {
        $data = WhyChoose::where('created_by', auth()->id())->latest()->first();
        return view('frontendsetting::sections.why_choose_section', compact('data'));
    }

    /**
     * Store the Why Choose Us section data.
     */
    public function store(Request $request)
    {
        $auth_user = auth()->user();
        $isSubscribed = CheckSubscription($auth_user->id);

    

        if (!$isSubscribed) {

            return response()->json([
                'success' => false,
                'message' => __('messages.subscription_required')
            ], 403);
        }

        try {
            // Custom validation rules
            $rules = [
                'chooseUs_title' => 'nullable|string',
                'chooseUs_subtitle' => 'nullable|string',
                'chooseUs_description' => 'nullable|string',
                'chooseUs_image' => 'nullable|image|mimes:jpg,jpeg,png|max:10000',
                'add_more_title' => 'nullable|array',
                'add_more_subtitle' => 'nullable|array',
                'add_more_image' => 'nullable|array',
                'add_more_image.*' => 'nullable|image|mimes:jpg,jpeg,png|max:10000',
            ];

            // Custom validation messages
            $messages = [
                'chooseUs_image.image' => 'The main image must be a valid image file.',
                'chooseUs_image.mimes' => 'The main image must be a file of type: JPG, JPEG, PNG.',
                'chooseUs_image.max' => 'The main image may not be greater than 10MB.',
                'add_more_image.*.image' => 'Each feature image must be a valid image file.',
                'add_more_image.*.mimes' => 'Each feature image must be a file of type: JPG, JPEG, PNG.',
                'add_more_image.*.max' => 'Each feature image may not be greater than 10MB.',
            ];

            $validated = $request->validate($rules, $messages);

            // Custom validation: If title is provided, image is required
            if (!empty($validated['chooseUs_title']) && !$request->hasFile('chooseUs_image') && empty($request->input('existing_image'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image is required when title is provided.',
                    'errors' => ['chooseUs_image' => ['Image is required when title is provided.']]
                ], 422);
            }

            // Custom validation for add more features
            if ($request->has('add_more_title')) {
                $titles = $request->input('add_more_title', []);
                $images = $request->file('add_more_image', []);

                foreach ($titles as $i => $title) {
                    if (!empty($title) && (!isset($images[$i]) || !$images[$i])) {
                        return response()->json([
                            'success' => false,
                            'message' => "Image is required for feature: {$title}",
                            'errors' => ["add_more_image.{$i}" => ['Image is required when title is provided.']]
                        ], 422);
                    }
                }
            }

            // Handle main image
            $imagePath = '';

            if ($request->hasFile('chooseUs_image')) {

                $imagePath = $request->input('existing_image', '');
                // Delete old image if exists
                if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
                // Store new image
                $image = $request->file('chooseUs_image');
                $imagePath = $image->store('why_choose', 'public');
            }

            $data = [
                'image' => $imagePath,
                'title' => $validated['chooseUs_title'] ?? '',
                'subtitle' => $validated['chooseUs_subtitle'] ?? '',
                'description' => $validated['chooseUs_description'] ?? '',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ];

            $whyChoose = WhyChoose::updateOrCreate(
                ['created_by' => auth()->id()],
                $data
            );


            // Initialize counters/defaults used in response message
            $newFeaturesAdded = 0;
            $titles = [];

            // Save new features if any (total limit of 3 including existing features)
            if ($request->has('add_more_title')) {
                $titles = $request->input('add_more_title', []);
                $subtitles = $request->input('add_more_subtitle', []);
                $images = $request->file('add_more_image', []);

                // Get current feature count
                $existingFeaturesCount = $whyChoose->features()->count();
                $maxTotalFeatures = 3;
                $remainingSlots = $maxTotalFeatures - $existingFeaturesCount;

                foreach ($titles as $i => $title) {
                    if (empty($title)) continue;

                    // Check if we've reached the total limit
                    if ($newFeaturesAdded >= $remainingSlots) {
                        break;
                    }

                    $featureData = [
                        'title' => $title,
                        'subtitle' => $subtitles[$i] ?? null,
                        'image' => null,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ];

                    if (isset($images[$i]) && $images[$i]) {
                        $featureData['image'] = $images[$i]->store('why_choose_features', 'public');
                    }

                    $whyChoose->features()->create($featureData);
                    $newFeaturesAdded++;
                }
            }


            $message = 'Why Choose Us section updated successfully.';
            if ($newFeaturesAdded > 0 && $newFeaturesAdded < count(array_filter($titles))) {
                $skippedFeatures = count(array_filter($titles)) - $newFeaturesAdded;
                $message .= " Note: $skippedFeatures feature(s) were not saved due to the 3-feature limit.";
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
        } catch (\Exception $e) {



            return response()->json([
                'success' => false,
                'message' => 'Failed to save: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a specific feature
     */
    public function deleteFeature($id)
    {
        try {
            $feature = WhyChooseFeature::where('id', $id)
                ->where('created_by', auth()->id())
                ->first();

            if (!$feature) {
                return response()->json([
                    'success' => false,
                    'message' => 'Feature not found or unauthorized'
                ], 404);
            }

            // Delete image from storage if exists
            if ($feature->image && Storage::disk('public')->exists($feature->image)) {
                Storage::disk('public')->delete($feature->image);
            }

            // Delete the feature
            $feature->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Feature deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete feature: ' . $e->getMessage()
            ], 500);
        }
    }
}
