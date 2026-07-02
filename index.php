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

// Rutas de API
$router->get('/api/ubicaciones', 'UbicacionController', 'obtenerPorReferencia');

$router->dispatch();
