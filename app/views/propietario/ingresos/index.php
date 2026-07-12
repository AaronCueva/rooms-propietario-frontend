<div class="page-content">
    <div class="page-head mb-4 d-flex justify-content-between align-items-end flex-wrap gap-3">
        <div>
            <div class="eyebrow">Módulo Financiero</div>
            <h1 style="font-size: 24px; font-family: var(--font-display); font-weight: 700; margin: 0; color: var(--ink);">Mis Ingresos</h1>
            <div class="text-muted mt-1" style="font-size: 14px;">Revisa tu meta de ingresos y controla los pagos de tus inquilinos.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="/ingresos/cuenta" class="btn btn-light" style="border-radius: 10px; font-weight: 600;">
                <i class="fas fa-university me-1 text-primary"></i> Cuenta de Cobro
            </a>
            <a href="/ingresos/exportar?mes=<?php echo $mes; ?>&anio=<?php echo $anio; ?>&format=pdf" class="btn btn-dark" style="border-radius: 10px; font-weight: 600;">
                <i class="fas fa-file-pdf me-1"></i> Exportar PDF
            </a>
            <a href="/ingresos/exportar?mes=<?php echo $mes; ?>&anio=<?php echo $anio; ?>&format=excel" class="btn btn-success" style="border-radius: 10px; font-weight: 600;">
                <i class="fas fa-file-excel me-1"></i> Exportar Excel
            </a>
        </div>
    </div>

    <!-- Filtro Mes y Año -->
    <div class="card mb-4" style="border-radius: 16px; border: 1px solid var(--line);">
        <div class="card-body p-3 d-flex flex-wrap gap-3 align-items-center">
            <span class="fw-semibold text-muted" style="font-size: 13.5px;"><i class="fas fa-filter"></i> Filtrar por periodo:</span>
            <form action="/ingresos" method="GET" class="d-flex gap-2 align-items-center mb-0">
                <select name="mes" class="form-select form-select-sm" style="border-radius: 8px; font-size: 13.5px; width: 140px;" onchange="this.form.submit()">
                    <?php
                    $meses = ['1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio', 
                              '7' => 'Julio', '8' => 'Agosto', '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'];
                    foreach ($meses as $num => $nombre): ?>
                        <option value="<?php echo $num; ?>" <?php echo $mes == $num ? 'selected' : ''; ?>><?php echo $nombre; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="anio" class="form-select form-select-sm" style="border-radius: 8px; font-size: 13.5px; width: 100px;" onchange="this.form.submit()">
                    <?php for($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo $anio == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Cuenta Configurada Alert -->
    <?php if (empty($cuenta_principal)): ?>
        <div class="alert alert-warning border-0 d-flex align-items-center gap-3 mb-4" style="background: var(--amber-wash); color: #92400E; border-radius: 16px;">
            <div style="font-size: 24px;"><i class="fas fa-exclamation-circle"></i></div>
            <div>
                <h6 class="mb-1 fw-bold">Cuenta de Cobro no configurada</h6>
                <div style="font-size: 13.5px;">No has registrado una cuenta bancaria principal. Necesitamos este dato para realizarte los abonos.</div>
            </div>
            <a href="/ingresos/cuenta" class="btn btn-sm btn-warning ms-auto" style="border-radius: 8px; font-weight: 600; color: #78350F;">Configurar ahora</a>
        </div>
    <?php endif; ?>

    <!-- KPIs -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100" style="border-radius: 16px; border: 1px solid var(--line); border-left: 4px solid var(--blue);">
                <div class="card-body p-4">
                    <div style="font-size:12px; font-weight:700; color:var(--ink-faint); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Ingreso Proyectado (Meta)</div>
                    <div style="font-family: var(--font-mono); font-size: 28px; font-weight: 700; color: var(--ink);">
                        S/ <?php echo number_format($total_proyectado, 2); ?>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 13px;">Suma de todas las cuotas de este mes.</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100" style="border-radius: 16px; border: 1px solid var(--line); border-left: 4px solid var(--green);">
                <div class="card-body p-4">
                    <div style="font-size:12px; font-weight:700; color:var(--ink-faint); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Total Recibido</div>
                    <div style="font-family: var(--font-mono); font-size: 28px; font-weight: 700; color: var(--green);">
                        S/ <?php echo number_format($total_recibido, 2); ?>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 13px;">Cuotas ya pagadas por los inquilinos.</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100" style="border-radius: 16px; border: 1px solid var(--line); border-left: 4px solid var(--red);">
                <div class="card-body p-4">
                    <div style="font-size:12px; font-weight:700; color:var(--ink-faint); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Estado de Cuotas</div>
                    <div class="d-flex gap-4">
                        <div>
                            <div style="font-family: var(--font-mono); font-size: 24px; font-weight: 700; color: var(--ink);"><?php echo $pendientes; ?></div>
                            <div class="text-muted" style="font-size: 13px;">Pendientes</div>
                        </div>
                        <div>
                            <div style="font-family: var(--font-mono); font-size: 24px; font-weight: 700; color: var(--red);"><?php echo $retrasos; ?></div>
                            <div class="text-danger" style="font-size: 13px;">En retraso</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Inquilinos y Pagos -->
    <div class="card" style="border-radius: 16px; border: 1px solid var(--line);">
        <div class="card-header bg-white border-bottom p-4">
            <h6 class="m-0" style="font-family: var(--font-display); font-weight: 700;">Detalle de Pagos - <?php echo $meses[$mes] . ' ' . $anio; ?></h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                    <thead style="background: var(--gray-50); color: var(--ink-faint); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <tr>
                            <th class="border-0 px-4 py-3" style="border-bottom: 1px solid var(--line) !important;">Inquilino</th>
                            <th class="border-0 px-4 py-3" style="border-bottom: 1px solid var(--line) !important;">Alojamiento</th>
                            <th class="border-0 px-4 py-3" style="border-bottom: 1px solid var(--line) !important;">N° Cuota</th>
                            <th class="border-0 px-4 py-3" style="border-bottom: 1px solid var(--line) !important;">Vencimiento</th>
                            <th class="border-0 px-4 py-3 text-end" style="border-bottom: 1px solid var(--line) !important;">Monto (S/)</th>
                            <th class="border-0 px-4 py-3 text-center" style="border-bottom: 1px solid var(--line) !important;">Estado</th>
                            <th class="border-0 px-4 py-3 text-center" style="border-bottom: 1px solid var(--line) !important;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ingresos)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-file-invoice-dollar fs-3 mb-2 d-block text-black-50"></i>
                                    No hay cuotas programadas para este mes.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ingresos as $ing): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($ing['inquilino_nombres'] . ' ' . $ing['inquilino_apellido']); ?></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-truncate" style="max-width: 200px;"><i class="fas fa-home text-muted me-1"></i> <?php echo htmlspecialchars($ing['alojamiento_titulo']); ?></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-light text-dark border">Cuota <?php echo $ing['numero_cuota']; ?></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php echo date('d/m/Y', strtotime($ing['fecha_vencimiento'])); ?>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <span style="font-family: var(--font-mono); font-weight: 600; color: var(--ink);">
                                            <?php echo number_format($ing['monto'], 2); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if (isset($ing['estado_mostrar']) && $ing['estado_mostrar'] === 'Retrasado'): ?>
                                            <span class="badge" style="background: var(--red-wash); color: var(--red); font-size: 11px;">Retrasado</span>
                                        <?php elseif (isset($ing['estado_mostrar']) && $ing['estado_mostrar'] === 'Pendiente'): ?>
                                            <span class="badge" style="background: var(--amber-wash); color: #B45309; font-size: 11px;">Pendiente</span>
                                        <?php else: ?>
                                            <span class="badge" style="background: var(--green-wash); color: var(--green); font-size: 11px;">Pagado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if (isset($ing['estado_mostrar']) && in_array($ing['estado_mostrar'], ['Pendiente','Retrasado'], true)): ?>
                                            <button type="button" class="btn btn-sm btn-success confirmar-pago"
                                                    data-pago-id="<?php echo htmlspecialchars($ing['pago_id'], ENT_QUOTES); ?>"
                                                    style="border-radius: 8px; font-weight: 600;">
                                                <i class="fas fa-check me-1"></i> Confirmar
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    document.querySelectorAll('.confirmar-pago').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var pagoId = btn.dataset.pagoId;
            Swal.fire({
                title: '¿Confirmar recepción del pago?',
                text: 'Marcarás esta cuota como pagada/recibida.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar'
            }).then(function (r) {
                if (!r.isConfirmed) return;
                var fd = new FormData(); fd.append('pago_id', pagoId);
                fetch('/ingresos/confirmar', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                })
                .then(function (res) { return res.json(); })
                .then(function (d) {
                    Swal.fire({
                        icon: d.success ? 'success' : 'error',
                        title: d.success ? 'Confirmado' : 'Error',
                        text: d.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function () { if (d.success) window.location.reload(); });
                })
                .catch(function () { window.location.reload(); });
            });
        });
    });
})();
</script>
