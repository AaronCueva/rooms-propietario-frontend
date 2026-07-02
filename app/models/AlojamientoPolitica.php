<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class AlojamientoPolitica
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerPorAlojamiento($alojamiento_id)
    {
        $query = "SELECT ap.*, p.nombre, p.descripcion 
                  FROM alojamiento_politica ap
                  JOIN politica_casa p ON ap.politica_casa_id = p.politica_casa_id
                  WHERE ap.alojamiento_id = :alojamiento_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':alojamiento_id', $alojamiento_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function asociar($alojamiento_id, $politica_casa_id)
    {
        $queryCheck = "SELECT alojamiento_politica_id FROM alojamiento_politica WHERE alojamiento_id = :aid AND politica_casa_id = :pid";
        $stmtCheck = $this->db->prepare($queryCheck);
        $stmtCheck->bindParam(':aid', $alojamiento_id);
        $stmtCheck->bindParam(':pid', $politica_casa_id);
        $stmtCheck->execute();
        
        if ($stmtCheck->rowCount() == 0) {
            $query = "INSERT INTO alojamiento_politica (alojamiento_id, politica_casa_id) VALUES (:aid, :pid)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':aid', $alojamiento_id);
            $stmt->bindParam(':pid', $politica_casa_id);
            return $stmt->execute();
        }
        return true;
    }

    public function remover($alojamiento_id, $politica_casa_id)
    {
        $query = "DELETE FROM alojamiento_politica WHERE alojamiento_id = :aid AND politica_casa_id = :pid";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':aid', $alojamiento_id);
        $stmt->bindParam(':pid', $politica_casa_id);
        return $stmt->execute();
    }
}
