<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/includes/base_path.php';

$hasAccess = isset($_SESSION['usuario_id']) || !empty($_SESSION['es_invitado']);

if ($hasAccess) {
    header('Location: ' . $basePath . '/views/index.php');
} else {
    header('Location: ' . $basePath . '/views/login.php');
}
exit;
?>
