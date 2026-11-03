<?php
// src/Core/Auth.php

namespace App\Core;

use PDO;

class Auth {
    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $sessDir = __DIR__ . '/../../database/sessions';
            if (!is_dir($sessDir)) {
                @mkdir($sessDir, 0777, true);
            }
            if (is_dir($sessDir)) {
                @session_save_path($sessDir);
            }
            $config = require __DIR__ . '/../../config/app.php';
            if (!headers_sent()) {
                session_name($config['session_name'] ?? 'hinaplanon_drrm_session');
                session_start();
            } else {
                @session_name($config['session_name'] ?? 'hinaplanon_drrm_session');
                @session_start();
            }
        }
    }

    public static function login(string $email, string $password): array {
        self::startSession();
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([trim($email)]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email address or password.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email address or password.'];
        }

        if ($user['user_type'] === 'Resident' && (int)$user['is_approved'] !== 1) {
            return [
                'success' => false,
                'message' => 'Your resident registration is currently pending review and approval by the Barangay Head. Please coordinate with Barangay Hinaplanon DRRM.'
            ];
        }

        // Set session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['id_number'] = $user['id_number'];

        return ['success' => true, 'user' => $user];
    }

    public static function logout(): void {
        self::startSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function check(): bool {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array {
        self::startSession();
        if (!self::check()) {
            return null;
        }
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch() ?: null;
    }

    public static function id(): ?int {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string {
        self::startSession();
        return $_SESSION['user_type'] ?? null;
    }

    public static function hasRole($roles): bool {
        self::startSession();
        if (!self::check()) {
            return false;
        }
        $currentRole = self::role();
        if (is_array($roles)) {
            return in_array($currentRole, $roles, true);
        }
        return $currentRole === $roles;
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            self::setFlash('danger', 'Please log in to access this page.');
            header("Location: index.php?route=login");
            exit;
        }
    }

    public static function requireRole($roles): void {
        self::requireLogin();
        if (!self::hasRole($roles)) {
            self::setFlash('danger', 'Access denied. You do not have permission to view that module.');
            header("Location: index.php?route=dashboard");
            exit;
        }
    }

    public static function setFlash(string $type, string $message): void {
        self::startSession();
        $_SESSION['flash'][$type][] = $message;
    }

    public static function getFlashes(): array {
        self::startSession();
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }

    public static function csrfToken(): string {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf(?string $token): bool {
        self::startSession();
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
