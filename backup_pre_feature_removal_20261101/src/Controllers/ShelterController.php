<?php
// src/Controllers/ShelterController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\Shelter;
use App\Models\Purok;
use App\Models\AuditLog;
use App\Models\Notification;

class ShelterController {
    public function index(): void {
        Auth::requireLogin();
        $shelters = Shelter::all();
        $summary = Shelter::getSummary();
        $puroks = Purok::all();

        View::render('shelters.index', [
            'title' => 'Evacuation Shelter Management & Monitoring',
            'shelters' => $shelters,
            'summary' => $summary,
            'puroks' => $puroks
        ]);
    }

    public function updateOccupancy(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head', 'Responder']);
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: index.php?route=shelters");
            exit;
        }

        $id = (int)Request::post('shelter_id');
        $occ = (int)Request::post('current_occupancy', 0);
        $status = Request::post('availability_status');

        $shelter = Shelter::findById($id);
        if (!$shelter) {
            Auth::setFlash('danger', 'Shelter record not found.');
            header("Location: index.php?route=shelters");
            exit;
        }

        Shelter::updateOccupancy($id, $occ, $status);

        AuditLog::log('Shelter Occupancy Updated', 'evacuation_shelters', $id, "{$shelter['shelter_name']}: Occupancy set to {$occ} ({$status})");

        // Notify if shelter becomes full or near full
        if (in_array($status, ['Near Full', 'Full'])) {
            Notification::notifyRole(
                'ICDRRMO',
                'shelter_warning',
                "Shelter Alert: {$shelter['shelter_name']} is {$status}",
                "Occupancy reached {$occ} / {$shelter['max_capacity']} evacuees. Secondary shelter opening may be required.",
                'High',
                'evacuation_shelters',
                $id
            );
        }

        Auth::setFlash('success', "Occupancy for '{$shelter['shelter_name']}' updated to {$occ} ({$status}).");
        header("Location: index.php?route=shelters");
        exit;
    }

    public function create(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head']);
        if (Request::isPost()) {
            if (!Auth::validateCsrf(Request::post('csrf_token'))) {
                Auth::setFlash('danger', 'Security verification failed.');
                header("Location: index.php?route=shelters");
                exit;
            }

            $name = trim(Request::post('shelter_name', ''));
            $purokId = (int)Request::post('purok_id');
            $location = trim(Request::post('location', ''));
            $maxCap = (int)Request::post('max_capacity', 100);
            $contact = trim(Request::post('contact_person', ''));
            $phone = trim(Request::post('contact_number', ''));
            $lat = Request::post('latitude') ? (float)Request::post('latitude') : null;
            $lng = Request::post('longitude') ? (float)Request::post('longitude') : null;

            if (empty($name) || $purokId <= 0 || empty($location)) {
                Auth::setFlash('warning', 'Please provide shelter name, affected purok, and address location.');
                header("Location: index.php?route=shelters");
                exit;
            }

            $newId = Shelter::create([
                'shelter_name' => $name,
                'purok_id' => $purokId,
                'location' => $location,
                'current_occupancy' => 0,
                'max_capacity' => $maxCap,
                'availability_status' => 'Open',
                'contact_person' => $contact,
                'contact_number' => $phone,
                'latitude' => $lat,
                'longitude' => $lng
            ]);

            AuditLog::log('Evacuation Shelter Added', 'evacuation_shelters', $newId, "Name: {$name}, Max Cap: {$maxCap}");
            Auth::setFlash('success', "Evacuation shelter '{$name}' registered successfully.");
            header("Location: index.php?route=shelters");
            exit;
        }

        header("Location: index.php?route=shelters");
        exit;
    }
}
