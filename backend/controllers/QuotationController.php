<?php
/**
 * Sagar Advertising CRM - Quotation Controller
 */

declare(strict_types=1);

require_once ROOT_PATH . '/backend/core/Controller.php';
require_once ROOT_PATH . '/backend/services/QuotationCalculator.php';
require_once ROOT_PATH . '/backend/services/PdfGenerator.php';
require_once ROOT_PATH . '/backend/services/MailerService.php';

class QuotationController extends Controller {
    public function index(): void {
        $this->requirePermission('quotations.view');

        $status = trim((string)$this->input('status', ''));
        $search = trim((string)$this->input('search', ''));
        $customer = trim((string)$this->input('customer', ''));
        $dateFrom = trim((string)$this->input('from', ''));
        $dateTo = trim((string)$this->input('to', ''));

        $conditions = ["1=1"];
        $params = [];

        if ($status !== '') {
            $conditions[] = "q.status = ?";
            $params[] = $status;
        }
        if ($search !== '') {
            $conditions[] = "(q.quotation_number LIKE ? OR q.project_name LIKE ? OR c.company_name LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term]);
        }
        if ($customer !== '') {
            $conditions[] = "q.customer_id = ?";
            $params[] = (int)$customer;
        }
        if ($dateFrom !== '') {
            $conditions[] = "q.quotation_date >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $conditions[] = "q.quotation_date <= ?";
            $params[] = $dateTo;
        }

        $whereClause = implode(' AND ', $conditions);

        $quotations = DB::fetchAll(
            "SELECT q.*, c.company_name, c.contact_person, c.mobile, c.email, u.name as sales_person_name
             FROM `quotations` q
             JOIN `customers` c ON q.customer_id = c.id
             LEFT JOIN `users` u ON q.sales_person_id = u.id
             WHERE {$whereClause}
             ORDER BY q.created_at DESC",
            $params
        );

        $customers = DB::fetchAll("SELECT id, company_name FROM `customers` WHERE deleted_at IS NULL ORDER BY company_name ASC");

        $this->view('quotations/index', [
            'quotations' => $quotations,
            'customers'  => $customers,
            'filters'    => [
                'status'   => $status,
                'search'   => $search,
                'customer' => $customer,
                'from'     => $dateFrom,
                'to'       => $dateTo
            ]
        ]);
    }

    public function builder(): void {
        $this->requirePermission('quotations.create');
        $id = (int)$this->input('id');

        $quote = null;
        $items = [];
        if ($id > 0) {
            $quote = DB::fetch("SELECT * FROM `quotations` WHERE id = ?", [$id]);
            if ($quote) {
                $items = DB::fetchAll("SELECT * FROM `quotation_items` WHERE quotation_id = ? ORDER BY sort_order ASC", [$id]);
            }
        }

        $customers = DB::fetchAll("SELECT * FROM `customers` WHERE deleted_at IS NULL ORDER BY company_name ASC");
        $services = DB::fetchAll("SELECT * FROM `services` WHERE status = 'active' ORDER BY name ASC");
        $templates = DB::fetchAll("SELECT * FROM `quotation_templates` ORDER BY name ASC");

        // Next quotation number preview
        $prefix = (string)DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'quotation_prefix'") ?: 'SA/QTN/';
        $nextNum = (int)DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'quotation_next_number'") ?: 1001;
        $year = date('Y');
        $previewQuoteNumber = $quote ? $quote['quotation_number'] : sprintf("%s%s/%04d", $prefix, $year, $nextNum);

        $this->view('quotations/builder', [
            'quote'              => $quote,
            'items'              => $items,
            'customers'          => $customers,
            'services'           => $services,
            'templates'          => $templates,
            'previewQuoteNumber' => $previewQuoteNumber
        ]);
    }

    public function store(): void {
        $this->requirePermission('quotations.create');

        $data = $this->allInputs();
        $customerId = (int)($data['customer_id'] ?? 0);
        $items = $data['items'] ?? [];

        if (!$customerId || empty($items)) {
            $this->json(['success' => false, 'error' => 'Please select a customer and add at least one line item.'], 422);
        }

        $discount = (float)($data['discount_amount'] ?? 0);
        $transport = (float)($data['transportation_charges'] ?? 0);
        $install = (float)($data['installation_charges'] ?? 0);
        $gstRate = (float)($data['gst_rate'] ?? 18);
        $mode = in_array($data['commission_mode'] ?? 'markup', ['markup', 'margin']) ? $data['commission_mode'] : 'markup';

        // Precise server-side calculation
        $calc = QuotationCalculator::calculateQuotationTotals($items, $discount, $gstRate, $transport, $install, $mode);

        $pdo = DB::connect();
        $pdo->beginTransaction();

        try {
            // Generate official quotation number
            $prefix = (string)DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'quotation_prefix'") ?: 'SA/QTN/';
            $counter = (int)DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'quotation_next_number'") ?: 1001;
            $year = date('Y');
            $quotationNumber = sprintf("%s%s/%04d", $prefix, $year, $counter);

            // Increment counter
            DB::query("UPDATE `settings` SET setting_value = ? WHERE setting_key = 'quotation_next_number'", [$counter + 1]);

            $viewToken = bin2hex(random_bytes(24));
            $qDate = !empty($data['quotation_date']) ? $data['quotation_date'] : date('Y-m-d');
            $vDate = !empty($data['valid_until']) ? $data['valid_until'] : date('Y-m-d', strtotime('+15 days'));

            $stmt = $pdo->prepare("INSERT INTO `quotations` (
                `quotation_number`, `quotation_date`, `valid_until`, `customer_id`, `sales_person_id`,
                `project_name`, `reference`, `notes`, `terms_and_conditions`, `payment_terms`, `delivery_time`,
                `commission_mode`, `subtotal`, `total_commission`, `discount_amount`, `taxable_amount`,
                `gst_rate`, `gst_amount`, `transportation_charges`, `installation_charges`, `round_off`,
                `grand_total`, `status`, `view_token`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Draft', ?)");

            $stmt->execute([
                $quotationNumber,
                $qDate,
                $vDate,
                $customerId,
                Session::userId(),
                $data['project_name'] ?? 'Advertising & Branding Work',
                $data['reference'] ?? '',
                $data['notes'] ?? '',
                $data['terms_and_conditions'] ?? '',
                $data['payment_terms'] ?? '50% Advance, 50% upon installation',
                $data['delivery_time'] ?? '3 to 7 working days',
                $mode,
                $calc['subtotal'],
                $calc['total_commission'],
                $calc['discount_amount'],
                $calc['taxable_amount'],
                $calc['gst_rate'],
                $calc['gst_amount'],
                $calc['transportation_charges'],
                $calc['installation_charges'],
                $calc['round_off'],
                $calc['grand_total'],
                $viewToken
            ]);

            $quotationId = (int)$pdo->lastInsertId();

            // Insert line items
            $itemStmt = $pdo->prepare("INSERT INTO `quotation_items` (
                `quotation_id`, `service_id`, `item_name`, `description`, `size_dimension`,
                `quantity`, `unit`, `actual_price`, `commission_rate`, `commission_amount`,
                `selling_price`, `total_amount`, `sort_order`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($calc['items'] as $item) {
                $itemStmt->execute([
                    $quotationId,
                    !empty($item['service_id']) ? (int)$item['service_id'] : null,
                    $item['item_name'] ?: 'Advertising Service',
                    $item['description'] ?? '',
                    $item['size_dimension'] ?? '',
                    $item['quantity'],
                    $item['unit'] ?? 'Sq Ft',
                    $item['actual_price'],
                    $item['commission_rate'],
                    $item['commission_amount'],
                    $item['selling_price'],
                    $item['total_amount'],
                    $item['sort_order'] ?? 0
                ]);
            }

            // Record status history
            $pdo->prepare("INSERT INTO `quotation_status_history` (`quotation_id`, `user_id`, `old_status`, `new_status`, `comments`) VALUES (?, ?, NULL, 'Draft', 'Quotation drafted and saved.')")
                ->execute([$quotationId, Session::userId()]);

            $pdo->commit();

            AuditLogger::log('CREATE', 'Quotations', $quotationId, "Created quotation {$quotationNumber} (₹{$calc['grand_total']})");

            $this->json([
                'success'      => true,
                'message'      => "Quotation {$quotationNumber} generated successfully!",
                'quotation_id' => $quotationId,
                'redirect_url' => url('quotations/view?id=' . $quotationId)
            ]);

        } catch (Exception $e) {
            $pdo->rollBack();
            $this->json(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    public function viewDocument(): void {
        $rawId = trim((string)$this->input('id'));
        if (is_numeric($rawId) && (int)$rawId > 0) {
            $quote = DB::fetch(
                "SELECT q.*, c.company_name, c.contact_person, c.mobile, c.email, c.address, c.city, c.state, c.pincode, c.gstin, u.name as sales_person_name
                 FROM `quotations` q
                 JOIN `customers` c ON q.customer_id = c.id
                 LEFT JOIN `users` u ON q.sales_person_id = u.id
                 WHERE q.id = ?",
                [(int)$rawId]
            );
        } else {
            $quote = DB::fetch(
                "SELECT q.*, c.company_name, c.contact_person, c.mobile, c.email, c.address, c.city, c.state, c.pincode, c.gstin, u.name as sales_person_name
                 FROM `quotations` q
                 JOIN `customers` c ON q.customer_id = c.id
                 LEFT JOIN `users` u ON q.sales_person_id = u.id
                 WHERE q.quotation_number = ?",
                [$rawId]
            );
        }

        if (!$quote) {
            Session::setFlash('error', 'Quotation not found.');
            $this->redirect('quotations');
        }

        $id = (int)$quote['id'];
        $items = DB::fetchAll("SELECT * FROM `quotation_items` WHERE quotation_id = ? ORDER BY sort_order ASC", [$id]);
        $customer = DB::fetch("SELECT * FROM `customers` WHERE id = ?", [$quote['customer_id']]);
        
        $companySettings = DB::fetchAll("SELECT setting_key, setting_value FROM `settings` WHERE category = 'company'");
        $company = [];
        foreach ($companySettings as $cs) {
            $company[$cs['setting_key']] = $cs['setting_value'];
        }

        // Render printable view
        echo PdfGenerator::generateHtml($quote, $items, $customer, $company);
        exit;
    }

    public function sendEmail(): void {
        $this->requirePermission('quotations.send');
        $this->validateCsrf();

        $quotationId = (int)$this->input('quotation_id');
        $toEmail = trim((string)$this->input('to_email'));
        $subject = trim((string)$this->input('subject'));
        $message = trim((string)$this->input('message'));

        if (empty($toEmail) || empty($subject)) {
            Session::setFlash('error', 'Recipient email and subject are required.');
            $this->redirect('quotations');
        }

        try {
            $res = MailerService::sendQuotationEmail($quotationId, $toEmail, $subject, $message);
            if ($res['success']) {
                Session::setFlash('success', "Quotation sent successfully to {$toEmail} with attached proposal.");
            } else {
                Session::setFlash('error', "Failed to dispatch email: {$res['error']}");
            }
        } catch (Exception $e) {
            Session::setFlash('error', "Error sending quotation: " . $e->getMessage());
        }

        $this->redirect('quotations');
    }

    public function duplicate(): void {
        $this->requirePermission('quotations.create');
        $this->validateCsrf();
        $id = (int)$this->input('id');

        $orig = DB::fetch("SELECT * FROM `quotations` WHERE id = ?", [$id]);
        if (!$orig) {
            Session::setFlash('error', 'Quotation not found.');
            $this->redirect('quotations');
        }

        // Auto new quotation number
        $prefix = (string)DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'quotation_prefix'") ?: 'SA/QTN/';
        $counter = (int)DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'quotation_next_number'") ?: 1001;
        $newNumber = sprintf("%s%s/%04d", $prefix, date('Y'), $counter);
        DB::query("UPDATE `settings` SET setting_value = ? WHERE setting_key = 'quotation_next_number'", [$counter + 1]);

        $pdo = DB::connect();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("INSERT INTO `quotations` (
                `quotation_number`, `quotation_date`, `valid_until`, `customer_id`, `sales_person_id`,
                `project_name`, `reference`, `notes`, `terms_and_conditions`, `payment_terms`, `delivery_time`,
                `commission_mode`, `subtotal`, `total_commission`, `discount_amount`, `taxable_amount`,
                `gst_rate`, `gst_amount`, `transportation_charges`, `installation_charges`, `round_off`,
                `grand_total`, `status`, `view_token`
            ) VALUES (?, CURRENT_DATE(), DATE_ADD(CURRENT_DATE(), INTERVAL 15 DAY), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Draft', ?)");

            $viewToken = bin2hex(random_bytes(24));

            $stmt->execute([
                $newNumber,
                $orig['customer_id'],
                Session::userId(),
                $orig['project_name'] . ' (Copy)',
                $orig['reference'],
                $orig['notes'],
                $orig['terms_and_conditions'],
                $orig['payment_terms'],
                $orig['delivery_time'],
                $orig['commission_mode'],
                $orig['subtotal'],
                $orig['total_commission'],
                $orig['discount_amount'],
                $orig['taxable_amount'],
                $orig['gst_rate'],
                $orig['gst_amount'],
                $orig['transportation_charges'],
                $orig['installation_charges'],
                $orig['round_off'],
                $orig['grand_total'],
                $viewToken
            ]);

            $newId = (int)$pdo->lastInsertId();

            // Clone items
            $items = DB::fetchAll("SELECT * FROM `quotation_items` WHERE quotation_id = ?", [$id]);
            $itemStmt = $pdo->prepare("INSERT INTO `quotation_items` (
                `quotation_id`, `service_id`, `item_name`, `description`, `size_dimension`,
                `quantity`, `unit`, `actual_price`, `commission_rate`, `commission_amount`,
                `selling_price`, `total_amount`, `sort_order`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($items as $it) {
                $itemStmt->execute([
                    $newId, $it['service_id'], $it['item_name'], $it['description'], $it['size_dimension'],
                    $it['quantity'], $it['unit'], $it['actual_price'], $it['commission_rate'], $it['commission_amount'],
                    $it['selling_price'], $it['total_amount'], $it['sort_order']
                ]);
            }

            $pdo->commit();
            AuditLogger::log('DUPLICATE', 'Quotations', $newId, "Duplicated quotation {$orig['quotation_number']} to new {$newNumber}");
            Session::setFlash('success', "Quotation duplicated successfully as {$newNumber} (Draft).");
            $this->redirect('quotations/builder?id=' . $newId);

        } catch (Exception $e) {
            $pdo->rollBack();
            Session::setFlash('error', "Failed to duplicate: " . $e->getMessage());
            $this->redirect('quotations');
        }
    }

    public function changeStatus(): void {
        $this->requireAuth();
        $this->validateCsrf();

        $id = (int)$this->input('id');
        $newStatus = trim((string)$this->input('status'));
        $comments = trim((string)$this->input('comments'));

        $quote = DB::fetch("SELECT status, quotation_number FROM `quotations` WHERE id = ?", [$id]);
        if ($quote && !empty($newStatus)) {
            DB::query("UPDATE `quotations` SET `status` = ? WHERE `id` = ?", [$newStatus, $id]);
            DB::query("INSERT INTO `quotation_status_history` (`quotation_id`, `user_id`, `old_status`, `new_status`, `comments`) VALUES (?, ?, ?, ?, ?)", [$id, Session::userId(), $quote['status'], $newStatus, $comments]);

            AuditLogger::log('STATUS_CHANGE', 'Quotations', $id, "Changed status of {$quote['quotation_number']} from {$quote['status']} to {$newStatus}.");
            Session::setFlash('success', "Status updated to '{$newStatus}'.");
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? 'quotations');
    }

    public function delete(): void {
        $this->requirePermission('quotations.delete');
        $this->validateCsrf();

        $id = (int)$this->input('id');
        $quote = DB::fetch("SELECT quotation_number FROM `quotations` WHERE id = ?", [$id]);

        DB::query("DELETE FROM `quotations` WHERE `id` = ?", [$id]);
        AuditLogger::log('DELETE', 'Quotations', $id, "Deleted quotation " . ($quote['quotation_number'] ?? $id));

        Session::setFlash('success', 'Quotation deleted successfully.');
        $this->redirect('quotations');
    }
}
