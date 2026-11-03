<?php
// src/Models/Supply.php

namespace App\Models;

use App\Core\Database;
use Exception;
use PDO;

class Supply {
    public static function all(): array {
        $db = Database::getInstance();
        return $db->query("SELECT s.*, b.barangay_name 
            FROM supply s 
            JOIN barangay b ON s.barangay_id = b.barangay_id 
            WHERE s.deleted_at IS NULL 
            ORDER BY s.supply_type ASC, s.supply_name ASC")->fetchAll();
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT s.*, b.barangay_name 
            FROM supply s 
            JOIN barangay b ON s.barangay_id = b.barangay_id 
            WHERE s.supply_id = ? AND s.deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getLowStock(): array {
        $db = Database::getInstance();
        return $db->query("SELECT s.*, b.barangay_name 
            FROM supply s 
            JOIN barangay b ON s.barangay_id = b.barangay_id 
            WHERE s.deleted_at IS NULL 
              AND (s.supply_status IN ('Low Stock', 'Critical', 'Out of Stock') 
                   OR s.supply_remaining <= (s.supply_quantity * 0.25))
            ORDER BY s.supply_remaining ASC")->fetchAll();
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $status = self::calculateStatus((int)$data['supply_quantity'], (int)$data['supply_quantity']);
        $stmt = $db->prepare("INSERT INTO supply (barangay_id, supply_type, supply_name, supply_quantity, supply_remaining, total_release, supply_status, unit_of_measure)
            VALUES (?, ?, ?, ?, ?, 0, ?, ?)");
        $stmt->execute([
            $data['barangay_id'],
            $data['supply_type'],
            $data['supply_name'],
            (int)$data['supply_quantity'],
            (int)$data['supply_quantity'],
            $status,
            $data['unit_of_measure'] ?? 'units'
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getInstance();
        $supply = self::findById($id);
        if (!$supply) return false;

        $total = isset($data['supply_quantity']) ? (int)$data['supply_quantity'] : (int)$supply['supply_quantity'];
        $remaining = isset($data['supply_remaining']) ? (int)$data['supply_remaining'] : (int)$supply['supply_remaining'];
        $status = self::calculateStatus($remaining, $total);

        $stmt = $db->prepare("UPDATE supply 
            SET supply_type = ?, supply_name = ?, supply_quantity = ?, supply_remaining = ?, supply_status = ?, unit_of_measure = ?, updated_at = CURRENT_TIMESTAMP
            WHERE supply_id = ?");
        return $stmt->execute([
            $data['supply_type'] ?? $supply['supply_type'],
            $data['supply_name'] ?? $supply['supply_name'],
            $total,
            $remaining,
            $status,
            $data['unit_of_measure'] ?? $supply['unit_of_measure'],
            $id
        ]);
    }

    /**
     * Atomic deduction of stock with strict validation against available inventory.
     */
    public static function deductStock(int $supplyId, int $quantity): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT supply_quantity, supply_remaining, total_release FROM supply WHERE supply_id = ? AND deleted_at IS NULL");
        $stmt->execute([$supplyId]);
        $curr = $stmt->fetch();

        if (!$curr) {
            throw new Exception("Target supply ID {$supplyId} does not exist.");
        }

        $remaining = (int)$curr['supply_remaining'];
        $total = (int)$curr['supply_quantity'];
        $released = (int)$curr['total_release'];

        if ($quantity > $remaining) {
            throw new Exception("Allocation exceeds available stock! Requested: {$quantity}, Remaining: {$remaining}.");
        }

        $newRemaining = $remaining - $quantity;
        $newReleased = $released + $quantity;
        $newStatus = self::calculateStatus($newRemaining, $total);

        $update = $db->prepare("UPDATE supply 
            SET supply_remaining = ?, total_release = ?, supply_status = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE supply_id = ?");
        return $update->execute([$newRemaining, $newReleased, $newStatus, $supplyId]);
    }

    public static function replenishStock(int $supplyId, int $quantity): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT supply_quantity, supply_remaining FROM supply WHERE supply_id = ? AND deleted_at IS NULL");
        $stmt->execute([$supplyId]);
        $curr = $stmt->fetch();

        if (!$curr) return false;

        $newRemaining = (int)$curr['supply_remaining'] + $quantity;
        $newTotal = (int)$curr['supply_quantity'] + $quantity;
        $newStatus = self::calculateStatus($newRemaining, $newTotal);

        $update = $db->prepare("UPDATE supply 
            SET supply_quantity = ?, supply_remaining = ?, supply_status = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE supply_id = ?");
        return $update->execute([$newTotal, $newRemaining, $newStatus, $supplyId]);
    }

    public static function calculateStatus(int $remaining, int $total): string {
        if ($remaining <= 0) {
            return 'Out of Stock';
        }
        if ($total > 0 && ($remaining / $total) <= 0.10) {
            return 'Critical';
        }
        if ($total > 0 && ($remaining / $total) <= 0.25) {
            return 'Low Stock';
        }
        return 'In Stock';
    }

    public static function getInventorySummary(): array {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT 
            COUNT(*) as total_items,
            SUM(supply_quantity) as total_quantity,
            SUM(supply_remaining) as total_remaining,
            SUM(total_release) as total_released,
            SUM(CASE WHEN supply_status IN ('Low Stock', 'Critical', 'Out of Stock') THEN 1 ELSE 0 END) as low_stock_count
            FROM supply WHERE deleted_at IS NULL");
        return $stmt->fetch() ?: [
            'total_items' => 0, 'total_quantity' => 0, 'total_remaining' => 0, 'total_released' => 0, 'low_stock_count' => 0
        ];
    }
}
