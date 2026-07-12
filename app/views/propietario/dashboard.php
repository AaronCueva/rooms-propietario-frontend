<div class="page-content">
    <div class="page-head">
        <div>
            <div class="eyebrow">Centro de operaciones</div>
            <h1>Bienvenida de nuevo, <?php echo htmlspecialchars($nombre_usuario); ?></h1>
            <div class="sub">Resumen de tus alojamientos y actividad reciente.</div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="stat-grid">
        <div class="stat-card c-red">
            <div class="stat-icon" style="background:var(--red-wash);">
                <i class="fas fa-building text-danger"></i>
            </div>
            <div class="stat-label">Cuartos activos</div>
            <div class="stat-value"><?php echo $total_cuartos; ?></div>
            <div class="stat-foot"><?php echo $cuartos_ocupados; ?> ocupados · <?php echo $cuartos_disponibles; ?>
                disponible(s)</div>
            <div class="stat-trend trend-up"><i class="fas fa-check"></i> Activos</div>
        </div>
        <div class="stat-card c-green">
            <div class="stat-icon" style="background:var(--green-wash);">
                <i class="fas fa-wallet text-success"></i>
            </div>
            <div class="stat-label">Ingresos recibidos (Mes)</div>
            <div class="stat-value mono">S/ <?php echo number_format($total_recibido_mes, 2); ?></div>
            <div class="stat-foot">De S/ <?php echo number_format($total_proyectado_mes, 2); ?> proyectados</div>
            <div class="stat-trend trend-up"><i class="fas fa-coins"></i> <?php echo date('M Y'); ?></div>
        </div>
        <div class="stat-card c-blue">
            <div class="stat-icon" style="background:var(--blue-wash);">
                <i class="fas fa-clipboard-list text-primary"></i>
            </div>
            <div class="stat-label">Solicitudes pendientes</div>
            <div class="stat-value"><?php echo $pendientes_count; ?></div>
            <div class="stat-foot">Por revisar</div>
            <?php if ($pendientes_count > 0): ?>
                <div class="stat-trend trend-down"><i class="fas fa-exclamation-circle"></i> Requieren atención</div>
            <?php else: ?>
                <div class="stat-trend trend-up"><i class="fas fa-check-circle"></i> Todo al día</div>
            <?php endif; ?>
        </div>
        <div class="stat-card c-gold">
            <div class="stat-icon" style="background:var(--gold-wash);">
                <i class="fas fa-star text-warning"></i>
            </div>
            <div class="stat-label">Tu calificación</div>
            <div class="stat-value"><?php echo htmlspecialchars($calificacion); ?> <span
                    style="font-size:15px;color:var(--ink-faint);">/ 5</span></div>
            <div class="stat-foot">Promedio de reseñas</div>
            <div class="stat-trend trend-up"><i class="fas fa-medal"></i> Nivel Propietario</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4 mt-2 mb-4">
        <div class="col-lg-5">
            <div class="card p-3 h-100" style="border-radius: 16px; border: 1px solid var(--line);">
                <h6 class="fw-bold mb-3" style="font-family: var(--font-display); font-size: 14.5px;">Progreso de
                    Ingresos del Mes</h6>
                <div class="chart-container" style="position: relative; height:250px; width:100%;">
                    <canvas id="ingresosPieChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card p-3 h-100" style="border-radius: 16px; border: 1px solid var(--line);">
                <h6 class="fw-bold mb-3" style="font-family: var(--font-display); font-size: 14.5px;">Histórico de
                    Ingresos (6 meses)</h6>
                <div class="chart-container" style="position: relative; height:250px; width:100%;">
                    <canvas id="ingresosBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Lists -->
    <div class="row g-4 mt-2">
        <!-- Nuevas solicitudes -->
        <div class="col-lg-7">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-clipboard-list text-danger"></i>
                <h6 class="fw-bold mb-0" style="font-family: var(--font-display); font-size: 14.5px;">Nuevas solicitudes
                </h6>
            </div>
            <div class="card mb-4" style="border-radius: 16px; border: 1px solid var(--line);">
                <div class="req-list">
                    <?php if (empty($nuevas_solicitudes)): ?>
                        <div class="p-4 text-center text-muted">No tienes solicitudes pendientes.</div>
                    <?php else: ?>
                        <?php foreach ($nuevas_solicitudes as $req):
                            $iniciales = strtoupper(substr($req['inquilino_nombres'], 0, 1) . substr($req['inquilino_apellido_paterno'], 0, 1));
                            ?>
                            <div class="req-row p-3 border-bottom d-flex align-items-center gap-3">
                                <div class="req-av text-white d-flex justify-content-center align-items-center rounded-circle"
                                    style="width: 42px; height: 42px; font-weight: bold; background: linear-gradient(135deg,var(--blue),#6E97F4);">
                                    <?php echo $iniciales; ?>
                                </div>
                                <div class="req-info flex-grow-1">
                                    <div class="req-name fw-bold" style="font-size: 14px;">
                                        <?php echo htmlspecialchars($req['inquilino_nombres'] . ' ' . $req['inquilino_apellido_paterno']); ?>
                                    </div>
                                    <div class="req-detail text-muted" style="font-size: 12.5px;">
                                        <?php echo htmlspecialchars($req['alojamiento_titulo'] ?? 'Cuarto'); ?>
                                    </div>
                                </div>
                                <div class="req-meta text-end">
                                    <div class="req-date text-muted mb-1" style="font-size: 12px;">S/
                                        <?php echo number_format($req['monto_total'], 2); ?>
                                    </div>
                                    <a href="/solicitudes/detalle?id=<?php echo $req['reserva_id']; ?>"
                                        class="btn btn-sm btn-light rounded-pill fw-bold"
                                        style="font-size: 12px; border: 1px solid var(--line);">Ver Detalle</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Estado de cuartos -->
        <div class="col-lg-5">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-bed text-danger"></i>
                <h6 class="fw-bold mb-0" style="font-family: var(--font-display); font-size: 14.5px;">Estado de cuartos
                </h6>
            </div>
            <div class="card p-3" style="border-radius: 16px; border: 1px solid var(--line);">
                <?php if (empty($alojamientos)): ?>
                    <div class="p-4 text-center text-muted">No tienes cuartos publicados.</div>
                <?php else: ?>
                    <?php foreach ($alojamientos as $alj):
                        $codigoCorto = substr($alj['codigo'] ?? 'C', -2);
                        if ($alj['estado_codigo'] === 'EPA001') {
                            $color = 'bg-primary';
                            $estadoText = 'En revisión';
                        } elseif ($alj['estado_codigo'] === 'EPA004') {
                            $color = 'bg-warning';
                            $estadoText = 'Ocupado';
                        } elseif ($alj['estado_codigo'] === 'EPA003') {
                            $color = 'bg-success';
                            $estadoText = 'Disponible';
                        } else {
                            $color = 'bg-danger';
                            $estadoText = 'Inactivo';
                        }
                        ?>
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded <?php echo $color; ?> text-white d-flex justify-content-center align-items-center"
                                    style="width: 40px; height: 40px; font-weight: bold;">
                                    <?php echo htmlspecialchars($codigoCorto); ?>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size: 13.5px;">
                                        <?php echo htmlspecialchars($alj['titulo']); ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 12px;"><?php echo $estadoText; ?></div>
                                </div>
                            </div>
                            <div class="fw-bold" style="font-family: var(--font-mono); font-size: 13.5px; color: var(--ink);">S/
                                <?php echo number_format($alj['precio_mensual'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="/alojamientos" class="btn btn-sm btn-light w-100 fw-bold mt-2" style="border-radius: 8px;">Ver
                    todos los cuartos</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Doughnut Chart (Ingresos del Mes)
        var ctxPie = document.getElementById('ingresosPieChart');
        if (ctxPie) {
            var proyectado = <?php echo $total_proyectado_mes; ?>;
            var recibido = <?php echo $total_recibido_mes; ?>;
            var pendiente = proyectado - recibido;
            if (pendiente < 0) pendiente = 0;

            new Chart(ctxPie.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Recibido', 'Pendiente'],
                    datasets: [{
                        data: [recibido, pendiente],
                        backgroundColor: ['#2ECC71', '#E74C3C'], // Verde, Rojo
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // Bar Chart (Histórico)
        var ctxBar = document.getElementById('ingresosBarChart');
        if (ctxBar) {
            new Chart(ctxBar.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chart_meses); ?>,
                    datasets: [
                        {
                            label: 'Proyectado',
                            data: <?php echo json_encode($chart_proyectado); ?>,
                            backgroundColor: '#BDC3C7', // Gris claro
                            borderRadius: 4
                        },
                        {
                            label: 'Recibido',
                            data: <?php echo json_encode($chart_recibido); ?>,
                            backgroundColor: '#3498DB', // Azul
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    });
</script>