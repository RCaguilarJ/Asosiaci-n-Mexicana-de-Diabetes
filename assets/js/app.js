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