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
        <?php endif; ?>
    </div>
</body>
</html>
