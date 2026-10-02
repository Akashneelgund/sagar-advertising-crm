<?php
/**
 * Sagar Advertising CRM - System Settings & Database Backup Controller
 */

declare(strict_types=1);

require_once ROOT_PATH . '/backend/core/Controller.php';

class SettingsController extends Controller {
    public function index(): void {
        $this->requirePermission('settings.manage');

        $settingsRows = DB::fetchAll("SELECT * FROM `settings`");
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        // List backups
        $backupFiles = [];
        if (is_dir(BACKUP_PATH)) {
            $files = scandir(BACKUP_PATH);
            foreach ($files as $f) {
                if (str_ends_with($f, '.sql')) {
                    $path = BACKUP_PATH . '/' . $f;
                    $backupFiles[] = [
                        'filename' => $f,
                        'size'     => round(filesize($path) / 1024, 2) . ' KB',
                        'date'     => date('Y-m-d H:i:s', filemtime($path))
                    ];
                }
            }
        }

        $this->view('settings/index', [
            'settings'    => $settings,
            'backupFiles' => $backupFiles
        ]);
    }

    public function update(): void {
        $this->requirePermission('settings.manage');
        $this->validateCsrf();

        $posted = $this->allInputs();
        unset($posted['csrf_token']);

        $stmt = DB::connect()->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");

        foreach ($posted as $key => $val) {
            if (is_string($val) || is_numeric($val)) {
                $stmt->execute([$key, (string)$val]);
            }
        }

        AuditLogger::log('UPDATE', 'Settings', null, "Updated system settings.");
        Session::setFlash('success', 'Settings updated successfully.');
        $this->redirect('settings');
    }

    /**
     * Generate fresh database backup dump
     */
    public function createBackup(): void {
        $this->requirePermission('settings.manage');
        $this->validateCsrf();

        $filename = 'sagar_advertising_backup_' . date('Ymd_His') . '.sql';
        $destPath = BACKUP_PATH . '/' . $filename;

        // Try mysqldump command
        $cmd = "\"C:\\xampp\\mysql\\bin\\mysqldump.exe\" -u root sagar_advertising_crm > \"{$destPath}\"";
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0 || !file_exists($destPath)) {
            // PHP fallback backup
            $tables = DB::fetchAll("SHOW TABLES");
            $sqlDump = "-- SAGAR ADVERTISING CRM DATABASE BACKUP\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
            foreach ($tables as $tRow) {
                $tbl = reset($tRow);
                $createTbl = DB::fetch("SHOW CREATE TABLE `{$tbl}`");
                $sqlDump .= "DROP TABLE IF EXISTS `{$tbl}`;\n" . $createTbl['Create Table'] . ";\n\n";
                $rows = DB::fetchAll("SELECT * FROM `{$tbl}`");
                foreach ($rows as $r) {
                    $vals = array_map(fn($v) => $v === null ? "NULL" : DB::connect()->quote((string)$v), array_values($r));
                    $sqlDump .= "INSERT INTO `{$tbl}` VALUES (" . implode(',', $vals) . ");\n";
                }
                $sqlDump .= "\n";
            }
            file_put_contents($destPath, $sqlDump);
        }

        AuditLogger::log('BACKUP', 'Settings', null, "Generated database backup {$filename}");
        Session::setFlash('success', "Database backup '{$filename}' created successfully.");
        $this->redirect('settings');
    }

    /**
     * Download backup file
     */
    public function downloadBackup(): void {
        $this->requirePermission('settings.manage');
        $file = basename((string)$this->input('file'));
        $filePath = BACKUP_PATH . '/' . $file;

        if (file_exists($filePath)) {
            header('Content-Type: application/sql');
            header("Content-Disposition: attachment; filename=\"{$file}\"");
            readfile($filePath);
            exit;
        }

        Session::setFlash('error', 'Backup file not found.');
        $this->redirect('settings');
    }

    public function activityLogs(): void {
        $this->requireAuth();

        $logs = DB::fetchAll(
            "SELECT a.*, u.name as user_name
             FROM `activity_logs` a
             LEFT JOIN `users` u ON a.user_id = u.id
             ORDER BY a.created_at DESC
             LIMIT 150"
        );

        $this->view('logs/index', ['logs' => $logs]);
    }

    /**
     * Test SMTP connectivity & optionally send test email
     */
    public function testSmtp(): void {
        $this->requirePermission('settings.manage');

        $host = trim((string)$this->input('smtp_host', ''));
        $port = (int)$this->input('smtp_port', 587);
        $enc  = strtolower(trim((string)$this->input('smtp_encryption', 'tls')));
        $user = trim((string)$this->input('smtp_username', ''));
        $pass = trim((string)$this->input('smtp_password', ''));
        $testEmail = trim((string)$this->input('test_email', ''));

        // Fallbacks from DB if empty
        if (empty($host)) {
            $host = DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'smtp_host'") ?: 'smtp.gmail.com';
        }
        if (empty($user)) {
            $user = DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'smtp_username'") ?: '';
        }
        if (empty($pass)) {
            $pass = DB::fetchColumn("SELECT setting_value FROM `settings` WHERE setting_key = 'smtp_password'") ?: '';
        }

        $config = [
            'smtp_host'       => $host,
            'smtp_port'       => $port ?: 587,
            'smtp_encryption' => $enc ?: 'tls',
            'smtp_username'   => $user,
            'smtp_password'   => $pass,
            'smtp_from_name'  => 'Sagar Advertising'
        ];

        require_once ROOT_PATH . '/backend/services/MailerService.php';
        $result = MailerService::testSmtpConnection($config);

        if ($result['success'] && !empty($testEmail)) {
            if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                $this->json(['success' => false, 'error' => 'Invalid test recipient email address.']);
                return;
            }

            try {
                // Perform single test mail send using this config
                $body = "
                <div style='font-family: Arial, sans-serif; padding: 25px; border-left: 5px solid #FF5500; background: #fff;'>
                    <h2 style='color: #121417; margin-top: 0;'>SAGAR ADVERTISING &ndash; SMTP Verified!</h2>
                    <p>Congratulations, your mail server credentials are confirmed working.</p>
                    <p style='color: #666; font-size: 13px;'>
                        <strong>Host:</strong> " . htmlspecialchars($host) . "<br>
                        <strong>Port:</strong> {$port}<br>
                        <strong>Encryption:</strong> " . strtoupper($enc) . "<br>
                        <strong>Sender:</strong> " . htmlspecialchars($user) . "
                    </p>
                    <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #888;'>Sagar Advertising &bull; #18096, 'Shanti Kunj', Akkasaligar Oni, Old-Hubballi, HUBBALLI - 580 024 &bull; 9611620862</p>
                </div>";

                MailerService::sendViaSocketSmtp($config, $testEmail, 'Test Recipient', 'Sagar Advertising - SMTP Connectivity Verification', $body);
                $result['message'] = "SMTP connected and live verification email was successfully delivered to {$testEmail}!";
            } catch (Exception $e) {
                $result = [
                    'success' => false,
                    'error'   => "Connected & authenticated, but failed sending test email: " . $e->getMessage()
                ];
            }
        }

        $this->json($result);
    }
}
