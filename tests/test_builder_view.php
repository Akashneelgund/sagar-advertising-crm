<?php
declare(strict_types=1);

$baseUrl = 'http://localhost:8000';
$cookieFile = __DIR__ . '/test_cookies.txt';

function doRequest(string $url, string $method = 'GET', array $data = [], string $cookieFile = ''): string {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    $res = (string)curl_exec($ch);
    curl_close($ch);
    return $res;
}

// 1. Login
$loginHtml = doRequest("{$baseUrl}/login", 'GET', [], $cookieFile);
preg_match('/name="csrf_token" value="([^"]+)"/', $loginHtml, $m);
$csrf = $m[1] ?? '';
doRequest("{$baseUrl}/login", 'POST', [
    'csrf_token' => $csrf,
    'username'   => 'admin',
    'password'   => 'Admin@123'
], $cookieFile);

// 2. Fetch Builder
$html = doRequest("{$baseUrl}/quotations/builder", 'GET', [], $cookieFile);

echo "Checking Quotation Builder View HTML:\n";
echo "1. prevDeliveryTime has '3 to 7 working days from artwork approval': " 
    . (str_contains($html, 'id="prevDeliveryTime">3 to 7 working days from artwork approval<') ? 'PASS' : 'FAIL') . "\n";
echo "2. prevPaymentTerms has '50% Advance with Purchase Order': " 
    . (str_contains($html, 'id="prevPaymentTerms">50% Advance with Purchase Order') ? 'PASS' : 'FAIL') . "\n";
echo "3. deliveryTimeInput has default value: " 
    . (str_contains($html, 'id="deliveryTimeInput" name="delivery_time" value="3 to 7 working days from artwork approval"') ? 'PASS' : 'FAIL') . "\n";
echo "4. paymentTermsInput has default value: " 
    . (str_contains($html, 'id="paymentTermsInput" name="payment_terms"') ? 'PASS' : 'FAIL') . "\n";
echo "5. templatePresetSelect exists: " 
    . (str_contains($html, 'id="templatePresetSelect"') ? 'PASS' : 'FAIL') . "\n";
echo "6. window.QUOTATION_TEMPLATES initialized: " 
    . (str_contains($html, 'window.QUOTATION_TEMPLATES =') ? 'PASS' : 'FAIL') . "\n";
