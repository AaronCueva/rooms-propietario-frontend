<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($titulo); ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .header h1 { margin: 0 0 10px 0; font-size: 24px; }
        .header p { margin: 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 14px; }
        th { background-color: #f8f9fa; font-weight: bold; text-transform: uppercase; font-size: 12px; color: #555; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .summary { display: flex; justify-content: flex-end; gap: 40px; margin-top: 20px; }
        .summary-box { text-align: right; }
        .summary-box .label { font-size: 12px; color: #666; text-transform: uppercase; }
        .summary-box .value { font-size: 20px; font-weight: bold; color: #111; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div style="padding: 20px; max-width: 800px; margin: 0 auto;">
        <?php
        // Cumplimiento de pagos del mes (conteo de cuotas por estado)
        $cnt_pagado = 0; $cnt_pendiente = 0; $cnt_retrasado = 0;
        foreach ($ingresos as $ing) {
            $hoy_ = new \DateTime();
            $venc_ = new \DateTime($ing['fecha_vencimiento']);
            if ($ing['estado_codigo'] === 'ESPA002') {
                $cnt_pagado++;
            } elseif ($hoy_ > $venc_) {
                $cnt_retrasado++;
            } else {
                $cnt_pendiente++;
            }
        }
        $total_cuotas = $cnt_pagado + $cnt_pendiente + $cnt_retrasado;

        // Genera un gráfico de pastel en SVG inline (sin JS, ideal para impresión/PDF).
        // $datos = [['valor'=>int,'color'=>'#hex'], ...]
        $pastelSVG = function ($datos, $cx = 90, $cy = 90, $r = 80) {
            $total = array_sum(array_column($datos, 'valor'));
            $w = $cx * 2; $h = $cy * 2;
            $svg = '<svg width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '" xmlns="http://www.w3.org/2000/svg">';
            if ($total <= 0) {
                $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="#e5e7eb" stroke="#fff" stroke-width="2"/>';
                return $svg . '</svg>';
            }
            $start = -90.0; // empezar en la parte superior
            foreach ($datos as $d) {
                if ($d['valor'] <= 0) continue;
                $sweep = ($d['valor'] / $total) * 360.0;
                $end = $start + $sweep;
                if ($sweep >= 359.999) {
                    // Círculo completo: dos semicírculos (un arco de 360° no se dibuja con un solo path)
                    $svg .= '<path d="M ' . ($cx - $r) . ' ' . $cy . ' A ' . $r . ' ' . $r . ' 0 1 1 ' . ($cx + $r) . ' ' . $cy . ' A ' . $r . ' ' . $r . ' 0 1 1 ' . ($cx - $r) . ' ' . $cy . ' Z" fill="' . $d['color'] . '" stroke="#fff" stroke-width="2"/>';
                } else {
                    $a0 = deg2rad($start); $a1 = deg2rad($end);
                    $x0 = $cx + $r * cos($a0); $y0 = $cy + $r * sin($a0);
                    $x1 = $cx + $r * cos($a1); $y1 = $cy + $r * sin($a1);
                    $large = ($sweep > 180.0) ? 1 : 0;
                    $svg .= '<path d="M ' . $cx . ' ' . $cy . ' L ' . round($x0, 2) . ' ' . round($y0, 2) . ' A ' . $r . ' ' . $r . ' 0 ' . $large . ' 1 ' . round($x1, 2) . ' ' . round($y1, 2) . ' Z" fill="' . $d['color'] . '" stroke="#fff" stroke-width="2"/>';
                }
                $start = $end;
            }
            return $svg . '</svg>';
        };
        ?>

        <div class="no-print" style="margin-bottom: 20px; text-align: right;">
            <button onclick="window.print()" style="padding: 8px 16px; background: #111; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Imprimir / Guardar como PDF</button>
            <button onclick="history.back()" style="padding: 8px 16px; background: #eee; color: #333; border: none; border-radius: 6px; cursor: pointer; margin-left: 10px;">Volver</button>
        </div>

        <div class="header">
            <h1>Reporte de Ingresos</h1>
            <p>Periodo: <?php echo $nombre_mes . ' ' . $anio; ?></p>
            <p style="font-size: 12px; margin-top: 10px;">Generado el: <?php echo date('d/m/Y H:i'); ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Inquilino</th>
                    <th>Alojamiento</th>
                    <th>Cuota</th>
                    <th>Vencimiento</th>
                    <th class="text-right">Monto (S/)</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_proyectado = 0;
                $total_recibido = 0;

                if (empty($ingresos)): ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 40px; color: #888;">No hay ingresos programados para este periodo.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ingresos as $ing): 
                        $hoy = new \DateTime();
                        $vencimiento = new \DateTime($ing['fecha_vencimiento']);
                        
                        $estado = 'Pagado';
                        $badge_class = 'badge-green';

                        if ($ing['estado_codigo'] !== 'ESPA002') {
                            if ($hoy > $vencimiento) {
                                $estado = 'Retrasado';
                                $badge_class = 'badge-red';
                            } else {
                                $estado = 'Pendiente';
                                $badge_class = 'badge-amber';
                            }
                        }

                        $total_proyectado += floatval($ing['monto']);
                        if ($estado === 'Pagado') {
                            $total_recibido += floatval($ing['monto']);
                        }
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ing['inquilino_nombres'] . ' ' . $ing['inquilino_apellido']); ?></td>
                            <td><?php echo htmlspecialchars($ing['alojamiento_titulo']); ?></td>
                            <td><?php echo $ing['numero_cuota']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($ing['fecha_vencimiento'])); ?></td>
                            <td class="text-right"><?php echo number_format($ing['monto'], 2); ?></td>
                            <td class="text-center"><span class="badge <?php echo $badge_class; ?>"><?php echo $estado; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (!empty($ingresos)): ?>
        <div class="summary">
            <div class="summary-box">
                <div class="label">Total Proyectado</div>
                <div class="value">S/ <?php echo number_format($total_proyectado, 2); ?></div>
            </div>
            <div class="summary-box">
                <div class="label">Total Recibido</div>
                <div class="value" style="color: #166534;">S/ <?php echo number_format($total_recibido, 2); ?></div>
            </div>
        </div>

        <!-- Gráfico de pastel: cumplimiento de pagos del mes -->
        <div style="margin-top: 30px; page-break-inside: avoid;">
            <h2 style="font-size: 18px; margin: 0 0 15px 0; color: #333;">Cumplimiento de pagos</h2>
            <div style="display: flex; align-items: center; gap: 30px;">
                <div style="flex: none;">
                    <?php echo $pastelSVG([
                        ['valor' => $cnt_pagado,    'color' => '#16a34a'],
                        ['valor' => $cnt_pendiente, 'color' => '#f59e0b'],
                        ['valor' => $cnt_retrasado, 'color' => '#dc2626'],
                    ]); ?>
                </div>
                <div style="font-size: 14px; line-height: 1.9;">
                    <div><span style="display:inline-block;width:12px;height:12px;background:#16a34a;border-radius:2px;vertical-align:middle;margin-right:8px;"></span>Pagados: <strong><?php echo $cnt_pagado; ?></strong> (<?php echo round($cnt_pagado / $total_cuotas * 100); ?>%)</div>
                    <div><span style="display:inline-block;width:12px;height:12px;background:#f59e0b;border-radius:2px;vertical-align:middle;margin-right:8px;"></span>Pendientes: <strong><?php echo $cnt_pendiente; ?></strong> (<?php echo round($cnt_pendiente / $total_cuotas * 100); ?>%)</div>
                    <div><span style="display:inline-block;width:12px;height:12px;background:#dc2626;border-radius:2px;vertical-align:middle;margin-right:8px;"></span>Retrasados: <strong><?php echo $cnt_retrasado; ?></strong> (<?php echo round($cnt_retrasado / $total_cuotas * 100); ?>%)</div>
                    <div style="margin-top: 6px; color: #666; font-size: 13px;">Total de cuotas del mes: <?php echo $total_cuotas; ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
