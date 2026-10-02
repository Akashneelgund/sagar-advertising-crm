<?php
/**
 * Sagar Advertising CRM - Database Connection Manager
 */

declare(strict_types=1);

class DB {
    private static ?PDO $instance = null;

    public static function connect(): PDO {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $dbname = getenv('DB_NAME') ?: 'sagar_advertising_crm';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') ?: '';

            try {
                self::$instance = new PDO(
                    "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                // If default user failed with Access Denied (e.g. Linux MariaDB root unix_socket restriction), try crm_user fallback
                if (str_contains($e->getMessage(), 'Access denied')) {
                    try {
                        self::$instance = new PDO(
                            "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
                            'crm_user',
                            'crm_password',
                            [
                                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                                PDO::ATTR_EMULATE_PREPARES   => false,
                            ]
                        );
                        return self::$instance;
                    } catch (PDOException $fallbackErr) {
                        // Fall through to report original error
                    }
                }
                http_response_code(500);
                die("Database Connection Error: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    /**
     * Query helper: prepared statement execution
     */
    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch all records
     */
    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Fetch single record
     */
    public static function fetch(string $sql, array $params = []): ?array {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Fetch single column value
     */
    public static function fetchColumn(string $sql, array $params = []): mixed {
        return self::query($sql, $params)->fetchColumn();
    }

    /**
     * Last inserted ID
     */
    public static function lastInsertId(): string {
        return self::connect()->lastInsertId();
    }
}
