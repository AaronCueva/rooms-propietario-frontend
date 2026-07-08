<div class="container-fluid py-3" style="max-width: 1200px;">
    <div class="row g-4">
        <div class="col-12">
            <!-- Header section -->
            <div class="d-flex align-items-center mb-4">
                <a href="/" class="btn btn-sm btn-light me-3" style="border-radius: 50%; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--line);">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h4 class="mb-1" style="font-family: var(--font-display); font-weight: 700; color: var(--ink);">Mi Perfil</h4>
                    <div class="text-muted" style="font-size: 14px;">Actualiza tu información personal y foto de perfil</div>
                </div>
            </div>

            <form action="/perfil" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <!-- Columna Izquierda: Foto de Perfil -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                            <div class="card-body p-4 text-center">
                                <div class="mb-4 position-relative d-inline-block">
                                    <?php if (!empty($usuario['url_foto'])): ?>
                                        <img id="preview-foto" src="<?php echo htmlspecialchars($usuario['url_foto']); ?>" alt="Foto de perfil" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover; border-color: var(--line);">
                                    <?php else: ?>
                                        <div id="preview-foto-placeholder" class="rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px; background-color: var(--blue-wash); color: var(--blue); font-size: 48px; border: 2px solid var(--blue-wash);">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <img id="preview-foto" src="" alt="Foto de perfil" class="rounded-circle img-thumbnail d-none" style="width: 150px; height: 150px; object-fit: cover; border-color: var(--line);">
                                    <?php endif; ?>
                                    
                                    <label for="foto_perfil" class="position-absolute bottom-0 end-0 btn btn-sm btn-primary rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid #fff;">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                    <input type="file" id="foto_perfil" name="foto_perfil" class="d-none" accept="image/png, image/jpeg, image/webp">
                                </div>
                                <h5 class="fw-bold mb-1" style="font-size: 18px;"><?php echo htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellido_paterno']); ?></h5>
                                <p class="text-muted mb-3" style="font-size: 14px;">Propietario · <?php echo htmlspecialchars($usuario['distrito_nombre'] ?? 'Lima'); ?></p>
                                
                                <div class="mb-4">
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-semibold" style="font-size: 13px;">
                                        <i class="fas fa-check"></i> Identidad verificada
                                    </span>
                                </div>

                                <div class="d-flex justify-content-center gap-3 mb-4">
                                    <div class="rounded p-2 text-center" style="width: 110px; background-color: #F6F4F0;">
                                        <div class="text-muted fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">CUARTOS</div>
                                        <div class="fw-bold fs-4 text-dark"><?php echo $total_cuartos; ?></div>
                                    </div>
                                    <div class="rounded p-2 text-center" style="width: 110px; background-color: #F6F4F0;">
                                        <div class="text-muted fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">RATING</div>
                                        <div class="fw-bold fs-4 text-dark"><?php echo htmlspecialchars($calificacion); ?> <i class="fas fa-star text-dark" style="font-size: 16px;"></i></div>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <a href="/logout" class="btn btn-outline-secondary rounded-pill fw-bold" style="border-width: 1px; color: #000; border-color: #d1d5db; padding: 10px;">Cerrar sesión</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Formulario de Datos -->
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                            <div class="card-header bg-white border-bottom p-4">
                                <h6 class="m-0 fw-bold">Datos Personales</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <!-- Nombres y Apellidos -->
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-size: 13.5px; font-weight: 500;">Nombres</label>
                                        <input type="text" class="form-control" name="nombres" value="<?php echo htmlspecialchars($usuario['nombres'] ?? ''); ?>" required style="border-radius: 8px;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-size: 13.5px; font-weight: 500;">Apellido Paterno</label>
                                        <input type="text" class="form-control" name="apellido_paterno" value="<?php echo htmlspecialchars($usuario['apellido_paterno'] ?? ''); ?>" required style="border-radius: 8px;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-size: 13.5px; font-weight: 500;">Apellido Materno</label>
                                        <input type="text" class="form-control" name="apellido_materno" value="<?php echo htmlspecialchars($usuario['apellido_materno'] ?? ''); ?>" style="border-radius: 8px;">
                                    </div>

                                    <!-- Correo y Contacto -->
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-size: 13.5px; font-weight: 500;">Correo Electrónico</label>
                                        <input type="email" class="form-control" name="correo" value="<?php echo htmlspecialchars($usuario['correo'] ?? ''); ?>" required style="border-radius: 8px;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-size: 13.5px; font-weight: 500;">Celular</label>
                                        <input type="text" class="form-control" name="celular" value="<?php echo htmlspecialchars($usuario['celular'] ?? ''); ?>" required style="border-radius: 8px;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-size: 13.5px; font-weight: 500;">Teléfono Fijo (Opcional)</label>
                                        <input type="text" class="form-control" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" style="border-radius: 8px;">
                                    </div>

                                    <!-- Documento -->
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-size: 13.5px; font-weight: 500;">Tipo de Documento</label>
                                        <select name="tipo_documento_codigo" class="form-select" style="border-radius: 8px;">
                                            <option value="">Seleccionar...</option>
                                            <?php foreach ($tipos_documento as $td): ?>
                                                <option value="<?php echo $td['codigo']; ?>" <?php echo (($usuario['tipo_documento_codigo'] ?? '') === $td['codigo']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($td['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-size: 13.5px; font-weight: 500;">Número de Documento</label>
                                        <input type="text" class="form-control" name="numero_documento" value="<?php echo htmlspecialchars($usuario['numero_documento'] ?? ''); ?>" style="border-radius: 8px;">
                                    </div>

                                    <!-- Descripción -->
                                    <div class="col-12">
                                        <label class="form-label" style="font-size: 13.5px; font-weight: 500;">Acerca de mí (Descripción)</label>
                                        <textarea class="form-control" name="descripcion" rows="4" style="border-radius: 8px; resize: none;"><?php echo htmlspecialchars($usuario['descripcion'] ?? ''); ?></textarea>
                                        <div class="form-text mt-2" style="font-size: 12.5px;">Esta descripción podrá ser vista por los inquilinos cuando revisen tus alojamientos.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top p-4 text-end">
                                <button type="submit" class="btn btn-primary" style="border-radius: 8px; font-weight: 600; padding: 10px 24px;">
                                    <i class="fas fa-save me-2"></i> Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('foto_perfil').addEventListener('change', function(event) {
    if(event.target.files && event.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var imgPreview = document.getElementById('preview-foto');
            var placeholder = document.getElementById('preview-foto-placeholder');
            
            imgPreview.src = e.target.result;
            imgPreview.classList.remove('d-none');
            
            if(placeholder) {
                placeholder.classList.add('d-none');
            }
        }
        reader.readAsDataURL(event.target.files[0]);
    }
});
</script>
