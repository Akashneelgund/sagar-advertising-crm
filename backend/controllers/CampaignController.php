<?php
/**
 * Sagar Advertising CRM - Email Campaigns & Queue Controller
 */

declare(strict_types=1);

require_once ROOT_PATH . '/backend/core/Controller.php';
require_once ROOT_PATH . '/backend/services/MailerService.php';

class CampaignController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $campaigns = DB::fetchAll(
            "SELECT c.*, t.name as template_name
             FROM `email_campaigns` c
             LEFT JOIN `email_templates` t ON c.template_id = t.id
             ORDER BY c.created_at DESC"
        );

        $templates = DB::fetchAll("SELECT * FROM `email_templates` ORDER BY category ASC, name ASC");

        $totalSent = (int)DB::fetchColumn("SELECT COUNT(*) FROM `email_logs` WHERE status = 'sent'");
        $totalFailed = (int)DB::fetchColumn("SELECT COUNT(*) FROM `email_logs` WHERE status = 'failed'");
        $unsubs = (int)DB::fetchColumn("SELECT COUNT(*) FROM `email_unsubscribes`");
        $smtpMode = DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'smtp_mode'") ?: 'simulate';
        $smtpHost = DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'smtp_host'") ?: 'smtp.gmail.com';
        $smtpUser = DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'smtp_username'") ?: 'sagaradvertising7@gmail.com';

        $this->view('campaigns/index', [
            'campaigns'   => $campaigns,
            'templates'   => $templates,
            'totalSent'   => $totalSent,
            'totalFailed' => $totalFailed,
            'unsubs'      => $unsubs,
            'smtpMode'    => $smtpMode,
            'smtpHost'    => $smtpHost,
            'smtpUser'    => $smtpUser
        ]);
    }

    public function builder(): void {
        $this->requirePermission('campaigns.send');

        $templates = DB::fetchAll("SELECT * FROM `email_templates` ORDER BY name ASC");
        $cities = DB::fetchAll("SELECT DISTINCT city FROM `customers` WHERE deleted_at IS NULL AND city != '' ORDER BY city ASC");
        $types = ['Business', 'Corporate', 'Dealer', 'Individual', 'Existing Client', 'New Client'];

        $totalEligible = (int)DB::fetchColumn("SELECT COUNT(*) FROM `customers` WHERE deleted_at IS NULL AND marketing_opt_in = 1 AND email IS NOT NULL AND email != ''");

        $this->view('campaigns/builder', [
            'templates'     => $templates,
            'cities'        => array_column($cities, 'city'),
            'types'         => $types,
            'totalEligible' => $totalEligible
        ]);
    }

    public function store(): void {
        $this->requirePermission('campaigns.send');
        $this->validateCsrf();

        $name = trim((string)$this->input('name'));
        $subject = trim((string)$this->input('subject'));
        $body = trim((string)$this->input('body_html'));
        $filterType = $this->input('recipient_filter', 'all');
        $filterValue = trim((string)$this->input('filter_value', ''));

        if (empty($name) || empty($subject) || empty($body)) {
            Session::setFlash('error', 'Campaign name, subject, and message content are required.');
            $this->redirect('campaigns/builder');
        }

        // Query targeted customers
        $query = "SELECT id, contact_person, company_name, email FROM `customers` WHERE deleted_at IS NULL AND marketing_opt_in = 1 AND email IS NOT NULL AND email != ''";
        $params = [];

        if ($filterType === 'city' && !empty($filterValue)) {
            $query .= " AND city = ?";
            $params[] = $filterValue;
        } elseif ($filterType === 'type' && !empty($filterValue)) {
            $query .= " AND customer_type = ?";
            $params[] = $filterValue;
        } elseif ($filterType === 'status' && !empty($filterValue)) {
            $query .= " AND status = ?";
            $params[] = $filterValue;
        }

        $recipients = DB::fetchAll($query, $params);
        $totalRecip = count($recipients);

        if ($totalRecip === 0) {
            Session::setFlash('error', 'No eligible customers with valid emails match your filter.');
            $this->redirect('campaigns/builder');
        }

        $pdo = DB::connect();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("INSERT INTO `email_campaigns` (
                `name`, `subject`, `template_id`, `from_name`, `from_email`, `body_html`,
                `recipient_filter`, `total_recipients`, `pending_count`, `status`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'queued')");

            $stmt->execute([
                $name,
                $subject,
                $this->input('template_id') ?: null,
                $this->input('from_name') ?: 'Sagar Advertising',
                $this->input('from_email') ?: 'sagaradvertising7@gmail.com',
                $body,
                $filterType . ($filterValue ? ": {$filterValue}" : ''),
                $totalRecip,
                $totalRecip
            ]);

            $campaignId = (int)$pdo->lastInsertId();

            // Insert queue rows
            $recipStmt = $pdo->prepare("INSERT INTO `email_campaign_recipients` (
                `campaign_id`, `customer_id`, `recipient_email`, `recipient_name`, `status`
            ) VALUES (?, ?, ?, ?, 'pending')");

            foreach ($recipients as $r) {
                $recipStmt->execute([
                    $campaignId,
                    $r['id'],
                    $r['email'],
                    $r['contact_person'] ?: $r['company_name']
                ]);
            }

            $pdo->commit();
            AuditLogger::log('CAMPAIGN_CREATE', 'Campaigns', $campaignId, "Created email campaign '{$name}' with {$totalRecip} recipients.");
            Session::setFlash('success', "Campaign created! Ready to dispatch to {$totalRecip} recipients.");
            $this->redirect('campaigns/queue?id=' . $campaignId);

        } catch (Exception $e) {
            $pdo->rollBack();
            Session::setFlash('error', "Database error: " . $e->getMessage());
            $this->redirect('campaigns/builder');
        }
    }

    public function queue(): void {
        $this->requireAuth();
        $id = (int)$this->input('id');

        $campaign = DB::fetch("SELECT * FROM `email_campaigns` WHERE id = ?", [$id]);
        if (!$campaign) {
            Session::setFlash('error', 'Campaign not found.');
            $this->redirect('campaigns');
        }

        $recipients = DB::fetchAll(
            "SELECT r.*, c.company_name
             FROM `email_campaign_recipients` r
             LEFT JOIN `customers` c ON r.customer_id = c.id
             WHERE r.campaign_id = ?
             ORDER BY r.status DESC, r.id ASC
             LIMIT 100",
            [$id]
        );

        $smtpMode = DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'smtp_mode'") ?: 'simulate';
        $smtpHost = DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'smtp_host'") ?: 'smtp.gmail.com';
        $smtpUser = DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'smtp_username'") ?: 'sagaradvertising7@gmail.com';

        $this->view('campaigns/queue', [
            'campaign'   => $campaign,
            'recipients' => $recipients,
            'smtp_mode'  => $smtpMode,
            'smtp_host'  => $smtpHost,
            'smtp_user'  => $smtpUser
        ]);
    }

    public function processBatch(): void {
        $this->requirePermission('campaigns.send');

        $campaignId = (int)$this->input('campaign_id');
        $batchSize = (int)$this->input('batch_size', 20);

        try {
            $result = MailerService::processCampaignQueue($campaignId, $batchSize);
            $this->json(array_merge(['success' => true], $result));
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function cancel(): void {
        $this->requirePermission('campaigns.send');

        $campaignId = (int)$this->input('campaign_id');
        DB::query("UPDATE `email_campaigns` SET `status` = 'cancelled' WHERE `id` = ?", [$campaignId]);
        DB::query("UPDATE `email_campaign_recipients` SET `status` = 'failed', `error_message` = 'Cancelled by administrator' WHERE `campaign_id` = ? AND `status` = 'pending'", [$campaignId]);

        AuditLogger::log('CAMPAIGN_CANCEL', 'Campaigns', $campaignId, "Campaign #{$campaignId} cancelled.");
        $this->json(['success' => true, 'message' => 'Campaign cancelled.']);
    }

    public function restartQueue(): void {
        $this->requirePermission('campaigns.send');
        $campaignId = (int)$this->input('campaign_id');
        $campaign = DB::fetch("SELECT * FROM `email_campaigns` WHERE id = ?", [$campaignId]);
        if (!$campaign) {
            $this->json(['success' => false, 'error' => 'Campaign not found.'], 404);
            return;
        }

        // Reset all recipients of this campaign back to pending
        DB::query("UPDATE `email_campaign_recipients` SET `status` = 'pending', `error_message` = NULL, `sent_at` = NULL WHERE `campaign_id` = ?", [$campaignId]);

        // Reset campaign counters and set status back to scheduled
        DB::query("UPDATE `email_campaigns` SET `sent_count` = 0, `failed_count` = 0, `pending_count` = `total_recipients`, `status` = 'scheduled' WHERE `id` = ?", [$campaignId]);

        AuditLogger::log('CAMPAIGN_RESTART', 'Campaigns', $campaignId, "Campaign #{$campaignId} ('{$campaign['name']}') reset and queued for re-dispatch.");

        $this->json([
            'success' => true,
            'message' => 'Campaign queue reset. All ' . $campaign['total_recipients'] . ' recipients are ready for dispatch.'
        ]);
    }

    public function sendTest(): void {
        $this->requirePermission('campaigns.send');
        $campaignId = (int)$this->input('campaign_id');
        $testEmail = trim((string)$this->input('test_email'));

        if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'error' => 'Please provide a valid recipient email address.'], 400);
            return;
        }

        $campaign = DB::fetch("SELECT * FROM `email_campaigns` WHERE id = ?", [$campaignId]);
        if (!$campaign) {
            $this->json(['success' => false, 'error' => 'Campaign not found.'], 404);
            return;
        }

        // Unsubscribe link test
        $unsubToken = md5($testEmail . 'sagar_crm_salt');
        $unsubUrl = url('unsubscribe?email=' . urlencode($testEmail) . '&token=' . $unsubToken);

        $vars = [
            '{{customer_name}}'   => 'Test Client / Vageesh H Hugar',
            '{{company_name}}'    => 'Sagar Advertising Sample Partner',
            '{{email}}'           => $testEmail,
            '{{phone}}'           => '9611620862',
            '{{city}}'            => 'Hubballi',
            '{{unsubscribe_url}}' => $unsubUrl
        ];

        $personalBody = str_replace(array_keys($vars), array_values($vars), $campaign['body_html']);
        $personalSubject = '[TEST PREVIEW] ' . str_replace(array_keys($vars), array_values($vars), $campaign['subject']);

        $res = MailerService::send(
            $testEmail,
            'Test Recipient',
            $personalSubject,
            $personalBody,
            [],
            null,
            $campaignId
        );

        if ($res['success']) {
            $this->json([
                'success' => true,
                'message' => (!empty($res['simulated']))
                    ? "Test email processed in Simulation Mode (Logged to storage/mail_logs). To deliver live emails to client inboxes, switch to Live SMTP in Settings."
                    : "Test email successfully delivered to {$testEmail} via live SMTP!",
                'simulated' => !empty($res['simulated'])
            ]);
        } else {
            $this->json([
                'success' => false,
                'error' => $res['error'] ?? 'Failed to send test email.'
            ], 500);
        }
    }

    public function templates(): void {
        $this->requireAuth();
        $templates = DB::fetchAll("SELECT * FROM `email_templates` ORDER BY category ASC, name ASC");
        $this->view('campaigns/templates', ['templates' => $templates]);
    }

    public function storeTemplate(): void {
        $this->requirePermission('campaigns.send');
        $this->validateCsrf();

        $name = trim((string)$this->input('name'));
        $subject = trim((string)$this->input('subject'));
        $body = trim((string)$this->input('body_html'));
        $cat = $this->input('category', 'promotional');

        if (!empty($name) && !empty($subject) && !empty($body)) {
            DB::query(
                "INSERT INTO `email_templates` (`name`, `category`, `subject`, `body_html`) VALUES (?, ?, ?, ?)",
                [$name, $cat, $subject, $body]
            );
            Session::setFlash('success', "Template '{$name}' saved successfully.");
        }
        $this->redirect('campaigns/templates');
    }

    public function unsubscribe(): void {
        $email = trim((string)$this->input('email'));
        $token = trim((string)$this->input('token'));

        $expected = md5($email . 'sagar_crm_salt');
        if (!empty($email) && hash_equals($expected, $token)) {
            DB::query("INSERT IGNORE INTO `email_unsubscribes` (`email`) VALUES (?)", [$email]);
            DB::query("UPDATE `customers` SET `marketing_opt_in` = 0 WHERE `email` = ?", [$email]);
            $msg = "You have been successfully unsubscribed from Sagar Advertising marketing communications.";
        } else {
            $msg = "Invalid unsubscribe link.";
        }

        echo "<div style='font-family:sans-serif; text-align:center; padding:60px 20px;'>";
        echo "<h2 style='color:#FF5500;'>SAGAR ADVERTISING</h2>";
        echo "<p style='color:#333; font-size:16px;'>{$msg}</p>";
        echo "</div>";
        exit;
    }
}
