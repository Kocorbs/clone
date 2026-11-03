<?php
// src/Models/DisasterRequest.php

namespace App\Models;

use App\Core\Database;

class DisasterRequest {
    public static function all(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "SELECT d.*, b.barangay_name, p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color,
                       u.firstname as requester_first, u.lastname as requester_last, u.user_type
                FROM disaster_request_form d
                JOIN barangay b ON d.barangay_id = b.barangay_id
                JOIN purok p ON d.purok_id = p.purok_id
                JOIN users u ON d.user_id = u.user_id
                WHERE d.deleted_at IS NULL";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND d.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['purok_id'])) {
            $sql .= " AND d.purok_id = ?";
            $params[] = $filters['purok_id'];
        }
        if (!empty($filters['disaster_type'])) {
            $sql .= " AND d.disaster_type = ?";
            $params[] = $filters['disaster_type'];
        }

        $sql .= " ORDER BY d.disaster_date DESC, d.disaster_request_id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT d.*, b.barangay_name, p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color,
                                     u.firstname as requester_first, u.lastname as requester_last, u.user_type, u.cellphone_number
                              FROM disaster_request_form d
                              JOIN barangay b ON d.barangay_id = b.barangay_id
                              JOIN purok p ON d.purok_id = p.purok_id
                              JOIN users u ON d.user_id = u.user_id
                              WHERE d.disaster_request_id = ? AND d.deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO disaster_request_form (
            user_id, barangay_id, purok_id, disaster_type, weather_name, description, 
            severity_level, is_urgent, affected_households, affected_population, 
            disaster_date, status, latitude, longitude
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?)");
        
        $stmt->execute([
            $data['user_id'],
            $data['barangay_id'],
            $data['purok_id'],
            $data['disaster_type'],
            $data['weather_name'],
            $data['description'],
            $data['severity_level'],
            !empty($data['is_urgent']) ? 1 : 0,
            (int)($data['affected_households'] ?? 1),
            (int)($data['affected_population'] ?? 1),
            $data['disaster_date'] ?? date('Y-m-d'),
            $data['latitude'] ?? null,
            $data['longitude'] ?? null
        ]);

        return (int)$db->lastInsertId();
    }

    public static function updateStatus(int $id, string $status): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE disaster_request_form SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE disaster_request_id = ?");
        return $stmt->execute([$status, $id]);
    }

    public static function getCounts(): array {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'Recommended' THEN 1 ELSE 0 END) as recommended,
            SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN severity_level IN ('Critical', 'High') OR is_urgent = 1 THEN 1 ELSE 0 END) as high_priority
            FROM disaster_request_form WHERE deleted_at IS NULL");
        return $stmt->fetch() ?: ['total' => 0, 'pending' => 0, 'recommended' => 0, 'approved' => 0, 'completed' => 0, 'high_priority' => 0];
    }
}
