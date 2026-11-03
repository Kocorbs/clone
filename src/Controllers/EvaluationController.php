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
        Auth::denyAccess('The TAM & ISO 9126 Results module is no longer accessible.');
    }

    public function submit(): void {
        Auth::requireLogin();
        Auth::denyAccess('The TAM & ISO 9126 Results module is no longer accessible.');
    }

    public function results(): void {
        Auth::requireLogin();
        Auth::denyAccess('The TAM & ISO 9126 Results module is no longer accessible.');
    }

    public function exportCsv(): void {
        Auth::requireLogin();
        Auth::denyAccess('The TAM & ISO 9126 Results module is no longer accessible.');
    }
}
