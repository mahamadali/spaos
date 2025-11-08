<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Subscriptions\Models\Plan;
use Modules\Subscriptions\Models\Subscription;
use Symfony\Component\HttpFoundation\Response;

class UserAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $today = Carbon::today();

        // Get the authenticated user
        $user = auth()->user();

        // If not authenticated, redirect to login
        if (!$user) {
            return redirect()->route('login');
        }

        if(auth()->user()->hasRole('admin')) {

            // Get the latest subscription for the user

            $subscription = Subscription::where('user_id', $user->id)->orderByDesc('id')->first();

            if($subscription) {
                $end_date = $subscription->end_date;
            } else {
                $start_date = $user->created_at; // Set user->created_at as the start date
                $plan = Plan::where('is_free_plan', 1)->first();

                // Set the duration for the free plan
                if ($plan->type == 'Monthly') {
                    $end_date = $start_date->copy()->addMonths($plan->duration);
                } elseif ($plan->type == 'Yearly') {
                    $end_date = $start_date->copy()->addYears($plan->duration);
                } elseif ($plan->type == 'Weekly') {
                    $end_date = $start_date->copy()->addWeeks($plan->duration);
                } elseif ($plan->type == 'Daily') {
                    $end_date = $start_date->copy()->addDays($plan->duration);
                }
            }



            if (!$subscription) {
                $plan->givePermissionToUser($user->id);
            }

            Subscription::where('user_id', $user->id)
                ->where('end_date', '<', $today)
                ->update(['is_active' => 0]);
        }

        // Proceed if subscription is valid
        return $next($request);
    }
}
