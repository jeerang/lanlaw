<?php
/**
 * Users List
 * ระบบงานเอกสารสำนักงานทนายความ
 */

require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

requireAdmin();

$pageTitle = 'จัดการผู้ใช้งาน';

// Handle delete
if (isset($_GET['delete'])) {
    $result = deleteUser($_GET['delete']);
    setFlashMessage($result['success'] ? 'success' : 'danger', $result['message']);
    redirect(BASE_URL . 'admin/users/');
}

// Get users
$users = getAll('users', '', [], 'created_at DESC');

include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-users me-2"></i>รายการผู้ใช้งาน</span>
        <a href="add.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>เพิ่มผู้ใช้งาน
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th>ชื่อผู้ใช้</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>อีเมล</th>
                        <th>สิทธิ์</th>
                        <th>สถานะ</th>
                        <th>เข้าสู่ระบบล่าสุด</th>
                        <th width="15%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    global $STATUS_LABELS, $ROLE_LABELS;
                    foreach ($users as $index => $user): 
                        $statusLabel = $STATUS_LABELS[$user['status']] ?? ['text' => $user['status'], 'class' => 'secondary'];
                        $roleLabel = $ROLE_LABELS[$user['role']] ?? ['text' => $user['role'], 'class' => 'secondary'];
                    ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo e($user['username']); ?></td>
                        <td><?php echo e($user['fullname']); ?></td>
                        <td><?php echo e($user['email'] ?? '-'); ?></td>
                        <td><span class="badge bg-<?php echo $roleLabel['class']; ?>"><?php echo $roleLabel['text']; ?></span></td>
                        <td><span class="badge bg-<?php echo $statusLabel['class']; ?>"><?php echo $statusLabel['text']; ?></span></td>
                        <td><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : '-'; ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm" title="แก้ไข">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm btn-delete" 
                               data-name="<?php echo e($user['fullname']); ?>" title="ลบ">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
