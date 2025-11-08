<?php

namespace Modules\Frontend\View\Components\section;

use Illuminate\View\Component;
use Illuminate\View\View;

class why_choose_section extends Component
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
        return view('frontend::components.section/why_choose_section');
    }
}
