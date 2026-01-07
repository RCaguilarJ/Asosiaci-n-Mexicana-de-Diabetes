<?php


require 'includes/db.php';

// --- CONFIGURACIÓN DEL NUEVO USUARIO ---
$nombre = "Administrador";
$email = "admin@diabetes.com";
$passwordPlana = "admin123"; // Esta será la contraseña que escribirás en el login

// 1. Encriptamos la contraseña usando el algoritmo por defecto (BCRYPT)
$passwordHash = password_hash($passwordPlana, PASSWORD_DEFAULT);

try {
    // 2. Verificamos si el email ya existe para no duplicarlo
    $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmtCheck->execute([$email]);
    
    if ($stmtCheck->rowCount() > 0) {
        // CASO A: El usuario ya existe -> Actualizamos su contraseña
        $stmt = $pdo->prepare("UPDATE usuarios SET password = ?, nombre = ? WHERE email = ?");
        $stmt->execute([$passwordHash, $nombre, $email]);
        echo "<div style='font-family:sans-serif; color: green; padding: 20px; border: 2px solid green; border-radius: 10px; max-width: 600px; margin: 20px auto;'>";
        echo "<h1> Usuario Actualizado</h1>";
        echo "<p>Se ha restablecido la contraseña para el usuario existente.</p>";
    } else {
        // CASO B: El usuario no existe -> Lo creamos desde cero
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $email, $passwordHash]);
        echo "<div style='font-family:sans-serif; color: blue; padding: 20px; border: 2px solid blue; border-radius: 10px; max-width: 600px; margin: 20px auto;'>";
        echo "<h1> Usuario Creado</h1>";
        echo "<p>Se ha creado un nuevo usuario administrador.</p>";
    }

    // Mostrar las credenciales para probar
    echo "<hr>";
    echo "<p><strong>Email:</strong> $email</p>";
    echo "<p><strong>Contraseña:</strong> $passwordPlana</p>";
    echo "<p><em>(Hash generado en BD: " . substr($passwordHash, 0, 20) . "...)</em></p>";
    echo "<br>";
    echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Ir a Iniciar Sesión >></a>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='font-family:sans-serif; color: red; padding: 20px; border: 2px solid red;'>";
    echo "<h1> Error</h1>";
    echo "<p>No se pudo conectar a la base de datos o ejecutar la consulta.</p>";
    echo "<p><strong>Detalle:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>