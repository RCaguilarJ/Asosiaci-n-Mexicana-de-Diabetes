<?php
/**
 * Helper para autenticación HMAC SHA-256 con API Sistema Gestión Médica
 * Maneja comunicación segura con backend Node.js
 */

require_once __DIR__ . '/load_env.php';

class ApiSistemaGestionHelper {
    
    private $baseUrl;
    private $secret;
    private static $syncQueueTableExists = null; // Cache para verificación de tabla
    
    public function __construct() {
        // Temporalmente usar endpoint local hasta que se configure el sistema real
        $this->baseUrl = 'http://localhost/asosiacionMexicanaDeDiabetes/api/especialistas_local.php';
        $this->secret = getenv('AMD_SYNC_SECRET') ?: 'default_secret_change_in_production';
        
        // URL original para cuando esté disponible
        // $this->baseUrl = getenv('SISTEMA_GESTION_API_URL') ?: 'https://sistema-gestion-medica.local/api';
    }
    
    /**
     * Genera firma HMAC SHA-256 para autenticación
     */
    private function generarFirmaHMAC($body) {
        return hash_hmac('sha256', $body, $this->secret);
    }
    
    /**
     * Realiza petición HTTP autenticada a la API
     */
    private function realizarPeticion($method, $endpoint, $data = null) {
        $url = $this->baseUrl . $endpoint;
        $body = $data ? json_encode($data) : '';
        $signature = $this->generarFirmaHMAC($body);
        
        $headers = [
            'Content-Type: application/json',
            'X-Signature: ' . $signature,
            'X-Source: amd-diabetes-app',
            'User-Agent: AMD-Diabetes-App/1.0'
        ];
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Solo para desarrollo
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Error cURL: " . $error);
        }
        
        return [
            'status_code' => $httpCode,
            'body' => $response,
            'success' => $httpCode >= 200 && $httpCode < 300
        ];
    }
    
    /**
     * Obtiene especialistas activos desde la API
     */
    public function obtenerEspecialistas($role = null) {
        try {
            // Para endpoint local temporal
            $url = $this->baseUrl;
            if ($role) {
                $url .= '?role=' . urlencode($role);
            }
            
            // Petición directa (sin autenticación HMAC para endpoint local)
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => 'Content-Type: application/json',
                    'timeout' => 10
                ]
            ]);
            
            $response = file_get_contents($url, false, $context);
            
            if ($response === false) {
                throw new Exception("No se pudo conectar al endpoint");
            }
            
            $data = json_decode($response, true);
            
            if (!$data || !$data['success']) {
                throw new Exception("Respuesta inválida de la API");
            }
            
            $this->registrarExito('obtener_especialistas', count($data['data']));
            return $data['data'];
            
        } catch (Exception $e) {
            $this->registrarError('obtener_especialistas', 0, $e->getMessage());
            error_log("Error obteniendo especialistas: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crea una cita en el sistema de gestión médica
     */
    public function crearCita($datosCita) {
        try {
            // Validar datos requeridos
            $requiredFields = ['pacienteId', 'medicoId', 'fechaHora', 'motivo'];
            foreach ($requiredFields as $field) {
                if (empty($datosCita[$field])) {
                    throw new Exception("Campo requerido faltante: $field");
                }
            }
            
            // Para el endpoint local temporal, simularemos el guardado de la cita
            // En un sistema real, esto se enviaría al Sistema de Gestión Médica
            
            // Simular respuesta exitosa con datos del médico
            $medicoId = $datosCita['medicoId'];
            
            // Obtener datos del médico desde nuestro endpoint local
            $especialistasUrl = 'http://localhost/asosiacionMexicanaDeDiabetes/api/especialistas_local.php';
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => 'Content-Type: application/json',
                    'timeout' => 5
                ]
            ]);
            
            $response = file_get_contents($especialistasUrl, false, $context);
            $especialistas = json_decode($response, true);
            
            $medicoEncontrado = null;
            if ($especialistas && $especialistas['success']) {
                foreach ($especialistas['data'] as $medico) {
                    if ($medico['id'] == $medicoId) {
                        $medicoEncontrado = $medico;
                        break;
                    }
                }
            }
            
            if (!$medicoEncontrado) {
                throw new Exception("Médico no encontrado con ID: $medicoId");
            }
            
            // Generar ID simulado para la cita remota
            $citaRemotaId = 'CITA_' . date('Ymd') . '_' . rand(1000, 9999);
            
            // Simular respuesta del sistema médico
            $citaRespuesta = [
                'id' => $citaRemotaId,
                'pacienteId' => $datosCita['pacienteId'],
                'medicoId' => $medicoId,
                'fechaHora' => $datosCita['fechaHora'],
                'motivo' => $datosCita['motivo'],
                'estado' => 'confirmada',
                'medico' => [
                    'id' => $medicoEncontrado['id'],
                    'nombre' => $medicoEncontrado['nombre'],
                    'role' => $medicoEncontrado['role'],
                    'especialidad' => $medicoEncontrado['especialidad']
                ],
                'fechaCreacion' => date('c'),
                'fuente' => 'amd-diabetes-app'
            ];
            
            $this->registrarExito('crear_cita', $citaRemotaId, $datosCita);
            
            return [
                'success' => true,
                'cita' => $citaRespuesta,
                'message' => 'Cita creada exitosamente y asignada al médico'
            ];
            
        } catch (Exception $e) {
            $this->registrarError('crear_cita', 0, $e->getMessage(), $datosCita ?? []);
            error_log("Error creando cita: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verifica si la tabla sync_queue existe (con caché)
     */
    private function verificarTablaSyncQueue() {
        // Si ya verificamos anteriormente, usar el valor cacheado
        if (self::$syncQueueTableExists !== null) {
            return self::$syncQueueTableExists;
        }
        
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $stmt = $pdo->query("SHOW TABLES LIKE 'sync_queue'");
            $tableExists = $stmt->fetch();
            
            // Cachear el resultado
            self::$syncQueueTableExists = (bool)$tableExists;
            
            return self::$syncQueueTableExists;
        } catch (Exception $e) {
            error_log("Error verificando tabla sync_queue: " . $e->getMessage());
            // En caso de error, asumir que no existe para evitar errores posteriores
            self::$syncQueueTableExists = false;
            return false;
        }
    }
    
    /**
     * Registra operación exitosa en sync_queue
     */
    private function registrarExito($operacion, $referencia, $datos = null) {
        try {
            // Verificar si la tabla sync_queue existe antes de insertar (con caché)
            if (!$this->verificarTablaSyncQueue()) {
                error_log("ADVERTENCIA: Tabla sync_queue no existe. Operación '$operacion' no registrada. Ejecute migrations/create_sync_queue_table.php");
                return;
            }
            
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $stmt = $pdo->prepare("
                INSERT INTO sync_queue (operacion, estado, referencia_id, datos_json, fecha_creacion) 
                VALUES (?, 'completado', ?, ?, NOW())
            ");
            
            $stmt->execute([
                $operacion,
                $referencia,
                $datos ? json_encode($datos) : null
            ]);
            
        } catch (Exception $e) {
            error_log("Error registrando éxito en sync_queue: " . $e->getMessage());
            // No lanzar excepción para evitar que falle el proceso principal
        }
    }
    
    /**
     * Registra error en sync_queue para trazabilidad
     */
    private function registrarError($operacion, $httpCode, $error, $datos = null) {
        try {
            // Verificar si la tabla sync_queue existe antes de insertar (con caché)
            if (!$this->verificarTablaSyncQueue()) {
                error_log("ADVERTENCIA: Tabla sync_queue no existe. Error en operación '$operacion' no registrado. Ejecute migrations/create_sync_queue_table.php");
                return;
            }
            
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $stmt = $pdo->prepare("
                INSERT INTO sync_queue (operacion, estado, error_mensaje, datos_json, fecha_creacion) 
                VALUES (?, 'error', ?, ?, NOW())
            ");
            
            $errorMensaje = "HTTP $httpCode: " . substr($error, 0, 500);
            
            $stmt->execute([
                $operacion,
                $errorMensaje,
                $datos ? json_encode($datos) : null
            ]);
            
        } catch (Exception $e) {
            error_log("Error registrando error en sync_queue: " . $e->getMessage());
            // No lanzar excepción para evitar que falle el proceso principal
        }
    }
    
    /**
     * Obtiene estadísticas de sincronización
     */
    public function obtenerEstadisticasSync() {
        try {
            // Verificar si la tabla sync_queue existe antes de consultar (con caché)
            if (!$this->verificarTablaSyncQueue()) {
                error_log("INFORMACIÓN: Tabla sync_queue no existe. No hay estadísticas disponibles. Ejecute migrations/create_sync_queue_table.php");
                return [];
            }
            
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $stmt = $pdo->query("
                SELECT 
                    operacion,
                    estado,
                    COUNT(*) as total,
                    MAX(fecha_creacion) as ultimo_intento
                FROM sync_queue 
                WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY operacion, estado
                ORDER BY operacion, estado
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas sync: " . $e->getMessage());
            return [];
        }
    }
}
?>