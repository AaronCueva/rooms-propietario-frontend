<?php
session_start();
$_SESSION['usuario_id'] = 'd4c4a5c6-d9b8-4c9b-b5d1-12c8a2b5b3c2'; // Dummy ID

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) { return; }
    $relative_class = substr($class, $len);
    $parts = explode('/', str_replace('\\', '/', $relative_class));
    $className = array_pop($parts);
    $dirPath = strtolower(implode('/', $parts));
    $file = $base_dir . ($dirPath ? $dirPath . '/' : '') . $className . '.php';
    if (file_exists($file)) { require $file; }
});

try {
    $m = new App\Models\CuentaBancaria();
    $data = [
        'usuario_id' => $_SESSION['usuario_id'],
        'cuenta_bancaria_id' => '',
        'banco_codigo' => 'BAN001',
        'tipo_cuenta_codigo' => 'TCB001',
        'numero_cuenta' => '123456',
        'cci' => '',
        'titular' => 'Test',
        'principal' => true
    ];
    $m->guardar($data);
    echo "OK!";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
} catch (Error $e) {
    echo "Error: " . $e->getMessage();
}
