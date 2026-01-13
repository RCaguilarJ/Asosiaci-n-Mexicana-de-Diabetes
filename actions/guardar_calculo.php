<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Limpiar cualquier output previo
ob_clean();

// Incluir conexión a la base de datos
require '../includes/db.php';

// Establecer header para respuesta JSON
header('Content-Type: application/json');

// Suprimir errores de display para evitar contaminar JSON
ini_set('display_errors', 0);
error_reporting(0);

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

try {
    // Obtener datos del formulario
    $usuario_id = $_SESSION['usuario_id'];
    $glucosa = floatval($_POST['glucosa'] ?? 0);
    $momento = trim($_POST['momento'] ?? '');
    $carbohidratos = floatval($_POST['carbohidratos'] ?? 0);
    $ratio = intval($_POST['ratio'] ?? 0);
    $dosis_correccion = floatval($_POST['dosis_correccion'] ?? 0);
    $dosis_carbohidratos = floatval($_POST['dosis_carbohidratos'] ?? 0);
    $dosis_total = floatval($_POST['dosis_total'] ?? 0);
    
    // Validaciones básicas
    if ($glucosa <= 0 || empty($momento) || $carbohidratos < 0 || $ratio <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos para guardar el cálculo']);
        exit;
    }

    // Crear tabla si no existe (verificar primero)
    try {
        $checkTable = $pdo->query("SHOW TABLES LIKE 'historial_calculos'");
        if ($checkTable->rowCount() == 0) {
            $createTable = "
                CREATE TABLE historial_calculos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    usuario_id INT NOT NULL,
                    glucosa DECIMAL(5,2) NOT NULL,
                    momento VARCHAR(20) NOT NULL,
                    carbohidratos DECIMAL(5,2) NOT NULL,
                    ratio_insulina INT NOT NULL,
                    dosis_correccion DECIMAL(4,2) DEFAULT 0,
                    dosis_carbohidratos DECIMAL(4,2) NOT NULL,
                    dosis_total DECIMAL(4,2) NOT NULL,
                    fecha_calculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_usuario_fecha (usuario_id, fecha_calculo)
                )
            ";
            $pdo->exec($createTable);
        }
    } catch (Exception $e) {
        // Si hay error creando la tabla, continuar (tal vez ya existe)
        error_log("Error creando tabla: " . $e->getMessage());
    }

    // Insertar el registro en la base de datos
    $stmt = $pdo->prepare("
        INSERT INTO historial_calculos (
            usuario_id, 
            glucosa, 
            momento, 
            carbohidratos, 
            ratio_insulina, 
            dosis_correccion, 
            dosis_carbohidratos, 
            dosis_total
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $resultado = $stmt->execute([
        $usuario_id,
        $glucosa,
        $momento,
        $carbohidratos,
        $ratio,
        $dosis_correccion,
        $dosis_carbohidratos,
        $dosis_total
    ]);
    
    if ($resultado) {
        $calculoId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'mensaje' => 'Cálculo guardado exitosamente en el historial',
            'calculoId' => $calculoId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar el cálculo en la base de datos']);
    }
    
} catch (Exception $e) {
    error_log("Error en guardar_calculo.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor'
    ]);
}

// Asegurar que no hay output adicional
exit;
?>
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar en BD']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos no recibidos']);
}
?>