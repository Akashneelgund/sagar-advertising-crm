<?php
/**
 * Sagar Advertising CRM - Secure Session Manager
 */

declare(strict_types=1);

class Session {
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool {
        return !empty($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }

    /**
     * Get current logged in user array
     */
    public static function user(): ?array {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Get current user ID
     */
    public static function userId(): ?int {
        return $_SESSION['user']['id'] ?? null;
    }

    /**
     * Get user role
     */
    public static function role(): string {
        return $_SESSION['user']['role'] ?? 'guest';
    }

    /**
     * Authenticate and initialize session
     */
    public static function login(array $user): void {
        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        $_SESSION['last_activity'] = time();
    }

    /**
     * Logout and destroy session
     */
    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Set a flash alert message (success, error, warning, info)
     */
    public static function setFlash(string $type, string $message): void {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    /**
     * Retrieve and clear flash messages
     */
    public static function getFlashes(): array {
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }

    /**
     * Simple Rate Limiting for IP / action (Brute-force protection)
     */
    public static function checkRateLimit(string $key, int $maxAttempts = 5, int $decaySeconds = 300): bool {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $sessionKey = "rate_limit_{$key}_{$ip}";
        
        $current = $_SESSION[$sessionKey] ?? ['attempts' => 0, 'first_attempt' => time()];
        
        if (time() - $current['first_attempt'] > $decaySeconds) {
            $current = ['attempts' => 1, 'first_attempt' => time()];
            $_SESSION[$sessionKey] = $current;
            return true;
        }

        if ($current['attempts'] >= $maxAttempts) {
            return false;
        }

        $current['attempts']++;
        $_SESSION[$sessionKey] = $current;
        return true;
    }

    public static function clearRateLimit(string $key): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        unset($_SESSION["rate_limit_{$key}_{$ip}"]);
    }
}
