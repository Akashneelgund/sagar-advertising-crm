<?php
/**
 * Sagar Advertising CRM - Excel & CSV Import/Export Handler
 * Handles multi-step preview, column mapping, validation, error/skip logs, and filtered exports.
 */

declare(strict_types=1);

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/backend/services/AuditLogger.php';

class ExcelHandler {
    /**
     * Parse uploaded file (.csv, .xlsx, .xls) and extract headers + first preview rows
     */
    public static function parseUploadPreview(string $filePath): array {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $rows = [];
        if ($ext === 'csv' || $ext === 'txt') {
            $handle = fopen($filePath, 'r');
            if ($handle !== false) {
                while (($data = fgetcsv($handle, 4096, ',')) !== false) {
                    // Filter empty lines
                    if (array_filter($data, fn($v) => trim((string)$v) !== '')) {
                        $rows[] = array_map('trim', $data);
                    }
                }
                fclose($handle);
            }
        } elseif ($ext === 'xml' || $ext === 'xls') {
            // Excel XML spreadsheet parser
            $content = file_get_contents($filePath);
            if (preg_match_all('/<Row[^>]*>(.*?)<\/Row>/is', $content, $rowMatches)) {
                foreach ($rowMatches[1] as $rStr) {
                    if (preg_match_all('/<Data[^>]*>(.*?)<\/Data>/is', $rStr, $cellMatches)) {
                        $cells = array_map(fn($c) => html_entity_decode(strip_tags($c)), $cellMatches[1]);
                        if (array_filter($cells, fn($v) => trim($v) !== '')) {
                            $rows[] = $cells;
                        }
                    }
                }
            }
        }

        // Fallback for simple line-by-line CSV if fgetcsv failed
        if (empty($rows)) {
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $rows[] = str_getcsv($line);
            }
        }

        if (empty($rows)) {
            throw new Exception("Unable to parse file or file is empty.");
        }

        $headers = array_shift($rows);
        $preview = array_slice($rows, 0, 5);
        $totalDataRows = count($rows);

        return [
            'headers' => $headers,
            'preview' => $preview,
            'total_rows' => $totalDataRows,
            'raw_rows' => $rows
        ];
    }

    /**
     * Validate and execute Customer import with mapping
     *
     * @param array $rows The raw data rows
     * @param array $mapping Associative array [excel_col_index => db_field]
     * @return array [imported => count, skipped => count, errors => count, error_details => []]
     */
    public static function importCustomers(array $rows, array $mapping): array {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        // Invert mapping for fast lookup: [db_field => col_index]
        $fieldToCol = [];
        foreach ($mapping as $colIdx => $dbField) {
            if (!empty($dbField) && $dbField !== 'ignore') {
                $fieldToCol[$dbField] = (int)$colIdx;
            }
        }

        if (!isset($fieldToCol['company_name']) && !isset($fieldToCol['contact_person'])) {
            throw new Exception("Mapping must include at least 'Company Name' or 'Contact Person'.");
        }

        $pdo = DB::connect();
        $pdo->beginTransaction();

        try {
            // Get existing mobiles and emails to check duplicates
            $existingMobiles = DB::fetchAll("SELECT mobile FROM `customers` WHERE deleted_at IS NULL");
            $mobileSet = array_flip(array_map(fn($r) => preg_replace('/\D/', '', $r['mobile']), $existingMobiles));

            $codeCount = (int)DB::fetchColumn("SELECT COUNT(*) FROM `customers`");

            $insertStmt = $pdo->prepare("INSERT INTO `customers` (
                `customer_code`, `company_name`, `contact_person`, `mobile`, `alternate_mobile`,
                `email`, `whatsapp`, `address`, `city`, `state`, `pincode`, `gstin`,
                `customer_type`, `source`, `notes`, `status`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($rows as $rowIndex => $row) {
                $rowNum = $rowIndex + 2; // Accounting for 1-based header row
                
                $getVal = fn($field) => isset($fieldToCol[$field]) ? trim((string)($row[$fieldToCol[$field]] ?? '')) : '';

                $companyName = $getVal('company_name');
                $contactPerson = $getVal('contact_person');
                $mobile = preg_replace('/[^\d+]/', '', $getVal('mobile'));
                $altMobile = $getVal('alternate_mobile');
                $email = $getVal('email');
                $whatsapp = $getVal('whatsapp') ?: $mobile;
                $address = $getVal('address');
                $city = $getVal('city') ?: 'Hubballi';
                $state = $getVal('state') ?: 'Karnataka';
                $pincode = $getVal('pincode') ?: '580024';
                $gstin = strtoupper($getVal('gstin'));
                $type = $getVal('customer_type') ?: 'Business';
                $source = $getVal('source') ?: 'Excel Import';
                $notes = $getVal('notes');
                $status = $getVal('status') ?: 'Active';

                // If company name is empty, fall back to contact person
                if (empty($companyName) && !empty($contactPerson)) {
                    $companyName = $contactPerson;
                }
                if (empty($contactPerson) && !empty($companyName)) {
                    $contactPerson = $companyName;
                }

                // 1. Validation: required name and mobile
                if (empty($companyName)) {
                    $errors[] = "Row {$rowNum}: Company Name or Contact Person is missing.";
                    continue;
                }

                $cleanPhone = preg_replace('/\D/', '', $mobile);
                if (strlen($cleanPhone) < 10) {
                    $errors[] = "Row {$rowNum} ('{$companyName}'): Mobile number '{$mobile}' is invalid (minimum 10 digits).";
                    continue;
                }

                // 2. Duplicate checking
                if (isset($mobileSet[$cleanPhone])) {
                    $skipped++;
                    $errors[] = "Row {$rowNum} ('{$companyName}'): Skipped duplicate mobile {$mobile}.";
                    continue;
                }

                // 3. Email validation if supplied
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Row {$rowNum} ('{$companyName}'): Invalid email address '{$email}'.";
                    continue;
                }

                $customerCode = 'SA-CUST-' . (1000 + ++$codeCount);

                $insertStmt->execute([
                    $customerCode,
                    $companyName,
                    $contactPerson,
                    $mobile,
                    $altMobile,
                    $email,
                    $whatsapp,
                    $address,
                    $city,
                    $state,
                    $pincode,
                    $gstin,
                    $type,
                    $source,
                    $notes,
                    $status
                ]);

                $mobileSet[$cleanPhone] = true;
                $imported++;
            }

            $pdo->commit();
            AuditLogger::log('IMPORT', 'Customers', null, "Excel import completed. Imported: {$imported}, Skipped: {$skipped}, Errors: " . count($errors));

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors_count' => count($errors),
            'errors' => $errors
        ];
    }

    /**
     * Export dataset to CSV or Excel XML stream
     */
    public static function export(string $format, string $filename, array $headers, array $data): void {
        if ($format === 'excel') {
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header("Content-Disposition: attachment; filename=\"{$filename}.xls\"");
            
            echo '<?xml version="1.0" encoding="utf-8"?>';
            ?>
            <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
                      xmlns:o="urn:schemas-microsoft-com:office:office"
                      xmlns:x="urn:schemas-microsoft-com:office:excel"
                      xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
                <Worksheet ss:Name="Sheet1">
                    <Table>
                        <Row>
                            <?php foreach ($headers as $h): ?>
                                <Cell><Data ss:Type="String"><?= htmlspecialchars((string)$h, ENT_QUOTES) ?></Data></Cell>
                            <?php endforeach; ?>
                        </Row>
                        <?php foreach ($data as $row): ?>
                        <Row>
                            <?php foreach ($row as $cell): ?>
                                <Cell><Data ss:Type="String"><?= htmlspecialchars((string)$cell, ENT_QUOTES) ?></Data></Cell>
                            <?php endforeach; ?>
                        </Row>
                        <?php endforeach; ?>
                    </Table>
                </Worksheet>
            </Workbook>
            <?php
            exit;
        } else {
            // CSV
            header('Content-Type: text/csv; charset=utf-8');
            header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");
            $out = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);
            foreach ($data as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
            exit;
        }
    }
}
