<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Jatniel\Pexels\Exceptions\PexelsException;
use Jatniel\Pexels\Exceptions\RateLimitException;
use Jatniel\Pexels\Http\PexelsClient;
use Jatniel\Pexels\Tests\Helpers;

beforeEach(function () {
    RateLimiter::clear('pexels-api-requests');
});

it('makes a GET request with authorization header', function () {
    Http::fake([
        'api.pexels.com/v1/search*' => Http::response(Helpers::searchResponse()),
    ]);

    $result = app(PexelsClient::class)->get('/search', ['query' => 'nature']);

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'test-api-key')
        && $request['query'] === 'nature'
    );

    expect($result['photos'])->toHaveCount(2);
});

it('throws exception when api key is missing', function () {
    config()->set('pexels.api_key', null);

    app(PexelsClient::class)->get('/curated');
})->throws(PexelsException::class, 'Pexels API key is not configured');

it('uses the test api key outside production', function () {
    config()->set('pexels.api_key_test', 'test-key');

    Http::fake(['api.pexels.com/v1/*' => Http::response([])]);

    app(PexelsClient::class)->get('/curated');

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'test-key'));
});

it('throws exception on failed response', function () {
    Http::fake(['api.pexels.com/v1/*' => Http::response('Server Error', 500)]);

    app(PexelsClient::class)->get('/curated');
})->throws(PexelsException::class, 'Pexels API request failed with status 500.');

it('throws rate limit exception when the API returns 429', function () {
    Http::fake(['api.pexels.com/v1/*' => Http::response('Too Many Requests', 429)]);

    app(PexelsClient::class)->get('/curated');
})->throws(RateLimitException::class);

it('caches responses without counting them against the rate limit', function () {
    config()->set('pexels.cache.enabled', true);
    config()->set('pexels.rate_limit.enabled', true);
    config()->set('pexels.rate_limit.requests_per_hour', 1);

    Http::fake(['api.pexels.com/v1/*' => Http::response(Helpers::searchResponse())]);

    $client = app(PexelsClient::class);

    expect($client->get('/search', ['query' => 'nature']))->toBe($client->get('/search', ['query' => 'nature']));
    Http::assertSentCount(1);
});

it('throws rate limit exception when the local limit is exceeded', function () {
    config()->set('pexels.rate_limit.enabled', true);
    config()->set('pexels.rate_limit.requests_per_hour', 1);

    Http::fake(['api.pexels.com/v1/*' => Http::response(Helpers::searchResponse())]);

    $client = app(PexelsClient::class);
    $client->get('/search', ['query' => 'first']);
    $client->get('/search', ['query' => 'second']);
})->throws(RateLimitException::class, 'Limit: 1 requests per hour.');
