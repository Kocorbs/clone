<?php
// src/Models/User.php

namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT u.*, p.purok_name, b.barangay_name 
            FROM users u
            LEFT JOIN purok p ON u.purok_id = p.purok_id
            LEFT JOIN barangay b ON p.barangay_id = b.barangay_id
            WHERE u.user_id = ? AND u.deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findByIdNumber(string $idNumber): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT u.*, p.purok_name 
            FROM users u
            LEFT JOIN purok p ON u.purok_id = p.purok_id
            WHERE u.id_number = ? AND u.deleted_at IS NULL LIMIT 1");
        $stmt->execute([trim($idNumber)]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findByEmail(string $email): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([trim($email)]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findByIdentifier(string $identifier): ?array {
        $clean = trim($identifier);
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT u.*, p.purok_name 
            FROM users u
            LEFT JOIN purok p ON u.purok_id = p.purok_id
            WHERE (u.email = ? OR u.id_number = ? OR u.username = ?) AND u.deleted_at IS NULL LIMIT 1");
        $stmt->execute([$clean, $clean, $clean]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function all(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "SELECT u.*, p.purok_name FROM users u 
                LEFT JOIN purok p ON u.purok_id = p.purok_id 
                WHERE u.deleted_at IS NULL";
        $params = [];

        if (!empty($filters['user_type'])) {
            $sql .= " AND u.user_type = ?";
            $params[] = $filters['user_type'];
        }
        if (isset($filters['is_approved'])) {
            $sql .= " AND u.is_approved = ?";
            $params[] = (int)$filters['is_approved'];
        }

        $sql .= " ORDER BY u.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getResponders(): array {
        return self::all(['user_type' => 'Responder', 'is_approved' => 1]);
    }

    public static function getPendingResidents(): array {
        return self::all(['user_type' => 'Resident', 'is_approved' => 0]);
    }

    public static function getApprovedResidents(): array {
        return self::all(['user_type' => 'Resident', 'is_approved' => 1]);
    }

    public static function getAllResidents(): array {
        return self::all(['user_type' => 'Resident']);
    }

    public static function approveResident(int $userId, int $approvedBy): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET is_approved = 1, approved_by = ?, approved_at = CURRENT_TIMESTAMP, rejection_reason = NULL, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
        $success = $stmt->execute([$approvedBy, $userId]);
        if ($success) {
            AuditLog::log('Resident Account Approved', 'users', $userId, 'Barangay Head verified resident identity and approved access.');
            Notification::notifyUser(
                $userId,
                'account_approved',
                'Barangay Registration Approved',
                'Your resident account has been officially verified and approved by Barangay Hinaplanon.',
                'Normal',
                'users',
                $userId
            );
        }
        return $success;
    }

    public static function rejectResident(int $userId, string $reason): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET is_approved = -1, rejection_reason = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
        $success = $stmt->execute([$reason, $userId]);
        if ($success) {
            AuditLog::log('Resident Account Declined', 'users', $userId, "Reason: {$reason}");
            Notification::notifyUser(
                $userId,
                'account_declined',
                'Barangay Registration Declined',
                "Your registration could not be verified: {$reason}",
                'High',
                'users',
                $userId
            );
        }
        return $success;
    }

    public static function create(array $data): int {
        $db = Database::getInstance();

        // Safe email fallback if resident registered without email
        $idNum = trim($data['id_number'] ?? '');
        $email = trim($data['email'] ?? '');
        if (empty($email)) {
            $sanitizedId = preg_replace('/[^A-Za-z0-9_-]/', '', $idNum) ?: ('res_' . bin2hex(random_bytes(4)));
            $email = strtolower($sanitizedId) . '@hinaplanon.resident.local';
        }

        $stmt = $db->prepare("INSERT INTO users (
            user_type, id_number, image, lastname, firstname, middle_name, 
            gender, age, birthdate, cellphone_number, email, username, purok_id, password_hash, plain_password, is_approved
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $data['user_type'],
            $idNum ?: null,
            $data['image'] ?? 'assets/images/default_user.png',
            $data['lastname'],
            $data['firstname'],
            $data['middle_name'] ?? null,
            $data['gender'] ?? '',
            !empty($data['age']) ? (int)$data['age'] : null,
            !empty($data['birthdate']) ? $data['birthdate'] : null,
            $data['cellphone_number'] ?? '',
            $email,
            !empty($data['username']) ? trim($data['username']) : null,
            !empty($data['purok_id']) ? (int)$data['purok_id'] : null,
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['password'],
            $data['is_approved'] ?? 1
        ]);

        $newId = (int)$db->lastInsertId();

        AuditLog::log('User Registered / Created', 'users', $newId, "Role: {$data['user_type']}, ID: {$idNum}");

        if ($data['user_type'] === 'Resident' && empty($data['is_approved'])) {
            // Alert Barangay Head about pending resident registration
            Notification::notifyRole(
                'Barangay Head',
                'registration_approval',
                'New Resident Registration Pending Approval',
                "Resident {$data['firstname']} {$data['lastname']} (Barangay ID: {$idNum}) registered and requires verification.",
                'Normal',
                'users',
                $newId
            );
        }

        return $newId;
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getInstance();
        $fields = [];
        $params = [];

        foreach (['lastname', 'firstname', 'middle_name', 'gender', 'age', 'birthdate', 'cellphone_number', 'id_number', 'purok_id', 'username'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (!empty($data['password'])) {
            $fields[] = "password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
            $fields[] = "plain_password = ?";
            $params[] = $data['password'];
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $id;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute($params);

        if ($res) {
            AuditLog::log('User Profile Updated', 'users', $id, 'User profile information updated.');
        }

        return $res;
    }

    public static function resetResidentPassword(int $userId, string $newPassword, int $resetByUserId): bool {
        $db = Database::getInstance();
        $targetUser = self::findById($userId);
        if (!$targetUser || $targetUser['user_type'] !== 'Resident') {
            return false;
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE users SET password_hash = ?, plain_password = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
        $res = $stmt->execute([$hash, $newPassword, $userId]);

        if ($res) {
            AuditLog::log(
                'Resident Password Reset',
                'users',
                $userId,
                "Password updated by Barangay Head / Official (ID: {$resetByUserId}) for {$targetUser['firstname']} {$targetUser['lastname']}"
            );

            Notification::notifyUser(
                $userId,
                'password_reset',
                'Password Reset by Barangay Head',
                'Your account password was updated by the Barangay Head. If you did not request this, please coordinate with the Barangay Hall.',
                'Normal',
                'users',
                $userId
            );
        }

        return $res;
    }

    public static function countByRole(): array {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT user_type, COUNT(*) as count FROM users WHERE deleted_at IS NULL GROUP BY user_type");
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['user_type']] = (int)$row['count'];
        }
        return $results;
    }
}
