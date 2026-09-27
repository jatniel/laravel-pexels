<?php

use Jatniel\Pexels\Facades\Pexels as PexelsFacade;
use Jatniel\Pexels\Pexels;
use Jatniel\Pexels\Services\CollectionService;
use Jatniel\Pexels\Services\PhotoService;
use Jatniel\Pexels\Services\StorageService;

it('resolves the services through the container and facade', function () {
    expect(app(Pexels::class))->toBe(app(Pexels::class))
        ->and(PexelsFacade::photos())->toBeInstanceOf(PhotoService::class)->toBe(app(PhotoService::class))
        ->and(PexelsFacade::collections())->toBeInstanceOf(CollectionService::class)
        ->and(PexelsFacade::storage())->toBeInstanceOf(StorageService::class);
});
