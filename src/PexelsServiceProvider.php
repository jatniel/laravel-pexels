<?php

namespace Jatniel\Pexels;

use Illuminate\Support\Facades\Blade;
use Jatniel\Pexels\Components\Background;
use Jatniel\Pexels\Components\Image;
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
        $this->app->singleton(Pexels::class, fn () => new Pexels);
    }

    public function packageBooted(): void
    {
        Blade::component('pexels-image', Image::class);
        Blade::component('pexels-background', Background::class);
    }
}
