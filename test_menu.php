<?php
try {
    $conn = new PDO(
        "pgsql:host=aws-1-us-east-2.pooler.supabase.com;port=6543;dbname=postgres",
        "postgres.lokjiueialuwrulybgut",
        "g0UNVXoLuA8uaPtH"
    );
    $menus = $conn->query("SELECT * FROM menu_maestro ORDER BY orden")->fetchAll(PDO::FETCH_ASSOC);
    print_r($menus);
} catch (Exception $e) {
    echo $e->getMessage();
}
