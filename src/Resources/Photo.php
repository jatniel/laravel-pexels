<?php

namespace Jatniel\Pexels\Resources;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class Photo implements Arrayable, JsonSerializable
{
    /**
     * @param  array<string, string>  $src  Image URL keyed by size.
     */
    public function __construct(
        public int $id,
        public int $width,
        public int $height,
        public string $url,
        public string $photographer,
        public string $photographerUrl,
        public int $photographerId,
        public string $avgColor,
        public array $src,
        public ?string $alt = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            width: $data['width'],
            height: $data['height'],
            url: $data['url'],
            photographer: $data['photographer'],
            photographerUrl: $data['photographer_url'],
            photographerId: $data['photographer_id'],
            avgColor: $data['avg_color'] ?? '',
            src: $data['src'],
            alt: $data['alt'] ?? null,
        );
    }

    public function getUrl(?string $size = null): string
    {
        if ($size === null) {
            return $this->url;
        }

        return $this->src[$size] ?? $this->src['original'];
    }

    /**
     * @return list<string>
     */
    public function getSizes(): array
    {
        return array_keys($this->src);
    }

    /**
     * Get the attribution as escaped HTML, safe to print with {!! !!}.
     */
    public function getAttribution(bool $withLink = true): string
    {
        $format = config('pexels.attribution.format', 'Photo by :photographer on Pexels');
        $text = e(str_replace(':photographer', $this->photographer, $format));

        if ($withLink && config('pexels.attribution.link_to_profile', true)) {
            return sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                e($this->photographerUrl),
                $text
            );
        }

        return $text;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'width' => $this->width,
            'height' => $this->height,
            'url' => $this->url,
            'photographer' => $this->photographer,
            'photographer_url' => $this->photographerUrl,
            'photographer_id' => $this->photographerId,
            'avg_color' => $this->avgColor,
            'src' => $this->src,
            'alt' => $this->alt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
