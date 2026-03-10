<?php
/**
 * Base path helper for deployments in root or subfolder.
 */
function amd_base_path() {
    $envBasePath = getenv('APP_BASE_PATH');
    if ($envBasePath !== false) {
        $envBasePath = trim($envBasePath);
        if ($envBasePath === '' || $envBasePath === '/') {
            return '';
        }
        return '/' . trim($envBasePath, " \t\n\r\0\x0B/");
    }

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
    if ($scriptName !== '') {
        $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        $stripDirs = [
            'views',
            'actions',
            'includes',
            'api',
            'workers',
            'scripts',
            'migrations',
            'archive',
        ];
        if ($dir !== '' && $dir !== '.') {
            $segments = explode('/', ltrim($dir, '/'));
            if (!empty($segments) && in_array(end($segments), $stripDirs, true)) {
                array_pop($segments);
                $dir = '/' . implode('/', $segments);
            }
        }
        if ($dir === '/' || $dir === '.') {
            return '';
        }
        return $dir;
    }

    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $appRoot = realpath(__DIR__ . '/..');

    if ($docRoot && $appRoot && strpos($appRoot, $docRoot) === 0) {
        $relative = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
        $relative = '/' . ltrim($relative, '/');
        return $relative === '/' ? '' : $relative;
    }

    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $prefix = '/asosiacionMexicanaDeDiabetes';

    if ($uriPath === $prefix || strpos($uriPath, $prefix . '/') === 0) {
        return $prefix;
    }

    return '';
}

$basePath = amd_base_path();
?>
