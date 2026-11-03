<?php
require_once __DIR__ . '/../src/Core/Database.php';
$db = App\Core\Database::getInstance();

$tablesWithPurok = [
    'users' => 'purok_id',
    'disaster_request_form' => 'purok_id',
    'field_reports' => 'purok_id',
    'recommendation' => 'purok_id',
    'relief_beneficiaries' => 'purok_id',
    'preparedness_activities' => 'target_purok_id'
];

foreach ($tablesWithPurok as $tbl => $col) {
    echo "--- Table {$tbl} ({$col}) ---\n";
    $res = $db->query("SELECT {$col}, count(*) as cnt FROM `{$tbl}` GROUP BY {$col}")->fetchAll();
    foreach ($res as $r) {
        echo "  Purok ID {$r[$col]}: {$r['cnt']} rows\n";
    }
}

