/* ====================================
   LOGICA DE PANTALLA DE ESCRITORIO (REDIRECCIÓN)
   ==================================== */
(function() {
    // Configuración
    const ANCHO_MINIMO_DESKTOP = 1025; 
    const URL_DESTINO = 'https://diabetesjalisco.org/';
    let alreadyRedirected = false; // Para evitar bucles de redirección

    function gestionarRedireccionDesktop() {
        // Redirigir solo si estamos en una pantalla de escritorio y no lo hemos hecho ya
        if (window.innerWidth >= ANCHO_MINIMO_DESKTOP && !alreadyRedirected) {
            alreadyRedirected = true; // Marcar como redirigido
            window.location.href = URL_DESTINO; // Redirigir a la web de escritorio
        }
    }

    // Comprobar al cargar la página
    window.addEventListener('load', gestionarRedireccionDesktop);
    // Comprobar si el usuario redimensiona la ventana
    window.addEventListener('resize', gestionarRedireccionDesktop);
    // Adicionalmente, se puede ejecutar en DOMContentLoaded por si la carga de otros recursos es lenta
    document.addEventListener('DOMContentLoaded', gestionarRedireccionDesktop);
})();


/* ====================================
   FUNCIONALIDAD DEL MENÚ DESLIZANTE (OFFCANVAS)
   ==================================== */
document.addEventListener('DOMContentLoaded', function() {
    
    const menuBtn = document.querySelector('.menu-btn');
    const menuDrawer = document.querySelector('.offcanvas-menu');
    const menuOverlay = document.querySelector('.menu-overlay');
    const menuCloseBtn = document.querySelector('.menu-close-btn');

    function abrirMenu() {
        if (!menuDrawer || !menuOverlay) return;
        menuDrawer.classList.add('visible');
        menuOverlay.classList.add('visible');
        menuBtn.setAttribute('aria-expanded', 'true');
        document.body.classList.add('body-noscroll');
    }

    function cerrarMenu() {
        if (!menuDrawer || !menuOverlay) return;
        menuDrawer.classList.remove('visible');
        menuOverlay.classList.remove('visible');
        menuBtn.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('body-noscroll');
    }

    if (menuBtn) menuBtn.addEventListener('click', abrirMenu);
    if (menuCloseBtn) menuCloseBtn.addEventListener('click', cerrarMenu);
    if (menuOverlay) menuOverlay.addEventListener('click', cerrarMenu);
});


/* ====================================
   FUNCIONALIDAD CALCULADORA DE DIABETES
   ==================================== */
document.addEventListener('DOMContentLoaded', function() {

    const formCalculadora = document.querySelector('#form-calculadora');
    const btnCalcular = document.querySelector('#btn-calcular');
    const btnGuardar = document.querySelector('.btn-guardar');
    
    let resultadoDosis = 0;
    
    if (formCalculadora && btnCalcular) {
        
        const camposRequeridos = formCalculadora.querySelectorAll('[required]');

        function verificarFormulario() {
            let todosLlenos = true;
            camposRequeridos.forEach(campo => {
                if (campo.value === '') todosLlenos = false;
            });
            btnCalcular.disabled = !todosLlenos;
        }

        camposRequeridos.forEach(campo => {
            campo.addEventListener('input', verificarFormulario);
            campo.addEventListener('change', verificarFormulario);
        });

        formCalculadora.addEventListener('submit', function(e) {
            e.preventDefault(); 
            
            const glucosa = parseFloat(document.querySelector('#glucosa').value);
            const carbohidratos = parseFloat(document.querySelector('#carbohidratos').value);
            const ratio = parseFloat(document.querySelector('#ratio').value);
            
            resultadoDosis = carbohidratos / ratio;
            
            let interpretacion = "Nivel Normal";
            let mensaje = "¡Sigue así!";
            
            if (glucosa < 70) {
                interpretacion = "Hipoglucemia";
                mensaje = "Atención: Tu nivel es bajo. Consume carbohidratos rápidos.";
            } else if (glucosa > 180) {
                interpretacion = "Hiperglucemia";
                mensaje = "Atención: Tu nivel es alto. Revisa tu corrección.";
            }

            const seccionResultados = document.querySelector('#seccion-resultados');
            if (seccionResultados) {
                document.querySelector('#resultado-interpretacion').textContent = interpretacion;
                document.querySelector('#resultado-dosis').textContent = resultadoDosis.toFixed(1) + ' unidades';
                document.querySelector('#resultado-mensaje').textContent = mensaje;

                seccionResultados.classList.remove('oculto');
                seccionResultados.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });

        if (btnGuardar) {
            btnGuardar.addEventListener('click', function() {
                const datos = {
                    glucosa: document.querySelector('#glucosa').value,
                    momento: document.querySelector('#momento').value,
                    dosis: resultadoDosis.toFixed(1)
                };

                const textoOriginal = btnGuardar.innerHTML;
                btnGuardar.innerHTML = '<span>Guardando...</span>';
                btnGuardar.disabled = true;

                fetch('guardar_calculo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("¡Registro guardado con éxito!");
                        window.location.href = 'index.php';
                    } else {
                        alert("Error: " + (data.message || "No se pudo guardar"));
                        btnGuardar.innerHTML = textoOriginal;
                        btnGuardar.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Error de conexión");
                    btnGuardar.innerHTML = textoOriginal;
                    btnGuardar.disabled = false;
                });
            });
        }
    }
});

/* ====================================
   FUNCIONALIDAD LOGIN Y ACCIONES RÁPIDAS
   ==================================== */
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Password
    const toggleBtns = document.querySelectorAll('.toggle-password-btn');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Lógica manejada inline en PHP/HTML, pero listeners listos por si acaso
        });
    });

    // Acciones Rápidas
    const quickActions = document.querySelectorAll('.action-button[data-action]');
    const actionMap = {
        calculadora: 'calculadora.php',
        eventos: 'eventos.php',
        blog: 'blog.php',
        contacto: 'contacto.php'
    };

    quickActions.forEach(actionBtn => {
        actionBtn.addEventListener('click', function(e) {
            const action = this.dataset.action;
            const destino = actionMap[action];
            if (!destino) return;

            e.preventDefault();
            if (destino.startsWith('#')) {
                const objetivo = document.querySelector(destino);
                if (objetivo) objetivo.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                window.location.href = destino;
            }
        });
    });
});

/* ====================================
   FUNCIONALIDAD AGENDAR CITA
   ==================================== */
document.addEventListener('DOMContentLoaded', function() {
    const formCitas = document.querySelector('#form-citas');

    if (formCitas) {
        const btnAgendar = formCitas.querySelector('button[type="submit"]');
        const textoOriginalBtn = btnAgendar.innerHTML;

        formCitas.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const datosCita = {
                nombre: document.querySelector('#nombre').value,
                email: document.querySelector('#email').value,
                telefono: document.querySelector('#telefono').value,
                especialidad: document.querySelector('#especialidad').value,
                fecha: document.querySelector('#fecha').value,
                hora: document.querySelector('#hora').value,
                notas: document.querySelector('#notas').value
            };

            if(!datosCita.nombre || !datosCita.telefono || !datosCita.fecha || !datosCita.hora) {
                alert("Por favor completa los campos obligatorios.");
                return;
            }

            btnAgendar.innerHTML = '<span>Guardando...</span>';
            btnAgendar.disabled = true;

            fetch('guardar_cita.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datosCita)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("¡Éxito! " + data.message);
                    formCitas.reset();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Ocurrió un error de conexión.");
            })
            .finally(() => {
                btnAgendar.innerHTML = textoOriginalBtn;
                btnAgendar.disabled = false;
            });
        });
    }
});

/* ====================================
   SCROLL-TO-TOP BUTTON
   ==================================== */
document.addEventListener('DOMContentLoaded', function() {
    const scrollToTopBtn = document.querySelector('.scroll-to-top-btn');

    if (scrollToTopBtn) {
        // Show or hide button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) { // Show after 300px of scroll
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        });

        // Scroll to top on click
        scrollToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});