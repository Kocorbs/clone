<?php
// src/Models/PreparednessActivity.php

namespace App\Models;

use App\Core\Database;

class PreparednessActivity {
    public static function all(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "SELECT a.*, b.barangay_name, p.purok_name, u.firstname as organizer_first, u.lastname as organizer_last,
                       e.evaluation_id, e.objectives_met_rating, e.actual_turnout,
                       e.problems_encountered, e.supplies_used, e.recommendations_for_improvement
                FROM preparedness_activities a
                JOIN barangay b ON a.barangay_id = b.barangay_id
                LEFT JOIN purok p ON a.target_purok_id = p.purok_id
                JOIN users u ON a.user_id = u.user_id
                LEFT JOIN event_evaluations e ON a.preparedness_activities_id = e.preparedness_activities_id
                WHERE a.deleted_at IS NULL";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY a.schedule_date DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getUpcoming(): array {
        $db = Database::getInstance();
        return $db->query("SELECT a.*, b.barangay_name, p.purok_name 
            FROM preparedness_activities a
            JOIN barangay b ON a.barangay_id = b.barangay_id
            LEFT JOIN purok p ON a.target_purok_id = p.purok_id
            WHERE a.deleted_at IS NULL AND a.status = 'Scheduled' AND a.schedule_date >= CURRENT_TIMESTAMP
            ORDER BY a.schedule_date ASC")->fetchAll();
    }

    public static function getPast(): array {
        $db = Database::getInstance();
        return $db->query("SELECT a.*, b.barangay_name, p.purok_name,
                                  e.evaluation_id, e.actual_turnout, e.objectives_met_rating, e.problems_encountered, e.supplies_used, e.recommendations_for_improvement, e.evaluated_at
            FROM preparedness_activities a
            JOIN barangay b ON a.barangay_id = b.barangay_id
            LEFT JOIN purok p ON a.target_purok_id = p.purok_id
            LEFT JOIN event_evaluations e ON a.preparedness_activities_id = e.preparedness_activities_id
            WHERE a.deleted_at IS NULL AND (a.status = 'Completed' OR a.schedule_date < CURRENT_TIMESTAMP)
            ORDER BY a.schedule_date DESC")->fetchAll();
    }

    public static function getSummary(): array {
        $db = Database::getInstance();
        $total = (int)$db->query("SELECT COUNT(*) FROM preparedness_activities WHERE deleted_at IS NULL")->fetchColumn();
        $upcoming = (int)$db->query("SELECT COUNT(*) FROM preparedness_activities WHERE deleted_at IS NULL AND status = 'Scheduled' AND schedule_date >= CURRENT_TIMESTAMP")->fetchColumn();
        $completed = (int)$db->query("SELECT COUNT(*) FROM preparedness_activities WHERE deleted_at IS NULL AND status = 'Completed'")->fetchColumn();
        $totalTurnout = (int)$db->query("SELECT COALESCE(SUM(actual_turnout), 0) FROM event_evaluations")->fetchColumn();

        return [
            'total' => $total,
            'upcoming' => $upcoming,
            'completed' => $completed,
            'total_turnout' => $totalTurnout
        ];
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT a.*, b.barangay_name, p.purok_name, u.firstname as organizer_first, u.lastname as organizer_last,
                                     e.evaluation_id, e.actual_turnout, e.objectives_met_rating, e.problems_encountered, e.supplies_used, e.recommendations_for_improvement, e.evaluated_at
                              FROM preparedness_activities a
                              JOIN barangay b ON a.barangay_id = b.barangay_id
                              LEFT JOIN purok p ON a.target_purok_id = p.purok_id
                              JOIN users u ON a.user_id = u.user_id
                              LEFT JOIN event_evaluations e ON a.preparedness_activities_id = e.preparedness_activities_id
                              WHERE a.preparedness_activities_id = ? AND a.deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO preparedness_activities (
            user_id, barangay_id, activity_type, title, description, 
            target_purok_id, schedule_date, location, assigned_personnel, volunteer_person, status, max_participants
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Scheduled', ?)");
        
        $stmt->execute([
            $data['user_id'],
            $data['barangay_id'],
            $data['activity_type'],
            $data['title'],
            $data['description'],
            !empty($data['target_purok_id']) ? $data['target_purok_id'] : null,
            $data['schedule_date'],
            $data['location'],
            !empty($data['assigned_personnel']) ? $data['assigned_personnel'] : null,
            !empty($data['volunteer_person']) ? $data['volunteer_person'] : null,
            (int)($data['max_participants'] ?? 100)
        ]);

        return (int)$db->lastInsertId();
    }

    public static function updateStatus(int $id, string $status): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE preparedness_activities SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE preparedness_activities_id = ?");
        return $stmt->execute([$status, $id]);
    }
}
