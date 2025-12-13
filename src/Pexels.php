<?php

namespace Jatniel\Pexels;

use Jatniel\Pexels\Http\PexelsClient;
use Jatniel\Pexels\Services\CollectionService;
use Jatniel\Pexels\Services\PhotoService;
use Jatniel\Pexels\Services\StorageService;

class Pexels
{
    private ?PexelsClient $client = null;

    private ?PhotoService $photoService = null;

    private ?CollectionService $collectionService = null;

    private ?StorageService $storageService = null;

    /**
     * Get the photos service.
     */
    public function photos(): PhotoService
    {
        if ($this->photoService === null) {
            $this->photoService = new PhotoService($this->getClient());
        }

        return $this->photoService;
    }

    /**
     * Get the collections service.
     */
    public function collections(): CollectionService
    {
        if ($this->collectionService === null) {
            $this->collectionService = new CollectionService($this->getClient());
        }

        return $this->collectionService;
    }

    /**
     * Get the storage service.
     */
    public function storage(): StorageService
    {
        if ($this->storageService === null) {
            $this->storageService = new StorageService();
        }

        return $this->storageService;
    }

    /**
     * Get the HTTP client instance.
     */
    private function getClient(): PexelsClient
    {
        if ($this->client === null) {
            $this->client = new PexelsClient();
        }

        return $this->client;
    }
    
}
