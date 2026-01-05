<?php
    // VERIFICAR SESIÓN - El usuario debe estar logueado para acceder
    require 'includes/check-session.php';
    
    // 1. Definimos la página actual y el título
    $paginaActual = 'eventos';
    $tituloDeLaPagina = "Eventos - Asoc. Mexicana de Diabetes"; 
?>
<!DOCTYPE html>
<html lang="es">

<?php 
    // 2. Incluimos el <head>
    include 'includes/head.php'; 
?>

<body>

    <?php 
        // 3. Incluimos el menú deslizante
        include 'includes/menu-drawer.php'; 
    ?>

    <?php 
        // 4. Incluimos el header (barra superior)
        include 'includes/header.php'; 
    ?>

    <header class="page-header page-header--green">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><path d="M3 10h18"></path></svg>
        </div>
        <div class="page-header-text">
            <h1>Eventos</h1>
            <p>Próximas actividades</p>
        </div>
    </header>

    <section class="event-stats-container contenedor">
        <div class="stat-card">
            <strong>3</strong>
            <span>Próximos</span>
        </div>
        <div class="stat-card">
            <strong>1</strong>
            <span>Registrado</span>
        </div>
        <div class="stat-card">
            <strong>193</strong>
            <span>Total</span>
        </div>
    </section>


    <main class="contenedor">

        <section class="event-list-section">
            <h2>Próximos Eventos</h2>
            
            <div class="event-list">
                
                <article class="event-card">
                    <img src="assets/img/eventos" alt="Taller de Nutrición" class="event-card-image">
                    <div class="event-card-content">
                        <span class="event-tag event-tag--educativo">Educativo</span>
                        <h3>Taller de Nutrición para Diabéticos</h3>
                        <p>Aprende a preparar comidas saludables y controlar carbohidratos.</p>
                        
                        <ul class="event-details">
                            <li><span>viernes, 14 de noviembre de 2025</span></li>
                            <li><span>10:00 AM</span></li>
                            <li><span>Centro de Salud Metropolitano</span></li>
                        </ul>

                        <div class="event-progress">
                            <div class="event-progress-label">
                                <span>Inscritos</span>
                                <span>42/60</span>
                            </div>
                            <div class="event-progress-bar">
                                <div class="event-progress-fill" style="width: 70%;"></div>
                            </div>
                        </div>

                        <a href="#" class="btn-evento btn-evento--registrar">
                            <span>Registrarse</span>
                        </a>
                    </div>
                </article>

                <article class="event-card">
                    <img src="assets/img/eventos" alt="Caminata por la Diabetes" class="event-card-image">
                    <div class="event-card-content">
                        <span class="event-tag event-tag--actividad">Actividad Física</span>
                        <h3>Caminata por la Diabetes</h3>
                        <p>Únete a nuestra caminata mensual para promover la actividad física.</p>
                        
                        <ul class="event-details">
                            <li><span>viernes, 21 de noviembre de 2025</span></li>
                            <li><span>7:00 AM</span></li>
                            <li><span>Parque Metropolitano Guadalajara</span></li>
                        </ul>

                        <div class="event-progress">
                            <div class="event-progress-label">
                                <span>Inscritos</span>
                                <span>120/200</span>
                            </div>
                            <div class="event-progress-bar">
                                <div class="event-progress-fill" style="width: 60%;"></div>
                            </div>
                        </div>

                        <a href="#" class="btn-evento btn-evento--registrado" disabled>
                            <span>Ya Registrado</span>
                        </a>
                    </div>
                </article>

                <article class="event-card">
                    <img src="assets/img/eventos" alt="Conferencia" class="event-card-image">
                    <div class="event-card-content">
                        <span class="event-tag event-tag--conferencia">Conferencia</span>
                        <h3>Conferencia: Nuevos Tratamientos</h3>
                        <p>Conoce los últimos avances en el tratamiento de la diabetes.</p>
                        
                        <ul class="event-details">
                            <li><span>jueves, 4 de diciembre de 2025</span></li>
                            <li><span>7:00 PM</span></li>
                            <li><span>Auditorio AMD Jalisco</span></li>
                        </ul>

                        <div class="event-progress">
                            <div class="event-progress-label">
                                <span>Inscritos</span>
                                <span>28/100</span>
                            </div>
                            <div class="event-progress-bar">
                                <div class="event-progress-fill" style="width: 28%;"></div>
                            </div>
                        </div>

                        <a href="#" class="btn-evento btn-evento--registrar">
                            <span>Registrarse</span>
                        </a>
                    </div>
                </article>

            </div>
        </section>

        <section class="quick-actions">
            <h2>Acciones</h2>
            <div class="actions-grid">
                <a href="#" class="action-button action-button--blue">
                    <span>Mis Eventos</span>
                </a>
                <a href="#" class="action-button action-button--eventos">
                    <span>Proponer</span>
                </a>
            </div>
        </section>

    </main>
    <?php 
        // 5. Incluimos el pie de página
        include 'includes/footer.php'; 
    ?>

    <script src="assets/js/app.js"></script> 
</body>
</html>