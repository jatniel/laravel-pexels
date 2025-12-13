<?php

namespace Jatniel\Pexels\Services;

use Illuminate\Support\Collection;
use Jatniel\Pexels\Exceptions\PhotoNotFoundException;
use Jatniel\Pexels\Http\PexelsClient;
use Jatniel\Pexels\Resources\Photo;

class PhotoService
{
    public function __construct(
        private readonly PexelsClient $client,
    ) {}

    /**
     * Search photos by query.
     */
    public function search(string $query, int $perPage = 15, int $page = 1): Collection
    {
        $response = $this->client->get('/search', [
            'query' => $query,
            'per_page' => $perPage,
            'page' => $page,
        ]);

        return $this->mapPhotos($response['photos'] ?? []);
    }

    /**
     * Get curated photos.
     */
    public function curated(int $perPage = 15, int $page = 1): Collection
    {
        $response = $this->client->get('/curated', [
            'per_page' => $perPage,
            'page' => $page,
        ]);

        return $this->mapPhotos($response['photos'] ?? []);
    }

    /**
     * Find a photo by ID.
     */
    public function find(int $id): Photo
    {
        $response = $this->client->get("/photos/{$id}");

        if (empty($response['id'])) {
            throw PhotoNotFoundException::withId($id);
        }

        return Photo::fromArray($response);
    }

    /**
     * Get a random photo by query.
     */
    public function random(?string $query = null): Photo
    {
        if ($query) {
            $photos = $this->search($query, perPage: 15, page: rand(1, 10));
        } else {
            $photos = $this->curated(perPage: 15, page: rand(1, 10));
        }

        if ($photos->isEmpty()) {
            throw new PhotoNotFoundException('No photos found.');
        }

        return $photos->random();
    }

    /**
     * Get the direct URL for a photo.
     */
    public function url(int $id, string $size = 'original'): string
    {
        $photo = $this->find($id);

        return $photo->getUrl($size);
    }

    /**
     * Map raw photo data to Photo resources.
     */
    private function mapPhotos(array $photos): Collection
    {
        return collect($photos)->map(fn (array $photo) => Photo::fromArray($photo));
    }
}
