<?php

namespace Jatniel\Pexels;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Jatniel\Pexels\Commands\PexelsCommand;

class PexelsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-pexels')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_pexels_table')
            ->hasCommand(PexelsCommand::class);
    }
}
