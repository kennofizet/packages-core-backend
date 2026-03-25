<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Table Name
    |--------------------------------------------------------------------------
    | The name of the users table in your database.
    | Default: 'users'
    */
    'table_user' => env('KNF_CORE_TABLE_USER', 'users'),

    /*
    |--------------------------------------------------------------------------
    | User Display Name Column
    |--------------------------------------------------------------------------
    | Column on the users table used as the "display name" in APIs/UI.
    | Example: 'name', 'username', 'full_name'
    | Default: null — do not include/display a name unless the host app does it
    */
    'user_col_name' => env('KNF_CORE_USER_COL_NAME', 'name'),

    /*
    |--------------------------------------------------------------------------
    | User Server ID Column
    |--------------------------------------------------------------------------
    | The name of the column on the users table that stores the server ID.
    | Default: null — merges all users into one server
    */
    'user_server_id_column' => env('KNF_CORE_USER_SERVER_ID_COLUMN'),

    /*
    |--------------------------------------------------------------------------
    | Table Prefix
    |--------------------------------------------------------------------------
    | Prefix for all core tables (e.g. 'rp_' → 'rp_knf_core_tokens').
    | Default: '' (no prefix)
    */
    'table_prefix' => env('KNF_CORE_TABLE_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | API Route Prefix
    |--------------------------------------------------------------------------
    | Prefix for all core API routes.
    | Default: 'api/knf'
    */
    'api_prefix' => env('KNF_CORE_API_PREFIX', 'api/knf'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    | Maximum requests per minute per token.
    | Default: 60
    */
    'rate_limit' => env('KNF_CORE_RATE_LIMIT', 60),
];
