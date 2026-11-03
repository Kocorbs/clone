<?php
// src/Models/PurokAssessment.php

namespace App\Models;

use App\Core\Database;
use PDO;

class PurokAssessment {
    public static function all(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "
            SELECT pa.*, p.purok_name, p.risk_classification, p.risk_zone_color, 
                   p.latitude, p.longitude, p.evacuation_center, p.flood_hazard_exposure,
                   p.population, p.households, p.senior_citizens, p.pwds, p.four_ps_members,
                   u.firstname as reviewer_first, u.lastname as reviewer_last
            FROM purok_assessments pa
            JOIN purok p ON pa.purok_id = p.purok_id
            LEFT JOIN users u ON pa.reviewed_by = u.user_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['priority'])) {
            $sql .= " AND pa.priority_level = :priority";
            $params[':priority'] = $filters['priority'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND pa.allocation_status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['purok_id'])) {
            $sql .= " AND pa.purok_id = :purok_id";
            $params[':purok_id'] = (int)$filters['purok_id'];
        }

        $sql .= " ORDER BY pa.priority_rank ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $assessmentId): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT pa.*, p.purok_name, p.risk_classification, p.risk_zone_color, 
                   p.latitude, p.longitude, p.evacuation_center, p.flood_hazard_exposure,
                   p.population, p.households, p.senior_citizens, p.pwds, p.four_ps_members,
                   p.historical_totally_damaged, p.historical_partially_damaged, p.historical_beneficiaries,
                   u.firstname as reviewer_first, u.lastname as reviewer_last
            FROM purok_assessments pa
            JOIN purok p ON pa.purok_id = p.purok_id
            LEFT JOIN users u ON pa.reviewed_by = u.user_id
            WHERE pa.assessment_id = ?
            LIMIT 1
        ");
        $stmt->execute([$assessmentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findByPurokId(int $purokId): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT pa.*, p.purok_name, p.risk_classification, p.risk_zone_color, 
                   p.latitude, p.longitude, p.evacuation_center, p.flood_hazard_exposure,
                   p.population, p.households, p.senior_citizens, p.pwds, p.four_ps_members,
                   u.firstname as reviewer_first, u.lastname as reviewer_last
            FROM purok_assessments pa
            JOIN purok p ON pa.purok_id = p.purok_id
            LEFT JOIN users u ON pa.reviewed_by = u.user_id
            WHERE pa.purok_id = ?
            LIMIT 1
        ");
        $stmt->execute([$purokId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function review(
        int $assessmentId,
        string $action, // 'approve', 'modify', 'reject'
        string $justification,
        int $reviewedByUserId,
        ?int $modRiceKg = null,
        ?int $modCannedTins = null,
        ?int $modWaterBottles = null,
        ?int $modHygieneKits = null,
        ?int $modBlankets = null
    ): array {
        $db = Database::getInstance();
        $assessment = self::findById($assessmentId);
        if (!$assessment) {
            return ['success' => false, 'message' => "Purok Assessment #{$assessmentId} not found."];
        }

        $newStatus = match ($action) {
            'approve' => 'Approved',
            'modify'  => 'Modified',
            'reject'  => 'Rejected',
            default   => 'Recommended'
        };

        $updateSql = "
            UPDATE purok_assessments SET
                allocation_status = :status,
                admin_justification = :justification,
                reviewed_by = :reviewed_by,
                reviewed_at = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP
        ";
        $params = [
            ':status' => $newStatus,
            ':justification' => $justification,
            ':reviewed_by' => $reviewedByUserId,
            ':id' => $assessmentId
        ];

        if ($action === 'modify') {
            if ($modRiceKg !== null) {
                $updateSql .= ", recommended_rice_kg = :mod_rice";
                $params[':mod_rice'] = max(0, $modRiceKg);
            }
            if ($modCannedTins !== null) {
                $updateSql .= ", recommended_canned_tins = :mod_canned";
                $params[':mod_canned'] = max(0, $modCannedTins);
            }
            if ($modWaterBottles !== null) {
                $updateSql .= ", recommended_water_bottles = :mod_water";
                $params[':mod_water'] = max(0, $modWaterBottles);
            }
            if ($modHygieneKits !== null) {
                $updateSql .= ", recommended_hygiene_kits = :mod_hygiene";
                $params[':mod_hygiene'] = max(0, $modHygieneKits);
            }
            if ($modBlankets !== null) {
                $updateSql .= ", recommended_blankets = :mod_blankets";
                $params[':mod_blankets'] = max(0, $modBlankets);
            }
        }

        $updateSql .= " WHERE assessment_id = :id";
        $stmt = $db->prepare($updateSql);
        $stmt->execute($params);

        // Audit Trail Record
        AuditLog::log(
            "Purok Relief Allocation {$newStatus}",
            'purok_assessments',
            $assessmentId,
            "Purok '{$assessment['purok_name']}' allocation {$newStatus}. Justification: {$justification}"
        );

        return [
            'success' => true,
            'status' => $newStatus,
            'message' => "Purok '{$assessment['purok_name']}' allocation status updated to {$newStatus}."
        ];
    }

    public static function getKpiSummary(): array {
        $db = Database::getInstance();
        $row = $db->query("
            SELECT 
                COUNT(*) as total_assessed,
                SUM(CASE WHEN priority_level = 'Critical' THEN 1 ELSE 0 END) as critical_count,
                SUM(CASE WHEN priority_level = 'High' THEN 1 ELSE 0 END) as high_count,
                SUM(CASE WHEN priority_level = 'Moderate' THEN 1 ELSE 0 END) as moderate_count,
                SUM(CASE WHEN priority_level = 'Low' THEN 1 ELSE 0 END) as low_count,
                SUM(affected_households) as total_households,
                SUM(affected_population) as total_population,
                SUM(recommended_rice_kg) as total_rice_kg,
                SUM(recommended_canned_tins) as total_canned_tins,
                SUM(recommended_water_bottles) as total_water_bottles,
                SUM(recommended_hygiene_kits) as total_hygiene_kits,
                SUM(recommended_blankets) as total_blankets,
                SUM(CASE WHEN allocation_status = 'Approved' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN stock_capped = 1 THEN 1 ELSE 0 END) as capped_count
            FROM purok_assessments
        ")->fetch(PDO::FETCH_ASSOC);

        return $row ?: [];
    }
}

