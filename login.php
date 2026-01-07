<?php
    session_start();
    require 'includes/db.php';

    $paginaActual = 'login';
    $tituloDeLaPagina = "Acceso Usuarios - Asoc. Mexicana de Diabetes"; 
    $mensaje = '';
    $tipoMensaje = ''; 

    // =======================================================
    // PROCESAMIENTO DE DATOS (PHP)
    // =======================================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // --- 1. LÓGICA LOGIN ---
        if (isset($_POST['accion']) && $_POST['accion'] === 'login') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $stmt = $pdo->prepare("SELECT id, nombre, email, password FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['password'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                unset($_SESSION['es_invitado']);
                header("Location: index.php");
                exit;
            } else {
                $mensaje = "Correo o contraseña incorrectos.";
                $tipoMensaje = 'error';
            }
        }

        // --- 2. LÓGICA REGISTRO ---
        if (isset($_POST['accion']) && $_POST['accion'] === 'registro') {
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
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
                    
                    if ($stmt->execute([$nombre, $email, $hash])) {
                        $mensaje = "¡Cuenta creada con éxito! Por favor inicia sesión.";
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
        if (isset($_POST['accion']) && $_POST['accion'] === 'invitado') {
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
            <input type="hidden" name="accion" value="login">
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

            <button type="submit" class="btn-calculadora mt-20"><span>Entrar</span></button>

            <div class="form-divider" style="text-align:center; margin: 15px 0;"><span>O</span></div>

            <button type="submit" name="accion" value="invitado" class="btn-calculadora btn-outline-guest" style="background:#6c757d; border:none; color:white;">
                <span>Ingresar como Invitado</span>
            </button>
            
            <div class="mt-20" style="text-align: center;">
                <p>¿No tienes cuenta? <a href="#" id="link-ir-registro" style="color:#0066b2; font-weight:bold; cursor: pointer;">Regístrate aquí</a></p>
            </div>
        </form>

        <form id="form-registro" method="POST" class="card-form mt-30" style="display: none;">
            <input type="hidden" name="accion" value="registro">
            <legend class="card-form-legend"><span>Crear Nueva Cuenta</span></legend>
            
            <div class="form-group">
                <label>Nombre Completo</label>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej. Juan Pérez">
            </div>

            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" class="form-control" required placeholder="tu@correo.com">
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" name="password" class="form-control password-input" required minlength="6" placeholder="Mínimo 6 caracteres">
                    <button type="button" class="toggle-password-btn">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn-calculadora mt-20" style="background-color: #28a745;">
                <span>Registrarse</span>
            </button>

            <div class="mt-20" style="text-align: center;">
                <p>¿Ya tienes cuenta? <a href="#" id="link-ir-login" style="color:#0066b2; font-weight:bold; cursor: pointer;">Inicia sesión aquí</a></p>
            </div>
        </form>

    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/login.js"></script>
</body>
</html>