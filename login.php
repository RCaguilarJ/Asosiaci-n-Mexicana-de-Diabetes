<?php
    session_start(); // Iniciar sesión de PHP
    require 'includes/db.php'; // Conexión a la BD

    $paginaActual = 'login';
    $tituloDeLaPagina = "Acceso Usuarios - Asoc. Mexicana de Diabetes"; 
    $mensaje = '';

    // LÓGICA DE LOGIN Y REGISTRO
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // --- CASO 1: REGISTRO (CREAR CUENTA) ---
        if (isset($_POST['accion']) && $_POST['accion'] === 'registro') {
            $nombre = trim($_POST['nombre']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            if (!empty($email) && !empty($password)) {
                // Verificar si el correo ya existe
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->rowCount() > 0) {
                    $mensaje = "Este correo ya está registrado.";
                } else {
                    // Encriptar contraseña y guardar
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
                    
                    if ($stmt->execute([$nombre, $email, $hash])) {
                        $mensaje = "¡Cuenta creada! Ahora puedes iniciar sesión.";
                    } else {
                        $mensaje = "Error al registrar.";
                    }
                }
            }
        }

        // --- CASO 2: LOGIN (INICIAR SESIÓN) ---
        if (isset($_POST['accion']) && $_POST['accion'] === 'login') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $stmt = $pdo->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['password'])) {
                // ¡Login exitoso! Guardamos datos en sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                header("Location: index.php"); // Redirigir al inicio
                exit;
            } else {
                $mensaje = "Correo o contraseña incorrectos.";
            }
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
            <div style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
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

            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn-calculadora mt-20">
                <span>Entrar</span>
            </button>

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

            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control" required>
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
        // Pequeño script para alternar formularios
        function mostrarRegistro() {
            document.getElementById('form-login').style.display = 'none';
            document.getElementById('form-registro').style.display = 'block';
        }
        function mostrarLogin() {
            document.getElementById('form-registro').style.display = 'none';
            document.getElementById('form-login').style.display = 'block';
        }
    </script>
</body>
</html>