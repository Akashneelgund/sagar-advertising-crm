/**
 * Sagar Advertising CRM - Campaign Queue Manager & Dispatch Runner
 */

class CampaignQueueRunner {
    constructor(campaignId) {
        this.campaignId = campaignId;
        this.isRunning = false;
        this.pollInterval = null;

        this.progressBar = document.getElementById('queueProgressBar');
        this.btnStart = document.getElementById('btnStartQueue');
        this.btnPause = document.getElementById('btnPauseQueue');
        this.btnCancel = document.getElementById('btnCancelQueue');
        this.btnRestart = document.getElementById('btnRestartQueue');
        this.btnSubmitTest = document.getElementById('btnSubmitTestEmail');
        this.testEmailInput = document.getElementById('testEmailInput');
        this.testEmailAlert = document.getElementById('testEmailAlert');
        this.testEmailSpinner = document.getElementById('testEmailSpinner');
        this.testEmailIcon = document.getElementById('testEmailIcon');

        this.statusBadge = document.getElementById('queueStatusBadge');
        this.statSent = document.getElementById('queueSentCount');
        this.statPending = document.getElementById('queuePendingCount');
        this.statFailed = document.getElementById('queueFailedCount');

        this.bindEvents();
    }

    bindEvents() {
        if (this.btnStart) {
            this.btnStart.addEventListener('click', () => this.startProcessing());
        }
        if (this.btnPause) {
            this.btnPause.addEventListener('click', () => this.pauseProcessing());
        }
        if (this.btnCancel) {
            this.btnCancel.addEventListener('click', () => this.cancelCampaign());
        }
        if (this.btnRestart) {
            this.btnRestart.addEventListener('click', () => this.restartCampaign());
        }
        if (this.btnSubmitTest) {
            this.btnSubmitTest.addEventListener('click', () => this.sendTestEmail());
        }
    }

    async restartCampaign() {
        if (!confirm('Are you sure you want to reset all recipients to pending and restart this campaign?')) {
            return;
        }

        if (this.btnRestart) {
            this.btnRestart.disabled = true;
            this.btnRestart.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Resetting...';
        }

        const res = await apiRequest(`${window.APP_URL || ''}/api/campaigns/restart`, 'POST', {
            campaign_id: this.campaignId
        });

        if (res.ok && res.data && res.data.success) {
            showToast(res.data.message || 'Campaign reset successfully!', 'success');
            setTimeout(() => window.location.reload(), 800);
        } else {
            showToast(res.data?.error || 'Failed to reset campaign.', 'error');
            if (this.btnRestart) {
                this.btnRestart.disabled = false;
                this.btnRestart.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Reset & Re-dispatch';
            }
        }
    }

    async sendTestEmail() {
        const email = this.testEmailInput ? this.testEmailInput.value.trim() : '';
        if (!email) {
            this.showTestAlert('Please enter a recipient email address.', 'danger');
            return;
        }

        if (this.btnSubmitTest) this.btnSubmitTest.disabled = true;
        if (this.testEmailSpinner) this.testEmailSpinner.style.display = 'inline-block';
        if (this.testEmailIcon) this.testEmailIcon.style.display = 'none';
        this.showTestAlert('Transmitting test message...', 'info');

        try {
            const res = await apiRequest(`${window.APP_URL || ''}/api/campaigns/send-test`, 'POST', {
                campaign_id: this.campaignId,
                test_email: email
            });

            if (res.ok && res.data && res.data.success) {
                this.showTestAlert(res.data.message || 'Test email dispatched successfully!', 'success');
                showToast(res.data.message || 'Test email sent!', 'success');
            } else {
                const err = res.data?.error || 'Failed to send test email.';
                this.showTestAlert('Error: ' + err, 'danger');
                showToast(err, 'error');
            }
        } catch (e) {
            this.showTestAlert('Network error: ' + e.message, 'danger');
        } finally {
            if (this.btnSubmitTest) this.btnSubmitTest.disabled = false;
            if (this.testEmailSpinner) this.testEmailSpinner.style.display = 'none';
            if (this.testEmailIcon) this.testEmailIcon.style.display = 'inline-block';
        }
    }

    showTestAlert(msg, type) {
        if (!this.testEmailAlert) return;
        this.testEmailAlert.className = `alert alert-${type} small mb-0 mt-3`;
        this.testEmailAlert.style.display = 'block';
        this.testEmailAlert.innerHTML = msg;
    }

    async startProcessing() {
        this.isRunning = true;
        this.btnStart.style.display = 'none';
        if (this.btnPause) this.btnPause.style.display = 'inline-flex';

        showToast('Campaign queue processing initiated.', 'info');
        this.processBatch();
    }

    async processBatch() {
        if (!this.isRunning) return;

        const res = await apiRequest(`${window.APP_URL || ''}/api/campaigns/process-batch`, 'POST', {
            campaign_id: this.campaignId,
            batch_size: 15
        });

        if (res.ok && res.data && res.data.success) {
            const data = res.data;
            this.updateStats(data);

            if (data.completed) {
                this.isRunning = false;
                showToast('🎉 All emails in this campaign have been processed!', 'success');
                if (this.btnPause) this.btnPause.style.display = 'none';
                if (this.statusBadge) {
                    this.statusBadge.className = 'status-badge badge-completed';
                    this.statusBadge.textContent = 'Completed';
                }
                setTimeout(() => window.location.reload(), 1500);
            } else if (this.isRunning) {
                // Schedule next batch tick
                setTimeout(() => this.processBatch(), 1200);
            }
        } else {
            this.isRunning = false;
            showToast(res.data?.error || 'Queue runner encountered an error.', 'error');
            this.btnStart.style.display = 'inline-flex';
            if (this.btnPause) this.btnPause.style.display = 'none';
        }
    }

    pauseProcessing() {
        this.isRunning = false;
        if (this.btnStart) this.btnStart.style.display = 'inline-flex';
        if (this.btnPause) this.btnPause.style.display = 'none';
        showToast('Campaign processing paused.', 'warning');
    }

    async cancelCampaign() {
        if (!confirm('Are you sure you want to cancel the remaining unsent emails in this campaign?')) {
            return;
        }
        this.isRunning = false;
        const res = await apiRequest(`${window.APP_URL || ''}/api/campaigns/cancel`, 'POST', {
            campaign_id: this.campaignId
        });
        if (res.ok && res.data && res.data.success) {
            showToast('Campaign cancelled.', 'info');
            setTimeout(() => window.location.reload(), 1000);
        }
    }

    updateStats(data) {
        if (this.statSent) this.statSent.textContent = data.sent;
        if (this.statPending) this.statPending.textContent = data.pending;
        if (this.statFailed) this.statFailed.textContent = data.failed;

        const total = (data.sent + data.pending + data.failed) || 1;
        const percent = Math.min(100, Math.round(((data.sent + data.failed) / total) * 100));

        if (this.progressBar) {
            this.progressBar.style.width = percent + '%';
            this.progressBar.textContent = percent + '%';
        }
    }
}
