<?php
/**
 * System Configuration
 * ระบบงานเอกสารสำนักงานทนายความ
 */

// Include database config first to get ENVIRONMENT
require_once __DIR__ . '/database.php';

// Security settings
define('FORCE_HTTPS', defined('ENVIRONMENT') && ENVIRONMENT === 'production'); // Force HTTPS in production
define('SECURE_COOKIES', FORCE_HTTPS); // Secure cookies only over HTTPS

// Force HTTPS redirect in production
if (FORCE_HTTPS && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
    $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirectUrl);
    exit;
}

// Set security headers
if (ENVIRONMENT === 'production') {
    // HTTP Strict Transport Security (HSTS)
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
// Prevent clickjacking
header('X-Frame-Options: SAMEORIGIN');
// Prevent MIME type sniffing
header('X-Content-Type-Options: nosniff');
// XSS Protection
header('X-XSS-Protection: 1; mode=block');
// Referrer Policy
header('Referrer-Policy: strict-origin-when-cross-origin');

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    $sessionOptions = [
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
        'use_only_cookies' => true
    ];
    
    // Add secure flag for HTTPS
    if (SECURE_COOKIES) {
        $sessionOptions['cookie_secure'] = true;
    }
    
    session_start($sessionOptions);
}

// Regenerate session ID periodically to prevent session fixation
if (!isset($_SESSION['_last_regeneration'])) {
    $_SESSION['_last_regeneration'] = time();
} elseif (time() - $_SESSION['_last_regeneration'] > 300) { // Every 5 minutes
    session_regenerate_id(true);
    $_SESSION['_last_regeneration'] = time();
}

// Error reporting based on environment
if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', dirname(__DIR__) . '/logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Timezone
date_default_timezone_set('Asia/Bangkok');

// Site settings
define('SITE_NAME', 'ระบบงานเอกสารสำนักงานทนายความ');
define('SITE_SHORT_NAME', 'LanLaw');
define('SITE_VERSION', '1.0.0');

// Company Info
define('COMPANY_NAME', 'ลัลฌา สำนักงานกฏหมายและธุรกิจ');
define('COMPANY_ADDRESS', '');
define('COMPANY_PHONE', '');
define('COMPANY_EMAIL', '');

// Base URL (adjust for your environment)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . '://' . $host . '/lanlaw/');

// Directory paths
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('CONFIG_PATH', ROOT_PATH . 'config' . DIRECTORY_SEPARATOR);
define('INCLUDES_PATH', ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR);
define('UPLOADS_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('ASSETS_PATH', BASE_URL . 'assets/');

// Pagination settings
define('RECORDS_PER_PAGE', 20);

// Upload settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Date formats
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i:s');
define('DATE_FORMAT_DB', 'Y-m-d');
define('DATETIME_FORMAT_DB', 'Y-m-d H:i:s');

// Status labels
$STATUS_LABELS = [
    'active' => ['text' => 'ใช้งาน', 'class' => 'success'],
    'inactive' => ['text' => 'ไม่ใช้งาน', 'class' => 'secondary'],
    'pending' => ['text' => 'ระหว่างเบิก', 'class' => 'warning'],
    'processing' => ['text' => 'ค้างจ่าย', 'class' => 'info'],
    'paid' => ['text' => 'รับเงินแล้ว', 'class' => 'success'],
    'closed' => ['text' => 'ปิดคดี', 'class' => 'dark']
];

// Role labels
$ROLE_LABELS = [
    'admin' => ['text' => 'ผู้ดูแลระบบ', 'class' => 'danger'],
    'user' => ['text' => 'ผู้ใช้งาน', 'class' => 'primary']
];

// Disbursement statuses
$DISBURSEMENT_STATUSES = [
    'pending' => 'รออนุมัติ',
    'processing' => 'อนุมัติแล้ว',
    'paid' => 'จ่ายแล้ว',
    'rejected' => 'ปฏิเสธ'
];

/**
 * Format date from database to Thai format
 */
function formatDate($date, $format = null) {
    if (empty($date) || $date == '0000-00-00') return '-';
    $format = $format ?? DATE_FORMAT;
    return date($format, strtotime($date));
}

/**
 * Format datetime from database to Thai format
 */
function formatDateTime($datetime, $format = null) {
    if (empty($datetime) || $datetime == '0000-00-00 00:00:00') return '-';
    $format = $format ?? DATETIME_FORMAT;
    return date($format, strtotime($datetime));
}

/**
 * Format number with comma
 */
function formatNumber($number, $decimals = 2) {
    return number_format($number, $decimals, '.', ',');
}

/**
 * Format file size to human readable
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Convert Thai Buddhist year to Christian year
 */
function thaiToChristianYear($thaiDate) {
    // Convert from พ.ศ. to ค.ศ.
    if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $thaiDate, $matches)) {
        $year = $matches[3] - 543;
        return $matches[1] . '/' . $matches[2] . '/' . $year;
    }
    return $thaiDate;
}

/**
 * Convert Christian year to Thai Buddhist year
 */
function christianToThaiYear($date) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    $year = date('Y', $timestamp) + 543;
    return date('d/m/', $timestamp) . $year;
}

/**
 * Escape HTML output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate CSRF hidden input field
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require valid CSRF token or die
 */
function requireCSRF() {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($token)) {
        setFlashMessage('danger', 'คำขอไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
        redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
    }
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user info
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'fullname' => $_SESSION['fullname'],
        'role' => $_SESSION['user_role']
    ];
}
?>
