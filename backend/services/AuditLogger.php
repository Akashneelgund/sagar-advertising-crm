<?php
/**
 * Sagar Advertising CRM - Activity Audit Logger
 */

declare(strict_types=1);

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/backend/core/Session.php';

class AuditLogger {
    /**
     * Record an audit log entry
     */
    public static function log(string $action, string $module, ?int $recordId = null, ?string $details = null): void {
        try {
            $userId = Session::userId();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            DB::query(
                "INSERT INTO `activity_logs` (`user_id`, `action`, `module`, `record_id`, `details`, `ip_address`)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$userId, $action, $module, $recordId, $details, $ip]
            );
        } catch (Exception $e) {
            // Fail silently so logging never breaks primary business workflows
            error_log("Audit Logger failed: " . $e->getMessage());
        }
    }
}
