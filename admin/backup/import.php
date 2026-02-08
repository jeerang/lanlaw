<?php
/**
 * Import Data
 * นำเข้าข้อมูลจากไฟล์ SQL หรือ CSV
 */
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireAdmin();
requireCSRF();

$targetTable = $_POST['target_table'] ?? '';
$importMode = $_POST['import_mode'] ?? 'append';

if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
    setFlashMessage('danger', 'กรุณาเลือกไฟล์ที่ต้องการนำเข้า');
    redirect(BASE_URL . 'admin/backup/');
}

$file = $_FILES['import_file'];
$filename = $file['name'];
$tmpPath = $file['tmp_name'];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($ext, ['sql', 'csv', 'zip'])) {
    setFlashMessage('danger', 'รูปแบบไฟล์ไม่ถูกต้อง (รองรับ .sql, .csv, .zip)');
    redirect(BASE_URL . 'admin/backup/');
}

$db = getDB();
$importedCount = 0;
$errors = [];
$transactionActive = false;

try {
    if ($ext === 'sql') {
        // Import SQL file - don't use transaction as SQL may have its own
        $sql = file_get_contents($tmpPath);
        
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split by semicolon
        $statements = preg_split('/;\s*$/m', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                // Skip transaction control statements from backup file
                $upperStatement = strtoupper($statement);
                if (strpos($upperStatement, 'START TRANSACTION') !== false ||
                    strpos($upperStatement, 'BEGIN') !== false ||
                    strpos($upperStatement, 'COMMIT') !== false ||
                    strpos($upperStatement, 'ROLLBACK') !== false) {
                    continue;
                }
                
                try {
                    $db->exec($statement);
                    $importedCount++;
                } catch (PDOException $e) {
                    $errors[] = "SQL Error: " . substr($e->getMessage(), 0, 100);
                }
            }
        }
        
    } else {
        // For CSV and ZIP, use transaction
        $db->beginTransaction();
        $transactionActive = true;
        
        if ($ext === 'csv') {
            // Import single CSV file
            if (empty($targetTable)) {
                throw new Exception('กรุณาระบุตารางเป้าหมายสำหรับไฟล์ CSV');
            }
            
            $importedCount = importCSV($db, $tmpPath, $targetTable, $importMode);
            
        } elseif ($ext === 'zip') {
            // Import ZIP file containing multiple CSVs
            $zip = new ZipArchive();
            if ($zip->open($tmpPath) !== TRUE) {
                throw new Exception('ไม่สามารถเปิดไฟล์ ZIP ได้');
            }
            
            $tempDir = sys_get_temp_dir() . '/lanlaw_import_' . time();
            mkdir($tempDir, 0755, true);
            $zip->extractTo($tempDir);
            $zip->close();
            
            // Process each CSV file
            $csvFiles = glob($tempDir . '/*.csv');
            foreach ($csvFiles as $csvFile) {
                $tableName = pathinfo($csvFile, PATHINFO_FILENAME);
                
                // Verify table exists
                try {
                    $db->query("SELECT 1 FROM `{$tableName}` LIMIT 1");
                    $count = importCSV($db, $csvFile, $tableName, $importMode);
                    $importedCount += $count;
                } catch (PDOException $e) {
                    $errors[] = "ไม่พบตาราง: {$tableName}";
                }
            }
            
            // Cleanup temp directory
            array_map('unlink', glob($tempDir . '/*'));
            rmdir($tempDir);
        }
        
        $db->commit();
        $transactionActive = false;
    }
    
    // Log activity
    logActivity('import', 'system', null, null, [
        'file' => $filename,
        'mode' => $importMode,
        'count' => $importedCount
    ]);
    
    $message = "นำเข้าข้อมูลสำเร็จ ({$importedCount} รายการ)";
    if (!empty($errors)) {
        $message .= " แต่มีข้อผิดพลาดบางส่วน: " . implode(', ', array_slice($errors, 0, 3));
    }
    setFlashMessage('success', $message);
    
} catch (Exception $e) {
    if ($transactionActive) {
        $db->rollBack();
    }
    setFlashMessage('danger', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
}

redirect(BASE_URL . 'admin/backup/');

/**
 * Import CSV file to table
 */
function importCSV($db, $filepath, $table, $mode) {
    // Read CSV file
    $content = file_get_contents($filepath);
    // Remove BOM if present
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
    
    $lines = explode("\n", $content);
    if (count($lines) < 2) {
        return 0;
    }
    
    // Parse header
    $header = str_getcsv(array_shift($lines));
    $header = array_map('trim', $header);
    
    // Clear table if replace mode
    if ($mode === 'replace') {
        $db->exec("SET FOREIGN_KEY_CHECKS=0");
        $db->exec("TRUNCATE TABLE `{$table}`");
        $db->exec("SET FOREIGN_KEY_CHECKS=1");
    }
    
    // Prepare insert statement
    $placeholders = array_fill(0, count($header), '?');
    $columns = '`' . implode('`, `', $header) . '`';
    
    $sql = "INSERT INTO `{$table}` ({$columns}) VALUES (" . implode(', ', $placeholders) . ")";
    if ($mode === 'append') {
        $sql .= " ON DUPLICATE KEY UPDATE ";
        $updates = [];
        foreach ($header as $col) {
            if ($col !== 'id') {
                $updates[] = "`{$col}` = VALUES(`{$col}`)";
            }
        }
        $sql .= implode(', ', $updates);
    }
    
    $stmt = $db->prepare($sql);
    $count = 0;
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $values = str_getcsv($line);
        if (count($values) !== count($header)) continue;
        
        // Convert empty strings to null where appropriate
        $values = array_map(function($v) {
            $v = trim($v);
            return ($v === '' || $v === 'NULL') ? null : $v;
        }, $values);
        
        try {
            $stmt->execute($values);
            $count++;
        } catch (PDOException $e) {
            // Skip duplicate or invalid rows
            continue;
        }
    }
    
    return $count;
}
