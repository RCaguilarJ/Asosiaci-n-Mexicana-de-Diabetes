<?php
    // Validación para evitar errores si la variable no está definida
    if (!isset($paginaActual)) {
        $paginaActual = '';
    }
?>
<div class="menu-overlay"></div>

<nav class="offcanvas-menu">
    
    <div class="menu-drawer-header">
        <div class="menu-drawer-logo">
            <img src="assets/images/logo.png" alt="Logo AMD Jalisco">
            <span>
                <strong>AMD Jalisco</strong>
                <small>Menú Principal</small>
            </span>
        </div>
        <button class="menu-close-btn" aria-label="Cerrar menú">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>

    <ul class="menu-drawer-nav">
        <li>
            <a href="index.php" class="menu-drawer-link <?php echo ($paginaActual == 'inicio') ? 'active' : ''; ?>">
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Inicio</span>
                    <span class="menu-drawer-link__subtitle">Panel principal</span>
                </span>
            </a>
        </li>

        <li>
            <a href="calculadora.php" class="menu-drawer-link <?php echo ($paginaActual == 'calculadora') ? 'active' : ''; ?>"> 
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2"></rect><line x1="8" x2="16" y1="6" y2="6"></line><line x1="16" x2="16" y1="14" y2="18"></line><path d="M16 10h.01"></path><path d="M12 10h.01"></path><path d="M8 10h.01"></path><path d="M12 14h.01"></path><path d="M8 14h.01"></path><path d="M12 18h.01"></path><path d="M8 18h.01"></path></svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Calculadora de Diabetes</span>
                    <span class="menu-drawer-link__subtitle">Calcula dosis de insulina</span>
                </span>
            </a>
        </li>

        <li>
            <a href="eventos.php" class="menu-drawer-link <?php echo ($paginaActual == 'eventos') ? 'active' : ''; ?>">
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Eventos</span>
                    <span class="menu-drawer-link__subtitle">Próximas actividades</span>
                </span>
            </a>
        </li>

        <li>
            <a href="blog.php" class="menu-drawer-link <?php echo ($paginaActual == 'blog') ? 'active' : ''; ?>">
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Blog</span>
                    <span class="menu-drawer-link__subtitle">Educación y consejos</span>
                </span>
            </a>
        </li>

        <li>
            <a href="#" class="menu-drawer-link"> 
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Galería de Videos</span>
                    <span class="menu-drawer-link__subtitle">Videos educativos</span>
                </span>
            </a>
        </li>

        <li>
            <a href="#" class="menu-drawer-link"> 
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Educadores en Diabetes</span>
                    <span class="menu-drawer-link__subtitle">Especialistas certificados</span>
                </span>
            </a>
        </li>

        <li>
            <a href="citas.php" class="menu-drawer-link <?php echo ($paginaActual == 'citas') ? 'active' : ''; ?>"> 
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Agendar Cita</span>
                    <span class="menu-drawer-link__subtitle">Gestiona consultas</span>
                </span>
            </a>
        </li>

        <li>
            <a href="contacto.php" class="menu-drawer-link <?php echo ($paginaActual == 'contacto') ? 'active' : ''; ?>"> 
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 18a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><circle cx="12" cy="10" r="2"></circle><line x1="8" x2="8" y1="2" y2="4"></line><line x1="16" x2="16" y1="2" y2="4"></line></svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Contacto</span>
                    <span class="menu-drawer-link__subtitle">Contáctanos</span>
                </span>
            </a>
        </li>

        <li>
            <a href="login.php" class="menu-drawer-link <?php echo ($paginaActual == 'login') ? 'active' : ''; ?>"> 
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Acceso Usuarios</span>
                    <span class="menu-drawer-link__subtitle">Iniciar sesión</span>
                </span>
            </a>
        </li>
    </ul>

    <div class="menu-drawer-footer">
        <p>Versión 1.0.0</p>
        <p>&copy; <?php echo date("Y"); ?> Asociación Mexicana de Diabetes en Jalisco A.C.</p>
    </div>
</nav>