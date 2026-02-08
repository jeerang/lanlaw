<?php
/**
 * Disbursements List
 */
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$pageTitle = 'ใบเบิกค่าใช้จ่าย';

if (isset($_GET['delete'])) {
    $db = getDB();
    $oldData = getById('disbursements', $_GET['delete']);
    $db->prepare("DELETE FROM disbursement_items WHERE disbursement_id = ?")->execute([$_GET['delete']]);
    $db->prepare("DELETE FROM disbursements WHERE id = ?")->execute([$_GET['delete']]);
    logActivity('delete', 'disbursements', $_GET['delete'], $oldData);
    setFlashMessage('success', 'ลบใบเบิกสำเร็จ');
    redirect(BASE_URL . 'disbursements/');
}

$db = getDB();

// แสดงใบเบิกทั้งหมด
$stmt = $db->query("
    SELECT d.*, c.debtor_code, c.debtor_name, c.black_case, c.red_case,
           ct.name as court_name, u.fullname as created_by_name
    FROM disbursements d
    LEFT JOIN cases c ON d.case_id = c.id
    LEFT JOIN courts ct ON d.court_id = ct.id
    LEFT JOIN users u ON d.created_by = u.id
    ORDER BY d.disbursement_date DESC, d.created_at DESC
");
$disbursements = $stmt->fetchAll();

global $DISBURSEMENT_STATUSES;

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-file-invoice-dollar me-2"></i>รายการใบเบิกค่าใช้จ่าย</span>
        <a href="add.php" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>สร้างใบเบิกใหม่</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm datatable" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>เลขที่ใบเบิก</th>
                        <th>วันที่</th>
                        <th>รหัสลูกหนี้</th>
                        <th>ชื่อลูกหนี้</th>
                        <th>คดีดำ</th>
                        <th>ศาล</th>
                        <th class="text-end">ยอดรวม</th>
                        <th>สถานะ</th>
                        <th class="text-center">ตรวจสอบ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($disbursements as $i => $d): 
                        $statusClass = ['pending'=>'warning','processing'=>'info','paid'=>'success','rejected'=>'danger'][$d['status']] ?? 'secondary';
                        $statusText = $DISBURSEMENT_STATUSES[$d['status']] ?? $d['status'];
                    ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><a href="view.php?id=<?php echo $d['id']; ?>"><?php echo e($d['disbursement_number']); ?></a></td>
                        <td><?php echo formatDate($d['disbursement_date']); ?></td>
                        <td><?php echo e($d['debtor_code'] ?? '-'); ?></td>
                        <td><?php echo e(mb_substr($d['debtor_name'] ?? '-', 0, 25)); ?></td>
                        <td><?php echo e($d['black_case'] ?? '-'); ?></td>
                        <td><?php echo e($d['court_name'] ?? '-'); ?></td>
                        <td class="text-end"><strong><?php echo number_format($d['total_amount'], 2); ?></strong></td>
                        <td><span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                        <td class="text-center">
                            <a href="view.php?id=<?php echo $d['id']; ?>" class="btn btn-outline-primary btn-sm" title="ดูรายละเอียด">
                                <i class="fas fa-search me-1"></i>ตรวจสอบ
                            </a>
                            <?php if ($d['status'] == 'pending'): ?>
                            <a href="edit.php?id=<?php echo $d['id']; ?>" class="btn btn-warning btn-sm" title="แก้ไข">
                                <i class="fas fa-edit me-1"></i>แก้ไข
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

<?php include '../includes/footer.php'; ?>
