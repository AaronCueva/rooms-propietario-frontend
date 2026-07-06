<div class="page-content">
    <div class="page-head">
        <div>
            <div class="eyebrow">Gestión de solicitudes</div>
            <h1>Solicitudes de reserva</h1>
            <div class="sub">Revisa y gestiona las solicitudes de tus inquilinos.</div>
        </div>
    </div>

    <!-- FILTROS POR ESTADO -->
    <div class="sol-filters">
        <?php
        $estados_labels = [
            'TODOS'   => ['label' => 'Todos',       'icon' => 'fa-list',             'color' => ''],
            'ESRE001' => ['label' => 'Pendientes',   'icon' => 'fa-clock',            'color' => 'amber'],
            'ESRE005' => ['label' => 'En revisión',  'icon' => 'fa-eye',              'color' => 'blue'],
            'ESRE002' => ['label' => 'Aprobadas',    'icon' => 'fa-check-circle',     'color' => 'green'],
            'ESRE003' => ['label' => 'Rechazadas',   'icon' => 'fa-times-circle',     'color' => 'red'],
            'ESRE004' => ['label' => 'Finalizadas',  'icon' => 'fa-hourglass-end',    'color' => 'faint'],
        ];
        foreach ($estados_labels as $codigo => $meta):
            $activo = ($filtro_estado === $codigo);
            $count  = $conteos[$codigo] ?? 0;
        ?>
        <a href="/solicitudes<?php echo $codigo !== 'TODOS' ? '?estado=' . $codigo : ''; ?>"
           class="sol-filter-btn <?php echo $activo ? 'active color-' . $meta['color'] : ''; ?>">
            <i class="fas <?php echo $meta['icon']; ?>"></i>
            <?php echo $meta['label']; ?>
            <?php if ($count > 0): ?>
                <span class="sol-filter-count"><?php echo $count; ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- LISTADO -->
    <?php if (empty($solicitudes)): ?>
        <div class="sol-empty">
            <div class="sol-empty-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <div class="sol-empty-title">Sin solicitudes</div>
            <div class="sol-empty-sub">
                <?php if ($filtro_estado === 'TODOS'): ?>
                    Aún no has recibido solicitudes de reserva.
                <?php else: ?>
                    No tienes solicitudes con este estado actualmente.
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="sol-table-wrap card">
            <table class="sol-table">
                <thead>
                    <tr>
                        <th>Inquilino</th>
                        <th>Alojamiento</th>
                        <th>Ingreso / Duración</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Recibida</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes as $sol): ?>
                        <?php
                        // Nombre completo (ya calculado en el modelo)
                        $nombres  = htmlspecialchars($sol['inquilino_nombre_completo']);
                        $initials = strtoupper(
                            substr($sol['inquilino_nombres'] ?? 'I', 0, 1) .
                            substr($sol['inquilino_apellido_paterno'] ?? 'N', 0, 1)
                        );
                        $estado = $sol['estado_codigo'];

                        // Mapeo de estado a badge
                        $badge_map = [
                            'ESRE001' => ['label' => 'Pendiente',   'class' => 'badge-amber',  'icon' => 'fa-clock'],
                            'ESRE002' => ['label' => 'Aprobada',    'class' => 'badge-green',  'icon' => 'fa-check-circle'],
                            'ESRE003' => ['label' => 'Rechazada',   'class' => 'badge-red',    'icon' => 'fa-times-circle'],
                            'ESRE004' => ['label' => 'Finalizada',  'class' => 'badge-faint',  'icon' => 'fa-hourglass-end'],
                            'ESRE005' => ['label' => 'En revisión', 'class' => 'badge-blue',   'icon' => 'fa-eye'],
                        ];
                        $badge = $badge_map[$estado] ?? ['label' => $estado, 'class' => 'badge-faint', 'icon' => 'fa-circle'];

                        // Gradientes para avatar
                        $av_colors = [
                            'linear-gradient(135deg,var(--blue),#7BA0F6)',
                            'linear-gradient(135deg,var(--green),#5FCB89)',
                            'linear-gradient(135deg,var(--purple),#A78BFA)',
                            'linear-gradient(135deg,var(--amber),#F0AE5C)',
                            'linear-gradient(135deg,var(--teal),#4FCBDA)',
                        ];
                        $av_grad = $av_colors[abs(crc32($sol['inquilino_id'] ?? '0')) % count($av_colors)];

                        // Fecha de solicitud
                        $fecha_ref    = $sol['fecha_solicitud'] ?? $sol['creado'];
                        $fecha_creado = $fecha_ref ? date('d/m/Y', strtotime($fecha_ref)) : '—';
                        $hora_creado  = $fecha_ref ? date('H:i', strtotime($fecha_ref))   : '';

                        // Fecha de ingreso y duración
                        $fecha_ingreso = !empty($sol['fecha_ingreso']) ? date('d/m/Y', strtotime($sol['fecha_ingreso'])) : '—';
                        $duracion      = !empty($sol['duracion_meses']) ? $sol['duracion_meses'] . ' mes' . ($sol['duracion_meses'] > 1 ? 'es' : '') : '—';
                        ?>
                        <tr class="sol-row<?php echo ($estado === 'ESRE001') ? ' sol-row-pending' : ''; ?>">
                            <td>
                                <div class="sol-inquilino">
                                    <div class="sol-av" style="background: <?php echo $av_grad; ?>">
                                        <?php echo $initials; ?>
                                    </div>
                                    <div>
                                        <div class="sol-nombre"><?php echo $nombres; ?></div>
                                        <?php if (!empty($sol['universidad_nombre'])): ?>
                                            <div class="sol-univ">
                                                <i class="fas fa-university fa-fw" style="font-size:10px;"></i>
                                                <?php echo htmlspecialchars($sol['universidad_nombre']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="sol-aloj-nombre"><?php echo htmlspecialchars($sol['alojamiento_titulo']); ?></div>
                                <?php if (!empty($sol['distrito_nombre'])): ?>
                                    <div class="sol-aloj-loc">
                                        <i class="fas fa-map-marker-alt" style="font-size:10px;"></i>
                                        <?php echo htmlspecialchars($sol['distrito_nombre']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="sol-periodo"><?php echo $fecha_ingreso; ?></div>
                                <div class="sol-periodo-end"><?php echo $duracion; ?></div>
                            </td>
                            <td>
                                <?php if (!empty($sol['monto_total']) && $sol['monto_total'] > 0): ?>
                                    <div class="sol-precio mono">S/ <?php echo number_format($sol['monto_total'], 0, '.', ','); ?></div>
                                <?php else: ?>
                                    <div class="sol-precio mono">S/ <?php echo number_format($sol['precio_mensual'], 0, '.', ','); ?><span style="font-size:11px;color:var(--ink-faint);font-family:var(--font-body)">/mes</span></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $badge['class']; ?>">
                                    <i class="fas <?php echo $badge['icon']; ?>"></i>
                                    <?php echo $badge['label']; ?>
                                </span>
                                <?php if (!empty($sol['estado_calculado'])): ?>
                                    <div style="font-size:10.5px;color:var(--ink-faint);margin-top:4px;">
                                        <i class="fas fa-info-circle"></i> Sin respuesta
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="sol-fecha"><?php echo $fecha_creado; ?></div>
                                <div class="sol-hora"><?php echo $hora_creado; ?></div>
                            </td>
                            <td>
                                <a href="/solicitudes/detalle?id=<?php echo $sol['reserva_id']; ?>"
                                   class="btn btn-ghost btn-sm sol-ver-btn">
                                    Ver detalle <i class="fas fa-chevron-right" style="font-size:11px;"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
