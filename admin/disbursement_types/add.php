<?php
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireAdmin();

$pageTitle = 'เพิ่มประเภทการเบิก';
$tableName = 'disbursement_types';
$listUrl = BASE_URL . 'admin/disbursement_types/';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $data = ['code' => sanitize($_POST['code'] ?? ''), 'name' => sanitize($_POST['name'] ?? ''), 'description' => sanitize($_POST['description'] ?? ''), 'status' => sanitize($_POST['status'] ?? 'active')];
    $errors = validateRequired($data, ['code' => 'รหัส', 'name' => 'ชื่อ']);
    if (empty($errors)) {
        try {
            $db = getDB();
            $db->prepare("INSERT INTO {$tableName} (code, name, description, status) VALUES (?, ?, ?, ?)")->execute([$data['code'], $data['name'], $data['description'], $data['status']]);
            logActivity('create', $tableName, $db->lastInsertId(), null, $data);
            setFlashMessage('success', 'เพิ่มข้อมูลสำเร็จ');
            redirect($listUrl);
        } catch (PDOException $e) { $errors['general'] = $e->getCode() == 23000 ? 'รหัสนี้มีอยู่แล้ว' : $e->getMessage(); }
    }
}
include '../../includes/header.php';
?>
<div class="card">
    <div class="card-header"><i class="fas fa-plus-circle me-2"></i><?php echo $pageTitle; ?></div>
    <div class="card-body">
        <?php if (isset($errors['general'])): ?><div class="alert alert-danger"><?php echo e($errors['general']); ?></div><?php endif; ?>
        <form method="POST">
            <?php echo csrfField(); ?>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">รหัส *</label><input type="text" class="form-control" name="code" value="<?php echo e($_POST['code'] ?? ''); ?>" required></div>
                <div class="col-md-6 mb-3"><label class="form-label">ชื่อ *</label><input type="text" class="form-control" name="name" value="<?php echo e($_POST['name'] ?? ''); ?>" required></div>
            </div>
            <div class="row">
                <div class="col-md-8 mb-3"><label class="form-label">รายละเอียด</label><textarea class="form-control" name="description" rows="2"><?php echo e($_POST['description'] ?? ''); ?></textarea></div>
                <div class="col-md-4 mb-3"><label class="form-label">สถานะ</label><select class="form-select" name="status"><option value="active" selected>ใช้งาน</option><option value="inactive">ไม่ใช้งาน</option></select></div>
            </div>
            <hr><div class="d-flex justify-content-between"><a href="<?php echo $listUrl; ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>กลับ</a><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>บันทึก</button></div>
        </form>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
