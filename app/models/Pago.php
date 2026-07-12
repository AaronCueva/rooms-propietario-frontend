<?php
namespace App\Models;

use App\Core\Database;

class Pago
{
    /** Catálogo ESTADO_PAGO (confirmado en BD). */
    public const EST_PENDIENTE  = 'ESPG001';
    public const EST_COMPLETADO = 'ESPG003';

    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function generarCuotas($contrato_id, $monto, $fecha_inicio, $fecha_fin, $dia_pago, $usuario_id)
    {
        // No regenerar si ya existen cuotas (activas o anuladas)
        $chk = $this->conn->prepare("SELECT COUNT(*) FROM pago WHERE contrato_id = :c");
        $chk->bindParam(':c', $contrato_id);
        $chk->execute();
        if ((int)$chk->fetchColumn() > 0) {
            return;
        }

        $inicio = new \DateTime($fecha_inicio);
        $fin = new \DateTime($fecha_fin);

        $cuota = 1;
        $fecha_actual = clone $inicio;

        // Generar cuotas mes a mes hasta la fecha de fin
        while ($fecha_actual < $fin) {
            $fecha_vencimiento = new \DateTime($fecha_actual->format('Y-m') . '-' . sprintf("%02d", $dia_pago));

            $sql = "INSERT INTO pago (
                        contrato_id, numero_cuota, monto, fecha_vencimiento, estado_codigo, habilitado, creado, creado_por
                    ) VALUES (
                        :contrato_id, :numero_cuota, :monto, :fecha_vencimiento, :estado, TRUE, NOW(), :usuario_id
                    )";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':contrato_id', $contrato_id);
            $stmt->bindParam(':numero_cuota', $cuota);
            $stmt->bindParam(':monto', $monto);
            $stmt->bindValue(':estado', self::EST_PENDIENTE);

            $vencimiento = $fecha_vencimiento->format('Y-m-d');
            $stmt->bindParam(':fecha_vencimiento', $vencimiento);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();

            $cuota++;
            $fecha_actual->modify('+1 month');
        }
    }

    public function obtenerIngresosPropietario($usuario_id, $mes, $anio)
    {
        $sql = "SELECT
                    p.*,
                    c.monto_renta,
                    c.fecha_inicio as contrato_inicio,
                    c.fecha_fin as contrato_fin,
                    u.nombres as inquilino_nombres,
                    u.apellido_paterno as inquilino_apellido,
                    a.titulo as alojamiento_titulo,
                    ep.nombre as estado_pago_nombre
                FROM pago p
                INNER JOIN contrato c ON p.contrato_id = c.contrato_id
                INNER JOIN reserva r ON c.reserva_id = r.reserva_id
                INNER JOIN alojamiento a ON r.alojamiento_id = a.alojamiento_id
                INNER JOIN usuario u ON r.usuario_id = u.usuario_id
                LEFT JOIN catalogo ep ON p.estado_codigo = ep.codigo
                WHERE a.usuario_id = :usuario_id
                  AND c.estado_codigo = 'ESCO001'
                  AND EXTRACT(MONTH FROM p.fecha_vencimiento) = :mes
                  AND EXTRACT(YEAR FROM p.fecha_vencimiento) = :anio
                  AND p.habilitado = TRUE
                ORDER BY p.fecha_vencimiento ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':mes', $mes);
        $stmt->bindParam(':anio', $anio);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function anularPagosPendientes($contrato_id)
    {
        // Anular cuotas pendientes reales (ESPG001) y también datos viejos con ESPA001
        $sql = "UPDATE pago SET habilitado = FALSE
                WHERE contrato_id = :contrato_id
                  AND estado_codigo IN ('ESPG001','ESPA001')
                  AND habilitado = TRUE";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':contrato_id', $contrato_id);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error anulando pagos pendientes: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Confirma (marca como completado) un pago pendiente, verificando ownership
     * del propietario (pago → contrato → reserva → alojamiento.usuario_id).
     */
    public function confirmarPago($pago_id, $propietario_id): bool
    {
        $sql = "UPDATE pago p
                SET estado_codigo = :completado,
                    fecha_pago = now(),
                    modificado = now(),
                    modificado_por = :u
                FROM contrato c, reserva r, alojamiento a
                WHERE p.pago_id = :pago_id
                  AND p.contrato_id = c.contrato_id
                  AND c.reserva_id = r.reserva_id
                  AND r.alojamiento_id = a.alojamiento_id
                  AND a.usuario_id = :u
                  AND p.habilitado = true
                  AND p.estado_codigo <> :completado";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':completado', self::EST_COMPLETADO);
            $stmt->bindValue(':u', $propietario_id);
            $stmt->bindValue(':pago_id', $pago_id);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error confirmando pago: " . $e->getMessage());
            return false;
        }
    }
}
