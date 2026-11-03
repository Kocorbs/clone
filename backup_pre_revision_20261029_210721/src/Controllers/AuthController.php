<?php
// src/Controllers/AuthController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\User;

class AuthController {
    public function showLogin(): void {
        if (Auth::check()) {
            header("Location: index.php?route=dashboard");
            exit;
        }
        View::render('auth.login', ['title' => 'Sign In'], 'auth');
    }

    public function login(): void {
        if (Auth::check()) {
            header("Location: index.php?route=dashboard");
            exit;
        }

        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Invalid security token. Please try again.');
            header("Location: index.php?route=login");
            exit;
        }

        $email = Request::post('email', '');
        $password = Request::post('password', '');

        if (empty($email) || empty($password)) {
            Auth::setFlash('warning', 'Please provide both email address and password.');
            header("Location: index.php?route=login");
            exit;
        }

        $result = Auth::login($email, $password);

        if ($result['success']) {
            Auth::setFlash('success', 'Welcome back, ' . $result['user']['firstname'] . '! Signed in as ' . $result['user']['user_type'] . '.');
            header("Location: index.php?route=dashboard");
            exit;
        } else {
            Auth::setFlash('danger', $result['message']);
            header("Location: index.php?route=login");
            exit;
        }
    }

    public function logout(): void {
        Auth::logout();
        Auth::setFlash('info', 'You have been securely signed out.');
        header("Location: index.php?route=login");
        exit;
    }

    public function showRegister(): void {
        if (Auth::check()) {
            header("Location: index.php?route=dashboard");
            exit;
        }
        View::render('auth.register', ['title' => 'Resident Registration'], 'auth');
    }

    public function register(): void {
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Invalid security token.');
            header("Location: index.php?route=register");
            exit;
        }

        $email = trim(Request::post('email', ''));
        $password = Request::post('password', '');
        $confirm = Request::post('password_confirm', '');
        $lastname = trim(Request::post('lastname', ''));
        $firstname = trim(Request::post('firstname', ''));
        $gender = Request::post('gender', '');
        $phone = trim(Request::post('cellphone_number', ''));

        if (empty($email) || empty($password) || empty($lastname) || empty($firstname)) {
            Auth::setFlash('warning', 'Please fill in all required registration fields.');
            header("Location: index.php?route=register");
            exit;
        }

        if ($password !== $confirm) {
            Auth::setFlash('danger', 'Password confirmation does not match.');
            header("Location: index.php?route=register");
            exit;
        }

        if (strlen($password) < 6) {
            Auth::setFlash('danger', 'Password must be at least 6 characters.');
            header("Location: index.php?route=register");
            exit;
        }

        if (User::findByEmail($email)) {
            Auth::setFlash('danger', 'An account with that email address already exists.');
            header("Location: index.php?route=register");
            exit;
        }

        User::create([
            'user_type' => 'Resident',
            'id_number' => 'RES-' . date('Y') . '-' . rand(100, 999),
            'lastname' => $lastname,
            'firstname' => $firstname,
            'gender' => $gender,
            'cellphone_number' => $phone,
            'email' => $email,
            'password' => $password,
            'is_approved' => 0 // Requires Barangay Head verification
        ]);

        Auth::setFlash('success', 'Registration submitted successfully! As required by Barangay Hinaplanon policy, your account is pending verification and approval by the Barangay Head.');
        header("Location: index.php?route=login");
        exit;
    }

    public function profile(): void {
        Auth::requireLogin();
        $user = Auth::user();

        if (Request::isPost()) {
            if (!Auth::validateCsrf(Request::post('csrf_token'))) {
                Auth::setFlash('danger', 'Invalid security token.');
                header("Location: index.php?route=profile");
                exit;
            }

            $updateData = [
                'lastname' => trim(Request::post('lastname', '')),
                'firstname' => trim(Request::post('firstname', '')),
                'gender' => Request::post('gender', ''),
                'cellphone_number' => trim(Request::post('cellphone_number', '')),
                'id_number' => trim(Request::post('id_number', ''))
            ];

            $newPass = Request::post('new_password', '');
            if (!empty($newPass)) {
                if (strlen($newPass) < 6) {
                    Auth::setFlash('danger', 'New password must be at least 6 characters.');
                    header("Location: index.php?route=profile");
                    exit;
                }
                $updateData['password'] = $newPass;
            }

            User::update($user['user_id'], $updateData);
            Auth::setFlash('success', 'Profile details updated successfully.');
            header("Location: index.php?route=profile");
            exit;
        }

        View::render('users.profile', ['user' => $user, 'title' => 'Profile Management']);
    }
}
