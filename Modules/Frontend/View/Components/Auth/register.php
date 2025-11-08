<?php

namespace Modules\Frontend\View\Components\auth;

use Illuminate\View\Component;
use Illuminate\View\View;

class register extends Component
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
        return view('frontend::components.auth/register');
    }
}
