<?php
/**
 * Sagar Advertising CRM - Base Controller
 */

declare(strict_types=1);

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/permissions.php';
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/View.php';
require_once ROOT_PATH . '/backend/services/AuditLogger.php';

abstract class Controller {
    /**
     * Render view template
     */
    protected function view(string $viewPath, array $data = [], string $layout = 'default'): void {
        View::render($viewPath, $data, $layout);
    }

    /**
     * Send JSON HTTP response
     */
    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Redirect to route
     */
    protected function redirect(string $path): void {
        header("Location: " . url($path));
        exit;
    }

    /**
     * Ensure user is authenticated, otherwise redirect to login
     */
    protected function requireAuth(): void {
        if (!Session::isLoggedIn()) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'error' => 'Unauthenticated'], 401);
            }
            Session::setFlash('warning', 'Please sign in to access your Sagar Advertising portal.');
            $this->redirect('login');
        }
    }

    /**
     * Enforce RBAC permission
     */
    protected function requirePermission(string $permissionSlug): void {
        $this->requireAuth();
        AuthCheck::require($permissionSlug);
    }

    /**
     * Validate CSRF on state change
     */
    protected function validateCsrf(): void {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!validate_csrf($token)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'error' => 'Invalid or expired CSRF security token.'], 403);
            }
            Session::setFlash('error', 'Security token expired. Please retry.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? 'dashboard');
        }
    }

    /**
     * Is current request an AJAX / Fetch API request
     */
    protected function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'));
    }

    /**
     * Get sanitized request input parameter
     */
    protected function input(string $key, mixed $default = null): mixed {
        // If JSON input
        if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            static $jsonPayload = null;
            if ($jsonPayload === null) {
                $raw = file_get_contents('php://input');
                $jsonPayload = json_decode($raw, true) ?? [];
            }
            return $jsonPayload[$key] ?? $default;
        }

        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Get all inputs
     */
    protected function allInputs(): array {
        if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true) ?? [];
        }
        return array_merge($_GET, $_POST);
    }
}
