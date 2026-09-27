<?php

namespace Jatniel\Pexels\Exceptions;

use Exception;
use Throwable;

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

    public static function requestFailed(int $status, string $body = ''): self
    {
        return new self("Pexels API request failed with status {$status}.".($body ? " {$body}" : ''), $status);
    }

    public static function connectionFailed(Throwable $previous): self
    {
        return new self('Could not connect to the Pexels API.', 0, $previous);
    }
}
