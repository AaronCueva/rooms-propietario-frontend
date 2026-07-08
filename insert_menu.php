<?php
try {
    $conn = new PDO(
        "pgsql:host=aws-1-us-east-2.pooler.supabase.com;port=6543;dbname=postgres",
        "postgres.lokjiueialuwrulybgut",
        "g0UNVXoLuA8uaPtH"
    );

    $finanzas_id = '2ac83e63-0036-4587-9f69-9fa9ca0fb719';
    $propietario_rol = 'cd128d2e-cf53-4e95-87be-e0fbf096443a';

    $ingresos_id = 'c1234567-c87b-4494-a22b-0d319e6b35ef';
    $cuenta_id = 'd1234567-c87b-4494-a22b-0d319e6b35ef';

    // Insertar menú maestro Ingresos
    $stmt = $conn->prepare("INSERT INTO menu_maestro (menu_maestro_id, nombre, descripcion, url, icono, orden, referencia_id, habilitado, creado) VALUES (?, ?, ?, ?, ?, ?, ?, true, NOW()) ON CONFLICT (menu_maestro_id) DO NOTHING");
    $stmt->execute([$ingresos_id, 'Ingresos', 'Ingresos Propietario', '/ingresos', 'fas fa-fw fa-chart-line', 1, $finanzas_id]);

    // Insertar menú maestro Cuenta Bancaria
    $stmt->execute([$cuenta_id, 'Cuenta Bancaria', 'Cuenta Bancaria', '/ingresos/cuenta', 'fas fa-fw fa-university', 2, $finanzas_id]);

    // Insertar menu_rol Ingresos
    $stmt2 = $conn->prepare("INSERT INTO menu_rol (menu_rol_id, menu_maestro_id, rol_id, habilitado, creado) VALUES (gen_random_uuid(), ?, ?, true, NOW()) ON CONFLICT DO NOTHING");
    $stmt2->execute([$ingresos_id, $propietario_rol]);

    // Insertar menu_rol Cuenta Bancaria
    $stmt2->execute([$cuenta_id, $propietario_rol]);

    // Tambien darle acceso al padre (FINANZAS) por si acaso no lo tiene
    $stmt2->execute([$finanzas_id, $propietario_rol]);

    echo "Menús insertados exitosamente";
} catch (Exception $e) {
    echo $e->getMessage();
}
