<div class="page-content">
    <div class="page-head">
        <div>
            <div class="eyebrow">Centro de operaciones</div>
            <h1>Bienvenida de nuevo, <?php echo htmlspecialchars($nombre_usuario); ?></h1>
            <div class="sub">Resumen de tus alojamientos y actividad reciente.</div>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat-card c-red">
            <div class="stat-icon" style="background:var(--red-wash);">
                <i class="fas fa-building text-danger"></i>
            </div>
            <div class="stat-label">Cuartos activos</div>
            <div class="stat-value">4</div>
            <div class="stat-foot">3 ocupados · 1 disponible</div>
            <div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> +1 este mes</div>
        </div>
        <div class="stat-card c-green">
            <div class="stat-icon" style="background:var(--green-wash);">
                <i class="fas fa-wallet text-success"></i>
            </div>
            <div class="stat-label">Ingresos del mes</div>
            <div class="stat-value mono">S/ 2,550</div>
            <div class="stat-foot">Junio 2026</div>
            <div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> +S/ 300 vs mayo</div>
        </div>
        <div class="stat-card c-blue">
            <div class="stat-icon" style="background:var(--blue-wash);">
                <i class="fas fa-clipboard-list text-primary"></i>
            </div>
            <div class="stat-label">Solicitudes pendientes</div>
            <div class="stat-value">3</div>
            <div class="stat-foot">Por revisar hoy</div>
            <div class="stat-trend trend-down"><i class="fas fa-exclamation-circle"></i> Requieren atención</div>
        </div>
        <div class="stat-card c-gold">
            <div class="stat-icon" style="background:var(--gold-wash);">
                <i class="fas fa-star text-warning"></i>
            </div>
            <div class="stat-label">Tu calificación</div>
            <div class="stat-value">4.9 <span style="font-size:15px;color:var(--ink-faint);">/ 5</span></div>
            <div class="stat-foot">Promedio de reseñas</div>
            <div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> +0.1 vs trimestre pasado</div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-lg-7">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-clipboard-list text-danger"></i>
                <h6 class="fw-bold mb-0" style="font-family: var(--font-display); font-size: 14.5px;">Nuevas solicitudes</h6>
            </div>
            <div class="card mb-4">
                <div class="req-list">
                    <!-- Solicitud 1 -->
                    <div class="req-row p-3 border-bottom d-flex align-items-center gap-3">
                        <div class="req-av text-white d-flex justify-content-center align-items-center rounded-circle" style="width: 42px; height: 42px; font-weight: bold; background: linear-gradient(135deg,var(--blue),#6E97F4);">CR</div>
                        <div class="req-info flex-grow-1">
                            <div class="req-name fw-bold" style="font-size: 14px;">Camila Ríos</div>
                            <div class="req-detail text-muted" style="font-size: 12.5px;">PUCP · Ing. Industrial · Cuarto 1 — Pueblo Libre</div>
                        </div>
                        <div class="req-meta text-end">
                            <div class="req-date text-muted mb-1" style="font-size: 12px;">Hace 2 horas</div>
                            <div class="req-actions d-flex gap-2">
                                <button class="btn btn-sm btn-success rounded-pill fw-bold" style="font-size: 12px;">Aprobar</button>
                                <button class="btn btn-sm btn-danger rounded-pill fw-bold" style="font-size: 12px;">Rechazar</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Solicitud 2 -->
                    <div class="req-row p-3 d-flex align-items-center gap-3">
                        <div class="req-av text-white d-flex justify-content-center align-items-center rounded-circle" style="width: 42px; height: 42px; font-weight: bold; background: linear-gradient(135deg,var(--green),#5FCB89);">JL</div>
                        <div class="req-info flex-grow-1">
                            <div class="req-name fw-bold" style="font-size: 14px;">José López</div>
                            <div class="req-detail text-muted" style="font-size: 12.5px;">Universidad de Lima · Derecho · Cuarto 1 — Pueblo Libre</div>
                        </div>
                        <div class="req-meta text-end">
                            <div class="req-date text-muted mb-1" style="font-size: 12px;">Hace 5 horas</div>
                            <div class="req-actions d-flex gap-2">
                                <button class="btn btn-sm btn-success rounded-pill fw-bold" style="font-size: 12px;">Aprobar</button>
                                <button class="btn btn-sm btn-danger rounded-pill fw-bold" style="font-size: 12px;">Rechazar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-bed text-danger"></i>
                <h6 class="fw-bold mb-0" style="font-family: var(--font-display); font-size: 14.5px;">Estado de cuartos</h6>
            </div>
            <div class="card p-3">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded bg-primary text-white d-flex justify-content-center align-items-center" style="width: 40px; height: 40px; font-weight: bold;">C1</div>
                        <div>
                            <div class="fw-bold" style="font-size: 14px;">Cuarto 1 — Pueblo Libre</div>
                            <div class="text-muted" style="font-size: 12.5px;">Camila Ríos · S/ 850 / mes</div>
                        </div>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1">Ocupado</span>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded bg-info text-white d-flex justify-content-center align-items-center" style="width: 40px; height: 40px; font-weight: bold;">C2</div>
                        <div>
                            <div class="fw-bold" style="font-size: 14px;">Cuarto 2 — Pueblo Libre</div>
                            <div class="text-muted" style="font-size: 12.5px;">Ana Suárez · S/ 900 / mes</div>
                        </div>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1">Ocupado</span>
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded bg-light text-muted d-flex justify-content-center align-items-center" style="width: 40px; height: 40px; font-weight: bold;">C4</div>
                        <div>
                            <div class="fw-bold" style="font-size: 14px;">Cuarto 4 — San Miguel</div>
                            <div class="text-muted" style="font-size: 12.5px;">Sin inquilino asignado</div>
                        </div>
                    </div>
                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1">Disponible</span>
                </div>
            </div>
        </div>
    </div>
</div>
