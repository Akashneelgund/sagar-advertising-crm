<?php
/**
 * Sagar Advertising CRM - Employee & Access Management View
 */
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;">Employee & Access Control</h2>
        <p class="text-muted mb-0">Manage internal team members, access privileges, sales assignments, and user roles.</p>
    </div>
    <button type="button" class="btn-brand-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
        <i class="bi bi-person-plus-fill"></i>
        <span>+ Add Team Member</span>
    </button>
</div>

<!-- Employees Table -->
<div class="table-brand-wrap mb-4">
    <div class="card-header bg-white py-3 px-4 border-bottom">
        <h5 class="fw-bold mb-0">Active Team Accounts</h5>
    </div>
    <div class="table-responsive">
        <table class="table-brand table">
            <thead>
                <tr>
                    <th>Staff Name</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>System Role</th>
                    <th>Quotes Handled</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar" style="width: 32px; height: 32px; font-size: 12px;">
                                <?= strtoupper(substr($u['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-bold text-dark"><?= e($u['name']) ?></div>
                                <div class="small text-muted font-monospace">@<?= e($u['username']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['phone'] ?: '-') ?></td>
                    <td><?= e($u['department']) ?></td>
                    <td>
                        <?php if ($u['role'] === 'admin'): ?>
                            <span class="badge bg-danger-subtle text-danger fw-bold"><i class="bi bi-shield-fill-check me-1"></i>ADMIN</span>
                        <?php elseif ($u['role'] === 'manager'): ?>
                            <span class="badge bg-primary-subtle text-primary fw-bold"><i class="bi bi-briefcase-fill me-1"></i>MANAGER</span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border fw-bold">EMPLOYEE</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="fw-bold"><?= $u['quote_count'] ?></span> quotes &bull; <span class="text-muted small"><?= $u['customer_count'] ?> clients</span>
                    </td>
                    <td>
                        <span class="status-badge <?= $u['status'] === 'active' ? 'badge-active' : 'badge-inactive' ?>">
                            <?= ucfirst($u['status']) ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openEditEmployeeModal(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Role-Based Permissions Matrix -->
<div class="card-brand mb-4">
    <div class="card-header">
        <div>
            <h5 class="fw-bold mb-0">Role-Based Access Control (RBAC) Matrix</h5>
            <span class="text-muted small">Standardized permissions granted per role</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0 align-middle small">
                <thead class="table-dark">
                    <tr>
                        <th>Module / Feature</th>
                        <th>Permission Scope</th>
                        <th class="text-center" style="width: 120px;">Admin</th>
                        <th class="text-center" style="width: 120px;">Manager</th>
                        <th class="text-center" style="width: 120px;">Employee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permissions as $p): ?>
                    <tr>
                        <td class="fw-bold"><?= e($p['module']) ?></td>
                        <td>
                            <strong><?= e($p['name']) ?></strong>
                            <div class="text-muted small"><?= e($p['description']) ?></div>
                        </td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill fs-6"></i> Full</td>
                        <td class="text-center">
                            <?php if (in_array($p['slug'], ['employees.manage', 'settings.manage', 'quotations.delete', 'customers.delete'])): ?>
                                <span class="text-muted"><i class="bi bi-dash"></i></span>
                            <?php else: ?>
                                <span class="text-success"><i class="bi bi-check-circle-fill"></i> Allowed</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if (in_array($p['slug'], ['customers.view', 'customers.create', 'quotations.view', 'quotations.create', 'quotations.send'])): ?>
                                <span class="text-success"><i class="bi bi-check-circle-fill"></i> Allowed</span>
                            <?php else: ?>
                                <span class="text-muted"><i class="bi bi-dash"></i></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= url('employees/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="addEmployeeModalLabel"><i class="bi bi-person-plus-fill text-warning me-2"></i> Register New Employee</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g. Ramesh Patil">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required placeholder="e.g. ramesh@sagaradvertising.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <input type="text" class="form-control" name="phone" placeholder="e.g. 9845123456">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Department</label>
                            <input type="text" class="form-control" name="department" value="Sales & Marketing">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">System Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" required>
                                <option value="employee">Employee / Sales Rep</option>
                                <option value="manager">Manager / Supervisor</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Login Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" required placeholder="e.g. ramesh">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Account Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required placeholder="Min 6 characters">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand-primary">Create Team Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= url('employees/update') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="editEmpId">

                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="editEmployeeModalLabel"><i class="bi bi-pencil text-warning me-2"></i> Edit Team Member</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="editEmpName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="editEmpEmail" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <input type="text" class="form-control" name="phone" id="editEmpPhone">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Department</label>
                            <input type="text" class="form-control" name="department" id="editEmpDept">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">System Role</label>
                            <select class="form-select" name="role" id="editEmpRole">
                                <option value="employee">Employee / Sales Rep</option>
                                <option value="manager">Manager / Supervisor</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Account Status</label>
                            <select class="form-select" name="status" id="editEmpStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive / Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Reset Password (Leave blank to keep)</label>
                            <input type="password" class="form-control" name="password" placeholder="New password if resetting">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand-primary">Update Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditEmployeeModal(u) {
    document.getElementById('editEmpId').value = u.id;
    document.getElementById('editEmpName').value = u.name;
    document.getElementById('editEmpEmail').value = u.email;
    document.getElementById('editEmpPhone').value = u.phone || '';
    document.getElementById('editEmpDept').value = u.department || '';
    document.getElementById('editEmpRole').value = u.role || 'employee';
    document.getElementById('editEmpStatus').value = u.status || 'active';

    const modal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
    modal.show();
}
</script>
