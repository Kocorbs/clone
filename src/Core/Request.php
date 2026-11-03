<?php
// src/Core/Request.php

namespace App\Core;

class Request {
    public static function method(): string {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function isPost(): bool {
        return self::method() === 'POST';
    }

    public static function get(string $key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    public static function post(string $key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    public static function all(): array {
        return array_merge($_GET, $_POST);
    }

    public static function sanitize(string $val): string {
        return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
    }
}
