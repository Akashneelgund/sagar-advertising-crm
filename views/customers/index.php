<?php
/**
 * Sagar Advertising CRM - Customer Database Listing
 */
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;">Customer Relationship Management</h2>
        <p class="text-muted mb-0">Directory of retail, corporate, and dealer clients across Karnataka.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="<?= url('excel/import') ?>" class="btn-brand-outline">
            <i class="bi bi-file-earmark-arrow-up-fill text-success"></i>
            <span>Import Excel</span>
        </a>
        <a href="<?= url('excel/export-customers?' . http_build_query($filters)) ?>" class="btn-brand-outline">
            <i class="bi bi-file-earmark-arrow-down-fill text-primary"></i>
            <span>Export CSV</span>
        </a>
        <a href="<?= url('customers/create') ?>" class="btn-brand-primary">
            <i class="bi bi-person-plus-fill"></i>
            <span>+ Add Customer</span>
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card-brand mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('customers') ?>" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control" name="search" placeholder="Search company, person, phone..." value="<?= e($filters['search']) ?>">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="city" class="form-select form-select-sm">
                    <option value="">-- All Cities --</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?= e($c) ?>" <?= $filters['city'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">-- Customer Type --</option>
                    <?php foreach (['Business', 'Corporate', 'Dealer', 'Individual', 'Existing Client', 'New Client'] as $t): ?>
                        <option value="<?= $t ?>" <?= $filters['type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- All Statuses --</option>
                    <?php foreach (['Active', 'Lead', 'Inactive', 'Lost'] as $st): ?>
                        <option value="<?= $st ?>" <?= $filters['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="assigned" class="form-select form-select-sm">
                    <option value="">-- Assigned Rep --</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= $filters['assigned'] == $emp['id'] ? 'selected' : '' ?>><?= e($emp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-dark w-100">Filter</button>
                <?php if (array_filter($filters)): ?>
                    <a href="<?= url('customers') ?>" class="btn btn-sm btn-outline-secondary" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Customer Table Card -->
<div class="table-brand-wrap">
    <div class="table-responsive">
        <table class="table-brand table">
            <thead>
                <tr>
                    <th style="width: 110px;">Customer ID</th>
                    <th>Business / Company Name</th>
                    <th>Contact Person</th>
                    <th>Phone / WhatsApp</th>
                    <th>City / Location</th>
                    <th>Type</th>
                    <th>Quotes Value</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2 text-muted"></i>
                            No customers found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                    <tr>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace"><?= e($c['customer_code']) ?></span>
                        </td>
                        <td>
                            <a href="<?= url('customers/detail?id=' . $c['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                <?= e($c['company_name']) ?>
                            </a>
                            <?php if (!empty($c['gstin'])): ?>
                                <div class="small text-muted">GST: <?= e($c['gstin']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-bold"><?= e($c['contact_person']) ?></div>
                            <div class="small text-muted"><?= e($c['email'] ?: 'No email') ?></div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <a href="tel:<?= e($c['mobile']) ?>" class="text-dark fw-bold text-decoration-none">
                                    <?= e($c['mobile']) ?>
                                </a>
                                <?php if (!empty($c['whatsapp'])): ?>
                                    <a href="https://wa.me/91<?= preg_replace('/\D/', '', $c['whatsapp']) ?>" target="_blank" class="text-success ms-1" title="Chat on WhatsApp">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($c['alternate_mobile'])): ?>
                                <div class="small text-muted"><?= e($c['alternate_mobile']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><?= e($c['city']) ?></div>
                            <div class="small text-muted"><?= e($c['state']) ?></div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= e($c['customer_type']) ?></span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?= format_currency($c['total_quoted']) ?></div>
                            <div class="small text-muted"><?= $c['quote_count'] ?> quote(s)</div>
                        </td>
                        <td>
                            <?= get_status_badge($c['status']) ?>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <a class="dropdown-item" href="<?= url('customers/detail?id=' . $c['id']) ?>">
                                            <i class="bi bi-eye text-primary me-2"></i> View Profile & History
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= url('quotations/builder') ?>?customer_id=<?= $c['id'] ?>">
                                            <i class="bi bi-file-earmark-plus text-warning me-2"></i> Create Quotation
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= url('customers/edit?id=' . $c['id']) ?>">
                                            <i class="bi bi-pencil text-secondary me-2"></i> Edit Details
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="<?= url('customers/delete') ?>" onsubmit="return confirm('Are you sure you want to delete customer <?= e($c['company_name']) ?>?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash3 me-2"></i> Delete Customer
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
