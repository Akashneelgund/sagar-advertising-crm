<?php
/**
 * Sagar Advertising CRM - Business Reports & Profit/Commission Analytics
 */

declare(strict_types=1);

require_once ROOT_PATH . '/backend/core/Controller.php';

class ReportController extends Controller {
    public function index(): void {
        $this->requirePermission('reports.view');

        $type = $this->input('type', 'sales');
        $dateFrom = $this->input('from', date('Y-01-01'));
        $dateTo = $this->input('to', date('Y-m-d'));

        // Sales Report Data
        $salesData = DB::fetchAll(
            "SELECT q.id, q.quotation_number, q.quotation_date, c.company_name, q.project_name,
                    q.subtotal, q.total_commission, q.grand_total, q.status, u.name as sales_person
             FROM `quotations` q
             JOIN `customers` c ON q.customer_id = c.id
             LEFT JOIN `users` u ON q.sales_person_id = u.id
             WHERE q.quotation_date BETWEEN ? AND ?
             ORDER BY q.quotation_date DESC",
            [$dateFrom, $dateTo]
        );

        $this->view('reports/index', [
            'type'      => $type,
            'dateFrom'  => $dateFrom,
            'dateTo'    => $dateTo,
            'salesData' => $salesData
        ]);
    }

    /**
     * Specialized Quotation Profit & Commission Analytics Dashboard
     */
    public function commission(): void {
        $this->requirePermission('reports.view');

        $dateFrom = $this->input('from', date('Y-01-01'));
        $dateTo = $this->input('to', date('Y-m-d'));

        // Aggregates
        $totals = DB::fetch(
            "SELECT
                COALESCE(SUM(qi.actual_price * qi.quantity), 0) as total_actual_cost,
                COALESCE(SUM(qi.commission_amount * qi.quantity), 0) as total_commission,
                COALESCE(SUM(qi.total_amount), 0) as total_selling_value
             FROM `quotation_items` qi
             JOIN `quotations` q ON qi.quotation_id = q.id
             WHERE q.quotation_date BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        );

        $actualCost = (float)$totals['total_actual_cost'];
        $commission = (float)$totals['total_commission'];
        $sellingVal = (float)$totals['total_selling_value'];
        $avgCommissionPct = ($actualCost > 0) ? round(($commission / $actualCost) * 100, 2) : 0.0;
        $overallMarginPct = ($sellingVal > 0) ? round(($commission / $sellingVal) * 100, 2) : 0.0;

        // Commission by Service
        $byService = DB::fetchAll(
            "SELECT qi.item_name,
                    SUM(qi.actual_price * qi.quantity) as service_actual_cost,
                    SUM(qi.commission_amount * qi.quantity) as service_commission,
                    SUM(qi.total_amount) as service_selling_value
             FROM `quotation_items` qi
             JOIN `quotations` q ON qi.quotation_id = q.id
             WHERE q.quotation_date BETWEEN ? AND ?
             GROUP BY qi.item_name
             ORDER BY service_commission DESC",
            [$dateFrom, $dateTo]
        );

        // Commission by Employee
        $byEmployee = DB::fetchAll(
            "SELECT u.name,
                    COUNT(DISTINCT q.id) as quote_count,
                    SUM(q.subtotal) as total_volume,
                    SUM(q.total_commission) as earned_commission
             FROM `quotations` q
             LEFT JOIN `users` u ON q.sales_person_id = u.id
             WHERE q.quotation_date BETWEEN ? AND ?
             GROUP BY u.id
             ORDER BY earned_commission DESC",
            [$dateFrom, $dateTo]
        );

        $this->view('reports/commission', [
            'dateFrom'         => $dateFrom,
            'dateTo'           => $dateTo,
            'actualCost'       => $actualCost,
            'commission'       => $commission,
            'sellingVal'       => $sellingVal,
            'avgCommissionPct' => $avgCommissionPct,
            'overallMarginPct' => $overallMarginPct,
            'byService'        => $byService,
            'byEmployee'       => $byEmployee
        ]);
    }
}
