<?php
// src/Controllers/HazardMapController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\Purok;

class HazardMapController {
    public function index(): void {
        Auth::requireLogin();
        $puroks = Purok::all();
        $riskSummary = Purok::getRiskSummary();

        View::render('hazard_map.index', [
            'title' => 'Barangay Hinaplanon Hazard Map & Risk Zones',
            'puroks' => $puroks,
            'riskSummary' => $riskSummary
        ]);
    }

    public function updateRisk(): void {
        Auth::requireRole(['Barangay Head', 'ICDRRMO']);
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security validation failed.');
            header("Location: index.php?route=hazard-map");
            exit;
        }

        $purokId = (int)Request::post('purok_id');
        $risk = Request::post('risk_classification', 'Medium');
        $notes = trim(Request::post('notes', ''));

        $colors = [
            'High' => 'Pink',
            'Medium' => 'Orange',
            'Low' => 'Yellow',
            'Pending Verification' => 'Gray'
        ];
        $color = $colors[$risk] ?? 'Gray';

        Purok::updateRisk($purokId, $risk, $color, $notes);

        Auth::setFlash('success', "Purok risk classification updated to '{$risk}' ({$color}) with recorded official notes.");
        header("Location: index.php?route=hazard-map");
        exit;
    }
}
