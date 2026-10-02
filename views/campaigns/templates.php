<?php
/**
 * Sagar Advertising CRM - Reusable Email Templates Master
 */
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;">Reusable Email Templates</h2>
        <p class="text-muted mb-0">Pre-formatted festival wishes (Diwali, Ugadi), seasonal discounts, and customer thank-you letters.</p>
    </div>
    <button type="button" class="btn-brand-primary" data-bs-toggle="modal" data-bs-target="#newTemplateModal">
        <i class="bi bi-plus-circle-fill"></i>
        <span>+ Create New Template</span>
    </button>
</div>

<div class="row g-4">
    <?php foreach ($templates as $t): ?>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card-brand h-100 d-flex flex-column">
            <div class="card-header">
                <div>
                    <span class="badge bg-dark-subtle text-dark fw-bold text-uppercase small mb-1"><?= e($t['category']) ?></span>
                    <h5 class="fw-bold mb-0 text-dark"><?= e($t['name']) ?></h5>
                </div>
            </div>
            <div class="card-body flex-1">
                <div class="small fw-bold text-muted mb-2">Subject:</div>
                <div class="small fw-bold text-dark mb-3 p-2 bg-light rounded border text-truncate">
                    <?= e($t['subject']) ?>
                </div>

                <div class="small fw-bold text-muted mb-1">Body Preview:</div>
                <div class="p-2 border rounded bg-white small text-muted overflow-hidden" style="max-height: 120px;">
                    <?= strip_tags($t['body_html']) ?>
                </div>
            </div>
            <div class="card-footer bg-light p-3 border-top d-flex justify-content-between align-items-center">
                <span class="small text-muted font-monospace"><?= e($t['variables_hint']) ?></span>
                <a href="<?= url('campaigns/builder') ?>" class="btn btn-sm btn-brand-primary">Use in Campaign</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- New Template Modal -->
<div class="modal fade" id="newTemplateModal" tabindex="-1" aria-labelledby="newTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= url('campaigns/store-template') ?>">
                <?= csrf_field() ?>
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="newTemplateModalLabel"><i class="bi bi-layout-text-window-reverse text-warning me-2"></i> Save New Email Template</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g. Ugadi Festival Greeting">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Category</label>
                            <select class="form-select" name="category">
                                <option value="festival">Festival Greeting</option>
                                <option value="promotional">Promotional Offer</option>
                                <option value="quotation">Quotation Note</option>
                                <option value="custom">Customer Appreciation</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Default Subject Line <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject" required placeholder="Subject with {{customer_name}}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">HTML Message Body <span class="text-danger">*</span></label>
                            <textarea class="form-control font-monospace" name="body_html" rows="10" required></textarea>
                            <div class="form-text small">Use variables: <code>{{customer_name}}</code>, <code>{{company_name}}</code>, <code>{{city}}</code>, <code>{{unsubscribe_url}}</code></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand-primary">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
