<?php
/**
 * Delete Backup File
 */
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireAdmin();

$filename = $_GET['file'] ?? '';

if (empty($filename)) {
    setFlashMessage('danger', 'ไม่พบไฟล์');
    redirect(BASE_URL . 'admin/backup/');
}

// Sanitize filename to prevent directory traversal
$filename = basename($filename);
$filepath = ROOT_PATH . 'backups/' . $filename;

if (!file_exists($filepath)) {
    setFlashMessage('danger', 'ไม่พบไฟล์');
    redirect(BASE_URL . 'admin/backup/');
}

// Delete file
if (unlink($filepath)) {
    logActivity('delete_backup', 'system', null, null, ['file' => $filename]);
    setFlashMessage('success', 'ลบไฟล์สำเร็จ');
} else {
    setFlashMessage('danger', 'ไม่สามารถลบไฟล์ได้');
}

redirect(BASE_URL . 'admin/backup/');
