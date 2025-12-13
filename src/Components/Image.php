<?php

namespace Jatniel\Pexels\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Jatniel\Pexels\Facades\Pexels;
use Jatniel\Pexels\Resources\Photo;

class Image extends Component
{
    public Photo $photo;

    public string $src;

    public function __construct(
        public ?int $id = null,
        public ?string $query = null,
        public string $size = 'large',
        public bool $attribution = false,
        public bool $local = false,
    ) {
        $this->photo = $this->resolvePhoto();
        $this->src = $this->resolveSrc();
    }

    public function render(): View|Closure|string
    {
        return view('pexels::components.image');
    }

    /**
     * Resolve the photo from ID or query.
     */
    private function resolvePhoto(): Photo
    {
        if ($this->id) {
            return Pexels::photos()->find($this->id);
        }

        return Pexels::photos()->random($this->query);
    }

    /**
     * Resolve the image source URL.
     */
    private function resolveSrc(): string
    {
        if ($this->local) {
            $localUrl = Pexels::storage()->localUrl($this->photo->id, $this->size);

            if ($localUrl) {
                return $localUrl;
            }
        }

        return $this->photo->getUrl($this->size);
    }
}
