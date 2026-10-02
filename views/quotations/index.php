<?php
/**
 * Sagar Advertising CRM - Quotation List & Management
 */
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;">Quotations & Estimates</h2>
        <p class="text-muted mb-0">Track advertising proposals, commercial pricing, commission margins, and client approvals.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="<?= url('excel/export-quotations?' . http_build_query($filters)) ?>" class="btn-brand-outline">
            <i class="bi bi-file-earmark-arrow-down-fill text-primary"></i>
            <span>Export CSV</span>
        </a>
        <a href="<?= url('quotations/builder') ?>" class="btn-brand-primary">
            <i class="bi bi-plus-circle-fill"></i>
            <span>+ Create Quotation</span>
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card-brand mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('quotations') ?>" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control" name="search" placeholder="Search quotation #, project, client..." value="<?= e($filters['search']) ?>">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- All Statuses --</option>
                    <?php foreach (['Draft', 'Sent', 'Viewed', 'Under Discussion', 'Approved', 'Rejected', 'Expired', 'Converted'] as $st): ?>
                        <option value="<?= $st ?>" <?= $filters['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="customer" class="form-select form-select-sm">
                    <option value="">-- All Customers --</option>
                    <?php foreach ($customers as $cu): ?>
                        <option value="<?= $cu['id'] ?>" <?= $filters['customer'] == $cu['id'] ? 'selected' : '' ?>><?= e($cu['company_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="date" class="form-control form-control-sm" name="from" value="<?= e($filters['from']) ?>" placeholder="From Date">
            </div>
            <div class="col-6 col-md-1">
                <button type="submit" class="btn btn-sm btn-dark w-100">Filter</button>
            </div>
            <div class="col-12 col-md-1">
                <?php if (array_filter($filters)): ?>
                    <a href="<?= url('quotations') ?>" class="btn btn-sm btn-outline-secondary w-100" title="Reset">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Quotation Table -->
<div class="table-brand-wrap">
    <div class="table-responsive">
        <table class="table-brand table">
            <thead>
                <tr>
                    <th style="width: 140px;">Quotation #</th>
                    <th>Customer / Client</th>
                    <th>Project Description</th>
                    <th>Date / Validity</th>
                    <th>Amount (₹)</th>
                    <th>Commission (₹)</th>
                    <th>Sales Rep</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($quotations)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-muted"></i>
                            No quotations found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($quotations as $q): ?>
                    <tr>
                        <td>
                            <a href="<?= url('quotations/view?id=' . $q['id']) ?>" class="fw-bold text-dark text-decoration-none font-monospace">
                                <?= e($q['quotation_number']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= url('customers/detail?id=' . $q['customer_id']) ?>" class="fw-bold text-dark text-decoration-none">
                                <?= e($q['company_name']) ?>
                            </a>
                            <div class="small text-muted"><?= e($q['contact_person']) ?></div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark text-truncate" style="max-width: 180px;"><?= e($q['project_name'] ?: 'Advertising Work') ?></div>
                            <?php if (!empty($q['reference'])): ?>
                                <div class="small text-muted">Ref: <?= e($q['reference']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-bold"><?= date('d M Y', strtotime($q['quotation_date'])) ?></div>
                            <div class="small text-muted">Valid till <?= date('d M Y', strtotime($q['valid_until'])) ?></div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?= format_currency($q['grand_total']) ?></div>
                            <div class="small text-muted">Taxable: <?= format_currency($q['taxable_amount']) ?></div>
                        </td>
                        <td>
                            <div class="fw-bold text-success">+<?= format_currency($q['total_commission']) ?></div>
                            <div class="small text-muted">
                                <?= $q['subtotal'] > 0 ? round(($q['total_commission'] / $q['subtotal']) * 100, 1) : 0 ?>% margin
                            </div>
                        </td>
                        <td>
                            <div class="small fw-bold text-dark"><?= e($q['sales_person_name'] ?? 'Admin') ?></div>
                        </td>
                        <td>
                            <?= get_status_badge($q['status']) ?>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <a class="dropdown-item" href="<?= url('quotations/view?id=' . $q['id']) ?>" target="_blank">
                                            <i class="bi bi-eye text-primary me-2"></i> View / Print Quotation
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="openSendEmailModal(<?= htmlspecialchars(json_encode($q), ENT_QUOTES) ?>)">
                                            <i class="bi bi-send text-success me-2"></i> Send Quotation Email
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= url('quote/view/' . ($q['view_token'] ?: $q['quotation_number'])) ?>" target="_blank">
                                            <i class="bi bi-link-45deg text-info me-2"></i> Copy Customer Web Link
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= url('quotations/builder?id=' . $q['id']) ?>">
                                            <i class="bi bi-pencil text-secondary me-2"></i> Edit Line Items
                                        </a>
                                    </li>
                                    <li>
                                        <form method="POST" action="<?= url('quotations/duplicate') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                                            <button type="submit" class="dropdown-item">
                                                <i class="bi bi-copy text-warning me-2"></i> Duplicate Quotation
                                            </button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="dropdown-header small text-uppercase">Update Status</li>
                                    <?php foreach (['Approved', 'Under Discussion', 'Sent', 'Converted', 'Rejected'] as $targetStatus): ?>
                                        <?php if ($q['status'] !== $targetStatus): ?>
                                            <li>
                                                <form method="POST" action="<?= url('quotations/status') ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $q['id'] ?>">
                                                    <input type="hidden" name="status" value="<?= $targetStatus ?>">
                                                    <button type="submit" class="dropdown-item small py-1">
                                                        Mark as <strong><?= $targetStatus ?></strong>
                                                    </button>
                                                </form>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="<?= url('quotations/delete') ?>" onsubmit="return confirm('Are you sure you want to delete quotation <?= e($q['quotation_number']) ?>?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash3 me-2"></i> Delete Quotation
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

<!-- Send Quotation Email Modal -->
<div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= url('quotations/send-email') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="quotation_id" id="modalQuotationId">

                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="sendEmailModalLabel"><i class="bi bi-envelope-at me-2 text-warning"></i> Send Quotation Directly to Client</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-paperclip fs-5"></i>
                        <span>The official branded PDF/HTML quotation will be <strong>automatically attached</strong> to this email.</span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Recipient Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="to_email" id="modalToEmail" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Subject Line <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject" id="modalSubject" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Message</label>
                        <textarea class="form-control" name="message" id="modalMessage" rows="8"></textarea>
                        <div class="form-text small">
                            Variables supported: <code>{{customer_name}}</code>, <code>{{company_name}}</code>, <code>{{quotation_number}}</code>, <code>{{grand_total}}</code>, <code>{{valid_until}}</code>, <code>{{quotation_web_url}}</code>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand-primary">
                        <i class="bi bi-send-fill me-1"></i> Send Quotation Email Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openSendEmailModal(q) {
    document.getElementById('modalQuotationId').value = q.id;
    document.getElementById('modalToEmail').value = q.email || '';
    document.getElementById('modalSubject').value = `Quotation ${q.quotation_number} – Sagar Advertising (${q.project_name || 'Advertising & Signage'})`;

    const defaultMsg = `Dear {{customer_name}},\n\nThank you for reaching out to Sagar Advertising. We are pleased to submit our formal quotation for your project "${q.project_name || 'Advertising & Branding Work'}".\n\nQuotation Details:\n- Quotation No: {{quotation_number}}\n- Total Amount: ₹{{grand_total}} (Incl. GST)\n- Validity: {{valid_until}}\n\nWe have attached the detailed PDF quotation with complete line item specifications and commercial terms.\n\nYou can also view your live digital quotation online anytime: {{quotation_web_url}}\n\nPlease contact us at 9611620862 / 8904184867 if you have any questions.\n\nWarm Regards,\nVageesh H Hugar\nSagar Advertising, Hubballi`;

    document.getElementById('modalMessage').value = defaultMsg;

    const modal = new bootstrap.Modal(document.getElementById('sendEmailModal'));
    modal.show();
}
</script>
