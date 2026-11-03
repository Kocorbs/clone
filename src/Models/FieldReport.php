<?php
// src/Models/FieldReport.php

namespace App\Models;

use App\Core\Database;
use PDO;

class FieldReport {
    public static function all(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "SELECT f.*, p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color,
                       d.disaster_type, d.severity_level, d.status as disaster_status,
                       u.firstname as resp_first, u.lastname as resp_last, u.id_number as responder_badge, u.cellphone_number as resp_phone,
                       rev.firstname as reviewer_first, rev.lastname as reviewer_last, rev.user_type as reviewer_role
                FROM field_reports f
                JOIN purok p ON f.purok_id = p.purok_id
                LEFT JOIN disaster_request_form d ON f.disaster_request_id = d.disaster_request_id
                JOIN users u ON f.user_id = u.user_id
                LEFT JOIN users rev ON f.reviewed_by = rev.user_id
                WHERE f.deleted_at IS NULL";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND f.user_id = ?";
            $params[] = (int)$filters['user_id'];
        }

        if (!empty($filters['purok_id'])) {
            $sql .= " AND f.purok_id = ?";
            $params[] = (int)$filters['purok_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND f.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['road_accessibility'])) {
            $sql .= " AND f.road_accessibility = ?";
            $params[] = $filters['road_accessibility'];
        }

        $sql .= " ORDER BY f.report_time DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT f.*, p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color,
                                     d.disaster_type, d.weather_name, d.severity_level, d.status as disaster_status,
                                     u.firstname as resp_first, u.lastname as resp_last, u.cellphone_number as resp_phone, u.id_number as responder_badge,
                                     rev.firstname as reviewer_first, rev.lastname as reviewer_last, rev.user_type as reviewer_role
                              FROM field_reports f
                              JOIN purok p ON f.purok_id = p.purok_id
                              LEFT JOIN disaster_request_form d ON f.disaster_request_id = d.disaster_request_id
                              JOIN users u ON f.user_id = u.user_id
                              LEFT JOIN users rev ON f.reviewed_by = rev.user_id
                              WHERE f.report_id = ? AND f.deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO field_reports (
            user_id, disaster_request_id, purok_id, responder_name, 
            status_update, road_accessibility, casualties_count, damaged_houses, 
            urgent_needs, latitude, longitude, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $status = $data['status'] ?? 'Submitted';

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
            $data['longitude'] ?? null,
            $status
        ]);

        return (int)$db->lastInsertId();
    }

    public static function verify(int $reportId, int $reviewedBy, ?string $notes = null): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE field_reports 
            SET status = 'Verified', reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP, review_notes = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE report_id = ?");
        return $stmt->execute([$reviewedBy, $notes, $reportId]);
    }

    public static function reject(int $reportId, int $reviewedBy, string $reason): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE field_reports 
            SET status = 'Rejected', reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP, review_notes = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE report_id = ?");
        return $stmt->execute([$reviewedBy, $reason, $reportId]);
    }

    public static function getCounts(array $filters = []): array {
        $all = self::all($filters);
        $counts = [
            'total' => count($all),
            'submitted' => 0,
            'verified' => 0,
            'rejected' => 0,
            'blocked_roads' => 0
        ];

        foreach ($all as $r) {
            $st = $r['status'] ?? 'Submitted';
            if ($st === 'Verified') $counts['verified']++;
            elseif ($st === 'Rejected') $counts['rejected']++;
            else $counts['submitted']++;

            if (in_array($r['road_accessibility'], ['Not Passable / Blocked', 'Flooded'])) {
                $counts['blocked_roads']++;
            }
        }

        return $counts;
    }
}
