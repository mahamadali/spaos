<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {


        // Check if user is authenticated
        if (!Auth::check()) {
            $loginRoute = $this->getLoginRoute($request);


            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Authentication required. Please log in.',
                    'redirect_url' => $loginRoute
                ], 401);
            }
            return redirect($loginRoute)->with('error', 'Please log in to access this page.');
        }

        $user = Auth::user();

        // Check if user has 'user' role
        if (!$user->hasRole('user')) {
            Auth::logout();

            $loginRoute = $this->getLoginRoute($request);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Only users with "user" role are allowed to access this area.',
                    'redirect_url' => $loginRoute
                ], 403);
            }

            return redirect($loginRoute)->with('error', 'Unauthorized. Only users with "user" role are allowed to access this area.');
        }

        // Check if user account is active
        if ($user->status == 0) {
            Auth::logout();

            $loginRoute = $this->getLoginRoute($request);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has been disabled. Please contact support.',
                    'redirect_url' => $loginRoute
                ], 403);
            }

            return redirect($loginRoute)->with('error', 'Your account has been disabled. Please contact support.');
        }

        return $next($request);
    }

    /**
     * Determine the correct login route based on request context
     */
    private function getLoginRoute(Request $request): string
    {
        $vendorSlug = $request->route('vendor_slug');

        if (!$vendorSlug) {
            $path = $request->path();
            $pathSegments = explode('/', $path);
            if (count($pathSegments) > 0 && !empty($pathSegments[0])) {
                $vendorSlug = $pathSegments[0];
            }
        }

        if ($vendorSlug) {
            $vendorLoginUrl = route('vendor.login', ['vendor_slug' => $vendorSlug]);
            return $vendorLoginUrl;
        }

        $adminLoginUrl = route('login');
        return $adminLoginUrl;
    }
}
