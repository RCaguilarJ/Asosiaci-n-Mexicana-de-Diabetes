document.addEventListener('DOMContentLoaded', function() {
    
=
    const formLogin = document.getElementById('form-login');
    const formRegistro = document.getElementById('form-registro');
    
    // Botones (enlaces) para cambiar de formulario
    // Nota: Usamos getElementById porque agregamos ID="link-ir-..." en el HTML
    const btnIrRegistro = document.getElementById('link-ir-registro'); 
    const btnIrLogin = document.getElementById('link-ir-login');
    
    // Botones de mostrar contraseña (clase)
    const toggleBtns = document.querySelectorAll('.toggle-password-btn');




    // Acción: Clic en "¿No tienes cuenta? Regístrate aquí"
    if (btnIrRegistro) {
        btnIrRegistro.addEventListener('click', function(e) {
            e.preventDefault(); // Evita que la página salte o recargue
            
            if(formLogin) formLogin.style.display = 'none';      // Oculta Login
            if(formRegistro) formRegistro.style.display = 'block'; // Muestra Registro
            
            window.scrollTo(0, 0); // Sube al inicio de la página suavemente
        });
    }

    // Acción: Clic en "¿Ya tienes cuenta? Inicia sesión aquí"
    if (btnIrLogin) {
        btnIrLogin.addEventListener('click', function(e) {
            e.preventDefault();
            
            if(formRegistro) formRegistro.style.display = 'none'; // Oculta Registro
            if(formLogin) formLogin.style.display = 'block';      // Muestra Login
            
            window.scrollTo(0, 0);
        });
    }



    
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault(); // Evita que el botón envíe el formulario por accidente

            // Buscamos el contenedor padre (password-wrapper)
            const wrapper = this.closest('.password-wrapper');
            if (!wrapper) return;

            // Buscamos el input dentro de ese contenedor
            const input = wrapper.querySelector('.password-input');
            if (!input) return;

            // Alternamos el tipo de input
            if (input.type === 'password') {
                input.type = 'text';        // Mostrar texto
                this.style.opacity = '1';   // Poner el ícono más oscuro (activo)
            } else {
                input.type = 'password';    // Ocultar texto
                this.style.opacity = '0.5'; // Poner el ícono transparente (inactivo)
            }
        });
    });

});