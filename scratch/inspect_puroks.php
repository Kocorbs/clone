<?php
require_once __DIR__ . '/../src/Core/Database.php';
$db = App\Core\Database::getInstance();
$cols = $db->query("PRAGMA table_info(purok)")->fetchAll();
echo "Columns in purok table:\n";
foreach ($cols as $c) {
    echo " - {$c['name']} ({$c['type']})\n";
}
echo "\nRows in purok table:\n";
$rows = $db->query("SELECT * FROM purok")->fetchAll();
foreach ($rows as $r) {
    echo "ID: {$r['purok_id']} | Name: {$r['purok_name']} | Risk: {$r['hazard_risk_level']} | Pop: {$r['population']} | HH: {$r['households']}\n";
}

