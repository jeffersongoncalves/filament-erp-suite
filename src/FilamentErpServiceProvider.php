<?php

namespace JeffersonGoncalves\FilamentErp\Umbrella;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentErpServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-erp';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile();
    }
}
