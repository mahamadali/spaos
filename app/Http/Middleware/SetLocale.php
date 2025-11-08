<?php

namespace App\Http\Middleware;

use Closure;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! session()->has('locale')) {
            session()->put('locale', config('app.locale'));
        }

        $locale = session()->get('locale');
        app()->setLocale($locale);

        // Set direction if not already set
        if (! session()->has('dir')) {
            $rtlLanguages = ['ar', 'he', 'fa', 'ur'];
            $direction = in_array($locale, $rtlLanguages) ? 'rtl' : 'ltr';
            session()->put('dir', $direction);
        }

        return $next($request);
    }
}
