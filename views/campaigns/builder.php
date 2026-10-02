<?php
/**
 * Sagar Advertising CRM - Email Campaign Builder
 */
?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;">Create Bulk Email Campaign</h2>
                <p class="text-muted mb-0">Craft festival greetings, festive discount offers, and customer appreciation broadcasts.</p>
            </div>
            <a href="<?= url('campaigns') ?>" class="btn-brand-outline text-nowrap">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Campaigns</span>
            </a>
        </div>

        <div class="card-brand">
            <div class="card-body p-3 p-sm-4">
                <form method="POST" action="<?= url('campaigns/store') ?>" id="campaignBuilderForm">
                    <?= csrf_field() ?>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">1. Campaign Configuration</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Campaign Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g. Diwali 2026 Customer Wishes, Ugadi Branding Offer">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Load from Saved Template</label>
                            <select class="form-select" id="templateSelector">
                                <option value="">-- Start with Blank Canvas --</option>
                                <?php foreach ($templates as $tpl): ?>
                                    <option value="<?= $tpl['id'] ?>" data-subject="<?= e($tpl['subject']) ?>" data-body="<?= e($tpl['body_html']) ?>">
                                        <?= e($tpl['name']) ?> (<?= ucfirst($tpl['category']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">2. Target Audience & Recipient Filter</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Filter By</label>
                            <select class="form-select" name="recipient_filter" id="filterTypeSelect">
                                <option value="all">All Opted-in Customers (<?= $totalEligible ?> contacts)</option>
                                <option value="city">Filter by City</option>
                                <option value="type">Filter by Customer Type</option>
                                <option value="status">Filter by Customer Status</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="filterValueCol" style="display: none;">
                            <label class="form-label small fw-bold">Select Criterion Value</label>
                            <select class="form-select" name="filter_value" id="filterValueSelect">
                                <option value="">-- Choose Criterion --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="small text-muted fw-bold text-uppercase">Spam Compliance:</div>
                                <div class="small text-muted">Only active clients with marketing consent and valid emails are targeted. Unsubscribe links are auto-appended.</div>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">3. Email Content & Personalization</h5>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Sender Name</label>
                            <input type="text" class="form-control" name="from_name" value="Sagar Advertising">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Sender Email Address</label>
                            <input type="email" class="form-control" name="from_email" value="sagaradvertising7@gmail.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Email Subject Line <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject" id="campaignSubject" required placeholder="e.g. Warm Diwali Wishes to {{company_name}} from Sagar Advertising! ✨">
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Email Message Body (HTML Supported) <span class="text-danger">*</span></label>
                                <div class="small text-muted">Insert dynamic placeholders:</div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap mb-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="insertTag('{{customer_name}}')">+ {{customer_name}}</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="insertTag('{{company_name}}')">+ {{company_name}}</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="insertTag('{{city}}')">+ {{city}}</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="insertTag('{{phone}}')">+ {{phone}}</button>
                            </div>
                            <textarea class="form-control font-monospace" name="body_html" id="campaignBody" rows="12" required></textarea>
                        </div>
                    </div>

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= url('campaigns') ?>" class="btn btn-outline-secondary text-center">Cancel</a>
                        <button type="submit" class="btn btn-brand-primary">
                            <i class="bi bi-stack me-1"></i>
                            <span>Queue Campaign for Sending</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const cities = <?= json_encode($cities) ?>;
const types = <?= json_encode($types) ?>;
const statuses = ['Active', 'Lead', 'Inactive'];

const filterType = document.getElementById('filterTypeSelect');
const filterValCol = document.getElementById('filterValueCol');
const filterVal = document.getElementById('filterValueSelect');

filterType.addEventListener('change', () => {
    const val = filterType.value;
    filterVal.innerHTML = '<option value="">-- Choose Criterion --</option>';

    if (val === 'all') {
        filterValCol.style.display = 'none';
    } else {
        filterValCol.style.display = 'block';
        let list = [];
        if (val === 'city') list = cities;
        else if (val === 'type') list = types;
        else if (val === 'status') list = statuses;

        list.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item;
            opt.textContent = item;
            filterVal.appendChild(opt);
        });
    }
});

// Template loader
const templateSelector = document.getElementById('templateSelector');
templateSelector.addEventListener('change', () => {
    const selected = templateSelector.options[templateSelector.selectedIndex];
    if (selected && selected.dataset.subject) {
        document.getElementById('campaignSubject').value = selected.dataset.subject;
        document.getElementById('campaignBody').value = selected.dataset.body;
    }
});

function insertTag(tag) {
    const textarea = document.getElementById('campaignBody');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + tag + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + tag.length;
}
</script>
