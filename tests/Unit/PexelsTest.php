<?php

use Jatniel\Pexels\Pexels;
use Jatniel\Pexels\Services\CollectionService;
use Jatniel\Pexels\Services\PhotoService;
use Jatniel\Pexels\Services\StorageService;

it('returns photo service instance', function () {
    $pexels = app(Pexels::class);

    expect($pexels->photos())->toBeInstanceOf(PhotoService::class);
});

it('returns collection service instance', function () {
    $pexels = app(Pexels::class);

    expect($pexels->collections())->toBeInstanceOf(CollectionService::class);
});

it('returns storage service instance', function () {
    $pexels = app(Pexels::class);

    expect($pexels->storage())->toBeInstanceOf(StorageService::class);
});

it('returns the same service instance on multiple calls', function () {
    $pexels = app(Pexels::class);

    $photos1 = $pexels->photos();
    $photos2 = $pexels->photos();

    expect($photos1)->toBe($photos2);
});

it('is registered as singleton in the container', function () {
    $instance1 = app(Pexels::class);
    $instance2 = app(Pexels::class);

    expect($instance1)->toBe($instance2);
});
