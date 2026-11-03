<?php
require_once __DIR__ . '/../src/Core/Database.php';
$db = App\Core\Database::getInstance();
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    $c = $db->query("SELECT count(*) FROM `{$t}`")->fetchColumn();
    echo str_pad($t, 35) . ": " . $c . " rows\n";
}

