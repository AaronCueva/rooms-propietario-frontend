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

$router->dispatch();
