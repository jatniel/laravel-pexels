<?php

namespace Jatniel\Pexels\Facades;

use Illuminate\Support\Facades\Facade;
use Jatniel\Pexels\Services\CollectionService;
use Jatniel\Pexels\Services\PhotoService;
use Jatniel\Pexels\Services\StorageService;

/**
 * @method static PhotoService photos()
 * @method static CollectionService collections()
 * @method static StorageService storage()
 *
 * @see \Jatniel\Pexels\Pexels
 */
class Pexels extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Jatniel\Pexels\Pexels::class;
    }
}
