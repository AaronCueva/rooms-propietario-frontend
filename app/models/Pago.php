<?php
namespace App\Models;

use App\Core\Database;

class Pago
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function generarCuotas($contrato_id, $monto, $fecha_inicio, $fecha_fin, $dia_pago, $usuario_id)
    {
        $inicio = new \DateTime($fecha_inicio);
        $fin = new \DateTime($fecha_fin);
        
        $cuota = 1;
        $fecha_actual = clone $inicio;
        
        // Generar cuotas mes a mes hasta la fecha de fin
        while ($fecha_actual < $fin) {
            $fecha_vencimiento = new \DateTime($fecha_actual->format('Y-m') . '-' . sprintf("%02d", $dia_pago));
            
            // Si el día de pago del mes es menor a la fecha actual de iteración (e.g. inicio del contrato fue después), 
            // la primera cuota igual se cobra este mes o el siguiente dependiendo del caso. 
            // Para simplicidad, se proyecta 1 cuota por cada mes.
            
            $sql = "INSERT INTO pago (
                        contrato_id, numero_cuota, monto, fecha_vencimiento, estado_codigo, habilitado, creado, creado_por
                    ) VALUES (
                        :contrato_id, :numero_cuota, :monto, :fecha_vencimiento, 'ESPA001', TRUE, NOW(), :usuario_id
                    )";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':contrato_id', $contrato_id);
            $stmt->bindParam(':numero_cuota', $cuota);
            $stmt->bindParam(':monto', $monto);
            
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
        $sql = "UPDATE pago SET habilitado = FALSE 
                WHERE contrato_id = :contrato_id AND estado_codigo = 'ESPA001' AND habilitado = TRUE";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':contrato_id', $contrato_id);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error anulando pagos pendientes: " . $e->getMessage());
            return false;
        }
    }
}
