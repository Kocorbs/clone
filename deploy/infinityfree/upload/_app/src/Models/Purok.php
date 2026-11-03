<?php
// src/Models/Purok.php

namespace App\Models;

use App\Core\Database;

class Purok {
    public static function all(): array {
        $db = Database::getInstance();
        return $db->query("SELECT p.*, b.barangay_name 
            FROM purok p 
            JOIN barangay b ON p.barangay_id = b.barangay_id 
            WHERE p.deleted_at IS NULL 
            ORDER BY p.purok_name ASC")->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT p.*, b.barangay_name 
            FROM purok p 
            JOIN barangay b ON p.barangay_id = b.barangay_id 
            WHERE p.purok_id = ? AND p.deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function updateRisk(int $purokId, string $riskLevel, string $color, ?string $notes): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE purok 
            SET risk_classification = ?, risk_zone_color = ?, notes = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE purok_id = ?");
        return $stmt->execute([$riskLevel, $color, $notes, $purokId]);
    }

    public static function getRiskSummary(): array {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT risk_classification, COUNT(*) as count FROM purok WHERE deleted_at IS NULL GROUP BY risk_classification");
        $summary = ['High' => 0, 'Medium' => 0, 'Low' => 0, 'Pending Verification' => 0];
        while ($row = $stmt->fetch()) {
            $summary[$row['risk_classification']] = (int)$row['count'];
        }
        return $summary;
    }
}
