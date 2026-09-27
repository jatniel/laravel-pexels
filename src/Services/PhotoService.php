<?php

namespace Jatniel\Pexels\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Jatniel\Pexels\Exceptions\PexelsException;
use Jatniel\Pexels\Exceptions\PhotoNotFoundException;
use Jatniel\Pexels\Http\PexelsClient;
use Jatniel\Pexels\Resources\Photo;
use Jatniel\Pexels\Services\Concerns\PaginatesResponses;

class PhotoService
{
    use PaginatesResponses;

    public function __construct(
        private readonly PexelsClient $client,
    ) {}

    /**
     * Search photos by query.
     *
     * @param  string|null  $orientation  landscape, portrait or square
     * @param  string|null  $size  large (24MP), medium (12MP) or small (4MP)
     * @param  string|null  $color  Color name (red, blue...) or hex code (#ffffff)
     * @param  string|null  $locale  Search locale, e.g. es-ES
     * @return LengthAwarePaginator<int, Photo>
     */
    public function search(
        string $query,
        int $perPage = 15,
        int $page = 1,
        ?string $orientation = null,
        ?string $size = null,
        ?string $color = null,
        ?string $locale = null,
    ): LengthAwarePaginator {
        $response = $this->client->get('/search', array_filter([
            'query' => $query,
            'per_page' => $perPage,
            'page' => $page,
            'orientation' => $orientation,
            'size' => $size,
            'color' => $color,
            'locale' => $locale,
        ]));

        return $this->paginate($response, 'photos', Photo::fromArray(...), $perPage, $page);
    }

    /**
     * Get curated photos.
     *
     * @return LengthAwarePaginator<int, Photo>
     */
    public function curated(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $response = $this->client->get('/curated', [
            'per_page' => $perPage,
            'page' => $page,
        ]);

        return $this->paginate($response, 'photos', Photo::fromArray(...), $perPage, $page);
    }

    /**
     * Find a photo by ID.
     */
    public function find(int $id): Photo
    {
        try {
            $response = $this->client->get("/photos/{$id}");
        } catch (PexelsException $e) {
            throw $e->getCode() === 404 ? PhotoNotFoundException::withId($id) : $e;
        }

        if (empty($response['id'])) {
            throw PhotoNotFoundException::withId($id);
        }

        return Photo::fromArray($response);
    }

    /**
     * Get a random photo by query, or a random curated photo.
     */
    public function random(?string $query = null): Photo
    {
        $photos = $query
            ? $this->search($query, perPage: 80)
            : $this->curated(perPage: 80);

        if ($photos->isEmpty()) {
            throw new PhotoNotFoundException('No photos found.');
        }

        return $photos->getCollection()->random();
    }

    /**
     * Get the direct URL for a photo.
     */
    public function url(int $id, string $size = 'original'): string
    {
        return $this->find($id)->getUrl($size);
    }
}
