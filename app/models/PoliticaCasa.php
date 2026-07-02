<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class PoliticaCasa
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerTodos()
    {
        $query = "SELECT * FROM politica_casa WHERE habilitado = true ORDER BY nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function crear($nombre, $creado_por)
    {
        $query = "INSERT INTO politica_casa (nombre, habilitado, creado_por) 
                  VALUES (:nombre, true, :creado_por) RETURNING politica_casa_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':creado_por', $creado_por);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['politica_casa_id'] : null;
    }
}
