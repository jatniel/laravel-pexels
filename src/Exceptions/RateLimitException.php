<?php

namespace Jatniel\Pexels\Exceptions;

class RateLimitException extends PexelsException
{
    public static function exceeded(int $limit): self
    {
        return new self("Pexels API rate limit exceeded. Limit: {$limit} requests per hour.");
    }
}
