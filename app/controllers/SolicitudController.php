<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Reserva;

class SolicitudController extends Controller
{
    private $reservaModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/login');
        }
        $this->reservaModel = new Reserva();
    }

    /**
     * Listado de solicitudes del propietario con filtro por estado
     */
    public function index()
    {
        $usuario_id    = $_SESSION['usuario_id'];
        $filtro_estado = $_GET['estado'] ?? 'TODOS';

        $solicitudes = $this->reservaModel->obtenerPorPropietario($usuario_id, $filtro_estado);
        $conteos     = $this->reservaModel->obtenerConteosPorEstado($usuario_id);

        $this->render('propietario/solicitudes/index', [
            'solicitudes'   => $solicitudes,
            'conteos'       => $conteos,
            'filtro_estado' => $filtro_estado,
            'titulo'        => 'Solicitudes de reserva'
        ]);
    }

    /**
     * Vista de detalle de una solicitud
     */
    public function detalle()
    {
        $usuario_id  = $_SESSION['usuario_id'];
        $reserva_id  = $_GET['id'] ?? null;

        if (!$reserva_id) {
            $this->redirect('/solicitudes');
        }

        $solicitud = $this->reservaModel->obtenerPorId($reserva_id, $usuario_id);

        if (!$solicitud) {
            $this->setFlash('error', 'Solicitud no encontrada o no tienes acceso.');
            $this->redirect('/solicitudes');
        }

        // Si está PENDIENTE, cambiar a EN REVISIÓN automáticamente
        if ($solicitud['estado_codigo'] === 'ESRE001' && !$solicitud['estado_calculado']) {
            $this->reservaModel->cambiarEstado($reserva_id, 'ESRE005');
            $solicitud['estado_codigo'] = 'ESRE005';
        }

        $this->render('propietario/solicitudes/detalle', [
            'solicitud' => $solicitud,
            'titulo'    => 'Detalle de solicitud'
        ]);
    }

    /**
     * Aprobar una solicitud (ESRE005 → ESRE002)
     */
    public function aprobar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/solicitudes');
        }

        $usuario_id = $_SESSION['usuario_id'];
        $reserva_id = $_POST['reserva_id'] ?? null;

        if (!$reserva_id) {
            $this->setFlash('error', 'Solicitud inválida.');
            $this->redirect('/solicitudes');
        }

        // Verificar que la solicitud sea del propietario
        $solicitud = $this->reservaModel->obtenerPorId($reserva_id, $usuario_id);

        if (!$solicitud) {
            $this->setFlash('error', 'Solicitud no encontrada.');
            $this->redirect('/solicitudes');
        }

        // Solo se puede aprobar si está EN REVISIÓN
        if (!in_array($solicitud['estado_codigo'], ['ESRE005', 'ESRE001'])) {
            $this->setFlash('error', 'Solo se pueden aprobar solicitudes en revisión.');
            $this->redirect('/solicitudes/detalle?id=' . $reserva_id);
        }

        // Validar que el alojamiento no esté ocupado
        $alojamientoModel = new \App\Models\Alojamiento();
        $alojamiento = $alojamientoModel->obtenerPorId($solicitud['alojamiento_id'], $usuario_id);
        
        if ($alojamiento && $alojamiento['estado_codigo'] === 'EPA004') {
            $this->setFlash('error', 'No puedes aprobar la solicitud porque el alojamiento ya se encuentra ocupado.');
            $this->redirect('/solicitudes/detalle?id=' . $reserva_id);
        }

        $ok = $this->reservaModel->cambiarEstado($reserva_id, 'ESRE002');

        if ($ok) {
            $this->setFlash('success', '¡Solicitud aprobada! Ahora puede proceder al contrato.');
        } else {
            $this->setFlash('error', 'No se pudo aprobar la solicitud.');
        }

        $this->redirect('/solicitudes/detalle?id=' . $reserva_id);
    }

    /**
     * Rechazar una solicitud con observación (→ ESRE003)
     */
    public function rechazar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/solicitudes');
        }

        $usuario_id  = $_SESSION['usuario_id'];
        $reserva_id  = $_POST['reserva_id'] ?? null;
        $observacion = trim($_POST['observacion'] ?? '');

        if (!$reserva_id) {
            $this->setFlash('error', 'Solicitud inválida.');
            $this->redirect('/solicitudes');
        }

        if (empty($observacion)) {
            $this->setFlash('error', 'Debes indicar el motivo del rechazo.');
            $this->redirect('/solicitudes/detalle?id=' . $reserva_id);
        }

        // Verificar acceso
        $solicitud = $this->reservaModel->obtenerPorId($reserva_id, $usuario_id);

        if (!$solicitud) {
            $this->setFlash('error', 'Solicitud no encontrada.');
            $this->redirect('/solicitudes');
        }

        if (!in_array($solicitud['estado_codigo'], ['ESRE001', 'ESRE005'])) {
            $this->setFlash('error', 'Esta solicitud ya no puede ser rechazada.');
            $this->redirect('/solicitudes/detalle?id=' . $reserva_id);
        }

        $ok = $this->reservaModel->cambiarEstado($reserva_id, 'ESRE003', $observacion);

        if ($ok) {
            $this->setFlash('success', 'Solicitud rechazada. Se ha notificado al inquilino.');
        } else {
            $this->setFlash('error', 'No se pudo rechazar la solicitud.');
        }

        $this->redirect('/solicitudes');
    }
}
