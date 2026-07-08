<div class="container-fluid py-3" style="max-width: 1200px;">
    <div class="row justify-content-center g-4">
        <div class="col-lg-6 col-md-8 mt-5">
            <div class="d-flex align-items-center mb-4">
                <a href="/perfil" class="btn btn-sm btn-light me-3" style="border-radius: 50%; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--line);">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h4 class="mb-1" style="font-family: var(--font-display); font-weight: 700; color: var(--ink);">Cambiar Contraseña</h4>
                    <div class="text-muted" style="font-size: 14px;">Protege el acceso a tu cuenta</div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-5">
                    <form action="/perfil/password" method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-semibold" style="font-size: 13.5px;">Contraseña Actual</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password_actual" class="form-control border-start-0 ps-0" required style="border-radius: 0 8px 8px 0;" placeholder="Ingresa tu contraseña actual">
                            </div>
                        </div>

                        <hr class="my-4" style="border-color: var(--line);">

                        <div class="mb-4">
                            <label class="form-label fw-semibold" style="font-size: 13.5px;">Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-key"></i></span>
                                <input type="password" name="nueva_password" class="form-control border-start-0 ps-0" required minlength="6" style="border-radius: 0 8px 8px 0;" placeholder="Mínimo 6 caracteres">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" style="font-size: 13.5px;">Confirmar Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-check-circle"></i></span>
                                <input type="password" name="confirmar_password" class="form-control border-start-0 ps-0" required minlength="6" style="border-radius: 0 8px 8px 0;" placeholder="Repite la nueva contraseña">
                            </div>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-primary btn-lg" style="border-radius: 8px; font-weight: 600; font-size: 15px;">
                                <i class="fas fa-sync-alt me-2"></i> Actualizar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
