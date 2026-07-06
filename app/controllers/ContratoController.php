<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Contrato;
use App\Models\Reserva;
use App\Models\Alojamiento;
use App\Models\Multimedia;
use App\Models\Usuario;

class ContratoController extends Controller
{
    private $contratoModel;
    private $reservaModel;
    private $alojamientoModel;
    private $multimediaModel;
    private $usuarioModel;

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
    }

    /**
     * Listado de contratos del propietario
     */
    public function index()
    {
        $usuario_id = $_SESSION['usuario_id'];
        $estado = $_GET['estado'] ?? null;
        
        $contratos = $this->contratoModel->obtenerPorPropietario($usuario_id, $estado);

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

        $this->render('propietario/contratos/formalizar', [
            'solicitud' => $solicitud,
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

        // Subir archivo PDF
        if (!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Debe subir el documento del contrato (PDF).');
            $this->redirect('/contratos/formalizar?reserva_id=' . $reserva_id);
        }

        $archivo = $_FILES['documento'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        
        if ($extension !== 'pdf') {
            $this->setFlash('error', 'El documento debe ser un archivo PDF.');
            $this->redirect('/contratos/formalizar?reserva_id=' . $reserva_id);
        }

        // Directorio de uploads (local)
        $uploadDir = __DIR__ . '/../../public/uploads/contratos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $nuevoNombre = 'contrato_' . $reserva_id . '_' . time() . '.pdf';
        $rutaDestino = $uploadDir . $nuevoNombre;
        $urlBD = '/public/uploads/contratos/' . $nuevoNombre;

        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            // Guardar en multimedia
            $multimedia_id = $this->multimediaModel->guardarDocumentoContrato($urlBD, $archivo['name']);

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
                'fecha_pago_mensual' => $_POST['fecha_pago_mensual'] ?? 1
            ];

            $contrato_id = $this->contratoModel->crear($datosContrato);

            if ($contrato_id) {
                // Cambiar estado alojamiento a OCUPADO (EPA004)
                $this->alojamientoModel->cambiarEstado($solicitud['alojamiento_id'], 'EPA004');

                $this->setFlash('success', 'Contrato formalizado correctamente. El alojamiento ahora figura como ocupado.');
                $this->redirect('/contratos/detalle?id=' . $contrato_id);
            } else {
                $this->setFlash('error', 'Error al crear el contrato en base de datos.');
                $this->redirect('/contratos/formalizar?reserva_id=' . $reserva_id);
            }
        } else {
            $this->setFlash('error', 'Error al subir el archivo al servidor.');
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
            'titulo' => 'Detalle del Contrato'
        ]);
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

        if (!$contrato_id) {
            $this->redirect('/contratos');
        }

        $contrato = $this->contratoModel->obtenerDetalle($contrato_id, $usuario_id);
        if (!$contrato || $contrato['estado_codigo'] !== 'ESCO001') {
            $this->setFlash('error', 'El contrato no se puede finalizar.');
            $this->redirect('/contratos/detalle?id=' . $contrato_id);
        }

        // Marcar como finalizado
        if ($this->contratoModel->actualizarEstado($contrato_id, 'ESCO002')) {
            // Liberar alojamiento (cambiar a EPA001 - ACTIVO)
            $this->alojamientoModel->cambiarEstado($contrato['alojamiento_id'], 'EPA001');

            $this->setFlash('success', 'Contrato finalizado. El alojamiento vuelve a estar Activo. Por favor, califique al inquilino.');
        } else {
            $this->setFlash('error', 'Error al finalizar el contrato.');
        }

        $this->redirect('/contratos/detalle?id=' . $contrato_id);
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

        if ($puntuacion < 1 || $puntuacion > 5) {
            $this->setFlash('error', 'Puntuación inválida.');
            $this->redirect('/contratos/detalle?id=' . $contrato_id);
        }

        $contrato = $this->contratoModel->obtenerDetalle($contrato_id, $usuario_id);
        if (!$contrato || $contrato['estado_codigo'] !== 'ESCO002') {
            $this->setFlash('error', 'El contrato debe estar finalizado para calificar.');
            $this->redirect('/contratos/detalle?id=' . $contrato_id);
        }

        if (!empty($contrato['propietario_califico'])) {
            $this->setFlash('error', 'Ya has calificado a este inquilino por este contrato.');
            $this->redirect('/contratos/detalle?id=' . $contrato_id);
        }

        // Actualizar promedio en usuario (inquilino)
        if ($this->usuarioModel->agregarCalificacion($contrato['inquilino_id'], $puntuacion)) {
            $this->contratoModel->marcarComoCalificado($contrato_id);
            $this->setFlash('success', 'Calificación registrada exitosamente.');
        } else {
            $this->setFlash('error', 'No se pudo registrar la calificación.');
        }

        $this->redirect('/contratos/detalle?id=' . $contrato_id);
    }
}
