<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Reseñas de un contrato (tabla resena).
 * El inquilino califica/comenta el contrato; el propietario puede responder.
 */
class Resena {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene las reseñas de un contrato con datos del inquilino.
     */
    public function getByContratoId($contrato_id) {
        $query = "
            SELECT r.*,
                   u.nombres, u.apellido_paterno, u.correo, u.url_foto AS usuario_foto,
                   cat.nombre AS estado_nombre
            FROM resena r
            INNER JOIN contrato c ON r.contrato_id = c.contrato_id
            INNER JOIN reserva res ON c.reserva_id = res.reserva_id
            INNER JOIN usuario u ON res.usuario_id = u.usuario_id
            LEFT JOIN catalogo cat ON r.estado_codigo = cat.codigo AND cat.referencia_codigo = 'ESTADO_RESENA'
            WHERE r.contrato_id = :contrato_id AND r.habilitado = true
            ORDER BY r.fecha_creado DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':contrato_id' => $contrato_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $query = "SELECT * FROM resena WHERE resena_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * El propietario responde a una reseña de un contrato que le pertenece.
     * Verifica propiedad cruzando contrato -> reserva -> alojamiento -> usuario_id.
     */
    public function responder($resena_id, $propietario_id, $respuesta) {
        $query = "UPDATE resena r
                  SET respuesta_propietario = :respuesta,
                      estado_codigo = 'ESRS001',
                      modificado = CURRENT_TIMESTAMP
                  FROM contrato c
                  INNER JOIN reserva res ON c.reserva_id = res.reserva_id
                  INNER JOIN alojamiento a ON res.alojamiento_id = a.alojamiento_id
                  WHERE r.resena_id = :resena_id
                    AND r.contrato_id = c.contrato_id
                    AND a.usuario_id = :propietario_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':respuesta' => $respuesta,
            ':resena_id' => $resena_id,
            ':propietario_id' => $propietario_id
        ]);
    }
}
