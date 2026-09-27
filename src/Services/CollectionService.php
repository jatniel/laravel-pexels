<?php

namespace Jatniel\Pexels\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Jatniel\Pexels\Http\PexelsClient;
use Jatniel\Pexels\Resources\Collection;
use Jatniel\Pexels\Resources\Photo;
use Jatniel\Pexels\Services\Concerns\PaginatesResponses;

class CollectionService
{
    use PaginatesResponses;

    public function __construct(
        private readonly PexelsClient $client,
    ) {}

    /**
     * Get all user collections.
     *
     * @return LengthAwarePaginator<int, Collection>
     */
    public function all(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $response = $this->client->get('/collections', [
            'per_page' => $perPage,
            'page' => $page,
        ]);

        return $this->paginate($response, 'collections', Collection::fromArray(...), $perPage, $page);
    }

    /**
     * Get photos from a specific collection.
     *
     * @return LengthAwarePaginator<int, Photo>
     */
    public function photos(string $collectionId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $response = $this->client->get("/collections/{$collectionId}", [
            'type' => 'photos',
            'per_page' => $perPage,
            'page' => $page,
        ]);

        return $this->paginate($response, 'media', Photo::fromArray(...), $perPage, $page);
    }
}
