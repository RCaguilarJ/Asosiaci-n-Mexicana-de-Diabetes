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
        
        // Hacemos visibles el menú y el fondo
        menuDrawer.classList.add('visible');
        menuOverlay.classList.add('visible');
        
        // Actualizamos ARIA
        menuBtn.setAttribute('aria-expanded', 'true');
        
        // Bloqueamos el scroll del body
        document.body.classList.add('body-noscroll');
    }

    // Función para CERRAR el menú
    function cerrarMenu() {
        if (!menuDrawer || !menuOverlay) return;

        // Ocultamos el menú y el fondo
        menuDrawer.classList.remove('visible');
        menuOverlay.classList.remove('visible');

        // Actualizamos ARIA
        menuBtn.setAttribute('aria-expanded', 'false');

        // Desbloqueamos el scroll del body
        document.body.classList.remove('body-noscroll');
    }

    // --- Asignar Eventos ---
    
    // 1. Clic en el botón hamburguesa (ABRIR)
    if (menuBtn) {
        menuBtn.addEventListener('click', abrirMenu);
    }

    // 2. Clic en el botón 'X' (CERRAR)
    if (menuCloseBtn) {
        menuCloseBtn.addEventListener('click', cerrarMenu);
    }

    // 3. Clic en el fondo oscuro (CERRAR)
    if (menuOverlay) {
        menuOverlay.addEventListener('click', cerrarMenu);
    }

});


/* ====================================
   FUNCIONALIDAD CALCULADORA DE DIABETES
   (Activar/Desactivar botón y mostrar resultados)
   ==================================== */
   
document.addEventListener('DOMContentLoaded', function() {

    // 1. Selecciona el formulario y los campos
    const formCalculadora = document.querySelector('#form-calculadora');
    const btnCalcular = document.querySelector('#btn-calcular');
    
    // Solo ejecuta este código si estamos en la página de la calculadora
    if (formCalculadora && btnCalcular) {
        
        // Selecciona todos los campos REQUERIDOS
        const camposRequeridos = formCalculadora.querySelectorAll('[required]');

        // Función para verificar si todos los campos están llenos
        function verificarFormulario() {
            let todosLlenos = true;
            
            camposRequeridos.forEach(campo => {
                // Si un campo está vacío, el formulario no está completo
                if (campo.value === '') {
                    todosLlenos = false;
                }
            });

            // Habilita o deshabilita el botón basado en el resultado
            if (todosLlenos) {
                btnCalcular.disabled = false;
            } else {
                btnCalcular.disabled = true;
            }
        }

        // "Escucha" cada vez que el usuario escribe o cambia un campo
        camposRequeridos.forEach(campo => {
            campo.addEventListener('input', verificarFormulario);
            campo.addEventListener('change', verificarFormulario);
        });

        // Manejar el envío del formulario
        formCalculadora.addEventListener('submit', function(e) {
            // Previene que la página se recargue
            e.preventDefault(); 
            
            // --- ¡AQUÍ IRÁ LA FÓRMULA QUE ME VAS A DAR! ---
            // Por ahora, usaremos los datos de la imagen como ejemplo.
            const dosisSugerida = 2.8;
            const interpretacion = "Normal postprandial";
            const mensaje = "Buen control después de comer.";
            // -------------------------------------------------


            // 1. Selecciona la sección de resultados
            const seccionResultados = document.querySelector('#seccion-resultados');
            
            if (seccionResultados) {
                
                // 2. Llena los campos con los resultados de la fórmula
                document.querySelector('#resultado-interpretacion').textContent = interpretacion;
                document.querySelector('#resultado-dosis').textContent = dosisSugerida.toFixed(1) + ' unidades'; // .toFixed(1) para asegurar un decimal
                document.querySelector('#resultado-mensaje').textContent = mensaje;

                // 3. Le quita la clase 'oculto' para mostrarla
                seccionResultados.classList.remove('oculto');
                
                // 4. (Opcional) Mueve la pantalla para que el usuario vea los resultados
                seccionResultados.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

});