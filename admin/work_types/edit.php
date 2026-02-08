<?php
/**
 * Edit Work Type
 * ระบบงานเอกสารสำนักงานทนายความ
 */

require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

requireAdmin();

$tableName = 'work_types';
$listUrl = BASE_URL . 'admin/work_types/';

$id = $_GET['id'] ?? 0;
$item = getById($tableName, $id);

if (!$item) {
    setFlashMessage('danger', 'ไม่พบข้อมูล');
    redirect($listUrl);
}

$pageTitle = 'แก้ไขประเภทงาน';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $data = [
        'code' => sanitize($_POST['code'] ?? ''),
        'name' => sanitize($_POST['name'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'status' => sanitize($_POST['status'] ?? 'active')
    ];
    
    $errors = validateRequired($data, [
        'code' => 'รหัส',
        'name' => 'ชื่อ'
    ]);
    
    if (empty($errors)) {
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE {$tableName} SET code = ?, name = ?, description = ?, status = ? WHERE id = ?");
            $stmt->execute([$data['code'], $data['name'], $data['description'], $data['status'], $id]);
            logActivity('update', $tableName, $id, $item, $data);
            setFlashMessage('success', 'แก้ไขข้อมูลสำเร็จ');
            redirect($listUrl);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors['code'] = 'รหัสนี้มีอยู่แล้ว';
            } else {
                $errors['general'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            }
        }
    }
} else {
    $_POST = $item;
}

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-edit me-2"></i><?php echo $pageTitle; ?>
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
                        <label for="code" class="form-label">รหัส <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo isset($errors['code']) ? 'is-invalid' : ''; ?>" 
                               id="code" name="code" value="<?php echo e($_POST['code'] ?? ''); ?>" required>
                        <?php if (isset($errors['code'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['code']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">ชื่อ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                               id="name" name="name" value="<?php echo e($_POST['name'] ?? ''); ?>" required>
                        <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['name']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="description" class="form-label">รายละเอียด</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo e($_POST['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">สถานะ</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?php echo ($_POST['status'] ?? '') === 'active' ? 'selected' : ''; ?>>ใช้งาน</option>
                            <option value="inactive" <?php echo ($_POST['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>ไม่ใช้งาน</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <div class="d-flex justify-content-between">
                <a href="<?php echo $listUrl; ?>" class="btn btn-secondary">
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
