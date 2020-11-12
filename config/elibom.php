<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | If you're using API credentials, change these settings. Get your
    | credentials from https://dashboard.elibom.com | 'Settings'.
    |
    */

    'api_key'    => function_exists('env') ? env('ELIBOM_KEY', '') : '',
    'api_secret' => function_exists('env') ? env('ELIBOM_SECRET', '') : '',

    'api_secret' => function_exists('env') ? env('ELIBOM_SECRET', '') : '',

   /*
    |--------------------------------------------------------------------------
    | Application Identifiers
    |--------------------------------------------------------------------------
    |
    | Add an application name and version here to identify your application when
    | making API calls
    |
    */

    'app' => ['name' => function_exists('env') ? env('ELIBOM_APP_NAME', 'ElibomLaravel') : 'ElibomLaravel',
        'version' => function_exists('env') ? env('ELIBOM_APP_VERSION', '1.1.2') : '1.1.2'],

    /*
    |--------------------------------------------------------------------------
    | Client Override
    |--------------------------------------------------------------------------
    |
    | In the event you need to use this with elibom/client-core, this can be set
    | to provide a custom HTTP client.
    |
    */

    'http_client' => function_exists('env') ? env('ELIBOM_HTTP_CLIENT', '') : '',
];
