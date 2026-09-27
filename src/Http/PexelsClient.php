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

    /**
     * @param  int|null  $cacheTtl  Seconds to cache responses, null disables the cache.
     * @param  int|null  $requestsPerHour  Local rate limit, null disables it.
     */
    public function __construct(
        private readonly ?string $apiKey,
        private readonly int $timeout = 10,
        private readonly ?int $cacheTtl = 3600,
        private readonly ?int $requestsPerHour = 200,
    ) {}

    /**
     * Build a client from the package configuration.
     *
     * The test key is used outside production when it is set.
     *
     * @param  array<string, mixed>  $config
     */
    public static function fromConfig(array $config, bool $production): self
    {
        $apiKey = ! $production && ! empty($config['api_key_test'])
            ? $config['api_key_test']
            : ($config['api_key'] ?? null);

        return new self(
            apiKey: $apiKey ?: null,
            timeout: (int) ($config['timeout'] ?? 10),
            cacheTtl: ($config['cache']['enabled'] ?? true) ? (int) ($config['cache']['ttl'] ?? 3600) : null,
            requestsPerHour: ($config['rate_limit']['enabled'] ?? true) ? (int) ($config['rate_limit']['requests_per_hour'] ?? 200) : null,
        );
    }

    /**
     * Make a GET request to the Pexels API, using the cache when enabled.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $endpoint, array $query = []): array
    {
        if ($this->cacheTtl === null) {
            return $this->send($endpoint, $query);
        }

        return Cache::remember(
            'pexels:'.md5($endpoint.serialize($query)),
            $this->cacheTtl,
            fn () => $this->send($endpoint, $query),
        );
    }

    /**
     * Send the request to the API and return the decoded body.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function send(string $endpoint, array $query): array
    {
        if (! $this->apiKey) {
            throw PexelsException::apiKeyMissing();
        }

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
            ->timeout($this->timeout)
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
        if ($this->requestsPerHour === null) {
            return;
        }

        if (RateLimiter::tooManyAttempts(self::RATE_LIMIT_KEY, $this->requestsPerHour)) {
            throw RateLimitException::exceeded($this->requestsPerHour);
        }

        RateLimiter::hit(self::RATE_LIMIT_KEY, 3600);
    }
}
