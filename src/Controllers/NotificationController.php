<?php
// src/Controllers/NotificationController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\Notification;
use App\Models\Beneficiary;
use App\Models\Supply;
use App\Models\PreparednessActivity;
use App\Models\Purok;
use App\Models\User;
use App\Models\AuditLog;

class NotificationController {
    public function index(): void {
        Auth::requireLogin();
        if (!Auth::hasRole('Resident')) {
            Auth::denyAccess('Notifications & Alerts module is not accessible for this role.');
            return;
        }
        $userId = (int)Auth::id();
        $role = (string)Auth::role();

        $user = User::findById($userId);
        $myBeneficiary = Beneficiary::findByUserId($userId);

        $statusFilter = Request::get('beneficiary_status', '');
        $purokFilter = Request::get('purok_id', '');
        $searchTerm = Request::get('search', '');

        $beneficiaryFilters = [];
        if (!empty($statusFilter)) $beneficiaryFilters['status'] = $statusFilter;
        if (!empty($purokFilter)) $beneficiaryFilters['purok_id'] = (int)$purokFilter;
        if (!empty($searchTerm)) $beneficiaryFilters['search'] = $searchTerm;

        $beneficiaries = Beneficiary::all($beneficiaryFilters);
        $beneficiaryStats = Beneficiary::getStatistics();
        $puroks = Purok::all();

        // Centralized relief inventory items
        $supplies = Supply::all();
        $reliefSupplies = array_filter($supplies, function($s) {
            return in_array($s['supply_type'], ['Relief Goods', 'Clean Water', 'Hygiene Kits', 'Shelter Kits', 'Medical Supplies']);
        });

        // Community General Alerts
        $category = Request::get('category');
        $notifications = Notification::getForUser($userId, $role, 60, $category);
        $unreadCount = Notification::getUnreadCount($userId, $role);

        // Community Preparedness Activities
        $preparednessActivities = PreparednessActivity::all();

        View::render('notifications.index', [
            'title' => 'Relief Goods & Community Preparedness Hub',
            'user' => $user,
            'myBeneficiary' => $myBeneficiary,
            'beneficiaries' => $beneficiaries,
            'beneficiaryStats' => $beneficiaryStats,
            'reliefSupplies' => array_values($reliefSupplies),
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'preparednessActivities' => $preparednessActivities,
            'puroks' => $puroks,
            'statusFilter' => $statusFilter,
            'purokFilter' => $purokFilter,
            'searchTerm' => $searchTerm,
            'activeCategory' => $category
        ]);
    }

    public function pollApi(): void {
        Auth::requireLogin();
        $role = (string)Auth::role();
        if ($role !== 'Resident') {
            View::json([
                'success' => false,
                'unread_count' => 0,
                'notifications' => [],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            return;
        }

        $userId = (int)Auth::id();
        $unreadCount = Notification::getUnreadCount($userId, $role);
        $latest = Notification::getForUser($userId, $role, 10);

        View::json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $latest,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function markRead(): void {
        Auth::requireRole('Resident');
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            View::json(['success' => false, 'message' => 'Invalid CSRF token.'], 403);
            return;
        }

        $id = (int)Request::post('notification_id');
        $userId = (int)Auth::id();
        $res = Notification::markAsRead($id, $userId);

        if (Request::get('format') === 'json') {
            View::json(['success' => $res]);
            return;
        }

        header("Location: index.php?route=notifications");
        exit;
    }

    public function markAllRead(): void {
        Auth::requireRole('Resident');
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: index.php?route=notifications");
            exit;
        }

        $userId = (int)Auth::id();
        $role = (string)Auth::role();
        Notification::markAllAsRead($userId, $role);

        Auth::setFlash('success', 'All notifications marked as read.');
        header("Location: index.php?route=notifications");
        exit;
    }

    public function claimAyuda(): void {
        Auth::requireRole('Resident');
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: index.php?route=notifications");
            exit;
        }

        $userId = (int)Auth::id();
        $user = Auth::user();
        $residentName = $user['firstname'] . ' ' . $user['lastname'];
        $purokName = $user['purok_name'] ?? 'Barangay Hinaplanon';

        $beneficiary = Beneficiary::findByUserId($userId);
        if (!$beneficiary) {
            Auth::setFlash('danger', 'Beneficiary record not found.');
            header("Location: index.php?route=notifications");
            exit;
        }

        $beneficiaryId = (int)$beneficiary['beneficiary_id'];

        // Strict duplicate claim prevention
        if ($beneficiary['assistance_status'] !== 'Eligible') {
            Auth::setFlash('warning', "You already have an active or processed claim in progress (Current Status: {$beneficiary['assistance_status']}). Duplicate claims are strictly prevented.");
            header("Location: index.php?route=notifications");
            exit;
        }

        $ok = Beneficiary::requestClaim($beneficiaryId, $userId);
        if ($ok) {
            // Notify Barangay Head (Admin)
            Notification::notifyRole(
                'Barangay Head',
                'ayuda_claim_requested',
                'New Ayuda Claim Request Submitted',
                "Resident {$residentName} ({$purokName}) submitted an official claim request for relief assistance ({$beneficiary['allocated_quantity']}x {$beneficiary['assistance_type']}). Priority: {$beneficiary['priority_level']}. Pickup Station: {$beneficiary['claiming_location']}. Awaiting verification.",
                'Normal',
                'relief_beneficiaries',
                $beneficiaryId
            );

            // Notify ICDRRMO (Super Admin) for oversight
            Notification::notifyRole(
                'ICDRRMO',
                'ayuda_claim_requested',
                'New Ayuda Claim Request Submitted',
                "Resident {$residentName} ({$purokName}) submitted an official claim request for relief goods ({$beneficiary['allocated_quantity']}x {$beneficiary['assistance_type']}). Priority: {$beneficiary['priority_level']}. Pickup Station: {$beneficiary['claiming_location']}. Awaiting Barangay Head verification.",
                'Normal',
                'relief_beneficiaries',
                $beneficiaryId
            );

            // Notify Resident
            Notification::create([
                'user_id' => $userId,
                'notification_type' => 'ayuda_claim_submitted',
                'title' => 'Ayuda Claim Request Received',
                'message' => "Your claim request for {$beneficiary['assistance_type']} has been officially submitted and is queued for Barangay Head verification. Scheduled location: {$beneficiary['claiming_location']}.",
                'priority' => 'Normal'
            ]);

            // Audit Trail
            AuditLog::log(
                'Ayuda Claim Requested',
                'relief_beneficiaries',
                $beneficiaryId,
                "Resident {$residentName} submitted claim request for relief goods."
            );

            Auth::setFlash('success', 'Your Ayuda claim request has been submitted successfully! Barangay officials have been notified to review and verify your request.');
        } else {
            Auth::setFlash('danger', 'Unable to submit claim request. Please verify eligibility.');
        }

        header("Location: index.php?route=notifications");
        exit;
    }
}
