<?php
/**
 * Sagar Advertising CRM - Services Master Catalog View
 */
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;">Advertising Services Master Catalog</h2>
        <p class="text-muted mb-0">Pre-configured service rates, standard units, tax classifications, and default commission percentages.</p>
    </div>
    <?php if (AuthCheck::can('services.manage')): ?>
    <button type="button" class="btn-brand-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
        <i class="bi bi-plus-circle-fill"></i>
        <span>+ Add New Service</span>
    </button>
    <?php endif; ?>
</div>

<div class="table-brand-wrap">
    <div class="table-responsive">
        <table class="table-brand table">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Service / Product Name</th>
                    <th>Category</th>
                    <th>Technical Description</th>
                    <th>Default Unit</th>
                    <th>Base Price (₹)</th>
                    <th>Commission Rate</th>
                    <th>GST %</th>
                    <th>Status</th>
                    <?php if (AuthCheck::can('services.manage')): ?>
                    <th class="text-end">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $idx => $s): ?>
                <tr>
                    <td class="text-muted fw-bold"><?= $idx + 1 ?></td>
                    <td>
                        <div class="fw-bold text-dark fs-6"><?= e($s['name']) ?></div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border"><?= e($s['category_name'] ?? 'General') ?></span>
                    </td>
                    <td style="max-width: 280px;" class="small text-muted text-truncate" title="<?= e($s['description']) ?>">
                        <?= e($s['description']) ?>
                    </td>
                    <td>
                        <span class="badge bg-dark-subtle text-dark fw-bold"><?= e($s['default_unit']) ?></span>
                    </td>
                    <td class="fw-bold text-dark">
                        ₹<?= number_format((float)$s['default_price'], 2) ?>
                    </td>
                    <td>
                        <span class="text-success fw-bold"><?= (float)$s['default_commission_rate'] ?>%</span>
                    </td>
                    <td>
                        <?= (float)$s['default_gst_rate'] ?>%
                    </td>
                    <td>
                        <span class="status-badge <?= $s['status'] === 'active' ? 'badge-active' : 'badge-inactive' ?>">
                            <?= ucfirst($s['status']) ?>
                        </span>
                    </td>
                    <?php if (AuthCheck::can('services.manage')): ?>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openEditServiceModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="<?= url('services/delete') ?>" class="d-inline" onsubmit="return confirm('Delete service <?= e($s['name']) ?>?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= url('services/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="addServiceModalLabel"><i class="bi bi-palette2 text-warning me-2"></i> Add New Advertising Service</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold">Service / Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g. 3D Acrylic Glow Board">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Service Category</label>
                            <select class="form-select" name="category_id">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Technical Specifications / Description</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Materials, LED module brands, frame thickness, warranties"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Measurement Unit</label>
                            <select class="form-select" name="default_unit">
                                <?php foreach (['Sq Ft', 'Sq Inch', 'Piece', 'Running Ft', 'Day', 'Hour', 'Unit', 'Custom'] as $u): ?>
                                    <option value="<?= $u ?>"><?= $u ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Base Actual Price (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="default_price" value="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Default Commission %</label>
                            <input type="number" step="0.1" min="0" max="100" class="form-control" name="default_commission_rate" value="15.0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">GST Rate %</label>
                            <input type="number" step="0.1" min="0" class="form-control" name="default_gst_rate" value="18.0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand-primary">Save to Catalog</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= url('services/update') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="editServiceId">

                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="editServiceModalLabel"><i class="bi bi-pencil text-warning me-2"></i> Edit Advertising Service</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold">Service / Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="editServiceName" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Category</label>
                            <select class="form-select" name="category_id" id="editServiceCat">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Description</label>
                            <textarea class="form-control" name="description" id="editServiceDesc" rows="2"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Default Unit</label>
                            <select class="form-select" name="default_unit" id="editServiceUnit">
                                <?php foreach (['Sq Ft', 'Sq Inch', 'Piece', 'Running Ft', 'Day', 'Hour', 'Unit', 'Custom'] as $u): ?>
                                    <option value="<?= $u ?>"><?= $u ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Base Price (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="default_price" id="editServicePrice">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Default Commission %</label>
                            <input type="number" step="0.1" min="0" max="100" class="form-control" name="default_commission_rate" id="editServiceComm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">GST Rate %</label>
                            <input type="number" step="0.1" min="0" class="form-control" name="default_gst_rate" id="editServiceGst">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Status</label>
                            <select class="form-select" name="status" id="editServiceStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand-primary">Update Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditServiceModal(s) {
    document.getElementById('editServiceId').value = s.id;
    document.getElementById('editServiceName').value = s.name;
    document.getElementById('editServiceCat').value = s.category_id || '';
    document.getElementById('editServiceDesc').value = s.description || '';
    document.getElementById('editServiceUnit').value = s.default_unit || 'Sq Ft';
    document.getElementById('editServicePrice').value = s.default_price || '0';
    document.getElementById('editServiceComm').value = s.default_commission_rate || '15';
    document.getElementById('editServiceGst').value = s.default_gst_rate || '18';
    document.getElementById('editServiceStatus').value = s.status || 'active';

    const modal = new bootstrap.Modal(document.getElementById('editServiceModal'));
    modal.show();
}
</script>
