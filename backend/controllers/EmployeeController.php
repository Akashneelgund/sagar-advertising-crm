<?php
/**
 * Sagar Advertising CRM - Employee & User Access Controller
 */

declare(strict_types=1);

require_once ROOT_PATH . '/backend/core/Controller.php';

class EmployeeController extends Controller {
    public function index(): void {
        $this->requirePermission('employees.manage');

        $users = DB::fetchAll(
            "SELECT u.*,
                    (SELECT COUNT(*) FROM `quotations` q WHERE q.sales_person_id = u.id) as quote_count,
                    (SELECT COUNT(*) FROM `customers` c WHERE c.assigned_employee_id = u.id) as customer_count
             FROM `users` u
             ORDER BY u.role ASC, u.name ASC"
        );

        $permissions = DB::fetchAll("SELECT * FROM `permissions` ORDER BY module ASC, name ASC");

        $this->view('employees/index', [
            'users'       => $users,
            'permissions' => $permissions
        ]);
    }

    public function store(): void {
        $this->requirePermission('employees.manage');
        $this->validateCsrf();

        $name = trim((string)$this->input('name'));
        $email = trim((string)$this->input('email'));
        $username = trim((string)$this->input('username'));
        $password = (string)$this->input('password');
        $role = $this->input('role', 'employee');
        $dept = trim((string)$this->input('department', 'Sales'));
        $phone = trim((string)$this->input('phone', ''));

        if (empty($name) || empty($email) || empty($username) || empty($password)) {
            Session::setFlash('error', 'All fields including password are required.');
            $this->redirect('employees');
        }

        // Check duplicates
        $exists = DB::fetch("SELECT id FROM `users` WHERE email = ? OR username = ?", [$email, $username]);
        if ($exists) {
            Session::setFlash('error', 'A user with that email or username already exists.');
            $this->redirect('employees');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        DB::query(
            "INSERT INTO `users` (`name`, `email`, `phone`, `role`, `department`, `username`, `password_hash`, `status`)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'active')",
            [$name, $email, $phone, $role, $dept, $username, $hash]
        );

        $newId = (int)DB::lastInsertId();
        AuditLogger::log('CREATE', 'Users', $newId, "Created user account for {$name} ({$role})");
        Session::setFlash('success', "Employee account created for '{$name}'.");
        $this->redirect('employees');
    }

    public function update(): void {
        $this->requirePermission('employees.manage');
        $this->validateCsrf();

        $id = (int)$this->input('id');
        $name = trim((string)$this->input('name'));
        $email = trim((string)$this->input('email'));
        $role = $this->input('role', 'employee');
        $status = $this->input('status', 'active');
        $password = (string)$this->input('password');

        if (empty($name) || empty($email)) {
            Session::setFlash('error', 'Name and email are required.');
            $this->redirect('employees');
        }

        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            DB::query("UPDATE `users` SET `name` = ?, `email` = ?, `phone` = ?, `role` = ?, `department` = ?, `status` = ?, `password_hash` = ? WHERE `id` = ?", [
                $name, $email, $this->input('phone'), $role, $this->input('department'), $status, $hash, $id
            ]);
        } else {
            DB::query("UPDATE `users` SET `name` = ?, `email` = ?, `phone` = ?, `role` = ?, `department` = ?, `status` = ? WHERE `id` = ?", [
                $name, $email, $this->input('phone'), $role, $this->input('department'), $status, $id
            ]);
        }

        AuditLogger::log('UPDATE', 'Users', $id, "Updated employee {$name}");
        Session::setFlash('success', "User '{$name}' updated successfully.");
        $this->redirect('employees');
    }
}
