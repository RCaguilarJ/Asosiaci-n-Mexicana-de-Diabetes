<?php
    session_start(); // 1. INICIAR SESIÓN (SIEMPRE PRIMERO)

    // 2. EL "CADENERO": Verificar si el usuario está logueado o es invitado
    if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['es_invitado'])) {
        header('Location: login.php');
        exit;
    }

    require 'includes/db.php'; // 3. CONECTAR A LA BASE DE DATOS

    $paginaActual = 'inicio';
    $tituloDeLaPagina = "Mi Panel - Asoc. Mexicana de Diabetes"; 
    
    // Variables por defecto
    $nivelGlucosa = "--";
    $ultimaMedicion = "Sin registros";
    $promedioSemanal = "--";
    $colorGlucosa = "#e0f0ff"; 

    // 4. CONSULTAR DATOS (Solo si no es invitado)
    if (isset($_SESSION['usuario_id'])) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM registros_glucosa WHERE usuario_id = ? ORDER BY fecha_registro DESC LIMIT 1");
            $stmt->execute([$_SESSION['usuario_id']]);
            $ultimoRegistro = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($ultimoRegistro) {
                $nivelGlucosa = $ultimoRegistro['nivel_glucosa'] . " mg/dL";
                $fechaRegistro = new DateTime($ultimoRegistro['fecha_registro']);
                $ultimaMedicion = $fechaRegistro->format('d/m/Y h:i A');

            if ($ultimoRegistro['nivel_glucosa'] > 180) {
                $colorGlucosa = "#ffe0e0"; 
            } elseif ($ultimoRegistro['nivel_glucosa'] < 70) {
                $colorGlucosa = "#fffbe0"; 
            }
        }

            $stmtAvg = $pdo->prepare("SELECT AVG(nivel_glucosa) as promedio FROM registros_glucosa WHERE usuario_id = ?");
            $stmtAvg->execute([$_SESSION['usuario_id']]);
            $avg = $stmtAvg->fetch(PDO::FETCH_ASSOC);
            if ($avg && $avg['promedio']) {
                $promedioSemanal = round($avg['promedio']) . " mg/dL";
            }

        } catch (Exception $e) {
            error_log("Error BD: " . $e->getMessage());
        }
    }
?>
<!DOCTYPE html>
<html lang="es">

<?php include 'includes/head.php'; ?>

<body>

    <?php include 'includes/menu-drawer.php'; ?>
    <?php include 'includes/header.php'; ?>

    <section class="welcome-hero">
        <div class="welcome-hero-content">
            <div class="welcome-text">
                <h1>¡Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>!</h1>
                <p>Cuidando tu salud cada día</p>
            </div>
            <div class="welcome-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart w-7 h-7 text-white" aria-hidden="true"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg>
            </div>
        </div>

        <div class="status-cards-container">
            <div class="status-card">
                <div class="status-icon-container" style="background-color: <?php echo $colorGlucosa; ?>;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-droplet w-6 h-6" aria-hidden="true" style="color: rgb(0, 102, 178);"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"></path></svg>
                </div>
                <div class="status-text">
                    <span class="status-title">Nivel de Glucosa</span>
                    <span class="status-value"><?php echo $nivelGlucosa; ?></span>
                </div>
                <div class="status-bar" style="background-color: #007bff;"></div>
            </div>

            <div class="status-card">
                <div class="status-icon-container" style="background-color: #e0fff0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-activity w-6 h-6" aria-hidden="true" style="color: rgb(124, 179, 66);"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"></path></svg>
                </div>
                <div class="status-text">
                    <span class="status-title">Última Medición</span>
                    <span class="status-value"><?php echo $ultimaMedicion; ?></span>
                </div>
                <div class="status-bar" style="background-color: #28a745;"></div>
            </div>

            <div class="status-card">
                <div class="status-icon-container" style="background-color: #f0e6ff;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up w-6 h-6" aria-hidden="true" style="color: rgb(0, 151, 216);"><path d="M16 7h6v6"></path><path d="m22 7-8.5 8.5-5-5L2 17"></path></svg>
                </div>
                <div class="status-text">
                    <span class="status-title">Promedio Semanal</span>
                    <span class="status-value"><?php echo $promedioSemanal; ?></span>
                </div>
                <div class="status-bar" style="background-color: #007bff;"></div>
            </div>
        </div>
    </section>

    <main class="contenedor">

        <?php include 'includes/check-guest-banner.php'; ?>

        <section class="reminders-section">
            <div class="card-base card-reminder">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-alert w-5 h-5 mt-0.5" aria-hidden="true" style="color: rgb(245, 158, 11);"><circle cx="12" cy="12" r="10"></circle><line x1="12" x2="12" y1="8" y2="12"></line><line x1="12" x2="12.01" y1="16" y2="16"></line></svg>
                <div class="card-content">
                    <span class="card-title">Recordatorio de Medicamento</span>
                    <span class="card-subtitle">Tomar medicamento a las 8:00 PM</span>
                </div>
            </div>
            <div class="card-base card-appointment">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar w-5 h-5 mt-0.5" aria-hidden="true" style="color: rgb(0, 102, 178);"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><path d="M3 10h18"></path></svg>
                <div class="card-content">
                    <span class="card-title">Próxima Cita Médica</span>
                    <span class="card-subtitle">15 de Noviembre de 2025</span>
                </div>
            </div>
        </section>

        <section class="quick-actions">
            <h2>Acciones Rápidas</h2>
            <div class="actions-grid">
                <a href="calculadora.php" class="action-button action-button--blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calculator w-7 h-7" aria-hidden="true" style="color: rgb(0, 102, 178);"><rect width="16" height="20" x="4" y="2" rx="2"></rect><line x1="8" x2="16" y1="6" y2="6"></line><line x1="16" x2="16" y1="14" y2="18"></line><path d="M16 10h.01"></path><path d="M12 10h.01"></path><path d="M8 10h.01"></path><path d="M12 14h.01"></path><path d="M8 14h.01"></path><path d="M12 18h.01"></path><path d="M8 18h.01"></path></svg>
                    <span>Calculadora</span>
                </a>
                <a href="eventos.php" class="action-button action-button--eventos">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open w-7 h-7" aria-hidden="true" style="color: rgb(124, 179, 66);"><path d="M12 7v14"></path><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"></path></svg>
                    <span>Eventos</span>
                </a>
                <a href="blog.php" class="action-button action-button--blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open w-7 h-7" aria-hidden="true" style="color: rgb(0, 151, 216);"><path d="M12 7v14"></path><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"></path></svg>
                    <span>Blog</span>
                </a>
                <a href="contacto.php" class="action-button action-button--contacto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users w-7 h-7" aria-hidden="true" style="color: rgb(0, 168, 89);"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><path d="M16 3.128a4 4 0 0 1 0 7.744"></path><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    <span>Contacto</span>
                </a>
            </div>
        </section>

        <section class="health-tips">
            <h2>Consejos de Salud</h2>
            <div class="tips-list">
                <article class="tip-item">
                    <img src="assets/img/platilloSano.jpg" alt="Plato de comida saludable" class="tip-image">
                    <div class="tip-content">
                        <h3>Alimentación Saludable</h3>
                        <p>Mantén una dieta balanceada rica en fibra, verduras y proteínas magras.</p>
                    </div>
                </article>
                <article class="tip-item">
                    <img src="assets/img/medidorInsulina.jpg" alt="Monitor de glucosa" class="tip-image">
                    <div class="tip-content">
                        <h3>Monitoreo Regular</h3>
                        <p>Registra tus niveles de glucosa diariamente para un mejor control.</p>
                    </div>
                </article>
            </div>
        </section>

        <section class="nutrition-tip-card">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-apple w-8 h-8" aria-hidden="true" style="color: white;"><path d="M12 6.528V3a1 1 0 0 1 1-1h0"></path><path d="M18.237 21A15 15 0 0 0 22 11a6 6 0 0 0-10-4.472A6 6 0 0 0 2 11a15.1 15.1 0 0 0 3.763 10 3 3 0 0 0 3.648.648 5.5 5.5 0 0 1 5.178 0A3 3 0 0 0 18.237 21"></path></svg>
            <h3>Tips Nutricionales</h3>
            <p>Una alimentación balanceada es fundamental. Incluye vegetales, proteínas magras y granos enteros en tu dieta diaria.</p>
            <a href="#" class="btn-ver-mas">Ver más consejos</a>
        </section>

    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/app.js"></script> 
</body>
</html>