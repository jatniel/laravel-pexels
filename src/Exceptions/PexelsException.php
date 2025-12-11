<?php

namespace Jatniel\Pexels\Exceptions;

use Exception;

class PexelsException extends Exception
{
    public static function apiKeyMissing(): self
    {
        return new self('Pexels API key is not configured. Set PEXELS_API_KEY in your .env file.');
    }

    public static function invalidResponse(string $message = ''): self
    {
        return new self('Invalid response from Pexels API.'.($message ? " {$message}" : ''));
    }
}
