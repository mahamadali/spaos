<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User; // Make sure to import your Vendor model
use Illuminate\Support\Facades\URL;

class VendorModeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(config('frontend.vendor_mode') === 'single' ) {

             session(['current_vendor_id' => null]);
            URL::defaults(['vendor_slug' => null]);

             return $next($request);

        }else{

            $segments = $request->segments();
            
            $vendorSlug = !empty($segments) ? $segments[0] : null;
            $vendor=null;

            if( $vendorSlug ){
                $vendor = User::where('slug', $vendorSlug)->first();
            }
            if ($vendorSlug && $vendor) {
                app()->instance('active_vendor', $vendor);
                session(['current_vendor_id' => $vendor->id]);
                URL::defaults(['vendor_slug' => $vendorSlug]); // <-- inject globally!

                $vendor->loadMissing('subscriptionPackage'); // or 'plan' if that's the relation name

                // Get permissions as array
                $permissions = json_decode($vendor->subscriptionPackage->plan_details ?? '[]', true);

                // Set globally accessible permissions
                app()->instance('vendor_plan_permissions', $permissions['permission_ids'] ?? []);

            } else {

                abort(404);
                
            }

        return $next($request);

        }


    }
}
