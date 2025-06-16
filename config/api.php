<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for API endpoints and settings.
    |
    */

    'exness' => [
        'base_url' => env('EXNESS_API_URL', 'https://my.exnessaffiliates.com'),
        'auth_endpoint' => '/api/v2/auth/',
        'clients_v1_endpoint' => '/api/reports/clients/',
        'clients_v2_endpoint' => '/api/v2/reports/clients/',
        'timeout' => 30,
    ],

    'domain' => [
        'allowed_domains' => [
            'localhost',
            '127.0.0.1',
            'gogoldadmin.com',
        ],
    ],
]; 