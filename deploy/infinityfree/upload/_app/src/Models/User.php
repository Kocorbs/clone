<?php
// src/Models/User.php

namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([trim($email)]);
        return $stmt->fetch() ?: null;
    }

    public static function all(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "SELECT * FROM users WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($filters['user_type'])) {
            $sql .= " AND user_type = ?";
            $params[] = $filters['user_type'];
        }
        if (isset($filters['is_approved'])) {
            $sql .= " AND is_approved = ?";
            $params[] = (int)$filters['is_approved'];
        }

        $sql .= " ORDER BY created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getPendingResidents(): array {
        return self::all(['user_type' => 'Resident', 'is_approved' => 0]);
    }

    public static function approveResident(int $userId, int $approvedBy): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET is_approved = 1, approved_by = ?, approved_at = CURRENT_TIMESTAMP, rejection_reason = NULL, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
        return $stmt->execute([$approvedBy, $userId]);
    }

    public static function rejectResident(int $userId, string $reason): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET is_approved = -1, rejection_reason = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
        return $stmt->execute([$reason, $userId]);
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO users (user_type, id_number, image, lastname, firstname, gender, cellphone_number, email, password_hash, is_approved)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_type'],
            $data['id_number'] ?? null,
            $data['image'] ?? 'assets/images/default_user.png',
            $data['lastname'],
            $data['firstname'],
            $data['gender'] ?? '',
            $data['cellphone_number'] ?? '',
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['is_approved'] ?? 1
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getInstance();
        $fields = [];
        $params = [];

        foreach (['lastname', 'firstname', 'gender', 'cellphone_number', 'id_number'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (!empty($data['password'])) {
            $fields[] = "password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $id;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function countByRole(): array {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT user_type, COUNT(*) as count FROM users WHERE deleted_at IS NULL GROUP BY user_type");
        $results = [];
        while ($row = $stmt->fetch()) {
            $results[$row['user_type']] = (int)$row['count'];
        }
        return $results;
    }
}
