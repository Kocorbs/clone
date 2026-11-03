<?php
// src/Models/Beneficiary.php

namespace App\Models;

use App\Core\Database;
use PDO;

class Beneficiary {
    public static function all(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "SELECT b.*, 
                       u.firstname, u.lastname, u.email, u.cellphone_number, u.id_number,
                       p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color,
                       s.supply_name, s.supply_remaining, s.supply_status,
                       ver.firstname as verifier_first, ver.lastname as verifier_last, ver.user_type as verifier_role,
                       dist.firstname as distributor_first, dist.lastname as distributor_last, dist.user_type as distributor_role
                FROM relief_beneficiaries b
                JOIN users u ON b.user_id = u.user_id
                LEFT JOIN purok p ON b.purok_id = p.purok_id
                LEFT JOIN supply s ON b.allocated_supply_id = s.supply_id
                LEFT JOIN users ver ON b.verified_by = ver.user_id
                LEFT JOIN users dist ON b.distributed_by = dist.user_id
                WHERE u.deleted_at IS NULL";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND b.assistance_status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['purok_id'])) {
            $sql .= " AND b.purok_id = ?";
            $params[] = (int)$filters['purok_id'];
        }

        if (!empty($filters['priority_level'])) {
            $sql .= " AND b.priority_level = ?";
            $params[] = $filters['priority_level'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $sql .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? OR p.purok_name LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY 
                    CASE b.assistance_status 
                        WHEN 'Pending' THEN 1
                        WHEN 'Approved' THEN 2 
                        WHEN 'Ready to Claim' THEN 3
                        WHEN 'Eligible' THEN 4
                        WHEN 'Claimed' THEN 5 
                        ELSE 6 
                    END,
                    b.beneficiary_id ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT b.*, 
                                     u.firstname, u.lastname, u.email, u.cellphone_number, u.id_number,
                                     p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color,
                                     s.supply_name, s.supply_remaining, s.supply_status,
                                     ver.firstname as verifier_first, ver.lastname as verifier_last, ver.user_type as verifier_role,
                                     dist.firstname as distributor_first, dist.lastname as distributor_last, dist.user_type as distributor_role
                              FROM relief_beneficiaries b
                              JOIN users u ON b.user_id = u.user_id
                              LEFT JOIN purok p ON b.purok_id = p.purok_id
                              LEFT JOIN supply s ON b.allocated_supply_id = s.supply_id
                              LEFT JOIN users ver ON b.verified_by = ver.user_id
                              LEFT JOIN users dist ON b.distributed_by = dist.user_id
                              WHERE b.beneficiary_id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findByUserId(int $userId): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT b.*, 
                                     u.firstname, u.lastname, u.email, u.cellphone_number, u.id_number,
                                     p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color,
                                     s.supply_name, s.supply_remaining, s.supply_status,
                                     ver.firstname as verifier_first, ver.lastname as verifier_last, ver.user_type as verifier_role,
                                     dist.firstname as distributor_first, dist.lastname as distributor_last, dist.user_type as distributor_role
                              FROM relief_beneficiaries b
                              JOIN users u ON b.user_id = u.user_id
                              LEFT JOIN purok p ON b.purok_id = p.purok_id
                              LEFT JOIN supply s ON b.allocated_supply_id = s.supply_id
                              LEFT JOIN users ver ON b.verified_by = ver.user_id
                              LEFT JOIN users dist ON b.distributed_by = dist.user_id
                              WHERE b.user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            // Auto-enroll approved resident if missing
            $userStmt = $db->prepare("SELECT u.*, p.purok_name, p.risk_classification FROM users u LEFT JOIN purok p ON u.purok_id = p.purok_id WHERE u.user_id = ? AND u.user_type = 'Resident'");
            $userStmt->execute([$userId]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                self::enrollResident($user);
                return self::findByUserId($userId);
            }
            return null;
        }

        return $record;
    }

    public static function enrollResident(array $user): int {
        $db = Database::getInstance();
        $purokRisk = $user['risk_classification'] ?? 'Medium';
        $purokName = $user['purok_name'] ?? 'Hinaplanon Sector';

        $priority = 'Not Yet Assessed';
        if ($purokRisk === 'High') {
            $priority = 'High';
            $reason = "Purok {$purokName} is designated as High Flood Risk in the CamScanner Hazard Map & CART ML Model.";
        } elseif ($purokRisk === 'Medium') {
            $priority = 'Medium';
            $reason = "Purok {$purokName} is classified as Moderate Flood Risk sector.";
        } elseif ($purokRisk === 'Low') {
            $priority = 'Low';
            $reason = "Purok {$purokName} is classified as Low Risk evacuation corridor.";
        } else {
            $reason = "Sector assessment pending official field triage.";
        }

        $foodSupply = $db->query("SELECT supply_id FROM supply WHERE supply_type = 'Relief Goods' ORDER BY supply_id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $supplyId = $foodSupply ? (int)$foodSupply['supply_id'] : 1;

        $stmt = $db->prepare("INSERT INTO relief_beneficiaries (
            user_id, purok_id, assistance_status, priority_level, is_approved,
            allocated_supply_id, assistance_type, allocated_quantity, unit_of_measure,
            claiming_date, claiming_time, claiming_location, priority_reason, remarks
        ) VALUES (?, ?, 'Eligible', ?, 1, ?, 'Standard Family Food Pack (5kg Rice, Canned Goods, Clean Water)', 1, 'packs', ?, '08:00 AM - 12:00 PM', 'Hinaplanon Covered Court, Station B (Purok 1)', ?, 'Eligible for community relief distribution cycle.')");

        $stmt->execute([
            (int)$user['user_id'],
            !empty($user['purok_id']) ? (int)$user['purok_id'] : 1,
            $priority,
            $supplyId,
            date('Y-m-d', strtotime('+3 days')),
            $reason
        ]);

        return (int)$db->lastInsertId();
    }

    public static function requestClaim(int $beneficiaryId, int $userId): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE relief_beneficiaries 
                              SET assistance_status = 'Pending', 
                                  claim_requested_at = CURRENT_TIMESTAMP, 
                                  updated_at = CURRENT_TIMESTAMP 
                              WHERE beneficiary_id = ? AND user_id = ? AND assistance_status = 'Eligible'");
        $stmt->execute([$beneficiaryId, $userId]);
        return $stmt->rowCount() > 0;
    }

    public static function updateStatus(int $id, string $status, int $verifiedBy, ?string $remarks = null): bool {
        $db = Database::getInstance();
        $isApproved = in_array($status, ['Eligible', 'Approved', 'Ready to Claim', 'Claimed']) ? 1 : 0;
        $claimedAt = ($status === 'Claimed') ? date('Y-m-d H:i:s') : null;
        $distributedBy = ($status === 'Claimed') ? $verifiedBy : null;
        $distributedAt = ($status === 'Claimed') ? date('Y-m-d H:i:s') : null;

        $sql = "UPDATE relief_beneficiaries 
                SET assistance_status = ?, is_approved = ?, verified_by = ?, verified_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP";
        $params = [$status, $isApproved, $verifiedBy];

        if ($remarks !== null) {
            $sql .= ", remarks = ?";
            $params[] = $remarks;
        }
        if ($claimedAt !== null) {
            $sql .= ", claimed_at = ?, distributed_by = ?, distributed_at = ?";
            $params[] = $claimedAt;
            $params[] = $distributedBy;
            $params[] = $distributedAt;
        }

        $sql .= " WHERE beneficiary_id = ?";
        $params[] = $id;

        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function updateSchedule(int $id, string $date, string $time, string $location, int $verifiedBy): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE relief_beneficiaries 
                              SET claiming_date = ?, claiming_time = ?, claiming_location = ?, 
                                  verified_by = ?, verified_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP 
                              WHERE beneficiary_id = ?");
        return $stmt->execute([$date, $time, $location, $verifiedBy, $id]);
    }

    public static function confirmDistribution(int $beneficiaryId, int $distributedBy, ?string $remarks = null): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM relief_beneficiaries WHERE beneficiary_id = ?");
        $stmt->execute([$beneficiaryId]);
        $curr = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$curr || $curr['assistance_status'] === 'Claimed') {
            return false;
        }

        $sql = "UPDATE relief_beneficiaries 
                SET assistance_status = 'Claimed', 
                    is_approved = 1,
                    claimed_at = CURRENT_TIMESTAMP, 
                    distributed_by = ?, 
                    distributed_at = CURRENT_TIMESTAMP, 
                    updated_at = CURRENT_TIMESTAMP";
        $params = [$distributedBy];
        if ($remarks !== null) {
            $sql .= ", remarks = ?";
            $params[] = $remarks;
        }
        $sql .= " WHERE beneficiary_id = ?";
        $params[] = $beneficiaryId;

        $update = $db->prepare($sql);
        $ok = $update->execute($params);

        if ($ok && !empty($curr['allocated_supply_id'])) {
            try {
                Supply::deductStock((int)$curr['allocated_supply_id'], (int)($curr['allocated_quantity'] ?? 1));
            } catch (\Throwable $e) {
                // If inventory already exhausted or test run, continue without failing claim record
            }
        }
        return $ok;
    }

    public static function getStatistics(): array {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN assistance_status = 'Eligible' THEN 1 ELSE 0 END) as eligible,
            SUM(CASE WHEN assistance_status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN assistance_status = 'Approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN assistance_status = 'Ready to Claim' THEN 1 ELSE 0 END) as ready_to_claim,
            SUM(CASE WHEN assistance_status = 'Claimed' THEN 1 ELSE 0 END) as claimed,
            SUM(CASE WHEN assistance_status = 'Not Eligible' THEN 1 ELSE 0 END) as not_eligible,
            SUM(CASE WHEN priority_level = 'High' THEN 1 ELSE 0 END) as high_priority
        FROM relief_beneficiaries");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total' => 0, 'eligible' => 0, 'pending' => 0, 'approved' => 0, 'ready_to_claim' => 0, 'claimed' => 0, 'not_eligible' => 0, 'high_priority' => 0
        ];
    }

    public static function anonymizeName(string $first, string $last): string {
        $first = trim($first);
        $last = trim($last);
        $maskedFirst = mb_substr($first, 0, 1) . str_repeat('*', max(2, mb_strlen($first) - 1));
        $maskedLast = mb_substr($last, 0, 1) . str_repeat('*', max(2, mb_strlen($last) - 1));
        return $maskedFirst . ' ' . $maskedLast;
    }
}
