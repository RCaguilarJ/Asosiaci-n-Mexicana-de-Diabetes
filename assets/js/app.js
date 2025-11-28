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
    // Seleccionamos el botón de guardar específicamente
    const btnGuardar = document.querySelector('.btn-guardar');
    
    // Variables para guardar los datos temporalmente antes de enviarlos
    let resultadoDosis = 0;
    
    if (formCalculadora && btnCalcular) {
        
        const camposRequeridos = formCalculadora.querySelectorAll('[required]');

        // --- 1. VALIDACIÓN (Activar botón Calcular) ---
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

        // --- 2. CÁLCULO (Al dar clic en Calcular) ---
        formCalculadora.addEventListener('submit', function(e) {
            e.preventDefault(); 
            
            // Obtener valores de los inputs
            const glucosa = parseFloat(document.querySelector('#glucosa').value);
            const carbohidratos = parseFloat(document.querySelector('#carbohidratos').value);
            const ratio = parseFloat(document.querySelector('#ratio').value);
            
            // --- FÓRMULA REAL ---
            // Dosis = Carbohidratos / Ratio
            // (Esta es una fórmula básica, puedes ajustarla si tienes la corrección por glucosa)
            resultadoDosis = carbohidratos / ratio;
            
            // Interpretación simple basada en glucosa
            let interpretacion = "Nivel Normal";
            let mensaje = "¡Sigue así!";
            
            if (glucosa < 70) {
                interpretacion = "Hipoglucemia";
                mensaje = "Atención: Tu nivel es bajo. Consume carbohidratos rápidos.";
            } else if (glucosa > 180) {
                interpretacion = "Hiperglucemia";
                mensaje = "Atención: Tu nivel es alto. Revisa tu corrección.";
            }

            // Mostrar resultados en pantalla
            const seccionResultados = document.querySelector('#seccion-resultados');
            if (seccionResultados) {
                document.querySelector('#resultado-interpretacion').textContent = interpretacion;
                // .toFixed(1) muestra solo 1 decimal (ej: 4.5)
                document.querySelector('#resultado-dosis').textContent = resultadoDosis.toFixed(1) + ' unidades';
                document.querySelector('#resultado-mensaje').textContent = mensaje;

                seccionResultados.classList.remove('oculto');
                seccionResultados.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });

        // --- 3. GUARDAR EN BASE DE DATOS (Al dar clic en Guardar) ---
        if (btnGuardar) {
            btnGuardar.addEventListener('click', function() {
                
                // Preparar los datos
                const datos = {
                    glucosa: document.querySelector('#glucosa').value,
                    momento: document.querySelector('#momento').value, // Ej: "ayunas"
                    dosis: resultadoDosis.toFixed(1)
                };

                // Cambiar texto del botón para feedback visual
                const textoOriginal = btnGuardar.innerHTML;
                btnGuardar.innerHTML = '<span>Guardando...</span>';
                btnGuardar.disabled = true;

                // Enviar a PHP usando Fetch (AJAX)
                fetch('guardar_calculo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(datos)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Éxito: Redirigir al Inicio para ver el dashboard actualizado
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
   FUNCIONALIDAD LOGIN (Simulación)
   ==================================== */
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Toggle Password (Ver/Ocultar Contraseña)
    const togglePassword = document.querySelector('#toggle-password');
    const passwordInput = document.querySelector('#login-password');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            // Alternar tipo de input
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Cambiar color del ícono para indicar estado
            this.style.color = type === 'text' ? '#007bff' : '#6c757d';
        });
    }

    // 2. Lógica de Registro y Login Simulado
    const loginForm = document.querySelector('#login-form');
    const btnCrearCuenta = document.querySelector('#btn-crear-cuenta');
    const emailInput = document.querySelector('#login-email');

    if (loginForm && btnCrearCuenta) {

        // --- CREAR CUENTA (Simulado) ---
        btnCrearCuenta.addEventListener('click', function() {
            // Usamos prompt para simular un flujo rápido de registro
            const nuevoEmail = prompt("REGISTRO: Ingresa tu correo electrónico:");
            if (!nuevoEmail) return;

            const nuevaPass = prompt("REGISTRO: Crea una contraseña:");
            if (!nuevaPass) return;

            // Guardar en localStorage (Base de datos del navegador)
            localStorage.setItem('demoUserEmail', nuevoEmail);
            localStorage.setItem('demoUserPass', nuevaPass);

            alert("¡Cuenta creada con éxito!\n\nUsuario: " + nuevoEmail + "\nContraseña: " + nuevaPass + "\n\nAhora puedes iniciar sesión.");
            
            // Llenar el campo de email automáticamente
            if (emailInput) emailInput.value = nuevoEmail;
        });

        // --- INICIAR SESIÓN (Simulado) ---
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const inputEmail = emailInput.value;
            const inputPass = passwordInput.value;

            // Obtener datos guardados
            const storedEmail = localStorage.getItem('demoUserEmail');
            const storedPass = localStorage.getItem('demoUserPass');

            // Validar
            if (inputEmail === storedEmail && inputPass === storedPass) {
                alert("¡Bienvenido de nuevo! Has iniciado sesión correctamente.");
                // Redirigir al inicio (simulado)
                window.location.href = 'index.php'; 
            } else {
                alert("Error: Correo o contraseña incorrectos.\n(Asegúrate de haber creado una cuenta primero con el botón 'Crear Nueva Cuenta')");
            }
        });
    }
});

/* ====================================
   ACCIONES RÁPIDAS (Inicio)
   ==================================== */
document.addEventListener('DOMContentLoaded', function() {
    const quickActions = document.querySelectorAll('.action-button[data-action]');
    if (!quickActions.length) return;

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

            // Evitamos doble navegación si es enlace normal
            e.preventDefault();

            if (destino.startsWith('#')) {
                const objetivo = document.querySelector(destino);
                if (objetivo) {
                    objetivo.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } else {
                window.location.href = destino;
            }
        });
    });
});
/* ====================================
   FUNCIONALIDAD AGENDAR CITA (Conexión a BD)
   ==================================== */
document.addEventListener('DOMContentLoaded', function() {
    const formCitas = document.querySelector('#form-citas');

    if (formCitas) {
        const btnAgendar = formCitas.querySelector('button[type="submit"]');
        const textoOriginalBtn = btnAgendar.innerHTML;

        formCitas.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // 1. Recopilar datos del formulario
            const datosCita = {
                nombre: document.querySelector('#nombre').value,
                email: document.querySelector('#email').value,
                telefono: document.querySelector('#telefono').value,
                especialidad: document.querySelector('#especialidad').value,
                fecha: document.querySelector('#fecha').value,
                hora: document.querySelector('#hora').value,
                notas: document.querySelector('#notas').value
            };

            // Validar campos básicos (por si acaso el HTML falla)
            if(!datosCita.nombre || !datosCita.telefono || !datosCita.fecha || !datosCita.hora) {
                alert("Por favor completa los campos obligatorios.");
                return;
            }

            // 2. Feedback visual (Botón cargando)
            btnAgendar.innerHTML = '<span>Guardando...</span>';
            btnAgendar.disabled = true;

            // 3. Enviar datos al servidor
            fetch('guardar_cita.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datosCita)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("¡Éxito! " + data.message);
                    formCitas.reset(); // Limpiar el formulario
                    // Opcional: Recargar la página para ver la cita nueva (si implementamos la lista dinámica)
                    // window.location.reload(); 
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Ocurrió un error de conexión.");
            })
            .finally(() => {
                // Restaurar botón
                btnAgendar.innerHTML = textoOriginalBtn;
                btnAgendar.disabled = false;
            });
        });
    }
});