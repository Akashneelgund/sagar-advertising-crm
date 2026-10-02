<?php
/**
 * Sagar Advertising CRM - Configuration & Global Helpers
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// App metadata
define('APP_NAME', 'SAGAR ADVERTISING');
define('APP_SHORT_NAME', 'SA');
define('APP_TAGLINE', 'Your Brand. Our Passion.');
define('APP_VERSION', '1.0.0');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('PDF_PATH', STORAGE_PATH . '/pdf');
define('BACKUP_PATH', STORAGE_PATH . '/backups');
define('MAIL_LOG_PATH', STORAGE_PATH . '/mail_logs');

// Detect Base URL dynamically (supports Render / reverse-proxy HTTPS)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? 80) == 443)
    || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
$protocol = $isHttps ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = rtrim($protocol . $host . ($scriptName === '/' ? '' : $scriptName), '/');
define('BASE_URL', $baseUrl);

/**
 * Return absolute URL for app route
 */
function url(string $path = ''): string {
    $trimmed = ltrim($path, '/');
    return BASE_URL . ($trimmed ? '/' . $trimmed : '');
}

/**
 * Return URL for static asset
 */
function asset(string $path): string {
    return url('assets/' . ltrim($path, '/'));
}

/**
 * HTML Escaping shorthand
 */
function e(?string $string): string {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate or get CSRF token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output hidden CSRF input field
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Validate CSRF token
 */
function validate_csrf(?string $token): bool {
    return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Format currency to Indian Rupee format (₹xx,xx,xxx.xx)
 */
function format_currency(float|int|string|null $amount, bool $includeSymbol = true): string {
    $amt = (float)($amount ?? 0);
    $formatted = number_format($amt, 2, '.', ',');
    return ($includeSymbol ? '₹' : '') . $formatted;
}

/**
 * Format date nicely
 */
function format_date(?string $date, string $format = 'd M Y'): string {
    if (!$date) return '-';
    $time = strtotime($date);
    return $time ? date($format, $time) : '-';
}

/**
 * Render Bootstrap / Custom status badge
 */
function get_status_badge(string $status): string {
    $badges = [
        'Draft'            => 'badge-draft',
        'Sent'             => 'badge-sent',
        'Viewed'           => 'badge-viewed',
        'Under Discussion' => 'badge-discussion',
        'Approved'         => 'badge-approved',
        'Rejected'         => 'badge-rejected',
        'Expired'          => 'badge-expired',
        'Converted'        => 'badge-converted',
        'Active'           => 'badge-active',
        'Lead'             => 'badge-lead',
        'Inactive'         => 'badge-inactive',
        'Lost'             => 'badge-lost',
        'pending'          => 'badge-pending',
        'completed'        => 'badge-completed',
        'scheduled'        => 'badge-scheduled',
        'queued'           => 'badge-queued',
        'processing'       => 'badge-processing',
        'paused'           => 'badge-paused'
    ];
    $cls = $badges[$status] ?? 'badge-secondary';
    return '<span class="status-badge ' . $cls . '">' . e($status) . '</span>';
}
