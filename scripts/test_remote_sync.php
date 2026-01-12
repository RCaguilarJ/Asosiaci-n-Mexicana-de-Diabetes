<?php
// scripts/test_remote_sync.php
// Usage: http://localhost/asosiacionMexicanaDeDiabetes/scripts/test_remote_sync.php?email=correo@ejemplo.com
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: text/plain; charset=utf-8');

$email = $_GET['email'] ?? null;
if (!$email) {
    echo "Usage: ?email=you@example.com\n";
    exit;
}

$pdoRemote = getRemoteConnection();
if (!$pdoRemote) {
    echo "getRemoteConnection() returned null - no hay conexión remota.\n";
    exit;
}

echo "Conexión remota OK.\n";

try {
    $stmt = $pdoRemote->prepare('SELECT id, email FROM pacientes WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "Paciente encontrado en remoto: id={$row['id']} email={$row['email']}\n";
    } else {
        echo "Paciente NO encontrado en remoto por email {$email}\n";
    }
} catch (Exception $e) {
    echo "Error en consulta remota: " . $e->getMessage() . "\n";
}

?>