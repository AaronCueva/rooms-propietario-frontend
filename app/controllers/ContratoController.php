<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Contrato;
use App\Models\Reserva;
use App\Models\Alojamiento;
use App\Models\Multimedia;
use App\Models\Usuario;
use App\Models\Pago;

class ContratoController extends Controller
{
    private $contratoModel;
    private $reservaModel;
    private $alojamientoModel;
    private $multimediaModel;
    private $usuarioModel;
    private $pagoModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/login');
        }
        $this->contratoModel = new Contrato();
        $this->reservaModel = new Reserva();
        $this->alojamientoModel = new Alojamiento();
        $this->multimediaModel = new Multimedia();
        $this->usuarioModel = new Usuario();
        $this->pagoModel = new Pago();
    }

    /**
     * Listado de contratos del propietario
     */
    public function index()
    {
        $usuario_id = $_SESSION['usuario_id'];
        $estado = $_GET['estado'] ?? null;
        
        $contratos = $this->contratoModel->obtenerPorPropietario($usuario_id, $estado);

        // Sumar servicios al total a pagar para mostrar en el listado
        foreach ($contratos as &$c) {
            $servicios = $this->contratoModel->obtenerServiciosPorAlojamiento($c['alojamiento_id']);
            $total_servicios = 0;
            foreach ($servicios as $srv) {
                $total_servicios += floatval($srv['precio'] ?? 0);
            }
            $c['total_pagar'] = floatval($c['monto_renta']) + $total_servicios;
        }
        unset($c);

        $this->render('propietario/contratos/index', [
            'contratos' => $contratos,
            'estado_filtro' => $estado,
            'titulo' => 'Mis Contratos'
        ]);
    }

    /**
     * Mostrar formulario para formalizar contrato a partir de reserva
     */
    public function formalizar()
    {
        $usuario_id = $_SESSION['usuario_id'];
        $reserva_id = $_GET['reserva_id'] ?? null;

        if (!$reserva_id) {
            $this->redirect('/solicitudes');
        }

        $solicitud = $this->reservaModel->obtenerPorId($reserva_id, $usuario_id);

        if (!$solicitud || $solicitud['estado_codigo'] !== 'ESRE002') {
            $this->setFlash('error', 'La solicitud debe estar aprobada para formalizar un contrato.');
            $this->redirect('/solicitudes/detalle?id=' . $reserva_id);
        }

        $servicios = $this->contratoModel->obtenerServiciosPorAlojamiento($solicitud['alojamiento_id']);
        $total_servicios = 0;
        foreach ($servicios as $srv) {
            $total_servicios += floatval($srv['precio'] ?? 0);
        }

        $this->render('propietario/contratos/formalizar', [
            'solicitud' => $solicitud,
            'total_servicios' => $total_servicios,
            'titulo' => 'Formalizar Contrato'
        ]);
    }

    /**
     * Guardar el contrato
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/contratos');
        }

        $usuario_id = $_SESSION['usuario_id'];
        $reserva_id = $_POST['reserva_id'] ?? null;
        
        if (!$reserva_id) {
            $this->setFlash('error', 'Datos inválidos.');
            $this->redirect('/solicitudes');
        }

        // Verificar que la reserva sea del propietario y esté aprobada
        $solicitud = $this->reservaModel->obtenerPorId($reserva_id, $usuario_id);
        if (!$solicitud || $solicitud['estado_codigo'] !== 'ESRE002') {
            $this->setFlash('error', 'Reserva inválida o no aprobada.');
            $this->redirect('/solicitudes');
        }

        // Subir documento del contrato — Azure Blob Storage
        if (!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Debe subir el documento del contrato (PDF o imagen).');
            $this->redirect('/contratos/formalizar?reserva_id=' . $reserva_id);
        }

        $archivo = $_FILES['documento'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extension, $allowed)) {
            $this->setFlash('error', 'El documento debe ser PDF, JPG, PNG o WEBP.');
            $this->redirect('/contratos/formalizar?reserva_id=' . $reserva_id);
        }

        $nuevoNombre = 'contratos/contrato_' . $reserva_id . '_' . time() . '.' . $extension;

        $mimeType = 'application/octet-stream';
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($archivo['tmp_name']);
        }
        if (!$mimeType) $mimeType = 'application/octet-stream';

        $azureUrl = \App\Core\AzureStorage::uploadFile($archivo['tmp_name'], $nuevoNombre, $mimeType);

        if (!$azureUrl) {
            // Fallback local si Azure falla o no está configurado
            $azureUrl = \App\Core\AzureStorage::uploadFileLocal($archivo['tmp_name'], $nuevoNombre);
        }

        if ($azureUrl) {
            // Guardar en multimedia
            $multimedia_id = $this->multimediaModel->guardarDocumentoContrato($azureUrl, $archivo['name']);

            if (!$multimedia_id) {
                $this->setFlash('error', 'Error al registrar el documento en BD.');
                $this->redirect('/contratos/formalizar?reserva_id=' . $reserva_id);
            }

            // Crear contrato
            $datosContrato = [
                'reserva_id' => $reserva_id,
                'multimedia_id' => $multimedia_id,
                'fecha_inicio' => $_POST['fecha_inicio'],
                'fecha_fin' => $_POST['fecha_fin'],
                'monto_renta' => $_POST['monto_renta'],
                'monto_garantia' => $_POST['monto_garantia'] ?? 0,
                'fecha_pago_mensual' => $_POST['fecha_pago_mensual'] ?? 1,
                'cargo_plataforma' => 3.00 // 3% por defecto
            ];

            $contrato_id = $this->contratoModel->crear($datosContrato);

            if ($contrato_id) {
                // Generar cuotas (pagos)
                $this->pagoModel->generarCuotas(
                    $contrato_id,
                    $_POST['monto_renta'],
                    $_POST['fecha_inicio'],
                    $_POST['fecha_fin'],
                    $_POST['fecha_pago_mensual'] ?? 1,
                    $usuario_id
                );

                // Cambiar estado alojamiento a OCUPADO (EPA004)
                $this->alojamientoModel->cambiarEstado($solicitud['alojamiento_id'], 'EPA004');

                // Cambiar estado de la reserva a FORMALIZADO (ESRE006)
                $this->reservaModel->cambiarEstado($reserva_id, 'ESRE006');

                $this->setFlash('success', 'Contrato formalizado correctamente. El alojamiento ahora figura como ocupado.');
                $this->redirect('/contratos/detalle?id=' . $contrato_id);
            } else {
                $this->setFlash('error', 'Error al crear el contrato en base de datos.');
                $this->redirect('/contratos/formalizar?reserva_id=' . $reserva_id);
            }
        } else {
            $this->setFlash('error', 'No se pudo guardar el documento (ni en Azure ni en almacenamiento local).');
            $this->redirect('/contratos/formalizar?reserva_id=' . $reserva_id);
        }
    }

    /**
     * Detalle del contrato
     */
    public function detalle()
    {
        $usuario_id = $_SESSION['usuario_id'];
        $contrato_id = $_GET['id'] ?? null;

        if (!$contrato_id) {
            $this->redirect('/contratos');
        }

        $contrato = $this->contratoModel->obtenerDetalle($contrato_id, $usuario_id);

        if (!$contrato) {
            $this->setFlash('error', 'Contrato no encontrado.');
            $this->redirect('/contratos');
        }

        $this->render('propietario/contratos/detalle', [
            'contrato' => $contrato,
            'servicios' => $this->contratoModel->obtenerServiciosPorAlojamiento($contrato['alojamiento_id']),
            'politicas' => $this->contratoModel->obtenerPoliticasPorAlojamiento($contrato['alojamiento_id']),
            'descuentos' => $this->contratoModel->obtenerDescuentosPorAlojamiento($contrato['alojamiento_id']),
            'beneficios' => $this->contratoModel->obtenerBeneficiosPorAlojamiento($contrato['alojamiento_id']),
            'resenas' => (new \App\Models\Resena())->getByContratoId($contrato_id),
            'titulo' => 'Detalle del Contrato'
        ]);
    }

    /**
     * El propietario responde a una reseña del contrato (tabla resena)
     */
    public function responderResena()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/contratos');
        }

        $usuario_id = $_SESSION['usuario_id'];
        $contrato_id = $_POST['contrato_id'] ?? null;
        $resena_id = $_POST['resena_id'] ?? null;
        $respuesta = trim($_POST['respuesta'] ?? '');

        if (!$contrato_id || !$resena_id || $respuesta === '') {
            $this->setFlash('error', 'Datos inválidos para responder la reseña.');
            $this->redirect('/contratos/detalle?id=' . $contrato_id);
        }

        // Verificar que el contrato pertenece al propietario
        $contrato = $this->contratoModel->obtenerDetalle($contrato_id, $usuario_id);
        if (!$contrato) {
            $this->setFlash('error', 'Contrato no encontrado.');
            $this->redirect('/contratos');
        }

        $resenaModel = new \App\Models\Resena();
        if ($resenaModel->responder($resena_id, $usuario_id, $respuesta)) {
            $this->setFlash('success', 'Respuesta publicada correctamente.');
        } else {
            $this->setFlash('error', 'No se pudo guardar la respuesta.');
        }

        $this->redirect('/contratos/detalle?id=' . $contrato_id);
    }

    /**
     * Finalizar contrato
     */
    public function finalizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/contratos');
        }

        $usuario_id = $_SESSION['usuario_id'];
        $contrato_id = $_POST['contrato_id'] ?? null;
        $from_inquilino = $_POST['from_inquilino'] ?? false;
        $redirect_url = $from_inquilino ? '/inquilinos/detalle?id=' . $contrato_id : '/contratos/detalle?id=' . $contrato_id;

        if (!$contrato_id) {
            $this->redirect('/contratos');
        }

        $contrato = $this->contratoModel->obtenerDetalle($contrato_id, $usuario_id);
        if (!$contrato || $contrato['estado_codigo'] !== 'ESCO001') {
            $this->setFlash('error', 'El contrato no se puede finalizar.');
            $this->redirect($redirect_url);
        }

        // Marcar como finalizado
        if ($this->contratoModel->actualizarEstado($contrato_id, 'ESCO002')) {
            // Liberar alojamiento (cambiar a EPA001 - ACTIVO)
            $this->alojamientoModel->cambiarEstado($contrato['alojamiento_id'], 'EPA003');

            $this->setFlash('success', 'Contrato finalizado. El alojamiento vuelve a estar Activo. Por favor, califique al inquilino.');
        } else {
            $this->setFlash('error', 'Error al finalizar el contrato.');
        }

        $this->redirect($redirect_url);
    }

    /**
     * Calificar inquilino al final del contrato
     */
    public function calificar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/contratos');
        }

        $usuario_id = $_SESSION['usuario_id'];
        $contrato_id = $_POST['contrato_id'] ?? null;
        $puntuacion = (int)($_POST['puntuacion'] ?? 0);
        $from_inquilino = $_POST['from_inquilino'] ?? false;
        $redirect_url = $from_inquilino ? '/inquilinos/detalle?id=' . $contrato_id : '/contratos/detalle?id=' . $contrato_id;

        if ($puntuacion < 1 || $puntuacion > 5) {
            $this->setFlash('error', 'Puntuación inválida.');
            $this->redirect($redirect_url);
        }

        $contrato = $this->contratoModel->obtenerDetalle($contrato_id, $usuario_id);
        if (!$contrato || $contrato['estado_codigo'] !== 'ESCO002') {
            $this->setFlash('error', 'El contrato debe estar finalizado para calificar.');
            $this->redirect($redirect_url);
        }

        if (!empty($contrato['propietario_califico'])) {
            $this->setFlash('error', 'Ya has calificado a este inquilino por este contrato.');
            $this->redirect($redirect_url);
        }

        // Actualizar promedio en usuario (inquilino)
        if ($this->usuarioModel->agregarCalificacion($contrato['inquilino_id'], $puntuacion)) {
            $this->contratoModel->marcarComoCalificado($contrato_id);
            $this->setFlash('success', 'Calificación registrada exitosamente.');
        } else {
            $this->setFlash('error', 'No se pudo registrar la calificación.');
        }

        $this->redirect($redirect_url);
    }
}
