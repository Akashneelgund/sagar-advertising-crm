<?php
/**
 * Sagar Advertising CRM - Customer Management Controller
 */

declare(strict_types=1);

require_once ROOT_PATH . '/backend/core/Controller.php';

class CustomerController extends Controller {
    public function index(): void {
        $this->requirePermission('customers.view');

        $search = trim((string)$this->input('search', ''));
        $city = trim((string)$this->input('city', ''));
        $status = trim((string)$this->input('status', ''));
        $type = trim((string)$this->input('type', ''));
        $assigned = trim((string)$this->input('assigned', ''));

        $conditions = ["c.deleted_at IS NULL"];
        $params = [];

        if ($search !== '') {
            $conditions[] = "(c.company_name LIKE ? OR c.contact_person LIKE ? OR c.mobile LIKE ? OR c.email LIKE ? OR c.customer_code LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term, $term]);
        }
        if ($city !== '') {
            $conditions[] = "c.city = ?";
            $params[] = $city;
        }
        if ($status !== '') {
            $conditions[] = "c.status = ?";
            $params[] = $status;
        }
        if ($type !== '') {
            $conditions[] = "c.customer_type = ?";
            $params[] = $type;
        }
        if ($assigned !== '') {
            $conditions[] = "c.assigned_employee_id = ?";
            $params[] = (int)$assigned;
        }

        $whereClause = implode(' AND ', $conditions);

        // Fetch distinct cities for filter dropdown
        $cities = DB::fetchAll("SELECT DISTINCT city FROM `customers` WHERE deleted_at IS NULL AND city IS NOT NULL AND city != '' ORDER BY city ASC");
        $employees = DB::fetchAll("SELECT id, name FROM `users` WHERE status = 'active' ORDER BY name ASC");

        $customers = DB::fetchAll(
            "SELECT c.*, u.name as assigned_employee_name,
                    (SELECT COUNT(*) FROM `quotations` q WHERE q.customer_id = c.id) as quote_count,
                    (SELECT COALESCE(SUM(grand_total), 0) FROM `quotations` q WHERE q.customer_id = c.id) as total_quoted
             FROM `customers` c
             LEFT JOIN `users` u ON c.assigned_employee_id = u.id
             WHERE {$whereClause}
             ORDER BY c.created_at DESC",
            $params
        );

        $this->view('customers/index', [
            'customers' => $customers,
            'cities'    => array_column($cities, 'city'),
            'employees' => $employees,
            'filters'   => [
                'search'   => $search,
                'city'     => $city,
                'status'   => $status,
                'type'     => $type,
                'assigned' => $assigned
            ]
        ]);
    }

    public function detail(): void {
        $this->requirePermission('customers.view');
        $id = (int)$this->input('id');

        $customer = DB::fetch(
            "SELECT c.*, u.name as assigned_employee_name
             FROM `customers` c
             LEFT JOIN `users` u ON c.assigned_employee_id = u.id
             WHERE c.id = ? AND c.deleted_at IS NULL",
            [$id]
        );

        if (!$customer) {
            Session::setFlash('error', 'Customer not found.');
            $this->redirect('customers');
        }

        // Quotations history
        $quotations = DB::fetchAll(
            "SELECT * FROM `quotations` WHERE customer_id = ? ORDER BY quotation_date DESC",
            [$id]
        );

        // Calculate financials
        $totalQuoted = 0.0;
        $acceptedVal = 0.0;
        $pendingVal = 0.0;
        foreach ($quotations as $q) {
            $gt = (float)$q['grand_total'];
            $totalQuoted += $gt;
            if (in_array($q['status'], ['Approved', 'Converted'])) {
                $acceptedVal += $gt;
            } elseif (in_array($q['status'], ['Draft', 'Sent', 'Under Discussion'])) {
                $pendingVal += $gt;
            }
        }

        // Follow-ups
        $followups = DB::fetchAll(
            "SELECT f.*, u.name as user_name
             FROM `customer_followups` f
             LEFT JOIN `users` u ON f.user_id = u.id
             WHERE f.customer_id = ?
             ORDER BY f.followup_date DESC",
            [$id]
        );

        // Notes
        $notes = DB::fetchAll(
            "SELECT n.*, u.name as user_name
             FROM `customer_notes` n
             LEFT JOIN `users` u ON n.user_id = u.id
             WHERE n.customer_id = ?
             ORDER BY n.created_at DESC",
            [$id]
        );

        // Email logs
        $emails = DB::fetchAll(
            "SELECT el.*, ec.name as campaign_name
             FROM `email_logs` el
             LEFT JOIN `email_campaigns` ec ON el.campaign_id = ec.id
             WHERE el.recipient_email = ?
             ORDER BY el.created_at DESC",
            [$customer['email']]
        );

        $employees = DB::fetchAll("SELECT id, name FROM `users` WHERE status = 'active' ORDER BY name ASC");

        $this->view('customers/detail', [
            'customer'    => $customer,
            'quotations'  => $quotations,
            'totalQuoted' => $totalQuoted,
            'acceptedVal' => $acceptedVal,
            'pendingVal'  => $pendingVal,
            'followups'   => $followups,
            'notes'       => $notes,
            'emails'      => $emails,
            'employees'   => $employees
        ]);
    }

    public function create(): void {
        $this->requirePermission('customers.create');
        $employees = DB::fetchAll("SELECT id, name FROM `users` WHERE status = 'active' ORDER BY name ASC");
        $this->view('customers/create_edit', ['customer' => null, 'employees' => $employees]);
    }

    public function store(): void {
        $this->requirePermission('customers.create');
        $this->validateCsrf();

        $company = trim((string)$this->input('company_name'));
        $person = trim((string)$this->input('contact_person'));
        $mobile = trim((string)$this->input('mobile'));

        if (empty($company) || empty($mobile)) {
            Session::setFlash('error', 'Company name and Mobile number are required.');
            $this->redirect('customers/create');
        }

        // Auto customer code
        $nextId = (int)DB::fetchColumn("SELECT COUNT(*) FROM `customers`") + 1001;
        $customerCode = 'SA-CUST-' . $nextId;

        $data = [
            'customer_code'        => $customerCode,
            'company_name'         => $company,
            'contact_person'       => $person ?: $company,
            'mobile'               => $mobile,
            'alternate_mobile'     => trim((string)$this->input('alternate_mobile')),
            'email'                => trim((string)$this->input('email')),
            'whatsapp'             => trim((string)$this->input('whatsapp')) ?: $mobile,
            'address'              => trim((string)$this->input('address')),
            'city'                 => trim((string)$this->input('city')) ?: 'Hubballi',
            'state'                => trim((string)$this->input('state')) ?: 'Karnataka',
            'pincode'              => trim((string)$this->input('pincode')) ?: '580024',
            'gstin'                => strtoupper(trim((string)$this->input('gstin'))),
            'customer_type'        => $this->input('customer_type', 'Business'),
            'source'               => $this->input('source', 'Direct Visit'),
            'notes'                => trim((string)$this->input('notes')),
            'assigned_employee_id' => $this->input('assigned_employee_id') ?: null,
            'status'               => $this->input('status', 'Active'),
            'marketing_opt_in'     => $this->input('marketing_opt_in') ? 1 : 0
        ];

        $fields = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $fieldStr = implode('`, `', $fields);

        DB::query("INSERT INTO `customers` (`{$fieldStr}`) VALUES ({$placeholders})", array_values($data));
        $newId = (int)DB::lastInsertId();

        AuditLogger::log('CREATE', 'Customers', $newId, "Added new customer {$company} ({$customerCode}).");
        Session::setFlash('success', "Customer '{$company}' added successfully!");
        $this->redirect('customers/detail?id=' . $newId);
    }

    public function edit(): void {
        $this->requirePermission('customers.edit');
        $id = (int)$this->input('id');
        $customer = DB::fetch("SELECT * FROM `customers` WHERE `id` = ? AND deleted_at IS NULL", [$id]);
        if (!$customer) {
            Session::setFlash('error', 'Customer not found.');
            $this->redirect('customers');
        }

        $employees = DB::fetchAll("SELECT id, name FROM `users` WHERE status = 'active' ORDER BY name ASC");
        $this->view('customers/create_edit', ['customer' => $customer, 'employees' => $employees]);
    }

    public function update(): void {
        $this->requirePermission('customers.edit');
        $this->validateCsrf();

        $id = (int)$this->input('id');
        $company = trim((string)$this->input('company_name'));
        $mobile = trim((string)$this->input('mobile'));

        if (empty($company) || empty($mobile)) {
            Session::setFlash('error', 'Company name and Mobile number are required.');
            $this->redirect('customers/edit?id=' . $id);
        }

        DB::query(
            "UPDATE `customers` SET
                `company_name` = ?, `contact_person` = ?, `mobile` = ?, `alternate_mobile` = ?,
                `email` = ?, `whatsapp` = ?, `address` = ?, `city` = ?, `state` = ?,
                `pincode` = ?, `gstin` = ?, `customer_type` = ?, `source` = ?,
                `notes` = ?, `assigned_employee_id` = ?, `status` = ?, `marketing_opt_in` = ?
             WHERE `id` = ?",
            [
                $company,
                trim((string)$this->input('contact_person')) ?: $company,
                $mobile,
                trim((string)$this->input('alternate_mobile')),
                trim((string)$this->input('email')),
                trim((string)$this->input('whatsapp')),
                trim((string)$this->input('address')),
                trim((string)$this->input('city')),
                trim((string)$this->input('state')),
                trim((string)$this->input('pincode')),
                strtoupper(trim((string)$this->input('gstin'))),
                $this->input('customer_type', 'Business'),
                $this->input('source', 'Direct Visit'),
                trim((string)$this->input('notes')),
                $this->input('assigned_employee_id') ?: null,
                $this->input('status', 'Active'),
                $this->input('marketing_opt_in') ? 1 : 0,
                $id
            ]
        );

        AuditLogger::log('UPDATE', 'Customers', $id, "Updated details for customer {$company}.");
        Session::setFlash('success', "Customer details updated successfully!");
        $this->redirect('customers/detail?id=' . $id);
    }

    public function delete(): void {
        $this->requirePermission('customers.delete');
        $this->validateCsrf();

        $id = (int)$this->input('id');
        DB::query("UPDATE `customers` SET `deleted_at` = NOW() WHERE `id` = ?", [$id]);

        AuditLogger::log('DELETE', 'Customers', $id, "Soft deleted customer ID {$id}.");
        Session::setFlash('success', 'Customer record deleted successfully.');
        $this->redirect('customers');
    }

    public function addNote(): void {
        $this->requireAuth();
        $this->validateCsrf();

        $customerId = (int)$this->input('customer_id');
        $note = trim((string)$this->input('note'));

        if (!empty($note)) {
            DB::query(
                "INSERT INTO `customer_notes` (`customer_id`, `user_id`, `note`) VALUES (?, ?, ?)",
                [$customerId, Session::userId(), $note]
            );
            Session::setFlash('success', 'Note added successfully.');
        }

        $this->redirect('customers/detail?id=' . $customerId);
    }

    public function addFollowup(): void {
        $this->requireAuth();
        $this->validateCsrf();

        $customerId = (int)$this->input('customer_id');
        $date = trim((string)$this->input('followup_date'));
        $time = trim((string)$this->input('followup_time')) ?: null;
        $type = $this->input('followup_type', 'Call');
        $notes = trim((string)$this->input('notes'));

        if (!empty($date)) {
            DB::query(
                "INSERT INTO `customer_followups` (`customer_id`, `user_id`, `followup_date`, `followup_time`, `followup_type`, `notes`, `status`)
                 VALUES (?, ?, ?, ?, ?, ?, 'pending')",
                [$customerId, Session::userId(), $date, $time, $type, $notes]
            );
            Session::setFlash('success', 'Follow-up scheduled successfully.');
        }

        $this->redirect('customers/detail?id=' . $customerId);
    }
}
