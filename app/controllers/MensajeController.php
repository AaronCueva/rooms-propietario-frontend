<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Chat;

/**
 * MensajeController — Portal Propietario.
 * Inbox + conversación + enviar + fallback AJAX de mensajes nuevos.
 */
class MensajeController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/login');
        }
    }

    /**
     * Inbox (lista de hilos) + conversación activa.
     */
    public function index()
    {
        $uid = $_SESSION['usuario_id'];
        $chatActivo = isset($_GET['chat']) ? (string)$_GET['chat'] : null;
        $busqueda = trim($_GET['q'] ?? '');

        try {
            $cm = new Chat();
            $chats = $cm->getChatsByUsuario($uid, $busqueda);

            $mensajes = [];
            $otro = null;
            if ($chatActivo && $cm->esParticipante($chatActivo, $uid)) {
                $mensajes = $cm->getMensajes($chatActivo, $uid);
                $cm->marcarLeido($chatActivo, $uid);
                $otro = $cm->getOtroParticipante($chatActivo, $uid);
            }
            // Si chatActivo pero NO es participante → mensajes=[], otro=null (no exponer datos ajenos)

            $totalNoLeidos = $cm->contarNoLeidos($uid);

            require_once __DIR__ . '/../config/supabase.php';

            $this->render('propietario/mensajes/index', [
                'titulo' => 'Mensajes',
                'chats' => $chats,
                'chatActivo' => $chatActivo,
                'mensajes' => $mensajes,
                'otro' => $otro,
                'busqueda' => $busqueda,
                'totalNoLeidos' => $totalNoLeidos,
                'supabaseUrl' => SUPABASE_URL,
                'supabaseAnonKey' => SUPABASE_ANON_KEY,
                'uid' => $uid,
            ], 'main');
        } catch (\Throwable $ex) {
            // Mostrar el error real en vez de pantalla blanca (diagnóstico).
            while (ob_get_level() > 0) { ob_end_clean(); }
            http_response_code(500);
            echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Error Mensajes</title>'
               . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">'
               . '</head><body class="p-4"><div class="container"><div class="alert alert-danger">'
               . '<h4 class="alert-heading">Error al cargar Mensajes</h4>'
               . '<pre class="mb-0" style="white-space:pre-wrap"><b>' . htmlspecialchars(get_class($ex)) . ':</b> '
               . htmlspecialchars($ex->getMessage()) . "\n\n" . htmlspecialchars($ex->getTraceAsString()) . '</pre>'
               . '</div><a href="/dashboard" class="btn btn-secondary">Volver al dashboard</a></div></body></html>';
            exit;
        }
    }

    /**
     * Envía un mensaje (POST, responde JSON).
     */
    public function enviar()
    {
        $uid = $_SESSION['usuario_id'];
        $chatId = (string)($_POST['chat_id'] ?? '');
        $contenido = (string)($_POST['contenido'] ?? '');

        header('Content-Type: application/json; charset=utf-8');
        if ($chatId === '') {
            echo json_encode(['ok' => false, 'error' => 'chat inválido']);
            exit;
        }

        $cm = new Chat();
        if (!$cm->esParticipante($chatId, $uid)) {
            echo json_encode(['ok' => false, 'error' => 'no autorizado']);
            exit;
        }

        $msg = $cm->enviarMensaje($chatId, $uid, $contenido);
        if ($msg === null) {
            echo json_encode(['ok' => false, 'error' => 'mensaje inválido (vacío o >2000 chars)']);
            exit;
        }

        echo json_encode(['ok' => true, 'mensaje' => $msg]);
        exit;
    }

    /**
     * Fallback AJAX: mensajes nuevos desde `ultimo` (GET, responde JSON).
     */
    public function nuevo()
    {
        $uid = $_SESSION['usuario_id'];
        $chatId = (string)($_GET['chat'] ?? '');
        $ultimo = $_GET['ultimo'] ?? '';

        header('Content-Type: application/json; charset=utf-8');
        $cm = new Chat();
        if ($chatId === '' || !$cm->esParticipante($chatId, $uid)) {
            echo json_encode(['ok' => false, 'mensajes' => []]);
            exit;
        }

        $todos = $cm->getMensajes($chatId, $uid);
        if ($ultimo === '') {
            echo json_encode(['ok' => true, 'mensajes' => $todos]);
            exit;
        }

        $nuevos = [];
        $encontrado = false;
        foreach ($todos as $m) {
            if ($encontrado) {
                $nuevos[] = $m;
            } elseif ($m['mensaje_id'] === $ultimo) {
                $encontrado = true;
            }
        }
        echo json_encode(['ok' => true, 'mensajes' => $nuevos]);
        exit;
    }
}
