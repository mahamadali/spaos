<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminAuth
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
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Authentication required. Please log in.',
                    'redirect_url' => route('login')
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $user = Auth::user();

        // Check if user has 'admin' or 'super admin' role
        if (!$user->hasRole(['admin', 'super admin'])) {
            Auth::logout();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Only admin users are allowed to access this area.',
                    'redirect_url' => route('login')
                ], 403);
            }

            return redirect()->route('login')->with('error', 'Unauthorized. Only admin users are allowed to access this area.');
        }

        // Check if user account is active
        if ($user->status == 0) {
            Auth::logout();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has been disabled. Please contact support.',
                    'redirect_url' => route('login')
                ], 403);
            }

            return redirect()->route('login')->with('error', 'Your account has been disabled. Please contact support.');
        }

        return $next($request);
    }
}
