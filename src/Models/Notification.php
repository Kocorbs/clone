<?php
// src/Models/Notification.php

namespace App\Models;

use App\Core\Database;
use PDO;

class Notification {
    public static function create(array $data): int {
        $db = Database::getInstance();

        // Prevent exact duplicate unread notification for the same record within the last 5 minutes
        $recId = !empty($data['related_record_id']) ? (int)$data['related_record_id'] : null;
        $check = $db->prepare("SELECT notification_id FROM notifications 
            WHERE (user_id = ? OR (user_id IS NULL AND target_role = ?))
              AND notification_type = ? AND title = ? 
              AND (related_record_id = ? OR (related_record_id IS NULL AND ? IS NULL))
              AND is_read = 0 
              AND datetime(created_at, '+5 minutes') >= datetime('now') LIMIT 1");
        $check->execute([
            $data['user_id'] ?? null,
            $data['target_role'] ?? null,
            $data['notification_type'],
            $data['title'],
            $recId,
            $recId
        ]);
        if ($existing = $check->fetchColumn()) {
            return (int)$existing;
        }

        $stmt = $db->prepare("INSERT INTO notifications (
            user_id, target_role, notification_type, title, message, 
            related_record_type, related_record_id, priority, is_read, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP)");

        $stmt->execute([
            !empty($data['user_id']) ? (int)$data['user_id'] : null,
            !empty($data['target_role']) ? $data['target_role'] : null,
            $data['notification_type'],
            $data['title'],
            $data['message'],
            $data['related_record_type'] ?? null,
            !empty($data['related_record_id']) ? (int)$data['related_record_id'] : null,
            $data['priority'] ?? 'Normal'
        ]);

        return (int)$db->lastInsertId();
    }

    public static function getForUser(int $userId, string $role, int $limit = 50, ?string $category = null): array {
        $db = Database::getInstance();
        $sql = "SELECT * FROM notifications 
                WHERE (user_id = ? OR (user_id IS NULL AND target_role = ?) OR (user_id IS NULL AND target_role = 'All'))";
        $params = [$userId, $role];

        if (!empty($category)) {
            $sql .= " AND notification_type = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUnreadCount(int $userId, string $role): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications 
            WHERE (user_id = ? OR (user_id IS NULL AND target_role = ?) OR (user_id IS NULL AND target_role = 'All')) 
              AND is_read = 0");
        $stmt->execute([$userId, $role]);
        return (int)$stmt->fetchColumn();
    }

    public static function markAsRead(int $notificationId, int $userId): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE notification_id = ?");
        return $stmt->execute([$notificationId]);
    }

    public static function markAllAsRead(int $userId, string $role): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1, read_at = CURRENT_TIMESTAMP 
            WHERE (user_id = ? OR (user_id IS NULL AND target_role = ?) OR (user_id IS NULL AND target_role = 'All')) 
              AND is_read = 0");
        return $stmt->execute([$userId, $role]);
    }

    public static function notifyRole(string $role, string $type, string $title, string $message, string $priority = 'Normal', ?string $recType = null, ?int $recId = null): int {
        return self::create([
            'user_id' => null,
            'target_role' => $role,
            'notification_type' => $type,
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'related_record_type' => $recType,
            'related_record_id' => $recId
        ]);
    }

    public static function notifyUser(int $userId, string $type, string $title, string $message, string $priority = 'Normal', ?string $recType = null, ?int $recId = null): int {
        return self::create([
            'user_id' => $userId,
            'target_role' => null,
            'notification_type' => $type,
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'related_record_type' => $recType,
            'related_record_id' => $recId
        ]);
    }
}
