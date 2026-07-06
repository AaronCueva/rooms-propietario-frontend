<div class="page-content">
    <div class="sol-breadcrumb mb-3">
        <a href="/solicitudes/detalle?id=<?php echo $solicitud['reserva_id']; ?>">
            <i class="fas fa-chevron-left"></i> Volver a solicitud
        </a>
        <span>/ Formalizar Contrato</span>
    </div>

    <div class="page-head mb-4">
        <div>
            <div class="eyebrow">Alojamiento: <?php echo htmlspecialchars($solicitud['alojamiento_titulo']); ?></div>
            <h1>Formalizar Contrato</h1>
            <div class="sub">Inquilino: <?php echo htmlspecialchars($solicitud['inquilino_nombre_completo']); ?></div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <form action="/contratos/guardar" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="reserva_id" value="<?php echo $solicitud['reserva_id']; ?>">
                        
                        <h6 class="mb-4" style="font-family: var(--font-display); font-weight: 700;">
                            <i class="fas fa-calendar-alt text-primary me-2"></i> Detalles del Contrato
                        </h6>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha de Inicio <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_inicio" class="form-control" 
                                       value="<?php echo !empty($solicitud['fecha_ingreso']) ? date('Y-m-d', strtotime($solicitud['fecha_ingreso'])) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <?php
                                $fecha_fin_estimada = '';
                                if (!empty($solicitud['fecha_ingreso']) && !empty($solicitud['duracion_meses'])) {
                                    $fecha_fin_estimada = date('Y-m-d', strtotime($solicitud['fecha_ingreso'] . ' + ' . $solicitud['duracion_meses'] . ' months'));
                                }
                                ?>
                                <label class="form-label fw-semibold">Fecha de Fin <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_fin" class="form-control" 
                                       value="<?php echo $fecha_fin_estimada; ?>" required>
                            </div>
                        </div>

                        <h6 class="mb-4 mt-4" style="font-family: var(--font-display); font-weight: 700;">
                            <i class="fas fa-money-bill-wave text-success me-2"></i> Detalles Financieros
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Monto Renta Mensual (S/) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="monto_renta" class="form-control" 
                                       value="<?php echo htmlspecialchars($solicitud['monto_total'] > 0 ? $solicitud['monto_total'] : $solicitud['precio_mensual']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Monto Garantía (S/)</label>
                                <input type="number" step="0.01" name="monto_garantia" class="form-control" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Día de pago mensual <span class="text-danger">*</span></label>
                                <input type="number" min="1" max="31" name="fecha_pago_mensual" class="form-control" 
                                       value="<?php echo !empty($solicitud['fecha_ingreso']) ? date('d', strtotime($solicitud['fecha_ingreso'])) : '1'; ?>" required>
                            </div>
                        </div>

                        <h6 class="mb-4 mt-4" style="font-family: var(--font-display); font-weight: 700;">
                            <i class="fas fa-file-pdf text-danger me-2"></i> Documento Firmado
                        </h6>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Subir Contrato PDF (escaneado) <span class="text-danger">*</span></label>
                            <input type="file" name="documento" class="form-control" accept="application/pdf" required>
                            <div class="form-text mt-2 text-muted">
                                <i class="fas fa-info-circle"></i> Sube el documento firmado por ambas partes para constancia.
                            </div>
                        </div>

                        <div class="alert alert-warning border-0" style="background: var(--amber-wash); color: #92400E; border-radius: 12px;">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Importante:</strong> Al formalizar este contrato, el estado de tu alojamiento cambiará automáticamente a <strong>Ocupado</strong> y no se mostrará disponible en las búsquedas.
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="/solicitudes/detalle?id=<?php echo $solicitud['reserva_id']; ?>" class="btn btn-light">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Contrato
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card bg-light border-0">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-lightbulb text-warning"></i> Recomendaciones</h6>
                    <ul class="text-muted" style="font-size: 13px; padding-left: 1rem;">
                        <li class="mb-2">Revisa bien las fechas antes de guardar. Las puedes tomar de lo acordado en la reserva.</li>
                        <li class="mb-2">El archivo debe estar en formato PDF y ser legible.</li>
                        <li class="mb-2">Una vez guardado el contrato, podrás finalizarlo o cancelarlo más adelante.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
