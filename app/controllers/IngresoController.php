<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Pago;
use App\Models\CuentaBancaria;

class IngresoController extends Controller
{
    private $pagoModel;
    private $cuentaBancariaModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/login');
        }
        $this->pagoModel = new Pago();
        $this->cuentaBancariaModel = new CuentaBancaria();
    }

    public function index()
    {
        $usuario_id = $_SESSION['usuario_id'];
        
        $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
        $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
        
        $ingresos = $this->pagoModel->obtenerIngresosPropietario($usuario_id, $mes, $anio);
        $cuenta_principal = $this->cuentaBancariaModel->obtenerPrincipal($usuario_id);

        $total_proyectado = 0;
        $total_recibido = 0;
        $pendientes = 0;
        $retrasos = 0;

        foreach ($ingresos as &$ing) {
            $total_proyectado += floatval($ing['monto']);
            
            // Check if overdue
            $hoy = new \DateTime();
            $vencimiento = new \DateTime($ing['fecha_vencimiento']);
            
            if ($ing['estado_codigo'] === 'ESPA002') { // Pagado (Asumiendo ESPA002 es pagado)
                $total_recibido += floatval($ing['monto']);
            } else {
                if ($hoy > $vencimiento) {
                    $ing['estado_mostrar'] = 'Retrasado';
                    $retrasos++;
                } else {
                    $ing['estado_mostrar'] = 'Pendiente';
                    $pendientes++;
                }
            }
        }
        unset($ing);

        $this->render('propietario/ingresos/index', [
            'ingresos' => $ingresos,
            'mes' => $mes,
            'anio' => $anio,
            'total_proyectado' => $total_proyectado,
            'total_recibido' => $total_recibido,
            'pendientes' => $pendientes,
            'retrasos' => $retrasos,
            'cuenta_principal' => $cuenta_principal,
            'titulo' => 'Módulo de Ingresos'
        ]);
    }

    public function cuenta()
    {
        $usuario_id = $_SESSION['usuario_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'usuario_id' => $usuario_id,
                'cuenta_bancaria_id' => $_POST['cuenta_bancaria_id'] ?? null,
                'banco_codigo' => $_POST['banco_codigo'],
                'tipo_cuenta_codigo' => $_POST['tipo_cuenta_codigo'],
                'numero_cuenta' => $_POST['numero_cuenta'],
                'cci' => $_POST['cci'] ?? null,
                'titular' => $_POST['titular'],
                'principal' => true // Al guardar desde acá, lo hacemos principal
            ];

            if ($this->cuentaBancariaModel->guardar($data)) {
                $this->setFlash('success', 'Cuenta de cobro actualizada exitosamente.');
            } else {
                $this->setFlash('error', 'Ocurrió un error al guardar la cuenta de cobro.');
            }
            $this->redirect('/ingresos');
        }

        $cuenta = $this->cuentaBancariaModel->obtenerPrincipal($usuario_id);
        
        // Cargar catálogos
        $db = \App\Core\Database::getInstance()->getConnection();
        $bancos = $db->query("SELECT codigo, nombre FROM catalogo WHERE referencia_codigo = 'BANCO' AND habilitado = TRUE ORDER BY orden")->fetchAll(\PDO::FETCH_ASSOC);
        $tipos_cuenta = $db->query("SELECT codigo, nombre FROM catalogo WHERE referencia_codigo = 'TIPO_CUENTA_BANCARIA' AND habilitado = TRUE ORDER BY orden")->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('propietario/ingresos/cuenta', [
            'cuenta' => $cuenta,
            'bancos' => $bancos,
            'tipos_cuenta' => $tipos_cuenta,
            'titulo' => 'Cuenta de Cobro'
        ]);
    }

    public function exportar()
    {
        $usuario_id = $_SESSION['usuario_id'];
        $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
        $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
        $format = $_GET['format'] ?? 'csv';

        $ingresos = $this->pagoModel->obtenerIngresosPropietario($usuario_id, $mes, $anio);
        
        $meses = ['1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril', '5' => 'Mayo', '6' => 'Junio', 
                  '7' => 'Julio', '8' => 'Agosto', '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'];
        $nombre_mes = $meses[$mes];

        if ($format === 'excel') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=reporte_ingresos_' . $nombre_mes . '_' . $anio . '.csv');
            
            $output = fopen('php://output', 'w');
            
            // BOM for Excel UTF-8
            fputs($output, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            fputcsv($output, ['INQUILINO', 'ALOJAMIENTO', 'N° CUOTA', 'FECHA VENCIMIENTO', 'MONTO (S/)', 'ESTADO'], ';');
            
            $total_proyectado = 0;
            $total_recibido = 0;
            
            foreach ($ingresos as $ing) {
                $hoy = new \DateTime();
                $vencimiento = new \DateTime($ing['fecha_vencimiento']);
                
                $estado = 'Pagado';
                if ($ing['estado_codigo'] !== 'ESPA002') {
                    if ($hoy > $vencimiento) {
                        $estado = 'Retrasado';
                    } else {
                        $estado = 'Pendiente';
                    }
                }

                $total_proyectado += floatval($ing['monto']);
                if ($estado === 'Pagado') {
                    $total_recibido += floatval($ing['monto']);
                }

                fputcsv($output, [
                    $ing['inquilino_nombres'] . ' ' . $ing['inquilino_apellido'],
                    $ing['alojamiento_titulo'],
                    $ing['numero_cuota'],
                    date('d/m/Y', strtotime($ing['fecha_vencimiento'])),
                    number_format($ing['monto'], 2, '.', ''),
                    $estado
                ], ';');
            }
            
            fputcsv($output, ['', '', '', '', '', ''], ';');
            fputcsv($output, ['TOTAL PROYECTADO', '', '', '', number_format($total_proyectado, 2, '.', ''), ''], ';');
            fputcsv($output, ['TOTAL RECIBIDO', '', '', '', number_format($total_recibido, 2, '.', ''), ''], ';');
            
            fclose($output);
            exit;
        } else if ($format === 'pdf') {
            // Generar vista HTML para impresión (sin layout principal: el reporte
            // es un documento independiente, no debe incluir sidebar ni topbar al imprimir)
            $this->render('propietario/ingresos/print', [
                'ingresos' => $ingresos,
                'mes' => $mes,
                'anio' => $anio,
                'nombre_mes' => $nombre_mes,
                'titulo' => 'Reporte de Ingresos - ' . $nombre_mes . ' ' . $anio
            ], null);
        }
    }
}
