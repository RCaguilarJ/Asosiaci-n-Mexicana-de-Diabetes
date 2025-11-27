<?php
    $paginaActual = 'login';
    $tituloDeLaPagina = "Acceso Usuarios - Asoc. Mexicana de Diabetes"; 
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
            <p>Inicia sesión en tu cuenta</p>
        </div>
    </header>

    <main class="contenedor">

        <h3 class="section-subtitle">Beneficios de tu Cuenta</h3>
        
        <div class="benefits-container">
            <div class="benefit-card">
                <div class="benefit-icon benefit-icon--blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                <div class="benefit-text">
                    <h4>Perfil Personalizado</h4>
                    <p>Guarda tu información médica de forma segura</p>
                </div>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon benefit-icon--green">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <div class="benefit-text">
                    <h4>Datos Seguros</h4>
                    <p>Tu información está protegida y encriptada</p>
                </div>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon benefit-icon--cyan">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>
                </div>
                <div class="benefit-text">
                    <h4>Notificaciones</h4>
                    <p>Recibe recordatorios y actualizaciones importantes</p>
                </div>
            </div>
        </div>

        <form id="login-form" class="card-form mt-30">
            
            <div class="form-group">
                <label for="login-email">Correo Electrónico</label>
                <div class="input-icon-wrapper">
                    <svg class="input-icon-left" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>
                    <input type="email" id="login-email" class="form-control pl-icon" placeholder="tu@correo.com" required>
                </div>
            </div>

            <div class="form-group">
                <label for="login-password">Contraseña</label>
                <div class="input-icon-wrapper">
                    <svg class="input-icon-left" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    
                    <input type="password" id="login-password" class="form-control pl-icon" placeholder="••••••••" required>
                    
                    <span class="toggle-password" id="toggle-password">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </span>
                </div>
            </div>

            <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>

            <button type="submit" class="btn-calculadora mt-20">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                <span>Iniciar Sesión</span>
            </button>

            <div class="form-divider">
                <span>O</span>
            </div>

            <button type="button" id="btn-crear-cuenta" class="btn-calculadora btn-outline-green">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                <span>Crear Nueva Cuenta</span>
            </button>

        </form>

        <section class="security-banner">
            <div class="security-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </div>
            <div class="security-text">
                <h4>Seguridad y Privacidad</h4>
                <p>Tus datos están protegidos con encriptación de nivel bancario. Nunca compartiremos tu información sin tu consentimiento.</p>
            </div>
        </section>

    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/app.js"></script> 
</body>
</html>