<?php
require_once __DIR__ . '/../src/Core/Database.php';
$db = App\Core\Database::getInstance();
$supplies = $db->query('SELECT * FROM supply')->fetchAll(PDO::FETCH_ASSOC);
foreach ($supplies as $s) {
    echo "ID: {$s['supply_id']} | {$s['supply_name']} | Type: {$s['supply_type']} | Remaining: {$s['supply_remaining']}/{$s['supply_quantity']} {$s['unit_of_measure']} | Status: {$s['supply_status']}\n";
}

