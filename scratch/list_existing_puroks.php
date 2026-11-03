<?php
require_once __DIR__ . '/../src/Core/Database.php';
$db = App\Core\Database::getInstance();
$rows = $db->query("SELECT purok_id, purok_name, risk_classification, latitude, longitude FROM purok ORDER BY purok_id")->fetchAll();
echo "Total in DB: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "ID: {$r['purok_id']} | {$r['purok_name']} | Risk: {$r['risk_classification']} | Lat: {$r['latitude']}, Lng: {$r['longitude']}\n";
}

