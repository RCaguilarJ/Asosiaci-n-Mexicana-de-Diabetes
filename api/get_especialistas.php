<?php
/**
 * API endpoint para obtener especialistas del Sistema de Gestión Médica
 * Devuelve especialistas agrupados por rol para el formulario de citas
 */

// Configurar headers antes que cualquier output
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Capturar errores para enviar como JSON
function handleError($error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $error,
        'debug' => [
            'file' => __FILE__,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
    exit;
}

// Configurar manejo de errores
set_error_handler(function($severity, $message, $file, $line) {
    handleError("PHP Error: $message in $file at line $line");
});

// Solo permitir GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    // Cargar dependencias necesarias
    require_once '../includes/db.php';
    require_once '../includes/load_env.php';
    
    // Verificar si existe el helper de API
    if (file_exists('../includes/api_sistema_gestion.php')) {
        require_once '../includes/api_sistema_gestion.php';
        $useApi = true;
    } else {
        $useApi = false;
    }
    
    // Obtener filtro por rol si se especifica
    $role = $_GET['role'] ?? null;
    
    $especialistas = false;
    $fallbackLocal = false;
    
    // Intentar usar API externa si está disponible
    if ($useApi) {
        try {
            $api = new ApiSistemaGestionHelper();
            $especialistas = $api->obtenerEspecialistas($role);
        } catch (Exception $e) {
            // API falló, usar fallback local
            $fallbackLocal = true;
        }
    } else {
        $fallbackLocal = true;
    }
    
    // Si la API falló o no está disponible, usar datos locales
    if ($especialistas === false || $fallbackLocal) {
        $fallbackLocal = true;
        
        $whereClause = '';
        $params = [];
        
        if ($role) {
            $whereClause = 'WHERE rol = ?';
            $params[] = $role;
        }
        
        $stmt = $pdo->prepare("
            SELECT id, nombre, email, rol as role 
            FROM usuarios 
            $whereClause
            ORDER BY nombre
        ");
        $stmt->execute($params);
        $usuariosLocales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir a formato esperado
        $especialistas = [];
        foreach ($usuariosLocales as $usuario) {
            $especialistas[] = [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'username' => $usuario['email'], // Usar email como username
                'disponible' => true
            ];
        }
    }
    
    // Agrupar especialistas por rol
    if ($role) {
        // Si se pidió un rol específico, devolver solo ese
        $rolesAgrupados = [];
        
        switch ($role) {
            case 'NUTRI':
                $nombreRol = 'Nutrición';
                break;
            case 'ENDOCRINO':
                $nombreRol = 'Endocrinología';
                break;
            case 'PODOLOGO':
                $nombreRol = 'Podología';
                break;
            case 'PSICOLOGO':
                $nombreRol = 'Psicología';
                break;
            case 'DOCTOR':
                $nombreRol = 'Medicina General';
                break;
            default:
                $nombreRol = ucfirst(strtolower($role));
        }
        
        $rolesAgrupados[] = [
            'role' => $role,
            'nombre_rol' => $nombreRol,
            'especialistas' => is_array($especialistas) ? $especialistas : []
        ];
        
    } else {
        // Devolver todos los roles disponibles
        $todosLosRoles = ['NUTRI', 'ENDOCRINO', 'PODOLOGO', 'PSICOLOGO', 'DOCTOR'];
        $nombresRoles = [
            'NUTRI' => 'Nutrición',
            'ENDOCRINO' => 'Endocrinología',
            'PODOLOGO' => 'Podología',
            'PSICOLOGO' => 'Psicología',
            'DOCTOR' => 'Medicina General'
        ];
        
        $rolesAgrupados = [];
        
        foreach ($todosLosRoles as $rolItem) {
            // Si no hay API externa, crear datos de ejemplo
            if ($fallbackLocal) {
                $especialistasRol = [
                    [
                        'id' => rand(100, 999),
                        'nombre' => 'Dr. ' . $nombresRoles[$rolItem] . ' Local',
                        'email' => strtolower($rolItem) . '@diabetes.local',
                        'username' => strtolower($rolItem) . '_local',
                        'disponible' => true
                    ]
                ];
            } else {
                // Filtrar especialistas por rol desde API
                $especialistasRol = is_array($especialistas) ? array_filter($especialistas, function($esp) use ($rolItem) {
                    return isset($esp['role']) && $esp['role'] === $rolItem;
                }) : [];
            }
            
            $rolesAgrupados[] = [
                'role' => $rolItem,
                'nombre_rol' => $nombresRoles[$rolItem],
                'especialistas' => array_values($especialistasRol)
            ];
        }
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'roles' => $rolesAgrupados,
        'fallback_local' => $fallbackLocal,
        'total_roles' => count($rolesAgrupados),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    handleError('Error general: ' . $e->getMessage());
} catch (Error $e) {
    handleError('Error fatal: ' . $e->getMessage());
}
?>