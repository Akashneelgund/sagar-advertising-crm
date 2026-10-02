<?php
/**
 * Sagar Advertising CRM - Global Layout Header
 */
$currentUser = Session::user();
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$activeRoute = function(string $route) use ($currentUri): string {
    return str_contains($currentUri, $route) ? 'active' : '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e(APP_NAME) ?> - CRM & Quotation System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Custom Brand Styles -->
    <link rel="stylesheet" href="<?= asset('css/brand.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">

    <script>
        window.APP_URL = "<?= rtrim(BASE_URL, '/') ?>";
    </script>
</head>
<body>

<div class="app-wrapper">
    <!-- Sidebar -->
    <aside class="app-sidebar">
        <div class="sidebar-brand d-flex justify-content-between align-items-center">
            <a href="<?= url('dashboard') ?>" class="d-flex align-items-center gap-2 text-decoration-none">
                <img src="<?= asset('images/logo.svg') ?>" alt="Sagar Advertising" class="logo-img">
            </a>
            <button type="button" class="btn btn-link text-white-50 d-lg-none p-0 fs-4" id="mobileSidebarClose" aria-label="Close menu">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="sidebar-menu">
            <div class="menu-header">Main Menu</div>
            <div class="nav-item">
                <a href="<?= url('dashboard') ?>" class="nav-link <?= $activeRoute('dashboard') ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= url('customers') ?>" class="nav-link <?= $activeRoute('customers') ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Customers</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= url('quotations') ?>" class="nav-link <?= $activeRoute('quotations') ?>">
                    <i class="bi bi-file-earmark-ruled-fill"></i>
                    <span>Quotations</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= url('services') ?>" class="nav-link <?= $activeRoute('services') ?>">
                    <i class="bi bi-palette2"></i>
                    <span>Services Master</span>
                </a>
            </div>

            <div class="menu-header">Marketing & Outreach</div>
            <div class="nav-item">
                <a href="<?= url('campaigns') ?>" class="nav-link <?= $activeRoute('campaigns') ?>">
                    <i class="bi bi-envelope-paper-heart-fill"></i>
                    <span>Email Campaigns</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= url('campaigns/templates') ?>" class="nav-link <?= $activeRoute('campaigns/templates') ?>">
                    <i class="bi bi-layout-text-window-reverse"></i>
                    <span>Email Templates</span>
                </a>
            </div>

            <div class="menu-header">Data & Analytics</div>
            <div class="nav-item">
                <a href="<?= url('excel/import') ?>" class="nav-link <?= $activeRoute('excel/import') ?>">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    <span>Excel Import</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= url('excel/export') ?>" class="nav-link <?= $activeRoute('excel/export') ?>">
                    <i class="bi bi-file-earmark-arrow-down-fill"></i>
                    <span>Excel Export</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= url('reports') ?>" class="nav-link <?= $activeRoute('reports') && !$activeRoute('commission') ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>Sales Reports</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= url('reports/commission') ?>" class="nav-link <?= $activeRoute('commission') ?>">
                    <i class="bi bi-pie-chart-fill"></i>
                    <span>Profit & Margin</span>
                </a>
            </div>

            <div class="menu-header">System Administration</div>
            <?php if (AuthCheck::can('employees.manage')): ?>
            <div class="nav-item">
                <a href="<?= url('employees') ?>" class="nav-link <?= $activeRoute('employees') ?>">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Employees & Access</span>
                </a>
            </div>
            <?php endif; ?>
            <?php if (AuthCheck::can('settings.manage')): ?>
            <div class="nav-item">
                <a href="<?= url('settings') ?>" class="nav-link <?= $activeRoute('settings') ?>">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings & Backup</span>
                </a>
            </div>
            <?php endif; ?>
            <div class="nav-item">
                <a href="<?= url('logs') ?>" class="nav-link <?= $activeRoute('logs') ?>">
                    <i class="bi bi-clock-history"></i>
                    <span>Activity Logs</span>
                </a>
            </div>
        </div>

        <!-- Sidebar User Footer -->
        <div class="sidebar-footer">
            <div class="user-badge">
                <div class="user-avatar">
                    <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?= e($currentUser['name'] ?? 'User') ?></div>
                    <div class="user-role"><?= e($currentUser['role'] ?? 'Employee') ?></div>
                </div>
            </div>
            <a href="<?= url('logout') ?>" class="btn btn-sm btn-link text-white-50 p-0 fs-5" title="Sign Out">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </aside>
    <!-- Mobile Sidebar Backdrop Overlay -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Main Content Area -->
    <main class="app-main">
        <!-- Topbar -->
        <header class="app-topbar">
            <div class="d-flex align-items-center gap-2 gap-sm-3 flex-grow-1 me-2" style="min-width: 0;">
                <button type="button" class="btn btn-link d-lg-none p-0 text-dark fs-3 flex-shrink-0" id="mobileSidebarToggle" aria-label="Toggle menu">
                    <i class="bi bi-list"></i>
                </button>
                <div class="search-bar-wrap">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" id="globalSearchInput" placeholder="Search..." autocomplete="off">
                    <div class="search-results-dropdown" id="globalSearchResults"></div>
                </div>
            </div>

            <div class="topbar-actions flex-shrink-0">
                <a href="<?= url('customers/create') ?>" class="btn-brand-outline d-none d-md-inline-flex">
                    <i class="bi bi-person-plus-fill text-muted"></i>
                    <span>+ Customer</span>
                </a>
                <a href="<?= url('quotations/builder') ?>" class="btn-brand-primary text-nowrap">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span class="d-none d-sm-inline">+ New Quotation</span>
                    <span class="d-sm-none">+ Quote</span>
                </a>
            </div>
        </header>

        <!-- Content Area -->
        <div class="app-content">
            <!-- Flash Message Alerts -->
            <div class="flash-container">
                <?php foreach ($flashes as $flash): ?>
                <div class="alert-flash alert-flash-<?= e($flash['type']) ?>">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle-fill' : ($flash['type'] === 'error' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill') ?>"></i>
                        <span><?= e($flash['message']) ?></span>
                    </div>
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
                </div>
                <?php endforeach; ?>
            </div>
