<?php
// src/Models/Shelter.php

namespace App\Models;

use App\Core\Database;
use PDO;

class Shelter {
    public static function all(): array {
        $db = Database::getInstance();
        return $db->query("SELECT s.*, s.availability_status as shelter_status, s.location as location_address, p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color 
            FROM evacuation_shelters s
            JOIN purok p ON s.purok_id = p.purok_id
            ORDER BY s.shelter_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT s.*, s.availability_status as shelter_status, s.location as location_address, p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color 
            FROM evacuation_shelters s
            JOIN purok p ON s.purok_id = p.purok_id
            WHERE s.shelter_id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function updateOccupancy(int $shelterId, int $occupancy, ?string $status = null): bool {
        $db = Database::getInstance();
        $shelter = self::findById($shelterId);
        if (!$shelter) return false;

        $max = (int)$shelter['max_capacity'];
        $occupancy = max(0, $occupancy);

        if ($status === null) {
            if ($occupancy >= $max) {
                $status = 'Full';
            } elseif ($occupancy >= ($max * 0.85)) {
                $status = 'Near Full';
            } else {
                $status = 'Open';
            }
        }

        $stmt = $db->prepare("UPDATE evacuation_shelters 
            SET current_occupancy = ?, availability_status = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE shelter_id = ?");
        return $stmt->execute([$occupancy, $status, $shelterId]);
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO evacuation_shelters (
            shelter_name, purok_id, location, current_occupancy, max_capacity, 
            availability_status, contact_person, contact_number, latitude, longitude
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $data['shelter_name'],
            (int)$data['purok_id'],
            $data['location'],
            (int)($data['current_occupancy'] ?? 0),
            (int)($data['max_capacity'] ?? 100),
            $data['availability_status'] ?? 'Open',
            $data['contact_person'] ?? null,
            $data['contact_number'] ?? null,
            $data['latitude'] ?? null,
            $data['longitude'] ?? null
        ]);

        return (int)$db->lastInsertId();
    }

    public static function getSummary(): array {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT 
            COUNT(*) as total_shelters,
            SUM(max_capacity) as total_capacity,
            SUM(current_occupancy) as total_occupancy,
            SUM(CASE WHEN availability_status = 'Open' THEN 1 ELSE 0 END) as open_count,
            SUM(CASE WHEN availability_status = 'Full' THEN 1 ELSE 0 END) as full_count,
            SUM(CASE WHEN availability_status = 'Near Full' THEN 1 ELSE 0 END) as near_full_count
            FROM evacuation_shelters");
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalCap = (int)($res['total_capacity'] ?? 0);
        $totalOcc = (int)($res['total_occupancy'] ?? 0);

        return [
            'total_shelters' => (int)($res['total_shelters'] ?? 0),
            'total_capacity' => $totalCap,
            'total_occupancy' => $totalOcc,
            'available_slots' => max(0, $totalCap - $totalOcc),
            'open_count' => (int)($res['open_count'] ?? 0),
            'full_count' => (int)($res['full_count'] ?? 0),
            'near_full_count' => (int)($res['near_full_count'] ?? 0),
            'occupancy_rate' => $totalCap > 0 ? round(($totalOcc / $totalCap) * 100, 1) : 0
        ];
    }
}
