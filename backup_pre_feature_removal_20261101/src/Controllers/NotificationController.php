<?php
// src/Controllers/NotificationController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\Notification;

class NotificationController {
    public function index(): void {
        Auth::requireLogin();
        $userId = (int)Auth::id();
        $role = (string)Auth::role();

        $category = Request::get('category');
        $notifications = Notification::getForUser($userId, $role, 60, $category);
        $unreadCount = Notification::getUnreadCount($userId, $role);

        View::render('notifications.index', [
            'title' => 'Notifications & Alerts Center',
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'activeCategory' => $category
        ]);
    }

    public function pollApi(): void {
        Auth::requireLogin();
        $userId = (int)Auth::id();
        $role = (string)Auth::role();

        $unreadCount = Notification::getUnreadCount($userId, $role);
        $latest = Notification::getForUser($userId, $role, 10);

        View::json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $latest,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function markRead(): void {
        Auth::requireLogin();
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            View::json(['success' => false, 'message' => 'Invalid CSRF token.'], 403);
        }

        $id = (int)Request::post('notification_id');
        $userId = (int)Auth::id();
        $res = Notification::markAsRead($id, $userId);

        if (Request::get('format') === 'json') {
            View::json(['success' => $res]);
        }

        header("Location: index.php?route=notifications");
        exit;
    }

    public function markAllRead(): void {
        Auth::requireLogin();
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: index.php?route=notifications");
            exit;
        }

        $userId = (int)Auth::id();
        $role = (string)Auth::role();
        Notification::markAllAsRead($userId, $role);

        Auth::setFlash('success', 'All notifications marked as read.');
        header("Location: index.php?route=notifications");
        exit;
    }
}
