<?php
declare(strict_types=1);

$baseUrl = 'http://localhost:8000';
$cookieFile = __DIR__ . '/test_cookies.txt';

function doRequest(string $url, string $method = 'GET', mixed $data = null, string $cookieFile = '', array $headers = []): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (is_array($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else if (is_string($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $res = (string)curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'body' => $res];
}

// 1. Login
$loginHtml = doRequest("{$baseUrl}/login", 'GET', [], $cookieFile)['body'];
preg_match('/name="csrf_token" value="([^"]+)"/', $loginHtml, $m);
$csrf = $m[1] ?? '';
doRequest("{$baseUrl}/login", 'POST', [
    'csrf_token' => $csrf,
    'username'   => 'admin',
    'password'   => 'Admin@123'
], $cookieFile);

// 2. Test JSON POST to /api/campaigns/process-batch
$jsonPayload = json_encode(['campaign_id' => 1, 'batch_size' => 5]);
$res = doRequest("{$baseUrl}/api/campaigns/process-batch", 'POST', $jsonPayload, $cookieFile, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'X-CSRF-TOKEN: ' . $csrf
]);

echo "Status: {$res['status']}\n";
echo "Response Body:\n{$res['body']}\n";
