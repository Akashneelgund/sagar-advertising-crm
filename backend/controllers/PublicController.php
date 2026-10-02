<?php
/**
 * Sagar Advertising CRM - Public Quotation Viewer & Tracker
 */

declare(strict_types=1);

require_once ROOT_PATH . '/backend/core/Controller.php';
require_once ROOT_PATH . '/backend/services/PdfGenerator.php';

class PublicController extends Controller {
    /**
     * Public quotation link viewed by client (tracks viewed_at timestamp)
     */
    public function viewQuotation(): void {
        $token = trim((string)$this->input('token'));
        if (empty($token)) {
            die("Invalid quotation link.");
        }

        $quote = DB::fetch(
            "SELECT q.*, c.company_name, c.contact_person, c.mobile, c.email, c.address, c.city, c.state, c.pincode, c.gstin, u.name as sales_person_name
             FROM `quotations` q
             JOIN `customers` c ON q.customer_id = c.id
             LEFT JOIN `users` u ON q.sales_person_id = u.id
             WHERE q.view_token = ? OR q.quotation_number = ?",
            [$token, $token]
        );

        if (!$quote) {
            die("Quotation not found or link has expired.");
        }

        // Track when customer opens quotation link
        if (empty($quote['viewed_at']) || $quote['status'] === 'Sent') {
            DB::query("UPDATE `quotations` SET `viewed_at` = NOW(), `status` = CASE WHEN `status` = 'Sent' THEN 'Viewed' ELSE `status` END WHERE `id` = ?", [$quote['id']]);
            DB::query(
                "INSERT INTO `quotation_status_history` (`quotation_id`, `old_status`, `new_status`, `comments`) VALUES (?, ?, 'Viewed', 'Customer viewed digital quotation online.')",
                [$quote['id'], $quote['status']]
            );
            AuditLogger::log('VIEWED', 'Quotations', $quote['id'], "Client opened online quotation {$quote['quotation_number']}");
        }

        $items = DB::fetchAll("SELECT * FROM `quotation_items` WHERE quotation_id = ? ORDER BY sort_order ASC", [$quote['id']]);
        $customer = DB::fetch("SELECT * FROM `customers` WHERE id = ?", [$quote['customer_id']]);

        $companySettings = DB::fetchAll("SELECT setting_key, setting_value FROM `settings` WHERE category = 'company'");
        $company = [];
        foreach ($companySettings as $cs) {
            $company[$cs['setting_key']] = $cs['setting_value'];
        }

        echo PdfGenerator::generateHtml($quote, $items, $customer, $company);
        exit;
    }
}
