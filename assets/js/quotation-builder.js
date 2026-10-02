/**
 * Sagar Advertising CRM - Quotation Builder & Live Synchronizer
 * Handles real-time commercial math, dynamic item rows, and live preview rendering.
 */

class QuotationBuilder {
    constructor() {
        this.items = [];
        this.servicesCatalog = window.SERVICES_CATALOG || [];
        this.customersCatalog = window.CUSTOMERS_CATALOG || [];
        this.commissionMode = 'markup'; // 'markup' or 'margin'
        this.defaultCommission = 15.0;
        this.defaultGstRate = 18.0;

        this.initElements();
        this.bindEvents();
        this.loadInitialData();
    }

    initElements() {
        this.itemsContainer = document.getElementById('lineItemsContainer');
        this.btnAddItem = document.getElementById('btnAddLineItem');
        this.customerSelect = document.getElementById('customerSelect');
        this.projectNameInput = document.getElementById('projectNameInput');
        this.quotationDateInput = document.getElementById('quotationDateInput');
        this.validUntilInput = document.getElementById('validUntilInput');
        this.referenceInput = document.getElementById('referenceInput');
        this.notesInput = document.getElementById('notesInput');
        this.termsInput = document.getElementById('termsInput');
        this.paymentTermsInput = document.getElementById('paymentTermsInput');
        this.deliveryTimeInput = document.getElementById('deliveryTimeInput');
        this.commissionModeSelect = document.getElementById('commissionModeSelect');
        this.templatePresetSelect = document.getElementById('templatePresetSelect');
        this.discountInput = document.getElementById('discountInput');
        this.transportInput = document.getElementById('transportInput');
        this.installationInput = document.getElementById('installationInput');
        this.gstRateInput = document.getElementById('gstRateInput');

        // Summary elements
        this.displaySubtotal = document.getElementById('summarySubtotal');
        this.displayCommission = document.getElementById('summaryCommission');
        this.displayMargin = document.getElementById('summaryMargin');
        this.displayTaxable = document.getElementById('summaryTaxable');
        this.displayGstAmount = document.getElementById('summaryGstAmount');
        this.displayRoundOff = document.getElementById('summaryRoundOff');
        this.displayGrandTotal = document.getElementById('summaryGrandTotal');

        // Live preview elements
        this.prevCustomerName = document.getElementById('prevCustomerName');
        this.prevCustomerDetails = document.getElementById('prevCustomerDetails');
        this.prevProjectName = document.getElementById('prevProjectName');
        this.prevDate = document.getElementById('prevDate');
        this.prevValidUntil = document.getElementById('prevValidUntil');
        this.prevItemsTableBody = document.getElementById('prevItemsTableBody');
        this.prevSubtotal = document.getElementById('prevSubtotal');
        this.prevDiscount = document.getElementById('prevDiscount');
        this.prevDiscountRow = document.getElementById('prevDiscountRow');
        this.prevTransport = document.getElementById('prevTransport');
        this.prevTransportRow = document.getElementById('prevTransportRow');
        this.prevInstall = document.getElementById('prevInstall');
        this.prevInstallRow = document.getElementById('prevInstallRow');
        this.prevTaxable = document.getElementById('prevTaxable');
        this.prevGstRate = document.getElementById('prevGstRate');
        this.prevGstAmount = document.getElementById('prevGstAmount');
        this.prevRoundOff = document.getElementById('prevRoundOff');
        this.prevRoundOffRow = document.getElementById('prevRoundOffRow');
        this.prevGrandTotal = document.getElementById('prevGrandTotal');
        this.prevTermsText = document.getElementById('prevTermsText');
        this.prevPaymentTerms = document.getElementById('prevPaymentTerms');
        this.prevDeliveryTime = document.getElementById('prevDeliveryTime');
    }

    bindEvents() {
        if (this.btnAddItem) {
            this.btnAddItem.addEventListener('click', () => this.addItem());
        }

        if (this.customerSelect) {
            this.customerSelect.addEventListener('change', () => this.syncCustomer());
        }

        if (this.projectNameInput) {
            this.projectNameInput.addEventListener('input', () => {
                if (this.prevProjectName) {
                    this.prevProjectName.textContent = this.projectNameInput.value.trim() || 'Advertising & Branding Work';
                }
            });
        }

        if (this.quotationDateInput) {
            this.quotationDateInput.addEventListener('change', () => {
                if (this.prevDate) {
                    this.prevDate.textContent = this.formatDate(this.quotationDateInput.value);
                }
            });
        }

        if (this.validUntilInput) {
            this.validUntilInput.addEventListener('change', () => {
                if (this.prevValidUntil) {
                    this.prevValidUntil.textContent = this.formatDate(this.validUntilInput.value);
                }
            });
        }

        if (this.commissionModeSelect) {
            this.commissionModeSelect.addEventListener('change', (e) => {
                this.commissionMode = e.target.value;
                this.recalculateAll();
            });
        }

        if (this.templatePresetSelect) {
            this.templatePresetSelect.addEventListener('change', (e) => {
                const tplId = e.target.value;
                if (!tplId) return;
                const templates = window.QUOTATION_TEMPLATES || [];
                const tpl = templates.find(t => t.id == tplId);
                if (tpl) {
                    this.loadTemplate(tpl);
                }
            });
        }

        [this.discountInput, this.transportInput, this.installationInput, this.gstRateInput].forEach(inp => {
            if (inp) {
                inp.addEventListener('input', () => this.recalculateAll());
            }
        });

        if (this.termsInput) {
            this.termsInput.addEventListener('input', () => {
                if (this.prevTermsText) {
                    this.prevTermsText.textContent = this.termsInput.value.trim();
                }
            });
        }

        if (this.paymentTermsInput) {
            this.paymentTermsInput.addEventListener('input', () => {
                if (this.prevPaymentTerms) {
                    this.prevPaymentTerms.textContent = this.paymentTermsInput.value.trim() || '50% Advance with Purchase Order, 50% upon delivery/installation';
                }
            });
        }

        if (this.deliveryTimeInput) {
            this.deliveryTimeInput.addEventListener('input', () => {
                if (this.prevDeliveryTime) {
                    this.prevDeliveryTime.textContent = this.deliveryTimeInput.value.trim() || '3 to 7 working days from artwork approval';
                }
            });
        }
    }

    syncAllMetadata() {
        if (this.prevDeliveryTime && this.deliveryTimeInput) {
            this.prevDeliveryTime.textContent = this.deliveryTimeInput.value.trim() || '3 to 7 working days from artwork approval';
        }
        if (this.prevPaymentTerms && this.paymentTermsInput) {
            this.prevPaymentTerms.textContent = this.paymentTermsInput.value.trim() || '50% Advance with Purchase Order, 50% upon delivery/installation';
        }
        if (this.prevProjectName && this.projectNameInput) {
            this.prevProjectName.textContent = this.projectNameInput.value.trim() || 'Storefront 3D LED Signboard & ACP Cladding';
        }
        if (this.prevTermsText && this.termsInput) {
            this.prevTermsText.textContent = this.termsInput.value.trim();
        }
        if (this.prevDate && this.quotationDateInput) {
            this.prevDate.textContent = this.formatDate(this.quotationDateInput.value);
        }
        if (this.prevValidUntil && this.validUntilInput) {
            this.prevValidUntil.textContent = this.formatDate(this.validUntilInput.value);
        }
        this.syncCustomer();
    }

    loadTemplate(tpl) {
        let items = [];
        try {
            items = typeof tpl.items_json === 'string' ? JSON.parse(tpl.items_json) : (tpl.items_json || []);
        } catch (err) {
            console.error('Failed to parse template items JSON', err);
        }

        if (items && items.length > 0) {
            if (this.itemsContainer) this.itemsContainer.innerHTML = '';
            this.items = [];
            items.forEach(it => this.addItem(it));
        }

        if (tpl.default_terms && this.termsInput) {
            this.termsInput.value = tpl.default_terms;
        }
        if (tpl.default_notes && this.notesInput) {
            this.notesInput.value = tpl.default_notes;
        }
        if (tpl.name && this.projectNameInput && !this.projectNameInput.value.trim()) {
            this.projectNameInput.value = tpl.name;
        }

        this.syncAllMetadata();
        this.recalculateAll();
        if (typeof showToast === 'function') {
            showToast(`Loaded template: "${tpl.name}"`, 'success');
        }
    }

    loadInitialData() {
        if (window.INITIAL_QUOTE_DATA && window.INITIAL_QUOTE_DATA.items && window.INITIAL_QUOTE_DATA.items.length > 0) {
            this.commissionMode = window.INITIAL_QUOTE_DATA.commission_mode || 'markup';
            if (this.commissionModeSelect) this.commissionModeSelect.value = this.commissionMode;
            window.INITIAL_QUOTE_DATA.items.forEach(item => this.addItem(item));
        } else {
            // Add two default initial item rows
            this.addItem({
                service_id: 2,
                item_name: '3D/2D LED Boards',
                description: '3D Acrylic Channel Letters with high lumen Samsung LED modules',
                size_dimension: '15 x 3 ft',
                quantity: 45,
                unit: 'Sq Ft',
                actual_price: 450,
                commission_rate: 18
            });
            this.addItem({
                service_id: 5,
                item_name: 'ACP Interiors & Cladding',
                description: 'Exterior metallic grade ACP panel facade cladding',
                size_dimension: '20 x 4 ft',
                quantity: 80,
                unit: 'Sq Ft',
                actual_price: 260,
                commission_rate: 15
            });
        }
        this.syncAllMetadata();
        this.recalculateAll();
    }

    addItem(data = null) {
        const id = 'item_' + Math.random().toString(36).substr(2, 9);
        const item = {
            id: id,
            service_id: data ? data.service_id : '',
            item_name: data ? data.item_name : '',
            description: data ? data.description : '',
            size_dimension: data ? data.size_dimension : '',
            quantity: data ? parseFloat(data.quantity) || 1 : 1,
            unit: data ? data.unit : 'Sq Ft',
            actual_price: data ? parseFloat(data.actual_price) || 0 : 0,
            commission_rate: data ? parseFloat(data.commission_rate) || this.defaultCommission : this.defaultCommission,
            commission_amount: 0,
            selling_price: 0,
            total_amount: 0
        };

        this.items.push(item);
        this.renderItemHtml(item);
        this.recalculateAll();
    }

    renderItemHtml(item) {
        const index = this.items.indexOf(item) + 1;
        const html = `
            <div class="line-item-card" id="${item.id}" data-id="${item.id}">
                <div class="line-item-header">
                    <span class="item-index-badge">Item #${index}</span>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-duplicate-item" title="Duplicate Item">
                            <i class="bi bi-copy"></i>
                        </button>
                        <button type="button" class="btn-remove-item" title="Delete Item">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold mb-1">Service / Product</label>
                        <select class="form-select form-select-sm item-service-select">
                            <option value="">-- Select Pre-configured Service --</option>
                            ${this.servicesCatalog.map(s => `
                                <option value="${s.id}" ${item.service_id == s.id ? 'selected' : ''}>
                                    ${s.name} (Default: ₹${s.default_price}/${s.default_unit})
                                </option>
                            `).join('')}
                            <option value="custom" ${!item.service_id ? 'selected' : ''}>Custom Item / Service</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small fw-bold mb-1">Item Title</label>
                        <input type="text" class="form-control form-control-sm item-name-input" value="${item.item_name}" placeholder="Service or Item Name">
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-md-12">
                        <label class="form-label small text-muted mb-1">Description / Technical Specifications</label>
                        <textarea class="form-control form-control-sm item-desc-input" rows="1" placeholder="Materials, frame gauges, lighting, warranty">${item.description}</textarea>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold mb-1">Size / Dimension</label>
                        <input type="text" class="form-control form-control-sm item-size-input" value="${item.size_dimension}" placeholder="e.g. 12x4 ft">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold mb-1">Quantity</label>
                        <input type="number" step="any" min="0.01" class="form-control form-control-sm item-qty-input" value="${item.quantity}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold mb-1">Unit</label>
                        <select class="form-select form-select-sm item-unit-select">
                            ${['Sq Ft', 'Sq Inch', 'Piece', 'Running Ft', 'Day', 'Hour', 'Unit', 'Custom'].map(u => `
                                <option value="${u}" ${item.unit === u ? 'selected' : ''}>${u}</option>
                            `).join('')}
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold mb-1">Actual Price (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm item-actual-input" value="${item.actual_price}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold mb-1">Commission %</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm item-comm-input" value="${item.commission_rate}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold mb-1">Selling Rate</label>
                        <input type="text" readonly class="form-control form-control-sm bg-light fw-bold text-end item-selling-display" value="₹0.00">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top small text-muted">
                    <div>Commission: <strong class="text-success item-comm-amount-display">₹0.00</strong></div>
                    <div>Line Total: <strong class="text-dark fs-6 item-total-display">₹0.00</strong></div>
                </div>
            </div>
        `;

        this.itemsContainer.insertAdjacentHTML('beforeend', html);
        this.bindItemRowEvents(document.getElementById(item.id));
    }

    bindItemRowEvents(rowElem) {
        const itemId = rowElem.dataset.id;
        const item = this.items.find(i => i.id === itemId);
        if (!item) return;

        const serviceSelect = rowElem.querySelector('.item-service-select');
        const nameInput = rowElem.querySelector('.item-name-input');
        const descInput = rowElem.querySelector('.item-desc-input');
        const sizeInput = rowElem.querySelector('.item-size-input');
        const qtyInput = rowElem.querySelector('.item-qty-input');
        const unitSelect = rowElem.querySelector('.item-unit-select');
        const actualInput = rowElem.querySelector('.item-actual-input');
        const commInput = rowElem.querySelector('.item-comm-input');
        const btnRemove = rowElem.querySelector('.btn-remove-item');
        const btnDuplicate = rowElem.querySelector('.btn-duplicate-item');

        serviceSelect.addEventListener('change', () => {
            const sId = serviceSelect.value;
            if (sId && sId !== 'custom') {
                const srv = this.servicesCatalog.find(s => s.id == sId);
                if (srv) {
                    item.service_id = srv.id;
                    item.item_name = srv.name;
                    item.description = srv.description || '';
                    item.unit = srv.default_unit || 'Sq Ft';
                    item.actual_price = parseFloat(srv.default_price) || 0;
                    item.commission_rate = parseFloat(srv.default_commission_rate) || this.defaultCommission;

                    nameInput.value = item.item_name;
                    descInput.value = item.description;
                    unitSelect.value = item.unit;
                    actualInput.value = item.actual_price;
                    commInput.value = item.commission_rate;
                }
            } else {
                item.service_id = null;
            }
            this.recalculateAll();
        });

        nameInput.addEventListener('input', () => { item.item_name = nameInput.value; this.syncLivePreview(); });
        descInput.addEventListener('input', () => { item.description = descInput.value; this.syncLivePreview(); });
        sizeInput.addEventListener('input', () => { item.size_dimension = sizeInput.value; this.syncLivePreview(); });
        unitSelect.addEventListener('change', () => { item.unit = unitSelect.value; this.syncLivePreview(); });

        qtyInput.addEventListener('input', () => {
            item.quantity = parseFloat(qtyInput.value) || 0;
            this.recalculateAll();
        });

        actualInput.addEventListener('input', () => {
            item.actual_price = parseFloat(actualInput.value) || 0;
            this.recalculateAll();
        });

        commInput.addEventListener('input', () => {
            item.commission_rate = parseFloat(commInput.value) || 0;
            this.recalculateAll();
        });

        btnRemove.addEventListener('click', () => {
            if (this.items.length <= 1) {
                alert('At least one line item is required.');
                return;
            }
            this.items = this.items.filter(i => i.id !== itemId);
            rowElem.remove();
            this.updateItemIndices();
            this.recalculateAll();
        });

        btnDuplicate.addEventListener('click', () => {
            this.addItem({
                service_id: item.service_id,
                item_name: item.item_name + ' (Copy)',
                description: item.description,
                size_dimension: item.size_dimension,
                quantity: item.quantity,
                unit: item.unit,
                actual_price: item.actual_price,
                commission_rate: item.commission_rate
            });
        });
    }

    updateItemIndices() {
        this.items.forEach((item, idx) => {
            const row = document.getElementById(item.id);
            if (row) {
                const badge = row.querySelector('.item-index-badge');
                if (badge) badge.textContent = `Item #${idx + 1}`;
            }
        });
    }

    recalculateAll() {
        let subtotal = 0;
        let totalCommission = 0;
        let totalActualCost = 0;

        this.items.forEach(item => {
            const actual = parseFloat(item.actual_price) || 0;
            const commRate = parseFloat(item.commission_rate) || 0;
            const qty = parseFloat(item.quantity) || 0;

            let sellingPrice = actual;
            let commAmount = 0;

            if (this.commissionMode === 'margin' && commRate < 100) {
                sellingPrice = actual / (1 - (commRate / 100));
                commAmount = sellingPrice - actual;
            } else {
                commAmount = (actual * commRate) / 100;
                sellingPrice = actual + commAmount;
            }

            const lineTotal = Math.round(sellingPrice * qty * 100) / 100;
            const lineCommTotal = Math.round(commAmount * qty * 100) / 100;

            item.commission_amount = commAmount;
            item.selling_price = sellingPrice;
            item.total_amount = lineTotal;

            subtotal += lineTotal;
            totalCommission += lineCommTotal;
            totalActualCost += (actual * qty);

            // Update row UI
            const rowElem = document.getElementById(item.id);
            if (rowElem) {
                rowElem.querySelector('.item-selling-display').value = '₹' + sellingPrice.toFixed(2);
                rowElem.querySelector('.item-comm-amount-display').textContent = '₹' + commAmount.toFixed(2) + ' (₹' + lineCommTotal.toFixed(2) + ' total)';
                rowElem.querySelector('.item-total-display').textContent = '₹' + lineTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            }
        });

        const discount = Math.max(0, parseFloat(this.discountInput?.value) || 0);
        const transport = Math.max(0, parseFloat(this.transportInput?.value) || 0);
        const installation = Math.max(0, parseFloat(this.installationInput?.value) || 0);
        const gstRate = Math.max(0, parseFloat(this.gstRateInput?.value) || 18.0);

        const taxableAmount = Math.max(0, subtotal - discount + transport + installation);
        const gstAmount = Math.round((taxableAmount * gstRate / 100) * 100) / 100;
        const rawGrandTotal = taxableAmount + gstAmount;
        const roundedGrandTotal = Math.round(rawGrandTotal);
        const roundOff = Math.round((roundedGrandTotal - rawGrandTotal) * 100) / 100;
        const marginPercent = subtotal > 0 ? ((totalCommission / subtotal) * 100).toFixed(1) : 0;

        // Update Summary Cards
        if (this.displaySubtotal) this.displaySubtotal.textContent = '₹' + subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2 });
        if (this.displayCommission) this.displayCommission.textContent = '₹' + totalCommission.toLocaleString('en-IN', { minimumFractionDigits: 2 });
        if (this.displayMargin) this.displayMargin.textContent = `${marginPercent}% margin`;
        if (this.displayTaxable) this.displayTaxable.textContent = '₹' + taxableAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 });
        if (this.displayGstAmount) this.displayGstAmount.textContent = '₹' + gstAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 });
        if (this.displayRoundOff) this.displayRoundOff.textContent = (roundOff >= 0 ? '+₹' : '-₹') + Math.abs(roundOff).toFixed(2);
        if (this.displayGrandTotal) this.displayGrandTotal.textContent = '₹' + roundedGrandTotal.toLocaleString('en-IN');

        this.syncLivePreview({
            subtotal, discount, transport, installation, taxableAmount, gstRate, gstAmount, roundOff, roundedGrandTotal
        });
    }

    syncCustomer() {
        if (!this.customerSelect) return;
        const custId = this.customerSelect.value;
        if (!custId) {
            if (this.prevCustomerName) this.prevCustomerName.textContent = 'Select a client on the left';
            if (this.prevCustomerDetails) this.prevCustomerDetails.textContent = 'Address, phone and tax info will appear here.';
            return;
        }

        const customer = this.customersCatalog.find(c => c.id == custId);
        if (customer && this.prevCustomerName && this.prevCustomerDetails) {
            this.prevCustomerName.textContent = customer.company_name;
            let details = `<strong>Attn:</strong> ${customer.contact_person}<br>`;
            if (customer.address) details += `${customer.address}<br>`;
            details += `${customer.city || 'Hubballi'}, ${customer.state || 'Karnataka'} - ${customer.pincode || '580024'}<br>`;
            details += `Mobile: ${customer.mobile} &bull; Email: ${customer.email || 'N/A'}`;
            if (customer.gstin) details += `<br><strong>GSTIN:</strong> ${customer.gstin}`;
            this.prevCustomerDetails.innerHTML = details;
        }
    }

    syncLivePreview(totals = null) {
        if (!this.prevItemsTableBody) return;

        let rowsHtml = '';
        this.items.forEach((item, idx) => {
            rowsHtml += `
                <tr>
                    <td class="text-center fw-bold">${idx + 1}</td>
                    <td>
                        <div class="fw-bold">${this.escape(item.item_name || 'Item Name')}</div>
                        ${item.description ? `<div class="small text-muted">${this.escape(item.description)}</div>` : ''}
                    </td>
                    <td class="text-center">${this.escape(item.size_dimension || '-')}</td>
                    <td class="text-center fw-bold">${item.quantity}</td>
                    <td class="text-center">${item.unit}</td>
                    <td class="text-end">₹${parseFloat(item.selling_price || 0).toFixed(2)}</td>
                    <td class="text-end fw-bold">₹${parseFloat(item.total_amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
                </tr>
            `;
        });
        this.prevItemsTableBody.innerHTML = rowsHtml;

        if (totals) {
            if (this.prevSubtotal) this.prevSubtotal.textContent = '₹' + totals.subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            if (this.prevDiscountRow) {
                this.prevDiscountRow.style.display = totals.discount > 0 ? 'table-row' : 'none';
                if (this.prevDiscount) this.prevDiscount.textContent = '- ₹' + totals.discount.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            }
            if (this.prevTransportRow) {
                this.prevTransportRow.style.display = totals.transport > 0 ? 'table-row' : 'none';
                if (this.prevTransport) this.prevTransport.textContent = '₹' + totals.transport.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            }
            if (this.prevInstallRow) {
                this.prevInstallRow.style.display = totals.installation > 0 ? 'table-row' : 'none';
                if (this.prevInstall) this.prevInstall.textContent = '₹' + totals.installation.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            }
            if (this.prevTaxable) this.prevTaxable.textContent = '₹' + totals.taxableAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            if (this.prevGstRate) this.prevGstRate.textContent = totals.gstRate;
            if (this.prevGstAmount) this.prevGstAmount.textContent = '₹' + totals.gstAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            if (this.prevRoundOffRow) {
                this.prevRoundOffRow.style.display = totals.roundOff !== 0 ? 'table-row' : 'none';
                if (this.prevRoundOff) this.prevRoundOff.textContent = (totals.roundOff >= 0 ? '+₹' : '-₹') + Math.abs(totals.roundOff).toFixed(2);
            }
            if (this.prevGrandTotal) this.prevGrandTotal.textContent = '₹' + totals.roundedGrandTotal.toLocaleString('en-IN');
        }
    }

    formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    escape(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    getFormData() {
        return {
            customer_id: this.customerSelect ? this.customerSelect.value : '',
            project_name: this.projectNameInput ? this.projectNameInput.value : '',
            quotation_date: this.quotationDateInput ? this.quotationDateInput.value : '',
            valid_until: this.validUntilInput ? this.validUntilInput.value : '',
            reference: this.referenceInput ? this.referenceInput.value : '',
            commission_mode: this.commissionMode,
            discount_amount: parseFloat(this.discountInput?.value) || 0,
            transportation_charges: parseFloat(this.transportInput?.value) || 0,
            installation_charges: parseFloat(this.installationInput?.value) || 0,
            gst_rate: parseFloat(this.gstRateInput?.value) || 18,
            notes: this.notesInput ? this.notesInput.value : '',
            terms_and_conditions: this.termsInput ? this.termsInput.value : '',
            payment_terms: this.paymentTermsInput ? this.paymentTermsInput.value : '',
            delivery_time: this.deliveryTimeInput ? this.deliveryTimeInput.value : '',
            items: this.items
        };
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('quotationBuilderForm')) {
        window.builder = new QuotationBuilder();

        // Mobile / Tablet View Switcher
        const btnSwitchEditor = document.getElementById('btnSwitchEditor');
        const btnSwitchPreview = document.getElementById('btnSwitchPreview');
        const editorPane = document.querySelector('.builder-editor-pane');
        const previewPane = document.querySelector('.builder-preview-pane');

        if (btnSwitchEditor && btnSwitchPreview && editorPane && previewPane) {
            btnSwitchEditor.addEventListener('click', () => {
                btnSwitchEditor.classList.add('active');
                btnSwitchPreview.classList.remove('active');
                editorPane.classList.remove('mobile-hidden');
                previewPane.classList.remove('mobile-active');
            });

            btnSwitchPreview.addEventListener('click', () => {
                btnSwitchPreview.classList.add('active');
                btnSwitchEditor.classList.remove('active');
                editorPane.classList.add('mobile-hidden');
                previewPane.classList.add('mobile-active');
                if (window.builder) {
                    window.builder.syncAllMetadata();
                    window.builder.renderPreviewItems();
                }
            });
        }

        const form = document.getElementById('quotationBuilderForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving Quotation...';

            const payload = window.builder.getFormData();
            const url = form.getAttribute('action');

            const res = await apiRequest(url, 'POST', payload);
            if (res.ok && res.data && res.data.success) {
                showToast(res.data.message || 'Quotation saved successfully!', 'success');
                setTimeout(() => {
                    window.location.href = res.data.redirect_url || (window.APP_URL + '/quotations');
                }, 800);
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save & Generate Quotation';
                showToast(res.data?.error || 'Failed to save quotation. Please check required fields.', 'error');
            }
        });
    }
});
