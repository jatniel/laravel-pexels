<?php

namespace Jatniel\Pexels;

use Illuminate\Support\Facades\Blade;
use Jatniel\Pexels\Components\Background;
use Jatniel\Pexels\Components\Image;
use Jatniel\Pexels\Http\PexelsClient;
use Jatniel\Pexels\Services\CollectionService;
use Jatniel\Pexels\Services\PhotoService;
use Jatniel\Pexels\Services\StorageService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PexelsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-pexels')
            ->hasConfigFile('pexels')
            ->hasViews('pexels');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PexelsClient::class, fn ($app) => PexelsClient::fromConfig(
            $app['config']->get('pexels', []),
            $app->environment('production'),
        ));

        $this->app->singleton(StorageService::class, fn ($app) => new StorageService(
            disk: $app['config']->get('pexels.storage.disk', 'public'),
            path: $app['config']->get('pexels.storage.path', 'pexels'),
            queueConnection: $app['config']->get('pexels.queue.connection'),
            queue: $app['config']->get('pexels.queue.name', 'pexels'),
        ));

        $this->app->singleton(PhotoService::class);
        $this->app->singleton(CollectionService::class);
        $this->app->singleton(Pexels::class);
    }

    public function packageBooted(): void
    {
        Blade::component('pexels-image', Image::class);
        Blade::component('pexels-background', Background::class);
    }
}
