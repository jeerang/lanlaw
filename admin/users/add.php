<?php
/**
 * Add User
 * ระบบงานเอกสารสำนักงานทนายความ
 */

require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

requireAdmin();

$pageTitle = 'เพิ่มผู้ใช้งาน';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    $data = [
        'username' => sanitize($_POST['username'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'fullname' => sanitize($_POST['fullname'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'role' => sanitize($_POST['role'] ?? 'user'),
        'status' => sanitize($_POST['status'] ?? 'active')
    ];
    
    // Validate
    $errors = validateRequired($data, [
        'username' => 'ชื่อผู้ใช้',
        'password' => 'รหัสผ่าน',
        'fullname' => 'ชื่อ-นามสกุล'
    ]);
    
    if (strlen($data['password']) < 6) {
        $errors['password'] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    }
    
    if (empty($errors)) {
        $result = createUser($data);
        if ($result['success']) {
            setFlashMessage('success', $result['message']);
            redirect(BASE_URL . 'admin/users/');
        } else {
            $errors['general'] = $result['message'];
        }
    }
}

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-user-plus me-2"></i><?php echo $pageTitle; ?>
    </div>
    <div class="card-body">
        <?php if (isset($errors['general'])): ?>
        <div class="alert alert-danger"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?php echo csrfField(); ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="username" class="form-label">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                               id="username" name="username" value="<?php echo e($_POST['username'] ?? ''); ?>" required>
                        <?php if (isset($errors['username'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['username']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password" class="form-label">รหัสผ่าน <span class="text-danger">*</span></label>
                        <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                               id="password" name="password" required>
                        <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['password']); ?></div>
                        <?php endif; ?>
                        <small class="text-muted">รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร</small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="fullname" class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo isset($errors['fullname']) ? 'is-invalid' : ''; ?>" 
                               id="fullname" name="fullname" value="<?php echo e($_POST['fullname'] ?? ''); ?>" required>
                        <?php if (isset($errors['fullname'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['fullname']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">อีเมล</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo e($_POST['email'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="role" class="form-label">สิทธิ์ <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="user" <?php echo ($_POST['role'] ?? '') === 'user' ? 'selected' : ''; ?>>ผู้ใช้งานทั่วไป</option>
                            <option value="admin" <?php echo ($_POST['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>ผู้ดูแลระบบ</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label">สถานะ <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" <?php echo ($_POST['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>ใช้งาน</option>
                            <option value="inactive" <?php echo ($_POST['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>ไม่ใช้งาน</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <div class="d-flex justify-content-between">
                <a href="<?php echo BASE_URL; ?>admin/users/" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>กลับ
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
