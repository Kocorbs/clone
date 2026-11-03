<?php
// src/Controllers/SosController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\DisasterRequest;
use App\Models\Purok;

class SosController {
    public function form(): void {
        Auth::requireLogin();
        $puroks = Purok::all();
        $user = Auth::user();

        View::render('sos.form', [
            'title' => 'Emergency SOS Assistance Request',
            'puroks' => $puroks,
            'user' => $user
        ]);
    }

    public function submit(): void {
        Auth::requireLogin();
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification token expired. Please try again.');
            header("Location: index.php?route=emergency-sos");
            exit;
        }

        $user = Auth::user();
        $purokId = (int)Request::post('purok_id');
        $disasterType = Request::post('disaster_type', 'Flood');
        $desc = trim(Request::post('description', ''));
        $phone = trim(Request::post('contact_number', $user['cellphone_number'] ?? ''));
        $persons = max(1, (int)Request::post('affected_persons', 1));
        $lat = Request::post('latitude') ? (float)Request::post('latitude') : null;
        $lng = Request::post('longitude') ? (float)Request::post('longitude') : null;

        if (empty($desc) || $purokId <= 0) {
            Auth::setFlash('warning', 'Please select your affected Purok and describe the emergency situation.');
            header("Location: index.php?route=emergency-sos");
            exit;
        }

        $purok = Purok::findById($purokId);
        $households = max(1, (int)ceil($persons / 4.0));

        $incidentId = DisasterRequest::createSos([
            'user_id' => (int)$user['user_id'],
            'barangay_id' => 1,
            'purok_id' => $purokId,
            'purok_name' => $purok['purok_name'] ?? 'Purok',
            'disaster_type' => $disasterType,
            'weather_name' => 'Emergency Incident / Active Calamity',
            'description' => "EMERGENCY SOS: " . $desc,
            'severity_level' => 'Critical',
            'affected_households' => $households,
            'affected_population' => $persons,
            'disaster_date' => date('Y-m-d'),
            'latitude' => $lat,
            'longitude' => $lng,
            'contact_number' => $phone
        ]);

        $incident = DisasterRequest::findById($incidentId);

        View::render('sos.confirmation', [
            'title' => 'Emergency SOS Received',
            'incident' => $incident
        ]);
    }
}
