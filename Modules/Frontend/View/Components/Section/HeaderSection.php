<?php

namespace Modules\Frontend\View\Components\Section;

use Illuminate\View\Component;

class HeaderSection extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Initialize component data if needed
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('frontend::components.section.header_section');
    }
}
