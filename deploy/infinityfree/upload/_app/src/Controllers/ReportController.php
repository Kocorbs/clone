<?php
// src/Controllers/ReportController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\Recommendation;
use App\Models\DisasterRequest;
use App\Models\Supply;
use App\Models\RequestLog;
use App\Models\PreparednessActivity;

class ReportController {
    public function index(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head']);
        $recommendations = Recommendation::all();
        $disasterRequests = DisasterRequest::all();
        $supplies = Supply::all();
        $logs = RequestLog::all();
        $activities = PreparednessActivity::all();

        View::render('reports.index', [
            'title' => 'Disaster Preparedness & Allocation Reports',
            'recommendations' => $recommendations,
            'disasterRequests' => $disasterRequests,
            'supplies' => $supplies,
            'logs' => $logs,
            'activities' => $activities
        ]);
    }

    public function exportAllocationsCsv(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head']);
        $recs = Recommendation::all();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="hinaplanon_allocations_report_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Recommendation ID', 'Disaster Request ID', 'Purok', 'Risk Zone', 'Predicted Priority', 'Recommended Supply', 'Quantity Allocated', 'Confidence %', 'Status', 'Reviewed By', 'Reviewed At', 'Justification']);

        foreach ($recs as $r) {
            fputcsv($out, [
                $r['recommendation_id'],
                $r['disaster_request_id'],
                $r['purok_name'],
                $r['purok_risk'],
                $r['predicted_priority'],
                $r['supply_name'],
                $r['recommended_quantity'],
                $r['confidence_score'],
                $r['recommendation_status'],
                $r['reviewer_first'] ? ($r['reviewer_first'] . ' ' . $r['reviewer_last']) : 'Pending Review',
                $r['reviewed_at'],
                $r['justification']
            ]);
        }

        fclose($out);
        exit;
    }
}
