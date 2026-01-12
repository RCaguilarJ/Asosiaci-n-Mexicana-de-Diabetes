<?php
// Bridge file so API scripts that do require_once('../config/headers.php') find the bootstrap.
// This forwards to the actual API headers implementation in api/config/headers.php.
$apiHeaders = __DIR__ . '/api/config/headers.php';
if (file_exists($apiHeaders)) {
    require_once $apiHeaders;
} else {
    // Fallback minimal headers to avoid fatal errors if file is missing.
    header('Content-Type: application/json; charset=utf-8');
}
