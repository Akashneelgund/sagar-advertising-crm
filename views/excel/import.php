<?php
/**
 * Sagar Advertising CRM - Multi-Step Excel / CSV Import Wizard View
 */
?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-11">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;">Excel & CSV Customer Importer</h2>
                <p class="text-muted mb-0">Import existing client records from Excel spreadsheets with column mapping and duplicate validation.</p>
            </div>
            <a href="<?= url('customers') ?>" class="btn-brand-outline text-nowrap">
                <i class="bi bi-people-fill me-1"></i> View Customers
            </a>
        </div>

        <!-- Wizard Progress Indicator -->
        <div class="wizard-steps">
            <div class="wizard-step active" id="stepIndicator1">
                <div class="step-circle">1</div>
                <div class="step-label">Upload File</div>
            </div>
            <div class="wizard-step" id="stepIndicator2">
                <div class="step-circle">2</div>
                <div class="step-label">Map & Validate</div>
            </div>
            <div class="wizard-step" id="stepIndicator3">
                <div class="step-circle">3</div>
                <div class="step-label">Import Report</div>
            </div>
        </div>

        <!-- Step 1: File Upload Screen -->
        <div id="wizardStep1" class="card-brand p-4">
            <div class="row justify-content-center text-center py-4">
                <div class="col-md-8">
                    <div class="stat-icon mx-auto mb-3" style="width: 70px; height: 70px; font-size: 32px; background: #DCFCE7; color: #16A34A; border-radius: 20px;">
                        <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Upload Your Customer Excel or CSV File</h4>
                    <p class="text-muted small mb-4">Supports Microsoft Excel (.xlsx, .xls) and Comma-Separated Values (.csv). The system will let you preview columns and match them before saving.</p>

                    <form id="excelUploadForm" enctype="multipart/form-data">
                        <div class="mb-4">
                            <input class="form-control form-control-lg" type="file" id="excelFileInput" name="excel_file" accept=".csv, .xlsx, .xls, .txt, .xml" required>
                        </div>
                        <button type="submit" class="btn btn-brand-primary btn-lg px-4 shadow">
                            <i class="bi bi-upload me-2"></i>Upload & Preview Columns
                        </button>
                    </form>

                    <div class="mt-4 pt-3 border-top small text-muted">
                        Need a template? <a href="<?= url('excel/export-customers?format=csv') ?>" class="text-decoration-none fw-bold text-dark"><i class="bi bi-download me-1"></i>Download Sample CSV Format</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Mapping & Preview Screen -->
        <div id="wizardStep2" class="card-brand p-4" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-0">Step 2: Map Columns & Validate Data</h4>
                    <p class="text-muted small mb-0">Match your Excel column headers to Sagar Advertising CRM database fields.</p>
                </div>
                <span class="badge bg-success fs-6" id="totalRowCountBadge">0 Records</span>
            </div>

            <!-- Mapping Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 30%;">Excel Header</th>
                            <th style="width: 30%;">Sample First Row Value</th>
                            <th style="width: 40%;">Map to Sagar CRM Field</th>
                        </tr>
                    </thead>
                    <tbody id="mappingTableBody">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>

            <!-- Preview First 5 Rows -->
            <h5 class="fw-bold mb-2">Data Preview (First 5 Rows)</h5>
            <div class="table-responsive border rounded mb-4" style="max-height: 250px;">
                <table class="table table-sm table-striped small mb-0">
                    <thead class="table-light" id="previewTableHeader">
                        <!-- Headers -->
                    </thead>
                    <tbody id="previewTableBody">
                        <!-- Rows -->
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <button type="button" class="btn btn-outline-secondary" onclick="window.importWizard.setStep(1)">
                    <i class="bi bi-arrow-left me-1"></i> Upload Different File
                </button>
                <button type="button" class="btn btn-brand-primary btn-lg px-4" id="btnProceedImport">
                    <i class="bi bi-check-circle me-1"></i> Confirm & Import to CRM
                </button>
            </div>
        </div>

        <!-- Step 3: Import Report & Summary Screen -->
        <div id="wizardStep3" class="card-brand p-4" style="display: none;">
            <div class="text-center py-4">
                <div class="stat-icon mx-auto mb-3" style="width: 70px; height: 70px; font-size: 32px; background: #ECFDF5; color: #059669; border-radius: 20px;">
                    <i class="bi bi-check-all"></i>
                </div>
                <h3 class="fw-bold mb-2">Import Process Completed!</h3>
                <p class="text-muted small">Here is the detailed summary of the records processed into the database.</p>
            </div>

            <div class="row g-3 mb-4 text-center">
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-success-subtle border-success">
                        <div class="text-success fw-bold small text-uppercase">Imported Successfully</div>
                        <div class="fs-1 fw-black text-success" id="statImportedCount">0</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-warning-subtle border-warning">
                        <div class="text-warning fw-bold small text-uppercase">Skipped Duplicates</div>
                        <div class="fs-1 fw-black text-warning" id="statSkippedCount">0</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-danger-subtle border-danger">
                        <div class="text-danger fw-bold small text-uppercase">Validation Errors</div>
                        <div class="fs-1 fw-black text-danger" id="statErrorsCount">0</div>
                    </div>
                </div>
            </div>

            <!-- Error List Box -->
            <div id="importErrorsContainer" class="border rounded p-3 mb-4 bg-light" style="display: none; max-height: 250px; overflow-y: auto;">
                <!-- Error items -->
            </div>

            <div class="d-flex justify-content-center gap-3">
                <a href="<?= url('customers') ?>" class="btn btn-brand-primary btn-lg px-4">
                    <i class="bi bi-people-fill me-2"></i> View Customer Directory
                </a>
                <button type="button" class="btn btn-outline-secondary btn-lg" onclick="window.location.reload()">
                    Import Another File
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('js/excel-import.js') ?>"></script>
