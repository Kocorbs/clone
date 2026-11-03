<?php
// src/Controllers/UserController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\User;
use App\Models\Beneficiary;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\Purok;

class UserController {
    public function index(): void {
        Auth::requireRole(['ICDRRMO']);
        $roleFilter = Request::get('role', '');
        $filters = [];
        if (!empty($roleFilter)) {
            $filters['user_type'] = $roleFilter;
        }

        $users = User::all($filters);
        $roleCounts = User::countByRole();

        View::render('users.index', [
            'title' => 'User Management',
            'users' => $users,
            'roleFilter' => $roleFilter,
            'roleCounts' => $roleCounts
        ]);
    }

    public function pendingResidents(): void {
        Auth::requireRole(['Barangay Head', 'ICDRRMO']);
        $pending = User::getPendingResidents();
        $approved = User::getApprovedResidents();
        $allResidents = User::getAllResidents();
        $declined = array_filter($allResidents, fn($u) => (int)$u['is_approved'] === -1);
        $beneficiaries = Beneficiary::all();
        $beneficiaryStats = Beneficiary::getStatistics();

        View::render('users.pending_residents', [
            'title' => 'Residents Account Management',
            'pendingResidents' => $pending,
            'approvedResidents' => $approved,
            'allResidents' => $allResidents,
            'declinedResidents' => array_values($declined),
            'reliefBeneficiaries' => $beneficiaries,
            'beneficiaryStats' => $beneficiaryStats
        ]);
    }

    public function changeResidentPassword(): void {
        Auth::requireRole(['Barangay Head', 'ICDRRMO']);
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: index.php?route=pending-residents");
            exit;
        }

        $userId = (int)Request::post('user_id');
        $newPassword = trim(Request::post('new_password', ''));

        if (strlen($newPassword) < 6) {
            Auth::setFlash('danger', 'New password must be at least 6 characters.');
            header("Location: index.php?route=pending-residents");
            exit;
        }

        $targetUser = User::findById($userId);
        if (!$targetUser || $targetUser['user_type'] !== 'Resident') {
            Auth::setFlash('danger', 'Target account is not a valid resident.');
            header("Location: index.php?route=pending-residents");
            exit;
        }

        $ok = User::resetResidentPassword($userId, $newPassword, (int)Auth::id());
        if ($ok) {
            Auth::setFlash('success', "Password for resident {$targetUser['firstname']} {$targetUser['lastname']} (Barangay ID: {$targetUser['id_number']}) was successfully updated to: {$newPassword}");
        } else {
            Auth::setFlash('danger', 'Failed to update resident password.');
        }

        header("Location: index.php?route=pending-residents");
        exit;
    }

    public function approveResident(): void {
        Auth::requireRole(['Barangay Head', 'ICDRRMO']);
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: index.php?route=pending-residents");
            exit;
        }

        $userId = (int)Request::post('user_id');
        User::approveResident($userId, (int)Auth::id());
        
        $residentUser = User::findById($userId);
        if ($residentUser && $residentUser['user_type'] === 'Resident') {
            Beneficiary::enrollResident($residentUser);
        }

        Auth::setFlash('success', 'Resident account approved successfully.');
        header("Location: index.php?route=pending-residents");
        exit;
    }

    public function updateBeneficiaryStatus(): void {
        Auth::requireRole(['Barangay Head', 'ICDRRMO']);
        $redirect = (Request::post('redirect') === 'oversight') ? 'index.php?route=relief-claims-oversight' : 'index.php?route=pending-residents';

        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: " . $redirect);
            exit;
        }

        $beneficiaryId = (int)Request::post('beneficiary_id');
        $status = Request::post('assistance_status', 'Pending');
        $remarks = trim(Request::post('remarks', ''));

        if (!in_array($status, ['Eligible', 'Pending', 'Approved', 'Ready to Claim', 'Claimed', 'Not Eligible'])) {
            Auth::setFlash('warning', 'Invalid assistance status specified.');
            header("Location: " . $redirect);
            exit;
        }

        $actorRole = (string)Auth::role();
        $actorUser = Auth::user();
        $actorName = ($actorUser['firstname'] ?? 'Staff') . ' ' . ($actorUser['lastname'] ?? '');

        $ok = Beneficiary::updateStatus($beneficiaryId, $status, (int)Auth::id(), $remarks);
        if ($ok) {
            $beneficiary = Beneficiary::findById($beneficiaryId);
            if ($beneficiary) {
                $resName = $beneficiary['firstname'] . ' ' . $beneficiary['lastname'];
                $purok = $beneficiary['purok_name'] ?? 'Hinaplanon';
                $prio = $beneficiary['priority_level'] ?? 'Normal';

                $title = 'Relief Goods Eligibility Status Update';
                $msg = "Your relief assistance status is now: {$status}. " . (!empty($remarks) ? "Note: {$remarks}" : '');
                $userPrio = 'Normal';

                if ($status === 'Approved') {
                    $title = 'Ayuda Claim Officially Approved';
                    $msg = "Your relief assistance claim has been approved by Barangay Officials. Scheduled pickup: {$beneficiary['claiming_date']} ({$beneficiary['claiming_time']}) at {$beneficiary['claiming_location']}.";
                    $userPrio = 'High';

                    // Real-time notification to ICDRRMO (Super Admin)
                    Notification::notifyRole(
                        'ICDRRMO',
                        'ayuda_claim_approved',
                        'Ayuda Claim Approved by ' . $actorRole,
                        "{$actorRole} ({$actorName}) approved relief claim for Resident {$resName} ({$purok}). Priority: {$prio}. Allocated: {$beneficiary['allocated_quantity']}x {$beneficiary['assistance_type']}. Scheduled pickup: {$beneficiary['claiming_date']} ({$beneficiary['claiming_time']}) at {$beneficiary['claiming_location']}." . (!empty($remarks) ? " Remarks: {$remarks}" : ''),
                        'Normal',
                        'relief_beneficiaries',
                        $beneficiaryId
                    );
                } elseif ($status === 'Not Eligible') {
                    $title = 'Relief Claim Disqualified / Not Eligible';
                    $msg = "Your relief assistance claim was not approved for this cycle. " . (!empty($remarks) ? "Reason: {$remarks}" : "Reason: Household outside active relief boundary.");
                    $userPrio = 'Normal';

                    // Real-time notification to ICDRRMO (Super Admin)
                    Notification::notifyRole(
                        'ICDRRMO',
                        'ayuda_claim_rejected',
                        'Ayuda Claim Rejected by ' . $actorRole,
                        "{$actorRole} ({$actorName}) rejected/disqualified relief claim for Resident {$resName} ({$purok})." . (!empty($remarks) ? " Reason: {$remarks}" : " Reason: Disqualified per census validation."),
                        'High',
                        'relief_beneficiaries',
                        $beneficiaryId
                    );
                } elseif ($status === 'Ready to Claim') {
                    $title = 'Relief Goods Ready for Pickup!';
                    $msg = "Your relief assistance package is staged and Ready to Claim at {$beneficiary['claiming_location']}. Please present a valid ID.";
                    $userPrio = 'High';

                    // Real-time notification to ICDRRMO (Super Admin)
                    Notification::notifyRole(
                        'ICDRRMO',
                        'ayuda_claim_ready',
                        'Relief Allocation Marked Ready for Pickup',
                        "{$actorRole} ({$actorName}) marked relief goods as Ready to Claim for Resident {$resName} ({$purok}) at {$beneficiary['claiming_location']}.",
                        'Info',
                        'relief_beneficiaries',
                        $beneficiaryId
                    );
                } elseif ($status === 'Claimed') {
                    $title = 'Relief Goods Successfully Claimed';
                    $msg = "Your relief assistance package was officially released and claimed. Thank you!";
                    $userPrio = 'Normal';

                    // Real-time notification to ICDRRMO (Super Admin)
                    Notification::notifyRole(
                        'ICDRRMO',
                        'relief_distribution_confirmed',
                        'Relief Goods Claimed',
                        "{$actorRole} ({$actorName}) recorded claim status as Claimed for Resident {$resName} ({$purok}).",
                        'Normal',
                        'relief_beneficiaries',
                        $beneficiaryId
                    );
                } else {
                    // Real-time notification to ICDRRMO (Super Admin)
                    Notification::notifyRole(
                        'ICDRRMO',
                        'ayuda_claim_status_updated',
                        "Relief Status Updated to {$status} by " . $actorRole,
                        "{$actorRole} ({$actorName}) updated relief status for Resident {$resName} ({$purok}) to '{$status}'." . (!empty($remarks) ? " Remarks: {$remarks}" : ''),
                        'Normal',
                        'relief_beneficiaries',
                        $beneficiaryId
                    );
                }

                // If ICDRRMO intervenes, notify Barangay Head
                if ($actorRole === 'ICDRRMO') {
                    Notification::notifyRole(
                        'Barangay Head',
                        'icdrrmo_relief_intervention',
                        'ICDRRMO Super Admin Intervention: Claim ' . $status,
                        "ICDRRMO (Super Admin) intervened on relief claim for Resident {$resName} ({$purok}), setting status to '{$status}'." . (!empty($remarks) ? " Remarks: {$remarks}" : ''),
                        'High',
                        'relief_beneficiaries',
                        $beneficiaryId
                    );
                }

                Notification::create([
                    'user_id' => (int)$beneficiary['user_id'],
                    'notification_type' => 'relief_status_updated',
                    'title' => $title,
                    'message' => $msg,
                    'priority' => $userPrio
                ]);

                $logAction = ($actorRole === 'ICDRRMO') ? 'ICDRRMO Relief Claim Intervention' : 'Relief Status Updated';
                AuditLog::log($logAction, 'relief_beneficiaries', $beneficiaryId, "Status updated to {$status} for resident {$resName} by " . $actorRole . (!empty($remarks) ? " | Remarks: {$remarks}" : ''));
            }
            Auth::setFlash('success', "Beneficiary assistance status updated to '{$status}' successfully.");
        } else {
            Auth::setFlash('danger', 'Failed to update beneficiary status.');
        }

        header("Location: " . $redirect);
        exit;
    }

    public function confirmDistribution(): void {
        Auth::requireRole(['Barangay Head', 'ICDRRMO', 'Responder']);
        $redirect = (Request::post('redirect') === 'oversight') ? 'index.php?route=relief-claims-oversight' : 'index.php?route=pending-residents';

        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: " . $redirect);
            exit;
        }

        $beneficiaryId = (int)Request::post('beneficiary_id');
        $remarks = trim(Request::post('remarks', 'Released at pickup station to household representative.'));

        $beneficiary = Beneficiary::findById($beneficiaryId);
        if (!$beneficiary) {
            Auth::setFlash('danger', 'Beneficiary record not found.');
            header("Location: " . $redirect);
            exit;
        }

        $actorRole = (string)Auth::role();
        $actorUser = Auth::user();
        $actorName = ($actorUser['firstname'] ?? 'Personnel') . ' ' . ($actorUser['lastname'] ?? '');

        $ok = Beneficiary::confirmDistribution($beneficiaryId, (int)Auth::id(), $remarks);
        if ($ok) {
            $resName = $beneficiary['firstname'] . ' ' . $beneficiary['lastname'];
            $purok = $beneficiary['purok_name'] ?? 'Hinaplanon';

            // Real-time notification to Resident
            Notification::create([
                'user_id' => (int)$beneficiary['user_id'],
                'notification_type' => 'relief_distributed',
                'title' => 'Relief Goods Successfully Claimed & Released',
                'message' => "Your relief assistance package was officially released and claimed at {$beneficiary['claiming_location']}. Released by " . $actorRole . ".",
                'priority' => 'High'
            ]);

            // Real-time notification to ICDRRMO (Super Admin)
            Notification::notifyRole(
                'ICDRRMO',
                'relief_distribution_confirmed',
                'Relief Distribution Confirmed by ' . $actorRole,
                "{$actorRole} ({$actorName}) confirmed physical distribution and release of {$beneficiary['allocated_quantity']}x {$beneficiary['assistance_type']} to Resident {$resName} ({$purok}) at {$beneficiary['claiming_location']}." . (!empty($remarks) ? " Remarks: {$remarks}" : ''),
                'Normal',
                'relief_beneficiaries',
                $beneficiaryId
            );

            // If distributor is not Barangay Head, notify Barangay Head
            if ($actorRole !== 'Barangay Head') {
                Notification::notifyRole(
                    'Barangay Head',
                    'relief_distribution_confirmed',
                    'Relief Distribution Confirmed by ' . $actorRole,
                    "{$actorRole} ({$actorName}) confirmed distribution of relief goods to Resident {$resName} ({$purok}) at {$beneficiary['claiming_location']}.",
                    'Normal',
                    'relief_beneficiaries',
                    $beneficiaryId
                );
            }

            AuditLog::log(
                'Relief Goods Distributed',
                'relief_beneficiaries',
                $beneficiaryId,
                "Confirmed distribution of {$beneficiary['assistance_type']} to resident {$resName} by " . $actorRole
            );

            Auth::setFlash('success', "Relief goods distribution confirmed for {$resName}. Status updated to Claimed.");
        } else {
            Auth::setFlash('danger', 'Unable to confirm distribution. Beneficiary may already be marked as Claimed.');
        }

        header("Location: " . $redirect);
        exit;
    }

    public function updateBeneficiarySchedule(): void {
        Auth::requireRole(['Barangay Head', 'ICDRRMO']);
        $redirect = (Request::post('redirect') === 'oversight') ? 'index.php?route=relief-claims-oversight' : 'index.php?route=pending-residents';

        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: " . $redirect);
            exit;
        }

        $beneficiaryId = (int)Request::post('beneficiary_id');
        $date = Request::post('claiming_date');
        $time = trim(Request::post('claiming_time', '08:00 AM - 12:00 PM'));
        $location = trim(Request::post('claiming_location', 'Hinaplanon Covered Court, Station B'));

        $actorRole = (string)Auth::role();
        $actorUser = Auth::user();
        $actorName = ($actorUser['firstname'] ?? 'Staff') . ' ' . ($actorUser['lastname'] ?? '');

        $ok = Beneficiary::updateSchedule($beneficiaryId, $date, $time, $location, (int)Auth::id());
        if ($ok) {
            $beneficiary = Beneficiary::findById($beneficiaryId);
            if ($beneficiary) {
                $resName = $beneficiary['firstname'] . ' ' . $beneficiary['lastname'];
                $purok = $beneficiary['purok_name'] ?? 'Hinaplanon';

                Notification::create([
                    'user_id' => (int)$beneficiary['user_id'],
                    'notification_type' => 'relief_schedule_updated',
                    'title' => 'Relief Goods Claiming Schedule Set',
                    'message' => "Your scheduled claiming date is {$date} ({$time}) at {$location}. Please bring valid ID.",
                    'priority' => 'High'
                ]);

                // Real-time notification to ICDRRMO (if updated by Barangay Head)
                if ($actorRole !== 'ICDRRMO') {
                    Notification::notifyRole(
                        'ICDRRMO',
                        'relief_schedule_updated',
                        'Claiming Schedule Updated by ' . $actorRole,
                        "{$actorRole} ({$actorName}) updated claiming schedule for Resident {$resName} ({$purok}) to {$date} ({$time}) at {$location}.",
                        'Info',
                        'relief_beneficiaries',
                        $beneficiaryId
                    );
                } else {
                    // Notify Barangay Head of ICDRRMO intervention
                    Notification::notifyRole(
                        'Barangay Head',
                        'icdrrmo_relief_intervention',
                        'ICDRRMO Intervention: Claiming Schedule Updated',
                        "ICDRRMO (Super Admin) updated claiming schedule for Resident {$resName} ({$purok}) to {$date} ({$time}) at {$location}.",
                        'Info',
                        'relief_beneficiaries',
                        $beneficiaryId
                    );
                }

                AuditLog::log('Relief Schedule Updated', 'relief_beneficiaries', $beneficiaryId, "Schedule set to {$date} {$time} at {$location} for resident {$resName} by " . $actorRole);
            }
            Auth::setFlash('success', "Claiming schedule updated successfully.");
        } else {
            Auth::setFlash('danger', 'Failed to update claiming schedule.');
        }

        header("Location: " . $redirect);
        exit;
    }

    public function reliefClaimsOversight(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head']);
        $role = (string)Auth::role();
        $userId = (int)Auth::id();

        $statusFilter = Request::get('status', '');
        $priorityFilter = Request::get('priority', '');
        $purokFilter = Request::get('purok_id', '');
        $searchTerm = Request::get('search', '');

        $filters = [];
        if (!empty($statusFilter)) $filters['status'] = $statusFilter;
        if (!empty($priorityFilter)) $filters['priority_level'] = $priorityFilter;
        if (!empty($purokFilter)) $filters['purok_id'] = (int)$purokFilter;
        if (!empty($searchTerm)) $filters['search'] = $searchTerm;

        $beneficiaries = Beneficiary::all($filters);
        $stats = Beneficiary::getStatistics();
        $puroks = Purok::all();

        // Fetch recent relief claim notifications for oversight activity feed
        $rawNotifs = Notification::getForUser($userId, $role, 20);
        $reliefNotifications = array_filter($rawNotifs, function($n) {
            return in_array($n['notification_type'] ?? '', [
                'ayuda_claim_requested', 'ayuda_claim_approved', 'ayuda_claim_rejected', 
                'ayuda_claim_ready', 'relief_status_updated', 'relief_distributed', 
                'relief_distribution_confirmed', 'icdrrmo_relief_intervention', 'relief_schedule_updated'
            ]) || ($n['related_record_type'] ?? '') === 'relief_beneficiaries';
        });

        // Fetch recent audit logs for relief beneficiaries
        $recentAuditLogs = AuditLog::all(30, ['entity_type' => 'relief_beneficiaries']);

        View::render('users.relief_claims_oversight', [
            'title' => 'Relief Claims Monitoring & Oversight',
            'beneficiaries' => $beneficiaries,
            'stats' => $stats,
            'puroks' => $puroks,
            'statusFilter' => $statusFilter,
            'priorityFilter' => $priorityFilter,
            'purokFilter' => $purokFilter,
            'searchTerm' => $searchTerm,
            'reliefNotifications' => array_values($reliefNotifications),
            'recentAuditLogs' => $recentAuditLogs,
            'currentRole' => $role
        ]);
    }

    public function rejectResident(): void {
        Auth::requireRole(['Barangay Head', 'ICDRRMO']);
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: index.php?route=pending-residents");
            exit;
        }

        $userId = (int)Request::post('user_id');
        $reason = trim(Request::post('rejection_reason', 'Not verified as Barangay Hinaplanon resident'));
        User::rejectResident($userId, $reason);
        Auth::setFlash('info', 'Resident registration declined.');
        header("Location: index.php?route=pending-residents");
        exit;
    }

    public function create(): void {
        Auth::requireRole(['ICDRRMO']);

        if (Request::isPost()) {
            if (!Auth::validateCsrf(Request::post('csrf_token'))) {
                Auth::setFlash('danger', 'Invalid CSRF token.');
                header("Location: index.php?route=users-create");
                exit;
            }

            $email = trim(Request::post('email', ''));
            $password = Request::post('password', '');
            $userType = Request::post('user_type', 'Resident');

            if (User::findByEmail($email)) {
                Auth::setFlash('danger', 'Email is already registered.');
                header("Location: index.php?route=users-create");
                exit;
            }

            User::create([
                'user_type' => $userType,
                'id_number' => trim(Request::post('id_number', '')),
                'lastname' => trim(Request::post('lastname', '')),
                'firstname' => trim(Request::post('firstname', '')),
                'gender' => Request::post('gender', ''),
                'cellphone_number' => trim(Request::post('cellphone_number', '')),
                'email' => $email,
                'password' => $password,
                'is_approved' => 1
            ]);

            Auth::setFlash('success', "User account for {$email} created successfully.");
            header("Location: index.php?route=users");
            exit;
        }

        View::render('users.create', ['title' => 'Create User Account']);
    }
}
