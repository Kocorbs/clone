<?php
// src/Core/View.php

namespace App\Core;

use App\Models\Notification;

class View {
    public static function render(string $view, array $data = [], string $layout = 'main'): void {
        $data['app'] = require __DIR__ . '/../../config/app.php';
        $data['currentUser'] = Auth::user();
        $data['currentRole'] = Auth::role();
        $data['csrfToken'] = Auth::csrfToken();
        $data['flashes'] = Auth::getFlashes();

        if (Auth::check()) {
            $data['unreadNotificationCount'] = Notification::getUnreadCount((int)Auth::id(), (string)Auth::role());
        } else {
            $data['unreadNotificationCount'] = 0;
        }

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

    public static function json($data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function e($str): string {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
    }

    public static function riskBadge(string $risk): string {
        switch (strtolower($risk)) {
            case 'high':
                return '<span class="badge badge-risk-high"><i class="fa fa-triangle-exclamation"></i> High Risk (Pink Zone)</span>';
            case 'medium':
                return '<span class="badge badge-risk-medium"><i class="fa fa-circle-exclamation"></i> Medium Risk (Orange Zone)</span>';
            case 'low':
                return '<span class="badge badge-risk-low"><i class="fa fa-shield-halved"></i> Low Risk (Yellow Zone)</span>';
            default:
                return '<span class="badge badge-risk-pending"><i class="fa fa-circle-question"></i> Pending Verification</span>';
        }
    }

    public static function priorityBadge(string $priority): string {
        switch (strtolower($priority)) {
            case 'urgent':
                return '<span class="badge badge-priority-urgent"><i class="fa fa-bolt-lightning"></i> Critical / Urgent</span>';
            case 'high':
                return '<span class="badge badge-priority-high"><i class="fa fa-arrow-up-right-dots"></i> High Priority</span>';
            case 'medium':
            case 'normal':
                return '<span class="badge badge-priority-medium"><i class="fa fa-equals"></i> Medium Priority</span>';
            case 'low':
            case 'info':
                return '<span class="badge badge-priority-low"><i class="fa fa-arrow-down-short-wide"></i> Low Priority</span>';
            default:
                return '<span class="badge badge-neutral">' . htmlspecialchars($priority) . '</span>';
        }
    }

    public static function statusBadge(string $status): string {
        switch (strtolower($status)) {
            case 'reported':
            case 'pending':
                return '<span class="badge badge-status-pending"><i class="fa fa-clock"></i> Pending Review</span>';
            case 'under verification':
                return '<span class="badge badge-status-verification"><i class="fa fa-magnifying-glass-arrow-right"></i> Under Verification</span>';
            case 'verified':
                return '<span class="badge badge-status-verified"><i class="fa fa-check-double"></i> Verified</span>';
            case 'assigned':
                return '<span class="badge badge-status-assigned"><i class="fa fa-user-tag"></i> Responder Assigned</span>';
            case 'responding':
                return '<span class="badge badge-status-responding"><i class="fa fa-truck-fast"></i> Team En Route</span>';
            case 'recommended':
                return '<span class="badge badge-status-recommended"><i class="fa fa-robot"></i> ML Recommended</span>';
            case 'approved':
            case 'fulfilled':
                return '<span class="badge badge-status-approved"><i class="fa fa-circle-check"></i> Approved & Allocated</span>';
            case 'declined':
            case 'rejected':
                return '<span class="badge badge-status-rejected"><i class="fa fa-circle-xmark"></i> Declined</span>';
            case 'resolved':
            case 'completed':
                return '<span class="badge badge-status-completed"><i class="fa fa-flag-checkered"></i> Resolved</span>';
            case 'open':
            case 'in stock':
                return '<span class="badge badge-status-approved"><i class="fa fa-check"></i> ' . htmlspecialchars($status) . '</span>';
            case 'near full':
            case 'low stock':
            case 'modified':
                return '<span class="badge badge-status-verification"><i class="fa fa-triangle-exclamation"></i> ' . htmlspecialchars($status) . '</span>';
            case 'full':
            case 'closed':
            case 'critical':
            case 'out of stock':
                return '<span class="badge badge-status-rejected"><i class="fa fa-ban"></i> ' . htmlspecialchars($status) . '</span>';
            default:
                return '<span class="badge badge-neutral">' . htmlspecialchars($status) . '</span>';
        }
    }
}
