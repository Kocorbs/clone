<?php
// src/Controllers/UserController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\User;

class UserController {
    public function index(): void {
        Auth::requireRole(['ICDRRMO']);
        $roleFilter = Request::get('role', '');
        $filters = [];
        if (!empty($roleFilter)) {
            $filters['user_type'] = $roleFilter;
        }

        $users = User::all($filters);
        $roleCounts = User::countByRole();

        View::render('users.index', [
            'title' => 'User Management',
            'users' => $users,
            'roleFilter' => $roleFilter,
            'roleCounts' => $roleCounts
        ]);
    }

    public function pendingResidents(): void {
        Auth::requireRole(['Barangay Head', 'ICDRRMO']);
        $pending = User::getPendingResidents();

        View::render('users.pending_residents', [
            'title' => 'Resident Account Approvals',
            'pendingResidents' => $pending
        ]);
    }

    public function approveResident(): void {
        Auth::requireRole(['Barangay Head', 'ICDRRMO']);
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: index.php?route=pending-residents");
            exit;
        }

        $userId = (int)Request::post('user_id');
        User::approveResident($userId, (int)Auth::id());
        Auth::setFlash('success', 'Resident account approved successfully.');
        header("Location: index.php?route=pending-residents");
        exit;
    }

    public function rejectResident(): void {
        Auth::requireRole(['Barangay Head', 'ICDRRMO']);
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed.');
            header("Location: index.php?route=pending-residents");
            exit;
        }

        $userId = (int)Request::post('user_id');
        $reason = trim(Request::post('rejection_reason', 'Not verified as Barangay Hinaplanon resident'));
        User::rejectResident($userId, $reason);
        Auth::setFlash('info', 'Resident registration declined.');
        header("Location: index.php?route=pending-residents");
        exit;
    }

    public function create(): void {
        Auth::requireRole(['ICDRRMO']);

        if (Request::isPost()) {
            if (!Auth::validateCsrf(Request::post('csrf_token'))) {
                Auth::setFlash('danger', 'Invalid CSRF token.');
                header("Location: index.php?route=users-create");
                exit;
            }

            $email = trim(Request::post('email', ''));
            $password = Request::post('password', '');
            $userType = Request::post('user_type', 'Resident');

            if (User::findByEmail($email)) {
                Auth::setFlash('danger', 'Email is already registered.');
                header("Location: index.php?route=users-create");
                exit;
            }

            User::create([
                'user_type' => $userType,
                'id_number' => trim(Request::post('id_number', '')),
                'lastname' => trim(Request::post('lastname', '')),
                'firstname' => trim(Request::post('firstname', '')),
                'gender' => Request::post('gender', ''),
                'cellphone_number' => trim(Request::post('cellphone_number', '')),
                'email' => $email,
                'password' => $password,
                'is_approved' => 1
            ]);

            Auth::setFlash('success', "User account for {$email} created successfully.");
            header("Location: index.php?route=users");
            exit;
        }

        View::render('users.create', ['title' => 'Create User Account']);
    }
}
