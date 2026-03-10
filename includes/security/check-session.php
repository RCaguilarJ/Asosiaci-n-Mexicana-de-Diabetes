<?php
/**
 * check-session.php
 * Verifica sesion y permite acceso como invitado sin forzar login
 */
require_once __DIR__ . '/../base_path.php';

// Hardened session start: secure cookie params, timeout e invalidacion
ini_set('session.use_strict_mode', 1);
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$now = time();

// Timeout de inactividad (30 minutos)
$timeout = 30 * 60;
if (isset($_SESSION['LAST_ACTIVITY']) && ($now - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    // Limpiar sesion por inactividad, pero permitir modo invitado
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}
$_SESSION['LAST_ACTIVITY'] = $now;

// Regenerar id de sesion periodicamente para evitar fixation
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = $now;
} elseif ($now - $_SESSION['created'] > 300) {
    session_regenerate_id(true);
    $_SESSION['created'] = $now;
}

// Si no esta logueado, permitir acceso como invitado y crear usuario invitado
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['es_invitado'] = true;
    if (empty($_SESSION['usuario_nombre'])) {
        $_SESSION['usuario_nombre'] = 'Invitado';
    }

    // Reutilizar invitado existente en la sesion si ya fue creado
    if (isset($_SESSION['invitado_id']) && !empty($_SESSION['invitado_id'])) {
        $_SESSION['usuario_id'] = $_SESSION['invitado_id'];
    } else {
        // Intentar crear un usuario invitado en BD para guardar datos igual que un usuario logueado
        try {
            if (!isset($pdo) || !($pdo instanceof PDO)) {
                require_once __DIR__ . '/../db.php';
            }
        } catch (Throwable $e) {
            error_log('check-session: error cargando DB para invitado: ' . $e->getMessage());
        }

        if (isset($pdo) && ($pdo instanceof PDO)) {
            $tries = 0;
            $created = false;

            while ($tries < 3 && !$created) {
                $tries++;
                try {
                    $token = bin2hex(random_bytes(8));
                } catch (Throwable $e) {
                    $token = 'inv' . str_replace('.', '', uniqid('', true));
                }
                $email = 'invitado_' . $token . '@amd.local';
                $nombreDb = 'Invitado ' . $token;
                try {
                    $rawPass = bin2hex(random_bytes(12));
                } catch (Throwable $e) {
                    $rawPass = 'pass' . str_replace('.', '', uniqid('', true));
                }
                $passwordHash = password_hash($rawPass, PASSWORD_DEFAULT);

                try {
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'PACIENTE')");
                    $stmt->execute([$nombreDb, $email, $passwordHash]);
                    $guestId = $pdo->lastInsertId();

                    $_SESSION['usuario_id'] = $guestId;
                    $_SESSION['invitado_id'] = $guestId;
                    $_SESSION['usuario_email'] = $email;
                    $_SESSION['usuario_nombre'] = 'Invitado';
                    $_SESSION['usuario_rol'] = 'PACIENTE';
                    $created = true;
                } catch (PDOException $e) {
                    // Reintentar si hay colision de email
                    if ($e->getCode() !== '23000') {
                        error_log('check-session: error creando usuario invitado: ' . $e->getMessage());
                        break;
                    }
                }
            }
        }
    }
}
?>
