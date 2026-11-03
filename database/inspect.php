<?php
// database/inspect.php
require_once __DIR__ . '/../src/Core/Database.php';

$db = App\Core\Database::getInstance();
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

echo "Tables in database:\n";
foreach ($tables as $table) {
    if ($table === 'sqlite_sequence') continue;
    echo "\n=== Table: {$table} ===\n";
    $cols = $db->query("PRAGMA table_info({$table})")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo "  - {$col['name']} ({$col['type']})" . ($col['pk'] ? ' [PK]' : '') . ($col['notnull'] ? ' NOT NULL' : '') . "\n";
    }
}
