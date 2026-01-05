<?php
    session_start(); // Iniciar sesión de PHP
    require 'includes/db.php'; // Conexión a la BD

    // Si ya está logueado, redirigir al index
    if (isset($_SESSION['usuario_id']) || isset($_SESSION['es_invitado'])) {
        header('Location: index.php');
        exit;
    }

    $paginaActual = 'login';
    $tituloDeLaPagina = "Acceso Usuarios - Asoc. Mexicana de Diabetes"; 
    $mensaje = '';
    $tipoMensaje = ''; // 'exito', 'error', 'advertencia'

    // LÓGICA DE LOGIN Y REGISTRO
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // --- CASO 1: REGISTRO (CREAR CUENTA) ---
        if (isset($_POST['accion']) && $_POST['accion'] === 'registro') {
            $nombre = trim($_POST['nombre']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validaciones
            if (empty($nombre)) {
                $mensaje = "El nombre completo es requerido.";
                $tipoMensaje = 'error';
            } elseif (empty($email)) {
                $mensaje = "El correo electrónico es requerido.";
                $tipoMensaje = 'error';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mensaje = "El correo electrónico no es válido.";
                $tipoMensaje = 'error';
            } elseif (empty($password)) {
                $mensaje = "La contraseña es requerida.";
                $tipoMensaje = 'error';
            } elseif (strlen($password) < 6) {
                $mensaje = "La contraseña debe tener al menos 6 caracteres.";
                $tipoMensaje = 'error';
            } elseif ($password !== $confirmPassword) {
                $mensaje = "Las contraseñas no coinciden.";
                $tipoMensaje = 'error';
            } else {
                // Verificar si el correo ya existe
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->rowCount() > 0) {
                    $mensaje = "Este correo ya está registrado. Intenta con otro.";
                    $tipoMensaje = 'error';
                } else {
                    try {
                        // Encriptar contraseña y guardar
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
                        
                        if ($stmt->execute([$nombre, $email, $hash])) {
                            $mensaje = "✓ ¡Cuenta creada exitosamente! Ya puedes iniciar sesión con tus credenciales.";
                            $tipoMensaje = 'exito';
                        } else {
                            $mensaje = "Error al registrar. Intenta más tarde.";
                            $tipoMensaje = 'error';
                        }
                    } catch (Exception $e) {
                        $mensaje = "Error en la base de datos. Intenta más tarde.";
                        $tipoMensaje = 'error';
                    }
                }
            }
        }

        // --- CASO 2: LOGIN (INICIAR SESIÓN) ---
        if (isset($_POST['accion']) && $_POST['accion'] === 'login') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            if (empty($email) || empty($password)) {
                $mensaje = "Por favor completa todos los campos.";
                $tipoMensaje = 'error';
            } else {
                $stmt = $pdo->prepare("SELECT id, nombre, email, password FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($usuario && password_verify($password, $usuario['password'])) {
                    // ¡Login exitoso! Guardamos datos en sesión
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre'];
                    $_SESSION['usuario_email'] = $usuario['email'];
                    header("Location: index.php"); // Redirigir al inicio
                    exit;
                } else {
                    $mensaje = "Correo o contraseña incorrectos.";
                    $tipoMensaje = 'error';
                }
            }
        }

        // --- CASO 3: INVITADO (INGRESAR COMO INVITADO) ---
        if (isset($_POST['accion']) && $_POST['accion'] === 'invitado') {
            // Crear sesión de invitado
            $_SESSION['es_invitado'] = true;
            $_SESSION['usuario_nombre'] = 'Invitado';
            $_SESSION['usuario_id'] = null; // Sin ID de BD
            header("Location: index.php");
            exit;
        }
    }
?>
<!DOCTYPE html>
<html lang="es">

<?php include 'includes/head.php'; ?>

<body>

    <?php include 'includes/menu-drawer.php'; ?>
    <?php include 'includes/header.php'; ?>

    <header class="page-header">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </div>
        <div class="page-header-text">
            <h1>Acceso Usuarios</h1>
            <p>Gestiona tu cuenta</p>
        </div>
    </header>

    <main class="contenedor">

        <?php if(!empty($mensaje)): ?>
            <div style="
                padding: 15px 20px; 
                border-radius: 8px; 
                margin-bottom: 25px; 
                text-align: center; 
                font-weight: 500;
                border-left: 4px solid;
                <?php 
                if ($tipoMensaje === 'exito') {
                    echo 'background: #d4edda; color: #155724; border-color: #28a745;';
                } elseif ($tipoMensaje === 'error') {
                    echo 'background: #f8d7da; color: #721c24; border-color: #f5c6cb;';
                } else {
                    echo 'background: #fff3cd; color: #856404; border-color: #ffeeba;';
                }
                ?>
            ">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form id="form-login" method="POST" class="card-form mt-30">
            <input type="hidden" name="accion" value="login">
            <legend class="card-form-legend"><span>Iniciar Sesión</span></legend>
            
            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group password-group">
                <label>Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" name="password" class="form-control password-input" required>
                    <button type="button" class="toggle-password-btn" onclick="togglePassword(this)" title="Mostrar/Ocultar contraseña">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-calculadora mt-20">
                <span>Entrar</span>
            </button>

            <div class="form-divider"><span>O</span></div>

            <form method="POST" style="margin: 0;">
                <input type="hidden" name="accion" value="invitado">
                <button type="submit" class="btn-calculadora btn-outline-guest" style="background-color: #6c757d; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span>Ingresar como Invitado</span>
                </button>
            </form>

            <div class="form-divider"><span>O</span></div>

            <button type="button" onclick="mostrarRegistro()" class="btn-calculadora btn-outline-green">
                <span>Crear Nueva Cuenta</span>
            </button>
        </form>

        <form id="form-registro" method="POST" class="card-form mt-30" style="display: none;">
            <input type="hidden" name="accion" value="registro">
            <legend class="card-form-legend"><span>Crear Cuenta</span></legend>
            
            <div class="form-group">
                <label>Nombre Completo</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group password-group">
                <label>Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" name="password" class="form-control password-input" required minlength="6" placeholder="Mínimo 6 caracteres">
                    <button type="button" class="toggle-password-btn" onclick="togglePassword(this)" title="Mostrar/Ocultar contraseña">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <div class="form-group password-group">
                <label>Confirmar Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" class="form-control password-input" required minlength="6" placeholder="Repite tu contraseña">
                    <button type="button" class="toggle-password-btn" onclick="togglePassword(this)" title="Mostrar/Ocultar contraseña">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-calculadora mt-20" style="background-color: var(--color-secundario-verde);">
                <span>Registrarse</span>
            </button>

            <div class="form-divider"><span>O</span></div>

            <button type="button" onclick="mostrarLogin()" class="btn-calculadora btn-outline-green">
                <span>Ya tengo cuenta</span>
            </button>
        </form>

    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/app.js"></script> 

    <script>
        // Alternar formularios de login/registro
        function mostrarRegistro() {
            document.getElementById('form-login').style.display = 'none';
            document.getElementById('form-registro').style.display = 'block';
        }
        function mostrarLogin() {
            document.getElementById('form-registro').style.display = 'none';
            document.getElementById('form-login').style.display = 'block';
        }

        // Mostrar/Ocultar contraseña
        function togglePassword(btn) {
            event.preventDefault();
            const passwordWrapper = btn.closest('.password-wrapper');
            const passwordInput = passwordWrapper.querySelector('.password-input');
            const isPassword = passwordInput.type === 'password';
            
            passwordInput.type = isPassword ? 'text' : 'password';
            
            // Cambiar opacidad del ícono para indicar estado
            btn.style.opacity = isPassword ? '1' : '0.5';
        }
    </script>
</body>
</html>