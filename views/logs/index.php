<?php
/**
 * Sagar Advertising CRM - Activity Audit Log View
 */
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;">System Activity & Audit Log</h2>
        <p class="text-muted mb-0">Complete historical security timeline tracking user actions, quotations, logins, and exports.</p>
    </div>
</div>

<div class="table-brand-wrap">
    <div class="table-responsive">
        <table class="table-brand table">
            <thead>
                <tr>
                    <th style="width: 160px;">Timestamp</th>
                    <th>User / Staff</th>
                    <th>Action</th>
                    <th>Module</th>
                    <th>Audit Details</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No activity logs recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                    <tr>
                        <td class="small text-muted font-monospace">
                            <?= date('d M Y, h:i:s A', strtotime($l['created_at'])) ?>
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?= e($l['user_name'] ?? 'System / Anonymous') ?></div>
                        </td>
                        <td>
                            <span class="badge bg-dark font-monospace"><?= e($l['action']) ?></span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= e($l['module']) ?></span>
                        </td>
                        <td class="small text-dark" style="max-width: 400px;">
                            <?= e($l['details']) ?>
                        </td>
                        <td class="small text-muted font-monospace">
                            <?= e($l['ip_address']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
