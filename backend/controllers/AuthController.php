<?php
/**
 * Sagar Advertising CRM - Authentication Controller
 */

declare(strict_types=1);

require_once ROOT_PATH . '/backend/core/Controller.php';

class AuthController extends Controller {
    /**
     * Show split-screen login page
     */
    public function login(): void {
        if (Session::isLoggedIn()) {
            $this->redirect('dashboard');
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $username = trim((string)$this->input('username'));
            $password = (string)$this->input('password');
            $remember = (bool)$this->input('remember');

            // Rate limiting: 5 attempts per 5 minutes
            if (!Session::checkRateLimit('login', 5, 300)) {
                $error = 'Too many failed login attempts. Please wait 5 minutes before trying again.';
            } elseif (empty($username) || empty($password)) {
                $error = 'Please enter both username/email and password.';
            } else {
                $user = DB::fetch(
                    "SELECT * FROM `users` WHERE (`username` = ? OR `email` = ?) AND `status` = 'active' LIMIT 1",
                    [$username, $username]
                );

                if ($user && password_verify($password, $user['password_hash'])) {
                    Session::clearRateLimit('login');
                    Session::login($user);
                    AuditLogger::log('LOGIN', 'Auth', $user['id'], "User {$user['name']} signed in.");

                    Session::setFlash('success', "Welcome back, {$user['name']}!");
                    $this->redirect('dashboard');
                } else {
                    $error = 'Invalid credentials or inactive account. Please check your username and password.';
                }
            }
        }

        $this->view('auth/login', ['error' => $error], 'none');
    }

    /**
     * Log out current user
     */
    public function logout(): void {
        if (Session::isLoggedIn()) {
            AuditLogger::log('LOGOUT', 'Auth', Session::userId(), "User logged out.");
        }
        Session::logout();
        Session::setFlash('info', 'You have been successfully signed out.');
        $this->redirect('login');
    }
}
