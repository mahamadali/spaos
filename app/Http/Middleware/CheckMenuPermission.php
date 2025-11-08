<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserHasMenuPermission;
use Modules\MenuBuilder\Models\MenuBuilder;
class CheckMenuPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->user()->hasRole('admin')) {
            $currentRoute = $request->route()->getName();

            // Get menu item for this route
            $menuItem = MenuBuilder::where('route', $currentRoute)->first();

            if($menuItem) {
                // Check if user has permission for this menu
                $hasPermission = UserHasMenuPermission::where('user_id', auth()->id())
                    ->where(function($query) use ($menuItem) {
                        $query->where('menu_id', $menuItem->id)
                              ->orWhere('menu_id', $menuItem->parent_id);
                    })
                    ->exists();

                    if (!$hasPermission) {
                        abort(403, __('messages.unauthorized_access'));
                    }
            }
        }

        return $next($request);
    }
}
