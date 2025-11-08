<?php

namespace Modules\Customer\Http\Middleware;

use App\Trait\Menu;
use Closure;

class GenerateMenus
{
    use Menu;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /*
         *
         * Module Menu for Admin Backend
         *
         * *********************************************************************
         */
        \Menu::make('menu', function ($menu) {
        })->sortBy('order');

        return $next($request);
    }
}
