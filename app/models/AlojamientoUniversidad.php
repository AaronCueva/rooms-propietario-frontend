<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Relación alojamiento <-> universidad (cercanía).
 * Calcula distancia_km con la fórmula de Haversine y sincroniza las filas
 * de alojamiento_universidad cuando se guarda un alojamiento.
 * Espejo de rooms-frontend/app/models/AlojamientoUniversidad.php (Admin).
 */
class AlojamientoUniversidad {
    private $db;

    /** Radio máximo en km para considerar cercanía */
    const RADIO_MAX_KM = 5;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Calcula la distancia entre dos puntos usando la fórmula de Haversine.
     * @return float Distancia en kilómetros
     */
    public static function haversine($lat1, $lon1, $lat2, $lon2) {
        $R = 6371; // Radio de la Tierra en km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($R * $c, 2);
    }

    /**
     * Sincroniza las relaciones de cercanía cuando se guarda/actualiza un ALOJAMIENTO.
     * Calcula la distancia contra todas las universidades con coordenadas y crea
     * las filas en alojamiento_universidad dentro del RADIO_MAX_KM.
     */
    public function sincronizarParaAlojamiento($alojamiento_id, $lat_aloj, $lng_aloj) {
        if (!$lat_aloj || !$lng_aloj) return;

        // Obtener todas las universidades con coordenadas
        $query = "SELECT universidad_id, latitud, longitud FROM universidad
                  WHERE latitud IS NOT NULL AND longitud IS NOT NULL AND habilitado = true";
        $stmt = $this->db->query($query);
        $universidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Eliminar relaciones previas para este alojamiento
        $delQuery = "DELETE FROM alojamiento_universidad WHERE alojamiento_id = :alojamiento_id";
        $delStmt = $this->db->prepare($delQuery);
        $delStmt->execute([':alojamiento_id' => $alojamiento_id]);

        // Insertar las nuevas relaciones dentro del radio
        $insQuery = "INSERT INTO alojamiento_universidad (alojamiento_id, universidad_id, distancia_km)
                     VALUES (:alojamiento_id, :universidad_id, :distancia_km)";
        $insStmt = $this->db->prepare($insQuery);

        foreach ($universidades as $uni) {
            $distancia = self::haversine($lat_aloj, $lng_aloj, $uni['latitud'], $uni['longitud']);
            if ($distancia <= self::RADIO_MAX_KM) {
                $insStmt->execute([
                    ':alojamiento_id' => $alojamiento_id,
                    ':universidad_id' => $uni['universidad_id'],
                    ':distancia_km' => $distancia
                ]);
            }
        }
    }

    /**
     * Devuelve las universidades cercanas (con su distancia) para un alojamiento.
     */
    public function obtenerPorAlojamiento($alojamiento_id) {
        $query = "SELECT au.*, u.nombre AS universidad_nombre
                  FROM alojamiento_universidad au
                  INNER JOIN universidad u ON au.universidad_id = u.universidad_id
                  WHERE au.alojamiento_id = :alojamiento_id AND au.habilitado = true
                  ORDER BY au.distancia_km ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':alojamiento_id' => $alojamiento_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
