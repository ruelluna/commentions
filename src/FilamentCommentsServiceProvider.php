<?php

namespace Kirschbaum\FilamentComments;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Filament\Support\Facades\FilamentAsset;
use Kirschbaum\FilamentComments\Livewire\Comments;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentCommentsServiceProvider extends PackageServiceProvider
{
    public function boot()
    {
        parent::boot();

        Livewire::component('comments', Comments::class);
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('filament-comments')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_filament_comment_tables')
        ;
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            [
                Js::make('filament-comments-scripts', __DIR__ . '/../resources/dist/filament-comments.js'),
            ],
            'filament-comments'
        );

        FilamentAsset::register(
            [
                Css::make('filament-comments', __DIR__ . '/../resources/dist/filament-comments.css'),
            ],
            'filament-comments'
        );

        // FilamentAsset::registerScriptData(
        //     $this->getScriptData(),
        //     $this->getAssetPackageName()
        // );
    }
}
