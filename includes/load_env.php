<?php
/**
 * Carga variables de entorno desde archivo .env
 * Usado en desarrollo para configurar conexiones sin exponer credenciales
 */

function loadEnvFile($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip if line is null or empty before trimming
        if ($line === null || $line === '') {
            continue;
        }
        
        $line = trim($line);
        
        // Skip empty lines after trimming
        if ($line === '') {
            continue;
        }
        
        // Saltar comentarios
        if (strpos($line, '#') === 0) {
            continue;
        }
        
        // Buscar formato KEY=value
        if (strpos($lSine, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remover comillas si existen
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }
            
            // Solo establecer si no existe ya en el environment
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
    
    return true;
}

// Intentar cargar .env desde varios lugares posibles
$envPaths = [
    __DIR__ . '/../.env',           // Raíz del proyecto
    __DIR__ . '/.env',              // Carpeta includes
    dirname(__DIR__) . '/.env'      // Carpeta padre
];

foreach ($envPaths as $envPath) {
    if (loadEnvFile($envPath)) {
        break; // Cargar solo el primer .env encontrado
    }
}
?>