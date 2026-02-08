<?php
/**
 * Authentication Functions
 * ระบบงานเอกสารสำนักงานทนายความ
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

/**
 * Authenticate user
 */
function authenticate($username, $password) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {  
        // Update last login
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['user_role'] = $user['role'];
        
        // Log activity
        logActivity('login', 'users', $user['id']);
        
        return true;
    }
    
    return false;
}

/**
 * Logout user
 */
function logout() {
    if (isLoggedIn()) {
        logActivity('logout', 'users', $_SESSION['user_id']);
    }
    
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('warning', 'กรุณาเข้าสู่ระบบก่อน');
        redirect(BASE_URL . 'index.php');
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        setFlashMessage('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        redirect(BASE_URL . 'dashboard.php');
    }
}

/**
 * Change password
 */
function changePassword($userId, $currentPassword, $newPassword) {
    $db = getDB();
    
    // Get current password
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        return ['success' => false, 'message' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง'];
    }
    
    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $userId]);
    
    logActivity('change_password', 'users', $userId);
    
    return ['success' => true, 'message' => 'เปลี่ยนรหัสผ่านสำเร็จ'];
}

/**
 * Reset password (Admin only)
 */
function resetPassword($userId, $newPassword) {
    $db = getDB();
    
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $userId]);
    
    logActivity('reset_password', 'users', $userId);
    
    return ['success' => true, 'message' => 'รีเซ็ตรหัสผ่านสำเร็จ'];
}

/**
 * Create user
 */
function createUser($data) {
    $db = getDB();
    
    // Check username exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'ชื่อผู้ใช้นี้มีอยู่แล้ว'];
    }
    
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO users (username, password, fullname, email, role, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['username'],
        $hashedPassword,
        $data['fullname'],
        $data['email'] ?? null,
        $data['role'] ?? 'user',
        $data['status'] ?? 'active'
    ]);
    
    $userId = $db->lastInsertId();
    logActivity('create', 'users', $userId, null, $data);
    
    return ['success' => true, 'message' => 'เพิ่มผู้ใช้งานสำเร็จ', 'id' => $userId];
}

/**
 * Update user
 */
function updateUser($id, $data) {
    $db = getDB();
    
    // Get old data
    $oldData = getById('users', $id);
    
    // Check username exists (if changed)
    if (isset($data['username']) && $data['username'] !== $oldData['username']) {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$data['username'], $id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'ชื่อผู้ใช้นี้มีอยู่แล้ว'];
        }
    }
    
    $sql = "UPDATE users SET username = ?, fullname = ?, email = ?, role = ?, status = ?";
    $params = [
        $data['username'],
        $data['fullname'],
        $data['email'] ?? null,
        $data['role'] ?? 'user',
        $data['status'] ?? 'active'
    ];
    
    // Update password if provided
    if (!empty($data['password'])) {
        $sql .= ", password = ?";
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $id;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    logActivity('update', 'users', $id, $oldData, $data);
    
    return ['success' => true, 'message' => 'แก้ไขผู้ใช้งานสำเร็จ'];
}

/**
 * Delete user
 */
function deleteUser($id) {
    $db = getDB();
    
    // Prevent self-deletion
    if ($id == $_SESSION['user_id']) {
        return ['success' => false, 'message' => 'ไม่สามารถลบบัญชีตัวเองได้'];
    }
    
    $oldData = getById('users', $id);
    
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    
    logActivity('delete', 'users', $id, $oldData, null);
    
    return ['success' => true, 'message' => 'ลบผู้ใช้งานสำเร็จ'];
}
?>
