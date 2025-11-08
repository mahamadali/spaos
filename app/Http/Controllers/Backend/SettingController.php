<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    protected $module_title;
    protected $module_name;
    protected $module_icon;
    protected $global_booking;
    public function __construct()
    {
        // Page Title
        $this->module_title = __('settings.title');

        // module name
        $this->module_name = 'settings';

        // module icon
        $this->module_icon = 'fas fa-cogs';

        $this->global_booking = false;

        view()->share([
            'module_title' => $this->module_title,
            'module_name' => $this->module_name,
            'module_icon' => $this->module_icon,
            'global_booking' => $this->global_booking,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $module_action = 'List';

        return view('backend.settings.index', compact('module_action'));
    }

    public function index_data(Request $request)
    {
        $userId = Auth::id();
        $data = [];
        $user = User::where('id',  $userId)->first();

        if (!isset($request->fields)) {
            return response()->json($data, 404);
        }

        $fields = explode(',', $request->fields);

        $data = Setting::whereIn('name', $fields)
            ->where(function ($query) use ($userId) {
                $query->where('created_by', $userId)
                    ->orWhereNull('created_by');
            })
            ->get();

        $newData = [];
        foreach ($fields as $field) {
            // Use the new fallback method for all users
            $value = Setting::getWithFallback($field, null, $userId);

            // Handle file fields (logos, favicon)
            if (in_array($field, ['logo', 'mini_logo', 'dark_logo', 'dark_mini_logo', 'favicon'])) {
                $newData[$field] = $value ? asset($value) : '';
            } else {
                $newData[$field] = Vendorsetting($field);
            }
            // if (in_array($field, ['logo', 'mini_logo', 'mini_logo', 'dark_logo', 'dark_mini_logo', 'favicon'])) {
            //     if (setting($field) !== null) {
            //         $newData[$field] = asset(setting($field));
            //     } else {
            //         $newData[$field] = null;
            //     }
            // }
        }

        $id = $this->base62_encode_with_random($user->id);

        // dd($this->base62_decode_with_random( $id));

        $newData['quick_booking_url'] = route('app.quick-booking', ['id' => $id]);


        return response()->json($newData, 200);
    }

    private function base62_encode_with_random($number, $length = 7)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($chars);
        $encoded = '';

        while ($number > 0) {
            $encoded = $chars[$number % $base] . $encoded;
            $number = floor($number / $base);
        }

        // Ensure that the string is at least the desired length
        $encoded = str_pad($encoded, $length, '0', STR_PAD_LEFT);

        // Add a random alphanumeric suffix of length (e.g., 2)
        $randomSuffix = $this->generate_random_string(2);  // Generate 2 random alphanumeric chars
        $encoded .= $randomSuffix;

        return $encoded;
    }

    private function generate_random_string($length = 2)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }



    public function store(Request $request)
    {
        $data = $request->all();

        $userId = Auth::id();

        $user = User::where('id', $userId)->first();

        if ($user->user_type !== 'super admin') {
            $isSubscribed = CheckSubscription($userId);

            if (!$isSubscribed) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.subscription_required')
                ], 500);
            }
        }


        if ($request->hasFile('json_file')) { // Check if file exists
            $file = $request->file('json_file');
            if ($file) {
                $fileName = $userId . '_' . $file->getClientOriginalName(); // Add user ID prefix
                $directoryPath = storage_path('app/data');

                if (!File::isDirectory($directoryPath)) {
                    File::makeDirectory($directoryPath, 0777, true, true);
                }

                // Delete only this admin's previous JSON files
                $files = File::files($directoryPath);
                foreach ($files as $existingFile) {
                    if (
                        strpos($existingFile->getFilename(), $userId . '_') === 0 &&
                        strtolower($existingFile->getExtension()) === 'json'
                    ) {
                        File::delete($existingFile->getPathname());
                    }
                }
                $file->move($directoryPath, $fileName);
            }
        }

        unset($data['json_file']);
        $rules = $request->wantsJson()
            ? Setting::getSelectedValidationRules(array_keys($request->all()))
            : Setting::getValidationRules();

        $data = $this->validate($request, $rules);

        $validSettings = array_keys($rules);

        foreach ($data as $key => $val) {
            if (in_array($key, $validSettings)) {
                $mimeTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/vnd.microsoft.icon'];
                if (gettype($val) == 'object') {
                    if ($val->getType() == 'file' && in_array($val->getmimeType(), $mimeTypes)) {
                        $setting = Setting::updateOrCreate(
                            ['name' => $key, 'created_by' => $userId],
                            ['val' => '', 'type' => Setting::getDataType($key)]
                        );
                        $mediaItems = $setting->addMedia($val)->toMediaCollection($key);
                        $setting->update(['val' => $mediaItems->getUrl()]);
                    }
                } else {

                    if (in_array($key, ['logo', 'mini_logo', 'dark_logo', 'favicon']) && (is_null($val) || $val === '' || $val === 'null')) {

                        Setting::where('name', $key)
                            ->where('created_by', $userId)
                            ->delete();
                    } else {
                        Setting::updateOrCreate(
                            ['name' => $key, 'created_by' => $userId],
                            ['val' => $val, 'type' => Setting::getDataType($key)]
                        );
                    }
                }
                if ($key === 'default_time_zone') {
                    Cache::forget('settings.default_time_zone');

                    // Fetch and apply the updated timezone
                    $newTimezone = $val;
                    Config::set('app.timezone', $newTimezone);
                    date_default_timezone_set($newTimezone);
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('settings.save_setting'),
                'status' => true
            ], 200);
        }

        return redirect()->back()->with('status', __('messages.setting_save'));
    }

    public function clear_cache()
    {
        Setting::flushCache();

        $message = __('messages.cache_cleard');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function verify_email(Request $request)
    {
        $mailObject = $request->all();
        try {
            \Config::set('mail', $mailObject);
            Mail::raw('This is a smtp mail varification test mail!', function ($message) use ($mailObject) {
                $message->to($mailObject['email'])->subject('Test Email');
            });

            return response()->json(['message' => 'Verification Successful', 'status' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Verification Failed', 'status' => false], 500);
        }
    }
}
