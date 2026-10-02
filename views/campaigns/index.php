<?php
/**
 * Sagar Advertising CRM - Email Campaigns Overview
 */
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;">Email Campaigns & Marketing</h2>
        <p class="text-muted mb-0">Broadcast festival wishes, promotional offers, and branding packages to your customer base.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="<?= url('settings') ?>" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-sliders me-1"></i>
            <span>SMTP Settings</span>
        </a>
        <a href="<?= url('campaigns/templates') ?>" class="btn-brand-outline">
            <i class="bi bi-layout-text-window-reverse"></i>
            <span>Email Templates</span>
        </a>
        <a href="<?= url('campaigns/builder') ?>" class="btn-brand-primary">
            <i class="bi bi-send-fill"></i>
            <span>+ Create Campaign</span>
        </a>
    </div>
</div>

<!-- Delivery Mode Notice Banner -->
<?php if (($smtpMode ?? 'simulate') === 'simulate'): ?>
    <div class="alert alert-warning border-warning shadow-sm d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="fs-3 text-warning"><i class="bi bi-shield-exclamation"></i></div>
            <div>
                <strong class="d-block text-dark">Current Delivery Mode: Offline Simulation Mode</strong>
                <span class="text-muted small">
                    Emails are currently staged in local logs at <code>storage/mail_logs/</code> instead of going to live inboxes.
                    To dispatch real emails to customers via Gmail or your mail provider, switch to <strong>Live SMTP</strong> in Settings.
                </span>
            </div>
        </div>
        <div>
            <a href="<?= url('settings') ?>" class="btn btn-sm btn-dark text-nowrap">
                <i class="bi bi-gear-fill me-1"></i> Switch to Live SMTP
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-success border-success shadow-sm d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="fs-3 text-success"><i class="bi bi-shield-check"></i></div>
            <div>
                <strong class="d-block text-dark">Current Delivery Mode: Live SMTP Dispatch</strong>
                <span class="text-muted small">
                    Emails will be transmitted through <strong><?= e($smtpHost ?? 'smtp.gmail.com') ?></strong> authenticated as <code><?= e($smtpUser ?? '') ?></code>.
                </span>
            </div>
        </div>
        <div>
            <a href="<?= url('settings') ?>" class="btn btn-sm btn-outline-success text-nowrap">
                <i class="bi bi-gear me-1"></i> SMTP Config
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Campaign KPI Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Campaigns</span>
                <div class="stat-icon" style="background:#FFF3E0; color:#FF5500;"><i class="bi bi-megaphone-fill"></i></div>
            </div>
            <div class="stat-value"><?= count($campaigns) ?></div>
            <div class="stat-subtitle text-muted">Created & dispatched</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Emails Delivered</span>
                <div class="stat-icon" style="background:#ECFDF5; color:#059669;"><i class="bi bi-envelope-check-fill"></i></div>
            </div>
            <div class="stat-value text-success"><?= $totalSent ?></div>
            <div class="stat-subtitle text-success"><i class="bi bi-check-all"></i> Successful transmissions</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Delivery Failures</span>
                <div class="stat-icon" style="background:#FEF2F2; color:#DC2626;"><i class="bi bi-exclamation-triangle-fill"></i></div>
            </div>
            <div class="stat-value text-danger"><?= $totalFailed ?></div>
            <div class="stat-subtitle text-muted">Bounced or bad email</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Unsubscribed</span>
                <div class="stat-icon" style="background:#F1F5F9; color:#475569;"><i class="bi bi-person-slash"></i></div>
            </div>
            <div class="stat-value"><?= $unsubs ?></div>
            <div class="stat-subtitle text-muted">Opt-out compliance</div>
        </div>
    </div>
</div>

<!-- Campaigns Table -->
<div class="table-brand-wrap">
    <div class="table-responsive">
        <table class="table-brand table">
            <thead>
                <tr>
                    <th>Campaign Name</th>
                    <th>Subject Line</th>
                    <th>Recipients Target</th>
                    <th>Sent / Total</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($campaigns)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No campaigns launched yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($campaigns as $camp): ?>
                    <?php
                        $total = max(1, $camp['total_recipients']);
                        $percent = min(100, round(($camp['sent_count'] / $total) * 100));
                    ?>
                    <tr>
                        <td>
                            <a href="<?= url('campaigns/queue?id=' . $camp['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                <?= e($camp['name']) ?>
                            </a>
                            <?php if ($camp['template_name']): ?>
                                <div class="small text-muted">Template: <?= e($camp['template_name']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="max-width: 250px;" class="text-truncate fw-bold text-dark" title="<?= e($camp['subject']) ?>">
                            <?= e($camp['subject']) ?>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= e($camp['recipient_filter']) ?></span>
                        </td>
                        <td>
                            <strong class="text-success"><?= $camp['sent_count'] ?></strong> / <span><?= $camp['total_recipients'] ?></span>
                            <?php if ($camp['failed_count'] > 0): ?>
                                <span class="text-danger small">(<?= $camp['failed_count'] ?> failed)</span>
                            <?php endif; ?>
                        </td>
                        <td style="width: 140px;">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percent ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="small text-muted"><?= $percent ?>% complete</span>
                        </td>
                        <td>
                            <?= get_status_badge($camp['status']) ?>
                        </td>
                        <td class="small text-muted">
                            <?= date('d M Y, h:i A', strtotime($camp['created_at'])) ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="<?= url('campaigns/queue?id=' . $camp['id']) ?>" class="btn btn-sm btn-brand-primary" title="View Queue & Send">
                                    <i class="bi bi-play-circle-fill me-1"></i> Queue Runner
                                </a>
                                <button type="button" class="btn btn-sm btn-brand-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <a class="dropdown-item" href="<?= url('campaigns/queue?id=' . $camp['id']) ?>">
                                            <i class="bi bi-speedometer2 me-2 text-primary"></i> Open Queue Monitor
                                        </a>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" onclick="resetCampaign(<?= $camp['id'] ?>)">
                                            <i class="bi bi-arrow-repeat me-2 text-warning"></i> Reset & Re-dispatch
                                        </button>
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

<script>
async function resetCampaign(campaignId) {
    if (!confirm('Are you sure you want to reset all recipients to pending and restart this campaign?')) {
        return;
    }
    const res = await apiRequest(`${window.APP_URL || ''}/api/campaigns/restart`, 'POST', {
        campaign_id: campaignId
    });
    if (res.ok && res.data && res.data.success) {
        showToast(res.data.message || 'Campaign reset successfully!', 'success');
        setTimeout(() => window.location.href = `${window.APP_URL || ''}/campaigns/queue?id=${campaignId}`, 700);
    } else {
        showToast(res.data?.error || 'Failed to reset campaign.', 'error');
    }
}
</script>
