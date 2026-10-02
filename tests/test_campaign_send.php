<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../backend/services/MailerService.php';

try {
    echo "Testing MailerService::processCampaignQueue(1, 5)...\n";
    $campaign = DB::fetch("SELECT * FROM `email_campaigns` WHERE id = 1");
    echo "Campaign status: " . ($campaign['status'] ?? 'NOT FOUND') . "\n";
    
    // Reset campaign 1 to processing/queued so we can test
    DB::query("UPDATE `email_campaigns` SET `status` = 'queued' WHERE id = 1");
    DB::query("UPDATE `email_campaign_recipients` SET `status` = 'pending' WHERE campaign_id = 1 LIMIT 5");

    $res = MailerService::processCampaignQueue(1, 5);
    echo "Success result:\n";
    print_r($res);
} catch (Throwable $e) {
    echo "CAUGHT EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
