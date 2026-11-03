<?php
// src/Models/Barangay.php

namespace App\Models;

use App\Core\Database;

class Barangay {
    public static function all(): array {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM barangay WHERE deleted_at IS NULL ORDER BY barangay_name ASC")->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM barangay WHERE barangay_id = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
