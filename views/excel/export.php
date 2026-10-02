<?php
/**
 * Sagar Advertising CRM - Filterable Data Export Center
 */
?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;">Data Export Center</h2>
                <p class="text-muted mb-0">Download customer lists, quotation records, and sales data in CSV or Microsoft Excel formats.</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Customer Export Card -->
            <div class="col-12 col-md-6">
                <div class="card-brand h-100 p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon" style="background:#E0F2FE; color:#0284C7; border-radius: 14px;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">Export Customers</h4>
                            <span class="text-muted small">Download complete customer directory</span>
                        </div>
                    </div>

                    <form method="GET" action="<?= url('excel/export-customers') ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Customer Status</label>
                            <select class="form-select" name="status">
                                <option value="">-- All Statuses --</option>
                                <option value="Active">Active Clients Only</option>
                                <option value="Lead">Leads Only</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Customer Type</label>
                            <select class="form-select" name="type">
                                <option value="">-- All Types --</option>
                                <option value="Business">Business</option>
                                <option value="Corporate">Corporate</option>
                                <option value="Dealer">Dealer</option>
                                <option value="Individual">Individual</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Export Format</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="fmtCustCsv" value="csv" checked>
                                    <label class="form-check-label" for="fmtCustCsv">CSV (.csv)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="fmtCustXls" value="excel">
                                    <label class="form-check-label" for="fmtCustXls">Excel (.xls)</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-brand-primary w-100 py-2">
                            <i class="bi bi-download me-1"></i> Download Customers File
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quotation Export Card -->
            <div class="col-12 col-md-6">
                <div class="card-brand h-100 p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon" style="background:#FFF3E0; color:#FF5500; border-radius: 14px;">
                            <i class="bi bi-file-earmark-ruled-fill"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">Export Quotations</h4>
                            <span class="text-muted small">Download commercial estimates and pricing</span>
                        </div>
                    </div>

                    <form method="GET" action="<?= url('excel/export-quotations') ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Quotation Status</label>
                            <select class="form-select" name="status">
                                <option value="">-- All Statuses --</option>
                                <option value="Approved">Approved / Won Only</option>
                                <option value="Sent">Sent to Client</option>
                                <option value="Draft">Drafts Only</option>
                                <option value="Under Discussion">Under Discussion</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Export Format</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="fmtQuoteCsv" value="csv" checked>
                                    <label class="form-check-label" for="fmtQuoteCsv">CSV (.csv)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="fmtQuoteXls" value="excel">
                                    <label class="form-check-label" for="fmtQuoteXls">Excel (.xls)</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-brand-dark w-100 py-2 mt-4">
                            <i class="bi bi-download me-1"></i> Download Quotations File
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
