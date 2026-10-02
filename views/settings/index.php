<?php
/**
 * Sagar Advertising CRM - System Settings & Database Backup
 */
?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-11">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;">System Settings & Profile</h2>
                <p class="text-muted mb-0">Configure company corporate details, quotation numbering sequences, mail SMTP, and database backups.</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Tabs / Nav -->
            <div class="col-12 col-md-3">
                <div class="list-group list-group-flush card-brand p-2" id="settingsTabs" role="tablist">
                    <button class="list-group-item list-group-item-action active fw-bold py-3" data-bs-toggle="list" data-bs-target="#tab-company" type="button" role="tab">
                        <i class="bi bi-building me-2 text-warning"></i> Company Profile
                    </button>
                    <button class="list-group-item list-group-item-action fw-bold py-3" data-bs-toggle="list" data-bs-target="#tab-quotations" type="button" role="tab">
                        <i class="bi bi-file-earmark-ruled me-2 text-primary"></i> Quotation Defaults
                    </button>
                    <button class="list-group-item list-group-item-action fw-bold py-3" data-bs-toggle="list" data-bs-target="#tab-smtp" type="button" role="tab">
                        <i class="bi bi-envelope-at me-2 text-success"></i> Mail & SMTP Config
                    </button>
                    <button class="list-group-item list-group-item-action fw-bold py-3" data-bs-toggle="list" data-bs-target="#tab-backup" type="button" role="tab">
                        <i class="bi bi-database-down me-2 text-danger"></i> Database Backup
                    </button>
                </div>
            </div>

            <!-- Right Content Panels -->
            <div class="col-12 col-md-9">
                <div class="tab-content" id="nav-tabContent">
                    <!-- 1. Company Profile -->
                    <div class="tab-pane fade show active" id="tab-company" role="tabpanel">
                        <div class="card-brand p-3 p-sm-4">
                            <h4 class="fw-bold mb-3 border-bottom pb-2">Sagar Advertising - Business Information</h4>
                            <form method="POST" action="<?= url('settings/update') ?>">
                                <?= csrf_field() ?>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Company Name</label>
                                        <input type="text" class="form-control" name="company_name" value="<?= e($settings['company_name'] ?? 'SAGAR ADVERTISING') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Brand Tagline</label>
                                        <input type="text" class="form-control" name="company_tagline" value="<?= e($settings['company_tagline'] ?? 'Your Brand. Our Passion.') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Proprietor / Executive 1</label>
                                        <input type="text" class="form-control" name="contact_person_1" value="<?= e($settings['contact_person_1'] ?? 'Vageesh H Hugar') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Phone 1</label>
                                        <input type="text" class="form-control" name="contact_phone_1" value="<?= e($settings['contact_phone_1'] ?? '9611620862') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Proprietor / Executive 2</label>
                                        <input type="text" class="form-control" name="contact_person_2" value="<?= e($settings['contact_person_2'] ?? 'Sagar V Hugar') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Phone 2</label>
                                        <input type="text" class="form-control" name="contact_phone_2" value="<?= e($settings['contact_phone_2'] ?? '8904184867') ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Office Address</label>
                                        <textarea class="form-control" name="company_address" rows="2"><?= e($settings['company_address'] ?? '#18096, "Shanti Kunj", Akkasaligar Oni, Old-Hubballi, HUBBALLI - 580 024') ?></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Official Email</label>
                                        <input type="email" class="form-control" name="company_email" value="<?= e($settings['company_email'] ?? 'sagaradvertising7@gmail.com') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">GSTIN Number</label>
                                        <input type="text" class="form-control font-monospace" name="company_gstin" value="<?= e($settings['company_gstin'] ?? '29AVPH4223R1ZV') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">MSME Registration</label>
                                        <input type="text" class="form-control font-monospace" name="company_msme" value="<?= e($settings['company_msme'] ?? 'UDYAM-KR-13-0061429') ?>">
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-brand-primary">Save Company Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- 2. Quotation Defaults -->
                    <div class="tab-pane fade" id="tab-quotations" role="tabpanel">
                        <div class="card-brand p-3 p-sm-4">
                            <h4 class="fw-bold mb-3 border-bottom pb-2">Quotation Defaults & Numbering</h4>
                            <form method="POST" action="<?= url('settings/update') ?>">
                                <?= csrf_field() ?>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Quotation Number Prefix</label>
                                        <input type="text" class="form-control" name="quotation_prefix" value="<?= e($settings['quotation_prefix'] ?? 'SA/QTN/') ?>">
                                        <div class="form-text small">Example output: <code>SA/QTN/2026/0001</code></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Next Sequence Counter</label>
                                        <input type="number" class="form-control" name="quotation_next_number" value="<?= (int)($settings['quotation_next_number'] ?? 1001) ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Default Validity (Days)</label>
                                        <input type="number" class="form-control" name="default_validity_days" value="<?= (int)($settings['default_validity_days'] ?? 15) ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Default GST %</label>
                                        <input type="number" step="0.1" class="form-control" name="default_gst_rate" value="<?= (float)($settings['default_gst_rate'] ?? 18.0) ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Default Commission %</label>
                                        <input type="number" step="0.1" class="form-control" name="default_commission_rate" value="<?= (float)($settings['default_commission_rate'] ?? 15.0) ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Default Terms & Conditions</label>
                                        <textarea class="form-control" name="default_terms_conditions" rows="4"><?= e($settings['default_terms_conditions'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-brand-primary">Update Quotation Defaults</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- 3. Mail & SMTP Config -->
                    <div class="tab-pane fade" id="tab-smtp" role="tabpanel">
                        <div class="card-brand p-3 p-sm-4">
                            <h4 class="fw-bold mb-3 border-bottom pb-2">Email & SMTP Provider Configuration</h4>
                            <p class="text-muted small mb-3">Supports Gmail SMTP, Google Workspace, Microsoft 365, Amazon SES, or custom corporate mail.</p>
                            
                            <div class="alert alert-info border-info small mb-4">
                                <div class="fw-bold mb-1"><i class="bi bi-info-circle-fill me-1"></i> Mail Configuration Notes:</div>
                                <ul class="mb-0 ps-3">
                                    <li><strong>Simulation Mode:</strong> Safely tests campaigns and quotation dispatch locally. Emails are saved as JSON in <code>storage/mail_logs/</code>.</li>
                                    <li><strong>Live SMTP Transmission:</strong> Sends genuine emails to recipient mailboxes. For Gmail (<code>smtp.gmail.com</code>), you must enable 2-Step Verification in Google and generate a <strong>16-letter App Password</strong> at <a href="https://myaccount.google.com/apppasswords" target="_blank" class="fw-bold text-decoration-underline">myaccount.google.com/apppasswords</a>.</li>
                                </ul>
                            </div>

                            <form method="POST" action="<?= url('settings/update') ?>" id="formSmtpSettings">
                                <?= csrf_field() ?>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Mail Delivery Mode</label>
                                        <select class="form-select" name="smtp_mode" id="smtp_mode">
                                            <option value="simulate" <?= ($settings['smtp_mode'] ?? '') === 'simulate' ? 'selected' : '' ?>>Simulation Mode (Offline Safe - Logs to storage/mail_logs)</option>
                                            <option value="live" <?= ($settings['smtp_mode'] ?? '') === 'live' ? 'selected' : '' ?>>Live SMTP Transmission</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">SMTP Host</label>
                                        <input type="text" class="form-control" name="smtp_host" id="smtp_host" value="<?= e($settings['smtp_host'] ?? 'smtp.gmail.com') ?>" placeholder="e.g. smtp.gmail.com">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">SMTP Port</label>
                                        <input type="number" class="form-control" name="smtp_port" id="smtp_port" value="<?= (int)($settings['smtp_port'] ?? 587) ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Encryption</label>
                                        <select class="form-select" name="smtp_encryption" id="smtp_encryption">
                                            <option value="tls" <?= ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS (Port 587 - Recommended)</option>
                                            <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                                            <option value="none" <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None (Port 25)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">SMTP Username / Email</label>
                                        <input type="text" class="form-control" name="smtp_username" id="smtp_username" value="<?= e($settings['smtp_username'] ?? 'sagaradvertising7@gmail.com') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">SMTP Password / Google App Password</label>
                                        <input type="password" class="form-control" name="smtp_password" id="smtp_password" value="<?= e($settings['smtp_password'] ?? '') ?>" placeholder="Enter 16-letter App Password">
                                        <div class="form-text small">Leave as-is if already saved, or enter your new 16-letter Google App Password.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">From Display Name</label>
                                        <input type="text" class="form-control" name="smtp_from_name" id="smtp_from_name" value="<?= e($settings['smtp_from_name'] ?? 'Sagar Advertising') ?>">
                                    </div>
                                </div>

                                <div class="border-top pt-3 mt-4">
                                    <div class="row align-items-center g-2">
                                        <div class="col-12 col-md-5">
                                            <input type="email" id="testEmailRecipient" class="form-control" placeholder="Test recipient (e.g. yourname@gmail.com)" value="<?= e($settings['smtp_username'] ?? 'sagaradvertising7@gmail.com') ?>">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <button type="button" class="btn btn-outline-dark w-100" id="btnTestSmtpConnection">
                                                <span id="testSmtpSpinner" class="spinner-border spinner-border-sm me-1" style="display:none;"></span>
                                                <i class="bi bi-broadcast me-1" id="testSmtpIcon"></i> Test Connection
                                            </button>
                                        </div>
                                        <div class="col-12 col-md-4 text-md-end">
                                            <button type="submit" class="btn btn-brand-primary w-100 w-md-auto">
                                                <i class="bi bi-save me-1"></i> Save Mail Configuration
                                            </button>
                                        </div>
                                    </div>
                                    <div id="smtpTestResult" class="alert small mt-3" style="display: none;"></div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- 4. Database Backup -->
                    <div class="tab-pane fade" id="tab-backup" role="tabpanel">
                        <div class="card-brand p-3 p-sm-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <div>
                                    <h4 class="fw-bold mb-0">Database Backup Manager</h4>
                                    <span class="text-muted small">Create and download full SQL dumps of clients, quotes, and audit tables.</span>
                                </div>
                                <form method="POST" action="<?= url('settings/backup') ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-brand-primary">
                                        <i class="bi bi-database-add me-1"></i> Generate Backup Now
                                    </button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Backup File</th>
                                            <th>File Size</th>
                                            <th>Creation Date</th>
                                            <th class="text-end">Download</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($backupFiles)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">No database backups generated yet. Click "Generate Backup Now" above.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($backupFiles as $bf): ?>
                                            <tr>
                                                <td class="font-monospace fw-bold"><i class="bi bi-file-earmark-code me-2 text-danger"></i><?= e($bf['filename']) ?></td>
                                                <td><?= e($bf['size']) ?></td>
                                                <td class="small text-muted"><?= e($bf['date']) ?></td>
                                                <td class="text-end">
                                                    <a href="<?= url('settings/download-backup?file=' . urlencode($bf['filename'])) ?>" class="btn btn-sm btn-outline-dark">
                                                        <i class="bi bi-download me-1"></i> Download SQL
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
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Auto-switch tab if hash present in URL (e.g., #tab-smtp)
    const hash = window.location.hash;
    if (hash) {
        const triggerEl = document.querySelector(`#settingsTabs button[data-bs-target="${hash}"]`);
        if (triggerEl) {
            const tab = new bootstrap.Tab(triggerEl);
            tab.show();
        }
    }

    // 2. SMTP Connection Test Handler
    const btnTest = document.getElementById('btnTestSmtpConnection');
    const testResult = document.getElementById('smtpTestResult');
    const spinner = document.getElementById('testSmtpSpinner');
    const icon = document.getElementById('testSmtpIcon');

    if (btnTest) {
        btnTest.addEventListener('click', async () => {
            const host = document.getElementById('smtp_host')?.value || '';
            const port = document.getElementById('smtp_port')?.value || '587';
            const enc = document.getElementById('smtp_encryption')?.value || 'tls';
            const user = document.getElementById('smtp_username')?.value || '';
            const pass = document.getElementById('smtp_password')?.value || '';
            const testEmail = document.getElementById('testEmailRecipient')?.value || '';

            btnTest.disabled = true;
            if (spinner) spinner.style.display = 'inline-block';
            if (icon) icon.style.display = 'none';

            if (testResult) {
                testResult.className = 'alert alert-info small mt-3';
                testResult.style.display = 'block';
                testResult.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Connecting to SMTP server & verifying credentials...';
            }

            try {
                const res = await apiRequest(`${window.APP_URL || ''}/api/settings/test-smtp`, 'POST', {
                    smtp_host: host,
                    smtp_port: port,
                    smtp_encryption: enc,
                    smtp_username: user,
                    smtp_password: pass,
                    test_email: testEmail
                });

                if (res.ok && res.data && res.data.success) {
                    if (testResult) {
                        testResult.className = 'alert alert-success small mt-3';
                        testResult.innerHTML = `<strong><i class="bi bi-check-circle-fill me-1"></i> Success:</strong> ${res.data.message || 'SMTP connected and verified!'}`;
                    }
                    showToast(res.data.message || 'SMTP connection successful!', 'success');
                } else {
                    const err = res.data?.error || 'Connection failed. Please check host, port, and credentials.';
                    if (testResult) {
                        testResult.className = 'alert alert-danger small mt-3';
                        testResult.innerHTML = `<strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Connection Failed:</strong> ${err}`;
                    }
                    showToast(err, 'error');
                }
            } catch (e) {
                if (testResult) {
                    testResult.className = 'alert alert-danger small mt-3';
                    testResult.innerHTML = `<strong><i class="bi bi-x-circle-fill me-1"></i> Error:</strong> ${e.message}`;
                }
            } finally {
                btnTest.disabled = false;
                if (spinner) spinner.style.display = 'none';
                if (icon) icon.style.display = 'inline-block';
            }
        });
    }
});
</script>
