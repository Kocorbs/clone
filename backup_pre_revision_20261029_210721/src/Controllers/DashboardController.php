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

class DashboardController {
    public function index(): void {
        Auth::requireLogin();
        $role = Auth::role();
        $user = Auth::user();

        $requestCounts = DisasterRequest::getCounts();
        $inventorySummary = Supply::getInventorySummary();
        $lowStockItems = Supply::getLowStock();
        $purokRiskSummary = Purok::getRiskSummary();
        $upcomingActivities = PreparednessActivity::getUpcoming();
        $recentRequests = DisasterRequest::all();
        $recentRecommendations = Recommendation::all();
        $pendingResidents = ($role === 'Barangay Head' || $role === 'ICDRRMO') ? User::getPendingResidents() : [];
        $recentLogs = RequestLog::all();
        $recentReports = FieldReport::all();

        $data = [
            'title' => 'Dashboard — ' . $role,
            'role' => $role,
            'user' => $user,
            'requestCounts' => $requestCounts,
            'inventorySummary' => $inventorySummary,
            'lowStockItems' => $lowStockItems,
            'purokRiskSummary' => $purokRiskSummary,
            'upcomingActivities' => $upcomingActivities,
            'recentRequests' => array_slice($recentRequests, 0, 6),
            'recentRecommendations' => array_slice($recentRecommendations, 0, 6),
            'pendingResidents' => $pendingResidents,
            'recentLogs' => array_slice($recentLogs, 0, 8),
            'recentReports' => array_slice($recentReports, 0, 6),
        ];

        View::render('dashboard.index', $data);
    }
}
