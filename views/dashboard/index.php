<?php
/**
 * Sagar Advertising CRM - Executive Dashboard View
 */
?>

<!-- Pass Chart Data to Frontend JS -->
<script>
    window.MONTHLY_SALES_DATA = <?= json_encode($monthlySalesChart) ?>;
    window.STATUS_BREAKDOWN_DATA = <?= json_encode($statusChart) ?>;
    window.SERVICE_REVENUE_DATA = <?= json_encode($serviceChart) ?>;
</script>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;"><?= e($greeting) ?></h2>
        <p class="text-muted mb-0">Here is your daily operational summary, quotation pipelines, and customer engagements.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="<?= url('quotations/builder') ?>" class="btn-brand-primary">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Create Quotation</span>
        </a>
        <a href="<?= url('customers/create') ?>" class="btn-brand-dark">
            <i class="bi bi-person-plus-fill"></i>
            <span>Add Customer</span>
        </a>
    </div>
</div>

<!-- Primary Quick Action Floating Banner -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <a href="<?= url('quotations/builder') ?>" class="card-brand p-3 d-flex align-items-center gap-3 text-decoration-none border-start border-4 border-warning">
            <div class="stat-icon" style="background:#FFF3E0; color:#FF5500;">
                <i class="bi bi-file-earmark-plus-fill"></i>
            </div>
            <div>
                <div class="fw-bold text-dark">New Quotation</div>
                <div class="small text-muted">Create custom quote</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= url('customers/create') ?>" class="card-brand p-3 d-flex align-items-center gap-3 text-decoration-none border-start border-4 border-info">
            <div class="stat-icon" style="background:#E0F2FE; color:#0284C7;">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <div>
                <div class="fw-bold text-dark">Add Customer</div>
                <div class="small text-muted">Register new client</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= url('excel/import') ?>" class="card-brand p-3 d-flex align-items-center gap-3 text-decoration-none border-start border-4 border-success">
            <div class="stat-icon" style="background:#DCFCE7; color:#16A34A;">
                <i class="bi bi-file-earmark-spreadsheet-fill"></i>
            </div>
            <div>
                <div class="fw-bold text-dark">Import Excel</div>
                <div class="small text-muted">Upload bulk records</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= url('campaigns/builder') ?>" class="card-brand p-3 d-flex align-items-center gap-3 text-decoration-none border-start border-4 border-primary">
            <div class="stat-icon" style="background:#EEF2FF; color:#4F46E5;">
                <i class="bi bi-send-check-fill"></i>
            </div>
            <div>
                <div class="fw-bold text-dark">Send Campaign</div>
                <div class="small text-muted">Festival & offers email</div>
            </div>
        </a>
    </div>
</div>

<!-- Key Business Metric Stat Cards -->
<div class="row g-3 mb-4">
    <!-- Total Quotation Pipeline Value -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Quotation Pipeline</span>
                <div class="stat-icon"><i class="bi bi-currency-rupee"></i></div>
            </div>
            <div class="stat-value"><?= format_currency($totalQuotationValue) ?></div>
            <div class="stat-subtitle">
                <span class="stat-trend-up"><i class="bi bi-arrow-up-right"></i> Active Pipeline</span>
                <span class="ms-1">across <?= $totalQuotes ?> quotes</span>
            </div>
        </div>
    </div>

    <!-- This Month's Converted Sales -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Month's Closed Sales</span>
                <div class="stat-icon" style="background:#ECFDF5; color:#059669;"><i class="bi bi-trophy-fill"></i></div>
            </div>
            <div class="stat-value text-success"><?= format_currency($monthSales) ?></div>
            <div class="stat-subtitle">
                <span class="stat-trend-up"><i class="bi bi-check-all"></i> Approved</span>
                <span class="ms-1">in <?= date('F Y') ?></span>
            </div>
        </div>
    </div>

    <!-- Active Customers -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Customer Base</span>
                <div class="stat-icon" style="background:#EFF6FF; color:#2563EB;"><i class="bi bi-building"></i></div>
            </div>
            <div class="stat-value"><?= $activeCustomers ?></div>
            <div class="stat-subtitle text-muted">
                <span>Total: <?= $totalCustomers ?> registered</span> &bull; <span>Hubballi-Dharwad</span>
            </div>
        </div>
    </div>

    <!-- Follow-ups & Campaigns -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Due Follow-ups</span>
                <div class="stat-icon" style="background:#FFFBEB; color:#D97706;"><i class="bi bi-calendar2-event-fill"></i></div>
            </div>
            <div class="stat-value text-warning"><?= $upcomingFollowups ?></div>
            <div class="stat-subtitle text-muted">
                <span><?= $emailsSent ?> emails sent</span> &bull; <a href="<?= url('customers') ?>" class="text-decoration-none fw-bold text-dark">View clients</a>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats Row (Pipeline Breakdown) -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card-brand p-3 d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small fw-bold text-uppercase">Pending Quotes</div>
                <div class="fs-4 fw-bold text-warning"><?= $pendingQuotes ?></div>
            </div>
            <span class="badge bg-warning-subtle text-warning fs-6 px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-brand p-3 d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small fw-bold text-uppercase">Approved / Won</div>
                <div class="fs-4 fw-bold text-success"><?= $acceptedQuotes ?></div>
            </div>
            <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-brand p-3 d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small fw-bold text-uppercase">Rejected</div>
                <div class="fs-4 fw-bold text-danger"><?= $rejectedQuotes ?></div>
            </div>
            <span class="badge bg-danger-subtle text-danger fs-6 px-3 py-2 rounded-pill"><i class="bi bi-x-circle-fill"></i></span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-brand p-3 d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small fw-bold text-uppercase">Email Dispatches</div>
                <div class="fs-4 fw-bold text-primary"><?= $emailsSent ?></div>
            </div>
            <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2 rounded-pill"><i class="bi bi-envelope-check-fill"></i></span>
        </div>
    </div>
</div>

<!-- Visual Analytics Charts Row -->
<div class="row g-4 mb-4">
    <!-- Monthly Sales Trend Chart -->
    <div class="col-12 col-xl-8">
        <div class="card-brand h-100">
            <div class="card-header">
                <div>
                    <h5 class="fw-bold mb-0">Quotation Trend & Revenue Growth</h5>
                    <span class="text-muted small">Comparison of quoted pipeline vs closed sales</span>
                </div>
                <a href="<?= url('reports') ?>" class="btn btn-sm btn-link text-muted text-decoration-none">Full Report &rarr;</a>
            </div>
            <div class="card-body" style="height: 320px; position: relative;">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quotation Status Distribution -->
    <div class="col-12 col-xl-4">
        <div class="card-brand h-100">
            <div class="card-header">
                <h5 class="fw-bold mb-0">Quotation Status Breakdown</h5>
                <span class="badge bg-light text-dark fw-bold"><?= $totalQuotes ?> Total</span>
            </div>
            <div class="card-body" style="height: 320px; position: relative;">
                <canvas id="quotationStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Service Revenue & Follow-ups Row -->
<div class="row g-4 mb-4">
    <!-- Service-wise Quotation Value -->
    <div class="col-12 col-lg-6">
        <div class="card-brand h-100">
            <div class="card-header">
                <div>
                    <h5 class="fw-bold mb-0">High-Demand Advertising Services</h5>
                    <span class="text-muted small">Top services by total quoted volume</span>
                </div>
                <a href="<?= url('services') ?>" class="btn btn-sm btn-link text-muted text-decoration-none">Manage Catalog &rarr;</a>
            </div>
            <div class="card-body" style="height: 280px; position: relative;">
                <canvas id="serviceRevenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Upcoming Customer Follow-ups -->
    <div class="col-12 col-lg-6">
        <div class="card-brand h-100">
            <div class="card-header">
                <div>
                    <h5 class="fw-bold mb-0">Actionable Follow-ups</h5>
                    <span class="text-muted small">Calls, meetings and WhatsApp follow-ups</span>
                </div>
                <span class="badge bg-warning text-dark fw-bold"><?= count($followupsList) ?> Pending</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th>Client / Business</th>
                                <th>Type</th>
                                <th>Schedule</th>
                                <th>Notes</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($followupsList)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No pending follow-ups scheduled for today.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($followupsList as $fo): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('customers/detail?id=' . $fo['customer_id']) ?>" class="fw-bold text-dark text-decoration-none">
                                            <?= e($fo['company_name']) ?>
                                        </a>
                                        <div class="small text-muted"><?= e($fo['contact_person']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= e($fo['followup_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= date('d M', strtotime($fo['followup_date'])) ?></div>
                                        <div class="small text-muted"><?= $fo['followup_time'] ? date('h:i A', strtotime($fo['followup_time'])) : '-' ?></div>
                                    </td>
                                    <td style="max-width: 180px;" class="text-truncate" title="<?= e($fo['notes']) ?>">
                                        <?= e($fo['notes']) ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="tel:<?= e($fo['mobile']) ?>" class="btn btn-sm btn-outline-success p-1" title="Call">
                                            <i class="bi bi-telephone-fill"></i>
                                        </a>
                                        <a href="https://wa.me/91<?= preg_replace('/\D/', '', $fo['mobile']) ?>" target="_blank" class="btn btn-sm btn-outline-primary p-1" title="WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Quotations & Customers Table -->
<div class="row g-4">
    <!-- Recent Quotations -->
    <div class="col-12 col-xl-7">
        <div class="card-brand">
            <div class="card-header">
                <div>
                    <h5 class="fw-bold mb-0">Recent Quotations</h5>
                    <span class="text-muted small">Latest generated commercial proposals</span>
                </div>
                <a href="<?= url('quotations') ?>" class="btn btn-sm btn-brand-outline">View All (<?= $totalQuotes ?>)</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-brand table">
                        <thead>
                            <tr>
                                <th>Quotation #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentQuotations as $q): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('quotations/view?id=' . $q['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= e($q['quotation_number']) ?>
                                    </a>
                                    <div class="small text-muted"><?= date('d M Y', strtotime($q['quotation_date'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= e($q['company_name']) ?></div>
                                    <div class="small text-muted text-truncate" style="max-width: 160px;"><?= e($q['project_name']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= format_currency($q['grand_total']) ?></div>
                                    <div class="small text-success">+<?= format_currency($q['total_commission']) ?> comm</div>
                                </td>
                                <td>
                                    <?= get_status_badge($q['status']) ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('quotations/view?id=' . $q['id']) ?>" class="btn btn-outline-secondary" title="View Document">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= url('quotations/builder?id=' . $q['id']) ?>" class="btn btn-outline-secondary" title="Edit Quote">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Customers -->
    <div class="col-12 col-xl-5">
        <div class="card-brand">
            <div class="card-header">
                <div>
                    <h5 class="fw-bold mb-0">Newly Registered Clients</h5>
                    <span class="text-muted small">Recent additions to customer CRM</span>
                </div>
                <a href="<?= url('customers') ?>" class="btn btn-sm btn-brand-outline">All Clients &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($recentCustomers as $c): ?>
                    <a href="<?= url('customers/detail?id=' . $c['id']) ?>" class="list-group-item list-group-item-action py-3 px-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark"><?= e($c['company_name']) ?></span>
                            <span class="badge bg-light text-muted border"><?= e($c['customer_type']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-muted small">
                            <div><i class="bi bi-person me-1"></i><?= e($c['contact_person']) ?> &bull; <?= e($c['city']) ?></div>
                            <span class="text-dark fw-bold"><?= e($c['mobile']) ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
