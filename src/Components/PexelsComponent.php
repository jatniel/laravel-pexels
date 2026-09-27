<?php

namespace Jatniel\Pexels\Components;

use Illuminate\View\Component;
use Jatniel\Pexels\Exceptions\PexelsException;
use Jatniel\Pexels\Pexels;
use Jatniel\Pexels\Resources\Photo;

abstract class PexelsComponent extends Component
{
    public ?Photo $photo = null;

    public string $src = '';

    public function __construct(
        public ?int $id = null,
        public ?string $query = null,
        public string $size = 'large',
        public bool $attribution = false,
        public bool $local = false,
    ) {
        $pexels = app(Pexels::class);

        try {
            $this->photo = $this->id
                ? $pexels->photos()->find($this->id)
                : $pexels->photos()->random($this->query);
        } catch (PexelsException $e) {
            // An API failure should not break the page: report it and skip rendering.
            report($e);

            return;
        }

        $this->src = ($this->local ? $pexels->storage()->localUrl($this->photo->id, $this->size) : null)
            ?? $this->photo->getUrl($this->size);
    }

    public function shouldRender(): bool
    {
        return $this->photo !== null;
    }
}
