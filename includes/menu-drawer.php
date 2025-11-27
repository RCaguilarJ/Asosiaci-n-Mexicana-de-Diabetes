<div class="menu-overlay"></div>

<nav class="offcanvas-menu">
    
    <div class="menu-drawer-header">
        <div class="menu-drawer-logo">
            <img src="assets/img/logo.png" alt="Logo AMD Jalisco">
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
            <a href="index.php" class="menu-drawer-link <?php echo (isset($paginaActual) && $paginaActual === 'inicio') ? 'active' : ''; ?>">
                <span class="menu-drawer-link__icon">
                    </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Inicio</span>
                    <span class="menu-drawer-link__subtitle">Panel principal</span>
                </span>
            </a>
        </li>
        <li>
            <a href="calculadora.php" class="menu-drawer-link <?php echo (isset($paginaActual) && $paginaActual === 'calculadora') ? 'active' : ''; ?>"> <span class="menu-drawer-link__icon">
                    </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Calculadora de Diabetes</span>
                    <span class="menu-drawer-link__subtitle">Calcula dosis de insulina</span>
                </span>
            </a>
        </li>
        <li>
            <a href="eventos.php" class="menu-drawer-link <?php echo (isset($paginaActual) && $paginaActual === 'eventos') ? 'active' : ''; ?>">
                <span class="menu-drawer-link__icon">
                    </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Eventos</span>
                    <span class="menu-drawer-link__subtitle">Próximas actividades</span>
                </span>
            </a>
        </li>
        <li>
            <a href="blog.php" class="menu-drawer-link <?php echo (isset($paginaActual) && $paginaActual === 'blog') ? 'active' : ''; ?>">
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
            <a href="#" class="menu-drawer-link <?php echo (isset($paginaActual) && $paginaActual === 'videos') ? 'active' : ''; ?>"> <span class="menu-drawer-link__icon">
                    </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Galería de Videos</span>
                    <span class="menu-drawer-link__subtitle">Videos educativos</span>
                </span>
            </a>
        </li>
        <li>
            <a href="#" class="menu-drawer-link <?php echo (isset($paginaActual) && $paginaActual === 'educadores') ? 'active' : ''; ?>"> <span class="menu-drawer-link__icon">
                    </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Educadores en Diabetes</span>
                    <span class="menu-drawer-link__subtitle">Especialistas certificados</span>
                </span>
            </a>
        </li>
        <li>
            <a href="citas.php" class="menu-drawer-link <?php echo (isset($paginaActual) && $paginaActual === 'citas') ? 'active' : ''; ?>"> <span class="menu-drawer-link__icon">
                    </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Agendar Cita</span>
                    <span class="menu-drawer-link__subtitle">Gestiona consultas</span>
                </span>
            </a>
        </li>
        <li>
            <a href="contacto.php" class="menu-drawer-link <?php echo (isset($paginaActual) && $paginaActual === 'contacto') ? 'active' : ''; ?>">
                <span class="menu-drawer-link__icon"></span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Contacto</span>
                    <span class="menu-drawer-link__subtitle">Envianos un mensaje</span>
                </span>
            </a>
        </li>
        <li>
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <a href="logout.php" class="menu-drawer-link">
                    <span class="menu-drawer-link__icon"></span>
                    <span class="menu-drawer-link__text">
                        <span class="menu-drawer-link__title">Cerrar Sesión</span>
                        <span class="menu-drawer-link__subtitle">Salir de la cuenta</span>
                    </span>
                </a>
            <?php else: ?>
                <a href="login.php" class="menu-drawer-link <?php echo (isset($paginaActual) && $paginaActual === 'usuarios') ? 'active' : ''; ?>">
                    <span class="menu-drawer-link__icon"></span>
                    <span class="menu-drawer-link__text">
                        <span class="menu-drawer-link__title">Acceso Usuarios</span>
                        <span class="menu-drawer-link__subtitle">Iniciar sesión</span>
                    </span>
                </a>
            <?php endif; ?>
        </li>
    </ul>

    <div class="menu-drawer-footer">
        <p>Versión 1.0.0</p>
        <p>&copy; <?php echo date("Y"); ?> Asociación Mexicana de Diabetes en Jalisco A.C.</p>
    </div>
</nav>
