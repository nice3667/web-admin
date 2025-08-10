<?php
// Bootstrap Laravel application
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Include the debug script
ob_start();
include_once __DIR__ . '/../debug_janischa_clients.php';
$output = ob_get_clean();

// Set headers
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output the debug information
echo $output;
