<?php
// src/Models/AuditLog.php

namespace App\Models;

use App\Core\Database;
use App\Core\Auth;
use PDO;

class AuditLog {
    public static function log(string $action, string $entityType, ?int $entityId = null, ?string $details = null, ?int $userId = null, ?string $userName = null, ?string $role = null): int {
        $db = Database::getInstance();

        if ($userId === null && Auth::check()) {
            $user = Auth::user();
            $userId = (int)$user['user_id'];
            $userName = $user['firstname'] . ' ' . $user['lastname'];
            $role = $user['user_type'];
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $stmt = $db->prepare("INSERT INTO audit_logs (
            user_id, user_name, user_role, action, entity_type, entity_id, details, ip_address, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");

        $stmt->execute([
            $userId,
            $userName ?? 'System / Anonymous',
            $role ?? 'System',
            $action,
            $entityType,
            $entityId,
            $details,
            $ip
        ]);

        return (int)$db->lastInsertId();
    }

    public static function all(int $limit = 100, array $filters = []): array {
        $db = Database::getInstance();
        $sql = "SELECT *, audit_id as log_id, action as action_name, entity_type as table_name, entity_id as record_id FROM audit_logs WHERE 1=1";
        $params = [];

        if (!empty($filters['action'])) {
            $sql .= " AND action = ?";
            $params[] = $filters['action'];
        }
        if (!empty($filters['entity_type'])) {
            $sql .= " AND entity_type = ?";
            $params[] = $filters['entity_type'];
        }

        $sql .= " ORDER BY audit_id DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
