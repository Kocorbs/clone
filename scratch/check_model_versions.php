<?php
require_once __DIR__ . '/../src/Core/Database.php';
$cols = App\Core\Database::getInstance()->query('PRAGMA table_info(model_versions)')->fetchAll();
foreach ($cols as $c) {
    echo " - {$c['name']} ({$c['type']})\n";
}

