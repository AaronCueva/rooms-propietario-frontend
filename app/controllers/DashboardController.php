<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Alojamiento;
use App\Models\Reserva;
use App\Models\Pago;
use App\Models\Usuario;

class DashboardController extends Controller
{
    private $alojamientoModel;
    private $reservaModel;
    private $pagoModel;
    private $usuarioModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/login');
        }
        $this->alojamientoModel = new Alojamiento();
        $this->reservaModel = new Reserva();
        $this->pagoModel = new Pago();
        $this->usuarioModel = new Usuario();
    }

    public function index()
    {
        $usuario_id = $_SESSION['usuario_id'];
        
        // 1. Alojamientos Activos
        $alojamientos = $this->alojamientoModel->obtenerPorUsuarioId($usuario_id);
        $total_cuartos = count($alojamientos);
        $cuartos_ocupados = 0;
        $cuartos_disponibles = 0;
        foreach ($alojamientos as $alj) {
            if ($alj['estado_codigo'] === 'EPA004') { // Ocupado
                $cuartos_ocupados++;
            } else if ($alj['estado_codigo'] === 'EPA001' || $alj['estado_codigo'] === 'EPA003') { 
                $cuartos_disponibles++; // Disponible
            }
        }

        // 2. Ingresos del mes
        $mes_actual = (int)date('m');
        $anio_actual = (int)date('Y');
        $ingresos_mes = $this->pagoModel->obtenerIngresosPropietario($usuario_id, $mes_actual, $anio_actual);
        $total_proyectado_mes = 0;
        $total_recibido_mes = 0;
        foreach ($ingresos_mes as $ing) {
            $total_proyectado_mes += floatval($ing['monto']);
            if ($ing['estado_codigo'] === \App\Models\Pago::EST_COMPLETADO) { // Pagado
                $total_recibido_mes += floatval($ing['monto']);
            }
        }

        // 3. Solicitudes pendientes
        $pendientes_count = $this->reservaModel->contarPendientes($usuario_id);
        $nuevas_solicitudes = array_slice($this->reservaModel->obtenerPorPropietario($usuario_id, 'ESRE001'), 0, 5); // TOP 5

        // 4. Calificación
        $perfil = $this->usuarioModel->findById($usuario_id);
        $calificacion = $perfil['calificacion'] ? number_format($perfil['calificacion'], 1) : '5.0';

        // 5. Datos para gráficos (Histórico 6 meses)
        $labels_meses = [];
        $data_proyectado_hist = [];
        $data_recibido_hist = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $m = (int)date('m', strtotime("-$i months"));
            $y = (int)date('Y', strtotime("-$i months"));
            $nombre_mes = substr(['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'][$m-1], 0, 3);
            
            $labels_meses[] = "$nombre_mes $y";
            
            $ing_hist = $this->pagoModel->obtenerIngresosPropietario($usuario_id, $m, $y);
            $p = 0;
            $r = 0;
            foreach ($ing_hist as $ih) {
                $p += floatval($ih['monto']);
                if ($ih['estado_codigo'] === \App\Models\Pago::EST_COMPLETADO) {
                    $r += floatval($ih['monto']);
                }
            }
            $data_proyectado_hist[] = $p;
            $data_recibido_hist[] = $r;
        }

        $this->render('propietario/dashboard', [
            'nombre_usuario' => $_SESSION['nombres'] . ' ' . $_SESSION['apellidos'],
            'titulo' => 'Centro de operaciones',
            'total_cuartos' => $total_cuartos,
            'cuartos_ocupados' => $cuartos_ocupados,
            'cuartos_disponibles' => $cuartos_disponibles,
            'total_proyectado_mes' => $total_proyectado_mes,
            'total_recibido_mes' => $total_recibido_mes,
            'pendientes_count' => $pendientes_count,
            'calificacion' => $calificacion,
            'nuevas_solicitudes' => $nuevas_solicitudes,
            'alojamientos' => array_slice($alojamientos, 0, 5), // TOP 5 cuartos
            'chart_meses' => $labels_meses,
            'chart_proyectado' => $data_proyectado_hist,
            'chart_recibido' => $data_recibido_hist
        ]);
    }
}
