<?php
/**
 * Sagar Advertising CRM - Customer Create / Edit Form
 */
$isEdit = !empty($customer);
$title = $isEdit ? "Edit Customer: " . e($customer['company_name']) : "Register New Customer";
$actionUrl = $isEdit ? url('customers/update') : url('customers/store');
?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;"><?= $title ?></h2>
                <p class="text-muted mb-0">Fill in client details, address specifications, GST number, and contact info.</p>
            </div>
            <a href="<?= url('customers') ?>" class="btn-brand-outline text-nowrap">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Customers</span>
            </a>
        </div>

        <div class="card-brand">
            <div class="card-body p-3 p-sm-4">
                <form method="POST" action="<?= $actionUrl ?>">
                    <?= csrf_field() ?>
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= $customer['id'] ?>">
                    <?php endif; ?>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">1. Business & Primary Contact</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Company / Business Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="company_name" required value="<?= e($customer['company_name'] ?? '') ?>" placeholder="e.g. KLE Technological University, Bapat Jewellers">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Contact Person / Owner <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="contact_person" required value="<?= e($customer['contact_person'] ?? '') ?>" placeholder="e.g. Dr. Ashok Shettar, Vinayak Bapat">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Primary Mobile <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="mobile" required value="<?= e($customer['mobile'] ?? '') ?>" placeholder="e.g. 9845012341">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Alternate Mobile / Landline</label>
                            <input type="text" class="form-control" name="alternate_mobile" value="<?= e($customer['alternate_mobile'] ?? '') ?>" placeholder="e.g. 08362378100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">WhatsApp Number</label>
                            <input type="text" class="form-control" name="whatsapp" value="<?= e($customer['whatsapp'] ?? '') ?>" placeholder="Leave blank to match mobile">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Email Address</label>
                            <input type="email" class="form-control" name="email" value="<?= e($customer['email'] ?? '') ?>" placeholder="e.g. procurement@company.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">GSTIN (GST Number)</label>
                            <input type="text" class="form-control text-uppercase font-monospace" name="gstin" value="<?= e($customer['gstin'] ?? '') ?>" placeholder="e.g. 29AVPH4223R1ZV">
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">2. Location & Address</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label fw-bold small">Street Address / Landmark</label>
                            <textarea class="form-control" name="address" rows="2" placeholder="Building name, shop number, road/street name"><?= e($customer['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">City</label>
                            <input type="text" class="form-control" name="city" value="<?= e($customer['city'] ?? 'Hubballi') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">State</label>
                            <input type="text" class="form-control" name="state" value="<?= e($customer['state'] ?? 'Karnataka') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Pincode</label>
                            <input type="text" class="form-control" name="pincode" value="<?= e($customer['pincode'] ?? '580024') ?>">
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">3. CRM Classification & Assignment</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Customer Type</label>
                            <select class="form-select" name="customer_type">
                                <?php foreach (['Business', 'Corporate', 'Dealer', 'Individual', 'Existing Client', 'New Client'] as $t): ?>
                                    <option value="<?= $t ?>" <?= ($customer['customer_type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach (['Active', 'Lead', 'Inactive', 'Lost'] as $st): ?>
                                    <option value="<?= $st ?>" <?= ($customer['status'] ?? 'Active') === $st ? 'selected' : '' ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Lead Source</label>
                            <input type="text" class="form-control" name="source" value="<?= e($customer['source'] ?? 'Direct Visit') ?>" placeholder="e.g. Direct Visit, Referral, Instagram, Tender">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Assigned Sales Executive</label>
                            <select class="form-select" name="assigned_employee_id">
                                <option value="">-- Unassigned --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp['id'] ?>" <?= ($customer['assigned_employee_id'] ?? '') == $emp['id'] ? 'selected' : '' ?>>
                                        <?= e($emp['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Internal Customer Notes</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Special pricing, architectural preferences, delivery notes"><?= e($customer['notes'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="marketingOptIn" name="marketing_opt_in" value="1" <?= (!isset($customer['marketing_opt_in']) || $customer['marketing_opt_in']) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="marketingOptIn">
                                    Customer has consented to receive festival greetings and promotional emails (Marketing Opt-In)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= url('customers') ?>" class="btn btn-outline-secondary text-center">Cancel</a>
                        <button type="submit" class="btn btn-brand-primary">
                            <i class="bi bi-check2-circle me-1"></i>
                            <span><?= $isEdit ? 'Update Customer Record' : 'Save & Register Customer' ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
