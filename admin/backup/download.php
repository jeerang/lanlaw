<?php
/**
 * Download Backup File
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

$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$contentTypes = [
    'sql' => 'application/sql',
    'csv' => 'text/csv',
    'zip' => 'application/zip'
];

$contentType = $contentTypes[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache');
header('Pragma: no-cache');

readfile($filepath);
exit;
