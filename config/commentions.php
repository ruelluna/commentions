<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Table name configurations
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'comments' => 'comments',
        'comment_reactions' => 'comment_reactions',
        'comment_subscriptions' => 'comment_subscriptions',
        'attachments' => 'comment_attachments',
    ],

    /*
    |--------------------------------------------------------------------------
    | Commenter model configuration
    |--------------------------------------------------------------------------
    */
    'commenter' => [
        'model' => \App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Comment model configuration
    |--------------------------------------------------------------------------
    */
    'comment' => [
        'model' => \Kirschbaum\Commentions\Comment::class,
        'policy' => \Kirschbaum\Commentions\Policies\CommentPolicy::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reactions
    |--------------------------------------------------------------------------
    */
    'reactions' => [
        'allowed' => ['👍', '❤️', '😂', '😮', '😢', '🤔'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscriptions
    |--------------------------------------------------------------------------
    */
    'subscriptions' => [
        // When true, subscribed users will also receive the same event as mentions
        // (UserWasMentionedEvent). When false, a distinct
        // UserIsSubscribedToCommentableEvent will be dispatched instead.
        'dispatch_as_mention' => env('COMMENTIONS_SUBSCRIPTIONS_DISPATCH_AS_MENTION', false),
        // Controls whether the subscribers list is shown in the sidebar UI
        'show_subscribers' => env('COMMENTIONS_SUBSCRIPTIONS_SHOW_SUBSCRIBERS', true),
        // Automatically subscribe the author when they add a comment
        'auto_subscribe_on_comment' => env('COMMENTIONS_SUBSCRIPTIONS_AUTO_SUBSCRIBE_ON_COMMENT', true),
        // Automatically subscribe a user when they are mentioned in a comment
        'auto_subscribe_on_mention' => env('COMMENTIONS_SUBSCRIPTIONS_AUTO_SUBSCRIBE_ON_MENTION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Uploads
    |--------------------------------------------------------------------------
    |
    | Configure file upload settings for comment attachments.
    |
    */
    'uploads' => [
        'enabled' => env('COMMENTIONS_UPLOADS_ENABLED', true),
        'max_file_size' => env('COMMENTIONS_UPLOADS_MAX_SIZE', 10240), // KB
        'max_files' => env('COMMENTIONS_UPLOADS_MAX_FILES', 5),
        'allowed_types' => explode(',', env('COMMENTIONS_UPLOADS_ALLOWED_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,txt,zip')),
        'disk' => env('COMMENTIONS_UPLOADS_DISK', 'local'),
        'path' => env('COMMENTIONS_UPLOADS_PATH', 'commentions/attachments'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications (opt-in)
    |--------------------------------------------------------------------------
    |
    | Configure notification delivery when a user is mentioned in a comment.
    | Disabled by default; enable and choose the channels you want to use.
    |
    */
    'notifications' => [
        'mentions' => [
            'enabled' => env('COMMENTIONS_NOTIFICATIONS_MENTIONS_ENABLED', false),

            'channels' => explode(',', env('COMMENTIONS_NOTIFICATIONS_MENTIONS_CHANNELS', 'mail')),

            'listener' => \Kirschbaum\Commentions\Listeners\SendUserMentionedNotification::class,
            'notification' => \Kirschbaum\Commentions\Notifications\UserMentionedInComment::class,

            'mail' => [
                'subject' => env('COMMENTIONS_NOTIFICATIONS_MENTIONS_MAIL_SUBJECT', 'You were mentioned in a comment'),
            ],
        ],
    ],
];
