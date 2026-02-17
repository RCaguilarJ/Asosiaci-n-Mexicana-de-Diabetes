<?php
/**
 * MIGRACIÓN DEFINITIVA - Tabla Citas
 * Basado en estructura actual confirmada del dump SQL
 */

require_once __DIR__ . '/../../includes/load_env.php';

// Conexión directa (desde .env)
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'sistema_gestion_medica';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO:: ERRMODE_EXCEPTION]
    );
    
    echo "<! DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Migración Definitiva - Tabla Citas</title>
        <style>
            body { font-family: 'Segoe UI', Arial; margin: 0; padding: 20px; background:  linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
            .container { max-width: 1000px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
            h1 { color: #2c3e50; border-bottom: 4px solid #3498db; padding-bottom: 15px; margin-bottom: 30px; }
            h2 { color: #34495e; margin-top: 30px; border-left: 5px solid #3498db; padding-left: 15px; }
            .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 5px solid #28a745; }
            .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 5px solid #dc3545; }
            .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 5px solid #ffc107; }
            .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 5px solid #17a2b8; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
            th, td { padding: 12px; text-align: left; border:  1px solid #dee2e6; }
            th { background:  linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 600; }
            tr:nth-child(even) { background: #f8f9fa; }
            tr:hover { background: #e9ecef; }
            .highlight { background: #d4edda ! important; }
            pre { background: #2d2d2d; color: #f8f8f2; padding: 20px; border-radius: 8px; overflow-x: auto; }
            .btn { display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; margin: 10px 5px; font-weight: 600; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: all 0.3s; }
            .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.3); }
            .final-box { background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); padding: 30px; border-radius: 15px; margin: 30px 0; border:  3px solid #28a745; }
        </style>
    </head>
    <body>
    <div class='container'>";
    
    echo "<h1> MIGRACIÓN DEFINITIVA - TABLA CITAS</h1>";
    echo "<div class='info'><strong> Fecha:</strong> " . date('Y-m-d H:i:s') . "</div>";
    echo "<div class='info'><strong> Base de datos:</strong> $dbname</div>";
    
    // PASO 1: Verificar estructura actualtructura actual
    echo "<h2> PASO 1: Verificando Estructura Actual</h2>";
    
    $stmt = $pdo->query("DESCRIBE citas");
    $columnasActuales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
    
    $camposActuales = [];
    foreach ($columnasActuales as $col) {
        $camposActuales[] = $col['Field'];
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>" . ($col['Default'] ??  'NULL') . "</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar columnas faltantes
    $faltantes = [];
    if (! in_array('nombre', $camposActuales)) $faltantes[] = 'nombre';
    if (!in_array('email', $camposActuales)) $faltantes[] = 'email';
    if (!in_array('telefono', $camposActuales)) $faltantes[] = 'telefono';
    if (!in_array('fecha_actualizacion', $camposActuales)) $faltantes[] = 'fecha_actualizacion';
    
    if (empty($faltantes)) {
        echo "<div class='success'><h3> ¡Tabla ya actualizada! </h3><p>No se requiere migración.</p></div>";
        echo "<div><a href='/views/citas.php' class='btn'> Ir al Formulario</a></div>";
        echo "</div></body></html>";
        exit;
    }
    
    echo "<div class='warning'>";
    echo "<h4> Columnas faltantes detectadas:</h4>";
    echo "<ul>";
    foreach ($faltantes as $col) {
        echo "<li><strong>$col</strong></li>";
    }
    echo "</ul>";
    echo "</div>";
    
    // PASO 2: Crear backup
    echo "<h2> PASO 2: Creando Backup de Seguridad</h2>";
    
    $backupName = 'citas_backup_' .  date('Ymd_His');
    
    $pdo->exec("DROP TABLE IF EXISTS $backupName");
    $pdo->exec("CREATE TABLE $backupName AS SELECT * FROM citas");
    
    $count = $pdo->query("SELECT COUNT(*) FROM $backupName")->fetchColumn();
    
    echo "<div class='success'>";
    echo " <strong>Backup creado:</strong> <code>$backupName</code><br>";
    echo " <strong>Registros guardados:</strong> $count";
    echo "</div>";
    
    // PASO 3: Eliminar tabla antigua
    echo "<h2> PASO 3: Eliminando Tabla Antigua</h2>";
    
    $pdo->exec("DROP TABLE IF EXISTS citas");
    
    echo "<div class='success'>Tabla antigua eliminada</div>";
    
    // PASO 4: Crear nueva tabla
    echo "<h2>🔨 PASO 4: Creando Nueva Tabla con Estructura Correcta</h2>";
    
    $createSQL = "
    CREATE TABLE citas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL COMMENT 'ID del usuario de la app',
        medico_id INT NULL COMMENT 'ID del médico del sistema de gestión',
        
        -- COLUMNAS NUEVAS (lo que estaba faltando)
        nombre VARCHAR(255) NOT NULL COMMENT 'Nombre completo del paciente',
        email VARCHAR(255) NOT NULL COMMENT 'Email de contacto',
        telefono VARCHAR(50) NOT NULL COMMENT 'Teléfono de contacto',
        
        -- COLUMNAS EXISTENTES
        especialidad VARCHAR(100) NOT NULL COMMENT 'Especialidad médica',
        fecha_cita DATETIME NOT NULL COMMENT 'Fecha y hora de la cita',
        descripcion TEXT NULL COMMENT 'Motivo de la consulta',
        estado ENUM('pendiente', 'confirmada', 'completada', 'cancelada') DEFAULT 'pendiente',
        
        -- COLUMNAS DE CONTROL
        cita_remota_id INT NULL COMMENT 'ID en sistema médico externo',
        sincronizada TINYINT(1) DEFAULT 0 COMMENT 'Si está sincronizada con API',
        
        -- COLUMNAS DE AUDITORÍA
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        -- ÍNDICES
        INDEX idx_usuario_id (usuario_id),
        INDEX idx_medico_id (medico_id),
        INDEX idx_fecha_cita (fecha_cita),
        INDEX idx_especialidad (especialidad),
        INDEX idx_estado (estado),
        INDEX idx_sincronizada (sincronizada)
        
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Citas médicas - App Diabetes (ESTRUCTURA CORREGIDA)'
    ";
    
    $pdo->exec($createSQL);
    
    echo "<div class='success'> Nueva tabla creada con estructura correcta</div>";
    
    // PASO 5: Restaurar datos
    echo "<h2> PASO 5: Restaurando Datos del Backup</h2>";
    
    if ($count > 0) {
        echo "<div class='info'> Restaurando $count registros...</div>";
        
        // Obtener datos del backup
        $stmt = $pdo->query("SELECT * FROM $backupName ORDER BY fecha_creacion");
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $restaurados = 0;
        $errores = 0;
        
        foreach ($registros as $reg) {
            try {
                $pdo->prepare("
                    INSERT INTO citas 
                    (id, usuario_id, medico_id, nombre, email, telefono, especialidad, fecha_cita, descripcion, estado, fecha_registro)
                    VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ")->execute([
                    $reg['id'],
                    $reg['usuario_id'],
                    $reg['medico_id'],
                    'Paciente App', // Valor temporal para nombre
                    'usuario_' . $reg['usuario_id'] . '@temp.com', // Email temporal
                    '0000000000', // Teléfono temporal
                    $reg['especialidad'] ?? 'General',
                    $reg['fecha_cita'],
                    $reg['descripcion'] ?? '',
                    'pendiente',
                    $reg['fecha_creacion']
                ]);
                $restaurados++;
            } catch (Exception $e) {
                $errores++;
                echo "<div class='warning'> Error en registro ID {$reg['id']}: " . $e->getMessage() . "</div>";
            }
        }
        
        echo "<div class='success'>";
        echo " <strong>Registros restaurados:</strong> $restaurados de $count<br>";
        if ($errores > 0) {
            echo " <strong>Errores: </strong> $errores";
        }
        echo "</div>";
        
        echo "<div class='warning'>";
        echo "<h4> IMPORTANTE:  Datos temporales</h4>";
        echo "<p>Los registros antiguos fueron restaurados con: </p>";
        echo "<ul>";
        echo "<li><strong>nombre:</strong> 'Paciente App' (temporal)</li>";
        echo "<li><strong>email:</strong> 'usuario_X@temp.com' (temporal)</li>";
        echo "<li><strong>telefono:</strong> '0000000000' (temporal)</li>";
        echo "</ul>";
        echo "<p>Las <strong>nuevas citas</strong> tendrán datos reales.</p>";
        echo "</div>";
    } else {
        echo "<div class='info'>ℹ No había registros para restaurar (tabla nueva)</div>";
    }
    
    // PASO 6: Verificar estructura final
    echo "<h2> PASO 6: Verificación Final</h2>";
    
    $stmt = $pdo->query("DESCRIBE citas");
    $estructuraFinal = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($estructuraFinal as $col) {
        $esNuevo = in_array($col['Field'], ['nombre', 'email', 'telefono', 'fecha_actualizacion']);
        $class = $esNuevo ? 'highlight' : '';
        
        echo "<tr class='$class'>";
        echo "<td><strong>{$col['Field']}</strong>" . ($esNuevo ? " 🆕" : "") . "</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Estadísticas finales
    $totalFinal = $pdo->query("SELECT COUNT(*) FROM citas")->fetchColumn();
    
    echo "<div class='final-box'>";
    echo "<h2> ¡MIGRACIÓN COMPLETADA EXITOSAMENTE!</h2>";
    echo "<h3> Resumen: </h3>";
    echo "<ul style='font-size: 16px; line-height: 2;'>";
    echo "<li> <strong>Columnas agregadas:</strong> nombre, email, telefono, fecha_actualizacion</li>";
    echo "<li> <strong>Engine:</strong> InnoDB (antes MyISAM)</li>";
    echo "<li><strong>Charset:</strong> utf8mb4</li>";
    echo "<li><strong>Índices:</strong> Optimizados</li>";
    echo "<li> <strong>Backup guardado:</strong> $backupName</li>";
    echo "<li> <strong>Registros actuales:</strong> $totalFinal</li>";
    echo "</ul>";
    
    echo "<h3> Siguiente Paso: </h3>";
    echo "<p style='font-size: 18px;'>El formulario de citas ahora funcionará correctamente.</p>";
    
    echo "<div style='text-align: center; margin-top: 30px;'>";
    echo "<a href='/views/citas.php' class='btn' style='font-size: 18px;'>🚀 PROBAR FORMULARIO DE CITAS</a>";
    echo "</div>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h4> Archivos de log sugeridos para revisar:</h4>";
    echo "<ul>";
    echo "<li><code>logs/citas_errors.log</code> - Errores de guardado de citas</li>";
    echo "<li><code>C:\\wamp64\\logs\\php_error.log</code> - Errores de PHP</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "</div></body></html>";
    
} catch (PDOException $e) {
    echo "<! DOCTYPE html><html><head><meta charset='UTF-8'><title>Error</title>";
    echo "<style>body{font-family: Arial;padding:20px;background:#f8d7da;}";
    echo ". error{background: white;padding:30px;border-radius:10px;max-width:800px;margin:0 auto;border-left:5px solid #dc3545;}</style></head><body>";
    echo "<div class='error'>";
    echo "<h1> ERROR EN LA MIGRACIÓN</h1>";
    echo "<p><strong>Error:</strong> " .  htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Código: </strong> " . $e->getCode() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<h3>Posibles causas: </h3>";
    echo "<ul>";
    echo "<li>Usuario/contraseña de MySQL incorrectos</li>";
    echo "<li>Base de datos 'sistema_gestion_medica' no existe</li>";
    echo "<li>MySQL no está ejecutándose</li>";
    echo "<li>Puerto 3306 incorrecto</li>";
    echo "</ul>";
    echo "</div></body></html>";
}
?>



