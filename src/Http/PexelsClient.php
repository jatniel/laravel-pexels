<?php

namespace Jatniel\Pexels\Http;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Jatniel\Pexels\Exceptions\PexelsException;
use Jatniel\Pexels\Exceptions\RateLimitException;

class PexelsClient
{
    private const BASE_URL = 'https://api.pexels.com/v1';

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
     * Make a GET request to the Pexels API.
     */
    public function get(string $endpoint, array $query = []): array
    {
        $this->checkRateLimit();

        $cacheKey = $this->getCacheKey($endpoint, $query);

        if ($this->shouldCache() && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = $this->request()->get($endpoint, $query);

        if ($response->failed()) {
            throw PexelsException::invalidResponse($response->body());
        }

        $data = $response->json();

        if ($this->shouldCache()) {
            Cache::put($cacheKey, $data, config('pexels.cache.ttl', 3600));
        }

        return $data;
    }

    /**
     * Build the HTTP request with authentication.
     */
    private function request(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->withHeaders([
                'Authorization' => $this->apiKey,
            ])
            ->acceptJson();
    }

    /**
     * Check if rate limiting is exceeded.
     */
    private function checkRateLimit(): void
    {
        if (! config('pexels.rate_limit.enabled', true)) {
            return;
        }

        $limit = config('pexels.rate_limit.requests_per_hour', 200);
        $key = 'pexels-api-requests';

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            throw RateLimitException::exceeded($limit);
        }

        RateLimiter::hit($key, 3600); // 1 hour decay
    }

    /**
     * Determine if caching is enabled.
     */
    private function shouldCache(): bool
    {
        return config('pexels.cache.enabled', true);
    }

    /**
     * Generate a cache key for the request.
     */
    private function getCacheKey(string $endpoint, array $query): string
    {
        return 'pexels:' . md5($endpoint . serialize($query));
    }
    
}
