<?php
// src/Controllers/AuthController.php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\User;
use App\Models\Purok;
use App\Models\AuditLog;

class AuthController {
    public function showLogin(): void {
        if (Auth::check()) {
            header("Location: index.php?route=dashboard");
            exit;
        }
        $puroks = Purok::all();
        View::render('auth.login', ['title' => 'Sign In', 'puroks' => $puroks], 'auth');
    }

    public function login(): void {
        if (Auth::check()) {
            header("Location: index.php?route=dashboard");
            exit;
        }

        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Security verification failed. Please try again.');
            header("Location: index.php?route=login");
            exit;
        }

        $identifier = Request::post('identifier', Request::post('email', ''));
        $password = Request::post('password', '');

        if (empty($identifier) || empty($password)) {
            Auth::setFlash('warning', 'Please provide both Barangay ID / Email and your password.');
            header("Location: index.php?route=login");
            exit;
        }

        $result = Auth::login($identifier, $password);

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
        $puroks = Purok::all();
        View::render('auth.register', ['title' => 'Resident Registration (Barangay ID)', 'puroks' => $puroks], 'auth');
    }

    public function register(): void {
        if (!Auth::validateCsrf(Request::post('csrf_token'))) {
            Auth::setFlash('danger', 'Invalid security token.');
            header("Location: index.php?route=register");
            exit;
        }

        $idNumber = trim(Request::post('id_number', ''));
        $firstname = trim(Request::post('firstname', ''));
        $middleName = trim(Request::post('middle_name', ''));
        $lastname = trim(Request::post('lastname', ''));
        $purokId = (int)Request::post('purok_id');
        $age = Request::post('age') ? (int)Request::post('age') : null;
        $birthdate = Request::post('birthdate') ? Request::post('birthdate') : null;
        $gender = Request::post('gender', '');
        $phone = trim(Request::post('cellphone_number', ''));
        $email = trim(Request::post('email', ''));
        $password = Request::post('password', '');
        $confirm = Request::post('password_confirm', '');

        // Validation
        if (empty($idNumber) || empty($lastname) || empty($firstname) || empty($password) || $purokId <= 0) {
            Auth::setFlash('warning', 'Please fill in all required fields: Barangay ID, Name, Purok, and Password.');
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

        // Check unique Barangay ID
        if (User::findByIdNumber($idNumber)) {
            Auth::setFlash('danger', 'A resident account with this Barangay ID Number is already registered.');
            header("Location: index.php?route=register");
            exit;
        }

        if (!empty($email) && User::findByEmail($email)) {
            Auth::setFlash('danger', 'That email address is already associated with an account.');
            header("Location: index.php?route=register");
            exit;
        }

        User::create([
            'user_type' => 'Resident',
            'id_number' => $idNumber,
            'lastname' => $lastname,
            'firstname' => $firstname,
            'middle_name' => $middleName,
            'gender' => $gender,
            'age' => $age,
            'birthdate' => $birthdate,
            'cellphone_number' => $phone,
            'email' => $email,
            'purok_id' => $purokId,
            'password' => $password,
            'is_approved' => 0 // Awaiting Barangay Head verification
        ]);

        Auth::setFlash('success', 'Registration submitted successfully! Your account is now pending identity verification by the Barangay Head. You will be able to sign in once approved.');
        header("Location: index.php?route=login");
        exit;
    }

    public function showForgotPassword(): void {
        View::render('auth.forgot_password', ['title' => 'Account Recovery'], 'auth');
    }

    public function profile(): void {
        Auth::requireLogin();
        $user = Auth::user();
        $puroks = Purok::all();

        if (Request::isPost()) {
            if (!Auth::validateCsrf(Request::post('csrf_token'))) {
                Auth::setFlash('danger', 'Invalid security token.');
                header("Location: index.php?route=profile");
                exit;
            }

            $updateData = [
                'lastname' => trim(Request::post('lastname', '')),
                'firstname' => trim(Request::post('firstname', '')),
                'middle_name' => trim(Request::post('middle_name', '')),
                'gender' => Request::post('gender', ''),
                'age' => Request::post('age') ? (int)Request::post('age') : null,
                'cellphone_number' => trim(Request::post('cellphone_number', '')),
                'id_number' => trim(Request::post('id_number', '')),
                'purok_id' => Request::post('purok_id') ? (int)Request::post('purok_id') : null
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

        View::render('users.profile', ['user' => $user, 'puroks' => $puroks, 'title' => 'Profile Management']);
    }
}
