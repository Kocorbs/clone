<?php
// src/Controllers/AuditController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\AuditLog;

class AuditController {
    public function index(): void {
        Auth::requireRole(['ICDRRMO', 'Barangay Head']);
        $logs = AuditLog::all(100);

        View::render('audit.index', [
            'title' => 'System Audit Trail & Security Ledger',
            'logs' => $logs
        ]);
    }
}
