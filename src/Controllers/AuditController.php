<?php
// src/Controllers/AuditController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\AuditLog;

class AuditController {
    public function index(): void {
        Auth::requireLogin();
        Auth::denyAccess('The System Audit Trail module is no longer accessible.');
    }
}
