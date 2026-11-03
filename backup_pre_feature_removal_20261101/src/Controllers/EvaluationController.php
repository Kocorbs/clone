<?php
// src/Controllers/EvaluationController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\TamEvaluation;

class EvaluationController {
    public function form(): void {
        Auth::requireLogin();
        View::render('evaluation.form', [
            'title' => 'System Evaluation (TAM & ISO 9126 Questionnaire)'
        ]);
    }

    public function submit(): void {
        Auth::requireLogin();
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: index.php?route=evaluation-form");
            exit;
        }

        $user = Auth::user();
        $respType = Request::post('respondent_type', 'Barangay Official');

        TamEvaluation::create([
            'user_id' => $user ? (int)$user['user_id'] : null,
            'respondent_type' => $respType,
            'pu_q1' => (int)Request::post('pu_q1', 5),
            'pu_q2' => (int)Request::post('pu_q2', 5),
            'pu_q3' => (int)Request::post('pu_q3', 5),
            'pu_q4' => (int)Request::post('pu_q4', 5),
            'peou_q1' => (int)Request::post('peou_q1', 5),
            'peou_q2' => (int)Request::post('peou_q2', 5),
            'peou_q3' => (int)Request::post('peou_q3', 5),
            'peou_q4' => (int)Request::post('peou_q4', 5),
            'iso_functionality' => (int)Request::post('iso_functionality', 5),
            'iso_reliability' => (int)Request::post('iso_reliability', 5),
            'iso_usability' => (int)Request::post('iso_usability', 5),
            'iso_efficiency' => (int)Request::post('iso_efficiency', 5),
            'iso_maintainability' => (int)Request::post('iso_maintainability', 5),
            'iso_portability' => (int)Request::post('iso_portability', 5),
            'qualitative_feedback' => trim(Request::post('qualitative_feedback', ''))
        ]);

        Auth::setFlash('success', 'Thank you! Your TAM and ISO 9126 software quality evaluation has been recorded.');
        header("Location: index.php?route=evaluation-results");
        exit;
    }

    public function results(): void {
        Auth::requireLogin();
        $stats = TamEvaluation::getStatistics();
        $rawRecords = TamEvaluation::all();

        View::render('evaluation.results', [
            'title' => 'TAM & ISO 9126 Research Evaluation Results',
            'stats' => $stats,
            'records' => $rawRecords
        ]);
    }

    public function exportCsv(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head']);
        $records = TamEvaluation::all();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="hinaplanon_tam_iso_evaluations_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'ID', 'Respondent Type',
            'PU Q1', 'PU Q2', 'PU Q3', 'PU Q4',
            'PEOU Q1', 'PEOU Q2', 'PEOU Q3', 'PEOU Q4',
            'Functionality', 'Reliability', 'Usability', 'Efficiency', 'Maintainability', 'Portability',
            'Feedback', 'Submitted At'
        ]);

        foreach ($records as $r) {
            fputcsv($out, [
                $r['tam_id'], $r['respondent_type'],
                $r['pu_q1'], $r['pu_q2'], $r['pu_q3'], $r['pu_q4'],
                $r['peou_q1'], $r['peou_q2'], $r['peou_q3'], $r['peou_q4'],
                $r['iso_functionality'], $r['iso_reliability'], $r['iso_usability'], $r['iso_efficiency'], $r['iso_maintainability'], $r['iso_portability'],
                $r['qualitative_feedback'], $r['submitted_at']
            ]);
        }

        fclose($out);
        exit;
    }
}
