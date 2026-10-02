<?php
/**
 * Sagar Advertising CRM - Internal REST API Endpoints
 */

declare(strict_types=1);

require_once ROOT_PATH . '/backend/core/Controller.php';

class ApiController extends Controller {
    /**
     * Fast Global Search for Customers, Quotations & Services
     */
    public function search(): void {
        $this->requireAuth();
        $q = trim((string)$this->input('q', ''));
        if (strlen($q) < 2) {
            $this->json([]);
        }

        $results = [];
        $term = "%{$q}%";

        // 1. Search Customers
        $customers = DB::fetchAll(
            "SELECT id, customer_code, company_name, contact_person, mobile, city
             FROM `customers`
             WHERE deleted_at IS NULL AND (company_name LIKE ? OR contact_person LIKE ? OR mobile LIKE ? OR customer_code LIKE ?)
             LIMIT 5",
            [$term, $term, $term, $term]
        );
        foreach ($customers as $c) {
            $results[] = [
                'type'        => 'Customer',
                'title'       => $c['company_name'],
                'subtitle'    => "{$c['contact_person']} &bull; {$c['mobile']} &bull; {$c['city']}",
                'url'         => url('customers/detail?id=' . $c['id']),
                'badge_class' => 'bg-info text-dark'
            ];
        }

        // 2. Search Quotations
        $quotes = DB::fetchAll(
            "SELECT q.id, q.quotation_number, q.project_name, q.grand_total, q.status, c.company_name
             FROM `quotations` q
             JOIN `customers` c ON q.customer_id = c.id
             WHERE q.quotation_number LIKE ? OR q.project_name LIKE ? OR c.company_name LIKE ?
             LIMIT 5",
            [$term, $term, $term]
        );
        foreach ($quotes as $qu) {
            $results[] = [
                'type'        => 'Quotation',
                'title'       => "{$qu['quotation_number']} – " . ($qu['project_name'] ?: $qu['company_name']),
                'subtitle'    => "₹" . number_format((float)$qu['grand_total'], 2) . " &bull; Status: {$qu['status']}",
                'url'         => url('quotations/view?id=' . $qu['id']),
                'badge_class' => 'bg-warning text-dark'
            ];
        }

        // 3. Search Services
        $services = DB::fetchAll(
            "SELECT id, name, default_price, default_unit
             FROM `services`
             WHERE status = 'active' AND name LIKE ?
             LIMIT 3",
            [$term]
        );
        foreach ($services as $s) {
            $results[] = [
                'type'        => 'Service',
                'title'       => $s['name'],
                'subtitle'    => "Rate: ₹{$s['default_price']} / {$s['default_unit']}",
                'url'         => url('services'),
                'badge_class' => 'bg-dark text-white'
            ];
        }

        $this->json($results);
    }

    /**
     * Toggle Follow-up completion status
     */
    public function toggleFollowup(): void {
        $this->requireAuth();
        $id = (int)$this->input('id');
        $status = $this->input('status', 'completed');

        DB::query("UPDATE `customer_followups` SET `status` = ? WHERE `id` = ?", [$status, $id]);
        $this->json(['success' => true]);
    }
}
