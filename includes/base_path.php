<?php
/**
 * Base path helper for deployments in root or subfolder.
 */
function amd_base_path() {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $prefix = '/asosiacionMexicanaDeDiabetes';

    if ($uriPath === $prefix || strpos($uriPath, $prefix . '/') === 0) {
        return $prefix;
    }

    return '';
}

$basePath = amd_base_path();
?>
