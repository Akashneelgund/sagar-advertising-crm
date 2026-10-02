<?php
/**
 * Sagar Advertising CRM - Automated Test Suite
 * Verifies core commercial calculations, database queries, and system integrity.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/backend/services/QuotationCalculator.php';
require_once dirname(__DIR__) . '/backend/services/PdfGenerator.php';

echo "========================================================\n";
echo "SAGAR ADVERTISING CRM - TEST SUITE VERIFICATION\n";
echo "========================================================\n\n";

$passes = 0;
$failures = 0;

function assertCondition(bool $condition, string $testName): void {
    global $passes, $failures;
    if ($condition) {
        echo "  [PASS] {$testName}\n";
        $passes++;
    } else {
        echo "  [FAIL] {$testName}\n";
        $failures++;
    }
}

// ----------------------------------------------------
// 1. Commission Math Tests
// ----------------------------------------------------
echo "TEST GROUP 1: QuotationCalculator (Mode 1: Markup)\n";
// Example from user request: Actual = 10,000, Comm = 15% -> Comm Amt = 1,500, Selling = 11,500
$line1 = QuotationCalculator::calculateLineItem(10000.0, 15.0, 2.0, 'markup');
assertCondition($line1['commission_amount'] === 1500.0, "Line Commission Amount should be ₹1,500");
assertCondition($line1['selling_price'] === 11500.0, "Line Selling Price should be ₹11,500");
assertCondition($line1['total_amount'] === 23000.0, "Line Total Amount (Qty 2) should be ₹23,000");
assertCondition($line1['total_commission'] === 3000.0, "Line Total Commission (Qty 2) should be ₹3,000");

// Flex Board example from user request: 12x8 ft, Qty 2, Actual = 2000, Comm = 15% -> Comm Amt = 300, Selling = 2300, Total = 4600
$line2 = QuotationCalculator::calculateLineItem(2000.0, 15.0, 2.0, 'markup');
assertCondition($line2['commission_amount'] === 300.0, "Flex Board Commission Amount should be ₹300");
assertCondition($line2['selling_price'] === 2300.0, "Flex Board Selling Price should be ₹2,300");
assertCondition($line2['total_amount'] === 4600.0, "Flex Board Total Amount should be ₹4,600");

echo "\nTEST GROUP 2: Quotation Totals, Discounts, GST & Round Off\n";
$items = [
    ['actual_price' => 10000.0, 'commission_rate' => 15.0, 'quantity' => 2.0], // Total 23000
    ['actual_price' => 2000.0,  'commission_rate' => 15.0, 'quantity' => 2.0]  // Total 4600
];
// Subtotal = 27600, Discount = 1000 -> Taxable = 26600, GST 18% = 4788, Grand Total = 31388
$totals = QuotationCalculator::calculateQuotationTotals($items, 1000.0, 18.0, 500.0, 1000.0, 'markup');
assertCondition($totals['subtotal'] === 27600.0, "Subtotal equals ₹27,600");
assertCondition($totals['discount_amount'] === 1000.0, "Discount equals ₹1,000");
// Taxable = 27600 - 1000 + 500 + 1000 = 28100
assertCondition($totals['taxable_amount'] === 28100.0, "Taxable equals ₹28,100 (incl transport & install)");
assertCondition($totals['gst_amount'] === 5058.0, "GST 18% on ₹28,100 equals ₹5,058");
assertCondition($totals['grand_total'] === 33158.0, "Grand Total equals ₹33,158");

// ----------------------------------------------------
// 2. Database Connectivity & Seeder Verification
// ----------------------------------------------------
echo "\nTEST GROUP 3: Database & Seeded Data Verification\n";
$custCount = (int)DB::fetchColumn("SELECT COUNT(*) FROM `customers`");
assertCondition($custCount >= 20, "Customer count ({$custCount}) >= 20");

$quoteCount = (int)DB::fetchColumn("SELECT COUNT(*) FROM `quotations`");
assertCondition($quoteCount >= 15, "Quotation count ({$quoteCount}) >= 15");

$serviceCount = (int)DB::fetchColumn("SELECT COUNT(*) FROM `services`");
assertCondition($serviceCount === 11, "All 11 Sagar Advertising services exist");

$userCount = (int)DB::fetchColumn("SELECT COUNT(*) FROM `users`");
assertCondition($userCount === 3, "3 Role accounts seeded (admin, manager, sales)");

$templateCount = (int)DB::fetchColumn("SELECT COUNT(*) FROM `email_templates`");
assertCondition($templateCount >= 5, "At least 5 email templates seeded");

// ----------------------------------------------------
// 3. PDF Generator Test
// ----------------------------------------------------
echo "\nTEST GROUP 4: PDF / HTML Invoice Generator\n";
$sampleQuote = DB::fetch("SELECT * FROM `quotations` LIMIT 1");
$sampleItems = DB::fetchAll("SELECT * FROM `quotation_items` WHERE quotation_id = ?", [$sampleQuote['id']]);
$sampleCust = DB::fetch("SELECT * FROM `customers` WHERE id = ?", [$sampleQuote['customer_id']]);
$sampleCompany = [
    'company_name' => 'SAGAR ADVERTISING',
    'company_tagline' => 'Your Brand. Our Passion.',
    'contact_phone_1' => '9611620862',
    'contact_phone_2' => '8904184867',
    'company_address' => '#18096, "Shanti Kunj", Akkasaligar Oni, Old-Hubballi, HUBBALLI - 580 024',
    'company_email' => 'sagaradvertising7@gmail.com',
    'company_gstin' => '29AVPH4223R1ZV',
    'company_msme' => 'UDYAM-KR-13-0061429'
];

$invoiceHtml = PdfGenerator::generateHtml($sampleQuote, $sampleItems, $sampleCust, $sampleCompany);
assertCondition(str_contains($invoiceHtml, 'SAGAR ADVERTISING'), "Invoice HTML contains brand SAGAR ADVERTISING");
assertCondition(str_contains($invoiceHtml, '29AVPH4223R1ZV'), "Invoice HTML contains correct GSTIN");
assertCondition(str_contains($invoiceHtml, 'UDYAM-KR-13-0061429'), "Invoice HTML contains MSME");
assertCondition(str_contains($invoiceHtml, 'Your Brand. Our Passion.'), "Invoice HTML contains tagline");

$savedPath = PdfGenerator::saveToFile($invoiceHtml, $sampleQuote['quotation_number']);
assertCondition(file_exists($savedPath), "Generated invoice file exists on disk: {$savedPath}");

echo "\n========================================================\n";
echo "TEST RESULTS: {$passes} Passed, {$failures} Failed\n";
echo "========================================================\n";

if ($failures > 0) {
    exit(1);
}
