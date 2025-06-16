<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Exness API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for the Exness API.
    |
    */

    'api_base' => env('EXNESS_API_BASE', 'https://my.exnessaffiliates.com'),
    
    'timeout' => env('EXNESS_API_TIMEOUT', 30),
    
    'connect_timeout' => env('EXNESS_API_CONNECT_TIMEOUT', 10),
]; 