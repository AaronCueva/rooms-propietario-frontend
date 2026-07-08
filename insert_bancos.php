<?php
try {
    $conn = new PDO(
        "pgsql:host=aws-1-us-east-2.pooler.supabase.com;port=6543;dbname=postgres",
        "postgres.lokjiueialuwrulybgut",
        "g0UNVXoLuA8uaPtH"
    );

    $catalogos = [
        ['codigo' => 'BANCO', 'nombre' => 'BANCOS Y BILLETERAS', 'descripcion' => 'Bancos y billeteras digitales', 'prefijo' => 'BAN'],
        ['codigo' => 'TIPO_CUENTA_BANCARIA', 'nombre' => 'TIPO DE CUENTA BANCARIA', 'descripcion' => 'Tipos de cuenta', 'prefijo' => 'TCB']
    ];
    
    $stmt = $conn->prepare("INSERT INTO catalogo (codigo, nombre, descripcion, orden, prefijo, habilitado, creado) VALUES (?, ?, ?, 1, ?, true, NOW()) ON CONFLICT DO NOTHING");
    foreach ($catalogos as $cat) {
        $stmt->execute([$cat['codigo'], $cat['nombre'], $cat['descripcion'], $cat['prefijo']]);
    }

    $bancos = [
        ['codigo' => 'BAN001', 'nombre' => 'BCP', 'referencia_codigo' => 'BANCO'],
        ['codigo' => 'BAN002', 'nombre' => 'BBVA', 'referencia_codigo' => 'BANCO'],
        ['codigo' => 'BAN003', 'nombre' => 'Interbank', 'referencia_codigo' => 'BANCO'],
        ['codigo' => 'BAN004', 'nombre' => 'Scotiabank', 'referencia_codigo' => 'BANCO'],
        ['codigo' => 'BAN005', 'nombre' => 'Yape', 'referencia_codigo' => 'BANCO'],
        ['codigo' => 'BAN006', 'nombre' => 'Plin', 'referencia_codigo' => 'BANCO'],
        ['codigo' => 'BAN007', 'nombre' => 'BanBif', 'referencia_codigo' => 'BANCO'],
        ['codigo' => 'BAN008', 'nombre' => 'Banco de la Nación', 'referencia_codigo' => 'BANCO']
    ];

    $tipos_cuenta = [
        ['codigo' => 'TCB001', 'nombre' => 'Ahorros', 'referencia_codigo' => 'TIPO_CUENTA_BANCARIA'],
        ['codigo' => 'TCB002', 'nombre' => 'Corriente', 'referencia_codigo' => 'TIPO_CUENTA_BANCARIA'],
        ['codigo' => 'TCB003', 'nombre' => 'Billetera Digital', 'referencia_codigo' => 'TIPO_CUENTA_BANCARIA']
    ];

    $items = array_merge($bancos, $tipos_cuenta);
    $stmt = $conn->prepare("INSERT INTO catalogo (codigo, nombre, descripcion, orden, referencia_codigo, habilitado, creado) VALUES (?, ?, ?, ?, ?, true, NOW()) ON CONFLICT DO NOTHING");
    
    $orden = 1;
    foreach ($items as $item) {
        $stmt->execute([$item['codigo'], $item['nombre'], $item['nombre'], $orden++, $item['referencia_codigo']]);
    }
    
    echo "Catálogos de bancos creados.";
} catch (Exception $e) {
    echo $e->getMessage();
}
