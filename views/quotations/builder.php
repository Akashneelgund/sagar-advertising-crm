<?php
/**
 * Sagar Advertising CRM - Split-Screen Live Quotation Builder
 */
$preselectedCustomerId = (int)($_GET['customer_id'] ?? ($quote['customer_id'] ?? 0));
?>

<!-- Pass catalogs to Client JavaScript Engine -->
<script>
    window.SERVICES_CATALOG = <?= json_encode($services) ?>;
    window.CUSTOMERS_CATALOG = <?= json_encode($customers) ?>;
    window.QUOTATION_TEMPLATES = <?= json_encode($templates ?? []) ?>;
    window.INITIAL_QUOTE_DATA = <?= json_encode($quote ? ['commission_mode' => $quote['commission_mode'], 'items' => $items] : null) ?>;
</script>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('quotations') ?>" class="btn btn-sm btn-outline-secondary" title="Back to Quotations">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="fw-black mb-0" style="font-family: 'Space Grotesk', sans-serif;">
            <?= $quote ? "Edit Quotation: " . e($quote['quotation_number']) : "Create New Quotation" ?>
        </h3>
        <span class="badge bg-dark font-monospace fs-6 px-3 py-1 ms-2"><?= e($previewQuoteNumber) ?></span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-muted border d-none d-md-inline-block">
            <i class="bi bi-broadcast text-danger me-1"></i> Live Real-Time Synchronizer Active
        </span>
    </div>
</div>

<form id="quotationBuilderForm" action="<?= url('quotations/store') ?>" method="POST">
    <?= csrf_field() ?>

    <!-- Mobile / Tablet View Switcher -->
    <div class="builder-mobile-switcher">
        <button type="button" class="builder-switcher-btn active" id="btnSwitchEditor">
            <i class="bi bi-pencil-square"></i>
            <span>1. Edit Form</span>
        </button>
        <button type="button" class="builder-switcher-btn" id="btnSwitchPreview">
            <i class="bi bi-file-earmark-pdf-fill text-warning"></i>
            <span>2. Live A4 Preview</span>
        </button>
    </div>

    <div class="builder-layout">
        <!-- Left Editor Pane -->
        <div class="builder-editor-pane">
            <!-- 1. Customer & Project Header Card -->
            <div class="card-brand">
                <div class="card-header py-2 px-3 bg-light">
                    <span class="fw-bold small text-dark"><i class="bi bi-person-bounding-box me-1 text-primary"></i> 1. Client & Project Details</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 mb-2">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold mb-1">Select Customer / Client <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <select class="form-select" id="customerSelect" name="customer_id" required>
                                    <option value="">-- Choose Existing Client --</option>
                                    <?php foreach ($customers as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= $preselectedCustomerId == $c['id'] ? 'selected' : '' ?>>
                                            <?= e($c['company_name']) ?> (<?= e($c['contact_person']) ?> - <?= e($c['city']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <a href="<?= url('customers/create') ?>" target="_blank" class="btn btn-outline-secondary" title="Add New Customer">+ New</a>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold mb-1">Project / Work Name</label>
                            <input type="text" class="form-control form-control-sm" id="projectNameInput" name="project_name" value="<?= e($quote['project_name'] ?? 'Storefront 3D LED Signboard & ACP Cladding') ?>" placeholder="e.g. Flagship Store Glow Sign">
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-sm-4">
                            <label class="form-label small fw-bold mb-1">Quotation Date</label>
                            <input type="date" class="form-control form-control-sm" id="quotationDateInput" name="quotation_date" value="<?= e($quote['quotation_date'] ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label small fw-bold mb-1">Valid Until</label>
                            <input type="date" class="form-control form-control-sm" id="validUntilInput" name="valid_until" value="<?= e($quote['valid_until'] ?? date('Y-m-d', strtotime('+15 days'))) ?>">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label small fw-bold mb-1">Client Ref / PO #</label>
                            <input type="text" class="form-control form-control-sm" id="referenceInput" name="reference" value="<?= e($quote['reference'] ?? '') ?>" placeholder="e.g. Verbal / WhatsApp">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Dynamic Line Items Container -->
            <div class="card-brand">
                <div class="card-header py-2 px-3 bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span class="fw-bold small text-dark"><i class="bi bi-list-check me-1 text-warning"></i> 2. Advertising Services & Products</span>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <label class="small text-muted mb-0 d-none d-md-inline">Template:</label>
                        <select class="form-select form-select-sm py-0" id="templatePresetSelect" style="max-width: 220px;">
                            <option value="">-- Preset Template --</option>
                            <?php foreach ($templates as $tpl): ?>
                                <option value="<?= $tpl['id'] ?>"><?= e($tpl['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label class="small text-muted mb-0 d-none d-sm-inline ms-1">Mode:</label>
                        <select class="form-select form-select-sm py-0" id="commissionModeSelect" name="commission_mode" style="width: auto;">
                            <option value="markup" <?= ($quote['commission_mode'] ?? 'markup') === 'markup' ? 'selected' : '' ?>>Mode 1: Markup</option>
                            <option value="margin" <?= ($quote['commission_mode'] ?? '') === 'margin' ? 'selected' : '' ?>>Mode 2: Margin</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div id="lineItemsContainer">
                        <!-- Dynamic Item Cards Injected via JavaScript -->
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        <button type="button" class="btn btn-sm btn-brand-primary" id="btnAddLineItem">
                            <i class="bi bi-plus-lg me-1"></i> Add Another Service / Item
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. Commercial Totals & Tax Adjustments -->
            <div class="card-brand">
                <div class="card-header py-2 px-3 bg-light">
                    <span class="fw-bold small text-dark"><i class="bi bi-calculator-fill me-1 text-success"></i> 3. Discounts, Taxes & Financial Summary</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1">Special Discount (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="discountInput" name="discount_amount" value="<?= (float)($quote['discount_amount'] ?? 0) ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1">Transport Charges (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="transportInput" name="transportation_charges" value="<?= (float)($quote['transportation_charges'] ?? 0) ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1">Installation / Fixing (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="installationInput" name="installation_charges" value="<?= (float)($quote['installation_charges'] ?? 0) ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1">GST Applicable (%)</label>
                            <input type="number" step="0.1" min="0" class="form-control form-control-sm" id="gstRateInput" name="gst_rate" value="<?= (float)($quote['gst_rate'] ?? 18.0) ?>">
                        </div>
                    </div>

                    <!-- Financial Summary Box -->
                    <div class="totals-summary-card bg-light">
                        <div class="summary-row">
                            <span>Subtotal (Line Items Total):</span>
                            <strong id="summarySubtotal">₹0.00</strong>
                        </div>
                        <div class="summary-row text-success">
                            <span>Total Commission Earned:</span>
                            <div>
                                <strong id="summaryCommission">₹0.00</strong>
                                <span class="badge bg-success-subtle text-success ms-1" id="summaryMargin">15% margin</span>
                            </div>
                        </div>
                        <div class="summary-row">
                            <span>Taxable Amount:</span>
                            <strong id="summaryTaxable">₹0.00</strong>
                        </div>
                        <div class="summary-row">
                            <span>GST Amount (18%):</span>
                            <strong id="summaryGstAmount">₹0.00</strong>
                        </div>
                        <div class="summary-row">
                            <span>Round Off:</span>
                            <span id="summaryRoundOff">₹0.00</span>
                        </div>
                        <div class="summary-row total">
                            <span>FINAL PAYABLE GRAND TOTAL:</span>
                            <span class="grand-price" id="summaryGrandTotal">₹0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Commercial Terms & Conditions -->
            <div class="card-brand">
                <div class="card-header py-2 px-3 bg-light">
                    <span class="fw-bold small text-dark"><i class="bi bi-file-text me-1 text-secondary"></i> 4. Terms, Delivery & Notes</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold mb-1">Delivery Timeline</label>
                            <input type="text" class="form-control form-control-sm" id="deliveryTimeInput" name="delivery_time" value="<?= e($quote['delivery_time'] ?? '3 to 7 working days from artwork approval') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold mb-1">Payment Terms</label>
                            <input type="text" class="form-control form-control-sm" id="paymentTermsInput" name="payment_terms" value="<?= e($quote['payment_terms'] ?? '50% Advance with Purchase Order, 50% upon delivery/installation') ?>">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold mb-1">Terms & Conditions</label>
                        <textarea class="form-control form-control-sm" id="termsInput" name="terms_and_conditions" rows="3"><?= e($quote['terms_and_conditions'] ?? "1. Rates are valid for 15 days from quotation date.\n2. GST 18% charged extra as applicable.\n3. Vector artwork (CDR/AI/PDF) to be supplied by client.\n4. 1 Year warranty on Samsung LED modules.") ?></textarea>
                    </div>
                    <div>
                        <label class="form-label small text-muted mb-1">Private Sales Notes (Internal Only)</label>
                        <input type="text" class="form-control form-control-sm" id="notesInput" name="notes" value="<?= e($quote['notes'] ?? '') ?>" placeholder="e.g. Promised 2 extra spot lights free of charge">
                    </div>
                </div>
            </div>

            <!-- Submit Footer -->
            <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 pt-2 pb-5 mt-2">
                <a href="<?= url('quotations') ?>" class="btn btn-outline-secondary px-3 py-2 text-center">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-brand-primary px-4 py-2 fs-6 shadow text-center">
                    <i class="bi bi-check-lg me-1"></i> Save & Generate Quotation
                </button>
            </div>
        </div>

        <!-- Right Live Document Preview Pane -->
        <div class="builder-preview-pane">
            <div class="preview-paper" id="livePreviewSheet">
                <!-- Preview Header -->
                <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3" style="border-color: #FF5500 !important; border-bottom-width: 3px !important;">
                    <div class="d-flex align-items-center gap-2">
                        <img src="<?= asset('images/logo.svg') ?>" style="height: 48px; max-width: 240px;">
                    </div>
                    <div class="text-end">
                        <div class="badge bg-dark text-white px-2 py-1 text-uppercase fw-bold" style="letter-spacing: 1px;">QUOTATION</div>
                        <div class="small fw-bold text-dark mt-1 font-monospace"><?= e($previewQuoteNumber) ?></div>
                        <div class="small text-muted">Date: <span id="prevDate"><?= date('d-M-Y') ?></span></div>
                        <div class="small text-muted">Valid: <span id="prevValidUntil"><?= date('d-M-Y', strtotime('+15 days')) ?></span></div>
                    </div>
                </div>

                <!-- Client & Project Preview -->
                <div class="bg-light p-3 rounded mb-3 border small">
                    <div class="row g-2">
                        <div class="col-6 border-end">
                            <div class="text-uppercase fw-bold small" style="color: #FF5500 !important;">Quotation For:</div>
                            <h6 class="fw-bold text-dark mb-1" id="prevCustomerName">Select a client on the left</h6>
                            <div class="text-muted small" id="prevCustomerDetails">Address, phone and tax info will appear here.</div>
                        </div>
                        <div class="col-6 ps-3">
                            <div class="text-uppercase fw-bold small" style="color: #FF5500 !important;">Project:</div>
                            <h6 class="fw-bold text-dark mb-1" id="prevProjectName"><?= e($quote['project_name'] ?? 'Storefront 3D LED Signboard & ACP Cladding') ?></h6>
                            <div class="text-muted small mt-1"><strong>Timeline:</strong> <span id="prevDeliveryTime"><?= e($quote['delivery_time'] ?? '3 to 7 working days from artwork approval') ?></span></div>
                            <div class="text-muted small mt-1"><strong>Payment:</strong> <span id="prevPaymentTerms"><?= e($quote['payment_terms'] ?? '50% Advance with Purchase Order, 50% upon delivery/installation') ?></span></div>
                        </div>
                    </div>
                </div>

                <!-- Line Items Table -->
                <table class="table table-sm table-bordered mb-3 small align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width: 30px;">#</th>
                            <th>Description</th>
                            <th class="text-center" style="width: 75px;">Size</th>
                            <th class="text-center" style="width: 40px;">Qty</th>
                            <th class="text-center" style="width: 50px;">Unit</th>
                            <th class="text-end" style="width: 80px;">Rate (₹)</th>
                            <th class="text-end" style="width: 85px;">Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody id="prevItemsTableBody">
                        <!-- Populated by JS -->
                    </tbody>
                </table>

                <!-- Summary & Terms -->
                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <div class="p-2 border rounded bg-white small">
                            <div class="fw-bold text-uppercase small text-muted mb-1">Terms & Conditions:</div>
                            <div class="small text-muted" id="prevTermsText" style="white-space: pre-line;"><?= e($quote['terms_and_conditions'] ?? "1. Rates are valid for 15 days from quotation date.\n2. GST 18% charged extra as applicable.\n3. Vector artwork (CDR/AI/PDF) to be supplied by client.\n4. 1 Year warranty on Samsung LED modules.") ?></div>
                        </div>
                    </div>
                    <div class="col-5">
                        <table class="table table-sm table-borderless small mb-0">
                            <tr>
                                <td class="text-end text-muted">Subtotal:</td>
                                <td class="text-end fw-bold" id="prevSubtotal">₹0.00</td>
                            </tr>
                            <tr id="prevDiscountRow" style="display: none;">
                                <td class="text-end text-success">Discount:</td>
                                <td class="text-end text-success fw-bold" id="prevDiscount">- ₹0.00</td>
                            </tr>
                            <tr id="prevTransportRow" style="display: none;">
                                <td class="text-end text-muted">Transport:</td>
                                <td class="text-end" id="prevTransport">₹0.00</td>
                            </tr>
                            <tr id="prevInstallRow" style="display: none;">
                                <td class="text-end text-muted">Installation:</td>
                                <td class="text-end" id="prevInstall">₹0.00</td>
                            </tr>
                            <tr>
                                <td class="text-end text-muted">Taxable:</td>
                                <td class="text-end fw-bold" id="prevTaxable">₹0.00</td>
                            </tr>
                            <tr>
                                <td class="text-end text-muted">GST (<span id="prevGstRate">18</span>%):</td>
                                <td class="text-end fw-bold" id="prevGstAmount">₹0.00</td>
                            </tr>
                            <tr id="prevRoundOffRow" style="display: none;">
                                <td class="text-end text-muted">Round Off:</td>
                                <td class="text-end" id="prevRoundOff">₹0.00</td>
                            </tr>
                            <tr class="table-dark">
                                <td class="text-end fw-bold py-2">GRAND TOTAL:</td>
                                <td class="text-end fw-bold py-2 fs-6 text-warning" id="prevGrandTotal">₹0.00</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Footer Signatory -->
                <div class="d-flex justify-content-between align-items-end pt-3 border-top small text-muted">
                    <div>
                        <strong>Bank:</strong> Canara Bank, Hubballi &bull; <strong>A/C:</strong> 0321201004567<br>
                        <strong>PhonePe / GooglePay:</strong> 9611620862@upi
                    </div>
                    <div class="text-center" style="width: 140px;">
                        <div style="border-bottom: 1px solid #333; height: 35px;"></div>
                        <div class="fw-bold mt-1">Authorized Signatory</div>
                        <div class="small">Sagar Advertising</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="<?= asset('js/quotation-builder.js') ?>"></script>
