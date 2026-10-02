<?php
/**
 * Sagar Advertising CRM - Sales & Business Reports
 */
$totalRevenue = 0;
$totalCommission = 0;
foreach ($salesData as $row) {
    if (in_array($row['status'], ['Approved', 'Converted'])) {
        $totalRevenue += (float)$row['grand_total'];
        $totalCommission += (float)$row['total_commission'];
    }
}
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;">Business & Sales Reports</h2>
        <p class="text-muted mb-0">Financial performance, closed quotations, and commission summaries.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('reports/commission') ?>" class="btn-brand-primary">
            <i class="bi bi-pie-chart-fill"></i>
            <span>Profit & Commission Dashboard</span>
        </a>
    </div>
</div>

<!-- Date Filter Form -->
<div class="card-brand mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('reports') ?>" class="row g-2 align-items-center">
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">From Date</label>
                <input type="date" class="form-control form-control-sm" name="from" value="<?= e($dateFrom) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">To Date</label>
                <input type="date" class="form-control form-control-sm" name="to" value="<?= e($dateTo) ?>">
            </div>
            <div class="col-6 col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-dark w-100 mt-2 mt-md-4">Generate</button>
            </div>
            <div class="col-6 col-md-2 d-flex align-items-end">
                <a href="<?= url('excel/export-quotations?status=Approved') ?>" class="btn btn-sm btn-outline-secondary w-100 mt-2 mt-md-4">Export CSV</a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Metrics for Period -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card-brand p-3">
            <div class="text-muted small fw-bold text-uppercase">Approved Closed Revenue</div>
            <div class="fs-3 fw-bold text-success mt-1"><?= format_currency($totalRevenue) ?></div>
            <div class="small text-muted">For period <?= date('d M Y', strtotime($dateFrom)) ?> - <?= date('d M Y', strtotime($dateTo)) ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-brand p-3">
            <div class="text-muted small fw-bold text-uppercase">Total Earned Commission</div>
            <div class="fs-3 fw-bold text-dark mt-1"><?= format_currency($totalCommission) ?></div>
            <div class="small text-success fw-bold">Profitable agency margin</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-brand p-3">
            <div class="text-muted small fw-bold text-uppercase">Total Quotations Issued</div>
            <div class="fs-3 fw-bold text-dark mt-1"><?= count($salesData) ?></div>
            <div class="small text-muted">Across all sales representatives</div>
        </div>
    </div>
</div>

<!-- Sales Data Table -->
<div class="table-brand-wrap">
    <div class="table-responsive">
        <table class="table-brand table">
            <thead>
                <tr>
                    <th>Quotation #</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Project Description</th>
                    <th>Subtotal (₹)</th>
                    <th>Commission (₹)</th>
                    <th>Grand Total (₹)</th>
                    <th>Representative</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($salesData)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">No quotation records for selected date range.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($salesData as $row): ?>
                    <tr>
                        <td class="font-monospace fw-bold">
                            <a href="<?= url('quotations/view?id=' . $row['id']) ?>" target="_blank" class="text-dark text-decoration-none">
                                <?= e($row['quotation_number']) ?>
                            </a>
                        </td>
                        <td><?= date('d M Y', strtotime($row['quotation_date'])) ?></td>
                        <td class="fw-bold"><?= e($row['company_name']) ?></td>
                        <td><?= e($row['project_name']) ?></td>
                        <td><?= format_currency($row['subtotal']) ?></td>
                        <td class="text-success fw-bold">+<?= format_currency($row['total_commission']) ?></td>
                        <td class="fw-bold text-dark"><?= format_currency($row['grand_total']) ?></td>
                        <td><?= e($row['sales_person'] ?? 'Admin') ?></td>
                        <td><?= get_status_badge($row['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
