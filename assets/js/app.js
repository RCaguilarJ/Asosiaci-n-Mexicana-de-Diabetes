/* ====================================
   LOGICA DE PANTALLA DE ESCRITORIO (REDIRECCIÓN)
   ==================================== */
(function() {
   
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

/* ====================================
   FUNCIONALIDAD DE LA CALCULADORA DE DIABETES
   ==================================== */
document.addEventListener('DOMContentLoaded', function() {
    const formCalculadora = document.getElementById('form-calculadora');
    const btnCalcular = document.getElementById('btn-calcular');
    const seccionResultados = document.getElementById('seccion-resultados');

    if (!formCalculadora) return; // Si no estamos en la página de calculadora, salir

    // Campos del formulario (nombres reales del HTML)
    const inputs = {
        glucosa: document.getElementById('glucosa'),
        momento: document.getElementById('momento'),
        carbohidratos: document.getElementById('carbohidratos'),
        ratio: document.getElementById('ratio')
    };

    // Validar campos y habilitar/deshabilitar el botón
    function validarCampos() {
        const todosLlenos = Object.values(inputs).every(input => 
            input && input.value.trim() !== ''
        );
        
        btnCalcular.disabled = !todosLlenos;
    }

    // Añadir event listeners a todos los campos
    Object.values(inputs).forEach(input => {
        if (input) {
            input.addEventListener('input', validarCampos);
            input.addEventListener('change', validarCampos);
        }
    });

    // Manejar envío del formulario
    formCalculadora.addEventListener('submit', function(e) {
        e.preventDefault();
        calcularDosis();
    });

    function calcularDosis() {
        const datos = {
            glucosa: parseFloat(inputs.glucosa.value),
            momento: inputs.momento.value,
            carbohidratos: parseFloat(inputs.carbohidratos.value),
            ratio: parseInt(inputs.ratio.value)
        };

        // Definir rangos objetivo según momento
        const rangosObjetivo = {
            'ayunas': { min: 80, max: 130 },
            'antes_comer': { min: 80, max: 130 },
            'despues_comer': { min: 80, max: 180 },
            'antes_dormir': { min: 100, max: 140 }
        };

        const rango = rangosObjetivo[datos.momento] || rangosObjetivo.ayunas;
        
        // Calcular dosis de corrección (simplificado)
        let dosisCorreccion = 0;
        if (datos.glucosa > rango.max) {
            // Usar factor de sensibilidad básico de 50 mg/dL por unidad
            dosisCorreccion = Math.ceil((datos.glucosa - rango.max) / 50);
        }
        
        // Calcular dosis para carbohidratos
        const dosisCarbs = datos.carbohidratos / datos.ratio;
        
        // Dosis total
        const dosisTotal = dosisCorreccion + dosisCarbs;

        // Mostrar resultados
        mostrarResultados({
            dosisCorreccion: dosisCorreccion.toFixed(1),
            dosisCarbs: dosisCarbs.toFixed(1),
            dosisTotal: dosisTotal.toFixed(1),
            glucosa: datos.glucosa,
            momento: datos.momento,
            rango: rango
        });
    }

    function mostrarResultados(resultados) {
        // Actualizar los elementos de resultado
        document.getElementById('resultado-imc').textContent = 'N/A'; // No calculamos IMC en esta versión
        document.getElementById('resultado-dosis-correccion').textContent = `${resultados.dosisCorreccion} unidades`;
        document.getElementById('resultado-dosis-carbs').textContent = `${resultados.dosisCarbs} unidades`;
        document.getElementById('resultado-dosis-total').textContent = `${resultados.dosisTotal} unidades`;

        // Determinar estado de glucosa según momento y rango
        let estadoGlucosa = '';
        if (resultados.glucosa < 70) {
            estadoGlucosa = '⚠️ Glucosa baja - Consulte a su médico inmediatamente';
        } else if (resultados.glucosa < resultados.rango.min) {
            estadoGlucosa = '⬇️ Glucosa por debajo del rango objetivo';
        } else if (resultados.glucosa > resultados.rango.max) {
            estadoGlucosa = '⬆️ Glucosa por encima del rango objetivo - Se requiere corrección';
        } else {
            estadoGlucosa = '✅ Glucosa en rango aceptable para ' + getMomentoTexto(resultados.momento);
        }

        document.getElementById('estado-glucosa').textContent = estadoGlucosa;

        // Mostrar la sección de resultados
        seccionResultados.classList.remove('oculto');
        seccionResultados.scrollIntoView({ behavior: 'smooth' });
    }

    function getMomentoTexto(momento) {
        const momentos = {
            'ayunas': 'ayunas',
            'antes_comer': 'antes de comer',
            'despues_comer': 'después de comer',
            'antes_dormir': 'antes de dormir'
        };
        return momentos[momento] || momento;
    }

    // Funcionalidad del botón guardar
    const btnGuardar = document.querySelector('.btn-guardar');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', function() {
            // Validar que se hayan calculado resultados
            const dosisTotal = document.getElementById('resultado-dosis-total').textContent;
            if (!dosisTotal || dosisTotal === '-- unidades') {
                mostrarToast('Primero calcula los resultados antes de guardar', 'error');
                return;
            }

            // Obtener datos actuales
            const formData = new FormData();
            formData.append('glucosa', inputs.glucosa.value);
            formData.append('momento', inputs.momento.value);
            formData.append('carbohidratos', inputs.carbohidratos.value);
            formData.append('ratio', inputs.ratio.value);
            formData.append('dosis_correccion', document.getElementById('resultado-dosis-correccion').textContent.replace(' unidades', ''));
            formData.append('dosis_carbohidratos', document.getElementById('resultado-dosis-carbs').textContent.replace(' unidades', ''));
            formData.append('dosis_total', dosisTotal.replace(' unidades', ''));
            
            // Deshabilitar botón mientras se procesa
            const btnOriginalText = btnGuardar.innerHTML;
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2v4M12 18v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M2 12h4M18 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg><span>Guardando...</span>';
            
            // Enviar datos al servidor
            fetch('../actions/guardar_calculo.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    mostrarToast(data.mensaje || 'Cálculo guardado exitosamente', 'success');
                    
                    // Opcional: limpiar formulario después de guardar
                    setTimeout(() => {
                        formCalculadora.reset();
                        seccionResultados.classList.add('oculto');
                        btnCalcular.disabled = true;
                    }, 2000);
                } else {
                    throw new Error(data.error || 'Error desconocido');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarToast('Error al guardar el cálculo: ' + error.message, 'error');
            })
            .finally(() => {
                // Reestablecer botón
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = btnOriginalText;
            });
        });
    }
});

/* ====================================
   FUNCIONALIDAD DEL BLOG (FILTROS)
   ==================================== */
document.addEventListener('DOMContentLoaded', function() {
    const filtrosCategorias = document.querySelectorAll('.cat-btn');
    
    filtrosCategorias.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remover clase activa de todos
            filtrosCategorias.forEach(b => b.classList.remove('active'));
            
            // Añadir clase activa al clickeado
            this.classList.add('active');
            
            // Aquí se puede añadir lógica para filtrar artículos
            // Por ahora solo manejamos la UI
        });
    });
});

/* ====================================
   FUNCIONALIDAD DEL FORMULARIO DE CITAS
   ==================================== */
document.addEventListener('DOMContentLoaded', function() {
    const formCitas = document.getElementById('form-citas');
    const inputFecha = document.getElementById('fecha');
    
    if (!formCitas) return; // Si no estamos en la página de citas, salir

    // Establecer fecha mínima como hoy
    if (inputFecha) {
        const hoy = new Date();
        hoy.setDate(hoy.getDate() + 1); // Permitir agendar desde mañana
        const fechaMinima = hoy.toISOString().split('T')[0];
        inputFecha.min = fechaMinima;
    }

    // Manejar envío del formulario
    formCitas.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btnSubmit = this.querySelector('button[type="submit"]');
        const textoBtnOriginal = btnSubmit.textContent;
        
        // Deshabilitar botón y mostrar loading
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Procesando...';

        // Enviar datos
        fetch('../actions/guardar_cita.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                mostrarToast(data.error, 'error');
            } else {
                // Mostrar mensaje de éxito
                mostrarToast(data.mensaje || 'Cita agendada exitosamente', 'success');
                
                // Limpiar formulario
                formCitas.reset();
                
                // Recargar página después de 2 segundos
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarToast('Error al agendar la cita. Inténtelo de nuevo.', 'error');
        })
        .finally(() => {
            // Reestablecer botón
            btnSubmit.disabled = false;
            btnSubmit.textContent = textoBtnOriginal;
        });
    });
});

/* ====================================
   FUNCIONALIDAD PARA GALERÍA DE VIDEOS  
   ==================================== */
document.addEventListener('DOMContentLoaded', function() {
    const videoCards = document.querySelectorAll('.video-card');
    
    videoCards.forEach(card => {
        const playBtn = card.querySelector('.play-overlay');
        if (playBtn) {
            playBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // Aquí se puede añadir lógica para abrir modal de video
                // o navegar a la página del video
                const videoTitle = card.querySelector('h3').textContent;
                mostrarToast(`Reproduciendo: ${videoTitle}`, 'info');
            });
        }
    });
});