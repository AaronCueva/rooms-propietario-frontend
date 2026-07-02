<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Alojamiento
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los alojamientos de un propietario
     */
    public function obtenerPorUsuarioId($usuario_id)
    {
        $query = "SELECT a.*, u.nombre AS distrito_nombre,
                    (SELECT m.url FROM multimedia m 
                     WHERE m.alojamiento_id = a.alojamiento_id 
                     AND m.habilitado = true 
                     ORDER BY m.orden ASC LIMIT 1) AS foto_principal
                  FROM alojamiento a
                  LEFT JOIN ubicacion u ON a.ubicacion_id = u.ubicacion_id
                  WHERE a.usuario_id = :usuario_id AND a.habilitado = true
                  ORDER BY a.creado DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un alojamiento por su ID
     */
    public function obtenerPorId($alojamiento_id)
    {
        $query = "SELECT a.*, u.nombre AS distrito_nombre
                  FROM alojamiento a
                  LEFT JOIN ubicacion u ON a.ubicacion_id = u.ubicacion_id
                  WHERE a.alojamiento_id = :alojamiento_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':alojamiento_id', $alojamiento_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un nuevo alojamiento
     */
    public function crear($datos)
    {
        $query = "INSERT INTO alojamiento (
                    titulo, tipo_codigo, descripcion,
                    numero_habitaciones, numero_banos, bano_privado,
                    tamano_m2, genero_exclusivo_codigo,
                    mascotas_permitidas, fumadores_permitidos,
                    ubicacion_id, direccion, latitud, longitud,
                    precio_mensual, moneda_codigo, garantia,
                    duracion_minima_meses, fecha_disponible,
                    amoblado, estado_codigo,
                    usuario_id, habilitado
                  ) VALUES (
                    :titulo, :tipo_codigo, :descripcion,
                    :numero_habitaciones, :numero_banos, :bano_privado,
                    :tamano_m2, :genero_exclusivo_codigo,
                    :mascotas_permitidas, :fumadores_permitidos,
                    :ubicacion_id, :direccion, :latitud, :longitud,
                    :precio_mensual, :moneda_codigo, :garantia,
                    :duracion_minima_meses, :fecha_disponible,
                    :amoblado, :estado_codigo,
                    :usuario_id, true
                  ) RETURNING alojamiento_id";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':titulo', $datos['titulo']);
        $stmt->bindValue(':tipo_codigo', $datos['tipo_codigo'] ?? null);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? null);
        $stmt->bindValue(':numero_habitaciones', $datos['numero_habitaciones'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':numero_banos', $datos['numero_banos'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':bano_privado', isset($datos['bano_privado']) ? true : false, PDO::PARAM_BOOL);
        $stmt->bindValue(':tamano_m2', $datos['tamano_m2'] ?? null);
        $stmt->bindValue(':genero_exclusivo_codigo', $datos['genero_exclusivo_codigo'] ?? null);
        $stmt->bindValue(':mascotas_permitidas', isset($datos['mascotas_permitidas']) ? true : false, PDO::PARAM_BOOL);
        $stmt->bindValue(':fumadores_permitidos', isset($datos['fumadores_permitidos']) ? true : false, PDO::PARAM_BOOL);
        $stmt->bindValue(':ubicacion_id', $datos['ubicacion_id'] ?? null);
        $stmt->bindValue(':direccion', $datos['direccion'] ?? null);
        $stmt->bindValue(':latitud', $datos['latitud'] ?? null);
        $stmt->bindValue(':longitud', $datos['longitud'] ?? null);
        $stmt->bindValue(':precio_mensual', $datos['precio_mensual']);
        $stmt->bindValue(':moneda_codigo', $datos['moneda_codigo'] ?? 'PEN');
        $stmt->bindValue(':garantia', $datos['garantia'] ?? null);
        $stmt->bindValue(':duracion_minima_meses', $datos['duracion_minima_meses'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':fecha_disponible', $datos['fecha_disponible'] ?? null);
        $stmt->bindValue(':amoblado', isset($datos['amoblado']) ? true : false, PDO::PARAM_BOOL);
        $stmt->bindValue(':estado_codigo', $datos['estado_codigo'] ?? 'EPA001');
        $stmt->bindValue(':usuario_id', $datos['usuario_id']);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['alojamiento_id'] : null;
    }

    /**
     * Actualizar un alojamiento
     */
    public function actualizar($alojamiento_id, $datos)
    {
        $query = "UPDATE alojamiento SET
                    titulo = :titulo,
                    tipo_codigo = :tipo_codigo,
                    descripcion = :descripcion,
                    numero_habitaciones = :numero_habitaciones,
                    numero_banos = :numero_banos,
                    bano_privado = :bano_privado,
                    tamano_m2 = :tamano_m2,
                    genero_exclusivo_codigo = :genero_exclusivo_codigo,
                    mascotas_permitidas = :mascotas_permitidas,
                    fumadores_permitidos = :fumadores_permitidos,
                    ubicacion_id = :ubicacion_id,
                    direccion = :direccion,
                    latitud = :latitud,
                    longitud = :longitud,
                    precio_mensual = :precio_mensual,
                    moneda_codigo = :moneda_codigo,
                    garantia = :garantia,
                    duracion_minima_meses = :duracion_minima_meses,
                    fecha_disponible = :fecha_disponible,
                    amoblado = :amoblado,
                    modificado = CURRENT_TIMESTAMP
                  WHERE alojamiento_id = :alojamiento_id AND usuario_id = :usuario_id";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':titulo', $datos['titulo']);
        $stmt->bindValue(':tipo_codigo', $datos['tipo_codigo'] ?? null);
        $stmt->bindValue(':descripcion', $datos['descripcion'] ?? null);
        $stmt->bindValue(':numero_habitaciones', $datos['numero_habitaciones'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':numero_banos', $datos['numero_banos'] ?? 1, PDO::PARAM_INT);
        $stmt->bindValue(':bano_privado', isset($datos['bano_privado']) ? true : false, PDO::PARAM_BOOL);
        $stmt->bindValue(':tamano_m2', $datos['tamano_m2'] ?? null);
        $stmt->bindValue(':genero_exclusivo_codigo', $datos['genero_exclusivo_codigo'] ?? null);
        $stmt->bindValue(':mascotas_permitidas', isset($datos['mascotas_permitidas']) ? true : false, PDO::PARAM_BOOL);
        $stmt->bindValue(':fumadores_permitidos', isset($datos['fumadores_permitidos']) ? true : false, PDO::PARAM_BOOL);
        $stmt->bindValue(':ubicacion_id', $datos['ubicacion_id'] ?? null);
        $stmt->bindValue(':direccion', $datos['direccion'] ?? null);
        $stmt->bindValue(':latitud', $datos['latitud'] ?? null);
        $stmt->bindValue(':longitud', $datos['longitud'] ?? null);
        $stmt->bindValue(':precio_mensual', $datos['precio_mensual']);
        $stmt->bindValue(':moneda_codigo', $datos['moneda_codigo'] ?? 'PEN');
        $stmt->bindValue(':garantia', $datos['garantia'] ?? null);
        $stmt->bindValue(':duracion_minima_meses', $datos['duracion_minima_meses'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':fecha_disponible', $datos['fecha_disponible'] ?? null);
        $stmt->bindValue(':amoblado', isset($datos['amoblado']) ? true : false, PDO::PARAM_BOOL);
        $stmt->bindValue(':alojamiento_id', $alojamiento_id);
        $stmt->bindValue(':usuario_id', $datos['usuario_id']);

        return $stmt->execute();
    }

    /**
     * Eliminar (deshabilitar) un alojamiento
     */
    public function eliminar($alojamiento_id, $usuario_id)
    {
        $query = "UPDATE alojamiento SET habilitado = false, modificado = CURRENT_TIMESTAMP
                  WHERE alojamiento_id = :alojamiento_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':alojamiento_id', $alojamiento_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        return $stmt->execute();
    }
}
