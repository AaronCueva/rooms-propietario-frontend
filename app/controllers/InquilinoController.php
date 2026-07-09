<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Contrato;

class InquilinoController extends Controller
{
    private $contratoModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/login');
        }
        $this->contratoModel = new Contrato();
    }

    /**
     * Listado de inquilinos (Vigentes / Historial)
     */
    public function index()
    {
        $usuario_id = $_SESSION['usuario_id'];
        $tab = $_GET['tab'] ?? 'ACTIVO'; // ACTIVO o HISTORIAL

        if (!in_array($tab, ['ACTIVO', 'HISTORIAL'])) {
            $tab = 'ACTIVO';
        }

        $inquilinos = $this->contratoModel->obtenerInquilinosPorPropietario($usuario_id, $tab);

        $this->render('propietario/inquilinos/index', [
            'inquilinos' => $inquilinos,
            'tab' => $tab,
            'titulo' => 'Mis Inquilinos'
        ]);
    }

    /**
     * Detalle del inquilino (asociado a un contrato específico)
     */
    public function detalle()
    {
        $usuario_id = $_SESSION['usuario_id'];
        $contrato_id = $_GET['id'] ?? null;

        if (!$contrato_id) {
            $this->redirect('/inquilinos');
        }

        // Reutilizamos el detalle del contrato que ya trae toda la info cruzada y valida permisos
        $detalle = $this->contratoModel->obtenerDetalle($contrato_id, $usuario_id);

        if (!$detalle) {
            $this->setFlash('error', 'Inquilino no encontrado o no tienes permisos.');
            $this->redirect('/inquilinos');
        }

        // Obtener calificación general del inquilino desde la tabla usuario
        // Traemos el DNI y universidad
        $usuarioModel = new \App\Models\Usuario();
        $inquilinoDB = $usuarioModel->findById($detalle['inquilino_id']);

        $detalle['inquilino_calificacion'] = $inquilinoDB['calificacion'] ?? 0;
        $detalle['inquilino_total_calificaciones'] = $inquilinoDB['total_calificaciones'] ?? 0;
        $detalle['inquilino_documento'] = $inquilinoDB['numero_documento'] ?? null;
        $detalle['universidad_nombre'] = $inquilinoDB['universidad_nombre'] ?? null;

        $servicios = $this->contratoModel->obtenerServiciosPorAlojamiento($detalle['alojamiento_id']);

        $this->render('propietario/inquilinos/detalle', [
            'detalle' => $detalle,
            'servicios' => $servicios,
            'titulo' => 'Perfil del Inquilino'
        ]);
    }
}
