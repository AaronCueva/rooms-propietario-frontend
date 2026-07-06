<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class Reserva
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las reservas (solicitudes) de los alojamientos del propietario.
     * Calcula estado FINALIZADA visualmente si lleva +7 días pendiente.
     *
     * Columnas reales de la tabla reserva:
     *   reserva_id, fecha_solicitud, fecha_ingreso, duracion_meses,
     *   monto_total, mensaje_presentacion, fecha_respuesta, estado_codigo,
     *   usuario_id, alojamiento_id, habilitado, creado, observacion
     */
    public function obtenerPorPropietario($usuario_id, $filtro_estado = null)
    {
        $query = "
            SELECT
                r.reserva_id,
                r.fecha_solicitud,
                r.fecha_ingreso,
                r.duracion_meses,
                r.monto_total,
                r.mensaje_presentacion,
                r.fecha_respuesta,
                r.estado_codigo,
                r.observacion,
                r.creado,
                r.modificado,
                -- Inquilino (columnas reales de usuario)
                u.usuario_id         AS inquilino_id,
                u.nombres            AS inquilino_nombres,
                u.apellido_paterno   AS inquilino_apellido_paterno,
                u.apellido_materno   AS inquilino_apellido_materno,
                u.correo             AS inquilino_correo,
                u.celular            AS inquilino_celular,
                u.telefono           AS inquilino_telefono,
                u.url_foto           AS inquilino_foto,
                -- Universidad (directa en usuario)
                univ.nombre          AS universidad_nombre,
                -- Alojamiento
                a.alojamiento_id,
                a.titulo             AS alojamiento_titulo,
                a.precio_mensual,
                a.direccion          AS alojamiento_direccion,
                -- Ubicación
                ub.nombre            AS distrito_nombre
            FROM reserva r
            INNER JOIN alojamiento a ON r.alojamiento_id = a.alojamiento_id
            INNER JOIN usuario u     ON r.usuario_id = u.usuario_id
            LEFT JOIN universidad univ ON u.universidad_id = univ.universidad_id
            LEFT JOIN ubicacion ub     ON a.ubicacion_id = ub.ubicacion_id
            WHERE a.usuario_id = :usuario_id
              AND r.habilitado = true
            ORDER BY
                CASE r.estado_codigo
                    WHEN 'ESRE001' THEN 1
                    WHEN 'ESRE005' THEN 2
                    WHEN 'ESRE002' THEN 3
                    WHEN 'ESRE003' THEN 4
                    WHEN 'ESRE004' THEN 5
                    ELSE 6
                END,
                r.creado DESC
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['usuario_id' => $usuario_id]);
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calcular nombre completo y estado FINALIZADA (solo visual, no toca BD)
            foreach ($reservas as &$reserva) {
                $reserva['inquilino_nombre_completo'] =
                    trim(($reserva['inquilino_nombres'] ?? '') . ' ' .
                         ($reserva['inquilino_apellido_paterno'] ?? '') . ' ' .
                         ($reserva['inquilino_apellido_materno'] ?? ''));

                $reserva['estado_calculado'] = false;

                if ($reserva['estado_codigo'] === 'ESRE001') {
                    $fecha_ref = $reserva['fecha_solicitud'] ?? $reserva['creado'];
                    if ($fecha_ref) {
                        $desde = new \DateTime($fecha_ref);
                        $ahora = new \DateTime();
                        $diff  = $ahora->diff($desde)->days;
                        if ($diff >= 7) {
                            $reserva['estado_codigo']    = 'ESRE004';
                            $reserva['estado_calculado'] = true;
                        }
                    }
                }
            }
            unset($reserva);

            // Filtrar por estado si se indicó
            if ($filtro_estado && $filtro_estado !== 'TODOS') {
                $reservas = array_filter($reservas, function ($r) use ($filtro_estado) {
                    return $r['estado_codigo'] === $filtro_estado;
                });
            }

            return array_values($reservas);
        } catch (Exception $e) {
            error_log("Error obteniendo reservas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una reserva por su ID, verificando que pertenezca al propietario.
     */
    public function obtenerPorId($reserva_id, $usuario_propietario_id)
    {
        $query = "
            SELECT
                r.reserva_id,
                r.fecha_solicitud,
                r.fecha_ingreso,
                r.duracion_meses,
                r.monto_total,
                r.mensaje_presentacion,
                r.fecha_respuesta,
                r.estado_codigo,
                r.observacion,
                r.creado,
                r.modificado,
                -- Inquilino
                u.usuario_id         AS inquilino_id,
                u.nombres            AS inquilino_nombres,
                u.apellido_paterno   AS inquilino_apellido_paterno,
                u.apellido_materno   AS inquilino_apellido_materno,
                u.correo             AS inquilino_correo,
                u.celular            AS inquilino_celular,
                u.telefono           AS inquilino_telefono,
                u.url_foto           AS inquilino_foto,
                u.descripcion        AS inquilino_descripcion,
                -- Universidad
                univ.nombre          AS universidad_nombre,
                -- Alojamiento
                a.alojamiento_id,
                a.titulo             AS alojamiento_titulo,
                a.precio_mensual,
                a.direccion          AS alojamiento_direccion,
                a.numero_habitaciones,
                a.numero_banos,
                a.tamano_m2,
                -- Ubicación
                ub.nombre            AS distrito_nombre
            FROM reserva r
            INNER JOIN alojamiento a ON r.alojamiento_id = a.alojamiento_id
            INNER JOIN usuario u     ON r.usuario_id = u.usuario_id
            LEFT JOIN universidad univ ON u.universidad_id = univ.universidad_id
            LEFT JOIN ubicacion ub     ON a.ubicacion_id = ub.ubicacion_id
            WHERE r.reserva_id  = :reserva_id
              AND a.usuario_id  = :usuario_id
              AND r.habilitado  = true
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'reserva_id' => $reserva_id,
                'usuario_id' => $usuario_propietario_id,
            ]);
            $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reserva) return null;

            // Nombre completo del inquilino
            $reserva['inquilino_nombre_completo'] =
                trim(($reserva['inquilino_nombres'] ?? '') . ' ' .
                     ($reserva['inquilino_apellido_paterno'] ?? '') . ' ' .
                     ($reserva['inquilino_apellido_materno'] ?? ''));

            // Iniciales para avatar
            $reserva['inquilino_iniciales'] =
                strtoupper(
                    substr($reserva['inquilino_nombres'] ?? 'I', 0, 1) .
                    substr($reserva['inquilino_apellido_paterno'] ?? 'N', 0, 1)
                );

            // Calcular FINALIZADA visualmente
            $reserva['estado_calculado'] = false;
            if ($reserva['estado_codigo'] === 'ESRE001') {
                $fecha_ref = $reserva['fecha_solicitud'] ?? $reserva['creado'];
                if ($fecha_ref) {
                    $desde = new \DateTime($fecha_ref);
                    $ahora = new \DateTime();
                    $diff  = $ahora->diff($desde)->days;
                    if ($diff >= 7) {
                        $reserva['estado_codigo']    = 'ESRE004';
                        $reserva['estado_calculado'] = true;
                    }
                }
            }

            return $reserva;
        } catch (Exception $e) {
            error_log("Error obteniendo reserva: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cuenta las solicitudes PENDIENTES (ESRE001) con menos de 7 días de antigüedad
     * del propietario — para el badge del sidebar.
     */
    public function contarPendientes($usuario_propietario_id)
    {
        $query = "
            SELECT COUNT(*) AS total
            FROM reserva r
            INNER JOIN alojamiento a ON r.alojamiento_id = a.alojamiento_id
            WHERE a.usuario_id   = :usuario_id
              AND r.estado_codigo = 'ESRE001'
              AND r.habilitado    = true
              AND COALESCE(r.fecha_solicitud, r.creado) > (NOW() - INTERVAL '7 days')
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['usuario_id' => $usuario_propietario_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['total'] ?? 0);
        } catch (Exception $e) {
            error_log("Error contando pendientes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Cambia el estado de una reserva (y opcionalmente guarda la observación).
     * Usa el campo `observacion` de la tabla.
     */
    public function cambiarEstado($reserva_id, $nuevo_estado, $observacion = null)
    {
        $query = "
            UPDATE reserva
            SET estado_codigo  = :estado_codigo,
                observacion    = COALESCE(:observacion, observacion),
                fecha_respuesta = CASE
                    WHEN :nuevo_estado_check IN ('ESRE002','ESRE003') THEN CURRENT_TIMESTAMP
                    ELSE fecha_respuesta
                END,
                modificado     = CURRENT_TIMESTAMP
            WHERE reserva_id   = :reserva_id
              AND habilitado    = true
        ";

        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'reserva_id'         => $reserva_id,
                'estado_codigo'      => $nuevo_estado,
                'observacion'        => $observacion,
                'nuevo_estado_check' => $nuevo_estado,
            ]);
        } catch (Exception $e) {
            error_log("Error cambiando estado de reserva: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Conteos de reservas por estado para los tabs de filtro.
     * Incluye corrección visual: las ESRE001 con +7 días se suman a ESRE004.
     */
    public function obtenerConteosPorEstado($usuario_propietario_id)
    {
        $query = "
            SELECT
                estado_codigo,
                COUNT(*) AS total,
                -- Cuántas de las pendientes llevan más de 7 días (serán ESRE004 visualmente)
                SUM(CASE
                    WHEN estado_codigo = 'ESRE001'
                     AND COALESCE(fecha_solicitud, creado) <= (NOW() - INTERVAL '7 days')
                    THEN 1 ELSE 0
                END) AS finalizadas_ocultas
            FROM reserva r
            INNER JOIN alojamiento a ON r.alojamiento_id = a.alojamiento_id
            WHERE a.usuario_id = :usuario_id
              AND r.habilitado = true
            GROUP BY estado_codigo
        ";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['usuario_id' => $usuario_propietario_id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $conteos = [
                'TODOS'   => 0,
                'ESRE001' => 0,
                'ESRE002' => 0,
                'ESRE003' => 0,
                'ESRE004' => 0,
                'ESRE005' => 0,
            ];

            foreach ($rows as $row) {
                $codigo             = $row['estado_codigo'];
                $total              = (int)$row['total'];
                $fin_ocultas        = (int)$row['finalizadas_ocultas'];
                $total_real         = $total - $fin_ocultas;

                if (isset($conteos[$codigo])) {
                    $conteos[$codigo] += $total_real;
                }

                // Las que eran PENDIENTE pero ya tienen +7 días van al conteo de FINALIZADA
                $conteos['ESRE004']  += $fin_ocultas;
                $conteos['TODOS']    += $total;
            }

            return $conteos;
        } catch (Exception $e) {
            error_log("Error obteniendo conteos: " . $e->getMessage());
            return ['TODOS' => 0, 'ESRE001' => 0, 'ESRE002' => 0, 'ESRE003' => 0, 'ESRE004' => 0, 'ESRE005' => 0];
        }
    }
}
