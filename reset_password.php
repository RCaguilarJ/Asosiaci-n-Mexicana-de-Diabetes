<?php
// reset_password.php
require 'includes/db.php';

// Datos de tu cuenta
$email = "carlagular800@gmail.com";
$nuevaPassword = "hola123"; // <--- Esta será tu nueva contraseña temporal

// Encriptar
$hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);

try {
    // Actualizar en la base de datos
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
    $stmt->execute([$hash, $email]);

    if ($stmt->rowCount() > 0) {
        echo "<h1 style='color:green'>¡Contraseña Actualizada!</h1>";
        echo "<p>Ahora puedes entrar con:</p>";
        echo "<ul><li>Email: <strong>$email</strong></li><li>Pass: <strong>$nuevaPassword</strong></li></ul>";
        echo "<a href='login.php'>Ir al Login</a>";
    } else {
        echo "<h1 style='color:orange'>No se encontró el usuario</h1>";
        echo "<p>El correo $email no existe en la tabla 'usuarios'.</p>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>