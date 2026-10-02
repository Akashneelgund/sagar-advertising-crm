<?php
/**
 * Sagar Advertising CRM - Mailer & Queue Dispatch Service
 * Handles SMTP email transmission, attachment encoding, campaign queue execution, personalization, and unsubscribe compliance.
 */

declare(strict_types=1);

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/backend/services/AuditLogger.php';

class MailerService {
    /**
     * Send single email (either live via SMTP or simulated in storage/mail_logs/)
     */
    public static function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $bodyHtml,
        array $attachments = [], // [ ['path' => ..., 'name' => ...], ... ]
        ?int $quotationId = null,
        ?int $campaignId = null
    ): array {
        // Retrieve SMTP settings
        $settings = DB::fetchAll("SELECT setting_key, setting_value FROM `settings` WHERE `category` IN ('smtp', 'company')");
        $config = [];
        foreach ($settings as $s) {
            $config[$s['setting_key']] = $s['setting_value'];
        }

        $smtpMode = $config['smtp_mode'] ?? 'simulate';
        $fromEmail = $config['smtp_from_email'] ?? 'sagaradvertising7@gmail.com';
        $fromName = $config['smtp_from_name'] ?? 'Sagar Advertising';

        $status = 'sent';
        $errorMessage = null;

        // Check if recipient is unsubscribed if this is a marketing campaign
        if ($campaignId !== null) {
            $isUnsub = DB::fetchColumn("SELECT COUNT(*) FROM `email_unsubscribes` WHERE `email` = ?", [$toEmail]);
            if ($isUnsub > 0) {
                return ['success' => false, 'error' => 'Recipient has unsubscribed from marketing emails.'];
            }
        }

        // If live SMTP is requested
        if ($smtpMode === 'live') {
            if (empty($config['smtp_host'])) {
                $status = 'failed';
                $errorMessage = 'SMTP Host is not configured. Please check Settings -> Mail & SMTP Config.';
                $providerMsg = 'Configuration Error: Missing SMTP Host';
            } elseif (empty($config['smtp_password'])) {
                $status = 'failed';
                $errorMessage = 'SMTP Password or App Password is not configured in Settings. For Gmail, generate a 16-letter Google App Password.';
                $providerMsg = 'Configuration Error: Missing SMTP Password';
            } else {
                try {
                    self::sendViaSocketSmtp($config, $toEmail, $toName, $subject, $bodyHtml, $attachments);
                    $providerMsg = 'Delivered via live SMTP server ' . $config['smtp_host'];
                } catch (Exception $e) {
                    $status = 'failed';
                    $errorMessage = $e->getMessage();
                    $providerMsg = 'SMTP Error: ' . $errorMessage;
                }
            }
        } else {
            // Simulation mode for reliable offline testing and staging
            $simLog = [
                'timestamp'   => date('Y-m-d H:i:s'),
                'to'          => "{$toName} <{$toEmail}>",
                'from'        => "{$fromName} <{$fromEmail}>",
                'subject'     => $subject,
                'attachments' => array_column($attachments, 'name'),
                'body'        => $bodyHtml
            ];
            $logFile = MAIL_LOG_PATH . '/mail_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 6) . '.json';
            file_put_contents($logFile, json_encode($simLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $providerMsg = "Simulated delivery (Logged to storage/mail_logs)";
        }

        // Log to database email_logs
        DB::query(
            "INSERT INTO `email_logs` (`campaign_id`, `quotation_id`, `recipient_email`, `subject`, `status`, `provider_message`)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$campaignId, $quotationId, $toEmail, $subject, $status, $providerMsg]
        );

        if ($status === 'failed') {
            return ['success' => false, 'error' => $errorMessage, 'mode' => $smtpMode];
        }

        return ['success' => true, 'message' => $providerMsg, 'mode' => $smtpMode, 'simulated' => ($smtpMode === 'simulate')];
    }

    /**
     * Send Quotation Email with Auto-Attached PDF
     */
    public static function sendQuotationEmail(
        int $quotationId,
        string $toEmail,
        string $subject,
        string $messageText,
        ?string $cc = null
    ): array {
        $quote = DB::fetch("SELECT q.*, c.company_name, c.contact_person FROM `quotations` q JOIN `customers` c ON q.customer_id = c.id WHERE q.id = ?", [$quotationId]);
        if (!$quote) {
            throw new Exception("Quotation not found.");
        }

        $items = DB::fetchAll("SELECT * FROM `quotation_items` WHERE `quotation_id` = ? ORDER BY `sort_order` ASC", [$quotationId]);
        $customer = DB::fetch("SELECT * FROM `customers` WHERE `id` = ?", [$quote['customer_id']]);
        $companyRows = DB::fetchAll("SELECT setting_key, setting_value FROM `settings` WHERE `category` = 'company'");
        $company = [];
        foreach ($companyRows as $cr) {
            $company[$cr['setting_key']] = $cr['setting_value'];
        }

        // 1. Generate branded HTML / PDF
        require_once ROOT_PATH . '/backend/services/PdfGenerator.php';
        $html = PdfGenerator::generateHtml($quote, $items, $customer, $company);
        $savedPath = PdfGenerator::saveToFile($html, $quote['quotation_number']);

        $attachments = [
            [
                'path' => $savedPath,
                'name' => "Quotation_{$quote['quotation_number']}.html"
            ]
        ];

        // Replace template variables
        $webUrl = url('quote/view/' . ($quote['view_token'] ?: $quote['quotation_number']));
        $replacements = [
            '{{customer_name}}'     => $customer['contact_person'] ?: $customer['company_name'],
            '{{company_name}}'      => $customer['company_name'],
            '{{quotation_number}}'  => $quote['quotation_number'],
            '{{grand_total}}'       => number_format((float)$quote['grand_total'], 2),
            '{{valid_until}}'       => date('d-M-Y', strtotime($quote['valid_until'])),
            '{{project_name}}'      => $quote['project_name'] ?: 'Advertising & Branding Work',
            '{{quotation_web_url}}' => $webUrl
        ];
        $body = str_replace(array_keys($replacements), array_values($replacements), $messageText);

        $res = self::send($toEmail, $customer['contact_person'], $subject, $body, $attachments, $quotationId);

        if ($res['success']) {
            // Update quotation status to Sent if it was Draft
            if ($quote['status'] === 'Draft') {
                DB::query("UPDATE `quotations` SET `status` = 'Sent', `sent_at` = NOW() WHERE `id` = ?", [$quotationId]);
                DB::query("INSERT INTO `quotation_status_history` (`quotation_id`, `user_id`, `old_status`, `new_status`, `comments`) VALUES (?, ?, 'Draft', 'Sent', 'Quotation dispatched via email with attached proposal document.')", [$quotationId, Session::userId()]);
            }
            AuditLogger::log('EMAIL_SEND', 'Quotations', $quotationId, "Sent quotation email to {$toEmail}");
        }

        return $res;
    }

    /**
     * Process Batch of Campaign Queue
     *
     * @param int $campaignId Campaign ID
     * @param int $batchSize Number of emails to process per tick
     * @return array [processed => count, remaining => count, completed => bool]
     */
    public static function processCampaignQueue(int $campaignId, int $batchSize = 25): array {
        $campaign = DB::fetch("SELECT * FROM `email_campaigns` WHERE `id` = ?", [$campaignId]);
        if (!$campaign) {
            throw new Exception("Campaign not found.");
        }

        if (in_array($campaign['status'], ['completed', 'cancelled', 'paused'])) {
            return [
                'processed' => 0,
                'status'    => $campaign['status'],
                'completed' => ($campaign['status'] === 'completed')
            ];
        }

        // Set status to processing
        DB::query("UPDATE `email_campaigns` SET `status` = 'processing' WHERE `id` = ?", [$campaignId]);

        // Fetch pending recipients
        $recipients = DB::fetchAll(
            "SELECT r.*, c.contact_person, c.company_name, c.city, c.mobile, c.marketing_opt_in
             FROM `email_campaign_recipients` r
             LEFT JOIN `customers` c ON r.customer_id = c.id
             WHERE r.campaign_id = ? AND r.status = 'pending'
             LIMIT ?",
            [$campaignId, $batchSize]
        );

        $processed = 0;
        foreach ($recipients as $recip) {
            // Check opt-in
            if (isset($recip['marketing_opt_in']) && (int)$recip['marketing_opt_in'] === 0) {
                DB::query("UPDATE `email_campaign_recipients` SET `status` = 'unsubscribed', `error_message` = 'Customer opted out of marketing' WHERE `id` = ?", [$recip['id']]);
                continue;
            }

            // Unsubscribe link
            $unsubToken = md5($recip['recipient_email'] . 'sagar_crm_salt');
            $unsubUrl = url('unsubscribe?email=' . urlencode($recip['recipient_email']) . '&token=' . $unsubToken);

            // Replace variables
            $vars = [
                '{{customer_name}}'   => $recip['recipient_name'] ?: ($recip['contact_person'] ?: 'Valued Client'),
                '{{company_name}}'    => $recip['company_name'] ?: 'Your Business',
                '{{email}}'           => $recip['recipient_email'],
                '{{phone}}'           => $recip['mobile'] ?: '',
                '{{city}}'            => $recip['city'] ?: 'Hubballi',
                '{{unsubscribe_url}}' => $unsubUrl
            ];

            $personalBody = str_replace(array_keys($vars), array_values($vars), $campaign['body_html']);
            $personalSubject = str_replace(array_keys($vars), array_values($vars), $campaign['subject']);

            $res = self::send(
                $recip['recipient_email'],
                $recip['recipient_name'],
                $personalSubject,
                $personalBody,
                [],
                null,
                $campaignId
            );

            if ($res['success']) {
                DB::query("UPDATE `email_campaign_recipients` SET `status` = 'sent', `sent_at` = NOW() WHERE `id` = ?", [$recip['id']]);
            } else {
                DB::query("UPDATE `email_campaign_recipients` SET `status` = 'failed', `error_message` = ? WHERE `id` = ?", [$res['error'], $recip['id']]);
            }
            $processed++;
        }

        // Recount counts
        $sentCount = (int)DB::fetchColumn("SELECT COUNT(*) FROM `email_campaign_recipients` WHERE `campaign_id` = ? AND `status` = 'sent'", [$campaignId]);
        $failedCount = (int)DB::fetchColumn("SELECT COUNT(*) FROM `email_campaign_recipients` WHERE `campaign_id` = ? AND `status` = 'failed'", [$campaignId]);
        $pendingCount = (int)DB::fetchColumn("SELECT COUNT(*) FROM `email_campaign_recipients` WHERE `campaign_id` = ? AND `status` = 'pending'", [$campaignId]);

        $newStatus = ($pendingCount === 0) ? 'completed' : 'processing';

        DB::query(
            "UPDATE `email_campaigns` SET `sent_count` = ?, `failed_count` = ?, `pending_count` = ?, `status` = ? WHERE `id` = ?",
            [$sentCount, $failedCount, $pendingCount, $newStatus, $campaignId]
        );

        return [
            'processed' => $processed,
            'sent'      => $sentCount,
            'failed'    => $failedCount,
            'pending'   => $pendingCount,
            'status'    => $newStatus,
            'completed' => ($newStatus === 'completed')
        ];
    }

    /**
     * Native Lightweight SMTP Socket Transmission with Full RFC Response Handling
     */
    public static function sendViaSocketSmtp(
        array $config,
        string $toEmail,
        string $toName,
        string $subject,
        string $bodyHtml,
        array $attachments = []
    ): void {
        $host = trim($config['smtp_host'] ?? 'smtp.gmail.com');
        $port = (int)($config['smtp_port'] ?: 587);
        $user = trim($config['smtp_username'] ?? '');
        $pass = trim($config['smtp_password'] ?? '');
        $enc  = strtolower($config['smtp_encryption'] ?? 'tls');

        $socketHost = ($enc === 'ssl' ? 'ssl://' : '') . $host;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $socket = @stream_socket_client(
            "{$socketHost}:{$port}",
            $errno,
            $errstr,
            15,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            throw new Exception("Could not connect to SMTP server {$host}:{$port} - {$errstr} ({$errno})");
        }

        stream_set_timeout($socket, 15);

        // Helper to read multi-line SMTP responses
        $readResponse = function() use ($socket): string {
            $response = "";
            while (!feof($socket)) {
                $line = fgets($socket, 515);
                if ($line === false) break;
                $response .= $line;
                if (strlen($line) >= 4 && $line[3] === ' ') break;
                if (strlen(trim($line)) === 3) break;
            }
            return $response;
        };

        // Helper to send command and check status code
        $cmd = function(string $c, array $expectedCodes = [250]) use ($socket, $readResponse): string {
            fputs($socket, $c . "\r\n");
            $res = $readResponse();
            $code = (int)substr(trim($res), 0, 3);
            if (!empty($expectedCodes) && !in_array($code, $expectedCodes, true)) {
                throw new Exception("SMTP command [{$c}] error: " . trim($res));
            }
            return $res;
        };

        $banner = $readResponse();
        $code = (int)substr(trim($banner), 0, 3);
        if ($code !== 220) {
            throw new Exception("SMTP invalid server greeting: " . trim($banner));
        }

        $cmd("EHLO localhost", [250]);

        if ($enc === 'tls') {
            $cmd("STARTTLS", [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                throw new Exception("Failed to establish TLS encryption with SMTP server.");
            }
            $cmd("EHLO localhost", [250]);
        }

        if (!empty($user) && !empty($pass)) {
            $cmd("AUTH LOGIN", [334]);
            $cmd(base64_encode($user), [334]);
            $authRes = $cmd(base64_encode($pass), [235]);
        }

        $from = $config['smtp_from_email'] ?: 'sagaradvertising7@gmail.com';
        $cmd("MAIL FROM: <{$from}>", [250]);
        $cmd("RCPT TO: <{$toEmail}>", [250, 251]);
        $cmd("DATA", [354]);

        // Headers
        $boundary = "====_SA_BOUNDARY_" . md5(uniqid((string)mt_rand(), true)) . "_====";
        $headers  = "From: =?UTF-8?B?" . base64_encode($config['smtp_from_name'] ?: 'Sagar Advertising') . "?= <{$from}>\r\n";
        $headers .= "To: =?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($bodyHtml)) . "\r\n";

        foreach ($attachments as $att) {
            if (!empty($att['path']) && file_exists($att['path'])) {
                $attData = file_get_contents($att['path']);
                $attName = basename($att['name'] ?? $att['path']);
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Type: application/octet-stream; name=\"{$attName}\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n";
                $body .= "Content-Disposition: attachment; filename=\"{$attName}\"\r\n\r\n";
                $body .= chunk_split(base64_encode($attData)) . "\r\n";
            }
        }
        $body .= "--{$boundary}--\r\n";

        fputs($socket, $headers . "\r\n" . $body . "\r\n.\r\n");
        $dataRes = $readResponse();
        $dataCode = (int)substr(trim($dataRes), 0, 3);
        if ($dataCode !== 250) {
            throw new Exception("SMTP DATA send failed: " . trim($dataRes));
        }

        $cmd("QUIT", [221, 250]);
        fclose($socket);
    }

    /**
     * Test SMTP Connection & Authentication Only
     */
    public static function testSmtpConnection(array $config): array {
        try {
            $host = trim($config['smtp_host'] ?? 'smtp.gmail.com');
            $port = (int)($config['smtp_port'] ?: 587);
            $user = trim($config['smtp_username'] ?? '');
            $pass = trim($config['smtp_password'] ?? '');
            $enc  = strtolower($config['smtp_encryption'] ?? 'tls');

            $socketHost = ($enc === 'ssl' ? 'ssl://' : '') . $host;
            $context = stream_context_create([
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
            ]);

            $socket = @stream_socket_client("{$socketHost}:{$port}", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
            if (!$socket) {
                return ['success' => false, 'error' => "Cannot connect to {$host}:{$port} - {$errstr} ({$errno})"];
            }

            stream_set_timeout($socket, 10);
            $readResponse = function() use ($socket): string {
                $response = "";
                while (!feof($socket)) {
                    $line = fgets($socket, 515);
                    if ($line === false) break;
                    $response .= $line;
                    if (strlen($line) >= 4 && $line[3] === ' ') break;
                    if (strlen(trim($line)) === 3) break;
                }
                return $response;
            };

            $banner = $readResponse();
            fputs($socket, "EHLO localhost\r\n");
            $readResponse();

            if ($enc === 'tls') {
                fputs($socket, "STARTTLS\r\n");
                $readResponse();
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                    fclose($socket);
                    return ['success' => false, 'error' => 'TLS encryption negotiation failed with server.'];
                }
                fputs($socket, "EHLO localhost\r\n");
                $readResponse();
            }

            if (!empty($user) && !empty($pass)) {
                fputs($socket, "AUTH LOGIN\r\n");
                $readResponse();
                fputs($socket, base64_encode($user) . "\r\n");
                $readResponse();
                fputs($socket, base64_encode($pass) . "\r\n");
                $authRes = $readResponse();
                $code = (int)substr(trim($authRes), 0, 3);
                if ($code !== 235) {
                    fclose($socket);
                    return ['success' => false, 'error' => "SMTP Authentication Failed: " . trim($authRes)];
                }
            }

            fputs($socket, "QUIT\r\n");
            fclose($socket);

            return ['success' => true, 'message' => "Successfully connected and authenticated to {$host}:{$port}!"];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
