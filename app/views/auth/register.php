<div class="auth-panel" style="max-width:550px; flex:0 0 550px; overflow-y:auto; padding: 40px;">
    <div class="auth-logo">
        <div class="logo-box"><i class="fas fa-building text-white"></i></div>
        <div class="wm">APP-<span>ROOMS</span></div>
    </div>
    <div class="auth-role-pill">
        <i class="fas fa-key"></i> Registro de propietario
    </div>
    
    <h1 id="reg-title">Datos de la cuenta</h1>
    <div class="sub" id="reg-sub">Empieza a publicar en menos de 5 minutos.</div>
    
    <!-- Indicadores de paso -->
    <div class="steps-row mb-4">
        <div class="step-dot is-active" id="dot-1">1</div>
        <div class="step-line" id="line-12"></div>
        <div class="step-dot" id="dot-2">2</div>
        <div class="step-line" id="line-23"></div>
        <div class="step-dot" id="dot-3">3</div>
    </div>

    <form id="registerForm" action="/register" method="POST">
        
        <!-- PASO 1: Datos Personales -->
        <div id="reg-s1">
            <div class="row g-3">
                <div class="col-md-12">
                    <div class="field">
                        <label>Nombres Completos <span class="req">*</span></label>
                        <input type="text" name="nombres" placeholder="Ej. María" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="field">
                        <label>Apellido Paterno <span class="req">*</span></label>
                        <input type="text" name="apellido_paterno" placeholder="Ej. Gutiérrez" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="field">
                        <label>Apellido Materno <span class="req">*</span></label>
                        <input type="text" name="apellido_materno" placeholder="Ej. Ruiz" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="field">
                        <label>Correo Electrónico <span class="req">*</span></label>
                        <input type="email" name="correo" placeholder="correo@email.com" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="field field-pw">
                        <label>Contraseña <span class="req">*</span></label>
                        <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required>
                        <i class="fas fa-eye-slash" id="toggleIcon" onclick="togglePassword()" style="position: absolute; right: 14px; top: 38px; cursor: pointer; color: var(--ink-faint);"></i>
                    </div>
                </div>
            </div>
            
            <button type="button" class="btn btn-primary btn-block w-100 mt-4" onclick="regStep(2)">Continuar</button>
        </div>

        <!-- PASO 2: Documento y Contacto -->
        <div id="reg-s2" style="display:none;">
            <div class="verify-note mb-3" style="background: var(--gold-wash); color: var(--gold);">
                <i class="fas fa-check-circle me-2 mt-1"></i>
                <span>Verificamos tu identidad para generar confianza en los estudiantes que se alojarán contigo.</span>
            </div>
            
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="field">
                        <label>Tipo Documento <span class="req">*</span></label>
                        <select name="tipo_documento_codigo" required>
                            <option value="" selected disabled>Seleccione...</option>
                            <?php if (isset($tipos_documento)): ?>
                                <?php foreach ($tipos_documento as $tipo): ?>
                                    <option value="<?php echo htmlspecialchars($tipo['codigo']); ?>">
                                        <?php echo htmlspecialchars($tipo['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="field">
                        <label>Número Documento <span class="req">*</span></label>
                        <input type="text" name="numero_documento" placeholder="Número" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="field">
                        <label>Celular Principal <span class="req">*</span></label>
                        <input type="text" name="celular" placeholder="Número celular" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="field">
                        <label>Teléfono Fijo <span class="text-muted">(Opcional)</span></label>
                        <input type="text" name="telefono" placeholder="Número teléfono">
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-primary btn-block w-100 mt-4" onclick="regStep(3)">Verificar y continuar</button>
            <button type="button" class="btn btn-ghost btn-block w-100 mt-2" onclick="regStep(1)">Atrás</button>
        </div>

        <!-- PASO 3: Ubicación Principal -->
        <div id="reg-s3" style="display:none;">
            <div class="verify-note mb-3" style="background: var(--gold-wash); color: var(--gold);">
                <i class="fas fa-home me-2 mt-1"></i>
                <span>Selecciona la ubicación de tu residencia principal. Posteriormente podrás registrar las propiedades en diferentes ubicaciones.</span>
            </div>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="field">
                        <label>Departamento <span class="req">*</span></label>
                        <select id="departamento" name="departamento" required onchange="cargarProvincias(this.value)">
                            <option value="" selected disabled>Seleccione...</option>
                            <?php if (isset($departamentos)): ?>
                                <?php foreach ($departamentos as $dep): ?>
                                    <option value="<?php echo htmlspecialchars($dep['ubicacion_id']); ?>">
                                        <?php echo htmlspecialchars($dep['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="field">
                        <label>Provincia <span class="req">*</span></label>
                        <select id="provincia" name="provincia" required onchange="cargarDistritos(this.value)" disabled>
                            <option value="" selected disabled>Seleccione...</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="field">
                        <label>Distrito <span class="req">*</span></label>
                        <select id="distrito" name="distrito" required disabled>
                            <option value="" selected disabled>Seleccione...</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block w-100 mt-4">Crear mi cuenta</button>
            <button type="button" class="btn btn-ghost btn-block w-100 mt-2" onclick="regStep(2)">Atrás</button>
        </div>

    </form>
    
    <div class="auth-footer mt-4">
        <a href="/login">Ya tengo cuenta — Iniciar sesión</a>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        }
    }

    function regStep(n) {
        if (n > 1) {
            let prevStep = document.getElementById('reg-s' + (n - 1));
            let inputs = prevStep.querySelectorAll('input[required], select[required]');
            let valid = true;
            inputs.forEach(input => {
                if (!input.value) {
                    input.style.borderColor = 'var(--red)';
                    valid = false;
                } else {
                    input.style.borderColor = 'var(--line)';
                }
            });
            if (!valid) return;
        }

        [1, 2, 3].forEach(i => {
            var s = document.getElementById('reg-s' + i);
            if (s) s.style.display = (i === n) ? 'block' : 'none';
            
            var d = document.getElementById('dot-' + i);
            if (d) {
                d.classList.remove('is-active', 'is-done');
                if (i < n) d.classList.add('is-done');
                else if (i === n) d.classList.add('is-active');
            }
        });
        
        var l12 = document.getElementById('line-12'), l23 = document.getElementById('line-23');
        if (l12) l12.classList.toggle('is-done', n > 1);
        if (l23) l23.classList.toggle('is-done', n > 2);
        
        const titles = {
            1: 'Datos de la cuenta',
            2: 'Verifica tu identidad',
            3: 'Ubicación principal'
        };
        const subs = {
            1: 'Empieza a publicar en menos de 5 minutos.',
            2: 'Protégete y protege a la comunidad.',
            3: 'Registra tu residencia actual.'
        };
        
        document.getElementById('reg-title').textContent = titles[n];
        document.getElementById('reg-sub').textContent = subs[n];
    }

    async function cargarProvincias(departamento_id) {
        const provinciaSelect = document.getElementById('provincia');
        const distritoSelect = document.getElementById('distrito');

        provinciaSelect.innerHTML = '<option value="" selected disabled>Seleccione...</option>';
        distritoSelect.innerHTML = '<option value="" selected disabled>Seleccione...</option>';
        provinciaSelect.disabled = true;
        distritoSelect.disabled = true;

        if (!departamento_id) return;

        try {
            const response = await fetch('/api/ubicaciones?referencia_id=' + departamento_id);
            const data = await response.json();

            if (data.length > 0) {
                data.forEach(provincia => {
                    const option = document.createElement('option');
                    option.value = provincia.ubicacion_id;
                    option.textContent = provincia.nombre;
                    provinciaSelect.appendChild(option);
                });
                provinciaSelect.disabled = false;
            }
        } catch (error) {
            console.error('Error al cargar provincias:', error);
        }
    }

    async function cargarDistritos(provincia_id) {
        const distritoSelect = document.getElementById('distrito');
        distritoSelect.innerHTML = '<option value="" selected disabled>Seleccione...</option>';
        distritoSelect.disabled = true;

        if (!provincia_id) return;

        try {
            const response = await fetch('/api/ubicaciones?referencia_id=' + provincia_id);
            const data = await response.json();

            if (data.length > 0) {
                data.forEach(distrito => {
                    const option = document.createElement('option');
                    option.value = distrito.ubicacion_id;
                    option.textContent = distrito.nombre;
                    distritoSelect.appendChild(option);
                });
                distritoSelect.disabled = false;
            }
        } catch (error) {
            console.error('Error al cargar distritos:', error);
        }
    }
</script>
