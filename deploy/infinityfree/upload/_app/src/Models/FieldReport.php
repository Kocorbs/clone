<?php
// src/Models/FieldReport.php

namespace App\Models;

use App\Core\Database;

class FieldReport {
    public static function all(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "SELECT f.*, p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color,
                       d.disaster_type, d.severity_level, d.status as disaster_status,
                       u.firstname as resp_first, u.lastname as resp_last, u.id_number as responder_badge
                FROM field_reports f
                JOIN purok p ON f.purok_id = p.purok_id
                LEFT JOIN disaster_request_form d ON f.disaster_request_id = d.disaster_request_id
                JOIN users u ON f.user_id = u.user_id
                WHERE f.deleted_at IS NULL";
        $params = [];

        if (!empty($filters['purok_id'])) {
            $sql .= " AND f.purok_id = ?";
            $params[] = $filters['purok_id'];
        }

        $sql .= " ORDER BY f.report_time DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT f.*, p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color,
                                     d.disaster_type, d.weather_name, d.severity_level, d.status as disaster_status,
                                     u.firstname as resp_first, u.lastname as resp_last, u.cellphone_number, u.id_number as responder_badge
                              FROM field_reports f
                              JOIN purok p ON f.purok_id = p.purok_id
                              LEFT JOIN disaster_request_form d ON f.disaster_request_id = d.disaster_request_id
                              JOIN users u ON f.user_id = u.user_id
                              WHERE f.report_id = ? AND f.deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO field_reports (
            user_id, disaster_request_id, purok_id, responder_name, 
            status_update, road_accessibility, casualties_count, damaged_houses, 
            urgent_needs, latitude, longitude
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $data['user_id'],
            !empty($data['disaster_request_id']) ? $data['disaster_request_id'] : null,
            $data['purok_id'],
            $data['responder_name'],
            $data['status_update'],
            $data['road_accessibility'],
            (int)($data['casualties_count'] ?? 0),
            (int)($data['damaged_houses'] ?? 0),
            $data['urgent_needs'] ?? null,
            $data['latitude'] ?? null,
            $data['longitude'] ?? null
        ]);

        return (int)$db->lastInsertId();
    }
}
