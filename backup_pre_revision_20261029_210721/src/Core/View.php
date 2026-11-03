<?php
// src/Core/View.php

namespace App\Core;

class View {
    public static function render(string $view, array $data = [], string $layout = 'main'): void {
        $data['app'] = require __DIR__ . '/../../config/app.php';
        $data['currentUser'] = Auth::user();
        $data['currentRole'] = Auth::role();
        $data['csrfToken'] = Auth::csrfToken();
        $data['flashes'] = Auth::getFlashes();

        extract($data);

        $viewFile = __DIR__ . '/../../views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            echo "<h1>View [{$view}] not found.</h1>";
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout) {
            $layoutFile = __DIR__ . '/../../views/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
                return;
            }
        }

        echo $content;
    }

    public static function e($str): string {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
    }

    public static function riskBadge(string $risk): string {
        switch (strtolower($risk)) {
            case 'high':
                return '<span class="badge badge-high"><i class="fa fa-exclamation-triangle"></i> High Risk (Pink)</span>';
            case 'medium':
                return '<span class="badge badge-medium"><i class="fa fa-info-circle"></i> Medium Risk (Orange)</span>';
            case 'low':
                return '<span class="badge badge-low"><i class="fa fa-check-circle"></i> Low Risk (Yellow)</span>';
            default:
                return '<span class="badge badge-pending"><i class="fa fa-question-circle"></i> Pending Verification</span>';
        }
    }

    public static function priorityBadge(string $priority): string {
        switch (strtolower($priority)) {
            case 'urgent':
                return '<span class="badge badge-urgent"><i class="fa fa-fire"></i> Urgent</span>';
            case 'high':
                return '<span class="badge badge-high"><i class="fa fa-arrow-up"></i> High</span>';
            case 'medium':
                return '<span class="badge badge-medium"><i class="fa fa-minus"></i> Medium</span>';
            case 'low':
                return '<span class="badge badge-low"><i class="fa fa-arrow-down"></i> Low</span>';
            default:
                return '<span class="badge badge-secondary">' . htmlspecialchars($priority) . '</span>';
        }
    }

    public static function statusBadge(string $status): string {
        switch (strtolower($status)) {
            case 'approved':
            case 'completed':
            case 'fulfilled':
            case 'in stock':
                return '<span class="badge badge-success">' . htmlspecialchars($status) . '</span>';
            case 'pending':
            case 'scheduled':
            case 'generated':
                return '<span class="badge badge-warning">' . htmlspecialchars($status) . '</span>';
            case 'declined':
            case 'rejected':
            case 'cancelled':
            case 'out of stock':
            case 'critical':
                return '<span class="badge badge-danger">' . htmlspecialchars($status) . '</span>';
            case 'recommended':
            case 'ongoing':
            case 'low stock':
            case 'modified':
                return '<span class="badge badge-info">' . htmlspecialchars($status) . '</span>';
            default:
                return '<span class="badge badge-secondary">' . htmlspecialchars($status) . '</span>';
        }
    }
}
