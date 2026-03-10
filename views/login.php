<?php
    session_start();

    require_once __DIR__ . '/../includes/base_path.php';
    $esInvitado = !empty($_SESSION['es_invitado']);
    if (isset($_SESSION['usuario_id']) && !$esInvitado) {
        header('Location: ' . $basePath . '/views/index.php');
        exit;
    }

    $dbError = null;
    try {
        require '../includes/db.php';
    } catch (Throwable $e) {
        $dbError = 'No se pudo conectar con la base de datos. Intenta más tarde.';
        error_log('Login DB error: ' . $e->getMessage());
    }

    if (!$dbError && isset($pdo) && ($pdo instanceof PDO)) {
        require '../includes/sync_helper.php';
    }

    $paginaActual = 'login';
    $tituloDeLaPagina = "Acceso Usuarios - Asoc. Mexicana de Diabetes"; 
    $mensaje = '';
    $tipoMensaje = ''; 

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($dbError) {
            $mensaje = $dbError;
            $tipoMensaje = 'error';
        }
   
        if (!$dbError && isset($_POST['accion']) && $_POST['accion'] === 'login') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            error_log("[LOGIN DEBUG] intento de login para: " . $email);
            error_log("[LOGIN DEBUG] \\_ POST keys: " . implode(',', array_keys($_POST)));

            try {
                $stmt = $pdo->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
             
                if (strpos($e->getMessage(), 'Unknown column') !== false || strpos($e->getCode(), '42S22') !== false) {
                    // Asegurarse de que $pdo es una instancia válida de PDO antes de usarla
                    if (!isset($pdo) || !$pdo instanceof PDO) {
                        throw new RuntimeException('Conexión a la base de datos no disponible.');
                    }
                    $stmt = $pdo->prepare("SELECT id, nombre, email, password FROM usuarios WHERE email = ?");
                    $stmt->execute([$email]);
                    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                    // Aseguramos rol por defecto
                    if ($usuario) {
                        $usuario['rol'] = 'PACIENTE';
                    }
                } else {
                    // Re-lanzar si es otro error
                    throw $e;
                }
            }

            error_log("[LOGIN DEBUG] usuario encontrado: " . ($usuario ? 'SI' : 'NO'));
            if ($usuario) {
                error_log("[LOGIN DEBUG] hash almacenado existe: " . (isset($usuario['password']) ? 'SI' : 'NO'));
            }

            if ($usuario && password_verify($password, $usuario['password'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                // Guardar rol en sesión para lógica de autorización y notificaciones
                $_SESSION['usuario_rol'] = isset($usuario['rol']) ? $usuario['rol'] : 'PACIENTE';
                unset($_SESSION['es_invitado']);
                header("Location: index.php");
                exit;
            } else {
                $mensaje = "Correo o contraseña incorrectos.";
                $tipoMensaje = 'error';
            }
        }

        // --- 2. LÓGICA REGISTRO ---
        if (!$dbError && isset($_POST['accion']) && $_POST['accion'] === 'registro') {
            $nombre = trim($_POST['nombre']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $mensaje = "Este correo ya está registrado. Intenta iniciar sesión.";
                $tipoMensaje = 'error';
            } else {
                try {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    // Asignar rol por defecto 'PACIENTE' al registrarse desde el frontend
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
                    
                    if ($stmt->execute([$nombre, $email, $hash, 'PACIENTE'])) {
                        $usuarioId = $pdo->lastInsertId();
                        
                        // Sincronizar con el sistema de gestión médica
                        if (function_exists('sincronizarPacienteEnSistemaGestion')) {
                            try {
                                $pacienteId = sincronizarPacienteEnSistemaGestion([
                                    'nombre' => $nombre,
                                    'email' => $email,
                                    'telefono' => '', // Se puede actualizar después en el perfil
                                    'usuario_id_app' => $usuarioId
                                ]);
                                
                                $mensaje = $pacienteId ? 
                                    "¡Cuenta creada con éxito y registrada en el sistema médico! Por favor inicia sesión." :
                                    "¡Cuenta creada con éxito! Por favor inicia sesión.";
                            } catch (Exception $e) {
                                $mensaje = "¡Cuenta creada con éxito! Por favor inicia sesión.";
                                // Log del error de sincronización sin mostrar al usuario
                                error_log("Error sincronizando paciente: " . $e->getMessage());
                            }
                        } else {
                            $mensaje = "¡Cuenta creada con éxito! Por favor inicia sesión.";
                        }
                        
                        $tipoMensaje = 'exito';
                    } else {
                        $mensaje = "Error al guardar en la base de datos.";
                        $tipoMensaje = 'error';
                    }
                } catch (Exception $e) {
                    $mensaje = "Error técnico: " . $e->getMessage();
                    $tipoMensaje = 'error';
                }
            }
        }

        // --- 3. LÓGICA INVITADO ---
        if (!$dbError && isset($_POST['accion']) && $_POST['accion'] === 'invitado') {
            $_SESSION['es_invitado'] = true;
            $_SESSION['usuario_nombre'] = 'Invitado';
            unset($_SESSION['usuario_id']);
            header("Location: index.php");
            exit;
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../includes/layout/head.php'; ?>
<body>
    <?php include '../includes/layout/menu-drawer.php'; ?>
    <?php include '../includes/layout/header.php'; ?>

    <header class="page-header">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </div>
        <div class="page-header-text">
            <h1>Acceso Usuarios</h1>
            <p>Gestiona tu cuenta y expediente</p>
        </div>
    </header>

    <main class="contenedor">

        <?php if(!empty($mensaje)): ?>
            <div style="padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold; 
                <?php echo ($tipoMensaje == 'exito') ? 'background:#d4edda; color:#155724;' : 'background:#f8d7da; color:#721c24;'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form id="form-login" method="POST" class="card-form mt-30">
            <legend class="card-form-legend"><span>Iniciar Sesión</span></legend>
            
            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" class="form-control" required placeholder="tu@correo.com">
            </div>

            <div class="form-group password-group">
                <label>Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" name="password" class="form-control password-input" required>
                    <button type="button" class="toggle-password-btn">👁️</button>
                </div>
            </div>

            <button type="submit" name="accion" value="login" class="btn-calculadora mt-20"><span>Entrar</span></button>

            <div class="form-divider" style="text-align:center; margin: 15px 0;"><span>O</span></div>

            <button type="submit" name="accion" value="invitado" class="btn-calculadora btn-outline-guest" style="background:#6c757d; border:none; color:white;">
                <span>Ingresar como Invitado</span>
            </button>
            
            <div class="mt-20" style="text-align: center;">
                <p style="color: #6c757d; margin-bottom: 10px;">¿No tienes cuenta?</p>
                <a href="#" id="link-ir-registro" style="color:#0066b2; font-weight:bold; cursor: pointer; text-decoration: none; padding: 8px 15px; border: 1px solid #0066b2; border-radius: 5px; display: inline-block; transition: all 0.3s ease;">
                    📝 Regístrate aquí
                </a>
            </div>
        </form>

        <form id="form-registro" method="POST" class="card-form mt-30" style="display: none;">
            <input type="hidden" name="accion" value="registro">
            <legend class="card-form-legend" style="background: linear-gradient(135deg, #28a745, #20c997);"><span>✨ Crear Nueva Cuenta</span></legend>
            
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <strong>🎉 ¡Únete a nuestra comunidad!</strong><br>
                <small>Registra tus datos de salud y accede a herramientas personalizadas.</small>
            </div>

            <div class="form-group">
                <label>👤 Nombre Completo</label>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej. Juan Pérez García" minlength="2">
                <small style="color: #6c757d; font-size: 12px;">Mínimo 2 caracteres</small>
            </div>

            <div class="form-group">
                <label>📧 Correo Electrónico</label>
                <input type="email" name="email" class="form-control" required placeholder="tu@correo.com">
                <small style="color: #6c757d; font-size: 12px;">Usaremos este correo para tu acceso</small>
            </div>

            <div class="form-group">
                <label>🔒 Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" name="password" class="form-control password-input" required minlength="6" placeholder="Mínimo 6 caracteres">
                    <button type="button" class="toggle-password-btn">👁️</button>
                </div>
                <small style="color: #6c757d; font-size: 12px;">Usa letras, números y símbolos para mayor seguridad</small>
            </div>

            <button type="submit" class="btn-calculadora mt-20" style="background: linear-gradient(135deg, #28a745, #20c997); border: none; font-size: 16px; font-weight: bold;">
                <span>🚀 Crear Mi Cuenta</span>
            </button>

            <div class="mt-20" style="text-align: center;">
                <p style="color: #6c757d; margin-bottom: 10px;">¿Ya tienes cuenta?</p>
                <a href="#" id="link-ir-login" style="color:#0066b2; font-weight:bold; cursor: pointer; text-decoration: none; padding: 8px 15px; border: 1px solid #0066b2; border-radius: 5px; display: inline-block; transition: all 0.3s ease;">
                    🔑 Inicia sesión aquí
                </a>
            </div>
        </form>

    </main>

    <?php include '../includes/layout/footer.php'; ?>
    
    <script src="<?php echo $basePath; ?>/assets/js/login.js"></script>
</body>
</html>
