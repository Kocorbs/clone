<?php
// src/Models/IncidentHistory.php

namespace App\Models;

use App\Core\Database;
use PDO;

class IncidentHistory {
    public static function record(int $disasterRequestId, int $userId, string $prevStatus, string $newStatus, ?string $remarks = null): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO incident_status_history (
            disaster_request_id, changed_by_user_id, previous_status, new_status, remarks, created_at
        ) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");

        $stmt->execute([
            $disasterRequestId,
            $userId,
            $prevStatus,
            $newStatus,
            $remarks
        ]);

        return (int)$db->lastInsertId();
    }

    public static function getByDisasterRequestId(int $disasterRequestId): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT h.*, u.firstname, u.lastname, u.user_type 
            FROM incident_status_history h
            JOIN users u ON h.changed_by_user_id = u.user_id
            WHERE h.disaster_request_id = ?
            ORDER BY h.created_at ASC");
        $stmt->execute([$disasterRequestId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
