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
        Auth::denyAccess('The Evacuation Shelters module is no longer accessible.');
    }

    public function updateOccupancy(): void {
        Auth::requireLogin();
        Auth::denyAccess('The Evacuation Shelters module is no longer accessible.');
    }

    public function create(): void {
        Auth::requireLogin();
        Auth::denyAccess('The Evacuation Shelters module is no longer accessible.');
    }
}
