<?php
try {
    $conn = new PDO(
        "pgsql:host=aws-1-us-east-2.pooler.supabase.com;port=6543;dbname=postgres",
        "postgres.lokjiueialuwrulybgut",
        "g0UNVXoLuA8uaPtH"
    );
    echo "--- usuario ---\n";
    print_r($conn->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'usuario'")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
