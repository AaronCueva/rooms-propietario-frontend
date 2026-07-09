<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<div class="page-content">
    <div class="page-head">
        <div>
            <div class="eyebrow">Nuevo alojamiento</div>
            <h1>Publicar nuevo cuarto</h1>
            <div class="sub">Completa los datos para publicar tu cuarto en APP-ROOMS.</div>
        </div>
        <a href="/alojamientos" class="btn btn-ghost"><i class="fas fa-arrow-left" style="font-size:13px;"></i> Volver al listado</a>
    </div>

    <form action="/alojamientos/guardar" method="POST" enctype="multipart/form-data" id="formAlojamiento">

        <!-- SECCIÓN 1: Información básica -->
        <div class="card form-section">
            <div class="form-section-head">
                <div class="fsh-icon" style="background:var(--red-wash);"><i class="fas fa-home" style="color:var(--red);"></i></div>
                <div>
                    <h3>Información básica</h3>
                    <p class="text-muted">Datos generales de tu cuarto.</p>
                </div>
            </div>
            <div class="form-grid">
                <div class="field" style="grid-column: 1 / -1;">
                    <label>Título del anuncio <span class="req">*</span></label>
                    <input type="text" name="titulo" placeholder="Ej. Cuarto privado amplio cerca a PUCP" required>
                </div>
                <div class="field">
                    <label>Tipo de alojamiento</label>
                    <select name="tipo_codigo">
                        <option value="" selected disabled>Seleccione...</option>
                        <?php if (isset($tipos_alojamiento) && is_array($tipos_alojamiento)): ?>
                            <?php foreach ($tipos_alojamiento as $tipo): ?>
                                <option value="<?php echo htmlspecialchars($tipo['codigo']); ?>">
                                    <?php echo htmlspecialchars($tipo['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="HAB_PRIVADA">Habitación privada</option>
                            <option value="HAB_COMPARTIDA">Habitación compartida</option>
                            <option value="DEPARTAMENTO">Departamento completo</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Género exclusivo</label>
                    <select name="genero_exclusivo_codigo">
                        <?php if (isset($generos) && is_array($generos)): ?>
                            <?php foreach ($generos as $gen): ?>
                                <option value="<?php echo htmlspecialchars($gen['codigo']); ?>">
                                    <?php echo htmlspecialchars($gen['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Mixto (sin restricción)</option>
                            <option value="M">Solo masculino</option>
                            <option value="F">Solo femenino</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="field" style="grid-column: 1 / -1;">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="4" placeholder="Describe tu cuarto: ambiente, iluminación, vista, ventajas de la ubicación..." style="width:100%;padding:12px 14px;border-radius:11px;border:1.5px solid var(--line);background:#FBFAF8;font-size:14px;font-family:var(--font-body);color:var(--ink);resize:vertical;"></textarea>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: Características -->
        <div class="card form-section">
            <div class="form-section-head">
                <div class="fsh-icon" style="background:var(--blue-wash);"><i class="fas fa-ruler-combined" style="color:var(--blue);"></i></div>
                <div>
                    <h3>Características del cuarto</h3>
                    <p class="text-muted">Detalles técnicos del espacio.</p>
                </div>
            </div>
            <div class="form-grid">
                <div class="field">
                    <label>N° de habitaciones</label>
                    <input type="number" name="numero_habitaciones" value="1" min="1" max="20">
                </div>
                <div class="field">
                    <label>N° de baños</label>
                    <input type="number" name="numero_banos" value="1" min="0" max="10">
                </div>
                <div class="field">
                    <label>Tamaño (m²)</label>
                    <input type="number" name="tamano_m2" placeholder="Ej. 15" min="1" step="0.5">
                </div>
                <div class="field">
                    <label>Duración mínima (meses)</label>
                    <input type="number" name="duracion_minima_meses" placeholder="Ej. 6" min="1">
                </div>
            </div>
            <div class="checkbox-row">
                <label class="check-item"><input type="checkbox" name="bano_privado" value="1"> <span>Baño privado</span></label>
                <label class="check-item"><input type="checkbox" name="amoblado" value="1"> <span>Amoblado</span></label>
                <label class="check-item"><input type="checkbox" name="mascotas_permitidas" value="1"> <span>Mascotas permitidas</span></label>
                <label class="check-item"><input type="checkbox" name="fumadores_permitidos" value="1"> <span>Fumadores permitidos</span></label>
            </div>
        </div>

        <!-- SECCIÓN 3: Ubicación -->
        <div class="card form-section">
            <div class="form-section-head">
                <div class="fsh-icon" style="background:var(--green-wash);"><i class="fas fa-map-marker-alt" style="color:var(--green);"></i></div>
                <div>
                    <h3>Ubicación</h3>
                    <p class="text-muted">¿Dónde se encuentra el cuarto?</p>
                </div>
            </div>
            <div class="form-grid">
                <div class="field" style="grid-column: 1 / -1;">
                    <label>Dirección <span class="req">*</span></label>
                    <input type="text" name="direccion" placeholder="Ej. Jr. Cantuarias 240, Pueblo Libre" required>
                </div>
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
                <div class="field">
                    <label>Provincia <span class="req">*</span></label>
                    <select id="provincia" name="provincia" required onchange="cargarDistritos(this.value)" disabled>
                        <option value="" selected disabled>Seleccione...</option>
                    </select>
                </div>
                <div class="field" style="grid-column: 1 / -1;">
                    <label>Distrito <span class="req">*</span></label>
                    <select id="distrito" name="distrito" required disabled onchange="centrarMapaDistrito()">
                        <option value="" selected disabled>Seleccione...</option>
                    </select>
                </div>
                <div class="field" style="grid-column: 1 / -1;">
                    <label>Ubicación exacta en el mapa <span class="req">*</span></label>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                        <p class="text-muted" style="font-size: 12px; margin: 0;">Mueve el marcador para ajustar la ubicación exacta.</p>
                        <button type="button" class="btn btn-ghost btn-sm" onclick="ubicarMiPosicion()" style="font-size: 12px; padding: 4px 8px;"><i class="fas fa-crosshairs"></i> Usar mi ubicación actual</button>
                    </div>
                    <div id="map" style="height: 300px; border-radius: 11px; z-index: 1;"></div>
                    <input type="hidden" name="latitud" id="latitud">
                    <input type="hidden" name="longitud" id="longitud">
                </div>
            </div>
        </div>

        <!-- SECCIÓN 4: Precio -->
        <div class="card form-section">
            <div class="form-section-head">
                <div class="fsh-icon" style="background:var(--gold-wash);"><i class="fas fa-tag" style="color:var(--gold);"></i></div>
                <div>
                    <h3>Precio y disponibilidad</h3>
                    <p class="text-muted">Establece la renta mensual y la garantía.</p>
                </div>
            </div>
            <div class="form-grid">
                <div class="field">
                    <label>Precio mensual (S/) <span class="req">*</span></label>
                    <input type="number" name="precio_mensual" placeholder="Ej. 850" min="0" step="10" required>
                </div>
                <div class="field">
                    <label>Garantía (S/)</label>
                    <input type="number" name="garantia" placeholder="Ej. 850" min="0" step="10">
                </div>
                <div class="field">
                    <label>Fecha disponible</label>
                    <input type="date" name="fecha_disponible">
                </div>
                <div class="field">
                    <label>Moneda</label>
                    <select name="moneda_codigo">
                        <?php if (isset($monedas) && is_array($monedas)): ?>
                            <?php foreach ($monedas as $mon): ?>
                                <option value="<?php echo htmlspecialchars($mon['codigo']); ?>" <?php echo $mon['codigo'] === 'PEN' ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($mon['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="PEN" selected>Soles (S/)</option>
                            <option value="USD">Dólares (US$)</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 5: Fotos -->
        <div class="card form-section">
            <div class="form-section-head">
                <div class="fsh-icon" style="background:var(--purple-wash);"><i class="fas fa-camera" style="color:var(--purple);"></i></div>
                <div>
                    <h3>Fotos del cuarto</h3>
                    <p class="text-muted">Sube hasta 5 fotos. La primera será la imagen principal del anuncio.</p>
                </div>
            </div>
            
            <div class="upload-zone" id="uploadZone" onclick="document.getElementById('inputFotos').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size:32px;color:var(--ink-faint);margin-bottom:10px;"></i>
                <div class="ub-title">Haz clic o arrastra las imágenes aquí</div>
                <div class="ub-sub">JPG, PNG o WebP · máx. 5 MB por imagen</div>
                <input type="file" name="fotos[]" id="inputFotos" multiple accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previewPhotos(this)">
            </div>

            <div id="photoPreview" class="photo-preview-grid"></div>
        </div>

        <!-- BOTONES -->
        <div class="form-footer">
            <a href="/alojamientos" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check" style="font-size:13px;"></i> Publicar cuarto</button>
        </div>

    </form>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // --- MAPA LEAFLET ---
    let map = L.map('map').setView([-12.046374, -77.042793], 12); // Lima, Perú por defecto
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let marker = L.marker([-12.046374, -77.042793], {draggable: true}).addTo(map);

    // Inicializar coordenadas con la posición por defecto del marcador
    document.getElementById('latitud').value = marker.getLatLng().lat;
    document.getElementById('longitud').value = marker.getLatLng().lng;

    // Al mover el marcador
    marker.on('dragend', function(event) {
        let position = marker.getLatLng();
        document.getElementById('latitud').value = position.lat;
        document.getElementById('longitud').value = position.lng;
    });

    // Al hacer click en el mapa
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        document.getElementById('latitud').value = e.latlng.lat;
        document.getElementById('longitud').value = e.latlng.lng;
    });

    // Geolocalización
    function ubicarMiPosicion() {
        if (!navigator.geolocation) {
            Swal.fire({
                icon: 'warning', title: 'No soportado',
                text: 'Tu navegador no soporta geolocalización.',
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000
            });
            return;
        }
        let obtenido = false;
        navigator.geolocation.getCurrentPosition(function(position) {
            obtenido = true;
            let lat = position.coords.latitude;
            let lng = position.coords.longitude;
            let latlng = L.latLng(lat, lng);
            marker.setLatLng(latlng);
            map.setView(latlng, 16);
            document.getElementById('latitud').value = lat;
            document.getElementById('longitud').value = lng;
        }, function(error) {
            // Si ya se obtuvo la posición, ignorar errores espurios (algunos navegadores
            // disparan ambos callbacks). Así no mostramos el error cuando sí se jaló la ubicación.
            if (obtenido) return;
            let msg = 'No pudimos obtener tu ubicación actual. Asegúrate de tener el GPS activado y dar permisos al navegador.';
            if (error.code === 1) msg = 'Permiso denegado. Habilita el acceso a tu ubicación en el navegador.';
            else if (error.code === 2) msg = 'Posición no disponible. Verifica tu GPS o tu conexión.';
            else if (error.code === 3) msg = 'Se agotó el tiempo al obtener la ubicación. Intenta de nuevo.';
            Swal.fire({
                icon: 'error', title: 'Error de ubicación', text: msg,
                toast: true, position: 'top-end', showConfirmButton: false, timer: 4000
            });
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
    }

    // Al seleccionar un distrito, centrar el mapa en sus coordenadas
    function centrarMapaDistrito() {
        const sel = document.getElementById('distrito');
        const opt = sel.options[sel.selectedIndex];
        if (!opt) return;
        const lat = parseFloat(opt.dataset.lat);
        const lng = parseFloat(opt.dataset.lng);
        if (isNaN(lat) || isNaN(lng)) return;
        const latlng = L.latLng(lat, lng);
        marker.setLatLng(latlng);
        map.setView(latlng, 14);
        document.getElementById('latitud').value = lat;
        document.getElementById('longitud').value = lng;
    }

    // --- GESTOR DE FOTOS ---
    let selectedFiles = [];
    const maxFotos = 5;

    function previewPhotos(input) {
        if (input.files) {
            for (let i = 0; i < input.files.length; i++) {
                if (selectedFiles.length < maxFotos) {
                    selectedFiles.push(input.files[i]);
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Límite alcanzado',
                        text: 'Solo puedes subir hasta 5 fotos.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    break;
                }
            }
            renderPreview();
        }
    }

    function renderPreview() {
        const previewContainer = document.getElementById('photoPreview');
        previewContainer.innerHTML = '';

        // Actualizar el input file oculto con los archivos acumulados
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        document.getElementById('inputFotos').files = dt.files;

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'photo-preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    ${index === 0 ? '<span class="photo-badge">Principal</span>' : ''}
                    <button type="button" onclick="event.stopPropagation(); removePhoto(${index})" 
                            style="position:absolute; top:4px; right:4px; background:var(--red); color:#fff; border:none; border-radius:50%; width:22px; height:22px; font-size:11px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    function removePhoto(index) {
        selectedFiles.splice(index, 1);
        renderPreview();
    }

    // Drag & drop visual feedback
    const uploadZone = document.getElementById('uploadZone');
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });
    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
    });
    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        const input = document.getElementById('inputFotos');
        input.files = e.dataTransfer.files;
        previewPhotos(input);
    });

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
                    option.dataset.lat = distrito.latitud ?? '';
                    option.dataset.lng = distrito.longitud ?? '';
                    distritoSelect.appendChild(option);
                });
                distritoSelect.disabled = false;
            }
        } catch (error) {
            console.error('Error al cargar distritos:', error);
        }
    }
</script>
