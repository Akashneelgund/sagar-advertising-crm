/**
 * Sagar Advertising CRM - Multi-Step Excel Import Wizard
 */

class ExcelImportWizard {
    constructor() {
        this.step = 1;
        this.parsedData = null;
        this.init();
    }

    init() {
        this.uploadForm = document.getElementById('excelUploadForm');
        this.fileInput = document.getElementById('excelFileInput');
        this.step1 = document.getElementById('wizardStep1');
        this.step2 = document.getElementById('wizardStep2');
        this.step3 = document.getElementById('wizardStep3');
        this.stepIndicators = document.querySelectorAll('.wizard-step');

        if (this.uploadForm) {
            this.uploadForm.addEventListener('submit', (e) => this.handleUpload(e));
        }

        const btnProceedImport = document.getElementById('btnProceedImport');
        if (btnProceedImport) {
            btnProceedImport.addEventListener('click', () => this.handleImportExecution());
        }
    }

    setStep(stepNum) {
        this.step = stepNum;
        this.stepIndicators.forEach((el, idx) => {
            if (idx + 1 < stepNum) {
                el.classList.add('completed');
                el.classList.remove('active');
            } else if (idx + 1 === stepNum) {
                el.classList.add('active');
                el.classList.remove('completed');
            } else {
                el.classList.remove('active', 'completed');
            }
        });

        this.step1.style.display = stepNum === 1 ? 'block' : 'none';
        this.step2.style.display = stepNum === 2 ? 'block' : 'none';
        this.step3.style.display = stepNum === 3 ? 'block' : 'none';
    }

    async handleUpload(e) {
        e.preventDefault();
        const file = this.fileInput.files[0];
        if (!file) {
            showToast('Please select a CSV or Excel file to upload.', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('excel_file', file);

        const btn = this.uploadForm.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Analyzing file...';

        const res = await apiRequest(`${window.APP_URL || ''}/api/excel/parse`, 'POST', formData);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-upload me-2"></i>Upload & Preview Columns';

        if (res.ok && res.data && res.data.success) {
            this.parsedData = res.data;
            this.renderMappingAndPreview(res.data);
            this.setStep(2);
        } else {
            showToast(res.data?.error || 'Failed to parse file.', 'error');
        }
    }

    renderMappingAndPreview(data) {
        const mappingTable = document.getElementById('mappingTableBody');
        const previewHeaders = document.getElementById('previewTableHeader');
        const previewRows = document.getElementById('previewTableBody');

        const dbFields = [
            { key: 'ignore', label: '-- Do Not Import / Ignore --' },
            { key: 'company_name', label: 'Company / Business Name *' },
            { key: 'contact_person', label: 'Contact Person *' },
            { key: 'mobile', label: 'Mobile Number *' },
            { key: 'alternate_mobile', label: 'Alternate Mobile' },
            { key: 'email', label: 'Email Address' },
            { key: 'whatsapp', label: 'WhatsApp Number' },
            { key: 'address', label: 'Street Address' },
            { key: 'city', label: 'City' },
            { key: 'state', label: 'State' },
            { key: 'pincode', label: 'Pincode' },
            { key: 'gstin', label: 'GST Number' },
            { key: 'customer_type', label: 'Customer Type (Business/Corporate/Individual)' },
            { key: 'source', label: 'Source (Direct/Referral/etc)' },
            { key: 'notes', label: 'Customer Notes' },
            { key: 'status', label: 'Status (Active/Lead/Inactive)' }
        ];

        // 1. Render Mapping rows
        let mapHtml = '';
        data.headers.forEach((header, colIdx) => {
            const hLower = header.toLowerCase().replace(/[^a-z0-9]/g, '');
            
            // Smart auto-detection of column mapping
            let bestMatch = 'ignore';
            if (hLower.includes('company') || hLower.includes('business') || hLower.includes('firm')) bestMatch = 'company_name';
            else if (hLower.includes('contact') || hLower.includes('person') || hLower.includes('name') || hLower.includes('client')) bestMatch = 'contact_person';
            else if (hLower.includes('phone') || hLower.includes('mobile') || hLower.includes('cell')) bestMatch = 'mobile';
            else if (hLower.includes('alt') || hLower.includes('secondphone')) bestMatch = 'alternate_mobile';
            else if (hLower.includes('email') || hLower.includes('mail')) bestMatch = 'email';
            else if (hLower.includes('whats') || hLower.includes('wa')) bestMatch = 'whatsapp';
            else if (hLower.includes('address') || hLower.includes('street')) bestMatch = 'address';
            else if (hLower.includes('city') || hLower.includes('town')) bestMatch = 'city';
            else if (hLower.includes('state')) bestMatch = 'state';
            else if (hLower.includes('pin') || hLower.includes('zip')) bestMatch = 'pincode';
            else if (hLower.includes('gst') || hLower.includes('tax')) bestMatch = 'gstin';
            else if (hLower.includes('type')) bestMatch = 'customer_type';
            else if (hLower.includes('source')) bestMatch = 'source';
            else if (hLower.includes('note') || hLower.includes('remark')) bestMatch = 'notes';

            const sampleVal = data.preview[0] ? (data.preview[0][colIdx] || '') : '-';

            mapHtml += `
                <tr>
                    <td class="fw-bold">${header}</td>
                    <td class="text-muted small">${sampleVal}</td>
                    <td>
                        <select class="form-select form-select-sm mapping-select" data-col="${colIdx}">
                            ${dbFields.map(f => `
                                <option value="${f.key}" ${f.key === bestMatch ? 'selected' : ''}>${f.label}</option>
                            `).join('')}
                        </select>
                    </td>
                </tr>
            `;
        });
        mappingTable.innerHTML = mapHtml;

        // 2. Render preview table
        previewHeaders.innerHTML = data.headers.map(h => `<th>${h}</th>`).join('');
        previewRows.innerHTML = data.preview.map(row => `
            <tr>
                ${row.map(cell => `<td>${cell || ''}</td>`).join('')}
            </tr>
        `).join('');

        document.getElementById('totalRowCountBadge').textContent = `${data.total_rows} Records Found`;
    }

    async handleImportExecution() {
        const mapping = {};
        const selects = document.querySelectorAll('.mapping-select');
        selects.forEach(sel => {
            mapping[sel.dataset.col] = sel.value;
        });

        const btn = document.getElementById('btnProceedImport');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Validating & Importing...';

        const payload = {
            temp_file: this.parsedData.temp_file,
            mapping: mapping
        };

        const res = await apiRequest(`${window.APP_URL || ''}/api/excel/execute-import`, 'POST', payload);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Confirm & Import';

        if (res.ok && res.data && res.data.success) {
            this.renderSummary(res.data);
            this.setStep(3);
        } else {
            showToast(res.data?.error || 'Import failed. Check mapping.', 'error');
        }
    }

    renderSummary(result) {
        document.getElementById('statImportedCount').textContent = result.imported || 0;
        document.getElementById('statSkippedCount').textContent = result.skipped || 0;
        document.getElementById('statErrorsCount').textContent = result.errors_count || 0;

        const errorBox = document.getElementById('importErrorsContainer');
        if (result.errors && result.errors.length > 0) {
            let errHtml = '<ul class="list-group list-group-flush small">';
            result.errors.slice(0, 15).forEach(err => {
                errHtml += `<li class="list-group-item text-danger py-1"><i class="bi bi-exclamation-circle me-1"></i>${err}</li>`;
            });
            if (result.errors.length > 15) {
                errHtml += `<li class="list-group-item text-muted py-1">...and ${result.errors.length - 15} more errors</li>`;
            }
            errHtml += '</ul>';
            errorBox.innerHTML = errHtml;
            errorBox.style.display = 'block';
        } else {
            errorBox.style.display = 'none';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('excelUploadForm')) {
        window.importWizard = new ExcelImportWizard();
    }
});
