<?php
// src/Controllers/DisasterRequestController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\DisasterRequest;
use App\Models\Purok;
use App\Models\Barangay;
use App\Models\Supply;
use App\Models\Recommendation;
use App\ML\RecommenderEngine;
use Exception;

class DisasterRequestController {
    public function index(): void {
        Auth::requireLogin();
        $statusFilter = Request::get('status', '');
        $purokFilter = Request::get('purok_id', '');

        $filters = [];
        if (!empty($statusFilter)) $filters['status'] = $statusFilter;
        if (!empty($purokFilter)) $filters['purok_id'] = (int)$purokFilter;

        $requests = DisasterRequest::all($filters);
        $counts = DisasterRequest::getCounts();
        $puroks = Purok::all();

        View::render('disaster_requests.index', [
            'title' => 'Disaster Incident & Resource Requests',
            'requests' => $requests,
            'counts' => $counts,
            'puroks' => $puroks,
            'statusFilter' => $statusFilter,
            'purokFilter' => $purokFilter
        ]);
    }

    public function create(): void {
        Auth::requireRole(['Barangay Head', 'ICDRRMO', 'Responder']);
        $puroks = Purok::all();
        $barangays = Barangay::all();

        if (Request::isPost()) {
            if (!Auth::validateCsrf(Request::post('csrf_token'))) {
                Auth::setFlash('danger', 'Security validation failed.');
                header("Location: index.php?route=disaster-requests-create");
                exit;
            }

            $purokId = (int)Request::post('purok_id');
            $disasterType = Request::post('disaster_type', 'Flood');
            $weather = trim(Request::post('weather_name', 'Heavy Rain'));
            $desc = trim(Request::post('description', ''));
            $severity = Request::post('severity_level', 'High');
            $isUrgent = !empty(Request::post('is_urgent')) ? 1 : 0;
            $households = max(1, (int)Request::post('affected_households', 1));
            $population = max(1, (int)Request::post('affected_population', $households * 4));
            $date = Request::post('disaster_date', date('Y-m-d'));
            $lat = Request::post('latitude') ? (float)Request::post('latitude') : null;
            $lng = Request::post('longitude') ? (float)Request::post('longitude') : null;

            if (empty($desc) || $purokId <= 0) {
                Auth::setFlash('warning', 'Please provide a description and select the affected Purok.');
                header("Location: index.php?route=disaster-requests-create");
                exit;
            }

            $reqId = DisasterRequest::create([
                'user_id' => (int)Auth::id(),
                'barangay_id' => 1, // Barangay Hinaplanon
                'purok_id' => $purokId,
                'disaster_type' => $disasterType,
                'weather_name' => $weather,
                'description' => $desc,
                'severity_level' => $severity,
                'is_urgent' => $isUrgent,
                'affected_households' => $households,
                'affected_population' => $population,
                'disaster_date' => $date,
                'latitude' => $lat,
                'longitude' => $lng
            ]);

            Auth::setFlash('success', "Disaster Request #{$reqId} successfully logged. You may now run the Decision Tree Recommender.");
            header("Location: index.php?route=disaster-requests-show&id={$reqId}");
            exit;
        }

        View::render('disaster_requests.create', [
            'title' => 'Fill Up Disaster Request Form',
            'puroks' => $puroks,
            'barangays' => $barangays
        ]);
    }

    public function show(): void {
        Auth::requireLogin();
        $id = (int)Request::get('id');
        $request = DisasterRequest::findById($id);

        if (!$request) {
            Auth::setFlash('danger', 'Disaster request record not found.');
            header("Location: index.php?route=disaster-requests");
            exit;
        }

        $recommendation = Recommendation::findByDisasterRequestId($id);
        $supplies = Supply::all();

        View::render('disaster_requests.show', [
            'title' => 'Disaster Request #' . $id,
            'request' => $request,
            'recommendation' => $recommendation,
            'supplies' => $supplies
        ]);
    }

    public function generateRecommendation(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head']);
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security validation failed.');
            header("Location: index.php?route=disaster-requests");
            exit;
        }

        $id = (int)Request::post('disaster_request_id');
        $supplyId = Request::post('supply_id') ? (int)Request::post('supply_id') : null;

        try {
            $result = RecommenderEngine::generateForDisasterRequest($id, $supplyId);
            Auth::setFlash('success', "Decision Tree recommendation generated: Predicted Priority '{$result['priority']}' with recommended quantity {$result['recommended_quantity']} units.");
            header("Location: index.php?route=recommendations-show&id={$result['recommendation_id']}");
            exit;
        } catch (Exception $e) {
            Auth::setFlash('danger', 'Recommendation failed: ' . $e->getMessage());
            header("Location: index.php?route=disaster-requests-show&id={$id}");
            exit;
        }
    }
}
