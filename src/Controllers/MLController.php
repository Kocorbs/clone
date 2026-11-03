<?php
// src/Controllers/MLController.php

namespace App\Controllers;

use App\Core\Auth;

class MLController {
    public function index(): void {
        Auth::requireLogin();
        Auth::denyAccess('The Decision Tree Engine module is no longer accessible.');
    }

    public function train(): void {
        Auth::requireLogin();
        Auth::denyAccess('The Decision Tree Engine module is no longer accessible.');
    }

    public function downloadTemplate(): void {
        Auth::requireLogin();
        Auth::denyAccess('The Decision Tree Engine module is no longer accessible.');
    }

    public function historicalRecords(): void {
        Auth::requireLogin();
        Auth::denyAccess('The Historical Datasets module is no longer accessible.');
    }

    public function importCsv(): void {
        Auth::requireLogin();
        Auth::denyAccess('The Historical Datasets module is no longer accessible.');
    }
}
