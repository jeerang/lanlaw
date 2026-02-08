<?php
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireAdmin();

$tableName = 'offices';
$listUrl = BASE_URL . 'admin/offices/';
$id = $_GET['id'] ?? 0;
$item = getById($tableName, $id);
if (!$item) { setFlashMessage('danger', 'ไม่พบข้อมูล'); redirect($listUrl); }

$pageTitle = 'แก้ไขสำนักงานเจ้าของงาน';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $data = ['code' => sanitize($_POST['code'] ?? ''), 'name' => sanitize($_POST['name'] ?? ''), 'short_name' => sanitize($_POST['short_name'] ?? ''), 'address' => sanitize($_POST['address'] ?? ''), 'phone' => sanitize($_POST['phone'] ?? ''), 'contact_person' => sanitize($_POST['contact_person'] ?? ''), 'status' => sanitize($_POST['status'] ?? 'active')];
    $errors = validateRequired($data, ['code' => 'รหัส', 'name' => 'ชื่อสำนักงาน']);
    if (empty($errors)) {
        try {
            $db = getDB();
            $db->prepare("UPDATE {$tableName} SET code=?, name=?, short_name=?, address=?, phone=?, contact_person=?, status=? WHERE id=?")->execute([$data['code'], $data['name'], $data['short_name'], $data['address'], $data['phone'], $data['contact_person'], $data['status'], $id]);
            logActivity('update', $tableName, $id, $item, $data);
            setFlashMessage('success', 'แก้ไขข้อมูลสำเร็จ');
            redirect($listUrl);
        } catch (PDOException $e) { $errors['general'] = $e->getCode() == 23000 ? 'รหัสนี้มีอยู่แล้ว' : $e->getMessage(); }
    }
} else { $_POST = $item; }
include '../../includes/header.php';
?>
<div class="card">
    <div class="card-header"><i class="fas fa-edit me-2"></i><?php echo $pageTitle; ?></div>
    <div class="card-body">
        <?php if (isset($errors['general'])): ?><div class="alert alert-danger"><?php echo e($errors['general']); ?></div><?php endif; ?>
        <form method="POST">
            <?php echo csrfField(); ?>
            <div class="row">
                <div class="col-md-3 mb-3"><label class="form-label">รหัส *</label><input type="text" class="form-control" name="code" value="<?php echo e($_POST['code'] ?? ''); ?>" required></div>
                <div class="col-md-5 mb-3"><label class="form-label">ชื่อสำนักงาน *</label><input type="text" class="form-control" name="name" value="<?php echo e($_POST['name'] ?? ''); ?>" required></div>
                <div class="col-md-4 mb-3"><label class="form-label">ชื่อย่อ</label><input type="text" class="form-control" name="short_name" value="<?php echo e($_POST['short_name'] ?? ''); ?>" placeholder="เช่น SCB, KBANK"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">ผู้ติดต่อ</label><input type="text" class="form-control" name="contact_person" value="<?php echo e($_POST['contact_person'] ?? ''); ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label">โทรศัพท์</label><input type="text" class="form-control" name="phone" value="<?php echo e($_POST['phone'] ?? ''); ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label">สถานะ</label><select class="form-select" name="status"><option value="active" <?php echo ($_POST['status']??'')=='active'?'selected':''; ?>>ใช้งาน</option><option value="inactive" <?php echo ($_POST['status']??'')=='inactive'?'selected':''; ?>>ไม่ใช้งาน</option></select></div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3"><label class="form-label">ที่อยู่</label><textarea class="form-control" name="address" rows="2"><?php echo e($_POST['address'] ?? ''); ?></textarea></div>
            </div>
            <hr><div class="d-flex justify-content-between"><a href="<?php echo $listUrl; ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>กลับ</a><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>บันทึก</button></div>
        </form>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
