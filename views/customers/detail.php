<?php
/**
 * Sagar Advertising CRM - 360 Degree Customer Detail View
 */
?>

<!-- Customer Header Card -->
<div class="card-brand mb-4">
    <div class="card-body p-3 p-sm-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div class="d-flex align-items-start align-items-sm-center gap-3">
                <div class="stat-icon flex-shrink-0" style="width: 52px; height: 52px; font-size: 24px; border-radius: 14px;">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h2 class="fw-black mb-0" style="font-family: 'Space Grotesk', sans-serif;"><?= e($customer['company_name']) ?></h2>
                        <span class="badge bg-light text-dark border font-monospace"><?= e($customer['customer_code']) ?></span>
                        <?= get_status_badge($customer['status']) ?>
                    </div>
                    <div class="text-muted small mt-1">
                        <strong>Contact:</strong> <?= e($customer['contact_person']) ?> &bull;
                        <strong>City:</strong> <?= e($customer['city']) ?>, <?= e($customer['state']) ?> &bull;
                        <strong>Type:</strong> <?= e($customer['customer_type']) ?> &bull;
                        <strong>Assigned:</strong> <?= e($customer['assigned_employee_name'] ?? 'Unassigned') ?>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="<?= url('quotations/builder?customer_id=' . $customer['id']) ?>" class="btn-brand-primary">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>+ New Quotation</span>
                </a>
                <a href="<?= url('customers/edit?id=' . $customer['id']) ?>" class="btn-brand-outline">
                    <i class="bi bi-pencil"></i>
                    <span>Edit Profile</span>
                </a>
                <a href="<?= url('customers') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Financial KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card-brand p-3">
            <div class="text-muted small fw-bold text-uppercase">Total Quoted Pipeline</div>
            <div class="fs-4 fw-bold text-dark mt-1"><?= format_currency($totalQuoted) ?></div>
            <div class="small text-muted mt-1"><?= count($quotations) ?> quotation(s) generated</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-brand p-3">
            <div class="text-muted small fw-bold text-uppercase">Accepted / Won Value</div>
            <div class="fs-4 fw-bold text-success mt-1"><?= format_currency($acceptedVal) ?></div>
            <div class="small text-success mt-1"><i class="bi bi-check-circle me-1"></i>Approved business</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-brand p-3">
            <div class="text-muted small fw-bold text-uppercase">Pending / In Discussion</div>
            <div class="fs-4 fw-bold text-warning mt-1"><?= format_currency($pendingVal) ?></div>
            <div class="small text-muted mt-1"><i class="bi bi-clock-history me-1"></i>Under negotiation</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-brand p-3">
            <div class="text-muted small fw-bold text-uppercase">Direct Communication</div>
            <div class="d-flex align-items-center gap-2 mt-2">
                <a href="tel:<?= e($customer['mobile']) ?>" class="btn btn-sm btn-outline-dark" title="Call">
                    <i class="bi bi-telephone-fill me-1"></i> Call
                </a>
                <a href="https://wa.me/91<?= preg_replace('/\D/', '', $customer['mobile']) ?>" target="_blank" class="btn btn-sm btn-outline-success" title="WhatsApp">
                    <i class="bi bi-whatsapp me-1"></i> WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Layout: Quotations, Notes, Follow-ups, Email History, Full Info -->
<div class="card-brand mb-4">
    <div class="card-header border-bottom p-0">
        <ul class="nav nav-tabs border-0 px-3 pt-2 flex-nowrap overflow-x-auto text-nowrap" id="customerTabs" role="tablist" style="-webkit-overflow-scrolling: touch;">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold py-3 text-nowrap" id="tab-quotes" data-bs-toggle="tab" data-bs-target="#content-quotes" type="button" role="tab">
                    <i class="bi bi-file-earmark-ruled me-1"></i> Quotations (<?= count($quotations) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold py-3 text-nowrap" id="tab-followups" data-bs-toggle="tab" data-bs-target="#content-followups" type="button" role="tab">
                    <i class="bi bi-calendar-check me-1"></i> Follow-ups (<?= count($followups) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold py-3 text-nowrap" id="tab-notes" data-bs-toggle="tab" data-bs-target="#content-notes" type="button" role="tab">
                    <i class="bi bi-journal-text me-1"></i> Notes (<?= count($notes) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold py-3 text-nowrap" id="tab-emails" data-bs-toggle="tab" data-bs-target="#content-emails" type="button" role="tab">
                    <i class="bi bi-envelope-check me-1"></i> Emails (<?= count($emails) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold py-3 text-nowrap" id="tab-overview" data-bs-toggle="tab" data-bs-target="#content-overview" type="button" role="tab">
                    <i class="bi bi-info-circle me-1"></i> Client Details
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content card-body p-3 p-sm-4" id="customerTabsContent">
        <!-- Tab 1: Quotations -->
        <div class="tab-pane fade show active" id="content-quotes" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Quotations Issued</h5>
                <a href="<?= url('quotations/builder?customer_id=' . $customer['id']) ?>" class="btn btn-sm btn-brand-primary">
                    <i class="bi bi-plus-circle me-1"></i> Create Quotation
                </a>
            </div>
            <div class="table-responsive">
                <table class="table-brand table">
                    <thead>
                        <tr>
                            <th>Quotation #</th>
                            <th>Date</th>
                            <th>Project / Work</th>
                            <th>Amount</th>
                            <th>Commission</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($quotations)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No quotations generated yet for this customer.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($quotations as $q): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('quotations/view?id=' . $q['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= e($q['quotation_number']) ?>
                                    </a>
                                </td>
                                <td><?= date('d M Y', strtotime($q['quotation_date'])) ?></td>
                                <td><?= e($q['project_name'] ?: 'Advertising Work') ?></td>
                                <td class="fw-bold text-dark"><?= format_currency($q['grand_total']) ?></td>
                                <td class="text-success fw-bold">+<?= format_currency($q['total_commission']) ?></td>
                                <td><?= get_status_badge($q['status']) ?></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('quotations/view?id=' . $q['id']) ?>" class="btn btn-outline-secondary" title="View Document">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= url('quotations/builder?id=' . $q['id']) ?>" class="btn btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: Follow-ups -->
        <div class="tab-pane fade" id="content-followups" role="tabpanel">
            <div class="row g-4">
                <div class="col-12 col-lg-7">
                    <h5 class="fw-bold mb-3">Scheduled Follow-ups</h5>
                    <div class="activity-timeline">
                        <?php if (empty($followups)): ?>
                            <p class="text-muted small">No scheduled follow-ups.</p>
                        <?php else: ?>
                            <?php foreach ($followups as $fo): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="fw-bold badge bg-light text-dark border"><?= e($fo['followup_type']) ?></span>
                                        <span><?= date('d M Y', strtotime($fo['followup_date'])) ?> <?= $fo['followup_time'] ? date('h:i A', strtotime($fo['followup_time'])) : '' ?></span>
                                    </div>
                                    <p class="mb-1 mt-2 text-dark small"><?= e($fo['notes']) ?></p>
                                    <div class="small text-muted">Scheduled by <?= e($fo['user_name'] ?? 'Team') ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="card bg-light border p-3 rounded-3">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-calendar-plus me-1 text-primary"></i> Schedule Next Follow-up</h6>
                        <form method="POST" action="<?= url('customers/add-followup') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">
                            <div class="mb-2">
                                <label class="form-label small fw-bold">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" name="followup_date" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold">Time</label>
                                <input type="time" class="form-control form-control-sm" name="followup_time" value="11:00">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold">Follow-up Type</label>
                                <select class="form-select form-select-sm" name="followup_type">
                                    <option value="Call">Phone Call</option>
                                    <option value="Meeting">In-Person Meeting</option>
                                    <option value="WhatsApp">WhatsApp Message</option>
                                    <option value="Email">Email Communication</option>
                                    <option value="Other">Site Visit / Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Discussion Agenda / Notes</label>
                                <textarea class="form-control form-control-sm" name="notes" rows="2" placeholder="e.g. Discuss revised quote rates or artwork approval"></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-brand-primary w-100">Schedule Follow-up</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Notes -->
        <div class="tab-pane fade" id="content-notes" role="tabpanel">
            <div class="row g-4">
                <div class="col-12 col-lg-7">
                    <h5 class="fw-bold mb-3">Client Interaction Notes</h5>
                    <?php if (empty($notes)): ?>
                        <p class="text-muted small">No notes logged yet.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                            <?php foreach ($notes as $n): ?>
                            <div class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1 small text-muted">
                                    <span><strong><?= e($n['user_name'] ?? 'Executive') ?></strong></span>
                                    <span><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></span>
                                </div>
                                <div class="text-dark small" style="white-space: pre-wrap;"><?= e($n['note']) ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="card bg-light border p-3 rounded-3">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-journal-plus me-1 text-primary"></i> Add Internal Note</h6>
                        <form method="POST" action="<?= url('customers/add-note') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">
                            <div class="mb-3">
                                <textarea class="form-control form-control-sm" name="note" rows="3" required placeholder="Log meeting discussion, custom price promises, design specs..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-brand-dark w-100">Save Note</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 4: Email History -->
        <div class="tab-pane fade" id="content-emails" role="tabpanel">
            <h5 class="fw-bold mb-3">Emails & Campaign Dispatches</h5>
            <?php if (empty($emails)): ?>
                <p class="text-muted small">No emails recorded for this client's address (<?= e($customer['email'] ?? 'N/A') ?>).</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table-brand table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Campaign / Type</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($emails as $em): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= e($em['subject']) ?></td>
                                <td><?= e($em['campaign_name'] ?? ($em['quotation_id'] ? 'Quotation Email' : 'Direct Message')) ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($em['created_at'])) ?></td>
                                <td><?= get_status_badge($em['status']) ?></td>
                                <td class="small text-muted"><?= e($em['provider_message']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab 5: Full Business Details -->
        <div class="tab-pane fade" id="content-overview" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card p-3 border">
                        <h6 class="fw-bold text-dark border-bottom pb-2">Contact Details</h6>
                        <dl class="row mb-0 small">
                            <dt class="col-sm-4 text-muted">Contact Person</dt>
                            <dd class="col-sm-8 fw-bold text-dark"><?= e($customer['contact_person']) ?></dd>
                            <dt class="col-sm-4 text-muted">Primary Phone</dt>
                            <dd class="col-sm-8"><?= e($customer['mobile']) ?></dd>
                            <dt class="col-sm-4 text-muted">Alternate Phone</dt>
                            <dd class="col-sm-8"><?= e($customer['alternate_mobile'] ?: 'None') ?></dd>
                            <dt class="col-sm-4 text-muted">Email</dt>
                            <dd class="col-sm-8"><?= e($customer['email'] ?: 'None') ?></dd>
                            <dt class="col-sm-4 text-muted">WhatsApp</dt>
                            <dd class="col-sm-8"><?= e($customer['whatsapp'] ?: 'None') ?></dd>
                        </dl>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card p-3 border">
                        <h6 class="fw-bold text-dark border-bottom pb-2">Business & Location</h6>
                        <dl class="row mb-0 small">
                            <dt class="col-sm-4 text-muted">GST Number</dt>
                            <dd class="col-sm-8 font-monospace fw-bold text-dark"><?= e($customer['gstin'] ?: 'Not Registered') ?></dd>
                            <dt class="col-sm-4 text-muted">Address</dt>
                            <dd class="col-sm-8"><?= nl2br(e($customer['address'] ?: 'Not Specified')) ?></dd>
                            <dt class="col-sm-4 text-muted">City / State</dt>
                            <dd class="col-sm-8"><?= e($customer['city']) ?>, <?= e($customer['state']) ?> - <?= e($customer['pincode']) ?></dd>
                            <dt class="col-sm-4 text-muted">Lead Source</dt>
                            <dd class="col-sm-8"><?= e($customer['source']) ?></dd>
                            <dt class="col-sm-4 text-muted">Marketing Opt-in</dt>
                            <dd class="col-sm-8"><?= $customer['marketing_opt_in'] ? '<span class="text-success fw-bold">Subscribed</span>' : '<span class="text-danger fw-bold">Unsubscribed</span>' ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
