<?php

namespace Jatniel\Pexels\Http;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Jatniel\Pexels\Exceptions\PexelsException;
use Jatniel\Pexels\Exceptions\RateLimitException;

class PexelsClient
{
    private const BASE_URL = 'https://api.pexels.com/v1';

    private const RATE_LIMIT_KEY = 'pexels-api-requests';

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = $this->resolveApiKey();
    }

    /**
     * Resolve the API key based on environment.
     */
    private function resolveApiKey(): string
    {
        $testKey = config('pexels.api_key_test');
        $productionKey = config('pexels.api_key');

        // Use test key if available and not in production
        if ($testKey && app()->environment() !== 'production') {
            return $testKey;
        }

        if (! $productionKey) {
            throw PexelsException::apiKeyMissing();
        }

        return $productionKey;
    }

    /**
     * Make a GET request to the Pexels API, using the cache when enabled.
     */
    public function get(string $endpoint, array $query = []): array
    {
        if (! config('pexels.cache.enabled', true)) {
            return $this->send($endpoint, $query);
        }

        return Cache::remember(
            'pexels:'.md5($endpoint.serialize($query)),
            (int) config('pexels.cache.ttl', 3600),
            fn () => $this->send($endpoint, $query),
        );
    }

    /**
     * Send the request to the API and return the decoded body.
     */
    private function send(string $endpoint, array $query): array
    {
        $this->checkRateLimit();

        try {
            $response = $this->request()->get($endpoint, $query);
        } catch (ConnectionException $e) {
            throw PexelsException::connectionFailed($e);
        }

        $this->ensureSuccessful($response);

        return $response->json() ?? [];
    }

    /**
     * Build the HTTP request with authentication.
     */
    private function request(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->withHeaders(['Authorization' => $this->apiKey])
            ->acceptJson()
            ->timeout((int) config('pexels.timeout', 10))
            ->retry(2, 200, fn ($e) => $e instanceof ConnectionException, throw: false);
    }

    /**
     * Map failed responses to package exceptions.
     */
    private function ensureSuccessful(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        if ($response->status() === 429) {
            throw RateLimitException::fromApi();
        }

        throw PexelsException::requestFailed($response->status(), $response->body());
    }

    /**
     * Throw if the local rate limit is exceeded, otherwise count the request.
     */
    private function checkRateLimit(): void
    {
        if (! config('pexels.rate_limit.enabled', true)) {
            return;
        }

        $limit = (int) config('pexels.rate_limit.requests_per_hour', 200);

        if (RateLimiter::tooManyAttempts(self::RATE_LIMIT_KEY, $limit)) {
            throw RateLimitException::exceeded($limit);
        }

        RateLimiter::hit(self::RATE_LIMIT_KEY, 3600);
    }
}
