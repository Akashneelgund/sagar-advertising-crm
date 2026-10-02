<?php
/**
 * Sagar Advertising CRM - Branded A4 Quotation PDF & Document Generator
 */

declare(strict_types=1);

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

class PdfGenerator {
    /**
     * Render the full branded HTML document for printing or PDF storage
     */
    public static function generateHtml(array $quote, array $items, array $customer, array $company): string {
        $logoSvg = file_exists(ROOT_PATH . '/assets/images/logo.svg')
            ? file_get_contents(ROOT_PATH . '/assets/images/logo.svg')
            : '<div style="font-weight:900; font-size:28px; color:#FF5500;">SA</div>';

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Quotation <?= e($quote['quotation_number']) ?> - Sagar Advertising</title>
            <style>
                @page {
                    size: A4 portrait;
                    margin: 12mm 15mm;
                }
                * {
                    box-sizing: border-box;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                body {
                    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
                    color: #1a1a1a;
                    background: #ffffff;
                    margin: 0;
                    padding: 0;
                    font-size: 13px;
                    line-height: 1.45;
                }
                .invoice-card {
                    max-width: 800px;
                    margin: 0 auto;
                    background: #fff;
                    padding: 10px;
                }
                .invoice-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    border-bottom: 3px solid #FF5500;
                    padding-bottom: 16px;
                    margin-bottom: 20px;
                }
                .brand-left {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }
                .brand-logo-wrap {
                    width: 70px;
                    height: 70px;
                }
                .brand-logo-wrap svg {
                    width: 100%;
                    height: 100%;
                }
                .brand-title h1 {
                    margin: 0;
                    font-size: 22px;
                    font-weight: 900;
                    letter-spacing: 0.5px;
                    color: #121417;
                    text-transform: uppercase;
                }
                .brand-title .tagline {
                    margin: 2px 0 6px 0;
                    font-size: 11px;
                    font-weight: 700;
                    color: #FF5500;
                    letter-spacing: 1px;
                    text-transform: uppercase;
                }
                .brand-address {
                    font-size: 11px;
                    color: #555;
                    line-height: 1.35;
                }
                .header-meta {
                    text-align: right;
                }
                .badge-quotation {
                    background: #121417;
                    color: #ffffff;
                    padding: 6px 14px;
                    border-radius: 4px;
                    font-size: 13px;
                    font-weight: 800;
                    letter-spacing: 1.5px;
                    text-transform: uppercase;
                    display: inline-block;
                    margin-bottom: 8px;
                    border-left: 3px solid #FF5500;
                }
                .meta-table {
                    font-size: 12px;
                    margin-left: auto;
                }
                .meta-table td {
                    padding: 2px 4px;
                }
                .meta-table .label {
                    color: #777;
                    font-weight: 600;
                    text-align: right;
                }
                .meta-table .val {
                    font-weight: 700;
                    color: #121417;
                    text-align: right;
                }

                .billing-grid {
                    display: flex;
                    justify-content: space-between;
                    background: #F8F9FA;
                    border-radius: 8px;
                    padding: 14px 18px;
                    margin-bottom: 20px;
                    border: 1px solid #E9ECEF;
                }
                .bill-to h4, .project-details h4 {
                    margin: 0 0 6px 0;
                    font-size: 11px;
                    color: #FF5500;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    font-weight: 800;
                }
                .client-name {
                    font-size: 15px;
                    font-weight: 800;
                    color: #121417;
                    margin-bottom: 4px;
                }
                .client-info {
                    font-size: 12px;
                    color: #444;
                    line-height: 1.4;
                }

                table.items-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                table.items-table th {
                    background: #121417;
                    color: #ffffff;
                    font-size: 11px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    padding: 10px 8px;
                    border: 1px solid #121417;
                }
                table.items-table th:first-child {
                    border-top-left-radius: 4px;
                }
                table.items-table th:last-child {
                    border-top-right-radius: 4px;
                }
                table.items-table td {
                    padding: 9px 8px;
                    border: 1px solid #E5E7EB;
                    font-size: 12px;
                    vertical-align: top;
                }
                table.items-table tr:nth-child(even) td {
                    background: #FAFAFA;
                }
                .item-title {
                    font-weight: 700;
                    color: #121417;
                }
                .item-desc {
                    color: #666;
                    font-size: 11px;
                    margin-top: 3px;
                }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .text-bold { font-weight: 700; }

                .summary-container {
                    display: flex;
                    justify-content: space-between;
                    gap: 20px;
                    margin-bottom: 20px;
                }
                .terms-block {
                    flex: 1;
                    background: #FDFDFD;
                    border: 1px solid #E9ECEF;
                    border-radius: 6px;
                    padding: 12px;
                }
                .terms-block h5 {
                    margin: 0 0 6px 0;
                    font-size: 11px;
                    text-transform: uppercase;
                    color: #FF5500;
                    font-weight: 800;
                    letter-spacing: 0.5px;
                }
                .terms-block ol, .terms-block p {
                    margin: 0;
                    padding-left: 16px;
                    font-size: 11px;
                    color: #555;
                    line-height: 1.45;
                }
                .totals-block {
                    width: 300px;
                }
                table.totals-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                table.totals-table td {
                    padding: 5px 8px;
                    font-size: 12px;
                }
                table.totals-table .val-label {
                    color: #666;
                    text-align: right;
                }
                table.totals-table .val-amount {
                    font-weight: 700;
                    color: #121417;
                    text-align: right;
                }
                .grand-total-row td {
                    background: #121417;
                    color: #ffffff !important;
                    font-size: 14px !important;
                    font-weight: 800;
                    padding: 8px 10px !important;
                    border-radius: 4px;
                }
                .grand-total-row .val-amount {
                    color: #FF5500 !important;
                    font-size: 16px !important;
                }

                .sign-footer {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-end;
                    margin-top: 25px;
                    padding-top: 15px;
                    border-top: 1px dashed #DDD;
                }
                .bank-details {
                    font-size: 11px;
                    color: #444;
                    line-height: 1.4;
                }
                .bank-title {
                    font-weight: 700;
                    color: #121417;
                    margin-bottom: 2px;
                }
                .signature-box {
                    text-align: center;
                    width: 200px;
                }
                .signature-line {
                    border-bottom: 1px solid #333;
                    height: 50px;
                    margin-bottom: 6px;
                }
                .signature-name {
                    font-size: 12px;
                    font-weight: 700;
                    color: #121417;
                }
                .signature-org {
                    font-size: 10px;
                    color: #777;
                    text-transform: uppercase;
                }

                .footer-bar {
                    margin-top: 25px;
                    border-top: 2px solid #FF5500;
                    padding-top: 10px;
                    text-align: center;
                    font-size: 11px;
                    color: #888;
                }
                .footer-bar strong {
                    color: #121417;
                }

                .no-print-bar {
                    background: #121417;
                    color: #fff;
                    padding: 12px 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 20px;
                    border-radius: 8px;
                }
                .btn-print {
                    background: #FF5500;
                    color: #fff;
                    border: none;
                    padding: 8px 18px;
                    font-weight: 700;
                    border-radius: 6px;
                    cursor: pointer;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    font-size: 13px;
                }
                .btn-print:hover {
                    background: #e04b00;
                }
                .btn-secondary {
                    background: #333;
                    color: #fff;
                    border: none;
                    padding: 8px 14px;
                    font-weight: 600;
                    border-radius: 6px;
                    text-decoration: none;
                    font-size: 13px;
                }

                @media screen and (max-width: 768px) {
                    body {
                        padding: 8px;
                        background: #f1f5f9;
                    }
                    .invoice-card {
                        padding: 16px 12px;
                        border-radius: 8px;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                    }
                    .no-print-bar {
                        flex-direction: column;
                        align-items: stretch;
                        gap: 12px;
                        text-align: center;
                        padding: 12px 14px;
                    }
                    .no-print-bar > div:last-child {
                        flex-direction: column;
                    }
                    .no-print-bar .btn-print, .no-print-bar .btn-secondary {
                        justify-content: center;
                        text-align: center;
                        width: 100%;
                    }
                    .invoice-header {
                        flex-direction: column;
                        gap: 14px;
                    }
                    .brand-left {
                        flex-direction: column;
                        align-items: flex-start;
                        gap: 10px;
                    }
                    .header-meta {
                        text-align: left;
                        width: 100%;
                    }
                    .meta-table {
                        margin-left: 0;
                        width: 100%;
                    }
                    .meta-table .label {
                        text-align: left;
                    }
                    .meta-table .val {
                        text-align: right;
                    }
                    .billing-grid {
                        flex-direction: column;
                        gap: 14px;
                        padding: 12px;
                    }
                    .project-details {
                        text-align: left !important;
                    }
                    .table-wrap-responsive {
                        width: 100%;
                        overflow-x: auto;
                        -webkit-overflow-scrolling: touch;
                        margin-bottom: 20px;
                    }
                    .table-wrap-responsive table.items-table {
                        min-width: 620px;
                        margin-bottom: 0;
                    }
                    .summary-container {
                        flex-direction: column;
                        gap: 16px;
                    }
                    .totals-block {
                        width: 100%;
                    }
                    .sign-footer {
                        flex-direction: column;
                        align-items: flex-start;
                        gap: 20px;
                    }
                    .signature-box {
                        width: 100%;
                        max-width: 250px;
                        margin: 0 auto;
                    }
                }

                @media print {
                    .no-print-bar {
                        display: none !important;
                    }
                    body {
                        padding: 0;
                    }
                    .invoice-card {
                        box-shadow: none;
                        padding: 0;
                        max-width: 100%;
                    }
                    .table-wrap-responsive {
                        overflow: visible !important;
                    }
                }
            </style>
        </head>
        <body>
            <div class="invoice-card">
                <!-- Action bar for browser viewing -->
                <div class="no-print-bar">
                    <div>
                        <strong>SAGAR ADVERTISING</strong> &bull; Quotation <?= e($quote['quotation_number']) ?>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="javascript:window.print()" class="btn-print">
                            🖨️ Print / Save as PDF
                        </a>
                        <a href="<?= url('quotations') ?>" class="btn-secondary">
                            &larr; Back to Quotations
                        </a>
                    </div>
                </div>

                <!-- Invoice Header -->
                <div class="invoice-header">
                    <div class="brand-left">
                        <div class="brand-logo-wrap">
                            <?= $logoSvg ?>
                        </div>
                        <div class="brand-title">
                            <h1><?= e($company['company_name'] ?? 'SAGAR ADVERTISING') ?></h1>
                            <div class="tagline"><?= e($company['company_tagline'] ?? 'Your Brand. Our Passion.') ?></div>
                            <div class="brand-address">
                                <?= e($company['company_address'] ?? '#18096, "Shanti Kunj", Akkasaligar Oni, Old-Hubballi, HUBBALLI - 580 024') ?><br>
                                Phone: <?= e($company['contact_phone_1'] ?? '9611620862') ?> / <?= e($company['contact_phone_2'] ?? '8904184867') ?> &bull; Email: <?= e($company['company_email'] ?? 'sagaradvertising7@gmail.com') ?><br>
                                <strong>GSTIN:</strong> <?= e($company['company_gstin'] ?? '29AVPH4223R1ZV') ?> &bull; <strong>MSME:</strong> <?= e($company['company_msme'] ?? 'UDYAM-KR-13-0061429') ?>
                            </div>
                        </div>
                    </div>
                    <div class="header-meta">
                        <div class="badge-quotation">QUOTATION</div>
                        <table class="meta-table">
                            <tr>
                                <td class="label">Quotation No:</td>
                                <td class="val"><?= e($quote['quotation_number']) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Date:</td>
                                <td class="val"><?= date('d-M-Y', strtotime($quote['quotation_date'])) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Valid Until:</td>
                                <td class="val"><?= date('d-M-Y', strtotime($quote['valid_until'])) ?></td>
                            </tr>
                            <?php if (!empty($quote['reference'])): ?>
                            <tr>
                                <td class="label">Ref / PO:</td>
                                <td class="val"><?= e($quote['reference']) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Client & Project Details -->
                <div class="billing-grid">
                    <div class="bill-to">
                        <h4>Quotation Prepared For</h4>
                        <div class="client-name"><?= e($customer['company_name']) ?></div>
                        <div class="client-info">
                            <strong>Attn:</strong> <?= e($customer['contact_person']) ?><br>
                            <?= nl2br(e($customer['address'] ?? '')) ?><br>
                            <?= e($customer['city']) ?>, <?= e($customer['state']) ?> - <?= e($customer['pincode']) ?><br>
                            Mobile: <?= e($customer['mobile']) ?> &bull; Email: <?= e($customer['email'] ?? 'N/A') ?><br>
                            <?php if (!empty($customer['gstin'])): ?>
                                <strong>GSTIN:</strong> <?= e($customer['gstin']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="project-details" style="text-align: right;">
                        <h4>Project Details</h4>
                        <div class="client-name"><?= e($quote['project_name'] ?: 'Advertising & Branding Work') ?></div>
                        <div class="client-info">
                            <strong>Executive:</strong> <?= e($quote['sales_person_name'] ?? 'Vageesh H Hugar') ?><br>
                            <strong>Delivery Timeline:</strong> <?= e($quote['delivery_time'] ?? '3 to 7 working days') ?><br>
                            <strong>Payment Terms:</strong> <?= e($quote['payment_terms'] ?? '50% Advance, 50% on installation') ?>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-wrap-responsive">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 35px;" class="text-center">#</th>
                                <th>Description of Services & Products</th>
                                <th style="width: 90px;" class="text-center">Size / Spec</th>
                                <th style="width: 50px;" class="text-center">Qty</th>
                                <th style="width: 55px;" class="text-center">Unit</th>
                                <th style="width: 85px;" class="text-right">Rate (₹)</th>
                                <th style="width: 95px;" class="text-right">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $idx => $item): ?>
                            <tr>
                                <td class="text-center text-bold"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="item-title"><?= e($item['item_name']) ?></div>
                                    <?php if (!empty($item['description'])): ?>
                                        <div class="item-desc"><?= nl2br(e($item['description'])) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= e($item['size_dimension'] ?: '-') ?></td>
                                <td class="text-center text-bold"><?= (float)$item['quantity'] ?></td>
                                <td class="text-center"><?= e($item['unit'] ?? 'Sq Ft') ?></td>
                                <td class="text-right"><?= number_format((float)$item['selling_price'], 2) ?></td>
                                <td class="text-right text-bold"><?= number_format((float)$item['total_amount'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary & Totals -->
                <div class="summary-container">
                    <div class="terms-block">
                        <h5>Terms & Conditions</h5>
                        <?php if (!empty($quote['terms_and_conditions'])): ?>
                            <p style="white-space: pre-line;"><?= e($quote['terms_and_conditions']) ?></p>
                        <?php else: ?>
                            <ol>
                                <li>Rates are valid for 15 days from quotation date.</li>
                                <li>GST 18% extra as applicable by law.</li>
                                <li>Client to provide high-res vector art files (CDR / AI / PDF).</li>
                                <li>Electrical & masonry connections at site by client.</li>
                                <li>1 Year replacement warranty on LED power supply units.</li>
                            </ol>
                        <?php endif; ?>
                    </div>
                    <div class="totals-block">
                        <table class="totals-table">
                            <tr>
                                <td class="val-label">Subtotal:</td>
                                <td class="val-amount"><?= format_currency($quote['subtotal']) ?></td>
                            </tr>
                            <?php if ((float)$quote['discount_amount'] > 0): ?>
                            <tr>
                                <td class="val-label" style="color: #28a745;">Special Discount:</td>
                                <td class="val-amount" style="color: #28a745;">- <?= format_currency($quote['discount_amount']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ((float)$quote['transportation_charges'] > 0): ?>
                            <tr>
                                <td class="val-label">Transportation:</td>
                                <td class="val-amount"><?= format_currency($quote['transportation_charges']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ((float)$quote['installation_charges'] > 0): ?>
                            <tr>
                                <td class="val-label">Installation / Fixing:</td>
                                <td class="val-amount"><?= format_currency($quote['installation_charges']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td class="val-label">Taxable Amount:</td>
                                <td class="val-amount"><?= format_currency($quote['taxable_amount']) ?></td>
                            </tr>
                            <tr>
                                <td class="val-label">GST (<?= (float)$quote['gst_rate'] ?>%):</td>
                                <td class="val-amount"><?= format_currency($quote['gst_amount']) ?></td>
                            </tr>
                            <?php if ((float)$quote['round_off'] != 0): ?>
                            <tr>
                                <td class="val-label">Round Off:</td>
                                <td class="val-amount"><?= format_currency($quote['round_off']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="grand-total-row">
                                <td style="text-align: right;">GRAND TOTAL:</td>
                                <td class="val-amount"><?= format_currency($quote['grand_total']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Payment Details & Signature -->
                <div class="sign-footer">
                    <div class="bank-details">
                        <div class="bank-title">BANK & REMITTANCE DETAILS</div>
                        <strong>Bank:</strong> Canara Bank, Hubballi Main Branch<br>
                        <strong>Account Name:</strong> SAGAR ADVERTISING<br>
                        <strong>A/C No:</strong> 0321201004567 &bull; <strong>IFSC:</strong> CNRB0000321<br>
                        <strong>UPI / GooglePay / PhonePe:</strong> 9611620862@upi
                    </div>
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signature-name">Authorized Signatory</div>
                        <div class="signature-org">For SAGAR ADVERTISING</div>
                    </div>
                </div>

                <!-- Footer Tagline -->
                <div class="footer-bar">
                    <strong>SAGAR ADVERTISING</strong> &bull; <?= e($company['company_tagline'] ?? 'Your Brand. Our Passion.') ?><br>
                    <span>This is a computer-generated quotation issued from the Sagar Advertising ERP.</span>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Save generated HTML to storage/pdf/ directory
     */
    public static function saveToFile(string $html, string $quotationNumber): string {
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $quotationNumber);
        $filePath = PDF_PATH . '/' . $safeName . '.html';
        file_put_contents($filePath, $html);
        return $filePath;
    }
}
