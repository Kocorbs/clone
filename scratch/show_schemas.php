<?php
require_once __DIR__ . '/../src/Core/Database.php';
$db = App\Core\Database::getInstance();

$tables = ['purok', 'historical_disaster_records', 'recommendation', 'relief_beneficiaries', 'supply', 'field_reports'];
foreach ($tables as $t) {
    echo "================ Table: {$t} ================\n";
    $schema = $db->query("SELECT sql FROM sqlite_master WHERE name='{$t}'")->fetchColumn();
    echo $schema . "\n\n";
}

