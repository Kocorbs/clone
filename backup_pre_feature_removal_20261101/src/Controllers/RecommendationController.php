<?php
// src/Controllers/RecommendationController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\Recommendation;
use App\Models\RequestLog;

class RecommendationController {
    public function index(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head', 'Responder']);
        $statusFilter = Request::get('status', '');
        $priorityFilter = Request::get('priority', '');

        $filters = [];
        if (!empty($statusFilter)) $filters['status'] = $statusFilter;
        if (!empty($priorityFilter)) $filters['priority'] = $priorityFilter;

        $recommendations = Recommendation::all($filters);

        View::render('recommendations.index', [
            'title' => 'Intelligent Resource Recommendations',
            'recommendations' => $recommendations,
            'statusFilter' => $statusFilter,
            'priorityFilter' => $priorityFilter
        ]);
    }

    public function show(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head', 'Responder']);
        $id = (int)Request::get('id');
        $rec = Recommendation::findById($id);

        if (!$rec) {
            Auth::setFlash('danger', 'Recommendation not found.');
            header("Location: index.php?route=recommendations");
            exit;
        }

        $explanation = json_decode($rec['explanation_details'] ?? '{}', true);

        View::render('recommendations.show', [
            'title' => 'Recommendation #' . $id,
            'rec' => $rec,
            'explanation' => $explanation
        ]);
    }

    public function review(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head']);
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security validation failed.');
            header("Location: index.php?route=recommendations");
            exit;
        }

        $id = (int)Request::post('recommendation_id');
        $action = Request::post('action'); // 'approve', 'modify', 'reject'
        $justification = trim(Request::post('justification', ''));
        $modQty = Request::post('modified_quantity') ? (int)Request::post('modified_quantity') : null;

        if (empty($justification)) {
            Auth::setFlash('warning', 'A written justification is required for all official review actions.');
            header("Location: index.php?route=recommendations-show&id={$id}");
            exit;
        }

        $res = Recommendation::review($id, $action, $justification, (int)Auth::id(), $modQty);

        if ($res['success']) {
            Auth::setFlash('success', "Recommendation updated: {$res['status']} successfully. Centralized inventory and audit trail updated.");
        } else {
            Auth::setFlash('danger', 'Action failed: ' . $res['message']);
        }

        header("Location: index.php?route=recommendations-show&id={$id}");
        exit;
    }
}
