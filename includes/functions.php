<?php
/**
 * Common Functions
 * ระบบงานเอกสารสำนักงานทนายความ
 */

require_once dirname(__DIR__) . '/config/config.php';

/**
 * Log activity
 */
function logActivity($action, $tableName = null, $recordId = null, $oldData = null, $newData = null) {
    if (!isLoggedIn()) return;
    
    try {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, table_name, record_id, old_data, new_data, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $action,
            $tableName,
            $recordId,
            $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        // Log error but don't break the application
        error_log("Activity log error: " . $e->getMessage());
    }
}

/**
 * Generate disbursement number
 * รูปแบบ: [ชื่อย่อสำนักงาน][เลขที่ 3 หลัก]/[ปี พ.ศ.]
 * ตัวอย่าง: SCB001/2569, KBANK002/2569
 */
function generateDisbursementNumber($caseId = null) {
    $db = getDB();
    $thaiYear = date('Y') + 543;
    
    // หาชื่อย่อสำนักงานจากคดี
    $officeShortName = 'DB'; // ค่าเริ่มต้นถ้าไม่มีข้อมูล
    
    if ($caseId) {
        $stmt = $db->prepare("
            SELECT o.short_name, o.code 
            FROM cases c 
            LEFT JOIN offices o ON c.office_id = o.id 
            WHERE c.id = ?
        ");
        $stmt->execute([$caseId]);
        $office = $stmt->fetch();
        
        if ($office) {
            // ใช้ short_name ถ้ามี ไม่งั้นใช้ code
            $officeShortName = !empty($office['short_name']) ? $office['short_name'] : $office['code'];
        }
    }
    
    // หาเลขที่ล่าสุดของสำนักงานนั้นในปีนี้
    $pattern = $officeShortName . '%/' . $thaiYear;
    
    $stmt = $db->prepare("
        SELECT disbursement_number FROM disbursements 
        WHERE disbursement_number LIKE ? 
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$pattern]);
    $last = $stmt->fetch();
    
    if ($last) {
        // ดึงเลขออกจากเลขที่ล่าสุด (ตัวเลขก่อน /)
        preg_match('/(\d+)\//', $last['disbursement_number'], $matches);
        $lastNumber = isset($matches[1]) ? intval($matches[1]) : 0;
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    return $officeShortName . str_pad($newNumber, 3, '0', STR_PAD_LEFT) . '/' . $thaiYear;
}

/**
 * Get all records from a table
 */
function getAll($table, $where = '', $params = [], $orderBy = 'id DESC') {
    $db = getDB();
    $sql = "SELECT * FROM {$table}";
    if ($where) {
        $sql .= " WHERE {$where}";
    }
    $sql .= " ORDER BY {$orderBy}";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get single record by ID
 */
function getById($table, $id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM {$table} WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get active records for dropdown
 */
function getActiveForDropdown($table, $valueField = 'id', $textField = 'name') {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT {$valueField} as value, {$textField} as text 
        FROM {$table} 
        WHERE status = 'active' 
        ORDER BY {$textField}
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get lawyers for dropdown
 */
function getLawyersForDropdown() {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT id as value, CONCAT(prefix, firstname, ' ', lastname) as text 
        FROM lawyers 
        WHERE status = 'active' 
        ORDER BY firstname
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Search cases by debtor code or name
 */
function searchCases($keyword, $limit = 10) {
    $db = getDB();
    $keyword = '%' . $keyword . '%';
    
    $stmt = $db->prepare("
        SELECT c.*, 
               ct.name as court_name,
               ao.name as ao_name
        FROM cases c
        LEFT JOIN courts ct ON c.court_id = ct.id
        LEFT JOIN account_officers ao ON c.ao_id = ao.id
        WHERE c.debtor_code LIKE ? OR c.debtor_name LIKE ?
        ORDER BY c.debtor_code
        LIMIT ?
    ");
    $stmt->execute([$keyword, $keyword, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get case details for disbursement
 */
function getCaseForDisbursement($caseId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT c.*, 
               ct.id as court_id, ct.name as court_name,
               ao.id as ao_id, ao.name as ao_name
        FROM cases c
        LEFT JOIN courts ct ON c.court_id = ct.id
        LEFT JOIN account_officers ao ON c.ao_id = ao.id
        WHERE c.id = ?
    ");
    $stmt->execute([$caseId]);
    return $stmt->fetch();
}

/**
 * Count records
 */
function countRecords($table, $where = '', $params = []) {
    $db = getDB();
    $sql = "SELECT COUNT(*) as count FROM {$table}";
    if ($where) {
        $sql .= " WHERE {$where}";
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result['count'];
}

/**
 * Pagination helper
 */
function paginate($table, $page = 1, $perPage = null, $where = '', $params = [], $orderBy = 'id DESC') {
    $perPage = $perPage ?? RECORDS_PER_PAGE;
    $offset = ($page - 1) * $perPage;
    
    $total = countRecords($table, $where, $params);
    $totalPages = ceil($total / $perPage);
    
    $db = getDB();
    $sql = "SELECT * FROM {$table}";
    if ($where) {
        $sql .= " WHERE {$where}";
    }
    $sql .= " ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    
    return [
        'data' => $data,
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages
    ];
}

/**
 * Build pagination HTML
 */
function paginationHTML($pagination, $baseUrl) {
    if ($pagination['total_pages'] <= 1) return '';
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($pagination['has_prev']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($pagination['current_page'] - 1) . '">ก่อนหน้า</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">ก่อนหน้า</span></li>';
    }
    
    // Page numbers
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $pagination['current_page']) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    // Next button
    if ($pagination['has_next']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($pagination['current_page'] + 1) . '">ถัดไป</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">ถัดไป</span></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Get dashboard statistics
 */
function getDashboardStats() {
    $db = getDB();
    
    // Total cases
    $stmt = $db->query("SELECT COUNT(*) as count FROM cases WHERE status = 'active'");
    $totalCases = $stmt->fetch()['count'];
    
    // Total disbursements pending
    $stmt = $db->query("SELECT COUNT(*) as count FROM disbursements WHERE status = 'pending'");
    $pendingDisbursements = $stmt->fetch()['count'];
    
    // Total amount pending
    $stmt = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM disbursements WHERE status = 'pending'");
    $pendingAmount = $stmt->fetch()['total'];
    
    // Total users
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
    $totalUsers = $stmt->fetch()['count'];
    
    // Recent cases
    $stmt = $db->query("
        SELECT c.*, wt.name as work_type_name, o.name as office_name
        FROM cases c
        LEFT JOIN work_types wt ON c.work_type_id = wt.id
        LEFT JOIN offices o ON c.office_id = o.id
        ORDER BY c.created_at DESC
        LIMIT 5
    ");
    $recentCases = $stmt->fetchAll();
    
    // Recent disbursements
    $stmt = $db->query("
        SELECT d.*, ct.name as court_name
        FROM disbursements d
        LEFT JOIN courts ct ON d.court_id = ct.id
        ORDER BY d.created_at DESC
        LIMIT 5
    ");
    $recentDisbursements = $stmt->fetchAll();
    
    return [
        'total_cases' => $totalCases,
        'pending_disbursements' => $pendingDisbursements,
        'pending_amount' => $pendingAmount,
        'total_users' => $totalUsers,
        'recent_cases' => $recentCases,
        'recent_disbursements' => $recentDisbursements
    ];
}

/**
 * Validate required fields
 */
function validateRequired($data, $fields) {
    $errors = [];
    foreach ($fields as $field => $label) {
        if (empty($data[$field])) {
            $errors[$field] = "กรุณากรอก{$label}";
        }
    }
    return $errors;
}

/**
 * Sanitize input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return trim(strip_tags($input));
}

/**
 * Upload file
 */
function uploadFile($file, $directory = 'uploads') {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์'];
    }
    
    // Check file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'ไฟล์มีขนาดใหญ่เกินไป'];
    }
    
    // Check extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'ประเภทไฟล์ไม่ได้รับอนุญาต'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $uploadPath = ROOT_PATH . $directory . DIRECTORY_SEPARATOR . $filename;
    
    // Create directory if not exists
    if (!is_dir(dirname($uploadPath))) {
        mkdir(dirname($uploadPath), 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $uploadPath];
    }
    
    return ['success' => false, 'message' => 'ไม่สามารถบันทึกไฟล์ได้'];
}
?>
