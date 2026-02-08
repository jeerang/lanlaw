<?php
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireAdmin();

$pageTitle = 'จัดการ A/O';
$tableName = 'account_officers';

if (isset($_GET['delete'])) {
    $db = getDB();
    $oldData = getById($tableName, $_GET['delete']);
    $db->prepare("DELETE FROM {$tableName} WHERE id = ?")->execute([$_GET['delete']]);
    logActivity('delete', $tableName, $_GET['delete'], $oldData);
    setFlashMessage('success', 'ลบข้อมูลสำเร็จ');
    redirect(BASE_URL . 'admin/account_officers/');
}

$items = getAll($tableName, '', [], 'name ASC');
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-user-cog me-2"></i>รายการ A/O</span>
        <a href="add.php" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>เพิ่ม A/O</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead><tr><th width="5%">#</th><th>รหัส</th><th>ชื่อ</th><th>แผนก</th><th>โทรศัพท์</th><th>สถานะ</th><th width="15%">จัดการ</th></tr></thead>
                <tbody>
                    <?php foreach ($items as $i => $item): global $STATUS_LABELS; $sl = $STATUS_LABELS[$item['status']] ?? ['text'=>$item['status'],'class'=>'secondary']; ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><code><?php echo e($item['code']); ?></code></td>
                        <td><?php echo e($item['name']); ?></td>
                        <td><?php echo e($item['department'] ?? '-'); ?></td>
                        <td><?php echo e($item['phone'] ?? '-'); ?></td>
                        <td><span class="badge bg-<?php echo $sl['class']; ?>"><?php echo $sl['text']; ?></span></td>
                        <td>
                            <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                            <a href="?delete=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm btn-delete" data-name="<?php echo e($item['name']); ?>"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
