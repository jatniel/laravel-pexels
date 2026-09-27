<?php

use Illuminate\Support\Facades\Http;
use Jatniel\Pexels\Exceptions\PhotoNotFoundException;
use Jatniel\Pexels\Resources\Photo;
use Jatniel\Pexels\Services\PhotoService;
use Jatniel\Pexels\Tests\Helpers;

it('searches photos with filters and returns a paginator', function () {
    Http::fake([
        'api.pexels.com/v1/search*' => Http::response(array_merge(Helpers::searchResponse(3), ['total_results' => 120, 'page' => 2, 'per_page' => 10])),
    ]);

    $photos = app(PhotoService::class)->search('nature', perPage: 10, page: 2, orientation: 'landscape', color: 'blue');

    expect($photos)->toHaveCount(3)
        ->and($photos->first())->toBeInstanceOf(Photo::class)
        ->and($photos->total())->toBe(120)
        ->and($photos->currentPage())->toBe(2)
        ->and($photos->lastPage())->toBe(12);

    Http::assertSent(fn ($request) => $request['query'] === 'nature'
        && $request['per_page'] === 10
        && $request['orientation'] === 'landscape'
        && $request['color'] === 'blue'
        && ! isset($request['size'])
    );
});

it('gets curated photos', function () {
    Http::fake([
        'api.pexels.com/v1/curated*' => Http::response(Helpers::searchResponse(2)),
    ]);

    $photos = app(PhotoService::class)->curated(perPage: 5, page: 2);

    expect($photos)->toHaveCount(2);

    Http::assertSent(fn ($request) => $request['per_page'] === 5 && $request['page'] === 2);
});

it('finds a photo by id', function () {
    Http::fake([
        'api.pexels.com/v1/photos/12345' => Http::response(Helpers::photoData()),
    ]);

    $photo = app(PhotoService::class)->find(12345);

    expect($photo->id)->toBe(12345)
        ->and($photo->photographer)->toBe('John Doe')
        ->and(app(PhotoService::class)->url(12345, 'medium'))->toBe('https://images.pexels.com/photos/12345/medium.jpg');
});

it('throws photo not found exception when the API returns 404', function () {
    Http::fake([
        'api.pexels.com/v1/photos/99999' => Http::response(['error' => 'Not Found'], 404),
    ]);

    app(PhotoService::class)->find(99999);
})->throws(PhotoNotFoundException::class, 'Photo with ID 99999 not found.');

it('gets a random photo from the first page of results', function () {
    Http::fake([
        'api.pexels.com/v1/search*' => Http::response(Helpers::searchResponse(3)),
    ]);

    expect(app(PhotoService::class)->random('nature'))->toBeInstanceOf(Photo::class);

    Http::assertSent(fn ($request) => $request['page'] === 1 && $request['per_page'] === 80);
});

it('throws exception when random finds no photos', function () {
    Http::fake([
        'api.pexels.com/v1/search*' => Http::response(['photos' => []]),
    ]);

    app(PhotoService::class)->random('nonexistent');
})->throws(PhotoNotFoundException::class, 'No photos found.');
