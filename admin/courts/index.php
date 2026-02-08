<?php
/**
 * Courts List
 */
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireAdmin();

$pageTitle = 'จัดการศาล';
$tableName = 'courts';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db = getDB();
    $oldData = getById($tableName, $id);
    $stmt = $db->prepare("DELETE FROM {$tableName} WHERE id = ?");
    $stmt->execute([$id]);
    logActivity('delete', $tableName, $id, $oldData);
    setFlashMessage('success', 'ลบข้อมูลสำเร็จ');
    redirect(BASE_URL . 'admin/courts/');
}

$items = getAll($tableName, '', [], 'name ASC');
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-gavel me-2"></i>รายการศาล</span>
        <a href="add.php" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>เพิ่มศาล</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th>รหัส</th>
                        <th>ชื่อศาล</th>
                        <th>จังหวัด</th>
                        <th>สถานะ</th>
                        <th width="15%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    global $STATUS_LABELS;
                    foreach ($items as $index => $item): 
                        $statusLabel = $STATUS_LABELS[$item['status']] ?? ['text' => $item['status'], 'class' => 'secondary'];
                    ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><code><?php echo e($item['code']); ?></code></td>
                        <td><?php echo e($item['name']); ?></td>
                        <td><?php echo e($item['province'] ?? '-'); ?></td>
                        <td><span class="badge bg-<?php echo $statusLabel['class']; ?>"><?php echo $statusLabel['text']; ?></span></td>
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
