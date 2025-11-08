<?php

namespace App\Http\Middleware;

use Closure;

class localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {

        $sessionLocal = session()->get('locale') ? session()->get('locale') : 'en';

        $local = ($request->hasHeader('frezka-localization')) ? $request->header('frezka-localization') : $sessionLocal;

        app()->setLocale($local);

        return $next($request);
    }
}
