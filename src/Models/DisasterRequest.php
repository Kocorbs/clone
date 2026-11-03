<?php
// src/Models/DisasterRequest.php

namespace App\Models;

use App\Core\Database;
use App\Core\Auth;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\IncidentHistory;
use PDO;

class DisasterRequest {
    public static function all(array $filters = []): array {
        $db = Database::getInstance();
        $sql = "SELECT d.*, b.barangay_name, p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color,
                       u.firstname as requester_first, u.lastname as requester_last, u.user_type, u.cellphone_number as requester_phone,
                       resp.firstname as responder_first, resp.lastname as responder_last,
                       v.firstname as verifier_first, v.lastname as verifier_last
                FROM disaster_request_form d
                JOIN barangay b ON d.barangay_id = b.barangay_id
                JOIN purok p ON d.purok_id = p.purok_id
                JOIN users u ON d.user_id = u.user_id
                LEFT JOIN users resp ON d.assigned_to_user_id = resp.user_id
                LEFT JOIN users v ON d.verified_by_user_id = v.user_id
                WHERE d.deleted_at IS NULL";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND d.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['purok_id'])) {
            $sql .= " AND d.purok_id = ?";
            $params[] = (int)$filters['purok_id'];
        }
        if (!empty($filters['disaster_type'])) {
            $sql .= " AND d.disaster_type = ?";
            $params[] = $filters['disaster_type'];
        }
        if (isset($filters['is_sos'])) {
            $sql .= " AND d.is_sos = ?";
            $params[] = (int)$filters['is_sos'];
        }
        if (!empty($filters['assigned_to_user_id'])) {
            $sql .= " AND d.assigned_to_user_id = ?";
            $params[] = (int)$filters['assigned_to_user_id'];
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND d.user_id = ?";
            $params[] = (int)$filters['user_id'];
        }

        $sql .= " ORDER BY d.disaster_date DESC, d.disaster_request_id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT d.*, b.barangay_name, p.purok_name, p.risk_classification as purok_risk, p.risk_zone_color,
                                     u.firstname as requester_first, u.lastname as requester_last, u.user_type, u.cellphone_number as requester_phone,
                                     resp.firstname as responder_first, resp.lastname as responder_last, resp.id_number as responder_badge, resp.cellphone_number as responder_phone,
                                     v.firstname as verifier_first, v.lastname as verifier_last
                              FROM disaster_request_form d
                              JOIN barangay b ON d.barangay_id = b.barangay_id
                              JOIN purok p ON d.purok_id = p.purok_id
                              JOIN users u ON d.user_id = u.user_id
                              LEFT JOIN users resp ON d.assigned_to_user_id = resp.user_id
                              LEFT JOIN users v ON d.verified_by_user_id = v.user_id
                              WHERE d.disaster_request_id = ? AND d.deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getInstance();
        $refNo = $data['reference_no'] ?? ('DR-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5)));

        $stmt = $db->prepare("INSERT INTO disaster_request_form (
            user_id, barangay_id, purok_id, disaster_type, weather_name, description, 
            severity_level, is_urgent, affected_households, affected_population, 
            disaster_date, status, latitude, longitude, reference_no, is_sos, contact_number
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $status = $data['status'] ?? 'Pending';

        $stmt->execute([
            $data['user_id'],
            $data['barangay_id'] ?? 1,
            $data['purok_id'],
            $data['disaster_type'],
            $data['weather_name'] ?? 'Inclement Weather',
            $data['description'],
            $data['severity_level'],
            !empty($data['is_urgent']) ? 1 : 0,
            (int)($data['affected_households'] ?? 1),
            (int)($data['affected_population'] ?? 1),
            $data['disaster_date'] ?? date('Y-m-d'),
            $status,
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            $refNo,
            !empty($data['is_sos']) ? 1 : 0,
            $data['contact_number'] ?? null
        ]);

        $newId = (int)$db->lastInsertId();

        // Record initial status in history
        IncidentHistory::record($newId, (int)$data['user_id'], 'None', $status, 'Incident initially reported.');

        // Audit log
        AuditLog::log('Disaster Request Created', 'disaster_request_form', $newId, "Ref: {$refNo}, Type: {$data['disaster_type']}, Severity: {$data['severity_level']}");

        // Notify Barangay Head and ICDRRMO
        $purok = Purok::findById((int)$data['purok_id']);
        $purokName = $purok['purok_name'] ?? 'Purok';
        $priority = (!empty($data['is_urgent']) || in_array($data['severity_level'], ['Critical', 'High'])) ? 'Urgent' : 'Normal';

        Notification::notifyRole(
            'Barangay Head',
            'new_incident',
            "New Incident Reported: {$data['disaster_type']} at {$purokName}",
            "Incident #{$newId} ({$refNo}) reported. Severity: {$data['severity_level']}. Affected: {$data['affected_households']} HH.",
            $priority,
            'disaster_request_form',
            $newId
        );

        Notification::notifyRole(
            'ICDRRMO',
            'new_incident',
            "Disaster Request Logged: {$purokName} ({$data['disaster_type']})",
            "Reference {$refNo}. Severity: {$data['severity_level']}. Ready for ML priority analysis & coordination.",
            $priority,
            'disaster_request_form',
            $newId
        );

        return $newId;
    }

    public static function createSos(array $data): int {
        $refNo = 'SOS-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        $data['reference_no'] = $refNo;
        $data['is_urgent'] = 1;
        $data['is_sos'] = 1;
        $data['severity_level'] = $data['severity_level'] ?? 'Critical';
        $data['status'] = 'Under Verification';

        $purokName = $data['purok_name'] ?? '';
        if (empty($purokName) && !empty($data['purok_id'])) {
            $p = Purok::findById((int)$data['purok_id']);
            $purokName = $p['purok_name'] ?? 'Barangay Hinaplanon';
        }
        $data['purok_name'] = $purokName;

        $id = self::create($data);

        // Send high priority alert to Responders too
        Notification::notifyRole(
            'Responder',
            'emergency_alert',
            "EMERGENCY SOS ALERT: {$data['disaster_type']}",
            "Resident SOS #{$refNo} submitted from {$purokName}. Immediate situational verification requested.",
            'Urgent',
            'disaster_request_form',
            $id
        );

        return $id;
    }

    public static function updateStatus(int $id, string $newStatus, ?int $userId = null, ?string $remarks = null): bool {
        $db = Database::getInstance();
        $curr = self::findById($id);
        if (!$curr) return false;

        if ($userId === null) {
            $userId = Auth::id() ? (int)Auth::id() : 1;
        }

        $prevStatus = $curr['status'];
        $stmt = $db->prepare("UPDATE disaster_request_form SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE disaster_request_id = ?");
        $ok = $stmt->execute([$newStatus, $id]);

        if ($ok) {
            IncidentHistory::record($id, $userId, $prevStatus, $newStatus, $remarks);
            AuditLog::log("Incident Status Changed: {$prevStatus} -> {$newStatus}", 'disaster_request_form', $id, $remarks);

            // Notify requester
            Notification::notifyUser(
                (int)$curr['user_id'],
                'request_status_update',
                "Incident Update: {$curr['reference_no']}",
                "Your reported incident status is now: {$newStatus}." . ($remarks ? " Note: {$remarks}" : ""),
                'Normal',
                'disaster_request_form',
                $id
            );
        }

        return $ok;
    }

    public static function verifyIncident(int $id, int $verifierUserId, ?string $remarks = null): bool {
        $db = Database::getInstance();
        $curr = self::findById($id);
        if (!$curr) return false;

        $stmt = $db->prepare("UPDATE disaster_request_form 
            SET status = 'Verified', verified_by_user_id = ?, verified_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP 
            WHERE disaster_request_id = ?");
        $ok = $stmt->execute([$verifierUserId, $id]);

        if ($ok) {
            IncidentHistory::record($id, $verifierUserId, $curr['status'], 'Verified', $remarks ?? 'Officially verified by Barangay personnel.');
            AuditLog::log('Incident Verified', 'disaster_request_form', $id, $remarks);

            Notification::notifyRole(
                'ICDRRMO',
                'incident_verified',
                "Incident Verified: {$curr['purok_name']}",
                "Barangay Head verified incident #{$id}. Ready for responder assignment and resource dispatch.",
                'High',
                'disaster_request_form',
                $id
            );
        }

        return $ok;
    }

    public static function assignResponder(int $id, int $responderUserId, int $assignerUserId, ?string $instructions = null): bool {
        $db = Database::getInstance();
        $curr = self::findById($id);
        if (!$curr) return false;

        $stmt = $db->prepare("UPDATE disaster_request_form 
            SET status = 'Assigned', assigned_to_user_id = ?, assigned_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP 
            WHERE disaster_request_id = ?");
        $ok = $stmt->execute([$responderUserId, $id]);

        if ($ok) {
            IncidentHistory::record($id, $assignerUserId, $curr['status'], 'Assigned', "Assigned to responder ID #{$responderUserId}. " . ($instructions ?? ''));
            AuditLog::log('Responder Assigned to Incident', 'disaster_request_form', $id, "Responder User #{$responderUserId}");

            // Notify assigned responder
            Notification::notifyUser(
                $responderUserId,
                'incident_assigned',
                "NEW MISSION ASSIGNMENT: {$curr['disaster_type']} at {$curr['purok_name']}",
                "You have been assigned to incident #{$id} ({$curr['reference_no']}). " . ($instructions ? "Instructions: {$instructions}" : "Please open dashboard to review and acknowledge."),
                'Urgent',
                'disaster_request_form',
                $id
            );

            // Notify requester
            Notification::notifyUser(
                (int)$curr['user_id'],
                'responder_assigned',
                "Responder Assigned to Your Incident",
                "Emergency responder team has been dispatched/assigned to coordinate assistance.",
                'High',
                'disaster_request_form',
                $id
            );
        }

        return $ok;
    }

    public static function getCounts(): array {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status IN ('Pending', 'Under Verification') THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'Verified' THEN 1 ELSE 0 END) as verified,
            SUM(CASE WHEN status IN ('Assigned', 'Responding') THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'Recommended' THEN 1 ELSE 0 END) as recommended,
            SUM(CASE WHEN status IN ('Approved', 'Completed', 'Resolved') THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status IN ('Resolved', 'Completed') THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN severity_level IN ('Critical', 'High') OR is_urgent = 1 THEN 1 ELSE 0 END) as high_priority,
            SUM(CASE WHEN is_sos = 1 AND status NOT IN ('Resolved', 'Completed') THEN 1 ELSE 0 END) as active_sos,
            SUM(affected_households) as total_affected_families,
            SUM(affected_population) as total_affected_population,
            COUNT(DISTINCT purok_id) as total_affected_puroks
            FROM disaster_request_form WHERE deleted_at IS NULL");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total' => 0, 'pending' => 0, 'verified' => 0, 'in_progress' => 0,
            'recommended' => 0, 'approved' => 0, 'completed' => 0, 'high_priority' => 0,
            'active_sos' => 0, 'total_affected_families' => 0, 'total_affected_population' => 0,
            'total_affected_puroks' => 0
        ];
    }
}
