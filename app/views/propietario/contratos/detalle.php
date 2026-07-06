<?php
$estado = $contrato['estado_codigo'];
$badge_map = [
    'ESCO001' => ['label' => 'Activo',     'class' => 'badge-green', 'icon' => 'fa-check-circle'],
    'ESCO002' => ['label' => 'Finalizado', 'class' => 'badge-faint', 'icon' => 'fa-flag-checkered'],
    'ESCO003' => ['label' => 'Cancelado',  'class' => 'badge-red',   'icon' => 'fa-times-circle'],
];
$badge = $badge_map[$estado] ?? ['label' => $estado, 'class' => 'badge-faint', 'icon' => 'fa-circle'];

$nombres_inquilino = htmlspecialchars(trim(($contrato['inquilino_nombres'] ?? '') . ' ' . ($contrato['inquilino_apellido_paterno'] ?? '') . ' ' . ($contrato['inquilino_apellido_materno'] ?? '')));
$iniciales = strtoupper(substr($contrato['inquilino_nombres'] ?? 'I', 0, 1) . substr($contrato['inquilino_apellido_paterno'] ?? 'N', 0, 1));
?>

<div class="page-content">
    <div class="sol-breadcrumb mb-3">
        <a href="/contratos"><i class="fas fa-chevron-left"></i> Contratos</a>
        <span>/ Detalle</span>
    </div>

    <div class="page-head mb-4 d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <div class="eyebrow">Alojamiento: <?php echo htmlspecialchars($contrato['alojamiento_titulo']); ?></div>
            <h1 style="font-size: 24px; font-family: var(--font-display); font-weight: 700; margin: 0; color: var(--ink);">
                Contrato #<?php echo substr($contrato['contrato_id'], 0, 8); ?>
            </h1>
            <div class="mt-2 d-flex align-items-center gap-2">
                <span class="badge <?php echo $badge['class']; ?> d-inline-flex align-items-center gap-1 px-2 py-1" style="border-radius:6px; font-size:11.5px; font-weight:600;">
                    <i class="fas <?php echo $badge['icon']; ?>"></i> <?php echo $badge['label']; ?>
                </span>
                <span class="text-muted" style="font-size: 13px;">Registrado el <?php echo date('d/m/Y', strtotime($contrato['creado'])); ?></span>
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <?php if (!empty($contrato['documento_url'])): ?>
                <a href="<?php echo htmlspecialchars($contrato['documento_url']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-file-pdf"></i> Ver Documento
                </a>
            <?php endif; ?>
            
            <?php if ($estado === 'ESCO001'): ?>
                <button class="btn btn-dark btn-sm" onclick="confirmarFinalizacion()">
                    <i class="fas fa-flag-checkered"></i> Finalizar Contrato
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna Izquierda -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h6 class="mb-4 pb-3 border-bottom" style="font-family: var(--font-display); font-weight: 700;">
                        <i class="fas fa-calendar-alt text-primary me-2"></i> Período del Contrato
                    </h6>
                    <div class="row g-4 mb-4">
                        <div class="col-sm-4">
                            <div class="text-muted" style="font-size: 13px; margin-bottom:4px;">Fecha de Inicio</div>
                            <div style="font-weight: 600; color: var(--ink);"><?php echo date('d \d\e M Y', strtotime($contrato['fecha_inicio'])); ?></div>
                        </div>
                        <div class="col-sm-4">
                            <div class="text-muted" style="font-size: 13px; margin-bottom:4px;">Fecha de Fin</div>
                            <div style="font-weight: 600; color: var(--ink);"><?php echo date('d \d\e M Y', strtotime($contrato['fecha_fin'])); ?></div>
                        </div>
                        <div class="col-sm-4">
                            <div class="text-muted" style="font-size: 13px; margin-bottom:4px;">Día de pago</div>
                            <div style="font-weight: 600; color: var(--ink);">Los días <?php echo $contrato['fecha_pago_mensual']; ?> de cada mes</div>
                        </div>
                    </div>

                    <h6 class="mb-4 mt-5 pb-3 border-bottom" style="font-family: var(--font-display); font-weight: 700;">
                        <i class="fas fa-money-bill-wave text-success me-2"></i> Detalles Financieros
                    </h6>
                    <div class="row g-4 mb-2">
                        <div class="col-sm-6">
                            <div class="p-3" style="background: var(--gray-50); border-radius: 12px; border: 1px solid var(--line);">
                                <div class="text-muted" style="font-size: 13px; margin-bottom:4px;">Renta Mensual</div>
                                <div style="font-family: var(--font-mono); font-size: 18px; font-weight: 700; color: var(--primary);">
                                    S/ <?php echo number_format($contrato['monto_renta'], 2, '.', ','); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3" style="background: var(--gray-50); border-radius: 12px; border: 1px solid var(--line);">
                                <div class="text-muted" style="font-size: 13px; margin-bottom:4px;">Monto Garantía</div>
                                <div style="font-family: var(--font-mono); font-size: 18px; font-weight: 700; color: var(--ink);">
                                    S/ <?php echo number_format($contrato['monto_garantia'], 2, '.', ','); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($estado === 'ESCO002'): ?>
                <!-- Panel de Calificación si está finalizado -->
                <div class="card border-0 shadow-sm mt-4" style="border-radius: 16px; border: 1px solid var(--line);">
                    <div class="card-body p-4 text-center">
                        <div style="width:56px; height:56px; border-radius:50%; background:var(--amber-wash); color:var(--amber); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 16px;">
                            <i class="fas fa-star"></i>
                        </div>
                        
                        <?php if (empty($contrato['propietario_califico'])): ?>
                            <h5 style="font-family:var(--font-display); font-weight:600;">Califica al Inquilino</h5>
                            <p class="text-muted mb-4" style="font-size:14px; max-width:400px; margin:0 auto;">
                                El contrato ha finalizado. Por favor, deja una puntuación sobre tu experiencia con <strong><?php echo $nombres_inquilino; ?></strong>.
                            </p>
                            
                            <form action="/contratos/calificar" method="POST" id="form-calificar">
                                <input type="hidden" name="contrato_id" value="<?php echo $contrato['contrato_id']; ?>">
                                <div class="rating-stars mb-4" style="font-size: 32px; color: #D1D5DB; cursor: pointer; display: flex; justify-content: center; gap: 8px; flex-direction: row-reverse;">
                                    <input type="radio" id="star5" name="puntuacion" value="5" class="d-none" required />
                                    <label for="star5" title="5 estrellas" style="cursor:pointer;"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" id="star4" name="puntuacion" value="4" class="d-none" />
                                    <label for="star4" title="4 estrellas" style="cursor:pointer;"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" id="star3" name="puntuacion" value="3" class="d-none" />
                                    <label for="star3" title="3 estrellas" style="cursor:pointer;"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" id="star2" name="puntuacion" value="2" class="d-none" />
                                    <label for="star2" title="2 estrellas" style="cursor:pointer;"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" id="star1" name="puntuacion" value="1" class="d-none" />
                                    <label for="star1" title="1 estrella" style="cursor:pointer;"><i class="fas fa-star"></i></label>
                                </div>
                                <button type="submit" class="btn btn-warning" style="color:#854D0E; font-weight:600; padding:10px 24px;">Enviar Calificación</button>
                            </form>
                            
                            <style>
                                .rating-stars label { transition: color 0.2s; }
                                .rating-stars label:hover,
                                .rating-stars label:hover ~ label,
                                .rating-stars input:checked ~ label {
                                    color: #F59E0B !important;
                                }
                            </style>
                        <?php else: ?>
                            <h5 style="font-family:var(--font-display); font-weight:600; color: var(--green);">¡Gracias por calificar!</h5>
                            <p class="text-muted mb-0" style="font-size:14px; max-width:400px; margin:0 auto;">
                                Ya has enviado tu calificación para <strong><?php echo $nombres_inquilino; ?></strong> en este contrato.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Columna Derecha -->
        <div class="col-lg-4">
            <!-- Card Inquilino -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body p-4 text-center">
                    <div style="width:80px; height:80px; border-radius:50%; background:var(--blue-wash); color:var(--blue); display:flex; align-items:center; justify-content:center; font-weight:600; font-size:28px; overflow:hidden; margin: 0 auto 16px;">
                        <?php if (!empty($contrato['inquilino_foto'])): ?>
                            <img src="<?php echo htmlspecialchars($contrato['inquilino_foto']); ?>" alt="Foto" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <?php echo $iniciales; ?>
                        <?php endif; ?>
                    </div>
                    <h6 style="font-weight: 700; color: var(--ink); margin-bottom: 4px;"><?php echo $nombres_inquilino; ?></h6>
                    <div class="badge badge-faint mb-3">Inquilino</div>
                    
                    <div class="text-start mt-3 pt-3 border-top">
                        <?php if (!empty($contrato['inquilino_correo'])): ?>
                            <div class="d-flex align-items-center gap-2 mb-2 text-muted" style="font-size: 13px;">
                                <i class="fas fa-envelope fa-fw"></i> <?php echo htmlspecialchars($contrato['inquilino_correo']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($contrato['inquilino_celular'])): ?>
                            <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 13px;">
                                <i class="fas fa-phone fa-fw"></i> <?php echo htmlspecialchars($contrato['inquilino_celular']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Card Alojamiento -->
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h6 style="font-weight: 700; color: var(--ink); margin-bottom: 12px;">Alojamiento</h6>
                    <div class="d-flex align-items-start gap-3">
                        <div style="width:40px; height:40px; border-radius:8px; background:var(--gray-100); display:flex; align-items:center; justify-content:center; color:var(--ink-faint); flex-shrink:0;">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 14px; margin-bottom: 4px;"><?php echo htmlspecialchars($contrato['alojamiento_titulo']); ?></div>
                            <div class="text-muted" style="font-size: 13px; line-height: 1.4;">
                                <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($contrato['alojamiento_direccion']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($estado === 'ESCO001'): ?>
<form id="form-finalizar" action="/contratos/finalizar" method="POST" style="display:none;">
    <input type="hidden" name="contrato_id" value="<?php echo $contrato['contrato_id']; ?>">
</form>

<script>
function confirmarFinalizacion() {
    Swal.fire({
        title: '¿Finalizar Contrato?',
        html: '<p style="font-size:14px; color:#5B5F6B;">Al finalizar, el estado de tu alojamiento cambiará automáticamente a <strong>Activo</strong> (disponible de nuevo).</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1F2937',
        cancelButtonColor: '#9499A3',
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Cancelar',
        customClass: { popup: 'rounded-4' }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form-finalizar').submit();
        }
    });
}
</script>
<?php endif; ?>
