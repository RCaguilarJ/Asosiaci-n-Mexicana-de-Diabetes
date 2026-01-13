<?php
/**
 * Script para verificar y crear tablas necesarias
 */

require_once 'includes/db.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔧 Verificación y Creación de Tablas</h2>";

try {
    // Verificar si existe la base de datos
    $stmt = $pdo->query("SELECT DATABASE()");
    $currentDb = $stmt->fetchColumn();
    echo "<p>✅ Conectado a base de datos: <strong>$currentDb</strong></p>";
    
    // Verificar tablas existentes
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>📊 Tablas existentes:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Verificar si existe tabla usuarios
    if (!in_array('usuarios', $tables)) {
        echo "<p>⚠️ Tabla 'usuarios' no existe. Creando...</p>";
        
        $sqlUsuarios = "
        CREATE TABLE usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            rol ENUM('NUTRI', 'ENDOCRINO', 'PODOLOGO', 'PSICOLOGO', 'DOCTOR', 'ADMIN', 'PACIENTE') DEFAULT 'PACIENTE',
            activo BOOLEAN DEFAULT TRUE,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $pdo->exec($sqlUsuarios);
        echo "<p>✅ Tabla 'usuarios' creada exitosamente</p>";
        
        // Insertar usuarios de ejemplo
        $usuariosEjemplo = [
            ['Dr. Juan Nutritólogo', 'nutricion@diabetes.local', 'NUTRI'],
            ['Dra. María Endocrinóloga', 'endocrino@diabetes.local', 'ENDOCRINO'],
            ['Dr. Carlos Podólogo', 'podologo@diabetes.local', 'PODOLOGO'],
            ['Dra. Ana Psicóloga', 'psicologo@diabetes.local', 'PSICOLOGO'],
            ['Dr. Luis Medicina General', 'doctor@diabetes.local', 'DOCTOR']
        ];
        
        $stmtInsert = $pdo->prepare("
            INSERT INTO usuarios (nombre, email, password, rol) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($usuariosEjemplo as $usuario) {
            $password = password_hash('123456', PASSWORD_DEFAULT);
            $stmtInsert->execute([$usuario[0], $usuario[1], $password, $usuario[2]]);
        }
        
        echo "<p>✅ Usuarios de ejemplo creados</p>";
    }
    
    // Verificar si existe tabla citas
    if (!in_array('citas', $tables)) {
        echo "<p>⚠️ Tabla 'citas' no existe. Creando...</p>";
        
        $sqlCitas = "
        CREATE TABLE citas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            medico_id INT NULL,
            nombre VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            telefono VARCHAR(20),
            especialidad VARCHAR(100) NOT NULL,
            fecha_cita DATETIME NOT NULL,
            descripcion TEXT,
            estado ENUM('pendiente', 'confirmada', 'completada', 'cancelada') DEFAULT 'pendiente',
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $pdo->exec($sqlCitas);
        echo "<p>✅ Tabla 'citas' creada exitosamente</p>";
    } else {
        // Verificar si tiene columna medico_id
        $stmt = $pdo->query("DESCRIBE citas");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $hasMedicoId = false;
        foreach ($columns as $column) {
            if ($column['Field'] === 'medico_id') {
                $hasMedicoId = true;
                break;
            }
        }
        
        if (!$hasMedicoId) {
            echo "<p>⚠️ Agregando columna 'medico_id' a tabla citas...</p>";
            $pdo->exec("ALTER TABLE citas ADD COLUMN medico_id INT NULL AFTER usuario_id");
            echo "<p>✅ Columna 'medico_id' agregada</p>";
        }
    }
    
    // Mostrar estructura final
    echo "<h3>🏗️ Estructura final de tablas:</h3>";
    
    foreach (['usuarios', 'citas'] as $tableName) {
        if (in_array($tableName, $tables) || $tableName === 'citas') {
            echo "<h4>Tabla: $tableName</h4>";
            $stmt = $pdo->query("DESCRIBE $tableName");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>{$col['Field']}</td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>{$col['Null']}</td>";
                echo "<td>{$col['Key']}</td>";
                echo "<td>{$col['Default']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    echo "<h3>🎯 Sistema listo para usar</h3>";
    echo "<p>✅ Base de datos configurada correctamente</p>";
    echo "<p>✅ Tablas necesarias creadas</p>";
    echo "<p>✅ Usuarios de ejemplo disponibles</p>";
    
    echo "<h3>🔗 Próximos pasos:</h3>";
    echo "<ul>";
    echo "<li><a href='api/get_especialistas.php'>Probar endpoint de especialistas</a></li>";
    echo "<li><a href='views/citas.php'>Ir al formulario de citas</a></li>";
    echo "<li><a href='test_selector_especialistas.php'>Test del selector</a></li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error de base de datos: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>