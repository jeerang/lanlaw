<?php
/**
 * View Disbursement
 */
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$id = $_GET['id'] ?? 0;
$db = getDB();

$stmt = $db->prepare("
    SELECT d.*, c.debtor_code, c.debtor_name, ct.name as court_name, u.fullname as created_by_name
    FROM disbursements d
    LEFT JOIN cases c ON d.case_id = c.id
    LEFT JOIN courts ct ON d.court_id = ct.id
    LEFT JOIN users u ON d.created_by = u.id
    WHERE d.id = ?
");
$stmt->execute([$id]);
$disbursement = $stmt->fetch();

if (!$disbursement) { setFlashMessage('danger', 'ไม่พบข้อมูล'); redirect(BASE_URL . 'disbursements/'); }

// Get items
$stmtItems = $db->prepare("
    SELECT di.*, dt.name as type_name 
    FROM disbursement_items di
    LEFT JOIN disbursement_types dt ON di.disbursement_type_id = dt.id
    WHERE di.disbursement_id = ?
");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll();

$pageTitle = 'ใบเบิก: ' . $disbursement['disbursement_number'];
global $DISBURSEMENT_STATUSES;
$statusClass = ['pending'=>'warning','processing'=>'info','paid'=>'success','rejected'=>'danger'][$disbursement['status']] ?? 'secondary';
$statusText = $DISBURSEMENT_STATUSES[$disbursement['status']] ?? $disbursement['status'];

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-file-invoice-dollar me-2"></i><?php echo $pageTitle; ?></span>
        <div>
            <?php if ($disbursement['status'] == 'pending'): ?>
            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i>แก้ไข</a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>disbursements/" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>กลับ</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><th width="35%">เลขที่ใบเบิก:</th><td><strong><?php echo e($disbursement['disbursement_number']); ?></strong></td></tr>
                    <tr><th>วันที่:</th><td><?php echo formatDate($disbursement['disbursement_date']); ?></td></tr>
                    <tr><th>รหัสลูกหนี้:</th><td><?php echo e($disbursement['debtor_code'] ?? '-'); ?></td></tr>
                    <tr><th>ชื่อลูกหนี้:</th><td><?php echo e($disbursement['debtor_name'] ?? '-'); ?></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><th width="35%">ศาล:</th><td><?php echo e($disbursement['court_name'] ?? '-'); ?></td></tr>
                    <tr><th>คดีดำ:</th><td><?php echo e($disbursement['black_case'] ?? '-'); ?></td></tr>
                    <tr><th>สถานะ:</th><td><span class="badge bg-<?php echo $statusClass; ?> fs-6"><?php echo $statusText; ?></span></td></tr>
                    <tr><th>ผู้สร้าง:</th><td><?php echo e($disbursement['created_by_name'] ?? '-'); ?></td></tr>
                </table>
            </div>
        </div>
        
        <h6 class="text-primary"><i class="fas fa-list me-2"></i>รายการเบิก</h6>
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th width="40">#</th>
                    <th>ประเภทค่าใช้จ่าย</th>
                    <th>รายละเอียด</th>
                    <th width="120" class="text-end">จำนวนเงิน</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $item): ?>
                <tr>
                    <td class="text-center"><?php echo $i+1; ?></td>
                    <td><?php echo e($item['type_name']); ?></td>
                    <td><?php echo e($item['description'] ?? '-'); ?></td>
                    <td class="text-end"><?php echo number_format($item['amount'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-light">
                    <td colspan="3" class="text-end"><strong>รวมทั้งสิ้น:</strong></td>
                    <td class="text-end"><strong class="text-primary fs-5"><?php echo number_format($disbursement['total_amount'], 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
        
        <?php if ($disbursement['remarks']): ?>
        <h6 class="text-primary mt-4"><i class="fas fa-sticky-note me-2"></i>หมายเหตุ</h6>
        <div class="alert alert-secondary"><?php echo nl2br(e($disbursement['remarks'])); ?></div>
        <?php endif; ?>
        
        <?php if (isAdmin()): ?>
        <hr>
        <h6 class="text-primary"><i class="fas fa-cog me-2"></i>จัดการสถานะ</h6>
        <div class="btn-group">
            <?php if ($disbursement['status'] == 'pending'): ?>
            <a href="update_status.php?id=<?php echo $id; ?>&status=processing" class="btn btn-info"><i class="fas fa-check me-1"></i>อนุมัติ</a>
            <a href="update_status.php?id=<?php echo $id; ?>&status=rejected" class="btn btn-danger"><i class="fas fa-times me-1"></i>ปฏิเสธ</a>
            <?php elseif ($disbursement['status'] == 'processing'): ?>
            <a href="update_status.php?id=<?php echo $id; ?>&status=paid" class="btn btn-success"><i class="fas fa-money-bill me-1"></i>จ่ายแล้ว</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
