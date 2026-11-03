<?php
// src/Core/Auth.php

namespace App\Core;

use App\Models\User;
use App\Models\AuditLog;
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

    public static function login(string $identifier, string $password): array {
        self::startSession();
        $clean = trim($identifier);

        $user = User::findByIdentifier($clean);

        if (!$user) {
            AuditLog::log('Failed Login Attempt', 'auth', null, "Identifier: {$clean}");
            return ['success' => false, 'message' => 'Invalid credentials. Please verify your Barangay ID / Email and password.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            AuditLog::log('Failed Login Attempt (Password Mismatch)', 'auth', (int)$user['user_id'], "User: {$user['firstname']} {$user['lastname']}");
            return ['success' => false, 'message' => 'Invalid credentials. Please verify your Barangay ID / Email and password.'];
        }

        if ($user['user_type'] === 'Resident' && (int)$user['is_approved'] !== 1) {
            if ((int)$user['is_approved'] === -1) {
                return [
                    'success' => false,
                    'message' => 'Your resident registration was not approved: ' . ($user['rejection_reason'] ?? 'Please contact Barangay Hall.')
                ];
            }
            return [
                'success' => false,
                'message' => 'Your resident registration is currently pending review and verification by the Barangay Head. Please coordinate with Barangay Hinaplanon DRRM.'
            ];
        }

        // Set session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['id_number'] = $user['id_number'];
        $_SESSION['purok_id'] = $user['purok_id'] ?? null;

        AuditLog::log('User Login Successful', 'auth', (int)$user['user_id'], "Role: {$user['user_type']}", (int)$user['user_id'], $_SESSION['user_name'], $user['user_type']);

        return ['success' => true, 'user' => $user];
    }

    public static function logout(): void {
        self::startSession();
        if (self::check()) {
            AuditLog::log('User Logout', 'auth', self::id(), 'Signed out');
        }
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
        return User::findById((int)$_SESSION['user_id']);
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

    public static function redirect(string $url): void {
        if (!headers_sent()) {
            header("Location: " . $url);
        }
        if (php_sapi_name() !== 'cli') {
            exit;
        }
    }

    public static function denyAccess(string $message = 'Access denied. You do not have authorization for that operational module.'): void {
        self::setFlash('danger', $message);
        self::redirect('index.php?route=dashboard');
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            self::setFlash('danger', 'Please sign in to access this module.');
            self::redirect('index.php?route=login');
        }
    }

    public static function requireRole($roles): void {
        self::requireLogin();
        if (!self::hasRole($roles)) {
            self::denyAccess('Access denied. You do not have authorization for that operational module.');
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

    public static function generateCsrf(): string {
        return self::csrfToken();
    }

    public static function validateCsrf(?string $token): bool {
        self::startSession();
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
