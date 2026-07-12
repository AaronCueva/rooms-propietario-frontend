<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Reseñas directas de un alojamiento (tabla resenia_alojamiento).
 * El estudiante califica/comenta; el propietario puede responder.
 */
class ReseniaAlojamiento {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene las reseñas de un alojamiento con datos del estudiante.
     */
    public function getByAlojamientoId($alojamiento_id) {
        $query = "
            SELECT r.*,
                   u.nombres, u.apellido_paterno, u.correo, u.url_foto AS usuario_foto,
                   cat.nombre AS estado_nombre
            FROM resenia_alojamiento r
            INNER JOIN usuario u ON r.estudiante_id = u.usuario_id
            LEFT JOIN catalogo cat ON r.estado_codigo = cat.codigo AND cat.referencia_codigo = 'ESTADO_RESENIA_ALOJAMIENTO'
            WHERE r.alojamiento_id = :alojamiento_id AND r.habilitado = true
            ORDER BY r.creado DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':alojamiento_id' => $alojamiento_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $query = "SELECT * FROM resenia_alojamiento WHERE resenia_alojamiento_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * El propietario responde a una reseña de su alojamiento.
     * Verifica que la reseña pertenezca a un alojamiento del propietario.
     */
    public function responder($resenia_id, $propietario_id, $respuesta) {
        $query = "UPDATE resenia_alojamiento r
                  SET respuesta_propietario = :respuesta,
                      estado_codigo = 'ESRA001',
                      modificado = CURRENT_TIMESTAMP
                  FROM alojamiento a
                  WHERE r.resenia_alojamiento_id = :resenia_id
                    AND r.alojamiento_id = a.alojamiento_id
                    AND a.usuario_id = :propietario_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':respuesta' => $respuesta,
            ':resenia_id' => $resenia_id,
            ':propietario_id' => $propietario_id
        ]);
    }
}
