<?php
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireAdmin();

$pageTitle = 'เพิ่มทนายความ';
$tableName = 'lawyers';
$listUrl = BASE_URL . 'admin/lawyers/';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $data = ['code' => sanitize($_POST['code'] ?? ''), 'prefix' => sanitize($_POST['prefix'] ?? ''), 'firstname' => sanitize($_POST['firstname'] ?? ''), 'lastname' => sanitize($_POST['lastname'] ?? ''), 'license_number' => sanitize($_POST['license_number'] ?? ''), 'phone' => sanitize($_POST['phone'] ?? ''), 'email' => sanitize($_POST['email'] ?? ''), 'status' => sanitize($_POST['status'] ?? 'active')];
    $errors = validateRequired($data, ['code' => 'รหัส', 'firstname' => 'ชื่อ', 'lastname' => 'นามสกุล']);
    if (empty($errors)) {
        try {
            $db = getDB();
            $db->prepare("INSERT INTO {$tableName} (code, prefix, firstname, lastname, license_number, phone, email, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")->execute([$data['code'], $data['prefix'], $data['firstname'], $data['lastname'], $data['license_number'], $data['phone'], $data['email'], $data['status']]);
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
                <div class="col-md-3 mb-3"><label class="form-label">รหัส *</label><input type="text" class="form-control" name="code" value="<?php echo e($_POST['code'] ?? ''); ?>" required></div>
                <div class="col-md-2 mb-3"><label class="form-label">คำนำหน้า</label><select class="form-select" name="prefix"><option value="นาย">นาย</option><option value="นาง">นาง</option><option value="นางสาว">นางสาว</option></select></div>
                <div class="col-md-3 mb-3"><label class="form-label">ชื่อ *</label><input type="text" class="form-control" name="firstname" value="<?php echo e($_POST['firstname'] ?? ''); ?>" required></div>
                <div class="col-md-4 mb-3"><label class="form-label">นามสกุล *</label><input type="text" class="form-control" name="lastname" value="<?php echo e($_POST['lastname'] ?? ''); ?>" required></div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3"><label class="form-label">เลขใบอนุญาต</label><input type="text" class="form-control" name="license_number" value="<?php echo e($_POST['license_number'] ?? ''); ?>"></div>
                <div class="col-md-3 mb-3"><label class="form-label">โทรศัพท์</label><input type="text" class="form-control" name="phone" value="<?php echo e($_POST['phone'] ?? ''); ?>"></div>
                <div class="col-md-3 mb-3"><label class="form-label">อีเมล</label><input type="email" class="form-control" name="email" value="<?php echo e($_POST['email'] ?? ''); ?>"></div>
                <div class="col-md-3 mb-3"><label class="form-label">สถานะ</label><select class="form-select" name="status"><option value="active" selected>ใช้งาน</option><option value="inactive">ไม่ใช้งาน</option></select></div>
            </div>
            <hr><div class="d-flex justify-content-between"><a href="<?php echo $listUrl; ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>กลับ</a><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>บันทึก</button></div>
        </form>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
