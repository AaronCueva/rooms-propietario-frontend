<?php
$nombres_inquilino = htmlspecialchars(trim(($detalle['inquilino_nombres'] ?? '') . ' ' . ($detalle['inquilino_apellido_paterno'] ?? '') . ' ' . ($detalle['inquilino_apellido_materno'] ?? '')));
$iniciales = strtoupper(substr($detalle['inquilino_nombres'] ?? 'I', 0, 1) . substr($detalle['inquilino_apellido_paterno'] ?? 'N', 0, 1));
$calificacion = number_format($detalle['inquilino_calificacion'] ?? 0, 1);
$total_calificaciones = $detalle['inquilino_total_calificaciones'] ?? 0;
$estado_contrato = $detalle['estado_codigo'];
?>

<div class="page-content">
    <div class="sol-breadcrumb mb-3">
        <a href="/inquilinos"><i class="fas fa-chevron-left"></i> Mis Inquilinos</a>
        <span>/ Perfil</span>
    </div>

    <div class="row g-4 mt-2">
        <!-- Lado Izquierdo: Perfil Inquilino -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body p-4 text-center">
                    <div style="width:100px; height:100px; border-radius:50%; background:var(--blue-wash); color:var(--blue); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:36px; overflow:hidden; margin: 0 auto 16px; border: 4px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                        <?php if (!empty($detalle['inquilino_foto'])): ?>
                            <img src="<?php echo htmlspecialchars($detalle['inquilino_foto']); ?>" alt="Foto" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <?php echo $iniciales; ?>
                        <?php endif; ?>
                    </div>
                    
                    <h5 style="font-weight: 700; color: var(--ink); margin-bottom: 4px; font-family: var(--font-display);"><?php echo $nombres_inquilino; ?></h5>
                    
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-4">
                        <i class="fas fa-star text-warning"></i>
                        <span style="font-size: 15px; font-weight: 700; color: var(--ink);"><?php echo $calificacion; ?></span>
                        <span class="text-muted" style="font-size: 13px;">(<?php echo $total_calificaciones; ?> reseñas)</span>
                    </div>
                    
                    <div class="text-start border-top pt-4">
                        <h6 style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--ink-faint); margin-bottom: 12px; font-weight: 700;">Información de Contacto</h6>
                        
                        <?php if (!empty($detalle['inquilino_correo'])): ?>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width:32px; height:32px; border-radius:8px; background:var(--gray-100); display:flex; align-items:center; justify-content:center; color:var(--ink-faint);">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div style="font-size: 13.5px; color: var(--ink); font-weight: 500;">
                                    <?php echo htmlspecialchars($detalle['inquilino_correo']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($detalle['inquilino_celular'])): ?>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width:32px; height:32px; border-radius:8px; background:var(--gray-100); display:flex; align-items:center; justify-content:center; color:var(--ink-faint);">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div style="font-size: 13.5px; color: var(--ink); font-weight: 500;">
                                    <?php echo htmlspecialchars($detalle['inquilino_celular']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($detalle['inquilino_documento'])): ?>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width:32px; height:32px; border-radius:8px; background:var(--gray-100); display:flex; align-items:center; justify-content:center; color:var(--ink-faint);">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div style="font-size: 13.5px; color: var(--ink); font-weight: 500;">
                                    DNI: <?php echo htmlspecialchars($detalle['inquilino_documento']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($detalle['universidad_nombre'])): ?>
                            <div class="d-flex align-items-center gap-3">
                                <div style="width:32px; height:32px; border-radius:8px; background:var(--blue-wash); display:flex; align-items:center; justify-content:center; color:var(--blue);">
                                    <i class="fas fa-university"></i>
                                </div>
                                <div style="font-size: 13.5px; color: var(--ink); font-weight: 500;">
                                    <?php echo htmlspecialchars($detalle['universidad_nombre']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lado Derecho: Contrato y Acciones -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4 pb-3 border-bottom">
                        <div>
                            <h6 style="font-family: var(--font-display); font-weight: 700; color: var(--ink); margin-bottom: 4px;">
                                <i class="fas fa-file-contract text-primary me-2"></i> Contrato Asociado
                            </h6>
                            <div class="text-muted" style="font-size: 13px;">Contrato #<?php echo substr($detalle['contrato_id'], 0, 8); ?></div>
                        </div>
                        <?php if ($estado_contrato === 'ESCO001'): ?>
                            <span class="badge badge-green px-3 py-2" style="border-radius: 8px; font-weight: 600;"><i class="fas fa-check-circle me-1"></i> Vigente</span>
                        <?php else: ?>
                            <span class="badge badge-faint px-3 py-2" style="border-radius: 8px; font-weight: 600;"><i class="fas fa-flag-checkered me-1"></i> Finalizado</span>
                        <?php endif; ?>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="p-3" style="background: var(--gray-50); border-radius: 12px; border: 1px solid var(--line);">
                                <div style="font-size:11px; font-weight:700; color:var(--ink-faint); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">Alojamiento</div>
                                <div style="font-size:14px; font-weight:600; color:var(--ink); margin-bottom: 2px;">
                                    <?php echo htmlspecialchars($detalle['alojamiento_titulo']); ?>
                                </div>
                                <div class="text-muted" style="font-size: 12.5px;">
                                    <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($detalle['alojamiento_direccion']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3" style="background: var(--gray-50); border-radius: 12px; border: 1px solid var(--line); height: 100%;">
                                <?php 
                                $total_servicios = 0;
                                if (!empty($servicios)) {
                                    foreach ($servicios as $srv) {
                                        $total_servicios += floatval($srv['precio'] ?? 0);
                                    }
                                }
                                $total_pagar = floatval($detalle['monto_renta']) + $total_servicios;
                                ?>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted" style="font-size: 13px;">Renta Base:</span>
                                    <span style="font-family: var(--font-mono); font-size: 14px; font-weight: 600; color: var(--ink);">S/ <?php echo number_format($detalle['monto_renta'], 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="text-muted" style="font-size: 13px;">Servicios:</span>
                                    <span style="font-family: var(--font-mono); font-size: 14px; font-weight: 600; color: var(--ink);">S/ <?php echo number_format($total_servicios, 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted" style="font-size: 13px; font-weight: 600;">Total Mes:</span>
                                    <span style="font-family: var(--font-mono); font-size: 16px; font-weight: 700; color: var(--primary);">S/ <?php echo number_format($total_pagar, 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted" style="font-size: 13px;">Garantía:</span>
                                    <span style="font-family: var(--font-mono); font-size: 15px; font-weight: 600; color: var(--ink);">S/ <?php echo number_format($detalle['monto_garantia'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-4 mb-5">
                        <div class="col-sm-4">
                            <div class="text-muted" style="font-size: 13px; margin-bottom:4px;">Inicio de contrato</div>
                            <div style="font-weight: 600; color: var(--ink);"><?php echo date('d \d\e M Y', strtotime($detalle['fecha_inicio'])); ?></div>
                        </div>
                        <div class="col-sm-4">
                            <div class="text-muted" style="font-size: 13px; margin-bottom:4px;">Fin de contrato</div>
                            <div style="font-weight: 600; color: var(--ink);"><?php echo date('d \d\e M Y', strtotime($detalle['fecha_fin'])); ?></div>
                        </div>
                        <div class="col-sm-4">
                            <div class="text-muted" style="font-size: 13px; margin-bottom:4px;">Documento</div>
                            <a href="<?php echo htmlspecialchars($detalle['documento_url'] ?? '#'); ?>" target="_blank" class="btn btn-outline-primary btn-sm" style="border-radius: 6px;">
                                <i class="fas fa-file-pdf"></i> Ver PDF
                            </a>
                        </div>
                    </div>

                    <?php if ($estado_contrato === 'ESCO001'): ?>
                        <!-- ACCIONES SI EL CONTRATO ESTÁ ACTIVO -->
                        <div class="border-top pt-4 text-end">
                            <form action="/contratos/finalizar" method="POST" id="form-finalizar">
                                <input type="hidden" name="contrato_id" value="<?php echo $detalle['contrato_id']; ?>">
                                <input type="hidden" name="from_inquilino" value="1">
                                <button type="button" class="btn btn-dark" onclick="confirmarFinalizacion()" style="padding: 10px 24px; font-weight: 600; border-radius: 10px;">
                                    <i class="fas fa-flag-checkered me-2"></i> Finalizar Contrato
                                </button>
                            </form>
                        </div>
                        
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
                        
                    <?php elseif ($estado_contrato === 'ESCO002'): ?>
                        <!-- ACCIONES SI EL CONTRATO ESTÁ FINALIZADO -->
                        <div class="border-top pt-4 text-center">
                            
                            <?php if (empty($detalle['propietario_califico'])): ?>
                                <div style="width:48px; height:48px; border-radius:50%; background:var(--amber-wash); color:var(--amber); display:flex; align-items:center; justify-content:center; font-size:20px; margin:0 auto 16px;">
                                    <i class="fas fa-star"></i>
                                </div>
                                <h6 style="font-family:var(--font-display); font-weight:700;">Califica al Inquilino</h6>
                                <p class="text-muted mb-4" style="font-size:13.5px; max-width:400px; margin:0 auto;">
                                    El contrato ha finalizado. Deja una puntuación sobre tu experiencia con este inquilino.
                                </p>
                                
                                <form action="/contratos/calificar" method="POST">
                                    <input type="hidden" name="contrato_id" value="<?php echo $detalle['contrato_id']; ?>">
                                    <input type="hidden" name="from_inquilino" value="1">
                                    <div class="rating-stars mb-4" style="font-size: 28px; color: #D1D5DB; cursor: pointer; display: flex; justify-content: center; gap: 8px; flex-direction: row-reverse;">
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
                                    <button type="submit" class="btn btn-warning" style="color:#854D0E; font-weight:600; padding:10px 24px; border-radius: 10px;">Enviar Calificación</button>
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
                                <div style="display:inline-flex; align-items:center; gap:10px; padding: 12px 20px; background: var(--green-wash); color: var(--green); border-radius: 12px;">
                                    <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                                    <span style="font-weight: 600; font-size: 14.5px;">Ya has calificado a este inquilino.</span>
                                </div>
                            <?php endif; ?>
                            
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>
