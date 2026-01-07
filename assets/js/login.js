document.addEventListener('DOMContentLoaded', function() {
    const formLogin = document.getElementById('form-login');
    const formRegistro = document.getElementById('form-registro');
    const btnMostrarRegistro = document.querySelector('.btn-mostrar-registro');
    const btnMostrarLogin = document.querySelector('.btn-mostrar-login');
    const togglePasswordBtns = document.querySelectorAll('.toggle-password-btn');

    // --- FUNCIONES ---

    function mostrarRegistro() {
        if (formLogin) formLogin.style.display = 'none';
        if (formRegistro) formRegistro.style.display = 'block';
        window.scrollTo(0, 0);
    }

    function mostrarLogin() {
        if (formRegistro) formRegistro.style.display = 'none';
        if (formLogin) formLogin.style.display = 'block';
        window.scrollTo(0, 0);
    }

    function togglePassword(btn) {
        const passwordWrapper = btn.closest('.password-wrapper');
        if (!passwordWrapper) return;

        const passwordInput = passwordWrapper.querySelector('.password-input');
        if (!passwordInput) return;

        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        btn.style.opacity = isPassword ? '1' : '0.5';
    }

    // --- ASIGNACIÓN DE EVENTOS ---

    if (btnMostrarRegistro) {
        btnMostrarRegistro.addEventListener('click', mostrarRegistro);
    }

    if (btnMostrarLogin) {
        btnMostrarLogin.addEventListener('click', mostrarLogin);
    }

    togglePasswordBtns.forEach(btn => {
        btn.addEventListener('click', function(event) {
            event.preventDefault(); // Evitar que el botón envíe el formulario
            togglePassword(this);
        });
    });
});
