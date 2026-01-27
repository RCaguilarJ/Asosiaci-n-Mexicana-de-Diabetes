document.addEventListener('DOMContentLoaded', function() {
    
    // Elementos del DOM
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
            
            // Limpiar formularios
            if(formLogin) formLogin.reset();
            if(formRegistro) formRegistro.reset();
            
            // Scroll suave
            if(formRegistro) {
                formRegistro.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    // Acción: Clic en "¿Ya tienes cuenta? Inicia sesión aquí"
    if (btnIrLogin) {
        btnIrLogin.addEventListener('click', function(e) {
            e.preventDefault();
            
            if(formRegistro) formRegistro.style.display = 'none'; // Oculta Registro
            if(formLogin) formLogin.style.display = 'block';      // Muestra Login
            
            // Limpiar formularios
            if(formLogin) formLogin.reset();
            if(formRegistro) formRegistro.reset();
            
            // Scroll suave
            if(formLogin) {
                formLogin.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    // Validación en tiempo real para el formulario de registro
    if (formRegistro) {
        const nombreInput = formRegistro.querySelector('input[name="nombre"]');
        const emailInput = formRegistro.querySelector('input[name="email"]');
        const passwordInput = formRegistro.querySelector('input[name="password"]');

        // Validación del nombre
        if (nombreInput) {
            nombreInput.addEventListener('input', function() {
                const nombre = this.value.trim();
                if (nombre.length < 2) {
                    this.style.borderColor = '#dc3545';
                    mostrarMensajeValidacion(this, 'El nombre debe tener al menos 2 caracteres');
                } else {
                    this.style.borderColor = '#28a745';
                    ocultarMensajeValidacion(this);
                }
            });
        }

        // Validación del email
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                const email = this.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    this.style.borderColor = '#dc3545';
                    mostrarMensajeValidacion(this, 'Por favor ingresa un correo válido');
                } else {
                    this.style.borderColor = '#28a745';
                    ocultarMensajeValidacion(this);
                }
            });
        }

        // Validación de la contraseña
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                if (password.length < 6) {
                    this.style.borderColor = '#dc3545';
                    mostrarMensajeValidacion(this, 'La contraseña debe tener al menos 6 caracteres');
                } else {
                    this.style.borderColor = '#28a745';
                    ocultarMensajeValidacion(this);
                }
            });
        }
    }

    // Funciones auxiliares para validación
    function mostrarMensajeValidacion(input, mensaje) {
        // Eliminar mensaje anterior si existe
        ocultarMensajeValidacion(input);
        
        // Crear nuevo mensaje
        const mensajeDiv = document.createElement('div');
        mensajeDiv.className = 'validation-message';
        mensajeDiv.style.cssText = 'color: #dc3545; font-size: 12px; margin-top: 5px;';
        mensajeDiv.textContent = mensaje;
        
        // Insertar después del input
        input.parentNode.appendChild(mensajeDiv);
    }

    function ocultarMensajeValidacion(input) {
        const mensaje = input.parentNode.querySelector('.validation-message');
        if (mensaje) {
            mensaje.remove();
        }
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

    // Agregar efectos hover a los enlaces de cambio de formulario
    const linksFormulario = document.querySelectorAll('#link-ir-registro, #link-ir-login');
    linksFormulario.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#0066b2';
            this.style.color = '#FFFFFF';
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 12px rgba(0, 102, 178, 0.3)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'transparent';
            this.style.color = '#0066b2';
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });

});