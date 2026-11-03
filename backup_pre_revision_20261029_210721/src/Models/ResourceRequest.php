<?php
// src/Models/ResourceRequest.php

namespace App\Models;

use App\Core\Database;

class ResourceRequest {
    public static function all(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "SELECT r.*, b.barangay_name, p.purok_name, s.supply_name, s.supply_type, s.supply_remaining,
                       u.firstname as requester_first, u.lastname as requester_last
                FROM resource_request r
                JOIN barangay b ON r.barangay_id = b.barangay_id
                JOIN purok p ON r.purok_id = p.purok_id
                LEFT JOIN supply s ON r.supply_id = s.supply_id
                JOIN users u ON r.user_id = u.user_id
                WHERE r.deleted_at IS NULL";
        $params = [];

        if (!empty($filters['request_status'])) {
            $sql .= " AND r.request_status = ?";
            $params[] = $filters['request_status'];
        }

        $sql .= " ORDER BY r.request_date DESC, r.request_id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT r.*, b.barangay_name, p.purok_name, p.risk_classification as purok_risk,
                                     s.supply_name, s.supply_type, s.supply_remaining, s.supply_quantity,
                                     u.firstname as requester_first, u.lastname as requester_last
                              FROM resource_request r
                              JOIN barangay b ON r.barangay_id = b.barangay_id
                              JOIN purok p ON r.purok_id = p.purok_id
                              LEFT JOIN supply s ON r.supply_id = s.supply_id
                              JOIN users u ON r.user_id = u.user_id
                              WHERE r.request_id = ? AND r.deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO resource_request (
            disaster_request_id, supply_id, barangay_id, purok_id, user_id, 
            requested_by, request_date, request_quantity, request_status, remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)");
        
        $stmt->execute([
            $data['disaster_request_id'] ?? null,
            $data['supply_id'] ?? null,
            $data['barangay_id'],
            $data['purok_id'],
            $data['user_id'],
            $data['requested_by'],
            $data['request_date'] ?? date('Y-m-d'),
            (int)$data['request_quantity'],
            $data['remarks'] ?? null
        ]);

        $requestId = (int)$db->lastInsertId();

        // Also record in requested_supplies if supply_id was provided
        if (!empty($data['supply_id'])) {
            $suppStmt = $db->prepare("INSERT INTO requested_supplies (request_id, supply_id, requested_supplies_quantity) VALUES (?, ?, ?)");
            $suppStmt->execute([$requestId, $data['supply_id'], (int)$data['request_quantity']]);
        }

        return $requestId;
    }

    public static function updateStatus(int $id, string $status): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE resource_request SET request_status = ?, updated_at = CURRENT_TIMESTAMP WHERE request_id = ?");
        return $stmt->execute([$status, $id]);
    }
}
