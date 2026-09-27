<?php

namespace Jatniel\Pexels\Components;

use Illuminate\Contracts\View\View;

class Background extends PexelsComponent
{
    public function __construct(
        ?int $id = null,
        ?string $query = null,
        string $size = 'large2x',
        bool $attribution = false,
        bool $local = false,
        public string $attributionPosition = 'bottom-right',
    ) {
        parent::__construct($id, $query, $size, $attribution, $local);
    }

    public function render(): View
    {
        return $this->view('pexels::components.background');
    }
}
