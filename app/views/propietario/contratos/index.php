<div class="page-content">
    <div class="page-head mb-4 d-flex justify-content-between align-items-end flex-wrap gap-3">
        <div>
            <div class="eyebrow">Gestión</div>
            <h1 style="font-size: 24px; font-family: var(--font-display); font-weight: 700; margin: 0; color: var(--ink);">Mis Contratos</h1>
            <div class="text-muted mt-1" style="font-size: 14px;">Administra los contratos activos y finalizados de tus alojamientos.</div>
        </div>
    </div>

    <!-- Tabs (Filtros de estado) -->
    <div class="nav-tabs-custom mb-4" style="border-bottom: 1px solid var(--line); display:flex; gap:20px; overflow-x:auto;">
        <?php 
        $tabs = [
            ['id' => null, 'label' => 'Todos'],
            ['id' => 'ESCO001', 'label' => 'Activos'],
            ['id' => 'ESCO002', 'label' => 'Finalizados'],
            ['id' => 'ESCO003', 'label' => 'Cancelados']
        ];
        foreach ($tabs as $tab): 
            $isActive = ($estado_filtro == $tab['id']) ? 'active' : '';
            $url = $tab['id'] ? "/contratos?estado={$tab['id']}" : "/contratos";
        ?>
            <a href="<?php echo $url; ?>" class="nav-link-custom <?php echo $isActive; ?>" style="text-decoration:none; padding:10px 0; color:var(--ink-faint); position:relative; font-weight:500;">
                <?php echo $tab['label']; ?>
                <?php if ($isActive): ?>
                    <div style="position:absolute; bottom:-1px; left:0; width:100%; height:2px; background:var(--primary); border-radius:2px 2px 0 0;"></div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($contratos)): ?>
        <div class="text-center p-5" style="background:#fff; border-radius:16px; border:1px dashed var(--line);">
            <div style="width:64px; height:64px; border-radius:50%; background:var(--blue-wash); color:var(--blue); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 16px;">
                <i class="fas fa-file-contract"></i>
            </div>
            <h5 style="font-family:var(--font-display); font-weight:600;">No tienes contratos aquí</h5>
            <p class="text-muted mb-4" style="font-size:14px; max-width:400px; margin:0 auto;">
                Cuando apruebes una solicitud de reserva y formalices el contrato, aparecerá en esta lista.
            </p>
            <a href="/solicitudes" class="btn btn-primary">Ir a Solicitudes</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($contratos as $contrato): 
                $estado = $contrato['estado_codigo'];
                
                $badge_map = [
                    'ESCO001' => ['label' => 'Activo', 'class' => 'badge-green', 'icon' => 'fa-check-circle'],
                    'ESCO002' => ['label' => 'Finalizado', 'class' => 'badge-faint', 'icon' => 'fa-flag-checkered'],
                    'ESCO003' => ['label' => 'Cancelado', 'class' => 'badge-red', 'icon' => 'fa-times-circle'],
                ];
                
                $badge = $badge_map[$estado] ?? ['label' => $estado, 'class' => 'badge-faint', 'icon' => 'fa-circle'];
                
                $nombres = htmlspecialchars(trim(($contrato['inquilino_nombres'] ?? '') . ' ' . ($contrato['inquilino_apellido_paterno'] ?? '')));
                $iniciales = strtoupper(substr($contrato['inquilino_nombres'] ?? 'I', 0, 1) . substr($contrato['inquilino_apellido_paterno'] ?? 'N', 0, 1));
            ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 sol-card" style="border-radius:16px; border:1px solid var(--line); transition:all 0.2s;">
                        <div class="card-body p-4 d-flex flex-column">
                            
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge <?php echo $badge['class']; ?> d-inline-flex align-items-center gap-1 px-2 py-1" style="border-radius:6px; font-size:11px; font-weight:600;">
                                    <i class="fas <?php echo $badge['icon']; ?>"></i> <?php echo $badge['label']; ?>
                                </span>
                                <span class="text-muted" style="font-size:12px;">Inicio: <?php echo date('d/m/Y', strtotime($contrato['fecha_inicio'])); ?></span>
                            </div>

                            <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                                <div class="avatar" style="width:40px; height:40px; border-radius:50%; background:var(--blue-wash); color:var(--blue); display:flex; align-items:center; justify-content:center; font-weight:600; font-size:14px; overflow:hidden;">
                                    <?php if (!empty($contrato['inquilino_foto'])): ?>
                                        <img src="<?php echo htmlspecialchars($contrato['inquilino_foto']); ?>" alt="Foto" style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        <?php echo $iniciales; ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div style="font-weight:600; font-size:14px; color:var(--ink);"><?php echo $nombres; ?></div>
                                    <div class="text-muted" style="font-size:12px;">Inquilino</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div style="font-size:14px; font-weight:600; color:var(--ink); margin-bottom:4px;" class="text-truncate">
                                    <i class="fas fa-home text-muted me-1"></i> <?php echo htmlspecialchars($contrato['alojamiento_titulo']); ?>
                                </div>
                                <div style="font-size:13px; color:var(--ink-faint);" class="text-truncate">
                                    <i class="fas fa-map-marker-alt text-muted me-1"></i> <?php echo htmlspecialchars($contrato['alojamiento_direccion']); ?>
                                </div>
                            </div>

                            <div class="mt-auto pt-3 d-flex justify-content-between align-items-center">
                                <div style="font-family:var(--font-mono); font-size:15px; font-weight:600; color:var(--primary);">
                                    S/ <?php echo number_format($contrato['total_pagar'], 0, '.', ','); ?> <span style="font-size:11px; color:var(--ink-faint); font-weight:400; font-family:var(--font-sans);">/mes</span>
                                </div>
                                <a href="/contratos/detalle?id=<?php echo $contrato['contrato_id']; ?>" class="btn btn-light btn-sm" style="border-radius:8px;">Ver detalle</a>
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
