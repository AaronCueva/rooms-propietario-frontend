<div class="page-content">
    <div class="sol-breadcrumb mb-3">
        <a href="/ingresos"><i class="fas fa-chevron-left"></i> Volver a Ingresos</a>
        <span>/ Cuenta de Cobro</span>
    </div>

    <div class="page-head mb-4">
        <div>
            <div class="eyebrow">Configuración</div>
            <h1 style="font-size: 24px; font-family: var(--font-display); font-weight: 700; margin: 0; color: var(--ink);">Cuenta Bancaria</h1>
            <div class="text-muted mt-1" style="font-size: 14px;">Registra la cuenta principal donde recibirás los pagos de tus inquilinos.</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card" style="border-radius: 16px; border: 1px solid var(--line);">
                <div class="card-body p-4">
                    <form action="/ingresos/cuenta" method="POST">
                        <input type="hidden" name="cuenta_bancaria_id" value="<?php echo $cuenta['cuenta_bancaria_id'] ?? ''; ?>">
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:13.5px;">Banco / Billetera <span class="text-danger">*</span></label>
                                <select name="banco_codigo" class="form-select" style="border-radius: 12px; font-size:14px;" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($bancos as $b): ?>
                                        <option value="<?php echo $b['codigo']; ?>" <?php echo (isset($cuenta['banco_codigo']) && $cuenta['banco_codigo'] === $b['codigo']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($b['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:13.5px;">Tipo de Cuenta <span class="text-danger">*</span></label>
                                <select name="tipo_cuenta_codigo" class="form-select" style="border-radius: 12px; font-size:14px;" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($tipos_cuenta as $tc): ?>
                                        <option value="<?php echo $tc['codigo']; ?>" <?php echo (isset($cuenta['tipo_cuenta_codigo']) && $cuenta['tipo_cuenta_codigo'] === $tc['codigo']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tc['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:13.5px;">Número de Cuenta <span class="text-danger">*</span></label>
                                <input type="text" name="numero_cuenta" class="form-control" style="border-radius: 12px; font-size:14px; font-family: var(--font-mono);" value="<?php echo htmlspecialchars($cuenta['numero_cuenta'] ?? ''); ?>" placeholder="Ej: 191-12345678-0-00" required>
                                <div class="form-text" style="font-size:11.5px;">Si es Yape/Plin, ingresa tu número de celular.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:13.5px;">CCI (Opcional)</label>
                                <input type="text" name="cci" class="form-control" style="border-radius: 12px; font-size:14px; font-family: var(--font-mono);" value="<?php echo htmlspecialchars($cuenta['cci'] ?? ''); ?>" placeholder="Ej: 00219112345678000055">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" style="font-size:13.5px;">Titular de la Cuenta <span class="text-danger">*</span></label>
                            <input type="text" name="titular" class="form-control" style="border-radius: 12px; font-size:14px;" value="<?php echo htmlspecialchars($cuenta['titular'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="alert alert-info border-0" style="background: var(--blue-wash); color: var(--blue); border-radius: 12px; font-size: 13.5px;">
                            <i class="fas fa-info-circle me-2"></i> Esta cuenta será configurada automáticamente como tu <strong>cuenta principal</strong> para recibir los desembolsos de la plataforma.
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="/ingresos" class="btn btn-light" style="border-radius:10px;">Cancelar</a>
                            <button type="submit" class="btn btn-primary" style="border-radius:10px;">
                                <i class="fas fa-save me-1"></i> Guardar Cuenta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
