<?php
/**
 * Sagar Advertising CRM - Quotation Profit & Commission Analytics Dashboard
 */
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;">Profit & Commission Analytics</h2>
        <p class="text-muted mb-0">Deep financial analysis of base fabrication costs, agency commission markups, and profit margins.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('reports') ?>" class="btn-brand-outline">
            <i class="bi bi-arrow-left"></i>
            <span>Back to Sales Report</span>
        </a>
    </div>
</div>

<!-- Date Filter Form -->
<div class="card-brand mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('reports/commission') ?>" class="row g-2 align-items-center">
            <div class="col-md-5">
                <label class="form-label small fw-bold mb-1">From Date</label>
                <input type="date" class="form-control form-control-sm" name="from" value="<?= e($dateFrom) ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-bold mb-1">To Date</label>
                <input type="date" class="form-control form-control-sm" name="to" value="<?= e($dateTo) ?>">
            </div>
            <div class="col-12 col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-dark w-100 mt-2 mt-md-4">Filter Analytics</button>
            </div>
        </form>
    </div>
</div>

<!-- Profit Matrix Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Base / Actual Cost</span>
                <div class="stat-icon" style="background:#F1F5F9; color:#475569;"><i class="bi bi-box-seam-fill"></i></div>
            </div>
            <div class="stat-value text-muted"><?= format_currency($actualCost) ?></div>
            <div class="stat-subtitle">Raw fabrication & materials</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Commission Earned</span>
                <div class="stat-icon" style="background:#ECFDF5; color:#059669;"><i class="bi bi-currency-rupee"></i></div>
            </div>
            <div class="stat-value text-success"><?= format_currency($commission) ?></div>
            <div class="stat-subtitle text-success fw-bold">Agency Gross Profit</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Selling Value</span>
                <div class="stat-icon" style="background:#FFF3E0; color:#FF5500;"><i class="bi bi-tag-fill"></i></div>
            </div>
            <div class="stat-value text-dark"><?= format_currency($sellingVal) ?></div>
            <div class="stat-subtitle">Base cost + Commission</div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Average Profit Margin</span>
                <div class="stat-icon" style="background:#EFF6FF; color:#2563EB;"><i class="bi bi-percent"></i></div>
            </div>
            <div class="stat-value text-primary"><?= $overallMarginPct ?>%</div>
            <div class="stat-subtitle"><?= $avgCommissionPct ?>% avg markup on cost</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Commission by Service Table -->
    <div class="col-12 col-lg-7">
        <div class="table-brand-wrap">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="fw-bold mb-0">Commission & Profit by Advertising Service</h5>
            </div>
            <div class="table-responsive">
                <table class="table-brand table">
                    <thead>
                        <tr>
                            <th>Service Name</th>
                            <th>Actual Cost (₹)</th>
                            <th>Commission Earned (₹)</th>
                            <th>Selling Total (₹)</th>
                            <th>Margin %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($byService as $bs): ?>
                        <?php
                            $cost = (float)$bs['service_actual_cost'];
                            $comm = (float)$bs['service_commission'];
                            $sell = (float)$bs['service_selling_value'];
                            $margin = ($sell > 0) ? round(($comm / $sell) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td class="fw-bold text-dark"><?= e($bs['item_name']) ?></td>
                            <td><?= format_currency($cost) ?></td>
                            <td class="text-success fw-bold">+<?= format_currency($comm) ?></td>
                            <td class="fw-bold text-dark"><?= format_currency($sell) ?></td>
                            <td>
                                <span class="badge bg-success-subtle text-success"><?= $margin ?>%</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Commission by Sales Rep Table -->
    <div class="col-12 col-lg-5">
        <div class="table-brand-wrap">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="fw-bold mb-0">Sales Executive Performance</h5>
            </div>
            <div class="table-responsive">
                <table class="table-brand table">
                    <thead>
                        <tr>
                            <th>Executive</th>
                            <th>Quotes</th>
                            <th>Volume (₹)</th>
                            <th>Commission (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($byEmployee as $be): ?>
                        <tr>
                            <td class="fw-bold text-dark"><?= e($be['name'] ?? 'Admin Team') ?></td>
                            <td><?= $be['quote_count'] ?></td>
                            <td><?= format_currency($be['total_volume']) ?></td>
                            <td class="text-success fw-bold">+<?= format_currency($be['earned_commission']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
