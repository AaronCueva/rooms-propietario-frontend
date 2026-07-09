<div class="page-content">
    <div class="page-head mb-4 d-flex justify-content-between align-items-end flex-wrap gap-3">
        <div>
            <div class="eyebrow">Directorio</div>
            <h1 style="font-size: 24px; font-family: var(--font-display); font-weight: 700; margin: 0; color: var(--ink);">Mis Inquilinos</h1>
            <div class="text-muted mt-1" style="font-size: 14px;">Gestiona a las personas que alquilan o alquilaron tus alojamientos.</div>
        </div>
    </div>

    <!-- Tabs (Filtros de estado) -->
    <div class="nav-tabs-custom mb-4" style="border-bottom: 1px solid var(--line); display:flex; gap:20px; overflow-x:auto;">
        <?php 
        $tabs = [
            ['id' => 'ACTIVO', 'label' => 'Vigentes'],
            ['id' => 'HISTORIAL', 'label' => 'Historial']
        ];
        foreach ($tabs as $t): 
            $isActive = ($tab == $t['id']) ? 'active' : '';
            $url = "/inquilinos?tab={$t['id']}";
        ?>
            <a href="<?php echo $url; ?>" class="nav-link-custom <?php echo $isActive; ?>" style="text-decoration:none; padding:10px 0; color:var(--ink-faint); position:relative; font-weight:500;">
                <?php echo $t['label']; ?>
                <?php if ($isActive): ?>
                    <div style="position:absolute; bottom:-1px; left:0; width:100%; height:2px; background:var(--primary); border-radius:2px 2px 0 0;"></div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($inquilinos)): ?>
        <div class="text-center p-5" style="background:#fff; border-radius:16px; border:1px dashed var(--line);">
            <div style="width:64px; height:64px; border-radius:50%; background:var(--blue-wash); color:var(--blue); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 16px;">
                <i class="fas fa-users"></i>
            </div>
            <h5 style="font-family:var(--font-display); font-weight:600;">No hay inquilinos aquí</h5>
            <p class="text-muted mb-4" style="font-size:14px; max-width:400px; margin:0 auto;">
                Aún no tienes inquilinos en esta categoría. Formaliza un contrato para verlos en tu lista de vigentes.
            </p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($inquilinos as $inq): 
                $nombres = htmlspecialchars(trim(($inq['inquilino_nombres'] ?? '') . ' ' . ($inq['inquilino_apellido_paterno'] ?? '')));
                $iniciales = strtoupper(substr($inq['inquilino_nombres'] ?? 'I', 0, 1) . substr($inq['inquilino_apellido_paterno'] ?? 'N', 0, 1));
                
                $calificacion = number_format($inq['inquilino_calificacion'] ?? 0, 1);
                $total_calificaciones = $inq['inquilino_total_calificaciones'] ?? 0;
            ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 sol-card" style="border-radius:16px; border:1px solid var(--line); transition:all 0.2s;">
                        <div class="card-body p-4 d-flex flex-column">
                            
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="avatar position-relative" style="width:50px; height:50px; border-radius:50%; background:var(--blue-wash); color:var(--blue); display:flex; align-items:center; justify-content:center; font-weight:600; font-size:18px; overflow:hidden;">
                                    <?php if (!empty($inq['inquilino_foto'])): ?>
                                        <img src="<?php echo htmlspecialchars($inq['inquilino_foto']); ?>" alt="Foto" style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        <?php echo $iniciales; ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div style="font-weight:700; font-size:15px; color:var(--ink);"><?php echo $nombres; ?></div>
                                    <div class="d-flex align-items-center gap-1 mt-1">
                                        <i class="fas fa-star text-warning" style="font-size: 11px;"></i>
                                        <span style="font-size: 13px; font-weight: 600; color: var(--ink);"><?php echo $calificacion; ?></span>
                                        <span class="text-muted" style="font-size: 12px;">(<?php echo $total_calificaciones; ?>)</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 mb-3" style="background: var(--gray-50); border-radius: 12px; border: 1px solid var(--line);">
                                <div style="font-size:11px; font-weight:600; color:var(--ink-faint); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Alojamiento</div>
                                <div style="font-size:14px; font-weight:600; color:var(--primary);" class="text-truncate">
                                    <i class="fas fa-home me-1"></i> <?php echo htmlspecialchars($inq['alojamiento_titulo']); ?>
                                </div>
                                <div class="mt-2 text-muted d-flex justify-content-between align-items-center" style="font-size:12px;">
                                    <span>Desde: <?php echo date('d/m/Y', strtotime($inq['fecha_inicio'])); ?></span>
                                    <?php if ($tab === 'ACTIVO'): ?>
                                        <span class="badge badge-green" style="font-size:10px;">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-faint" style="font-size:10px;">Finalizado</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mt-auto d-grid">
                                <a href="/inquilinos/detalle?id=<?php echo $inq['contrato_id']; ?>" class="btn btn-light btn-sm" style="border-radius:8px;">
                                    Ver perfil completo
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.nav-link-custom.active { color: var(--primary) !important; font-weight: 600 !important; }
.sol-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); }
</style>
