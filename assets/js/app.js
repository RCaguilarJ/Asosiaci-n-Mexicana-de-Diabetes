/* ====================================
   FUNCIONALIDAD DEL MENÚ DESLIZANTE (OFFCANVAS)
   ==================================== */
document.addEventListener('DOMContentLoaded', function() {
    
    // Seleccionamos todos los elementos necesarios
    const menuBtn = document.querySelector('.menu-btn');
    const menuDrawer = document.querySelector('.offcanvas-menu');
    const menuOverlay = document.querySelector('.menu-overlay');
    const menuCloseBtn = document.querySelector('.menu-close-btn');

    // Función para ABRIR el menú
    function abrirMenu() {
        if (!menuDrawer || !menuOverlay) return;
        
        menuDrawer.classList.add('visible');
        menuOverlay.classList.add('visible');
        menuBtn.setAttribute('aria-expanded', 'true');
        document.body.classList.add('body-noscroll');
    }

    // Función para CERRAR el menú
    function cerrarMenu() {
        if (!menuDrawer || !menuOverlay) return;

        menuDrawer.classList.remove('visible');
        menuOverlay.classList.remove('visible');
        menuBtn.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('body-noscroll');
    }

    // --- Asignar Eventos ---
    if (menuBtn) {
        menuBtn.addEventListener('click', abrirMenu);
    }
    if (menuCloseBtn) {
        menuCloseBtn.addEventListener('click', cerrarMenu);
    }
    if (menuOverlay) {
        menuOverlay.addEventListener('click', cerrarMenu);
    }

});


/* ====================================
   FUNCIONALIDAD CALCULADORA DE DIABETES
   (Activar/Desactivar botón y mostrar resultados)
   ==================================== */
   
document.addEventListener('DOMContentLoaded', function() {

    const formCalculadora = document.querySelector('#form-calculadora');
    const btnCalcular = document.querySelector('#btn-calcular');
    
    if (formCalculadora && btnCalcular) {
        
        const camposRequeridos = formCalculadora.querySelectorAll('[required]');

        function verificarFormulario() {
            let todosLlenos = true;
            camposRequeridos.forEach(campo => {
                if (campo.value === '') {
                    todosLlenos = false;
                }
            });

            if (todosLlenos) {
                btnCalcular.disabled = false;
            } else {
                btnCalcular.disabled = true;
            }
        }

        camposRequeridos.forEach(campo => {
            campo.addEventListener('input', verificarFormulario);
            campo.addEventListener('change', verificarFormulario);
        });

        formCalculadora.addEventListener('submit', function(e) {
            e.preventDefault(); 
            
            // --- ¡AQUÍ IRÁ LA FÓRMULA QUE ME VAS A DAR! ---
            // Por ahora, usaremos los datos de la imagen como ejemplo.
            const dosisSugerida = 2.8;
            const interpretacion = "Normal postprandial";
            const mensaje = "Buen control después de comer.";
            // -------------------------------------------------

            const seccionResultados = document.querySelector('#seccion-resultados');
            
            if (seccionResultados) {
                document.querySelector('#resultado-interpretacion').textContent = interpretacion;
                document.querySelector('#resultado-dosis').textContent = dosisSugerida.toFixed(1) + ' unidades';
                document.querySelector('#resultado-mensaje').textContent = mensaje;

                seccionResultados.classList.remove('oculto');
                seccionResultados.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

});