<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Multimedia
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Guardar una foto asociada a un alojamiento
     */
    public function guardarFotoAlojamiento($alojamiento_id, $url, $nombre, $orden = 1)
    {
        $query = "INSERT INTO multimedia (url, tipo_codigo, nombre, orden, alojamiento_id, habilitado)
                  VALUES (:url, 'FOTO', :nombre, :orden, :alojamiento_id, true)";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':url', $url);
        $stmt->bindValue(':nombre', $nombre);
        $stmt->bindValue(':orden', $orden, PDO::PARAM_INT);
        $stmt->bindValue(':alojamiento_id', $alojamiento_id);
        return $stmt->execute();
    }

    /**
     * Obtener la foto principal (orden = 1 o la primera) de un alojamiento
     */
    public function obtenerFotoPrincipal($alojamiento_id)
    {
        $query = "SELECT * FROM multimedia
                  WHERE alojamiento_id = :alojamiento_id AND habilitado = true
                  ORDER BY orden ASC LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':alojamiento_id', $alojamiento_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todas las fotos de un alojamiento
     */
    public function obtenerFotosPorAlojamiento($alojamiento_id)
    {
        $query = "SELECT * FROM multimedia
                  WHERE alojamiento_id = :alojamiento_id AND habilitado = true
                  ORDER BY orden ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':alojamiento_id', $alojamiento_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Eliminar (deshabilitar) una foto
     */
    public function eliminar($multimedia_id)
    {
        $query = "UPDATE multimedia SET habilitado = false WHERE multimedia_id = :multimedia_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':multimedia_id', $multimedia_id);
        return $stmt->execute();
    }

    /**
     * Guardar un documento PDF (ej. Contrato firmado)
     */
    public function guardarDocumentoContrato($url, $nombre)
    {
        // En este caso, el tipo_codigo puede ser 'DOC' o similar. Usaremos DOC.
        $query = "INSERT INTO multimedia (url, tipo_codigo, nombre, orden, habilitado)
                  VALUES (:url, 'DOC', :nombre, 1, true) RETURNING multimedia_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':url', $url);
        $stmt->bindValue(':nombre', $nombre);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['multimedia_id'] : null;
    }

    /**
     * Obtener documento por ID
     */
    public function obtenerPorId($multimedia_id)
    {
        $query = "SELECT * FROM multimedia WHERE multimedia_id = :multimedia_id AND habilitado = true";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':multimedia_id', $multimedia_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
