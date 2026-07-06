<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class Contrato
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crear un nuevo contrato
     */
    public function crear($datos)
    {
        $query = "INSERT INTO contrato (
                    fecha_inicio, fecha_fin, monto_renta, monto_garantia, 
                    estado_codigo, fecha_pago_mensual, reserva_id, multimedia_id, habilitado
                  ) VALUES (
                    :fecha_inicio, :fecha_fin, :monto_renta, :monto_garantia, 
                    'ESCO001', :fecha_pago_mensual, :reserva_id, :multimedia_id, true
                  ) RETURNING contrato_id";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':fecha_inicio', $datos['fecha_inicio']);
            $stmt->bindValue(':fecha_fin', $datos['fecha_fin']);
            $stmt->bindValue(':monto_renta', $datos['monto_renta']);
            $stmt->bindValue(':monto_garantia', $datos['monto_garantia'] ?? 0);
            $stmt->bindValue(':fecha_pago_mensual', $datos['fecha_pago_mensual'] ?? 1, PDO::PARAM_INT);
            $stmt->bindValue(':reserva_id', $datos['reserva_id']);
            $stmt->bindValue(':multimedia_id', $datos['multimedia_id']);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['contrato_id'] : null;
        } catch (Exception $e) {
            error_log("Error creando contrato: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener contratos del propietario
     */
    public function obtenerPorPropietario($usuario_id, $estado = null)
    {
        $query = "SELECT c.*, r.fecha_solicitud, 
                         u.nombres AS inquilino_nombres, u.apellido_paterno AS inquilino_apellido_paterno, u.url_foto AS inquilino_foto,
                         a.titulo AS alojamiento_titulo, a.direccion AS alojamiento_direccion,
                         m.url AS documento_url
                  FROM contrato c
                  INNER JOIN reserva r ON c.reserva_id = r.reserva_id
                  INNER JOIN alojamiento a ON r.alojamiento_id = a.alojamiento_id
                  INNER JOIN usuario u ON r.usuario_id = u.usuario_id
                  LEFT JOIN multimedia m ON c.multimedia_id = m.multimedia_id
                  WHERE a.usuario_id = :usuario_id AND c.habilitado = true";
                  
        if ($estado) {
            $query .= " AND c.estado_codigo = :estado";
        }
        
        $query .= " ORDER BY c.creado DESC";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':usuario_id', $usuario_id);
            if ($estado) {
                $stmt->bindValue(':estado', $estado);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo contratos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener el detalle de un contrato específico verificando propiedad
     */
    public function obtenerDetalle($contrato_id, $propietario_id)
    {
        $query = "SELECT c.*, r.fecha_solicitud, r.usuario_id AS inquilino_id,
                         u.nombres AS inquilino_nombres, u.apellido_paterno AS inquilino_apellido_paterno,
                         u.apellido_materno AS inquilino_apellido_materno, u.correo AS inquilino_correo,
                         u.celular AS inquilino_celular, u.url_foto AS inquilino_foto,
                         a.alojamiento_id, a.titulo AS alojamiento_titulo, a.direccion AS alojamiento_direccion,
                         a.precio_mensual AS alojamiento_precio,
                         m.url AS documento_url
                  FROM contrato c
                  INNER JOIN reserva r ON c.reserva_id = r.reserva_id
                  INNER JOIN alojamiento a ON r.alojamiento_id = a.alojamiento_id
                  INNER JOIN usuario u ON r.usuario_id = u.usuario_id
                  LEFT JOIN multimedia m ON c.multimedia_id = m.multimedia_id
                  WHERE c.contrato_id = :contrato_id AND a.usuario_id = :usuario_id AND c.habilitado = true";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':contrato_id', $contrato_id);
            $stmt->bindValue(':usuario_id', $propietario_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo detalle de contrato: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualizar estado del contrato
     */
    public function actualizarEstado($contrato_id, $estado_codigo)
    {
        $query = "UPDATE contrato SET estado_codigo = :estado_codigo, modificado = CURRENT_TIMESTAMP 
                  WHERE contrato_id = :contrato_id AND habilitado = true";
                  
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':estado_codigo', $estado_codigo);
            $stmt->bindValue(':contrato_id', $contrato_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error actualizando estado de contrato: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar contrato indicando que el propietario ya calificó al inquilino
     */
    public function marcarComoCalificado($contrato_id)
    {
        $query = "UPDATE contrato SET propietario_califico = true, modificado = CURRENT_TIMESTAMP 
                  WHERE contrato_id = :contrato_id AND habilitado = true";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':contrato_id', $contrato_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error marcando contrato como calificado: " . $e->getMessage());
            return false;
        }
    }
}
