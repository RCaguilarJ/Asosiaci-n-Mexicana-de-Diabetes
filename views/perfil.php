<?php
    // VERIFICAR SESIÓN - El usuario debe estar logueado para acceder
    require 'includes/check-session.php';
    require 'includes/db.php';

    $paginaActual = 'perfil';
    $tituloDeLaPagina = "Mi Perfil - Asoc. Mexicana de Diabetes";
    $mensaje = '';
    $tipoMensaje = '';

    // Obtener datos del usuario actual
    try {
        $stmt = $pdo->prepare("SELECT id, nombre, email, password, fecha_registro FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuarioActual = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error BD: " . $e->getMessage());
        $usuarioActual = null;
    }

    // Procesar cambio de contraseña
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_password') {
        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (empty($passwordActual) || empty($passwordNueva) || empty($passwordConfirm)) {
            $mensaje = "Todos los campos son requeridos.";
            $tipoMensaje = 'error';
        } elseif (strlen($passwordNueva) < 6) {
            $mensaje = "La nueva contraseña debe tener al menos 6 caracteres.";
            $tipoMensaje = 'error';
        } elseif ($passwordNueva !== $passwordConfirm) {
            $mensaje = "Las contraseñas nuevas no coinciden.";
            $tipoMensaje = 'error';
        } else {
            // Verificar contraseña actual
            if ($usuarioActual && password_verify($passwordActual, $usuarioActual['password'])) {
                try {
                    $hash = password_hash($passwordNueva, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                    if ($stmt->execute([$hash, $_SESSION['usuario_id']])) {
                        $mensaje = "✓ Contraseña cambiada exitosamente.";
                        $tipoMensaje = 'exito';
                    } else {
                        $mensaje = "Error al actualizar la contraseña. Intenta más tarde.";
                        $tipoMensaje = 'error';
                    }
                } catch (Exception $e) {
                    $mensaje = "Error en la base de datos.";
                    $tipoMensaje = 'error';
                }
            } else {
                $mensaje = "La contraseña actual es incorrecta.";
                $tipoMensaje = 'error';
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
            <h1>Mi Perfil</h1>
            <p>Gestiona tu información personal</p>
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

        <!-- INFORMACIÓN DEL USUARIO -->
        <div class="card-form mt-30">
            <legend class="card-form-legend"><span>Información de la Cuenta</span></legend>
            
            <div class="form-group">
                <label>Nombre Completo</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuarioActual['nombre'] ?? ''); ?>" disabled>
            </div>

            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($usuarioActual['email'] ?? ''); ?>" disabled>
            </div>

            <div class="form-group">
                <label>Fecha de Registro</label>
                <input type="text" class="form-control" value="<?php 
                    if ($usuarioActual && $usuarioActual['fecha_registro']) {
                        $fecha = new DateTime($usuarioActual['fecha_registro']);
                        echo $fecha->format('d/m/Y H:i');
                    }
                ?>" disabled>
            </div>
        </div>

        <!-- CAMBIAR CONTRASEÑA -->
        <form method="POST" class="card-form mt-30">
            <input type="hidden" name="accion" value="cambiar_password">
            <legend class="card-form-legend"><span>Cambiar Contraseña</span></legend>
            
            <div class="form-group password-group">
                <label>Contraseña Actual</label>
                <div class="password-wrapper">
                    <input type="password" name="password_actual" class="form-control password-input" required>
                    <button type="button" class="toggle-password-btn" onclick="togglePassword(this)" title="Mostrar/Ocultar contraseña">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <div class="form-group password-group">
                <label>Nueva Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" name="password_nueva" class="form-control password-input" required minlength="6" placeholder="Mínimo 6 caracteres">
                    <button type="button" class="toggle-password-btn" onclick="togglePassword(this)" title="Mostrar/Ocultar contraseña">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <div class="form-group password-group">
                <label>Confirmar Nueva Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" name="password_confirm" class="form-control password-input" required minlength="6" placeholder="Repite tu contraseña">
                    <button type="button" class="toggle-password-btn" onclick="togglePassword(this)" title="Mostrar/Ocultar contraseña">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-calculadora mt-20">
                <span>Actualizar Contraseña</span>
            </button>
        </form>

        <!-- CERRAR SESIÓN -->
        <div class="card-form mt-30" style="background: #fff3cd; border: 2px solid #ffc107;">
            <legend class="card-form-legend"><span>Sesión</span></legend>
            <p style="margin-bottom: 20px; color: #666;">Cuando cierres sesión, necesitarás volver a iniciar con tus credenciales.</p>
            <a href="logout.php" class="btn-calculadora" style="background-color: #dc3545; text-align: center; display: block; text-decoration: none;">
                <span>Cerrar Sesión</span>
            </a>
        </div>

    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/app.js"></script>

    <script>
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
