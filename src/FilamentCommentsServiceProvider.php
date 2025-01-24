<?php

namespace Kirschbaum\FilamentComments;

use Livewire\Livewire;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Filament\Support\Facades\FilamentAsset;
use Kirschbaum\FilamentComments\Livewire\Comment;
use Kirschbaum\FilamentComments\Livewire\CommentList;
use Kirschbaum\FilamentComments\Livewire\Comments;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentCommentsServiceProvider extends PackageServiceProvider
{
    public function boot()
    {
        parent::boot();

        Livewire::component('filament-comments::comment', Comment::class);
        Livewire::component('filament-comments::comments', Comments::class);

        // Blade::componentNamespace('Kirschbaum\\FilamentComments\\View\\Components', 'filament-comments');
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
        Livewire::component('filament-comments::comment', Comment::class);
        Livewire::component('filament-comments::comment-list', CommentList::class);
        Livewire::component('filament-comments::comments', Comments::class);

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
    }
}
