<?php

use Illuminate\Support\Facades\Http;
use Jatniel\Pexels\Exceptions\PhotoNotFoundException;
use Jatniel\Pexels\Http\PexelsClient;
use Jatniel\Pexels\Resources\Photo;
use Jatniel\Pexels\Services\PhotoService;
use Jatniel\Pexels\Tests\Helpers;

beforeEach(function () {
    config()->set('pexels.api_key', 'test-api-key');
    config()->set('pexels.cache.enabled', false);
    config()->set('pexels.rate_limit.enabled', false);
});

function createPhotoService(): PhotoService
{
    return new PhotoService(app(PexelsClient::class));
}

it('searches photos by query', function () {
    Http::fake([
        'api.pexels.com/v1/search*' => Http::response(Helpers::searchResponse(3)),
    ]);

    $photos = createPhotoService()->search('nature', perPage: 10, page: 1);

    expect($photos)->toHaveCount(3)
        ->and($photos->first())->toBeInstanceOf(Photo::class);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/search')
        && $request['query'] === 'nature'
        && $request['per_page'] === 10
    );
});

it('returns empty collection when search has no results', function () {
    Http::fake([
        'api.pexels.com/v1/search*' => Http::response(['photos' => []]),
    ]);

    $photos = createPhotoService()->search('nonexistent');

    expect($photos)->toHaveCount(0);
});

it('gets curated photos', function () {
    Http::fake([
        'api.pexels.com/v1/curated*' => Http::response(Helpers::searchResponse(2)),
    ]);

    $photos = createPhotoService()->curated(perPage: 5, page: 2);

    expect($photos)->toHaveCount(2)
        ->and($photos->first())->toBeInstanceOf(Photo::class);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/curated')
        && $request['per_page'] === 5
        && $request['page'] === 2
    );
});

it('finds a photo by id', function () {
    Http::fake([
        'api.pexels.com/v1/photos/12345' => Http::response(Helpers::photoData()),
    ]);

    $photo = createPhotoService()->find(12345);

    expect($photo)->toBeInstanceOf(Photo::class)
        ->and($photo->id)->toBe(12345)
        ->and($photo->photographer)->toBe('John Doe');
});

it('throws exception when photo is not found', function () {
    Http::fake([
        'api.pexels.com/v1/photos/99999' => Http::response([]),
    ]);

    createPhotoService()->find(99999);
})->throws(PhotoNotFoundException::class, 'Photo with ID 99999 not found.');

it('throws photo not found exception when the API returns 404', function () {
    Http::fake([
        'api.pexels.com/v1/photos/99999' => Http::response(['error' => 'Not Found'], 404),
    ]);

    createPhotoService()->find(99999);
})->throws(PhotoNotFoundException::class, 'Photo with ID 99999 not found.');

it('gets a random photo with query', function () {
    Http::fake([
        'api.pexels.com/v1/search*' => Http::response(Helpers::searchResponse(3)),
    ]);

    $photo = createPhotoService()->random('nature');

    expect($photo)->toBeInstanceOf(Photo::class);
});

it('gets a random photo without query (curated)', function () {
    Http::fake([
        'api.pexels.com/v1/curated*' => Http::response(Helpers::searchResponse(2)),
    ]);

    $photo = createPhotoService()->random();

    expect($photo)->toBeInstanceOf(Photo::class);
});

it('throws exception when random finds no photos', function () {
    Http::fake([
        'api.pexels.com/v1/search*' => Http::response(['photos' => []]),
    ]);

    createPhotoService()->random('nonexistent');
})->throws(PhotoNotFoundException::class, 'No photos found.');

it('gets url for a specific photo and size', function () {
    Http::fake([
        'api.pexels.com/v1/photos/12345' => Http::response(Helpers::photoData()),
    ]);

    $url = createPhotoService()->url(12345, 'medium');

    expect($url)->toBe('https://images.pexels.com/photos/12345/medium.jpg');
});

it('gets original url by default', function () {
    Http::fake([
        'api.pexels.com/v1/photos/12345' => Http::response(Helpers::photoData()),
    ]);

    $url = createPhotoService()->url(12345);

    expect($url)->toBe('https://images.pexels.com/photos/12345/original.jpg');
});
