<?php

namespace Jatniel\Pexels\Resources;

use Jatniel\Pexels\Contracts\PexelsResourceInterface;

readonly class Photo implements PexelsResourceInterface
{
    public function __construct(
        public int     $id,
        public int     $width,
        public int     $height,
        public string  $url,
        public string  $photographer,
        public string  $photographerUrl,
        public int     $photographerId,
        public string  $avgColor,
        public array   $src,
        public ?string $alt = null,
    ) {}

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getUrl(?string $size = null): string
    {
        if ($size === null) {
            return $this->url;
        }

        return $this->src[$size] ?? $this->src['original'];
    }

    public function getSizes(): array
    {
        return array_keys($this->src);
    }

    public function getAttribution(bool $withLink = true): string
    {
        $format = config('pexels.attribution.format', 'Photo by :photographer on Pexels');
        $text = str_replace(':photographer', $this->photographer, $format);

        if ($withLink && config('pexels.attribution.link_to_profile', true)) {
            return sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                $this->photographerUrl,
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

}
