<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Jatniel\Pexels\Exceptions\PexelsException;
use Jatniel\Pexels\Exceptions\RateLimitException;
use Jatniel\Pexels\Http\PexelsClient;
use Jatniel\Pexels\Tests\Helpers;

beforeEach(function () {
    config()->set('pexels.api_key', 'test-api-key');
    config()->set('pexels.cache.enabled', false);
    config()->set('pexels.rate_limit.enabled', false);
});

it('makes a GET request with authorization header', function () {
    Http::fake([
        'api.pexels.com/v1/search*' => Http::response(Helpers::searchResponse()),
    ]);

    $client = new PexelsClient;
    $result = $client->get('/search', ['query' => 'nature']);

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'test-api-key')
            && str_contains($request->url(), '/search')
            && $request['query'] === 'nature';
    });

    expect($result)->toHaveKey('photos')
        ->and($result['photos'])->toHaveCount(2);
});

it('throws exception when api key is missing', function () {
    config()->set('pexels.api_key', null);
    config()->set('pexels.api_key_test', null);

    new PexelsClient;
})->throws(PexelsException::class, 'Pexels API key is not configured');

it('uses test api key in non-production environment', function () {
    config()->set('pexels.api_key', 'production-key');
    config()->set('pexels.api_key_test', 'test-key');
    app()->detectEnvironment(fn () => 'testing');

    Http::fake([
        'api.pexels.com/v1/*' => Http::response(['data' => 'ok']),
    ]);

    $client = new PexelsClient;
    $client->get('/curated');

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'test-key'));
});

it('throws exception on failed response', function () {
    Http::fake([
        'api.pexels.com/v1/*' => Http::response('Server Error', 500),
    ]);

    $client = new PexelsClient;
    $client->get('/search', ['query' => 'test']);
})->throws(PexelsException::class, 'Invalid response from Pexels API.');

it('caches responses when cache is enabled', function () {
    config()->set('pexels.cache.enabled', true);
    config()->set('pexels.cache.ttl', 3600);

    Http::fake([
        'api.pexels.com/v1/*' => Http::response(Helpers::searchResponse()),
    ]);

    $client = new PexelsClient;

    // First call - hits the API
    $result1 = $client->get('/search', ['query' => 'nature']);
    // Second call - should use cache
    $result2 = $client->get('/search', ['query' => 'nature']);

    Http::assertSentCount(1);
    expect($result1)->toBe($result2);
});

it('skips cache when disabled', function () {
    config()->set('pexels.cache.enabled', false);

    Http::fake([
        'api.pexels.com/v1/*' => Http::response(Helpers::searchResponse()),
    ]);

    $client = new PexelsClient;
    $client->get('/search', ['query' => 'nature']);
    $client->get('/search', ['query' => 'nature']);

    Http::assertSentCount(2);
});

it('throws rate limit exception when limit is exceeded', function () {
    config()->set('pexels.rate_limit.enabled', true);
    config()->set('pexels.rate_limit.requests_per_hour', 2);

    Http::fake([
        'api.pexels.com/v1/*' => Http::response(Helpers::searchResponse()),
    ]);

    RateLimiter::clear('pexels-api-requests');

    $client = new PexelsClient;
    $client->get('/search', ['query' => 'test1']);
    $client->get('/search', ['query' => 'test2']);

    // Third request should exceed the limit
    $client->get('/search', ['query' => 'test3']);
})->throws(RateLimitException::class);

it('does not check rate limit when disabled', function () {
    config()->set('pexels.rate_limit.enabled', false);

    Http::fake([
        'api.pexels.com/v1/*' => Http::response(Helpers::searchResponse()),
    ]);

    $client = new PexelsClient;

    // Should not throw even with many requests
    for ($i = 0; $i < 5; $i++) {
        $client->get('/search', ['query' => "test{$i}"]);
    }

    Http::assertSentCount(5);
});
