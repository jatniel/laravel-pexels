# Laravel Pexels

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jatniel/laravel-pexels.svg?style=flat-square)](https://packagist.org/packages/jatniel/laravel-pexels)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jatniel/laravel-pexels/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jatniel/laravel-pexels/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jatniel/laravel-pexels/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jatniel/laravel-pexels/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jatniel/laravel-pexels.svg?style=flat-square)](https://packagist.org/packages/jatniel/laravel-pexels)

A Laravel package for the [Pexels API](https://www.pexels.com/api/). Search photos with filters, browse curated photos and collections, download images to any storage disk, and drop photos into your views with Blade components.

> Developed by [Jatniel Guzmán](https://jatniel.dev) • [LinkedIn](https://www.linkedin.com/in/jatniel/) • [X/Twitter](https://x.com/jatnieldev)

## Requirements

- PHP 8.3+
- Laravel 11, 12 or 13

## Installation

Install the package via Composer:
```bash
composer require jatniel/laravel-pexels
```

Publish the configuration file:
```bash
php artisan vendor:publish --tag="pexels-config"
```

Add your Pexels API key to your `.env` file:
```env
PEXELS_API_KEY=your-api-key-here
```

Get your free API key at [pexels.com/api](https://www.pexels.com/api/).

## Usage

### Search Photos
```php
use Jatniel\Pexels\Facades\Pexels;

// Search photos
$photos = Pexels::photos()->search('nature', perPage: 15, page: 1);

// Search with filters
$photos = Pexels::photos()->search(
    'ocean',
    orientation: 'landscape', // landscape, portrait, square
    size: 'large',            // large (24MP), medium (12MP), small (4MP)
    color: 'blue',            // color name or hex code (#ffffff)
    locale: 'es-ES',
);

// Get curated photos
$curated = Pexels::photos()->curated(perPage: 10);

// Get a specific photo by ID
$photo = Pexels::photos()->find(12345);

// Get a random photo (by query, or from curated photos)
$random = Pexels::photos()->random('ocean');
$random = Pexels::photos()->random();

// Get the URL of a photo directly
$url = Pexels::photos()->url(12345, 'medium');
```

### Pagination

`search()`, `curated()` and the collection methods return a Laravel `LengthAwarePaginator` of `Photo` (or `Collection`) objects:
```php
$photos = Pexels::photos()->search('nature', perPage: 20, page: request('page', 1));

$photos->total();       // Total results on Pexels
$photos->currentPage();
$photos->lastPage();

foreach ($photos as $photo) {
    echo $photo->getUrl('medium');
}
```
```blade
{{ $photos->links() }}
```

### Working with Photos
```php
$photo = Pexels::photos()->find(12345);

// Get photo URL in different sizes
$photo->getUrl('original');
$photo->getUrl('large2x');
$photo->getUrl('large');
$photo->getUrl('medium');
$photo->getUrl('small');

// Get available sizes
$photo->getSizes(); // ['original', 'large2x', 'large', 'medium', 'small', ...]

// Get attribution (required by Pexels), escaped and safe to print with {!! !!}
$photo->getAttribution(); // <a href="...">Photo by John Doe on Pexels</a>
$photo->getAttribution(withLink: false); // Photo by John Doe on Pexels

// Photo data
$photo->id;
$photo->width;
$photo->height;
$photo->photographer;
$photo->avgColor;
$photo->alt;

// Photos (and collections) are Arrayable and JsonSerializable
return response()->json($photo);
```

### Collections
```php
// Get your collections
$collections = Pexels::collections()->all();

// Get photos from a collection
$photos = Pexels::collections()->photos('collection-id');
```

### Download Photos Locally
```php
$photo = Pexels::photos()->find(12345);

// Download a single size
$paths = Pexels::storage()->download($photo, 'original');
// ['original' => '/storage/pexels/12345/original.jpg']

// Download multiple sizes (sizes already stored are skipped)
$paths = Pexels::storage()->download($photo, ['original', 'medium', 'small']);

// Force re-download
$paths = Pexels::storage()->download($photo, 'original', force: true);

// Async download (queued on the configured connection and queue)
Pexels::storage()->downloadAsync($photo, ['original', 'medium']);

// Check if photo exists locally
Pexels::storage()->exists(12345, 'medium');

// Get local URL
Pexels::storage()->localUrl(12345, 'medium');

// Delete local photo
Pexels::storage()->delete(12345); // All sizes
Pexels::storage()->delete(12345, 'medium'); // Specific size
```

### Blade Components

#### Image Component
```blade
{{-- Basic usage --}}
<x-pexels-image id="12345" />

{{-- With specific size --}}
<x-pexels-image id="12345" size="medium" />

{{-- Random photo by query --}}
<x-pexels-image query="mountains" size="large" />

{{-- With attribution (required by Pexels license) --}}
<x-pexels-image id="12345" attribution />

{{-- Use locally downloaded image --}}
<x-pexels-image id="12345" local />

{{-- With custom attributes --}}
<x-pexels-image id="12345" class="rounded-lg shadow-md" />
```

#### Background Component
```blade
{{-- Basic usage --}}
<x-pexels-background id="12345" class="min-h-screen">
    <h1>Welcome</h1>
</x-pexels-background>

{{-- Random background --}}
<x-pexels-background query="ocean sunset" class="hero-section">
    <div class="content">Your content here</div>
</x-pexels-background>

{{-- With attribution --}}
<x-pexels-background id="12345" attribution attribution-position="bottom-right">
    <h1>Welcome</h1>
</x-pexels-background>
```

Available sizes: `original`, `large2x`, `large`, `medium`, `small`, `portrait`, `landscape`, `tiny`

If the Pexels API fails (network error, rate limit, photo not found), the components render nothing and the exception is reported through Laravel's exception handler, so a Pexels outage never breaks your page.

## Error Handling

All exceptions extend `Jatniel\Pexels\Exceptions\PexelsException`:
```php
use Jatniel\Pexels\Exceptions\PexelsException;
use Jatniel\Pexels\Exceptions\PhotoNotFoundException;
use Jatniel\Pexels\Exceptions\RateLimitException;

try {
    $photo = Pexels::photos()->find(12345);
} catch (PhotoNotFoundException $e) {
    // The photo does not exist
} catch (RateLimitException $e) {
    // Local limit or Pexels API limit (HTTP 429) reached
} catch (PexelsException $e) {
    // Missing API key, connection error or any other API error
}
```

## Caching and Rate Limiting

API responses are cached (1 hour by default), and cached responses do not count toward the rate limit. The local rate limiter stops requests before you hit the Pexels limit (200 requests/hour on the free plan). Connection errors are retried twice.

## Configuration
```php
// config/pexels.php

return [
    // API keys. PEXELS_API_KEY_TEST is used outside production when set.
    'api_key' => env('PEXELS_API_KEY'),
    'api_key_test' => env('PEXELS_API_KEY_TEST'),

    // HTTP timeout in seconds
    'timeout' => env('PEXELS_TIMEOUT', 10),

    // Cache settings
    'cache' => [
        'enabled' => env('PEXELS_CACHE_ENABLED', true),
        'ttl' => env('PEXELS_CACHE_TTL', 3600), // 1 hour
    ],

    // Storage settings for local downloads
    'storage' => [
        'disk' => env('PEXELS_STORAGE_DISK', 'public'),
        'path' => env('PEXELS_STORAGE_PATH', 'pexels'),
    ],

    // Rate limiting (free plan: 200 req/hour)
    'rate_limit' => [
        'enabled' => env('PEXELS_RATE_LIMIT_ENABLED', true),
        'requests_per_hour' => env('PEXELS_RATE_LIMIT_REQUESTS', 200),
    ],

    // Queue settings for async downloads
    'queue' => [
        'connection' => env('PEXELS_QUEUE_CONNECTION'),
        'name' => env('PEXELS_QUEUE_NAME', 'pexels'),
    ],

    // Attribution format
    'attribution' => [
        'format' => 'Photo by :photographer on Pexels',
        'link_to_profile' => true,
    ],
];
```

## Attribution

Pexels requires attribution to photographers. Use the `attribution` prop on Blade components or call `$photo->getAttribution()` to generate proper credit.

Read more: [Pexels License](https://www.pexels.com/license/)

## Testing
```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Issues and pull requests are welcome on [GitHub](https://github.com/jatniel/laravel-pexels). Please run `composer test`, `composer analyse` and `composer format` before submitting.

## Security Vulnerabilities

If you discover a security vulnerability, please email [hello@jatniel.dev](mailto:hello@jatniel.dev) instead of opening a public issue.

## Credits

- [Jatniel Guzmán](https://jatniel.dev) - Freelance Web Developer with 20+ years of experience specializing in PHP (Laravel, Symfony), Python, and modern frontend technologies.
    - [LinkedIn](https://www.linkedin.com/in/jatniel/)
    - [X/Twitter](https://x.com/jatnieldev)
    - [GitHub](https://github.com/jatniel)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
