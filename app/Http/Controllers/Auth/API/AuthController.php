<?php

namespace App\Http\Controllers\Auth\API;

use App\Http\Controllers\Auth\Trait\AuthTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\LoginResource;
use App\Http\Resources\RegisterResource;
use App\Http\Resources\SocialLoginResource;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use AuthTrait;

    public function register(Request $request)
    {
        $user = $this->registerTrait($request);
        $success['token'] = $user->createToken(setting('app_name'))->plainTextToken;
        $success['name'] = $user->name;

        $userResource = new RegisterResource($user);

        return $this->sendResponse($userResource, __('messages.register_successfull'));
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $user = User::withTrashed()->where('email', $request->input('email'))->first();
        if ($user == null) {
            return response()->json(['status' => false, 'message' => __('messages.register_before_login')]);
        }
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();

            if ($user->is_banned == 1 || $user->status == 0) {
                return response()->json(['status' => false, 'message' => __('messages.login_error')]);
            }

            // Save the user
            $user->save();

            if (! $user->hasRole('user')) {
                return $this->sendError(__('messages.role_not_matched'), ['error' => __('messages.unauthorised')], 200);
            }
            $user['api_token'] = $user->createToken(setting('app_name'))->plainTextToken;

            $loginResource = new LoginResource($user);
            $message = __('messages.user_login');

            return $this->sendResponse($loginResource, $message);
        }
        $is_user_authorized = false;

        if (!empty($user)) {
            if ($user->status === 0) {
                $is_user_authorized = false;
            } elseif ($user->status === 1) {
                $is_user_authorized = !$user->trashed();
            }
        }
        else {
            return $this->sendError(__('messages.not_matched'), ['error' => __('messages.unauthorised')], 200);
        }

    }

    public function socialLogin(Request $request)
    {
        $input = $request->all();

        if ($input['login_type'] === 'mobile') {
            $user_data = User::where('username', $input['username'])->where('login_type', 'mobile')->first();
        } else {
            $user_data = User::where('email', $input['email'])->first();
        }

        if ($user_data != null) {
            if (! isset($user_data->login_type) || $user_data->login_type == '') {
                if ($request->login_type === 'google') {
                    $message = __('validation.unique', ['attribute' => 'email']);
                } else {
                    $message = __('validation.unique', ['attribute' => 'username']);
                }

                return $this->sendError($message, 400);
            }
            $message = __('messages.login_success');
        } else {
            if ($request->login_type === 'google') {
                $key = 'email';
                $value = $request->email;
            } else {
                $key = 'username';
                $value = $request->username;
            }

            $trashed_user_data = User::where($key, $value)->whereNotNull('login_type')->withTrashed()->first();

            if ($trashed_user_data != null && $trashed_user_data->trashed()) {
                if ($request->login_type === 'google') {
                    $message = __('validation.unique', ['attribute' => 'email']);
                } else {
                    $message = __('validation.unique', ['attribute' => 'username']);
                }

                return $this->sendError($message, 400);
            }

            if ($request->login_type === 'mobile' && $user_data == null) {
                $otp_response = [
                    'status' => true,
                    'is_user_exist' => false,
                ];

                return $this->sendError($otp_response);
            }

            if ($request->login_type === 'mobile' && $user_data != null) {
                $otp_response = [
                    'status' => true,
                    'is_user_exist' => true,
                ];

                return $this->sendError($otp_response);
            }

            $password = ! empty($input['accessToken']) ? $input['accessToken'] : $input['email'];

            $input['user_type'] = 'user';
            $input['display_name'] = $input['first_name'].' '.$input['last_name'];
            $input['password'] = Hash::make($password);
            $input['user_type'] = isset($input['user_type']) ? $input['user_type'] : 'user';

            $user = User::create($input);
            $user->assignRole('user');

            \Artisan::call('cache:clear');

            // Copy super admin settings to new user
            \App\Models\Setting::copySuperAdminSettingsToUser($user->id);

            if (! empty($input['profile_image'])) {
                $media = $user->addMediaFromUrl($input['profile_image'])->toMediaCollection('profile_image');
                $user->avatar = $media->getUrl();
            }
            $user_data = User::where('id', $user->id)->first();
            $message = trans('messages.save_form', ['form' => $input['user_type']]);
        }


        $user_data['api_token'] = $user_data->createToken('auth_token')->plainTextToken;

        $socialLogin = new SocialLoginResource($user_data);

        return $this->sendResponse($socialLogin, $message);
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if ($request->is('api*')) {
            $user->save();

            return response()->json(['status' => true, 'message' => __('messages.user_logout')]);
        }
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);


        $response = Password::sendResetLink(
            $request->only('email')
        );
        $user = User::where('email', $request->email)->first();
        if ($user == null) {
            return $response == Password::RESET_LINK_SENT
                ? response()->json(['message' => __($response), 'status' => true], 200)
                : response()->json(['message' => __($response), 'status' => false], 200);
        }

        return $response == Password::RESET_LINK_SENT
            ? response()->json(['message' => __($response), 'status' => true], 200)
            : response()->json(['message' => __($response), 'status' => false], 400);
    }

    public function changePassword(Request $request)
    {
        $user = \Auth::user();
        $user_id = ! empty($request->id) ? $request->id : $user->id;
        $user = User::where('id', $user_id)->first();
        if ($user == '') {
            return response()->json([
                'status' => false,
                'message' => __('messages.user_notfound'),
            ], 400);
        }

        $hashedPassword = $user->password;

        $match = Hash::check($request->old_password, $hashedPassword);

        $same_exits = Hash::check($request->new_password, $hashedPassword);

        if ($match) {
            if ($same_exits) {
                $message = __('messages.old_new_pass_same');

                return response()->json([
                    'status' => false,
                    'message' => __('messages.same_pass'),
                ], 400);
            }

            $user->fill([
                'password' => Hash::make($request->new_password),
            ])->save();

            $success['api_token'] = $user->createToken(setting('app_name'))->plainTextToken;
            $success['name'] = $user->name;

            return response()->json([
                'status' => true,
                'data' => $success,
                'message' => __('messages.pass_successfull'),
            ], 200);
        } else {
            $success['api_token'] = $user->createToken(setting('app_name'))->plainTextToken;
            $success['name'] = $user->name;
            $message = __('messages.valid_password');

            return response()->json([
                'status' => true,
                'data' => $success,
                'message' => __('messages.pass_successfull'),
            ], 200);
        }
    }

    public function updateProfile(Request $request)
    {

      
        // // Custom validation messages
        // $messages = [
        //     'username.required' => __('messages.username_required'),
        //     'first_name.required' => __('messages.first_name_required'),
        //     'first_name.regex' => __('messages.only_strings_allowed'),
        //     'last_name.required' => __('messages.last_name_required'),
        //     'last_name.regex' => __('messages.only_strings_allowed'),
        //     'email.required' => __('messages.email_required'),
        //     'email.email' => __('messages.valid_email'),
        //     'email.unique' => __('messages.email_unique'),
        //     'mobile.required' => __('messages.mobile_required'),
        //     'mobile.regex' => __('messages.mobile_valid'),
        //     'mobile.unique' => __('messages.mobile_unique'),
        //     'gender.required' => __('messages.gender_required'),
        //     'gender.in' => __('messages.gender_valid'),
        //     'date_of_birth.required' => __('messages.date_of_birth_required'),
        //     'date_of_birth.date' => __('messages.date_of_birth_valid'),
        //     'date_of_birth.before' => __('messages.date_of_birth_before'),
        // ];

        // // Validation rules
        // $rules = [
        //     'username' => 'required|string|unique:users,username,' . auth()->id(),
        //     'first_name' => 'required|string|regex:/^[A-Za-z]+$/|max:255',
        //     'last_name' => 'required|string|regex:/^[A-Za-z]+$/|max:255',
        //     'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
        //     'mobile' => 'required|string|regex:/^\+\d{1,3}\d{10}$/|unique:users,mobile,' . auth()->id(),
        //     'gender' => 'required|in:male,female,other',
        //     'date_of_birth' => 'required|date|before:today',
        // ];

        // // Create validator
        // $validator = Validator::make($request->all(), $rules, $messages);

        // // Check for validation failure
        // if ($validator->fails()) {
        //     // Get the first error message
        //     $error = $validator->errors()->first();

        //     return response()->json([
        //         'status' => false,
        //         'message' => $error, // Send the first error message
        //     ], 422);
        // }
        $user = \Auth::user();
        if ($request->has('id') && ! empty($request->id)) {
            $user = User::where('id', $request->id)->first();
        }
        if ($user == null) {
            return response()->json([
                'message' => __('messages.no_record'),
            ], 400);
        }
        $user->fill($request->all())->update();

        $user_data = User::find($user->id);
        if ($request->has('profile_image')) {
            $request->file('profile_image');

            storeMediaFile($user_data, $request->file('profile_image'), 'profile_image');
        }

        $user_data->save();

        $message = __('messages.profile_update');
        $user_data['user_role'] = $user->getRoleNames();
        $user_data['profile_image'] = $user->profile_image;
        unset($user_data['roles']);
        unset($user_data['media']);

        return response()->json([
            'status' => true,
            'data' => $user_data,
            'message' => $message,
        ], 200);
    }

    public function userDetails(Request $request)
    {
        $userID = $request->id;
        $user = User::find($userID);
        if (! $user) {
            return response()->json(['status' => false, 'message' => __('messages.user_notfound')], 404);
        }

        return response()->json(['status' => true, 'data' => $user, 'message' => __('messages.user_details_successfull')]);
    }

    public function deleteAccount(Request $request)
    {
        $user_id = \Auth::user()->id;
        $user = User::where('id', $user_id)->first();
        if ($user == null) {
            $message = __('messages.user_not_found');

            return response()->json([
                'status' => false,
                'message' => $message,
            ], 200);
        }
        $user->booking()->forceDelete();
        $user->forceDelete();
        $message = __('messages.delete_account');

        return response()->json([
            'status' => true,
            'message' => $message,
        ], 200);
    }
}
