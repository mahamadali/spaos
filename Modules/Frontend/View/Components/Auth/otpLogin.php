<?php

namespace Modules\Frontend\View\Components\auth;

use Illuminate\View\Component;
use Illuminate\View\View;

class otp_login extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view/contents that represent the component.
     */
    public function render(): View|string
    {
        return view('frontend::components.auth/otp_login');
    }
}
