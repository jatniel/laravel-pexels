<?php

namespace Jatniel\Pexels;

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
        $this->app->singleton(Pexels::class, fn () => new Pexels());
    }
}
