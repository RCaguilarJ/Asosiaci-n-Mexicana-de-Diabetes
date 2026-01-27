<?php
/**
 * Test específico del selector de especialistas
 * Verifica que TODOS los especialistas funcionen correctamente
 */

require_once 'includes/db.php';
require_once 'includes/api_sistema_gestion.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Completo - Selector de Especialistas</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            margin: 20px; 
            line-height: 1.6; 
            background: #f8f9fa;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; padding: 25px; margin: 20px 0; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        .form-group { margin-bottom: 20px; }
        .form-control { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #ddd; 
            border-radius: 8px; 
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-control:focus { border-color: #0066b2; outline: none; }
        .btn { 
            background: #0066b2; 
            color: white; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        .btn:hover { background: #004d85; }
        .test-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .test-card { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 8px; 
            border-left: 4px solid #0066b2;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-success { background: #d4edda; color: #155724; }
        .status-error { background: #f8d7da; color: #721c24; }
        .status-warning { background: #fff3cd; color: #856404; }
        
        .result-display {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-family: monospace;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🏥 Test Completo - Selector de Especialistas</h1>
            <p class="info">Esta página prueba que TODOS los especialistas del sistema funcionen correctamente en el selector.</p>
        </div>

        <!-- Formulario de prueba idéntico al real -->
        <div class="card">
            <h2>📋 Formulario de Prueba (Idéntico al Real)</h2>
            
            <form id="test-form">
                <div class="form-group">
                    <label for="especialidad">Especialidad</label>
                    <select id="especialidad" name="especialidad" class="form-control" required>
                        <option value="" disabled selected>Cargando especialidades...</option>
                    </select>
                    <div id="loading-especialidades" style="display: none; margin-top: 10px; color: #666;">
                        <span>🔄 Cargando especialistas disponibles...</span>
                    </div>
                </div>

                <div class="form-group" id="medico-group" style="display: none;">
                    <label for="medico_id">Especialista</label>
                    <select id="medico_id" name="medico_id" class="form-control" required>
                        <option value="" disabled selected>Selecciona un especialista</option>
                    </select>
                    <small style="color: #666; font-size: 14px;">Selecciona primero una especialidad para ver los especialistas disponibles</small>
                </div>

                <button type="button" id="test-all-btn" class="btn">
                    🧪 Probar Todos los Especialistas
                </button>
                
                <button type="button" id="test-current-btn" class="btn" style="background: #28a745;">
                    ✅ Probar Selección Actual
                </button>
            </form>
        </div>

        <!-- Resultados de las pruebas -->
        <div class="card">
            <h2>📊 Resultados de las Pruebas</h2>
            <div id="test-results"></div>
        </div>

        <!-- Log de actividad -->
        <div class="card">
            <h2>📝 Log de Actividad</h2>
            <div id="activity-log" class="result-display"></div>
        </div>
    </div>

    <script>
        let testData = {
            especialidades: [],
            currentTest: null,
            allTests: [],
            activityLog: []
        };

        document.addEventListener('DOMContentLoaded', function() {
            initializeTest();
            
            // Event listeners
            document.getElementById('test-all-btn').addEventListener('click', testAllEspecialistas);
            document.getElementById('test-current-btn').addEventListener('click', testCurrentSelection);
            document.getElementById('especialidad').addEventListener('change', handleEspecialidadChange);
        });

        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const logEntry = `[${timestamp}] ${message}`;
            testData.activityLog.push({timestamp, message, type});
            
            const logElement = document.getElementById('activity-log');
            logElement.textContent = testData.activityLog.map(entry => 
                `[${entry.timestamp}] ${entry.message}`
            ).join('\n');
            
            // Scroll to bottom
            logElement.scrollTop = logElement.scrollHeight;
            
            console.log(logEntry);
        }

        async function initializeTest() {
            log('🚀 Iniciando test del selector de especialistas');
            await cargarEspecialistas();
        }

        async function cargarEspecialistas() {
            const selectEspecialidad = document.getElementById('especialidad');
            const loadingSpinner = document.getElementById('loading-especialidades');
            
            try {
                log('📡 Cargando especialidades desde API...');
                loadingSpinner.style.display = 'block';
                
                const response = await fetch('/api/get_especialistas.php', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Error desconocido en API');
                }
                
                log(`✅ ${data.roles.length} especialidades cargadas correctamente`);
                
                // Limpiar opciones
                selectEspecialidad.innerHTML = '<option value="" disabled selected>Selecciona una especialidad</option>';
                
                // Agregar especialidades
                testData.especialidades = data.roles;
                data.roles.forEach(rol => {
                    const option = document.createElement('option');
                    option.value = rol.nombre_rol;
                    option.dataset.role = rol.role;
                    option.textContent = `${rol.nombre_rol} (${rol.especialistas.length} disponibles)`;
                    selectEspecialidad.appendChild(option);
                    
                    log(`   • ${rol.nombre_rol}: ${rol.especialistas.length} especialistas`);
                });
                
                if (data.fallback_local) {
                    log('⚠️ ADVERTENCIA: Usando datos locales (fallback)', 'warning');
                    updateTestResults('⚠️ Sistema usando fallback local - conexión con API limitada', 'warning');
                }
                
                log('📋 Especialidades disponibles para prueba:');
                testData.especialidades.forEach(esp => {
                    log(`   - ${esp.nombre_rol} (${esp.role}): ${esp.especialistas.length} especialistas`);
                });
                
            } catch (error) {
                log(`❌ Error cargando especialidades: ${error.message}`, 'error');
                
                // Fallback manual
                selectEspecialidad.innerHTML = `
                    <option value="" disabled selected>Error - usando fallback</option>
                    <option value="Medicina General">Medicina General (fallback)</option>
                    <option value="Nutrición">Nutrición (fallback)</option>
                    <option value="Endocrinología">Endocrinología (fallback)</option>
                    <option value="Podología">Podología (fallback)</option>
                    <option value="Psicología">Psicología (fallback)</option>
                `;
                
                updateTestResults(`❌ Error en API: ${error.message}`, 'error');
                
            } finally {
                loadingSpinner.style.display = 'none';
            }
        }

        async function handleEspecialidadChange() {
            const selectEspecialidad = document.getElementById('especialidad');
            const especialidad = selectEspecialidad.value;
            
            if (!especialidad) return;
            
            const selectedOption = selectEspecialidad.options[selectEspecialidad.selectedIndex];
            const role = selectedOption.dataset.role;
            
            log(`🔄 Usuario seleccionó: ${especialidad} (rol: ${role})`);
            
            await cargarMedicosPorEspecialidad(especialidad, role);
        }

        async function cargarMedicosPorEspecialidad(especialidad, role) {
            const selectMedico = document.getElementById('medico_id');
            const medicoGroup = document.getElementById('medico-group');
            
            try {
                log(`📡 Cargando médicos para ${especialidad}...`);
                
                medicoGroup.style.display = 'block';
                selectMedico.innerHTML = '<option value="" disabled selected>Cargando especialistas...</option>';
                
                const response = await fetch(`/api/get_especialistas.php?role=${encodeURIComponent(role)}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Error cargando médicos');
                }
                
                selectMedico.innerHTML = '<option value="" disabled selected>Selecciona un especialista</option>';
                
                if (data.roles.length > 0 && data.roles[0].especialistas.length > 0) {
                    const especialistas = data.roles[0].especialistas;
                    
                    log(`✅ ${especialistas.length} especialistas cargados para ${especialidad}`);
                    
                    especialistas.forEach(medico => {
                        const option = document.createElement('option');
                        option.value = medico.id;
                        option.dataset.username = medico.username;
                        option.dataset.email = medico.email;
                        option.textContent = medico.nombre;
                        if (!medico.disponible) {
                            option.disabled = true;
                            option.textContent += ' (No disponible)';
                        }
                        selectMedico.appendChild(option);
                        
                        log(`   • ID ${medico.id}: ${medico.nombre} ${!medico.disponible ? '(No disponible)' : ''}`);
                    });
                    
                } else {
                    selectMedico.innerHTML = '<option value="" disabled>No hay especialistas disponibles</option>';
                    log(`⚠️ No hay especialistas disponibles para ${especialidad}`, 'warning');
                }
                
            } catch (error) {
                log(`❌ Error cargando médicos para ${especialidad}: ${error.message}`, 'error');
                selectMedico.innerHTML = '<option value="" disabled>Error cargando especialistas</option>';
            }
        }

        async function testAllEspecialistas() {
            log('🧪 INICIANDO TEST COMPLETO DE TODOS LOS ESPECIALISTAS');
            
            const results = [];
            testData.allTests = [];
            
            for (let i = 0; i < testData.especialidades.length; i++) {
                const especialidad = testData.especialidades[i];
                log(`\n--- TEST ${i + 1}/${testData.especialidades.length}: ${especialidad.nombre_rol} ---`);
                
                const testResult = await testSingleEspecialidad(especialidad);
                results.push(testResult);
                testData.allTests.push(testResult);
                
                // Pequeña pausa entre tests
                await new Promise(resolve => setTimeout(resolve, 500));
            }
            
            log('\n🎯 RESUMEN DE TODOS LOS TESTS:');
            
            const summary = {
                total: results.length,
                passed: results.filter(r => r.success).length,
                failed: results.filter(r => !r.success).length,
                totalEspecialistas: results.reduce((sum, r) => sum + r.especialistasCount, 0)
            };
            
            log(`   ✅ Exitosos: ${summary.passed}/${summary.total}`);
            log(`   ❌ Fallidos: ${summary.failed}/${summary.total}`);
            log(`   👨‍⚕️ Total especialistas encontrados: ${summary.totalEspecialistas}`);
            
            updateTestResults(generateSummaryHTML(results), summary.failed === 0 ? 'success' : 'error');
        }

        async function testSingleEspecialidad(especialidad) {
            const testResult = {
                especialidad: especialidad.nombre_rol,
                role: especialidad.role,
                success: false,
                especialistasCount: 0,
                especialistas: [],
                error: null,
                timestamp: new Date().toISOString()
            };
            
            try {
                log(`🔍 Probando ${especialidad.nombre_rol} (${especialidad.role})...`);
                
                // Simular selección en el formulario
                const selectEspecialidad = document.getElementById('especialidad');
                selectEspecialidad.value = especialidad.nombre_rol;
                
                // Cargar médicos
                await cargarMedicosPorEspecialidad(especialidad.nombre_rol, especialidad.role);
                
                // Verificar que se cargaron médicos
                const selectMedico = document.getElementById('medico_id');
                const options = Array.from(selectMedico.options).filter(opt => opt.value && opt.value !== '');
                
                testResult.especialistasCount = options.length;
                testResult.especialistas = options.map(opt => ({
                    id: opt.value,
                    nombre: opt.textContent.replace(' (No disponible)', ''),
                    disponible: !opt.disabled,
                    username: opt.dataset.username,
                    email: opt.dataset.email
                }));
                
                if (options.length > 0) {
                    log(`   ✅ ${options.length} especialistas encontrados`);
                    
                    // Probar selección de un médico
                    const primerMedico = options[0];
                    selectMedico.value = primerMedico.value;
                    
                    log(`   👨‍⚕️ Primer especialista: ${primerMedico.textContent} (ID: ${primerMedico.value})`);
                    
                    testResult.success = true;
                    
                } else {
                    log(`   ⚠️ No se encontraron especialistas disponibles`, 'warning');
                    testResult.error = 'No hay especialistas disponibles';
                }
                
            } catch (error) {
                log(`   ❌ Error en test: ${error.message}`, 'error');
                testResult.error = error.message;
            }
            
            return testResult;
        }

        async function testCurrentSelection() {
            const selectEspecialidad = document.getElementById('especialidad');
            const selectMedico = document.getElementById('medico_id');
            
            const especialidad = selectEspecialidad.value;
            const medicoId = selectMedico.value;
            
            if (!especialidad) {
                log('⚠️ Selecciona una especialidad primero', 'warning');
                return;
            }
            
            if (!medicoId) {
                log('⚠️ Selecciona un especialista específico', 'warning');
                return;
            }
            
            const medicoOption = selectMedico.options[selectMedico.selectedIndex];
            const medicoNombre = medicoOption.textContent;
            
            log(`🧪 PROBANDO SELECCIÓN ACTUAL:`);
            log(`   📋 Especialidad: ${especialidad}`);
            log(`   👨‍⚕️ Especialista: ${medicoNombre} (ID: ${medicoId})`);
            log(`   📧 Email: ${medicoOption.dataset.email}`);
            log(`   👤 Username: ${medicoOption.dataset.username}`);
            
            // Simular envío de cita
            const datosCita = {
                especialidad: especialidad,
                medico_id: medicoId,
                nombre: 'Usuario de Prueba',
                email: 'test@ejemplo.com',
                telefono: '33 1234 5678',
                fecha: new Date(Date.now() + 24*60*60*1000).toISOString().split('T')[0], // mañana
                hora: '10:00',
                descripcion: `Cita de prueba para ${especialidad} con ${medicoNombre}`
            };
            
            log(`📤 Datos de cita preparados:`);
            log(JSON.stringify(datosCita, null, 2));
            
            updateTestResults(`
                <h3>✅ Selección Actual Válida</h3>
                <p><strong>Especialidad:</strong> ${especialidad}</p>
                <p><strong>Médico:</strong> ${medicoNombre}</p>
                <p><strong>ID:</strong> ${medicoId}</p>
                <p><strong>Email:</strong> ${medicoOption.dataset.email}</p>
                <p><strong>Username:</strong> ${medicoOption.dataset.username}</p>
                <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <strong>📋 Datos listos para envío:</strong>
                    <pre>${JSON.stringify(datosCita, null, 2)}</pre>
                </div>
            `, 'success');
        }

        function generateSummaryHTML(results) {
            let html = '<div class="test-grid">';
            
            results.forEach(result => {
                const statusClass = result.success ? 'status-success' : 'status-error';
                const statusText = result.success ? 'EXITOSO' : 'FALLIDO';
                
                html += `
                    <div class="test-card">
                        <h3>${result.especialidad} <span class="status-badge ${statusClass}">${statusText}</span></h3>
                        <p><strong>Rol técnico:</strong> ${result.role}</p>
                        <p><strong>Especialistas encontrados:</strong> ${result.especialistasCount}</p>
                        ${result.error ? `<p class="error"><strong>Error:</strong> ${result.error}</p>` : ''}
                        
                        ${result.especialistas.length > 0 ? `
                            <details>
                                <summary>Ver especialistas (${result.especialistas.length})</summary>
                                <ul>
                                    ${result.especialistas.map(esp => `
                                        <li>
                                            <strong>${esp.nombre}</strong> (ID: ${esp.id})
                                            ${!esp.disponible ? ' <em>(No disponible)</em>' : ''}
                                            <br><small>Email: ${esp.email} | Username: ${esp.username}</small>
                                        </li>
                                    `).join('')}
                                </ul>
                            </details>
                        ` : ''}
                    </div>
                `;
            });
            
            html += '</div>';
            return html;
        }

        function updateTestResults(content, type = 'info') {
            const resultsDiv = document.getElementById('test-results');
            const colorMap = {
                'success': '#d4edda',
                'error': '#f8d7da',
                'warning': '#fff3cd',
                'info': '#e7f3ff'
            };
            
            resultsDiv.innerHTML = `
                <div style="background: ${colorMap[type] || colorMap.info}; padding: 20px; border-radius: 8px;">
                    ${content}
                </div>
            `;
        }
    </script>
</body>
</html>