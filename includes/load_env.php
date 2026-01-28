<?php
/**
 * Legacy env loader.
 * Prefer Amd\Support\Env via Composer autoload when available.
 */

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

if (class_exists('Amd\\Support\\Env')) {
    Amd\Support\Env::load();
    return;
}

function loadEnvFile($path) {
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if ($line === null || $line === '') {
            continue;
        }

        $line = trim($line);

        if ($line === '') {
            continue;
        }

        if (strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }

            if (getenv($key) === false) {
                putenv("$key=$value");
            }
        }
    }

    return true;
}

$envPath = __DIR__ . '/../.env';
loadEnvFile($envPath);
?>
