<?php

namespace Jatniel\Pexels\Components;

use Illuminate\Contracts\View\View;

class Image extends PexelsComponent
{
    public function render(): View
    {
        return $this->view('pexels::components.image');
    }
}
