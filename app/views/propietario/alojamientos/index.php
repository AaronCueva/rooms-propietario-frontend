<div class="page-content">
    <div class="page-head">
        <div>
            <div class="eyebrow">Mis alojamientos</div>
            <h1>Tus cuartos publicados</h1>
            <div class="sub">Gestiona el estado, precio y detalles de cada uno.</div>
        </div>
        <a href="/alojamientos/nuevo" class="btn btn-primary"><i class="fas fa-plus" style="font-size:13px;"></i> Agregar cuarto</a>
    </div>

    <div class="prop-grid">

        <?php if (!empty($alojamientos)): ?>
            <?php 
            $gradients = [
                'linear-gradient(135deg, var(--blue), #7BA0F6)',
                'linear-gradient(135deg, var(--teal), #4FCBDA)',
                'linear-gradient(135deg, var(--amber), #F0AE5C)',
                'linear-gradient(135deg, var(--purple), #A78BFA)',
                'linear-gradient(135deg, var(--green), #5FCB89)',
            ];
            ?>
            <?php foreach ($alojamientos as $i => $aloj): ?>
                <div class="prop-card">
                    <div class="prop-thumb" style="<?php 
                        if (!empty($aloj['foto_principal'])): 
                            ?>background-image:url('<?php echo htmlspecialchars($aloj['foto_principal']); ?>');background-size:cover;background-position:center;<?php 
                        else: 
                            echo $gradients[$i % count($gradients)]; 
                        endif; 
                    ?>">
                        <?php if (empty($aloj['foto_principal'])): ?>
                            <i class="fas fa-home" style="font-size:38px;color:#fff;opacity:.7;"></i>
                        <?php endif; ?>
                        <div class="status-dot">
                            <?php
                            $estado = $aloj['estado_codigo'] ?? 'EPA001';
                            if ($estado === 'EPA001' || $estado === 'EPA003'): ?>
                                <span class="badge badge-green">Disponible</span>
                            <?php elseif ($estado === 'EPA004'): ?>
                                <span class="badge badge-amber">Ocupado</span>
                            <?php else: ?>
                                <span class="badge badge-red">Inactivo</span>
                            <?php endif; ?>
                            <?php if (!empty($aloj['total_favoritos']) && $aloj['total_favoritos'] > 0): ?>
                                <span class="badge" style="background: rgba(255, 255, 255, 0.9); color: var(--red); box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-weight: 600; margin-left: 4px;">
                                    <i class="fas fa-heart"></i> <?php echo $aloj['total_favoritos']; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="prop-body">
                        <div class="prop-title"><?php echo htmlspecialchars($aloj['titulo']); ?></div>
                        <div class="prop-sub">
                            <i class="fas fa-map-marker-alt" style="font-size:11px;"></i>
                            <?php echo htmlspecialchars($aloj['direccion'] ?? 'Sin dirección'); ?>
                            <?php if (!empty($aloj['distrito_nombre'])): ?>
                                · <?php echo htmlspecialchars($aloj['distrito_nombre']); ?>
                            <?php endif; ?>
                        </div>
                        <div class="prop-stats">
                            <div class="ps-item">
                                <div class="ps-label">Renta mensual</div>
                                <div class="ps-val mono">S/ <?php echo number_format($aloj['precio_mensual'], 0, '.', ','); ?></div>
                            </div>
                            <div class="ps-item">
                                <div class="ps-label">Habitaciones</div>
                                <div class="ps-val"><?php echo $aloj['numero_habitaciones'] ?? '-'; ?></div>
                            </div>
                            <div class="ps-item">
                                <div class="ps-label">Baños</div>
                                <div class="ps-val"><?php echo $aloj['numero_banos'] ?? '-'; ?></div>
                            </div>
                            <div class="ps-item">
                                <div class="ps-label">Tamaño</div>
                                <div class="ps-val"><?php echo $aloj['tamano_m2'] ? $aloj['tamano_m2'] . ' m²' : '-'; ?></div>
                            </div>
                        </div>
                        <div class="prop-actions">
                            <a href="/alojamientos/editar?id=<?php echo $aloj['alojamiento_id']; ?>" class="btn btn-dark btn-sm">Editar / Gestionar</a>
                            <form method="POST" action="/alojamientos/eliminar" id="form-delete-<?php echo $aloj['alojamiento_id']; ?>" style="display:inline;">
                                <input type="hidden" name="alojamiento_id" value="<?php echo $aloj['alojamiento_id']; ?>">
                                <button type="button" class="btn btn-ghost btn-sm" style="color:var(--red);border-color:var(--red-wash);" onclick="confirmarEliminacion('<?php echo $aloj['alojamiento_id']; ?>')">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Tarjeta para agregar nuevo -->
        <a href="/alojamientos/nuevo" class="prop-card prop-card-add">
            <div style="text-align:center;padding:30px;">
                <div class="add-icon-box">
                    <i class="fas fa-plus" style="color:var(--red);font-size:20px;"></i>
                </div>
                <div style="font-weight:800;font-size:14.5px;margin-bottom:4px;">Publicar nuevo cuarto</div>
                <div style="font-size:12.5px;color:var(--ink-faint);margin-bottom:16px;">Tarda menos de 3 minutos</div>
                <span class="btn btn-primary btn-sm">Empezar publicación</span>
            </div>
        </a>

    </div>
</div>

<script>
    function confirmarEliminacion(id) {
        Swal.fire({
            title: '¿Eliminar cuarto?',
            text: "Esta acción no se puede deshacer y el cuarto dejará de estar visible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'var(--red)',
            cancelButtonColor: 'var(--ink-faint)',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-delete-' + id).submit();
            }
        });
    }
</script>
