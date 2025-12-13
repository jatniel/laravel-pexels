# Laravel Pexels

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jatniel/laravel-pexels.svg?style=flat-square)](https://packagist.org/packages/jatniel/laravel-pexels)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jatniel/laravel-pexels/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jatniel/laravel-pexels/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jatniel/laravel-pexels/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jatniel/laravel-pexels/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jatniel/laravel-pexels.svg?style=flat-square)](https://packagist.org/packages/jatniel/laravel-pexels)

A Laravel package to integrate the [Pexels API](https://www.pexels.com/api/) for photos and videos. Search photos, get curated collections, download images locally, and use Blade components for easy integration.

> Developed by [Jatniel Guzmán](https://jatniel.dev) • [LinkedIn](https://www.linkedin.com/in/jatniel/) • [X/Twitter](https://x.com/jatnieldev)

## Requirements

- PHP 8.2+
- Laravel 11.0+

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
$photos = Pexels::photos()->search('nature', perPage: 15);

// Get a specific photo by ID
$photo = Pexels::photos()->find(12345);

// Get curated photos
$curated = Pexels::photos()->curated(perPage: 10);

// Get a random photo
$random = Pexels::photos()->random('ocean');
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

// Get attribution (required by Pexels)
$photo->getAttribution(); // <a href="...">Photo by John Doe on Pexels</a>
$photo->getAttribution(withLink: false); // Photo by John Doe on Pexels
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

// Download multiple sizes
$paths = Pexels::storage()->download($photo, ['original', 'medium', 'small']);

// Async download (queued)
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

## Configuration
```php
// config/pexels.php

return [
    // API Keys
    'api_key' => env('PEXELS_API_KEY'),
    'api_key_test' => env('PEXELS_API_KEY_TEST'),

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
        'enabled' => env('PEXELS_QUEUE_ENABLED', true),
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

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jatniel Guzmán](https://jatniel.dev) - Freelance Web Developer with 20+ years of experience specializing in PHP (Laravel, Symfony), Python, and modern frontend technologies.
    - [LinkedIn](https://www.linkedin.com/in/jatniel/)
    - [X/Twitter](https://x.com/jatnieldev)
    - [GitHub](https://github.com/jatniel)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
