<?php
// src/Controllers/FieldReportController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\FieldReport;
use App\Models\DisasterRequest;
use App\Models\Purok;
use App\Models\Notification;
use App\Models\AuditLog;

class FieldReportController {
    public function index(): void {
        Auth::requireLogin();
        if (!Auth::hasRole(['Responder', 'ICDRRMO', 'Barangay Head'])) {
            Auth::denyAccess('Responder Field Status is not accessible for this role.');
            return;
        }

        $role = Auth::role();
        $userId = (int)Auth::id();

        $statusFilter = Request::get('status', '');
        $purokFilter = Request::get('purok_id', '');
        $scopeFilter = Request::get('scope', ($role === 'Responder' ? 'my' : 'all'));

        $filters = [];
        if (!empty($statusFilter)) $filters['status'] = $statusFilter;
        if (!empty($purokFilter)) $filters['purok_id'] = (int)$purokFilter;
        if ($role === 'Responder' && $scopeFilter === 'my') {
            $filters['user_id'] = $userId;
        }

        $reports = FieldReport::all($filters);
        $counts = FieldReport::getCounts();
        $myCounts = ($role === 'Responder') ? FieldReport::getCounts(['user_id' => $userId]) : $counts;
        $puroks = Purok::all();

        View::render('field_reports.index', [
            'title' => 'Responder Field Reports & Operations Status',
            'reports' => $reports,
            'counts' => $counts,
            'myCounts' => $myCounts,
            'puroks' => $puroks,
            'statusFilter' => $statusFilter,
            'purokFilter' => $purokFilter,
            'scopeFilter' => $scopeFilter,
            'currentRole' => $role
        ]);
    }

    public function create(): void {
        Auth::requireLogin();
        if (!Auth::hasRole(['Responder'])) {
            Auth::denyAccess('Field report submission is restricted to Responders (Personnel) only.');
            return;
        }

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

            $targetPurok = Purok::findById($purokId);
            $purokName = $targetPurok['purok_name'] ?? 'Hinaplanon Sector';

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
                'longitude' => $lng,
                'status' => 'Submitted'
            ]);

            // Automatically notify Barangay Head (Admin)
            Notification::notifyRole(
                'Barangay Head',
                'field_report_submitted',
                'New Field Status Report Awaiting Review',
                "Responder {$responderName} submitted Field Report #{$reportId} for {$purokName}. Road: {$accessibility}. Pending Barangay Head verification.",
                'Normal',
                'field_reports',
                $reportId
            );

            // Automatically notify ICDRRMO (Super Admin) for citywide monitoring
            Notification::notifyRole(
                'ICDRRMO',
                'field_report_submitted',
                'New Field Status Report Received',
                "Responder {$responderName} submitted Field Report #{$reportId} for {$purokName} (Barangay Hinaplanon). Available for citywide monitoring.",
                'Normal',
                'field_reports',
                $reportId
            );

            // Audit Trail Record
            AuditLog::log(
                'Field Report Submitted',
                'field_reports',
                $reportId,
                "Responder {$responderName} submitted ground status report for {$purokName}. Road: {$accessibility}. Dispatched to Barangay Head & ICDRRMO."
            );

            Auth::setFlash('success', "Field report #{$reportId} submitted successfully! Automatically routed to the Barangay Head and ICDRRMO for review and monitoring.");
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
        if (!Auth::hasRole(['Responder', 'ICDRRMO', 'Barangay Head'])) {
            Auth::denyAccess('Responder Field Status is not accessible for this role.');
            return;
        }

        $id = (int)Request::get('id');
        $report = FieldReport::findById($id);

        if (!$report) {
            Auth::setFlash('danger', 'Field report not found.');
            header("Location: index.php?route=field-reports");
            exit;
        }

        View::render('field_reports.show', [
            'title' => 'Field Report #' . $id . ' — ' . $report['purok_name'],
            'report' => $report,
            'currentRole' => Auth::role()
        ]);
    }

    public function verify(): void {
        Auth::requireLogin();
        if (!Auth::hasRole(['Barangay Head'])) {
            Auth::denyAccess('Only Barangay Head has authority to review and verify field reports.');
            return;
        }

        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: index.php?route=field-reports");
            exit;
        }

        $reportId = (int)Request::post('report_id');
        $report = FieldReport::findById($reportId);
        if (!$report) {
            Auth::setFlash('danger', 'Field report not found.');
            header("Location: index.php?route=field-reports");
            exit;
        }

        $notes = trim(Request::post('review_notes', 'Ground report verified and confirmed accurate by Barangay Head.'));
        $reviewer = Auth::user();
        $reviewerName = $reviewer['firstname'] . ' ' . $reviewer['lastname'];

        FieldReport::verify($reportId, (int)Auth::id(), $notes);

        // Notify the submitting responder
        Notification::notifyUser(
            (int)$report['user_id'],
            'field_report_verified',
            'Field Report Verified',
            "Your Field Report #{$reportId} ({$report['purok_name']}) was officially verified by {$reviewerName} ({$reviewer['user_type']}). Remarks: {$notes}",
            'Normal',
            'field_reports',
            $reportId
        );

        // Notify ICDRRMO that report is verified
        if (Auth::role() === 'Barangay Head') {
            Notification::notifyRole(
                'ICDRRMO',
                'field_report_verified',
                'Field Report Verified by Barangay Head',
                "Field Report #{$reportId} for {$report['purok_name']} was verified by Barangay Head {$reviewerName}.",
                'Normal',
                'field_reports',
                $reportId
            );
        }

        AuditLog::log(
            'Field Report Verified',
            'field_reports',
            $reportId,
            "Verified by {$reviewerName} ({$reviewer['user_type']}). Remarks: {$notes}"
        );

        // Connect Verified Field Report to AI Relief Prioritization Analysis (Requirement 7)
        if (class_exists(\App\ML\PurokAssessmentEngine::class)) {
            \App\ML\PurokAssessmentEngine::recalculateForPurok((int)$report['purok_id']);
        }

        Auth::setFlash('success', "Field report #{$reportId} has been successfully verified.");
        header("Location: index.php?route=field-reports");
        exit;
    }

    public function reject(): void {
        Auth::requireLogin();
        if (!Auth::hasRole(['Barangay Head'])) {
            Auth::denyAccess('Only Barangay Head has authority to review and reject field reports.');
            return;
        }

        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: index.php?route=field-reports");
            exit;
        }

        $reportId = (int)Request::post('report_id');
        $report = FieldReport::findById($reportId);
        if (!$report) {
            Auth::setFlash('danger', 'Field report not found.');
            header("Location: index.php?route=field-reports");
            exit;
        }

        $reason = trim(Request::post('rejection_reason', 'Report requires clarification or contains inaccurate ground data.'));
        $reviewer = Auth::user();
        $reviewerName = $reviewer['firstname'] . ' ' . $reviewer['lastname'];

        FieldReport::reject($reportId, (int)Auth::id(), $reason);

        // Notify the submitting responder
        Notification::notifyUser(
            (int)$report['user_id'],
            'field_report_rejected',
            'Field Report Needs Revision / Rejected',
            "Your Field Report #{$reportId} ({$report['purok_name']}) was returned by {$reviewerName} ({$reviewer['user_type']}). Reason: {$reason}",
            'High',
            'field_reports',
            $reportId
        );

        AuditLog::log(
            'Field Report Rejected',
            'field_reports',
            $reportId,
            "Rejected by {$reviewerName} ({$reviewer['user_type']}). Reason: {$reason}"
        );

        Auth::setFlash('info', "Field report #{$reportId} has been rejected / returned for clarification.");
        header("Location: index.php?route=field-reports");
        exit;
    }
}
