<?php

namespace Kirschbaum\Commentions;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Kirschbaum\Commentions\Comment as CommentModel;
use Kirschbaum\Commentions\Events\UserWasMentionedEvent;
use Kirschbaum\Commentions\Listeners\SendUserMentionedNotification;
use Kirschbaum\Commentions\Livewire\Comment;
use Kirschbaum\Commentions\Livewire\CommentList;
use Kirschbaum\Commentions\Livewire\Comments;
use Kirschbaum\Commentions\Livewire\Reactions;
use Kirschbaum\Commentions\Livewire\SubscriptionSidebar;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CommentionsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'commentions';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews()
            ->hasMigrations([
                'create_commentions_tables',
                'create_commentions_reactions_table',
                'create_commentions_subscriptions_table',
                'create_commentions_attachments_table',
            ]);
    }

    public function packageBooted(): void
    {
        Livewire::component('commentions::comment', Comment::class);
        Livewire::component('commentions::comment-list', CommentList::class);
        Livewire::component('commentions::comments', Comments::class);
        Livewire::component('commentions::reactions', Reactions::class);
        Livewire::component('commentions::subscription-sidebar', SubscriptionSidebar::class);

        FilamentAsset::register(
            [
                Js::make('commentions-scripts', __DIR__ . '/../resources/dist/commentions.js'),
            ],
            'kirschbaum-development/' . static::$name
        );

        FilamentAsset::register(
            [
                Css::make('commentions', __DIR__ . '/../resources/dist/commentions.css'),
            ],
            'kirschbaum-development/' . static::$name
        );

        Gate::policy(CommentModel::class, config('commentions.comment.policy'));
        Gate::policy(CommentAttachment::class, config('commentions.attachment.policy'));

        // Allow publishing of translation files with a custom tag
        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/commentions'),
        ], 'commentions-lang');

        if (config('commentions.notifications.mentions.enabled', false)) {
            $listenerClass = (string) config('commentions.notifications.mentions.listener', SendUserMentionedNotification::class);
            Event::listen(UserWasMentionedEvent::class, $listenerClass);
        }

        // Add image upload route
        Route::post('/commentions/upload-image', function () {
            $request = request();

            if (! $request->hasFile('image')) {
                return response()->json(['error' => 'No image file provided'], 400);
            }

            $file = $request->file('image');

            // Validate file type
            if (! $file->isValid() || ! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                return response()->json(['error' => 'Invalid file type'], 400);
            }

            // Get max size from config
            $maxSize = config('commentions.uploads.max_size', 2 * 1024 * 1024);

            // Validate file size
            if ($file->getSize() > $maxSize) {
                return response()->json(['error' => 'File too large. Maximum size: ' . round($maxSize / 1024 / 1024, 1) . 'MB'], 400);
            }

            try {
                // Store the file
                $path = $file->store('commentions/images', config('commentions.uploads.disk', 'public'));

                // Get the URL
                $url = Storage::disk(config('commentions.uploads.disk', 'public'))->url($path);

                return response()->json(['url' => $url]);
            } catch (\Exception $e) {
                \Log::error('File upload failed: ' . $e->getMessage());

                return response()->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
            }
        })->name('commentions.upload-image');
    }
}
