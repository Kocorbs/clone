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
        Auth::denyAccess('The Emergency SOS module is no longer accessible.');
    }

    public function submit(): void {
        Auth::requireLogin();
        Auth::denyAccess('The Emergency SOS module is no longer accessible.');
    }
}
