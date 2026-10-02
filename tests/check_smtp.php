<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$smtp = DB::fetchAll("SELECT * FROM settings WHERE category = 'smtp'");
print_r($smtp);

$campaigns = DB::fetchAll("SELECT id, name, status, total_recipients, sent_count, pending_count, failed_count FROM email_campaigns");
print_r($campaigns);

$logs = DB::fetchAll("SELECT * FROM email_logs ORDER BY id DESC LIMIT 10");
print_r($logs);
