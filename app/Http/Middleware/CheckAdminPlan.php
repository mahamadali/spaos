<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Subscriptions\Models\Subscription;

class CheckAdminPlan
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Check if the user is an admin
        if ($user->user_type == 'admin') {
            // First check the is_subscribe flag
            if ($user->is_subscribe == 0) {
                // Double-check by looking for active subscriptions
                $activeSubscription = Subscription::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->where('is_active', 1)
                    ->where('end_date', '>', now())
                    ->first();
                
                // If no active subscription found, redirect to pricing
                if (!$activeSubscription) {
                    return redirect()->route('pricing');
                } else {
                    // If active subscription exists but is_subscribe flag is wrong, fix it
                    $user->update(['is_subscribe' => 1]);
                }
            }
        }
        
        return $next($request);
    }
}
