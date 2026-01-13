<?php
    if (!isset($paginaActual)) { $paginaActual = ''; }
?>
<div class="menu-overlay"></div>

<nav class="offcanvas-menu">
    <div class="menu-drawer-header">
        <div class="menu-drawer-logo">
            <img src="/asosiacionMexicanaDeDiabetes/assets/img/logo.png" alt="Logo AMD Jalisco">
            <span>
                <strong>AMD Jalisco</strong>
                <small>Menú Principal</small>
            </span>
        </div>
        <button class="menu-close-btn" aria-label="Cerrar menú">X</button>
    </div>

    <ul class="menu-drawer-nav">
        <li>
            <a href="/asosiacionMexicanaDeDiabetes/views/index.php" class="menu-drawer-link <?php echo ($paginaActual == 'inicio') ? 'active' : ''; ?>">
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9,22 9,12 15,12 15,22"/>
                    </svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Inicio</span>
                    <span class="menu-drawer-link__subtitle">Panel principal</span>
                </span>
            </a>
        </li>
        <li>
            <a href="/asosiacionMexicanaDeDiabetes/views/calculadora.php" class="menu-drawer-link <?php echo ($paginaActual == 'calculadora') ? 'active' : ''; ?>">
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="16" height="20" x="4" y="2" rx="2"/>
                        <line x1="8" x2="16" y1="6" y2="6"/>
                        <line x1="16" x2="16" y1="14" y2="18"/>
                        <path d="m16 10 .01.01"/>
                        <path d="m12 10 .01.01"/>
                        <path d="m8 10 .01.01"/>
                        <path d="m12 14 .01.01"/>
                        <path d="m8 14 .01.01"/>
                        <path d="m12 18 .01.01"/>
                        <path d="m8 18 .01.01"/>
                    </svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Calculadora de Diabetes</span>
                    <span class="menu-drawer-link__subtitle">Calcula dosis de insulina</span>
                </span>
            </a>
        </li>
        <li>
            <a href="/asosiacionMexicanaDeDiabetes/views/eventos.php" class="menu-drawer-link <?php echo ($paginaActual == 'eventos') ? 'active' : ''; ?>">
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                        <line x1="16" x2="16" y1="2" y2="6"/>
                        <line x1="8" x2="8" y1="2" y2="6"/>
                        <line x1="3" x2="21" y1="10" y2="10"/>
                    </svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Eventos</span>
                    <span class="menu-drawer-link__subtitle">Próximas actividades</span>
                </span>
            </a>
        </li>
        <li>
            <a href="/asosiacionMexicanaDeDiabetes/views/blog.php" class="menu-drawer-link <?php echo ($paginaActual == 'blog') ? 'active' : ''; ?>">
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Blog</span>
                    <span class="menu-drawer-link__subtitle">Educación y consejos</span>
                </span>
            </a>
        </li>
        <li>
            <a href="/asosiacionMexicanaDeDiabetes/views/galeria.php" class="menu-drawer-link <?php echo ($paginaActual == 'galeria') ? 'active' : ''; ?>">
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="23 7 16 12 23 17 23 7"/>
                        <rect width="14" height="14" x="1" y="5" rx="2" ry="2"/>
                    </svg>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Educadores en Diabetes</span>
                    <span class="menu-drawer-link__subtitle">Especialistas certificados</span>
                </span>
            </a>
        </li>
        <li>
            <a href="/asosiacionMexicanaDeDiabetes/views/citas.php" class="menu-drawer-link <?php echo ($paginaActual == 'citas') ? 'active' : ''; ?>">
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12,6 12,12 16,14"/>
                    </svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Agendar Cita</span>
                    <span class="menu-drawer-link__subtitle">Gestiona consultas</span>
                </span>
            </a>
        </li>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <li>
                <a href="/asosiacionMexicanaDeDiabetes/actions/logout.php" class="menu-drawer-link">
                    <span class="menu-drawer-link__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16,17 21,12 16,7"/>
                            <line x1="21" x2="9" y1="12" y2="12"/>
                        </svg>
                    </span>
                    <span class="menu-drawer-link__text">
                        <span class="menu-drawer-link__title">Cerrar Sesión</span>
                        <span class="menu-drawer-link__subtitle">Finalizar sesión actual</span>
                    </span>
                </a>
            </li>
        <?php else: ?>
            <li>
                <a href="/asosiacionMexicanaDeDiabetes/views/login.php" class="menu-drawer-link <?php echo ($paginaActual == 'login') ? 'active' : ''; ?>">
                    <span class="menu-drawer-link__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <span class="menu-drawer-link__text">
                        <span class="menu-drawer-link__title">Acceso Usuarios</span>
                        <span class="menu-drawer-link__subtitle">Iniciar sesión</span>
                    </span>
                </a>
            </li>
        <?php endif; ?>
        <li>
            <a href="/asosiacionMexicanaDeDiabetes/views/contacto.php" class="menu-drawer-link <?php echo ($paginaActual == 'contacto') ? 'active' : ''; ?>">
                <span class="menu-drawer-link__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                </span>
                <span class="menu-drawer-link__text">
                    <span class="menu-drawer-link__title">Contacto</span>
                    <span class="menu-drawer-link__subtitle">Información de contacto</span>
                </span>
            </a>
        </li>
    </ul>

    <div class="menu-drawer-footer">
        <p>© 2026 Asociación Mexicana de Diabetes Jalisco<br>
        <small>Versión móvil 1.0</small></p>
    </div>
</nav>