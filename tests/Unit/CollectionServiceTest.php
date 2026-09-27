<?php

use Illuminate\Support\Facades\Http;
use Jatniel\Pexels\Http\PexelsClient;
use Jatniel\Pexels\Resources\Collection;
use Jatniel\Pexels\Resources\Photo;
use Jatniel\Pexels\Services\CollectionService;
use Jatniel\Pexels\Tests\Helpers;

beforeEach(function () {
    config()->set('pexels.api_key', 'test-api-key');
    config()->set('pexels.cache.enabled', false);
    config()->set('pexels.rate_limit.enabled', false);
});

function createCollectionService(): CollectionService
{
    return new CollectionService(app(PexelsClient::class));
}

it('gets all collections', function () {
    Http::fake([
        'api.pexels.com/v1/collections*' => Http::response(Helpers::collectionsResponse(3)),
    ]);

    $collections = createCollectionService()->all(perPage: 10, page: 1);

    expect($collections)->toHaveCount(3)
        ->and($collections->first())->toBeInstanceOf(Collection::class)
        ->and($collections->first()->title)->toBe('Collection 1');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/collections')
        && $request['per_page'] === 10
    );
});

it('returns empty collection when no collections exist', function () {
    Http::fake([
        'api.pexels.com/v1/collections*' => Http::response(['collections' => []]),
    ]);

    $collections = createCollectionService()->all();

    expect($collections)->toHaveCount(0);
});

it('gets photos from a collection', function () {
    Http::fake([
        'api.pexels.com/v1/collections/abc123*' => Http::response(Helpers::collectionMediaResponse(2)),
    ]);

    $photos = createCollectionService()->photos('abc123', perPage: 5, page: 1);

    expect($photos)->toHaveCount(2)
        ->and($photos->first())->toBeInstanceOf(Photo::class);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/collections/abc123')
        && $request['type'] === 'photos'
        && $request['per_page'] === 5
    );
});

it('returns empty collection when collection has no photos', function () {
    Http::fake([
        'api.pexels.com/v1/collections/empty*' => Http::response(['media' => []]),
    ]);

    $photos = createCollectionService()->photos('empty');

    expect($photos)->toHaveCount(0);
});
