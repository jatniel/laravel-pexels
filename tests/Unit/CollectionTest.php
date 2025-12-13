<?php

use Jatniel\Pexels\Resources\Collection;

it('creates a collection from array', function () {
    $data = [
        'id' => 'abc123',
        'title' => 'Nature Photos',
        'description' => 'Beautiful nature photography',
        'private' => false,
        'media_count' => 50,
        'photos_count' => 45,
        'videos_count' => 5,
    ];

    $collection = Collection::fromArray($data);

    expect($collection->id)->toBe('abc123')
        ->and($collection->title)->toBe('Nature Photos')
        ->and($collection->description)->toBe('Beautiful nature photography')
        ->and($collection->private)->toBeFalse()
        ->and($collection->mediaCount)->toBe(50)
        ->and($collection->photosCount)->toBe(45)
        ->and($collection->videosCount)->toBe(5);
});

it('handles null description', function () {
    $data = [
        'id' => 'xyz789',
        'title' => 'My Collection',
        'private' => true,
        'media_count' => 10,
        'photos_count' => 10,
        'videos_count' => 0,
    ];

    $collection = Collection::fromArray($data);

    expect($collection->description)->toBeNull();
});

it('converts to array correctly', function () {
    $collection = Collection::fromArray([
        'id' => 'test',
        'title' => 'Test',
        'description' => null,
        'private' => false,
        'media_count' => 0,
        'photos_count' => 0,
        'videos_count' => 0,
    ]);

    $array = $collection->toArray();

    expect($array)->toHaveKeys(['id', 'title', 'description', 'private', 'media_count', 'photos_count', 'videos_count']);
});
