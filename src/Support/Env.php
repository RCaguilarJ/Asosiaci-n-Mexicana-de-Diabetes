<?php

namespace Amd\Support;

final class Env
{
    public static function load(?string $appEnv = null, ?array $paths = null): bool
    {
        $appEnv = strtolower($appEnv ?? getenv('APP_ENV') ?: '');
        $paths = $paths ?? self::defaultPaths($appEnv);

        foreach ($paths as $path) {
            if (self::loadFile($path)) {
                return true;
            }
        }

        return false;
    }

    public static function loadFile(string $path): bool
    {
        if (!file_exists($path)) {
            return false;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if ($line === null || $line === '') {
                continue;
            }

            $line = trim($line);

            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ($value !== '' && self::isQuoted($value)) {
                $value = substr($value, 1, -1);
            }

            if (getenv($key) === false) {
                putenv($key . '=' . $value);
            }
        }

        return true;
    }

    private static function defaultPaths(string $appEnv): array
    {
        $root = dirname(__DIR__, 2);
        return [$root . '/.env'];
    }

    private static function isQuoted(string $value): bool
    {
        $first = substr($value, 0, 1);
        $last = substr($value, -1);

        return ($first === '"' && $last === '"') || ($first === "'" && $last === "'");
    }
}
