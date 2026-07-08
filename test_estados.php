<?php
try {
    $conn = new PDO(
        "pgsql:host=aws-1-us-east-2.pooler.supabase.com;port=6543;dbname=postgres",
        "postgres.lokjiueialuwrulybgut",
        "g0UNVXoLuA8uaPtH"
    );
    echo "--- estado_codigo de alojamientos ---\n";
    $rows = $conn->query("SELECT alojamiento_id, titulo, estado_codigo FROM alojamiento WHERE habilitado = true")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo $r['titulo'] . ' => ' . $r['estado_codigo'] . "\n";
    }

    echo "\n--- catalogo ESTADO_ALOJAMIENTO ---\n";
    $cats = $conn->query("SELECT codigo, nombre FROM catalogo WHERE referencia_codigo LIKE '%ALOJ%' OR referencia_codigo LIKE '%EPA%' OR codigo LIKE '%EPA%' OR codigo LIKE '%ESAL%'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cats as $c) {
        echo $c['codigo'] . ' => ' . $c['nombre'] . "\n";
    }

    echo "\n--- catalogo con EPA ---\n";
    $cats2 = $conn->query("SELECT codigo, nombre, referencia_codigo FROM catalogo WHERE codigo LIKE 'E%' AND (nombre LIKE '%ctiv%' OR nombre LIKE '%cupad%' OR nombre LIKE '%ispon%' OR nombre LIKE '%anten%')")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cats2 as $c) {
        echo $c['codigo'] . ' => ' . $c['nombre'] . ' (ref: ' . $c['referencia_codigo'] . ")\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
