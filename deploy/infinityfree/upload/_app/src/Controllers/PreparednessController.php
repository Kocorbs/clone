<?php
// src/Controllers/PreparednessController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\PreparednessActivity;
use App\Models\Purok;
use App\Models\EventEvaluation;

class PreparednessController {
    public function index(): void {
        Auth::requireLogin();
        $activities = PreparednessActivity::all();

        View::render('preparedness.index', [
            'title' => 'Disaster Preparedness Activities',
            'activities' => $activities
        ]);
    }

    public function calendar(): void {
        Auth::requireLogin();
        $activities = PreparednessActivity::all();

        View::render('preparedness.calendar', [
            'title' => 'Preparedness Activity Calendar',
            'activities' => $activities
        ]);
    }

    public function create(): void {
        Auth::requireRole(['Barangay Head', 'ICDRRMO']);
        $puroks = Purok::all();

        if (Request::isPost()) {
            if (!Auth::validateCsrf(Request::post('csrf_token'))) {
                Auth::setFlash('danger', 'Security validation failed.');
                header("Location: index.php?route=preparedness-create");
                exit;
            }

            $type = Request::post('activity_type', 'Disaster Drill');
            $title = trim(Request::post('title', ''));
            $desc = trim(Request::post('description', ''));
            $purokId = Request::post('target_purok_id') ? (int)Request::post('target_purok_id') : null;
            $date = Request::post('schedule_date');
            $location = trim(Request::post('location', ''));
            $maxPart = (int)Request::post('max_participants', 100);

            if (empty($title) || empty($date) || empty($location)) {
                Auth::setFlash('warning', 'Please complete title, scheduled date/time, and venue location.');
                header("Location: index.php?route=preparedness-create");
                exit;
            }

            $actId = PreparednessActivity::create([
                'user_id' => (int)Auth::id(),
                'barangay_id' => 1,
                'activity_type' => $type,
                'title' => $title,
                'description' => $desc,
                'target_purok_id' => $purokId,
                'schedule_date' => $date,
                'location' => $location,
                'max_participants' => $maxPart
            ]);

            Auth::setFlash('success', "Preparedness activity '{$title}' scheduled successfully.");
            header("Location: index.php?route=preparedness");
            exit;
        }

        View::render('preparedness.create', [
            'title' => 'Schedule Preparedness Activity',
            'puroks' => $puroks
        ]);
    }

    public function evaluate(): void {
        Auth::requireRole(['Barangay Head', 'ICDRRMO', 'Responder']);
        $activityId = (int)Request::get('id');
        $activity = PreparednessActivity::findById($activityId);

        if (!$activity) {
            Auth::setFlash('danger', 'Activity not found.');
            header("Location: index.php?route=preparedness");
            exit;
        }

        if (Request::isPost()) {
            if (!Auth::validateCsrf(Request::post('csrf_token'))) {
                Auth::setFlash('danger', 'Security validation failed.');
                header("Location: index.php?route=preparedness-evaluate&id={$activityId}");
                exit;
            }

            $turnout = (int)Request::post('actual_turnout', 0);
            $rating = (int)Request::post('objectives_met_rating', 5);
            $problems = trim(Request::post('problems_encountered', ''));
            $supplies = trim(Request::post('supplies_used', ''));
            $recommendations = trim(Request::post('recommendations_for_improvement', ''));

            EventEvaluation::create([
                'preparedness_activities_id' => $activityId,
                'user_id' => (int)Auth::id(),
                'actual_turnout' => $turnout,
                'objectives_met_rating' => $rating,
                'problems_encountered' => $problems,
                'supplies_used' => $supplies,
                'recommendations_for_improvement' => $recommendations
            ]);

            // Mark activity completed
            PreparednessActivity::updateStatus($activityId, 'Completed');

            Auth::setFlash('success', 'Event evaluation recorded and activity marked as completed.');
            header("Location: index.php?route=preparedness");
            exit;
        }

        View::render('preparedness.evaluate', [
            'title' => 'Evaluate Activity: ' . $activity['title'],
            'activity' => $activity
        ]);
    }
}
