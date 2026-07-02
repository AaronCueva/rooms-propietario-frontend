<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class AlojamientoServicio
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerPorAlojamiento($alojamiento_id)
    {
        $query = "SELECT asrv.*, s.nombre, s.descripcion 
                  FROM alojamiento_servicio asrv
                  JOIN servicio s ON asrv.servicio_id = s.servicio_id
                  WHERE asrv.alojamiento_id = :alojamiento_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':alojamiento_id', $alojamiento_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function asociar($alojamiento_id, $servicio_id, $precio)
    {
        // Verificar si ya existe
        $queryCheck = "SELECT alojamiento_servicio_id FROM alojamiento_servicio WHERE alojamiento_id = :aid AND servicio_id = :sid";
        $stmtCheck = $this->db->prepare($queryCheck);
        $stmtCheck->bindParam(':aid', $alojamiento_id);
        $stmtCheck->bindParam(':sid', $servicio_id);
        $stmtCheck->execute();
        
        if ($stmtCheck->rowCount() > 0) {
            // Actualizar precio
            $query = "UPDATE alojamiento_servicio SET precio = :precio WHERE alojamiento_id = :aid AND servicio_id = :sid";
        } else {
            // Insertar nuevo
            $query = "INSERT INTO alojamiento_servicio (alojamiento_id, servicio_id, precio) VALUES (:aid, :sid, :precio)";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':aid', $alojamiento_id);
        $stmt->bindParam(':sid', $servicio_id);
        $stmt->bindParam(':precio', $precio);
        return $stmt->execute();
    }

    public function remover($alojamiento_id, $servicio_id)
    {
        $query = "DELETE FROM alojamiento_servicio WHERE alojamiento_id = :aid AND servicio_id = :sid";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':aid', $alojamiento_id);
        $stmt->bindParam(':sid', $servicio_id);
        return $stmt->execute();
    }
}
