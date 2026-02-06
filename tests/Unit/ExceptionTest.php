<?php

use Jatniel\Pexels\Exceptions\PexelsException;
use Jatniel\Pexels\Exceptions\PhotoNotFoundException;
use Jatniel\Pexels\Exceptions\RateLimitException;

it('creates api key missing exception', function () {
    $exception = PexelsException::apiKeyMissing();

    expect($exception)->toBeInstanceOf(PexelsException::class)
        ->and($exception->getMessage())->toBe('Pexels API key is not configured. Set PEXELS_API_KEY in your .env file.');
});

it('creates invalid response exception', function () {
    $exception = PexelsException::invalidResponse('Bad request');

    expect($exception)->toBeInstanceOf(PexelsException::class)
        ->and($exception->getMessage())->toBe('Invalid response from Pexels API. Bad request');
});

it('creates invalid response exception without message', function () {
    $exception = PexelsException::invalidResponse();

    expect($exception->getMessage())->toBe('Invalid response from Pexels API.');
});

it('creates rate limit exceeded exception', function () {
    $exception = RateLimitException::exceeded(200);

    expect($exception)->toBeInstanceOf(RateLimitException::class)
        ->and($exception)->toBeInstanceOf(PexelsException::class)
        ->and($exception->getMessage())->toBe('Pexels API rate limit exceeded. Limit: 200 requests per hour.');
});

it('creates photo not found exception with id', function () {
    $exception = PhotoNotFoundException::withId(12345);

    expect($exception)->toBeInstanceOf(PhotoNotFoundException::class)
        ->and($exception)->toBeInstanceOf(PexelsException::class)
        ->and($exception->getMessage())->toBe('Photo with ID 12345 not found.');
});
