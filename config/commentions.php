<?php

return [
    /**
     * The table name.
     */
    'table_name' => 'comments',

    /**
     * The commenter config.
     */
    'commenter' => [
        'model' => \App\Models\User::class,
    ],
];
