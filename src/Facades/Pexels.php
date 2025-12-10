<?php

namespace Jatniel\Pexels\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jatniel\Pexels\Pexels
 */
class Pexels extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Jatniel\Pexels\Pexels::class;
    }
}
