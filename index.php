<?php
session_start();

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $parts = explode('/', str_replace('\\', '/', $relative_class));
    $className = array_pop($parts);
    $dirPath = strtolower(implode('/', $parts));

    $file = $base_dir . ($dirPath ? $dirPath . '/' : '') . $className . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Router;

$routes = [
    // Auth & Dashboard
    'GET' => [
        '/' => 'DashboardController@index',
        '/login' => 'AuthController@showLogin',
        '/logout' => 'AuthController@logout',
        '/solicitudes' => 'SolicitudController@index',
        '/solicitudes/detalle' => 'SolicitudController@detalle',
        '/contratos' => 'ContratoController@index',
        '/contratos/detalle' => 'ContratoController@detalle',
        '/contratos/formalizar' => 'ContratoController@formalizar',
        '/alojamientos' => 'AlojamientoController@index',
        '/alojamientos/nuevo' => 'AlojamientoController@nuevo',
        '/alojamientos/editar' => 'AlojamientoController@editar',
        '/inquilinos' => 'InquilinoController@index',
        '/inquilinos/detalle' => 'InquilinoController@detalle',
        '/ingresos' => 'IngresoController@index',
        '/ingresos/cuenta' => 'IngresoController@cuenta',
        '/ingresos/exportar' => 'IngresoController@exportar',
    ],
    'POST' => [
        '/login' => 'AuthController@login',
        '/solicitudes/aprobar' => 'SolicitudController@aprobar',
        '/solicitudes/rechazar' => 'SolicitudController@rechazar',
        '/contratos/guardar' => 'ContratoController@guardar',
        '/contratos/finalizar' => 'ContratoController@finalizar',
        '/contratos/calificar' => 'ContratoController@calificar',
        '/alojamientos/guardar' => 'AlojamientoController@guardar',
        '/alojamientos/politica/guardar' => 'AlojamientoController@guardarPolitica',
        '/alojamientos/politica/eliminar' => 'AlojamientoController@eliminarPolitica',
        '/alojamientos/descuento/guardar' => 'AlojamientoController@guardarDescuento',
        '/alojamientos/descuento/eliminar' => 'AlojamientoController@eliminarDescuento',
        '/alojamientos/beneficio/guardar' => 'AlojamientoController@guardarBeneficio',
        '/alojamientos/beneficio/eliminar' => 'AlojamientoController@eliminarBeneficio',
        '/ingresos/cuenta' => 'IngresoController@cuenta',
    ]
];

$router = new Router();

$router->get('/', 'AuthController', 'showLogin');
$router->get('/login', 'AuthController', 'showLogin');
$router->post('/login', 'AuthController', 'login');
$router->get('/register', 'AuthController', 'showRegister');
$router->post('/register', 'AuthController', 'register');
$router->get('/logout', 'AuthController', 'logout');

$router->get('/dashboard', 'DashboardController', 'index');

// Rutas de Alojamientos
$router->get('/alojamientos', 'AlojamientoController', 'index');
$router->get('/alojamientos/nuevo', 'AlojamientoController', 'crear');
$router->post('/alojamientos/guardar', 'AlojamientoController', 'store');
$router->post('/alojamientos/eliminar', 'AlojamientoController', 'eliminar');
$router->get('/alojamientos/editar', 'AlojamientoController', 'edit');
$router->post('/alojamientos/actualizar', 'AlojamientoController', 'update');
$router->post('/alojamientos/servicios', 'AlojamientoController', 'gestionarServicios');
$router->post('/alojamientos/politicas', 'AlojamientoController', 'gestionarPoliticas');
$router->post('/alojamientos/fotos/agregar', 'AlojamientoController', 'agregarFotos');
$router->post('/alojamientos/fotos/eliminar', 'AlojamientoController', 'eliminarFoto');
$router->post('/alojamientos/descuentos/agregar', 'AlojamientoController', 'agregarDescuento');
$router->post('/alojamientos/descuentos/eliminar', 'AlojamientoController', 'eliminarDescuento');
$router->post('/alojamientos/beneficios/agregar', 'AlojamientoController', 'agregarBeneficio');
$router->post('/alojamientos/beneficios/eliminar', 'AlojamientoController', 'eliminarBeneficio');

// Rutas de Solicitudes
$router->get('/solicitudes', 'SolicitudController', 'index');
$router->get('/solicitudes/detalle', 'SolicitudController', 'detalle');
$router->post('/solicitudes/aprobar', 'SolicitudController', 'aprobar');
$router->post('/solicitudes/rechazar', 'SolicitudController', 'rechazar');

// Rutas de API
$router->get('/api/ubicaciones', 'UbicacionController', 'obtenerPorReferencia');

// Rutas de Contratos
$router->get('/contratos', 'ContratoController', 'index');
$router->get('/contratos/formalizar', 'ContratoController', 'formalizar');
$router->post('/contratos/guardar', 'ContratoController', 'guardar');
$router->get('/contratos/detalle', 'ContratoController', 'detalle');
$router->post('/contratos/finalizar', 'ContratoController', 'finalizar');
$router->post('/contratos/calificar', 'ContratoController', 'calificar');

// Inquilinos
$router->get('/inquilinos', 'InquilinoController', 'index');
$router->get('/inquilinos/detalle', 'InquilinoController', 'detalle');

// Ingresos
$router->get('/ingresos', 'IngresoController', 'index');
$router->get('/ingresos/cuenta', 'IngresoController', 'cuenta');
$router->post('/ingresos/cuenta', 'IngresoController', 'cuenta');
$router->get('/ingresos/exportar', 'IngresoController', 'exportar');

// Perfil
$router->get('/perfil', 'PerfilController', 'index');
$router->post('/perfil', 'PerfilController', 'index');
$router->get('/perfil/password', 'PerfilController', 'password');
$router->post('/perfil/password', 'PerfilController', 'password');

$router->dispatch();
