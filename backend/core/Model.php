<?php
/**
 * Sagar Advertising CRM - Base Model
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';

abstract class Model {
    protected static string $table = '';

    /**
     * Find record by ID
     */
    public static function find(int $id): ?array {
        $table = static::$table;
        return DB::fetch("SELECT * FROM `{$table}` WHERE `id` = ? LIMIT 1", [$id]);
    }

    /**
     * Get all records with optional ORDER BY
     */
    public static function all(string $orderBy = 'id DESC'): array {
        $table = static::$table;
        return DB::fetchAll("SELECT * FROM `{$table}` ORDER BY {$orderBy}");
    }

    /**
     * Count rows
     */
    public static function count(string $where = '1', array $params = []): int {
        $table = static::$table;
        return (int)DB::fetchColumn("SELECT COUNT(*) FROM `{$table}` WHERE {$where}", $params);
    }

    /**
     * Insert a record and return insert ID
     */
    public static function create(array $data): int {
        $table = static::$table;
        $fields = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $fieldList = implode('`, `', $fields);

        $sql = "INSERT INTO `{$table}` (`{$fieldList}`) VALUES ({$placeholders})";
        DB::query($sql, array_values($data));
        return (int)DB::lastInsertId();
    }

    /**
     * Update record by ID
     */
    public static function update(int $id, array $data): bool {
        $table = static::$table;
        $fields = [];
        $values = [];
        foreach ($data as $key => $val) {
            $fields[] = "`{$key}` = ?";
            $values[] = $val;
        }
        $values[] = $id;
        $sql = "UPDATE `{$table}` SET " . implode(', ', $fields) . " WHERE `id` = ?";
        DB::query($sql, $values);
        return true;
    }

    /**
     * Delete record by ID
     */
    public static function delete(int $id): bool {
        $table = static::$table;
        DB::query("DELETE FROM `{$table}` WHERE `id` = ?", [$id]);
        return true;
    }
}
