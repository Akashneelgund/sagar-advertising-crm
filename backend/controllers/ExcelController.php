<?php
/**
 * Sagar Advertising CRM - Excel Import & Export Controller
 */

declare(strict_types=1);

require_once ROOT_PATH . '/backend/core/Controller.php';
require_once ROOT_PATH . '/backend/services/ExcelHandler.php';

class ExcelController extends Controller {
    /**
     * Show Excel Import Wizard page
     */
    public function importView(): void {
        $this->requirePermission('excel.import');
        $this->view('excel/import');
    }

    /**
     * API Endpoint: Step 1 - Upload and parse file headers + preview
     */
    public function parseUpload(): void {
        $this->requirePermission('excel.import');

        if (empty($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'error' => 'File upload error.'], 400);
        }

        $file = $_FILES['excel_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['csv', 'txt', 'xls', 'xlsx', 'xml'])) {
            $this->json(['success' => false, 'error' => 'Unsupported format. Please upload .csv, .xls, or .xlsx file.'], 400);
        }

        $tmpDest = UPLOAD_PATH . '/import_' . time() . '_' . substr(md5(uniqid()), 0, 8) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $tmpDest)) {
            $this->json(['success' => false, 'error' => 'Failed to save uploaded file.'], 500);
        }

        try {
            $parsed = ExcelHandler::parseUploadPreview($tmpDest);
            $this->json([
                'success'    => true,
                'headers'    => $parsed['headers'],
                'preview'    => $parsed['preview'],
                'total_rows' => $parsed['total_rows'],
                'temp_file'  => basename($tmpDest)
            ]);
        } catch (Exception $e) {
            if (file_exists($tmpDest)) unlink($tmpDest);
            $this->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * API Endpoint: Step 2/3 - Execute mapped import
     */
    public function executeImport(): void {
        $this->requirePermission('excel.import');

        $tempFileName = trim((string)$this->input('temp_file'));
        $mapping = $this->input('mapping', []);

        $filePath = UPLOAD_PATH . '/' . basename($tempFileName);
        if (!file_exists($filePath)) {
            $this->json(['success' => false, 'error' => 'Uploaded file session has expired. Please re-upload.'], 400);
        }

        try {
            $parsed = ExcelHandler::parseUploadPreview($filePath);
            $result = ExcelHandler::importCustomers($parsed['raw_rows'], $mapping);

            // Clean up temporary upload file
            @unlink($filePath);

            $this->json(array_merge(['success' => true], $result));
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show Export Page with Filters
     */
    public function exportView(): void {
        $this->requirePermission('excel.export');
        $cities = DB::fetchAll("SELECT DISTINCT city FROM `customers` WHERE deleted_at IS NULL AND city != '' ORDER BY city ASC");
        $this->view('excel/export', ['cities' => array_column($cities, 'city')]);
    }

    /**
     * Export Customers Data
     */
    public function exportCustomers(): void {
        $this->requirePermission('excel.export');

        $format = $this->input('format', 'csv') === 'excel' ? 'excel' : 'csv';
        $status = trim((string)$this->input('status', ''));
        $type = trim((string)$this->input('type', ''));
        $city = trim((string)$this->input('city', ''));

        $conditions = ["c.deleted_at IS NULL"];
        $params = [];
        if ($status) { $conditions[] = "c.status = ?"; $params[] = $status; }
        if ($type) { $conditions[] = "c.customer_type = ?"; $params[] = $type; }
        if ($city) { $conditions[] = "c.city = ?"; $params[] = $city; }

        $where = implode(' AND ', $conditions);
        $rows = DB::fetchAll(
            "SELECT c.customer_code, c.company_name, c.contact_person, c.mobile, c.alternate_mobile,
                    c.email, c.city, c.state, c.gstin, c.customer_type, c.status, c.created_at
             FROM `customers` c WHERE {$where} ORDER BY c.created_at DESC",
            $params
        );

        $headers = ['Customer ID', 'Company Name', 'Contact Person', 'Mobile', 'Alt Mobile', 'Email', 'City', 'State', 'GSTIN', 'Customer Type', 'Status', 'Registered Date'];
        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                $r['customer_code'],
                $r['company_name'],
                $r['contact_person'],
                $r['mobile'],
                $r['alternate_mobile'] ?? '',
                $r['email'] ?? '',
                $r['city'] ?? '',
                $r['state'] ?? '',
                $r['gstin'] ?? '',
                $r['customer_type'],
                $r['status'],
                $r['created_at']
            ];
        }

        AuditLogger::log('EXPORT', 'Customers', null, "Exported " . count($data) . " customer records to {$format}");
        ExcelHandler::export($format, 'Sagar_Advertising_Customers_' . date('Ymd'), $headers, $data);
    }

    /**
     * Export Quotations Data
     */
    public function exportQuotations(): void {
        $this->requirePermission('excel.export');

        $format = $this->input('format', 'csv') === 'excel' ? 'excel' : 'csv';
        $status = trim((string)$this->input('status', ''));

        $conditions = ["1=1"];
        $params = [];
        if ($status) { $conditions[] = "q.status = ?"; $params[] = $status; }

        $where = implode(' AND ', $conditions);
        $rows = DB::fetchAll(
            "SELECT q.quotation_number, q.quotation_date, q.valid_until, c.company_name, c.contact_person, c.mobile,
                    q.project_name, q.subtotal, q.total_commission, q.discount_amount, q.taxable_amount,
                    q.gst_amount, q.grand_total, q.status, u.name as sales_person
             FROM `quotations` q
             JOIN `customers` c ON q.customer_id = c.id
             LEFT JOIN `users` u ON q.sales_person_id = u.id
             WHERE {$where} ORDER BY q.created_at DESC",
            $params
        );

        $headers = ['Quotation #', 'Date', 'Valid Until', 'Company Name', 'Contact Person', 'Mobile', 'Project', 'Subtotal', 'Commission', 'Discount', 'Taxable', 'GST', 'Grand Total', 'Status', 'Sales Rep'];
        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                $r['quotation_number'],
                $r['quotation_date'],
                $r['valid_until'],
                $r['company_name'],
                $r['contact_person'],
                $r['mobile'],
                $r['project_name'],
                $r['subtotal'],
                $r['total_commission'],
                $r['discount_amount'],
                $r['taxable_amount'],
                $r['gst_amount'],
                $r['grand_total'],
                $r['status'],
                $r['sales_person']
            ];
        }

        AuditLogger::log('EXPORT', 'Quotations', null, "Exported " . count($data) . " quotation records to {$format}");
        ExcelHandler::export($format, 'Sagar_Advertising_Quotations_' . date('Ymd'), $headers, $data);
    }
}
