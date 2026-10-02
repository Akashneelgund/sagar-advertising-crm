<?php
/**
 * Sagar Advertising CRM - Service & Product Master Controller
 */

declare(strict_types=1);

require_once ROOT_PATH . '/backend/core/Controller.php';

class ServiceController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $categories = DB::fetchAll("SELECT * FROM `service_categories` ORDER BY name ASC");
        $services = DB::fetchAll(
            "SELECT s.*, c.name as category_name
             FROM `services` s
             LEFT JOIN `service_categories` c ON s.category_id = c.id
             ORDER BY s.created_at ASC"
        );

        $this->view('services/index', [
            'services'   => $services,
            'categories' => $categories
        ]);
    }

    public function store(): void {
        $this->requirePermission('services.manage');
        $this->validateCsrf();

        $name = trim((string)$this->input('name'));
        if (empty($name)) {
            Session::setFlash('error', 'Service name is required.');
            $this->redirect('services');
        }

        DB::query(
            "INSERT INTO `services` (`category_id`, `name`, `description`, `default_unit`, `default_price`, `default_gst_rate`, `default_commission_rate`, `status`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $this->input('category_id') ?: null,
                $name,
                trim((string)$this->input('description')),
                $this->input('default_unit', 'Sq Ft'),
                (float)$this->input('default_price', 0),
                (float)$this->input('default_gst_rate', 18),
                (float)$this->input('default_commission_rate', 15),
                $this->input('status', 'active')
            ]
        );

        $id = (int)DB::lastInsertId();
        AuditLogger::log('CREATE', 'Services', $id, "Added service '{$name}'");
        Session::setFlash('success', "Service '{$name}' added to catalog.");
        $this->redirect('services');
    }

    public function update(): void {
        $this->requirePermission('services.manage');
        $this->validateCsrf();

        $id = (int)$this->input('id');
        $name = trim((string)$this->input('name'));

        if (empty($name)) {
            Session::setFlash('error', 'Service name is required.');
            $this->redirect('services');
        }

        DB::query(
            "UPDATE `services` SET
                `category_id` = ?, `name` = ?, `description` = ?, `default_unit` = ?,
                `default_price` = ?, `default_gst_rate` = ?, `default_commission_rate` = ?, `status` = ?
             WHERE `id` = ?",
            [
                $this->input('category_id') ?: null,
                $name,
                trim((string)$this->input('description')),
                $this->input('default_unit', 'Sq Ft'),
                (float)$this->input('default_price', 0),
                (float)$this->input('default_gst_rate', 18),
                (float)$this->input('default_commission_rate', 15),
                $this->input('status', 'active'),
                $id
            ]
        );

        AuditLogger::log('UPDATE', 'Services', $id, "Updated service '{$name}'");
        Session::setFlash('success', "Service '{$name}' updated successfully.");
        $this->redirect('services');
    }

    public function delete(): void {
        $this->requirePermission('services.manage');
        $this->validateCsrf();

        $id = (int)$this->input('id');
        DB::query("DELETE FROM `services` WHERE `id` = ?", [$id]);

        AuditLogger::log('DELETE', 'Services', $id, "Deleted service ID {$id}");
        Session::setFlash('success', 'Service deleted from catalog.');
        $this->redirect('services');
    }
}
