<?php
// src/Models/HistoricalRecord.php

namespace App\Models;

use App\Core\Database;
use Exception;

class HistoricalRecord {
    public static function all(bool $includeSynthetic = true): array {
        $db = Database::getInstance();
        $sql = "SELECT * FROM historical_disaster_records";
        if (!$includeSynthetic) {
            $sql .= " WHERE is_synthetic = 0";
        }
        $sql .= " ORDER BY disaster_date DESC, record_id DESC";
        return $db->query($sql)->fetchAll();
    }

    public static function countVerified(): int {
        $db = Database::getInstance();
        return (int)$db->query("SELECT COUNT(*) FROM historical_disaster_records WHERE is_synthetic = 0 AND is_verified = 1")->fetchColumn();
    }

    public static function countSynthetic(): int {
        $db = Database::getInstance();
        return (int)$db->query("SELECT COUNT(*) FROM historical_disaster_records WHERE is_synthetic = 1")->fetchColumn();
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO historical_disaster_records (
            disaster_date, disaster_type, weather_condition, affected_barangay, 
            affected_purok, purok_risk_level, severity_level, is_urgent, 
            affected_households, affected_population, requested_supply_type, 
            requested_quantity, allocated_quantity, priority_label, data_source, 
            is_synthetic, is_verified, verified_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $data['disaster_date'],
            $data['disaster_type'],
            $data['weather_condition'],
            $data['affected_barangay'] ?? 'Hinaplanon',
            $data['affected_purok'],
            $data['purok_risk_level'],
            $data['severity_level'],
            !empty($data['is_urgent']) ? 1 : 0,
            (int)($data['affected_households'] ?? 1),
            (int)($data['affected_population'] ?? 1),
            $data['requested_supply_type'] ?? 'Relief Goods',
            (int)($data['requested_quantity'] ?? 1),
            (int)($data['allocated_quantity'] ?? 1),
            $data['priority_label'],
            $data['data_source'] ?? 'Official BDRRMC Records',
            !empty($data['is_synthetic']) ? 1 : 0,
            isset($data['is_verified']) ? (int)$data['is_verified'] : 1,
            $data['verified_by'] ?? null
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Validates and imports a CSV file.
     */
    public static function importCsv(string $filePath, int $isSynthetic = 0, ?string $verifiedBy = null): array {
        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => 'CSV file does not exist.'];
        }

        $handle = @fopen($filePath, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'Unable to open CSV file for reading.'];
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return ['success' => false, 'message' => 'CSV file is completely empty.'];
        }

        // Clean headers (remove BOM and trim)
        $cleanHeader = array_map(function($h) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strtolower($h)));
        }, $header);

        $required = ['disaster_date', 'disaster_type', 'affected_purok', 'purok_risk_level', 'severity_level', 'priority_label'];
        foreach ($required as $req) {
            if (!in_array($req, $cleanHeader, true)) {
                fclose($handle);
                return ['success' => false, 'message' => "CSV format error: Missing required column '{$req}'."];
            }
        }

        $inserted = 0;
        $errors = [];
        $rowNum = 1;

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                if (count($row) < count($cleanHeader)) {
                    continue; // Skip malformed row
                }
                $data = array_combine($cleanHeader, $row);

                // Validate Priority Label
                $priority = ucfirst(strtolower(trim($data['priority_label'] ?? '')));
                if (!in_array($priority, ['Urgent', 'High', 'Medium', 'Low'])) {
                    $errors[] = "Row {$rowNum}: Invalid priority label '{$priority}'. Must be Urgent, High, Medium, or Low.";
                    continue;
                }

                // Validate Risk Level
                $risk = ucfirst(strtolower(trim($data['purok_risk_level'] ?? '')));
                if (!in_array($risk, ['High', 'Medium', 'Low', 'Pending Verification'])) {
                    $risk = 'Pending Verification';
                }

                self::create([
                    'disaster_date' => trim($data['disaster_date'] ?? date('Y-m-d')),
                    'disaster_type' => trim($data['disaster_type'] ?? 'Flood'),
                    'weather_condition' => trim($data['weather_condition'] ?? 'Heavy Rain'),
                    'affected_barangay' => trim($data['affected_barangay'] ?? 'Hinaplanon'),
                    'affected_purok' => trim($data['affected_purok'] ?? 'Purok 1'),
                    'purok_risk_level' => $risk,
                    'severity_level' => ucfirst(strtolower(trim($data['severity_level'] ?? 'High'))),
                    'is_urgent' => !empty($data['is_urgent']) ? 1 : 0,
                    'affected_households' => (int)($data['affected_households'] ?? 10),
                    'affected_population' => (int)($data['affected_population'] ?? 40),
                    'requested_supply_type' => trim($data['requested_supply_type'] ?? 'Relief Goods'),
                    'requested_quantity' => (int)($data['requested_quantity'] ?? 10),
                    'allocated_quantity' => (int)($data['allocated_quantity'] ?? 10),
                    'priority_label' => $priority,
                    'data_source' => trim($data['data_source'] ?? ($isSynthetic ? 'Synthetic Demonstration' : 'Official Import')),
                    'is_synthetic' => $isSynthetic,
                    'is_verified' => $isSynthetic ? 0 : 1,
                    'verified_by' => $verifiedBy
                ]);
                $inserted++;
            }

            $db->commit();
            fclose($handle);

            return [
                'success' => true,
                'imported_count' => $inserted,
                'errors' => $errors
            ];
        } catch (Exception $e) {
            $db->rollBack();
            fclose($handle);
            return ['success' => false, 'message' => 'Import failed: ' . $e->getMessage()];
        }
    }
}
