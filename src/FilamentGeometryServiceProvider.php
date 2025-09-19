<?php

namespace Swis\Filament\Geometry;

use Filament\Support\Assets;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentGeometryServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-geometry';

    public static string $viewNamespace = 'filament-geometry';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasViews()
            ->hasTranslations()
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->askToStarRepoOnGitHub('swisnl/filament-geometry');
            });
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );
    }

    protected function getAssetPackageName(): ?string
    {
        return 'swisnl/filament-geometry';
    }

    /**
     * @return array<\Filament\Support\Assets\Asset>
     */
    protected function getAssets(): array
    {
        return [
            Assets\AlpineComponent::make('filament-geometry-scripts', __DIR__.'/../resources/dist/filament-geometry.js'),
            Assets\Css::make('filament-geometry-styles', __DIR__.'/../resources/dist/filament-geometry.css')->loadedOnRequest(),
        ];
    }
}
