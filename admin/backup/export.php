<?php
/**
 * Export Data (Backup)
 * ส่งออกข้อมูลในรูปแบบ SQL หรือ CSV
 */
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireAdmin();
requireCSRF();

$tables = $_POST['tables'] ?? [];
$format = $_POST['format'] ?? 'sql';

if (empty($tables)) {
    setFlashMessage('danger', 'กรุณาเลือกตารางที่ต้องการสำรอง');
    redirect(BASE_URL . 'admin/backup/');
}

$db = getDB();
$timestamp = date('Y-m-d_His');
$backupDir = ROOT_PATH . 'backups';

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

try {
    if ($format === 'sql') {
        // Export as SQL
        $filename = "backup_{$timestamp}.sql";
        $filepath = $backupDir . '/' . $filename;
        
        $sql = "-- LanLaw Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Tables: " . implode(', ', $tables) . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "SET AUTOCOMMIT = 0;\n";
        $sql .= "START TRANSACTION;\n\n";
        
        foreach ($tables as $table) {
            // Get table structure
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Table structure for `{$table}`\n";
            $sql .= "-- --------------------------------------------------------\n\n";
            
            $stmt = $db->query("SHOW CREATE TABLE `{$table}`");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $row[1] . ";\n\n";
            
            // Get table data
            $stmt = $db->query("SELECT * FROM `{$table}`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $sql .= "-- Dumping data for table `{$table}`\n\n";
                
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';
                
                foreach ($rows as $row) {
                    $values = array_map(function($val) use ($db) {
                        if ($val === null) {
                            return 'NULL';
                        }
                        return $db->quote($val);
                    }, array_values($row));
                    
                    $sql .= "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }
        
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $sql .= "COMMIT;\n";
        
        // Save file
        file_put_contents($filepath, $sql);
        
        // Log activity
        logActivity('backup', 'system', null, null, ['tables' => $tables, 'format' => 'sql', 'file' => $filename]);
        
        // Download file
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
        
    } else {
        // Export as CSV (ZIP)
        $zipFilename = "backup_{$timestamp}.zip";
        $zipFilepath = $backupDir . '/' . $zipFilename;
        
        $zip = new ZipArchive();
        if ($zip->open($zipFilepath, ZipArchive::CREATE) !== TRUE) {
            throw new Exception('ไม่สามารถสร้างไฟล์ ZIP ได้');
        }
        
        foreach ($tables as $table) {
            $stmt = $db->query("SELECT * FROM `{$table}`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $csvContent = '';
                
                // Header row
                $columns = array_keys($rows[0]);
                $csvContent .= implode(',', array_map(function($col) {
                    return '"' . str_replace('"', '""', $col) . '"';
                }, $columns)) . "\n";
                
                // Data rows
                foreach ($rows as $row) {
                    $csvContent .= implode(',', array_map(function($val) {
                        if ($val === null) return '""';
                        return '"' . str_replace('"', '""', $val) . '"';
                    }, array_values($row))) . "\n";
                }
                
                $zip->addFromString("{$table}.csv", "\xEF\xBB\xBF" . $csvContent); // Add BOM for UTF-8
            }
        }
        
        $zip->close();
        
        // Log activity
        logActivity('backup', 'system', null, null, ['tables' => $tables, 'format' => 'csv', 'file' => $zipFilename]);
        
        // Download file
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($zipFilepath));
        readfile($zipFilepath);
        exit;
    }
    
} catch (Exception $e) {
    setFlashMessage('danger', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    redirect(BASE_URL . 'admin/backup/');
}
