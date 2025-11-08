<?php

namespace Modules\VendorWebsite\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Auth\Trait\AuthTrait;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\VendorWebsite\Models\UserBranch;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{

    public function Signup(Request $request)
    {

        $vendorId = session('current_vendor_id');

        $vendor = \App\Models\User::where('id', $vendorId)->first();

        $vendorSlug = $vendor ? $vendor->slug : null;

        return view('vendorwebsite::auth.register' ,compact('vendorSlug'));
    }

    public function SignupUser(Request $request)
    {
        //  dd($request->all());
        // try {
            $request->validate([
                'first_name' => ['required', 'string', 'max:191'],
                'last_name' => ['required', 'string', 'max:191'],
                'email' => ['required', 'string', 'email', 'max:191'],
                'password' => ['required', 'min:8'],
                'confirm_password' => ['required', 'same:password'],
                'mobile' => ['required', 'string', 'max:20'],
                'gender' => ['required'],
            ]);

            $email = trim($request->email);
            $existingUser = User::where('email', $email)->first();


            if ($existingUser) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The email has already been taken.',
                        'errors' => ['email' => ['The email has already been taken.']]
                    ], 422);
                }
                return redirect()->back()->withErrors(['email' => 'The email has already been taken.'])->withInput();
            }

            $arr = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $email,
                'mobile' => $request->mobile,
                'gender' => $request->gender,
                'password' => Hash::make($request->password),
                'user_type' => 'user',
                'slug' => null,
                'status' => 1,
            ];

            $user = User::create($arr);


            $user->assignRole('user');

            Artisan::call('permission:cache-reset');
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            // Force reload user from database
            $user = $user->fresh();

            $dbRoles = DB::table('model_has_roles')
                ->where('model_id', $user->id)
                ->where('model_type', 'App\Models\User')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->pluck('roles.name')
                ->toArray();

            Auth::login($user, true);

            $request->session()->regenerate();
            $request->session()->save();



            $vendorSlug = $request->input('vendor_slug');

            // If no vendor slug provided, try to get from active vendor
            if (!$vendorSlug) {
                $activeVendor = app('active_vendor');
                if ($activeVendor && $activeVendor->slug) {
                    $vendorSlug = $activeVendor->slug;
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful! You are now logged in.',
                    'redirect_url' => $vendorSlug ? route('vendor.index', ['vendor_slug' => $vendorSlug]) : url('/')
                ]);
            }

            if (!$vendorSlug) {
                return redirect(url('/'))->with('success', 'Registration successful! You are now logged in.');
            }

            $redirectUrl = route('vendor.index', ['vendor_slug' => $vendorSlug]);

            return redirect($redirectUrl)->with('success', 'Registration successful! You are now logged in.');
        // } catch (\Illuminate\Validation\ValidationException $e) {
        //     if ($request->expectsJson()) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Validation failed',
        //             'errors' => $e->errors()
        //         ], 422);
        //     }
        //     return redirect()->back()->withErrors($e->errors())->withInput();
        // } catch (\Exception $e) {
        //     Log::error('Registration error: ' . $e->getMessage());
        //     if ($request->expectsJson()) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Registration failed. Please try again.'
        //         ], 500);
        //     }
        //     return redirect()->back()->withErrors(['error' => 'Registration failed. Please try again.']);
        // }
    }



    public function Login(Request $request)
    {
        $vendorId = session('current_vendor_id');

        if (!$vendorId) {
            return redirect('/');
        }

        $activeBranches = \App\Models\Branch::where('status', 1)
            ->where('created_by', $vendorId)
            ->count();



        // Fetch branches for the current vendor only
        $branches = \App\Models\Branch::where('status', 1)
            ->where('created_by', $vendorId)
            ->get();

        return view('vendorwebsite::auth.login', compact('branches'));
    }


    public function ForgotPassword()
    {
        return view('vendorwebsite::auth.forgotpassword');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = Password::sendResetLink(
            $request->only('email')
        );
        $user = User::where('email', $request->email)->first();

        if ($user == null) {
            return redirect()->back()->with('status', [
                'message' => __($response),
                'status' => $response == Password::RESET_LINK_SENT
            ]);
        }

        return redirect()->back()->with('status', [
            'message' => __($response),
            'status' => $response == Password::RESET_LINK_SENT
        ]);
    }

    public function resetPassword($token)
    {
        return view('vendorwebsite::auth.resetpassword', [
            'token' => $token,
            'email' => request('email')
        ]);
    }

    public function updateResetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function NewPassword(Request $request)
    {

        return view('vendorwebsite::auth.newpassword');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8|different:old_password',
            'confirm_password' => 'required|same:new_password'
        ], [
            'new_password.different' => 'The new password must be different from your old password.'
        ]);

        $user = auth()->user();

        // Check if old password matches
        if (!\Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'The old password is incorrect']);
        }

        // Update password
        $user->password = \Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully');
    }

    public function logout(Request $request)
    {

        $selectedBranch = $request->session()->get('selected_branch');

        UserBranch::updateOrCreate(
            ['user_id' => auth()->id()],
            ['branch_id' => $selectedBranch]
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('vendor.index')->with('status', [
            'message' => 'You have been successfully logged out.',
            'status' => true
        ]);
    }
    public function loginUser(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $isAjax = $request->expectsJson() || $request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json';
        $remember = $request->has('remember_me') || $request->has('remember');

        // Attempt to authenticate the user
        if (Auth::attempt($request->only('email', 'password'), $remember)) {
            // Get the authenticated user
            $user = Auth::user();

            // Only allow users with the 'user' role to log in from the vendorwebsite
            if (!$user->hasRole('user')) {
                Auth::logout();
                if ($isAjax) {
                    return response()->json([
                        'status' => false,
                        'success' => false,
                        'message' => 'Unauthorized role. You are not allowed to log in from the vendorwebsite.',
                    ], 403);
                }
                return back()->withErrors([
                    'email' => 'Unauthorized role. You are not allowed to log in from the vendorwebsite.',
                ])->onlyInput('email');
            }

            // Regenerate session to prevent session fixation
            $request->session()->regenerate();

            Auth::login($user, $remember);

            // Force session save to ensure it's persisted
            $request->session()->save();

            // Get user branch
            $user_branch_id = UserBranch::where('user_id', $user->id)->first()->branch_id ?? null;


            // Store selected branch in session if provided
            if ($request->filled('branch_id') || $user_branch_id) {

                $branchId = $request->input('branch_id') ?? $user_branch_id;

                Session::put('selected_branch_id', $branchId);
                Session::put('selected_branch', $branchId);
            }


            $redirectUrl = $request->input('intended', route('vendor.index'));


            // If this is an AJAX request, return JSON response
            if ($isAjax) {

                return response()->json([
                    'status' => true,
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect_url' => $redirectUrl,
                    'user_branch_id' => $user_branch_id,
                    'session_id' => session()->getId(),
                    'auth_check' => Auth::check(),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->getRoleNames()->toArray()
                    ]
                ])->withCookie(cookie(
                    'laravel_session',
                    session()->getId(),
                    config('session.lifetime'),
                    config('session.path'),
                    config('session.domain'),
                    config('session.secure', false),
                    true, // httpOnly
                    false,
                    config('session.same_site')
                ));
            }



            // For non-AJAX requests, redirect with the session
            return redirect()->intended($redirectUrl)
                ->with('status', 'Successfully logged in!');
        }

        if ($isAjax) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'These credentials do not match our records.',
            ], 401);
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            if (!$googleUser || !$googleUser->getEmail()) {
                return redirect('/login')->with('error', 'Unable to get user information from Google. Please try again.');
            }

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $fullName = $googleUser->getName();
                $nameParts = explode(' ', $fullName);

                $firstName = isset($nameParts[0]) ? $nameParts[0] : '';
                $lastName = isset($nameParts[1]) ? $nameParts[1] : $firstName;

                $data = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'name' => $firstName . ' ' . $lastName,
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(8)),
                    'user_type' => 'user',
                    'login_type' => 'google',
                    'status' => 1,
                ];

                $user = User::create($data);
                $user->assignRole('user');
                $user->save();

                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                // Artisan::call('view:clear');
                // Artisan::call('config:cache');
                // Artisan::call('route:clear');

                // $user = $user->fresh();
            }
            // dd($user,$user->hasRole('user'),$user->getRoleNames());
            if (!$user->hasRole('user')) {
                return redirect('/login')->with('error', 'Unauthorized role. You are not allowed to log in from the vendorwebsite.');
            }

            Auth::login($user);
            $request->session()->regenerate();

            $branchId = $request->get('branch_id') ?? $request->input('branch_id');
            if ($branchId) {
                $request->session()->put('selected_branch', $branchId);
            }

            return redirect('/');
        } catch (\Exception $e) {

            return redirect('/login')->with('error', 'Something went wrong during Google authentication. Please try again.');
        }
    }

}
