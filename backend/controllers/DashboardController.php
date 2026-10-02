<?php
/**
 * Sagar Advertising CRM - Executive Dashboard Controller
 */

declare(strict_types=1);

require_once ROOT_PATH . '/backend/core/Controller.php';

class DashboardController extends Controller {
    public function index(): void {
        $this->requireAuth();

        // 1. Time-aware greeting
        $hour = (int)date('G');
        if ($hour < 12) {
            $greeting = "Good Morning, Sagar Advertising 👋";
        } elseif ($hour < 17) {
            $greeting = "Good Afternoon, Sagar Advertising ☀️";
        } else {
            $greeting = "Good Evening, Sagar Advertising 🌙";
        }

        // 2. High-level KPI aggregates
        $totalCustomers = (int)DB::fetchColumn("SELECT COUNT(*) FROM `customers` WHERE `deleted_at` IS NULL");
        $activeCustomers = (int)DB::fetchColumn("SELECT COUNT(*) FROM `customers` WHERE `status` = 'Active' AND `deleted_at` IS NULL");

        $totalQuotes = (int)DB::fetchColumn("SELECT COUNT(*) FROM `quotations`");
        $pendingQuotes = (int)DB::fetchColumn("SELECT COUNT(*) FROM `quotations` WHERE `status` IN ('Draft', 'Sent', 'Under Discussion')");
        $acceptedQuotes = (int)DB::fetchColumn("SELECT COUNT(*) FROM `quotations` WHERE `status` IN ('Approved', 'Converted')");
        $rejectedQuotes = (int)DB::fetchColumn("SELECT COUNT(*) FROM `quotations` WHERE `status` = 'Rejected'");

        $totalQuotationValue = (float)DB::fetchColumn("SELECT COALESCE(SUM(grand_total), 0) FROM `quotations`");
        $monthSales = (float)DB::fetchColumn(
            "SELECT COALESCE(SUM(grand_total), 0) FROM `quotations`
             WHERE `status` IN ('Approved', 'Converted') AND MONTH(quotation_date) = MONTH(CURRENT_DATE()) AND YEAR(quotation_date) = YEAR(CURRENT_DATE())"
        );

        $emailsSent = (int)DB::fetchColumn("SELECT COUNT(*) FROM `email_logs` WHERE `status` = 'sent'");
        $upcomingFollowups = (int)DB::fetchColumn(
            "SELECT COUNT(*) FROM `customer_followups` WHERE `status` = 'pending' AND `followup_date` >= CURRENT_DATE()"
        );

        // 3. Monthly Sales & Trend Data (Last 6 Months)
        $monthlyLabels = [];
        $monthlyValues = [];
        $monthlyApprovedValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $mTime = strtotime("-{$i} months");
            $mLabel = date('M Y', $mTime);
            $monthlyLabels[] = $mLabel;

            $mMonth = date('m', $mTime);
            $mYear = date('Y', $mTime);

            $val = (float)DB::fetchColumn(
                "SELECT COALESCE(SUM(grand_total), 0) FROM `quotations` WHERE MONTH(quotation_date) = ? AND YEAR(quotation_date) = ?",
                [$mMonth, $mYear]
            );
            $appVal = (float)DB::fetchColumn(
                "SELECT COALESCE(SUM(grand_total), 0) FROM `quotations` WHERE `status` IN ('Approved', 'Converted') AND MONTH(quotation_date) = ? AND YEAR(quotation_date) = ?",
                [$mMonth, $mYear]
            );

            $monthlyValues[] = $val;
            $monthlyApprovedValues[] = $appVal;
        }

        // 4. Quotation Status Breakdown
        $statusCounts = DB::fetchAll(
            "SELECT status, COUNT(*) as cnt FROM `quotations` GROUP BY status"
        );
        $statusLabels = [];
        $statusValues = [];
        foreach ($statusCounts as $sc) {
            $statusLabels[] = $sc['status'];
            $statusValues[] = (int)$sc['cnt'];
        }

        // 5. Service-Wise Quotation Value
        $serviceBreakdown = DB::fetchAll(
            "SELECT qi.item_name, COALESCE(SUM(qi.total_amount), 0) as total_val
             FROM `quotation_items` qi
             GROUP BY qi.item_name
             ORDER BY total_val DESC
             LIMIT 6"
        );
        $serviceLabels = array_column($serviceBreakdown, 'item_name');
        $serviceValues = array_map('floatval', array_column($serviceBreakdown, 'total_val'));

        // 6. Recent Quotations
        $recentQuotations = DB::fetchAll(
            "SELECT q.*, c.company_name, c.contact_person
             FROM `quotations` q
             JOIN `customers` c ON q.customer_id = c.id
             ORDER BY q.created_at DESC
             LIMIT 6"
        );

        // 7. Recent Customers
        $recentCustomers = DB::fetchAll(
            "SELECT * FROM `customers` WHERE `deleted_at` IS NULL ORDER BY `created_at` DESC LIMIT 5"
        );

        // 8. Today's & Upcoming Followups
        $followupsList = DB::fetchAll(
            "SELECT f.*, c.company_name, c.contact_person, c.mobile
             FROM `customer_followups` f
             JOIN `customers` c ON f.customer_id = c.id
             WHERE f.status = 'pending' AND f.followup_date >= CURRENT_DATE()
             ORDER BY f.followup_date ASC, f.followup_time ASC
             LIMIT 5"
        );

        $this->view('dashboard/index', [
            'greeting'             => $greeting,
            'totalCustomers'       => $totalCustomers,
            'activeCustomers'      => $activeCustomers,
            'totalQuotes'          => $totalQuotes,
            'pendingQuotes'        => $pendingQuotes,
            'acceptedQuotes'       => $acceptedQuotes,
            'rejectedQuotes'       => $rejectedQuotes,
            'totalQuotationValue'  => $totalQuotationValue,
            'monthSales'           => $monthSales,
            'emailsSent'           => $emailsSent,
            'upcomingFollowups'    => $upcomingFollowups,
            'monthlySalesChart'    => [
                'labels'          => $monthlyLabels,
                'values'          => $monthlyValues,
                'approved_values' => $monthlyApprovedValues
            ],
            'statusChart'          => [
                'labels' => $statusLabels,
                'counts' => $statusValues
            ],
            'serviceChart'         => [
                'labels' => $serviceLabels,
                'values' => $serviceValues
            ],
            'recentQuotations'     => $recentQuotations,
            'recentCustomers'      => $recentCustomers,
            'followupsList'        => $followupsList
        ]);
    }
}
