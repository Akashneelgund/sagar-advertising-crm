<?php
/**
 * Sagar Advertising CRM - Role Based Access Control (RBAC)
 */

declare(strict_types=1);

require_once __DIR__ . '/database.php';

class AuthCheck {
    private static array $cachedPermissions = [];

    /**
     * Check if a specific user (or currently logged in user) has a permission slug
     */
    public static function can(string $permissionSlug, ?array $user = null): bool {
        if ($user === null) {
            $user = $_SESSION['user'] ?? null;
        }

        if (!$user) {
            return false;
        }

        $role = $user['role'] ?? 'employee';

        // Superadmin bypass
        if ($role === 'admin') {
            return true;
        }

        if (!isset(self::$cachedPermissions[$role])) {
            $rows = DB::fetchAll(
                "SELECT permission_slug FROM `role_permissions` WHERE `role` = ?",
                [$role]
            );
            self::$cachedPermissions[$role] = array_column($rows, 'permission_slug');
        }

        return in_array($permissionSlug, self::$cachedPermissions[$role], true);
    }

    /**
     * Require permission or abort with 403 Forbidden
     */
    public static function require(string $permissionSlug): void {
        if (!self::can($permissionSlug)) {
            http_response_code(403);
            if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => '403 Forbidden: Insufficient Permissions']);
            } else {
                echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>";
                echo "<h1 style='color: #FF5500;'>403 - Access Denied</h1>";
                echo "<p>You do not have permission to perform this action ({$permissionSlug}).</p>";
                echo "<a href='" . url('dashboard') . "' style='color: #121417;'>Return to Dashboard</a>";
                echo "</div>";
            }
            exit;
        }
    }
}
