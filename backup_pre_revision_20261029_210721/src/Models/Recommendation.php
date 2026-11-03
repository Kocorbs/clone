<?php
// src/Models/Recommendation.php

namespace App\Models;

use App\Core\Database;
use Exception;
use PDO;

class Recommendation {
    public static function all(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "SELECT rec.*, p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color,
                       s.supply_name, s.supply_type, s.supply_remaining, s.unit_of_measure,
                       d.disaster_type, d.severity_level, d.is_urgent, d.affected_households, d.affected_population,
                       u.firstname as reviewer_first, u.lastname as reviewer_last
                FROM recommendation rec
                JOIN purok p ON rec.purok_id = p.purok_id
                LEFT JOIN supply s ON rec.recommended_supply_id = s.supply_id
                LEFT JOIN disaster_request_form d ON rec.disaster_request_id = d.disaster_request_id
                LEFT JOIN users u ON rec.reviewed_by = u.user_id
                WHERE rec.deleted_at IS NULL";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND rec.recommendation_status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $sql .= " AND rec.predicted_priority = ?";
            $params[] = $filters['priority'];
        }

        $sql .= " ORDER BY rec.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT rec.*, p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color,
                                     s.supply_name, s.supply_type, s.supply_remaining, s.supply_quantity, s.unit_of_measure,
                                     d.disaster_type, d.weather_name, d.severity_level, d.is_urgent, d.affected_households, d.affected_population, d.description as disaster_desc,
                                     u.firstname as reviewer_first, u.lastname as reviewer_last
                              FROM recommendation rec
                              JOIN purok p ON rec.purok_id = p.purok_id
                              LEFT JOIN supply s ON rec.recommended_supply_id = s.supply_id
                              LEFT JOIN disaster_request_form d ON rec.disaster_request_id = d.disaster_request_id
                              LEFT JOIN users u ON rec.reviewed_by = u.user_id
                              WHERE rec.recommendation_id = ? AND rec.deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByDisasterRequestId(int $disasterRequestId): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM recommendation WHERE disaster_request_id = ? AND deleted_at IS NULL ORDER BY recommendation_id DESC LIMIT 1");
        $stmt->execute([$disasterRequestId]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO recommendation (
            request_id, disaster_request_id, purok_id, predicted_priority, 
            recommended_supply_id, recommended_quantity, confidence_score, 
            recommendation_reason, explanation_details, model_version, recommendation_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Generated')");

        $stmt->execute([
            $data['request_id'] ?? null,
            $data['disaster_request_id'] ?? null,
            $data['purok_id'],
            $data['predicted_priority'],
            $data['recommended_supply_id'] ?? null,
            (int)$data['recommended_quantity'],
            (float)($data['confidence_score'] ?? 0.0),
            $data['recommendation_reason'],
            $data['explanation_details'] ?? null,
            $data['model_version'] ?? 'v1.0-decision-tree'
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Official Review: Approve, Modify, or Reject Recommendation with justification and inventory deduction.
     */
    public static function review(int $recommendationId, string $action, string $justification, int $reviewerUserId, ?int $modifiedQuantity = null): array {
        $db = Database::getInstance();
        $rec = self::findById($recommendationId);

        if (!$rec) {
            return ['success' => false, 'message' => 'Recommendation record not found.'];
        }

        if ($rec['recommendation_status'] === 'Approved') {
            return ['success' => false, 'message' => 'This recommendation has already been approved and inventory allocated.'];
        }

        $reviewer = User::findById($reviewerUserId);
        $reviewerName = $reviewer ? ($reviewer['firstname'] . ' ' . $reviewer['lastname']) : 'Authorized Official';

        try {
            $db->beginTransaction();

            $qtyToAllocate = (int)$rec['recommended_quantity'];
            $newStatus = 'Approved';

            if ($action === 'modify') {
                if ($modifiedQuantity === null || $modifiedQuantity <= 0) {
                    throw new Exception("Modified quantity must be greater than zero.");
                }
                $qtyToAllocate = $modifiedQuantity;
                $newStatus = 'Modified';
            } elseif ($action === 'reject') {
                $newStatus = 'Rejected';
                $qtyToAllocate = 0;
            }

            // If approving or modifying, deduct inventory with strict validation
            if (in_array($newStatus, ['Approved', 'Modified']) && !empty($rec['recommended_supply_id'])) {
                Supply::deductStock((int)$rec['recommended_supply_id'], $qtyToAllocate);
            }

            // Update Recommendation record
            $stmt = $db->prepare("UPDATE recommendation 
                SET recommendation_status = ?, recommended_quantity = ?, justification = ?, reviewed_by = ?, reviewed_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
                WHERE recommendation_id = ?");
            $stmt->execute([$newStatus, $qtyToAllocate, $justification, $reviewerUserId, $recommendationId]);

            // Update related Disaster Request Form status
            if (!empty($rec['disaster_request_id'])) {
                $targetStatus = ($newStatus === 'Rejected') ? 'Declined' : 'Approved';
                $db->prepare("UPDATE disaster_request_form SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE disaster_request_id = ?")
                   ->execute([$targetStatus, $rec['disaster_request_id']]);
            }

            // Update related Resource Request status
            if (!empty($rec['request_id'])) {
                $targetStatus = ($newStatus === 'Rejected') ? 'Rejected' : 'Approved';
                $db->prepare("UPDATE resource_request SET request_status = ?, updated_at = CURRENT_TIMESTAMP WHERE request_id = ?")
                   ->execute([$targetStatus, $rec['request_id']]);
            }

            // Write immutable Audit Log
            RequestLog::create([
                'request_id' => $rec['request_id'],
                'recommendation_id' => $recommendationId,
                'recommended_by' => $rec['model_version'],
                'action_taken' => "Recommendation {$newStatus}",
                'action_by' => $reviewerName,
                'remarks' => "Action: {$newStatus}. Allocated: {$qtyToAllocate} units. Justification: {$justification}"
            ]);

            $db->commit();
            return ['success' => true, 'status' => $newStatus, 'allocated_quantity' => $qtyToAllocate];
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
