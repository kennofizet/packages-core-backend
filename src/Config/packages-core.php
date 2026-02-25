<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Table Name
    |--------------------------------------------------------------------------
    | The name of the users table in your database.
    | Default: 'users'
    */
    'table_user' => env('REWARDPLAY_TABLE_USER', 'users'),

    /*
    |--------------------------------------------------------------------------
    | User Server ID Column
    |--------------------------------------------------------------------------
    | The name of the column on the users table that stores the server ID.
    | Default: null — merges all users into one server
    */
    'user_server_id_column' => env('REWARDPLAY_USER_SERVER_ID_COLUMN'),

    /*
    |--------------------------------------------------------------------------
    | Table Prefix
    |--------------------------------------------------------------------------
    | Prefix for all core tables (e.g. 'rp_' → 'rp_rewardplay_tokens').
    | Default: '' (no prefix)
    */
    'table_prefix' => env('REWARDPLAY_TABLE_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | API Route Prefix
    |--------------------------------------------------------------------------
    | Prefix for all core API routes.
    | Default: 'api/rewardplay'
    */
    'api_prefix' => env('REWARDPLAY_API_PREFIX', 'api/rewardplay'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    | Maximum requests per minute per token.
    | Default: 60
    */
    'rate_limit' => env('REWARDPLAY_RATE_LIMIT', 60),
];
