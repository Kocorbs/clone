<?php
// src/Models/EventEvaluation.php

namespace App\Models;

use App\Core\Database;

class EventEvaluation {
    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO event_evaluations (
            preparedness_activities_id, user_id, actual_turnout, objectives_met_rating, 
            problems_encountered, supplies_used, recommendations_for_improvement
        ) VALUES (?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $data['preparedness_activities_id'],
            $data['user_id'],
            (int)$data['actual_turnout'],
            (int)$data['objectives_met_rating'],
            $data['problems_encountered'] ?? null,
            $data['supplies_used'] ?? null,
            $data['recommendations_for_improvement'] ?? null
        ]);

        return (int)$db->lastInsertId();
    }

    public static function findByActivityId(int $activityId): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM event_evaluations WHERE preparedness_activities_id = ? LIMIT 1");
        $stmt->execute([$activityId]);
        return $stmt->fetch() ?: null;
    }
}
