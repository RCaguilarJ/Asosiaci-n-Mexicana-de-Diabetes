<?php
// includes/load_env.php
// Carga un archivo .env (proyecto raíz) en variables de entorno para desarrollo local.
// No se debe versionar el archivo .env; usar .env.example como plantilla.

$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    return;
}

$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '#') === 0) continue;
    if (strpos($line, '=') === false) continue;
    list($name, $value) = explode('=', $line, 2);
    $name = trim($name);
    $value = trim($value);
    // quitar comillas si existen
    if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') || (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
        $value = substr($value, 1, -1);
    }
    // No sobreescribir variables de entorno ya configuradas
    if (getenv($name) === false && empty($_ENV[$name])) {
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

?>
