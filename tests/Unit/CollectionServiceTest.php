<?php

use Illuminate\Support\Facades\Http;
use Jatniel\Pexels\Resources\Collection;
use Jatniel\Pexels\Resources\Photo;
use Jatniel\Pexels\Services\CollectionService;
use Jatniel\Pexels\Tests\Helpers;

it('gets all collections', function () {
    Http::fake([
        'api.pexels.com/v1/collections*' => Http::response(Helpers::collectionsResponse(3)),
    ]);

    $collections = app(CollectionService::class)->all(perPage: 10);

    expect($collections)->toHaveCount(3)
        ->and($collections->first())->toBeInstanceOf(Collection::class)
        ->and($collections->first()->title)->toBe('Collection 1')
        ->and($collections->total())->toBe(3);

    Http::assertSent(fn ($request) => $request['per_page'] === 10);
});

it('gets photos from a collection', function () {
    Http::fake([
        'api.pexels.com/v1/collections/abc123*' => Http::response(Helpers::collectionMediaResponse(2)),
    ]);

    $photos = app(CollectionService::class)->photos('abc123', perPage: 5);

    expect($photos)->toHaveCount(2)
        ->and($photos->first())->toBeInstanceOf(Photo::class);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/collections/abc123')
        && $request['type'] === 'photos'
        && $request['per_page'] === 5
    );
});
