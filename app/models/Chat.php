<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Modelo Chat — Portal Propietario.
 * Lógica simétrica al lado inquilino (mismas tablas, mismo proyecto Supabase).
 * El propietario solo lee y responde hilos donde es participante (no crea hilos).
 */
class Chat
{
    private $db;

    public const ESTADO_ENVIADO = 'ENVIADO'; // no leído
    public const ESTADO_LEIDO   = 'LEIDO';
    public const MAX_LEN        = 2000;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * ¿El usuario es participante habilitado del chat?
     */
    public function esParticipante(string $chat_id, string $usuario_id): bool
    {
        $sql = "SELECT 1 FROM chat_usuario
                WHERE chat_id = :c AND usuario_id = :u AND habilitado = true
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':c', $chat_id);
        $stmt->bindValue(':u', $usuario_id);
        $stmt->execute();
        return (bool)$stmt->fetch();
    }

    /**
     * Hilos del usuario con el otro participante, último mensaje y no leídos.
     * Búsqueda opcional por nombre del inquilino o contenido de mensajes.
     */
    public function getChatsByUsuario(string $usuario_id, string $busqueda = ''): array
    {
        $busqueda = trim($busqueda);

        $sql = "SELECT cu.chat_id,
                       u.usuario_id AS otro_id, u.nombres AS otro_nombre,
                       u.apellido_paterno AS otro_apellido, u.url_foto AS otro_foto,
                       lm.contenido AS ultimo_contenido, lm.fecha_envio AS ultimo_fecha,
                       (SELECT COUNT(*) FROM mensaje m
                          WHERE m.chat_id = cu.chat_id AND m.usuario_id <> :uid
                            AND m.estado_lectura_codigo <> 'LEIDO' AND m.habilitado = true) AS no_leidos
                FROM chat_usuario cu
                JOIN chat ch ON cu.chat_id = ch.chat_id
                JOIN chat_usuario cu2 ON cu2.chat_id = cu.chat_id AND cu2.usuario_id <> :uid
                JOIN usuario u ON cu2.usuario_id = u.usuario_id
                LEFT JOIN (
                   SELECT DISTINCT ON (chat_id) chat_id, contenido, fecha_envio
                   FROM mensaje WHERE habilitado = true
                   ORDER BY chat_id, fecha_envio DESC
                ) lm ON lm.chat_id = cu.chat_id
                WHERE cu.usuario_id = :uid AND cu.habilitado = true";

        $params = [':uid' => $usuario_id];

        if ($busqueda !== '') {
            $sql .= " AND (u.nombres ILIKE :q OR u.apellido_paterno ILIKE :q
                     OR EXISTS (SELECT 1 FROM mensaje m
                                  WHERE m.chat_id = cu.chat_id AND m.contenido ILIKE :q))";
            $params[':q'] = '%' . $busqueda . '%';
        }

        $sql .= " ORDER BY ultimo_fecha DESC NULLS LAST";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['no_leidos'] = (int)($r['no_leidos'] ?? 0);
        }
        unset($r);

        return $rows;
    }

    /**
     * Mensajes de un chat. Valida participación; si no, devuelve [].
     */
    public function getMensajes(string $chat_id, string $usuario_id): array
    {
        if (!$this->esParticipante($chat_id, $usuario_id)) {
            return [];
        }

        $sql = "SELECT mensaje_id, contenido, fecha_envio, usuario_id, (usuario_id = :u) AS es_mio
                FROM mensaje
                WHERE chat_id = :c AND habilitado = true
                ORDER BY fecha_envio ASC, creado ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':u', $usuario_id);
        $stmt->bindValue(':c', $chat_id);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            // PDO pgsql puede devolver 't'/'f' o true/false
            $r['es_mio'] = in_array($r['es_mio'], [true, 't', 'true', 1, '1'], true);
        }
        unset($r);

        return $rows;
    }

    /**
     * Envía un mensaje. Valida participación, trim, vacío y >MAX_LEN.
     * Devuelve la fila (con es_mio=true) o null si es inválido/no autorizado.
     */
    public function enviarMensaje(string $chat_id, string $usuario_id, string $contenido): ?array
    {
        if (!$this->esParticipante($chat_id, $usuario_id)) {
            return null;
        }

        $contenido = trim($contenido);
        if ($contenido === '') {
            return null;
        }

        $len = function_exists('mb_strlen') ? \mb_strlen($contenido) : strlen($contenido);
        if ($len > self::MAX_LEN) {
            return null;
        }

        // OJO: :u (uuid usuario_id) y :upor (varchar creado_por) son tipos distintos:
        // no reuses el mismo placeholder o Postgres lanza "inconsistent types deduced for parameter".
        $sql = "INSERT INTO mensaje (contenido, fecha_envio, estado_lectura_codigo, chat_id, usuario_id, habilitado, creado_por)
                VALUES (:c, now(), 'ENVIADO', :chat, :u, true, :upor)
                RETURNING mensaje_id, contenido, fecha_envio, usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':c', $contenido);
        $stmt->bindValue(':chat', $chat_id);
        $stmt->bindValue(':u', $usuario_id);
        $stmt->bindValue(':upor', $usuario_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }
        $row['es_mio'] = true;
        return $row;
    }

    /**
     * Marca como leídos los mensajes del otro participante.
     */
    public function marcarLeido(string $chat_id, string $usuario_id): void
    {
        $sql = "UPDATE mensaje
                SET estado_lectura_codigo = 'LEIDO', modificado = now(), modificado_por = :upor
                WHERE chat_id = :c AND usuario_id <> :u
                  AND estado_lectura_codigo <> 'LEIDO' AND habilitado = true";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':upor', $usuario_id);
        $stmt->bindValue(':c', $chat_id);
        $stmt->bindValue(':u', $usuario_id);
        $stmt->execute();
    }

    /**
     * Total de mensajes no leídos dirigidos al usuario (en chats donde participa).
     */
    public function contarNoLeidos(string $usuario_id): int
    {
        $sql = "SELECT COUNT(*) FROM mensaje m
                WHERE m.usuario_id <> :u AND m.estado_lectura_codigo <> 'LEIDO' AND m.habilitado = true
                  AND EXISTS (SELECT 1 FROM chat_usuario cu
                                WHERE cu.chat_id = m.chat_id AND cu.usuario_id = :u AND cu.habilitado = true)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':u', $usuario_id);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Devuelve el otro participante del chat (el inquilino).
     */
    public function getOtroParticipante(string $chat_id, string $usuario_id): ?array
    {
        $sql = "SELECT u.usuario_id, u.nombres, u.apellido_paterno, u.url_foto
                FROM chat_usuario cu JOIN usuario u ON cu.usuario_id = u.usuario_id
                WHERE cu.chat_id = :c AND cu.usuario_id <> :u AND cu.habilitado = true
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':c', $chat_id);
        $stmt->bindValue(':u', $usuario_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
