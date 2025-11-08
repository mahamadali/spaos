<?php

namespace Modules\Slider\Http\Middleware;

use App\Trait\HorizontalMenu;
use Closure;
use Illuminate\Http\Request;

class GenerateMenus
{
    use HorizontalMenu;

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /*
         *
         * Module Menu for Admin Backend
         *
         * *********************************************************************
         */
        \Menu::make('horizontal_menu', function ($menu) {
          
        })->sortBy('order');

        return $next($request);
    }
}
