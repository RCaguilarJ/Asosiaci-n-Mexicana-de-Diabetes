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