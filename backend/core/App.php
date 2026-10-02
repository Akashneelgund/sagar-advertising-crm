<?php
/**
 * Sagar Advertising CRM - Request Router & Dispatcher
 */

declare(strict_types=1);

require_once ROOT_PATH . '/backend/controllers/AuthController.php';
require_once ROOT_PATH . '/backend/controllers/DashboardController.php';
require_once ROOT_PATH . '/backend/controllers/CustomerController.php';
require_once ROOT_PATH . '/backend/controllers/QuotationController.php';
require_once ROOT_PATH . '/backend/controllers/ServiceController.php';
require_once ROOT_PATH . '/backend/controllers/CampaignController.php';
require_once ROOT_PATH . '/backend/controllers/ExcelController.php';
require_once ROOT_PATH . '/backend/controllers/ReportController.php';
require_once ROOT_PATH . '/backend/controllers/EmployeeController.php';
require_once ROOT_PATH . '/backend/controllers/SettingsController.php';
require_once ROOT_PATH . '/backend/controllers/PublicController.php';
require_once ROOT_PATH . '/backend/controllers/ApiController.php';

class App {
    public static function run(): void {
        // Parse requested path relative to base directory
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptDir !== '/' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }
        $path = trim($uri, '/');

        // Route dispatcher
        switch (true) {
            // Root & Auth
            case $path === '' || $path === 'index.php':
                header('Location: ' . url(Session::isLoggedIn() ? 'dashboard' : 'login'));
                exit;

            case $path === 'login':
                (new AuthController())->login();
                break;

            case $path === 'logout':
                (new AuthController())->logout();
                break;

            case $path === 'dashboard':
                (new DashboardController())->index();
                break;

            // Customers
            case $path === 'customers':
                (new CustomerController())->index();
                break;

            case $path === 'customers/detail':
                (new CustomerController())->detail();
                break;

            case $path === 'customers/create':
                (new CustomerController())->create();
                break;

            case $path === 'customers/store':
                (new CustomerController())->store();
                break;

            case $path === 'customers/edit':
                (new CustomerController())->edit();
                break;

            case $path === 'customers/update':
                (new CustomerController())->update();
                break;

            case $path === 'customers/delete':
                (new CustomerController())->delete();
                break;

            case $path === 'customers/add-note':
                (new CustomerController())->addNote();
                break;

            case $path === 'customers/add-followup':
                (new CustomerController())->addFollowup();
                break;

            // Quotations
            case $path === 'quotations':
                (new QuotationController())->index();
                break;

            case $path === 'quotations/builder':
                (new QuotationController())->builder();
                break;

            case $path === 'quotations/store':
                (new QuotationController())->store();
                break;

            case $path === 'quotations/view':
                (new QuotationController())->viewDocument();
                break;

            case $path === 'quotations/send-email':
                (new QuotationController())->sendEmail();
                break;

            case $path === 'quotations/duplicate':
                (new QuotationController())->duplicate();
                break;

            case $path === 'quotations/status':
                (new QuotationController())->changeStatus();
                break;

            case $path === 'quotations/delete':
                (new QuotationController())->delete();
                break;

            // Public Quotation View
            case str_starts_with($path, 'quote/view/'):
                $token = substr($path, strlen('quote/view/'));
                $_GET['token'] = $token;
                (new PublicController())->viewQuotation();
                break;

            // Services
            case $path === 'services':
                (new ServiceController())->index();
                break;

            case $path === 'services/store':
                (new ServiceController())->store();
                break;

            case $path === 'services/update':
                (new ServiceController())->update();
                break;

            case $path === 'services/delete':
                (new ServiceController())->delete();
                break;

            // Campaigns & Marketing
            case $path === 'campaigns':
                (new CampaignController())->index();
                break;

            case $path === 'campaigns/builder':
                (new CampaignController())->builder();
                break;

            case $path === 'campaigns/store':
                (new CampaignController())->store();
                break;

            case $path === 'campaigns/queue':
                (new CampaignController())->queue();
                break;

            case $path === 'campaigns/templates':
                (new CampaignController())->templates();
                break;

            case $path === 'campaigns/store-template':
                (new CampaignController())->storeTemplate();
                break;

            case $path === 'unsubscribe':
                (new CampaignController())->unsubscribe();
                break;

            // Follow-ups listing
            case $path === 'followups':
                (new CustomerController())->index();
                break;

            // Excel Tools
            case $path === 'excel/import':
                (new ExcelController())->importView();
                break;

            case $path === 'excel/export':
                (new ExcelController())->exportView();
                break;

            case $path === 'excel/export-customers':
                (new ExcelController())->exportCustomers();
                break;

            case $path === 'excel/export-quotations':
                (new ExcelController())->exportQuotations();
                break;

            // Reports
            case $path === 'reports':
                (new ReportController())->index();
                break;

            case $path === 'reports/commission':
                (new ReportController())->commission();
                break;

            // Employees & Roles
            case $path === 'employees':
                (new EmployeeController())->index();
                break;

            case $path === 'employees/store':
                (new EmployeeController())->store();
                break;

            case $path === 'employees/update':
                (new EmployeeController())->update();
                break;

            // System Settings & Logs
            case $path === 'settings':
                (new SettingsController())->index();
                break;

            case $path === 'settings/update':
                (new SettingsController())->update();
                break;

            case $path === 'settings/backup':
                (new SettingsController())->createBackup();
                break;

            case $path === 'settings/download-backup':
                (new SettingsController())->downloadBackup();
                break;

            case $path === 'logs':
                (new SettingsController())->activityLogs();
                break;

            // REST API Endpoints
            case $path === 'api/search':
                (new ApiController())->search();
                break;

            case $path === 'api/followups/toggle':
                (new ApiController())->toggleFollowup();
                break;

            case $path === 'api/excel/parse':
                (new ExcelController())->parseUpload();
                break;

            case $path === 'api/excel/execute-import':
                (new ExcelController())->executeImport();
                break;

            case $path === 'api/campaigns/process-batch':
                (new CampaignController())->processBatch();
                break;

            case $path === 'api/campaigns/cancel':
                (new CampaignController())->cancel();
                break;

            case $path === 'api/campaigns/restart':
                (new CampaignController())->restartQueue();
                break;

            case $path === 'api/campaigns/send-test':
                (new CampaignController())->sendTest();
                break;

            case $path === 'api/settings/test-smtp':
                (new SettingsController())->testSmtp();
                break;

            default:
                http_response_code(404);
                echo "<div style='font-family: sans-serif; text-align: center; padding: 60px;'>";
                echo "<h1 style='color: #FF5500;'>404 - Page Not Found</h1>";
                echo "<p>The requested route <code>/" . htmlspecialchars($path) . "</code> does not exist.</p>";
                echo "<a href='" . url('dashboard') . "' style='color: #121417;'>Return to Dashboard</a>";
                echo "</div>";
                break;
        }
    }
}
