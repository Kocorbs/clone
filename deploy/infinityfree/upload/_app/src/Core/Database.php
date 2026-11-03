<?php
// src/Core/Database.php

namespace App\Core;

use PDO;
use Exception;

class Database {
    private static ?PDO $instance = null;
    private static string $driver = 'sqlite';

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';
            self::$driver = $config['default'] ?? 'sqlite';
            $connConfig = $config['connections'][self::$driver] ?? null;

            if (!$connConfig) {
                throw new Exception("Database connection configuration for '" . self::$driver . "' not found.");
            }

            try {
                if (self::$driver === 'sqlite') {
                    $dbFile = $connConfig['database'];
                    $dir = dirname($dbFile);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    self::$instance = new PDO("sqlite:" . $dbFile, null, null, $connConfig['options'] ?? []);
                    self::$instance->exec("PRAGMA foreign_keys = ON;");
                } elseif (self::$driver === 'mysql') {
                    $dsn = sprintf(
                        "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                        $connConfig['host'],
                        $connConfig['port'],
                        $connConfig['database'],
                        $connConfig['charset'] ?? 'utf8mb4'
                    );
                    self::$instance = new PDO($dsn, $connConfig['username'], $connConfig['password'], $connConfig['options'] ?? []);
                } else {
                    throw new Exception("Unsupported database driver: " . self::$driver);
                }
            } catch (Exception $e) {
                // Hosting must not silently switch to an unrelated SQLite database.
                error_log('Database connection failed: ' . $e->getMessage());
                throw new Exception('Database connection unavailable. Check hosting configuration.');
            }
        }

        return self::$instance;
    }

    public static function getDriver(): string {
        self::getInstance();
        return self::$driver;
    }
}
