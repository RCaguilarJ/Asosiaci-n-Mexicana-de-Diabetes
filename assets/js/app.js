/* ====================================
   LOGICA DE PANTALLA DE ESCRITORIO (REDIRECCIÓN)
   ==================================== */
(function() {
    // Configuración
    const ANCHO_MINIMO_DESKTOP = 1025; 
    const URL_DESTINO = 'https://diabetesjalisco.org/';
    let alreadyRedirected = false; 

    function gestionarRedireccionDesktop() {
        // 1. VERIFICACIÓN DE SEGURIDAD PARA DESARROLLADORES
        // Si estamos en localhost o 127.0.0.1, NO hacemos nada.
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            return; // Salimos de la función sin redirigir
        }

        // 2. Lógica normal para producción
        if (window.innerWidth >= ANCHO_MINIMO_DESKTOP && !alreadyRedirected) {
            alreadyRedirected = true; 
            window.location.href = URL_DESTINO; 
        }
    }

    // Comprobar al cargar y redimensionar
    window.addEventListener('load', gestionarRedireccionDesktop);
    window.addEventListener('resize', gestionarRedireccionDesktop);
    document.addEventListener('DOMContentLoaded', gestionarRedireccionDesktop);
})();

/* ====================================
   FUNCIONALIDAD DEL MENÚ DRAWER
   ==================================== */
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-btn');
    const menuClose = document.querySelector('.menu-close-btn');
    const menuOverlay = document.querySelector('.menu-overlay');
    const offcanvasMenu = document.querySelector('.offcanvas-menu');
    const body = document.body;

    // Función para abrir el menú
    function abrirMenu() {
        body.classList.add('menu-open');
        offcanvasMenu.classList.add('visible');
        menuOverlay.classList.add('visible');
        
        // Prevenir scroll del body cuando el menú está abierto
        body.style.overflow = 'hidden';
    }

    // Función para cerrar el menú
    function cerrarMenu() {
        body.classList.remove('menu-open');
        offcanvasMenu.classList.remove('visible');
        menuOverlay.classList.remove('visible');
        
        // Restaurar scroll del body
        body.style.overflow = '';
    }

    // Event listeners
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            abrirMenu();
        });
    }

    if (menuClose) {
        menuClose.addEventListener('click', function(e) {
            e.preventDefault();
            cerrarMenu();
        });
    }

    if (menuOverlay) {
        menuOverlay.addEventListener('click', function() {
            cerrarMenu();
        });
    }

    // Cerrar menú con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarMenu();
        }
    });

    // Cerrar menú al hacer clic en un enlace (opcional en móviles)
    const menuLinks = document.querySelectorAll('.menu-drawer-link');
    menuLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Solo cerrar si no es el enlace activo
            if (!this.classList.contains('active')) {
                setTimeout(cerrarMenu, 100); // Pequeño delay para mejor UX
            }
        });
    });
});

/* ====================================
   FUNCIONALIDAD ADICIONAL
   ==================================== */

// Smooth scrolling para enlaces internos
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href !== '#' && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
});

// Función para mostrar notificaciones toast (si se necesita)
function mostrarToast(mensaje, tipo = 'info') {
    // Crear elemento toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-message">${mensaje}</span>
            <button class="toast-close">&times;</button>
        </div>
    `;
    
    // Agregar al DOM
    document.body.appendChild(toast);
    
    // Mostrar
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Auto-ocultar después de 3 segundos
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 3000);
    
    // Permitir cerrar manualmente
    toast.querySelector('.toast-close').addEventListener('click', () => {
        toast.classList.remove('show');
        setTimeout(() => document.body.removeChild(toast), 300);
    });
}