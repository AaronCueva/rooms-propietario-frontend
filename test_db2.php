<?php
try {
    $conn = new PDO(
        "pgsql:host=aws-1-us-east-2.pooler.supabase.com;port=6543;dbname=postgres",
        "postgres.lokjiueialuwrulybgut",
        "g0UNVXoLuA8uaPtH"
    );
    $cols = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'contrato'")->fetchAll(PDO::FETCH_COLUMN);
    print_r($cols);
} catch (Exception $e) {
    echo $e->getMessage();
}
