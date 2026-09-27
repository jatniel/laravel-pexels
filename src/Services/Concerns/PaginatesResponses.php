<?php

namespace Jatniel\Pexels\Services\Concerns;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

trait PaginatesResponses
{
    /**
     * Build a paginator from a Pexels list response.
     *
     * @template TItem
     *
     * @param  array<string, mixed>  $response
     * @param  callable(array<string, mixed>): TItem  $map
     * @return LengthAwarePaginator<int, TItem>
     */
    private function paginate(array $response, string $key, callable $map, int $perPage, int $page): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            items: array_map($map, $response[$key] ?? []),
            total: (int) ($response['total_results'] ?? 0),
            perPage: (int) ($response['per_page'] ?? $perPage),
            currentPage: (int) ($response['page'] ?? $page),
            options: ['path' => Paginator::resolveCurrentPath()],
        );
    }
}
