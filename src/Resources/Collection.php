<?php

namespace Jatniel\Pexels\Resources;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class Collection implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $id,
        public string $title,
        public ?string $description,
        public bool $private,
        public int $mediaCount,
        public int $photosCount,
        public int $videosCount,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            title: $data['title'],
            description: $data['description'] ?? null,
            private: $data['private'] ?? false,
            mediaCount: $data['media_count'] ?? 0,
            photosCount: $data['photos_count'] ?? 0,
            videosCount: $data['videos_count'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'private' => $this->private,
            'media_count' => $this->mediaCount,
            'photos_count' => $this->photosCount,
            'videos_count' => $this->videosCount,
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
