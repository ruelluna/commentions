<?php

namespace Kirschbaum\FilamentComments;

use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
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
}
