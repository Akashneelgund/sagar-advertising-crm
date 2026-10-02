<?php
/**
 * Sagar Advertising CRM - Campaign Queue Runner & Monitor
 */
$total = max(1, $campaign['total_recipients']);
$percent = min(100, round(($campaign['sent_count'] / $total) * 100));
$isFinished = in_array($campaign['status'], ['completed', 'cancelled']);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('campaigns') ?>" class="btn btn-sm btn-outline-secondary" title="Back to Campaigns">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h3 class="fw-black mb-0" style="font-family: 'Space Grotesk', sans-serif;">
                Queue Monitor: <?= e($campaign['name']) ?>
            </h3>
            <span class="text-muted small">Subject: <?= e($campaign['subject']) ?></span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalTestEmail">
            <i class="bi bi-envelope-paper-fill me-1 text-warning"></i> Send Test Email
        </button>
        <span class="status-badge" id="queueStatusBadge"><?= get_status_badge($campaign['status']) ?></span>
    </div>
</div>

<!-- Delivery Mode Banner -->
<?php if (($smtp_mode ?? 'simulate') === 'simulate'): ?>
    <div class="alert alert-warning border-warning shadow-sm d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="fs-2 text-warning"><i class="bi bi-shield-exclamation"></i></div>
            <div>
                <strong class="d-block text-dark">Current Mail Mode: Offline Simulation Mode</strong>
                <span class="text-muted small">
                    Dispatched emails will be safely written to <code>storage/mail_logs/</code> for previewing without sending external emails.
                    To deliver live emails to client inboxes, switch to <strong>Live SMTP</strong> in Settings.
                </span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('settings') ?>" class="btn btn-sm btn-dark text-nowrap">
                <i class="bi bi-gear-fill me-1"></i> Configure Live SMTP
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-success border-success shadow-sm d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="fs-2 text-success"><i class="bi bi-shield-check"></i></div>
            <div>
                <strong class="d-block text-dark">Current Mail Mode: Live SMTP Dispatch</strong>
                <span class="text-muted small">
                    Connecting to <strong><?= e($smtp_host ?? 'smtp.gmail.com') ?></strong> as <code><?= e($smtp_user ?? '') ?></code>.
                </span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('settings') ?>" class="btn btn-sm btn-outline-success text-nowrap">
                <i class="bi bi-sliders me-1"></i> SMTP Settings
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Queue Dispatch Controls & Metrics -->
<div class="card-brand mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center mb-3">
            <div class="col-12 col-md-6">
                <h5 class="fw-bold mb-1">Queue Dispatch Progress</h5>
                <p class="text-muted small mb-0">Emails are delivered in safe rate-limited batches to prevent provider rate limits.</p>
            </div>
            <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2 align-items-center">
                <?php if (!$isFinished): ?>
                    <button type="button" class="btn btn-brand-primary" id="btnStartQueue">
                        <i class="bi bi-play-fill me-1"></i> Start Batch Dispatch
                    </button>
                    <button type="button" class="btn btn-warning" id="btnPauseQueue" style="display: none;">
                        <i class="bi bi-pause-fill me-1"></i> Pause Queue
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="btnCancelQueue">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                <?php else: ?>
                    <span class="badge bg-success-subtle text-success fs-6 p-2 me-2">
                        <i class="bi bi-check-circle-fill me-1"></i> <?= ucfirst($campaign['status']) ?>
                    </span>
                <?php endif; ?>

                <button type="button" class="btn btn-outline-primary" id="btnRestartQueue" title="Reset all recipients to pending and re-run dispatch">
                    <i class="bi bi-arrow-repeat me-1"></i> Reset & Re-dispatch
                </button>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="progress mb-4" style="height: 18px; border-radius: 9px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success fw-bold" id="queueProgressBar" role="progressbar" style="width: <?= $percent ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100">
                <?= $percent ?>%
            </div>
        </div>

        <!-- Counters -->
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <div class="p-3 border rounded bg-light">
                    <div class="text-muted small fw-bold">TOTAL TARGET</div>
                    <div class="fs-4 fw-bold text-dark" id="queueTotalCount"><?= $campaign['total_recipients'] ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 border rounded bg-light">
                    <div class="text-muted small fw-bold text-success">SENT SUCCESSFULLY</div>
                    <div class="fs-4 fw-bold text-success" id="queueSentCount"><?= $campaign['sent_count'] ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 border rounded bg-light">
                    <div class="text-muted small fw-bold text-warning">QUEUED / PENDING</div>
                    <div class="fs-4 fw-bold text-warning" id="queuePendingCount"><?= $campaign['pending_count'] ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 border rounded bg-light">
                    <div class="text-muted small fw-bold text-danger">DELIVERY FAILED</div>
                    <div class="fs-4 fw-bold text-danger" id="queueFailedCount"><?= $campaign['failed_count'] ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recipients Queue Table -->
<div class="table-brand-wrap mb-4">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Recipient Delivery Logs (Top 100)</h5>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.location.reload();">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh Table
        </button>
    </div>
    <div class="table-responsive">
        <table class="table-brand table">
            <thead>
                <tr>
                    <th>Recipient Client</th>
                    <th>Email Address</th>
                    <th>Status</th>
                    <th>Sent Timestamp</th>
                    <th>Error / Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recipients)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No recipients associated with this campaign.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recipients as $recip): ?>
                    <tr>
                        <td class="fw-bold text-dark">
                            <?= e($recip['recipient_name']) ?>
                            <?php if ($recip['company_name']): ?>
                                <span class="text-muted small">(<?= e($recip['company_name']) ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($recip['recipient_email']) ?></td>
                        <td><?= get_status_badge($recip['status']) ?></td>
                        <td class="small text-muted"><?= $recip['sent_at'] ? date('d M Y, h:i A', strtotime($recip['sent_at'])) : '-' ?></td>
                        <td class="small text-danger"><?= e($recip['error_message'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Test Email Modal -->
<div class="modal fade" id="modalTestEmail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-envelope-paper-fill me-2 text-warning"></i> Send Test Campaign Email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    Send a single test email of this campaign with sample variable replacements to verify formatting, layout, and deliverability before running the full batch.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Recipient Email Address</label>
                    <input type="email" class="form-control" id="testEmailInput" placeholder="your.email@example.com" value="<?= e($smtp_user ?? 'sagaradvertising7@gmail.com') ?>">
                </div>
                <div id="testEmailAlert" style="display:none;" class="alert small mb-0"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-brand-primary" id="btnSubmitTestEmail">
                    <span id="testEmailSpinner" class="spinner-border spinner-border-sm me-1" style="display:none;"></span>
                    <i class="bi bi-send-fill me-1" id="testEmailIcon"></i> Send Test Now
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('js/campaign-queue.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.campaignRunner = new CampaignQueueRunner(<?= $campaign['id'] ?>);
    });
</script>
