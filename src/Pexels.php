<?php

namespace Jatniel\Pexels;

use Jatniel\Pexels\Services\CollectionService;
use Jatniel\Pexels\Services\PhotoService;
use Jatniel\Pexels\Services\StorageService;

class Pexels
{
    public function __construct(
        private readonly PhotoService $photos,
        private readonly CollectionService $collections,
        private readonly StorageService $storage,
    ) {}

    public function photos(): PhotoService
    {
        return $this->photos;
    }

    public function collections(): CollectionService
    {
        return $this->collections;
    }

    public function storage(): StorageService
    {
        return $this->storage;
    }
}
