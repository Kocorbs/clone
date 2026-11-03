<?php
// src/Controllers/DashboardController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\DisasterRequest;
use App\Models\Supply;
use App\Models\Recommendation;
use App\Models\PreparednessActivity;
use App\Models\Purok;
use App\Models\User;
use App\Models\FieldReport;
use App\Models\RequestLog;
use App\Models\Shelter;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\Beneficiary;

class DashboardController {
    public function index(): void {
        Auth::requireLogin();
        $role = Auth::role();
        $user = Auth::user();
        $userId = (int)Auth::id();

        // System-wide counts
        $requestCounts = DisasterRequest::getCounts();
        $inventorySummary = Supply::getInventorySummary();
        $lowStockItems = Supply::getLowStock();
        $purokRiskSummary = Purok::getRiskSummary();
        $upcomingActivities = PreparednessActivity::getUpcoming();
        $shelterSummary = Shelter::getSummary();
        $unreadNotifs = Notification::getUnreadCount($userId, $role);

        // Role-filtered records
        $recentRequests = [];
        $recentRecommendations = [];
        $recentReports = [];
        $assignedIncidents = [];
        $myRequests = [];

        $reliefStats = null;
        $recentReliefClaims = [];

        if ($role === 'ICDRRMO') {
            $recentRequests = array_slice(DisasterRequest::all(), 0, 8);
            $recentRecommendations = array_slice(Recommendation::all(), 0, 6);
            $recentReports = array_slice(FieldReport::all(), 0, 6);
            $reliefStats = Beneficiary::getStatistics();
            $recentReliefClaims = array_slice(Beneficiary::all(), 0, 8);
        } elseif ($role === 'Barangay Head') {
            $recentRequests = array_slice(DisasterRequest::all(), 0, 8);
            $recentRecommendations = array_slice(Recommendation::all(), 0, 6);
            $recentReports = array_slice(FieldReport::all(), 0, 6);
        } elseif ($role === 'Responder') {
            $assignedIncidents = DisasterRequest::all(['assigned_to_user_id' => $userId]);
            $recentReports = array_slice(FieldReport::all(), 0, 6);
        } elseif ($role === 'Resident') {
            $myRequests = DisasterRequest::all(['user_id' => $userId]);
        }

        $pendingResidents = ($role === 'Barangay Head' || $role === 'ICDRRMO') ? User::getPendingResidents() : [];
        $recentLogs = ($role === 'ICDRRMO' || $role === 'Barangay Head') ? array_slice(AuditLog::all(8), 0, 8) : [];
        $shelters = Shelter::all();
        $puroks = Purok::all();
        $alerts = Notification::getForUser($userId, $role, 5, 'emergency_alert');

        $data = [
            'title' => 'Command Dashboard — ' . $role,
            'role' => $role,
            'user' => $user,
            'requestCounts' => $requestCounts,
            'inventorySummary' => $inventorySummary,
            'lowStockItems' => $lowStockItems,
            'purokRiskSummary' => $purokRiskSummary,
            'upcomingActivities' => $upcomingActivities,
            'shelterSummary' => $shelterSummary,
            'shelters' => $shelters,
            'puroks' => $puroks,
            'recentRequests' => $recentRequests,
            'recentRecommendations' => $recentRecommendations,
            'assignedIncidents' => $assignedIncidents,
            'myRequests' => $myRequests,
            'pendingResidents' => $pendingResidents,
            'recentLogs' => $recentLogs,
            'recentReports' => $recentReports,
            'alerts' => $alerts,
            'unreadNotifs' => $unreadNotifs,
            'reliefStats' => $reliefStats,
            'recentReliefClaims' => $recentReliefClaims
        ];

        View::render('dashboard.index', $data);
    }
}
