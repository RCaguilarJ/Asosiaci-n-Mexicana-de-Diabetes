<?php
// includes/db.php modificado para conexión remota segura

// IP pública  base de datos central
$host = '162.240.37.227'; 
$dbname = 'diabetes_db'; 
$username = 'usuario_remoto'; 
$password = 'tuc0ntras3ñaSegura!'; 

// Configuración SSL (Necesaria para conexiones remotas seguras)
$ssl_ca = '/ruta/segura/al/certificado/ca-cert.pem'; 

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Habilitar encriptación SSL
        PDO::MYSQL_ATTR_SSL_CA => $ssl_ca, 
        // Desactiva verificar nombre si usas IP directa (según tu certificado)
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, 
    ];

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, $options);

} catch (PDOException $e) {
    // En producción, no muestres el error detallado al usuario
    error_log("Error de conexión: " . $e->getMessage());
    die("Error de conexión al sistema.");
}
?>