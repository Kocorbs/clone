<?php
require_once __DIR__ . '/../src/Core/Database.php';

$db = App\Core\Database::getInstance();
$u = $db->query("SELECT * FROM users WHERE user_id = 2")->fetch(PDO::FETCH_ASSOC);
echo "User 2: {$u['email']}\n";
$list = ['brgyhead123', 'admin123', 'password123', 'hinaplanon123', '12345678', 'password', 'brgy123'];
foreach ($list as $p) {
    if (password_verify($p, $u['password_hash'])) {
        echo "Match: $p\n";
    }
}
