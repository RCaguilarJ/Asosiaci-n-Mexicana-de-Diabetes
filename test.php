<?php
require_once 'includes/db.php';
if ($pdo) {
    echo "✅ Conexión exitosa a la BD";
} else {
    echo "❌ Error de conexión";
}
?>