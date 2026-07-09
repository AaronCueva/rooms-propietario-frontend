<?php
// Mapeo de estados
$badge_map = [
    'ESRE001' => ['label' => 'Pendiente',   'class' => 'badge-amber', 'icon' => 'fa-clock'],
    'ESRE002' => ['label' => 'Aprobada',    'class' => 'badge-green', 'icon' => 'fa-check-circle'],
    'ESRE003' => ['label' => 'Rechazada',   'class' => 'badge-red',   'icon' => 'fa-times-circle'],
    'ESRE004' => ['label' => 'Finalizada',  'class' => 'badge-faint', 'icon' => 'fa-hourglass-end'],
    'ESRE005' => ['label' => 'En revisión', 'class' => 'badge-blue',  'icon' => 'fa-eye'],
    'ESRE006' => ['label' => 'Formalizada', 'class' => 'badge-purple','icon' => 'fa-file-signature'],
];
$estado  = $solicitud['estado_codigo'];
$badge   = $badge_map[$estado] ?? ['label' => $estado, 'class' => 'badge-faint', 'icon' => 'fa-circle'];

// Nombre completo e iniciales (calculados en modelo)
$nombres  = htmlspecialchars($solicitud['inquilino_nombre_completo']);
$initials = $solicitud['inquilino_iniciales'] ?? strtoupper(
    substr($solicitud['inquilino_nombres'] ?? 'I', 0, 1) .
    substr($solicitud['inquilino_apellido_paterno'] ?? 'N', 0, 1)
);

// Gradiente avatar
$av_colors = [
    'linear-gradient(135deg,var(--blue),#7BA0F6)',
    'linear-gradient(135deg,var(--green),#5FCB89)',
    'linear-gradient(135deg,var(--purple),#A78BFA)',
    'linear-gradient(135deg,var(--amber),#F0AE5C)',
    'linear-gradient(135deg,var(--teal),#4FCBDA)',
];
$av_grad = $av_colors[abs(crc32($solicitud['inquilino_id'] ?? '0')) % count($av_colors)];

// Fechas
$fecha_ref     = $solicitud['fecha_solicitud'] ?? $solicitud['creado'];
$fecha_creado  = $fecha_ref ? date('d/m/Y H:i', strtotime($fecha_ref)) : '—';
$fecha_ingreso = !empty($solicitud['fecha_ingreso']) ? date('d \d\e M Y', strtotime($solicitud['fecha_ingreso'])) : '—';
$duracion      = !empty($solicitud['duracion_meses']) 
    ? $solicitud['duracion_meses'] . ' mes' . ($solicitud['duracion_meses'] > 1 ? 'es' : '') 
    : '—';

// Acciones según estado
$puede_aprobar  = in_array($estado, ['ESRE005']);
$puede_rechazar = in_array($estado, ['ESRE001', 'ESRE005']);
?>

<div class="page-content">
    <!-- Breadcrumb -->
    <div class="sol-breadcrumb">
        <a href="/solicitudes"><i class="fas fa-chevron-left"></i> Solicitudes</a>
        <span>/ Detalle</span>
    </div>

    <div class="page-head" style="margin-top: 12px;">
        <div>
            <div class="eyebrow">Solicitud de reserva</div>
            <h1 style="font-size:22px;">
                Solicitud #<?php echo substr($solicitud['reserva_id'], 0, 8); ?>
            </h1>
            <div style="margin-top:8px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <span class="badge <?php echo $badge['class']; ?>">
                    <i class="fas <?php echo $badge['icon']; ?>"></i>
                    <?php echo $badge['label']; ?>
                </span>
                <?php if (!empty($solicitud['estado_calculado'])): ?>
                    <span class="badge badge-faint" style="font-size:11px;">
                        <i class="fas fa-info-circle"></i> Sin respuesta por +7 días
                    </span>
                <?php endif; ?>
                <span style="font-size:12px; color:var(--ink-faint);">Recibida el <?php echo $fecha_creado; ?></span>
            </div>
        </div>

        <?php if ($puede_aprobar || $puede_rechazar || $estado === 'ESRE002'): ?>
            <div class="sol-det-actions">
                <?php if ($puede_rechazar): ?>
                    <button class="btn btn-ghost btn-sm" onclick="abrirModalRechazo()">
                        <i class="fas fa-times"></i> Rechazar
                    </button>
                <?php endif; ?>
                <?php if ($puede_aprobar): ?>
                    <button class="btn btn-primary btn-sm" onclick="confirmarAprobacion()">
                        <i class="fas fa-check"></i> Aprobar solicitud
                    </button>
                <?php endif; ?>
                <?php if ($estado === 'ESRE002'): ?>
                    <a href="/contratos/formalizar?reserva_id=<?php echo $solicitud['reserva_id']; ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-contract"></i> Formalizar Contrato
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="row g-4 mt-1">
        <!-- COLUMNA IZQUIERDA: Inquilino + Alojamiento -->
        <div class="col-lg-7">

            <!-- Card: Perfil del inquilino -->
            <div class="card sol-det-card mb-4">
                <div class="sol-det-card-head">
                    <i class="fas fa-user-circle text-primary"></i>
                    <h6>Perfil del inquilino</h6>
                </div>
                <div class="sol-det-card-body">
                    <div class="sol-det-inquilino">
                        <div class="sol-det-av" style="background: <?php echo $av_grad; ?>">
                            <?php if (!empty($solicitud['inquilino_foto'])): ?>
                                <img src="<?php echo htmlspecialchars($solicitud['inquilino_foto']); ?>" alt="Foto">
                            <?php else: ?>
                                <?php echo $initials; ?>
                            <?php endif; ?>
                        </div>
                        <div class="sol-det-info">
                            <div class="sol-det-nombre"><?php echo $nombres; ?></div>
                            <?php if (!empty($solicitud['universidad_nombre'])): ?>
                                <div class="sol-det-sub">
                                    <i class="fas fa-university fa-fw"></i>
                                    <?php echo htmlspecialchars($solicitud['universidad_nombre']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="sol-det-data-grid">
                        <?php if (!empty($solicitud['inquilino_correo'])): ?>
                            <div class="sol-det-data-item">
                                <div class="sol-dd-label">Correo</div>
                                <div class="sol-dd-val">
                                    <a href="mailto:<?php echo htmlspecialchars($solicitud['inquilino_correo']); ?>">
                                        <?php echo htmlspecialchars($solicitud['inquilino_correo']); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($solicitud['inquilino_celular'])): ?>
                            <div class="sol-det-data-item">
                                <div class="sol-dd-label">Celular</div>
                                <div class="sol-dd-val">
                                    <a href="tel:<?php echo htmlspecialchars($solicitud['inquilino_celular']); ?>">
                                        <?php echo htmlspecialchars($solicitud['inquilino_celular']); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($solicitud['inquilino_telefono'])): ?>
                            <div class="sol-det-data-item">
                                <div class="sol-dd-label">Teléfono</div>
                                <div class="sol-dd-val">
                                    <a href="tel:<?php echo htmlspecialchars($solicitud['inquilino_telefono']); ?>">
                                        <?php echo htmlspecialchars($solicitud['inquilino_telefono']); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Mensaje de presentación del inquilino -->
                    <?php if (!empty($solicitud['mensaje_presentacion'])): ?>
                        <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--line-soft);">
                            <div class="sol-dd-label" style="margin-bottom:8px;">Mensaje de presentación</div>
                            <div style="background:var(--blue-wash); border-radius:12px; padding:14px 16px; font-size:13.5px; color:var(--ink); line-height:1.6;">
                                <i class="fas fa-quote-left" style="color:var(--blue); opacity:.5; margin-right:6px;"></i>
                                <?php echo nl2br(htmlspecialchars($solicitud['mensaje_presentacion'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card: Alojamiento solicitado -->
            <div class="card sol-det-card mb-4">
                <div class="sol-det-card-head">
                    <i class="fas fa-home text-danger"></i>
                    <h6>Alojamiento solicitado</h6>
                </div>
                <div class="sol-det-card-body">
                    <div class="sol-aloj-row">
                        <div class="sol-aloj-thumb">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <div class="sol-aloj-nombre"><?php echo htmlspecialchars($solicitud['alojamiento_titulo']); ?></div>
                            <?php if (!empty($solicitud['alojamiento_direccion'])): ?>
                                <div class="sol-aloj-loc" style="margin-top:4px;">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($solicitud['alojamiento_direccion']); ?>
                                    <?php if (!empty($solicitud['distrito_nombre'])): ?>
                                        · <?php echo htmlspecialchars($solicitud['distrito_nombre']); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="sol-det-data-grid" style="margin-top:16px;">
                        <div class="sol-det-data-item">
                            <div class="sol-dd-label">Precio mensual</div>
                            <div class="sol-dd-val mono">S/ <?php echo number_format($solicitud['precio_mensual'], 0, '.', ','); ?></div>
                        </div>
                        <?php if (!empty($solicitud['numero_habitaciones'])): ?>
                            <div class="sol-det-data-item">
                                <div class="sol-dd-label">Habitaciones</div>
                                <div class="sol-dd-val"><?php echo $solicitud['numero_habitaciones']; ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($solicitud['numero_banos'])): ?>
                            <div class="sol-det-data-item">
                                <div class="sol-dd-label">Baños</div>
                                <div class="sol-dd-val"><?php echo $solicitud['numero_banos']; ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($solicitud['tamano_m2'])): ?>
                            <div class="sol-det-data-item">
                                <div class="sol-dd-label">Tamaño</div>
                                <div class="sol-dd-val"><?php echo $solicitud['tamano_m2']; ?> m²</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- COLUMNA DERECHA: Detalles + Timeline -->
        <div class="col-lg-5">

            <!-- Card: Detalles de la reserva -->
            <div class="card sol-det-card mb-4">
                <div class="sol-det-card-head">
                    <i class="fas fa-calendar-alt text-success"></i>
                    <h6>Detalles de la reserva</h6>
                </div>
                <div class="sol-det-card-body">
                    <div class="sol-det-period-box">
                        <div class="sol-period-col">
                            <div class="sol-period-label">Fecha ingreso</div>
                            <div class="sol-period-val"><?php echo $fecha_ingreso; ?></div>
                        </div>
                        <div class="sol-period-arrow"><i class="fas fa-long-arrow-alt-right"></i></div>
                        <div class="sol-period-col">
                            <div class="sol-period-label">Duración</div>
                            <div class="sol-period-val"><?php echo $duracion; ?></div>
                        </div>
                    </div>

                    <?php if (!empty($solicitud['monto_total']) && $solicitud['monto_total'] > 0): ?>
                        <div class="sol-det-total">
                            <div class="sol-dd-label">Monto total acordado</div>
                            <div class="sol-total-val mono">S/ <?php echo number_format($solicitud['monto_total'], 0, '.', ','); ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Timeline de estado -->
                    <div class="sol-timeline">
                        <div class="sol-tl-item <?php echo in_array($estado, ['ESRE001','ESRE005','ESRE002','ESRE003','ESRE004']) ? 'done' : ''; ?>">
                            <div class="sol-tl-dot"></div>
                            <div class="sol-tl-text">
                                <div class="sol-tl-label">Solicitud recibida</div>
                                <div class="sol-tl-date">
                                    <?php echo $fecha_ref ? date('d/m/Y H:i', strtotime($fecha_ref)) : '—'; ?>
                                </div>
                            </div>
                        </div>
                        <div class="sol-tl-item <?php echo in_array($estado, ['ESRE005','ESRE002','ESRE003']) ? 'done' : ''; ?>">
                            <div class="sol-tl-dot"></div>
                            <div class="sol-tl-text">
                                <div class="sol-tl-label">En revisión</div>
                                <div class="sol-tl-date">
                                    <?php if (in_array($estado, ['ESRE005','ESRE002','ESRE003']) && !empty($solicitud['modificado'])): ?>
                                        <?php echo date('d/m/Y H:i', strtotime($solicitud['modificado'])); ?>
                                    <?php else: ?>
                                        Pendiente
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="sol-tl-item <?php echo $estado === 'ESRE002' ? 'done success' : ($estado === 'ESRE003' ? 'done error' : ($estado === 'ESRE004' ? 'done faint' : '')); ?>">
                            <div class="sol-tl-dot"></div>
                            <div class="sol-tl-text">
                                <div class="sol-tl-label">
                                    <?php
                                    if ($estado === 'ESRE002') echo 'Aprobada → Contrato';
                                    elseif ($estado === 'ESRE003') echo 'Rechazada';
                                    elseif ($estado === 'ESRE004') echo 'Finalizada (sin respuesta)';
                                    else echo 'Resolución pendiente';
                                    ?>
                                </div>
                                <?php if (!empty($solicitud['fecha_respuesta']) && in_array($estado, ['ESRE002','ESRE003'])): ?>
                                    <div class="sol-tl-date"><?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_respuesta'])); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Observación de rechazo (si aplica) -->
            <?php if ($estado === 'ESRE003' && !empty($solicitud['observacion'])): ?>
                <div class="card sol-det-card mb-4" style="border-color: var(--red-wash);">
                    <div class="sol-det-card-head" style="color: var(--red);">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h6>Motivo del rechazo</h6>
                    </div>
                    <div class="sol-det-card-body">
                        <div class="sol-obs-box">
                            <?php echo nl2br(htmlspecialchars($solicitud['observacion'])); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulario oculto para aprobar -->
            <?php if ($puede_aprobar): ?>
                <form id="form-aprobar" method="POST" action="/solicitudes/aprobar" style="display:none;">
                    <input type="hidden" name="reserva_id" value="<?php echo $solicitud['reserva_id']; ?>">
                </form>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- MODAL: Rechazar -->
<?php if ($puede_rechazar): ?>
<div class="modal fade" id="modalRechazo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: 1px solid var(--line);">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold" style="font-family: var(--font-display);">
                        <i class="fas fa-times-circle text-danger me-2"></i> Rechazar solicitud
                    </h5>
                    <p class="text-muted" style="font-size:13px; margin-top:4px;">
                        El inquilino recibirá una notificación con el motivo.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/solicitudes/rechazar">
                <input type="hidden" name="reserva_id" value="<?php echo $solicitud['reserva_id']; ?>">
                <div class="modal-body px-4 py-3">
                    <label class="form-label fw-semibold" style="font-size:13.5px;">
                        Motivo del rechazo <span style="color:var(--red)">*</span>
                    </label>
                    <textarea 
                        id="observacion-rechazo"
                        name="observacion" 
                        class="form-control" 
                        rows="4" 
                        placeholder="Ej: El cuarto ya fue reservado por otro inquilino, o el perfil no cumple con los requisitos..."
                        style="border-radius:12px; font-size:13.5px; border-color: var(--line); resize:none;"
                        required
                    ></textarea>
                    <div style="font-size:11.5px; color:var(--ink-faint); margin-top:8px;">
                        <i class="fas fa-info-circle"></i> Este mensaje será visible para el inquilino.
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-ghost btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm" style="background:var(--red);color:#fff;">
                        <i class="fas fa-times"></i> Confirmar rechazo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function abrirModalRechazo() {
    const modal = new bootstrap.Modal(document.getElementById('modalRechazo'));
    modal.show();
}

function confirmarAprobacion() {
    Swal.fire({
        title: '¿Aprobar esta solicitud?',
        html: `
            <p style="font-size:14px; color:#5B5F6B; margin:0;">
                Al aprobar, la solicitud avanzará para iniciar el proceso de contrato.<br>
                <strong>Esta acción notificará al inquilino.</strong>
            </p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16A34A',
        cancelButtonColor: '#9499A3',
        confirmButtonText: '<i class="fas fa-check"></i> Sí, aprobar',
        cancelButtonText: 'Cancelar',
        customClass: { popup: 'rounded-4' }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form-aprobar').submit();
        }
    });
}
</script>
