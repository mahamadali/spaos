<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class LanguageController extends Controller
{
    public function switch($language)
    {
        app()->setLocale($language);

        session()->put('locale', $language);

        // Set direction based on language - RTL for Arabic, LTR for others
        $rtlLanguages = ['ar', 'he', 'fa', 'ur'];
        $direction = in_array($language, $rtlLanguages) ? 'rtl' : 'ltr';
        session()->put('dir', $direction);

        setlocale(LC_TIME, $language);

        Carbon::setLocale($language);

        flash()->success(__('Language changed to') . ' ' . strtoupper($language))->important();

        return redirect()->back();
    }
}
