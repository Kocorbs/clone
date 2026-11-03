<?php
// src/Models/RequestLog.php

namespace App\Models;

use App\Core\Database;

class RequestLog {
    public static function all(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "SELECT l.*, r.requested_by, r.request_quantity, rec.predicted_priority, rec.recommended_quantity
                FROM resource_request_log l
                LEFT JOIN resource_request r ON l.request_id = r.request_id
                LEFT JOIN recommendation rec ON l.recommendation_id = rec.recommendation_id
                WHERE l.deleted_at IS NULL
                ORDER BY l.action_date DESC, l.log_id DESC";
        return $db->query($sql)->fetchAll();
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO resource_request_log (request_id, recommendation_id, recommended_by, action_taken, action_by, remarks)
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['request_id'] ?? null,
            $data['recommendation_id'] ?? null,
            $data['recommended_by'] ?? null,
            $data['action_taken'],
            $data['action_by'],
            $data['remarks'] ?? null
        ]);
        return (int)$db->lastInsertId();
    }
}
