<?php

namespace Jatniel\Pexels\Exceptions;

class PhotoNotFoundException extends PexelsException
{
    public static function withId(int $id): self
    {
        return new self("Photo with ID {$id} not found.");
    }
}
