<?php

namespace Jatniel\Pexels\Services;

use Illuminate\Support\Collection as LaravelCollection;
use Jatniel\Pexels\Http\PexelsClient;
use Jatniel\Pexels\Resources\Collection;
use Jatniel\Pexels\Resources\Photo;

class CollectionService
{
    public function __construct(
        private readonly PexelsClient $client,
    ) {}

    /**
     * Get all user collections.
     */
    public function all(int $perPage = 15, int $page = 1): LaravelCollection
    {
        $response = $this->client->get('/collections', [
            'per_page' => $perPage,
            'page' => $page,
        ]);

        return collect($response['collections'] ?? [])
            ->map(fn (array $collection) => Collection::fromArray($collection));
    }

    /**
     * Get photos from a specific collection.
     */
    public function photos(string $collectionId, int $perPage = 15, int $page = 1): LaravelCollection
    {
        $response = $this->client->get("/collections/{$collectionId}", [
            'type' => 'photos',
            'per_page' => $perPage,
            'page' => $page,
        ]);

        return collect($response['media'] ?? [])
            ->map(fn (array $photo) => Photo::fromArray($photo));
    }
}
