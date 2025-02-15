<?php

namespace Kirschbaum\Commentions;

use Livewire\Livewire;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Filament\Support\Facades\FilamentAsset;
use Kirschbaum\Commentions\Livewire\Comment;
use Kirschbaum\Commentions\Livewire\CommentList;
use Kirschbaum\Commentions\Livewire\Comments;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CommentionsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('commentions')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_commention_tables')
        ;
    }

    public function packageBooted(): void
    {
        Livewire::component('commentions::comment', Comment::class);
        Livewire::component('commentions::comment-list', CommentList::class);
        Livewire::component('commentions::comments', Comments::class);

        // Asset Registration
        FilamentAsset::register(
            [
                Js::make('commentions-scripts', __DIR__ . '/../resources/dist/commentions.js'),
            ],
            'commentions'
        );

        FilamentAsset::register(
            [
                Css::make('commentions', __DIR__ . '/../resources/dist/commentions.css'),
            ],
            'commentions'
        );
    }
}
