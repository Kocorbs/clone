<?php
// src/Controllers/FieldReportController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\FieldReport;
use App\Models\DisasterRequest;
use App\Models\Purok;

class FieldReportController {
    public function index(): void {
        Auth::requireLogin();
        $reports = FieldReport::all();

        View::render('field_reports.index', [
            'title' => 'Responder Field Reports',
            'reports' => $reports
        ]);
    }

    public function create(): void {
        Auth::requireRole(['Responder', 'ICDRRMO', 'Barangay Head']);
        $puroks = Purok::all();
        $activeRequests = DisasterRequest::all(['status' => 'Pending']);

        if (Request::isPost()) {
            if (!Auth::validateCsrf(Request::post('csrf_token'))) {
                Auth::setFlash('danger', 'Security verification failed.');
                header("Location: index.php?route=field-reports-create");
                exit;
            }

            $user = Auth::user();
            $responderName = $user['firstname'] . ' ' . $user['lastname'];
            $purokId = (int)Request::post('purok_id');
            $disasterRequestId = Request::post('disaster_request_id') ? (int)Request::post('disaster_request_id') : null;
            $statusUpdate = trim(Request::post('status_update', ''));
            $accessibility = Request::post('road_accessibility', 'Passable');
            $casualties = (int)Request::post('casualties_count', 0);
            $damaged = (int)Request::post('damaged_houses', 0);
            $urgentNeeds = trim(Request::post('urgent_needs', ''));
            $lat = Request::post('latitude') ? (float)Request::post('latitude') : null;
            $lng = Request::post('longitude') ? (float)Request::post('longitude') : null;

            if (empty($statusUpdate) || $purokId <= 0) {
                Auth::setFlash('warning', 'Please provide a status update and select the target Purok.');
                header("Location: index.php?route=field-reports-create");
                exit;
            }

            $reportId = FieldReport::create([
                'user_id' => (int)Auth::id(),
                'disaster_request_id' => $disasterRequestId,
                'purok_id' => $purokId,
                'responder_name' => $responderName,
                'status_update' => $statusUpdate,
                'road_accessibility' => $accessibility,
                'casualties_count' => $casualties,
                'damaged_houses' => $damaged,
                'urgent_needs' => $urgentNeeds,
                'latitude' => $lat,
                'longitude' => $lng
            ]);

            Auth::setFlash('success', "Field report #{$reportId} recorded successfully.");
            header("Location: index.php?route=field-reports");
            exit;
        }

        View::render('field_reports.create', [
            'title' => 'Submit Field Status Update',
            'puroks' => $puroks,
            'activeRequests' => $activeRequests
        ]);
    }

    public function show(): void {
        Auth::requireLogin();
        $id = (int)Request::get('id');
        $report = FieldReport::findById($id);

        if (!$report) {
            Auth::setFlash('danger', 'Field report not found.');
            header("Location: index.php?route=field-reports");
            exit;
        }

        View::render('field_reports.show', [
            'title' => 'Field Report #' . $id,
            'report' => $report
        ]);
    }
}
