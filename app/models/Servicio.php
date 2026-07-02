<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Servicio
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerTodos()
    {
        $query = "SELECT * FROM servicio WHERE habilitado = true ORDER BY nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
