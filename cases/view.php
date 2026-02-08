<?php
/**
 * View Case
 */
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$id = $_GET['id'] ?? 0;
$db = getDB();
$stmt = $db->prepare("
    SELECT c.*, wt.name as work_type_name, o.name as office_name, ct.name as court_name,
           CONCAT(l.prefix, l.firstname, ' ', l.lastname) as lawyer_name, ao.name as ao_name
    FROM cases c
    LEFT JOIN work_types wt ON c.work_type_id = wt.id
    LEFT JOIN offices o ON c.office_id = o.id
    LEFT JOIN courts ct ON c.court_id = ct.id
    LEFT JOIN lawyers l ON c.lawyer_id = l.id
    LEFT JOIN account_officers ao ON c.ao_id = ao.id
    WHERE c.id = ?
");
$stmt->execute([$id]);
$case = $stmt->fetch();

if (!$case) { setFlashMessage('danger', 'ไม่พบข้อมูล'); redirect(BASE_URL . 'cases/'); }

$pageTitle = 'รายละเอียดคดี';
global $STATUS_LABELS;
$sl = $STATUS_LABELS[$case['status']] ?? ['text'=>$case['status'],'class'=>'secondary'];

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-folder-open me-2"></i><?php echo $pageTitle; ?></span>
        <div>
            <a href="edit.php?id=<?php echo $case['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit me-1"></i>แก้ไข</a>
            <a href="<?php echo BASE_URL; ?>cases/" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>กลับ</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><th width="35%">รหัสลูกหนี้:</th><td><strong><?php echo e($case['debtor_code']); ?></strong></td></tr>
                    <tr><th>ชื่อลูกหนี้:</th><td><?php echo e($case['debtor_name']); ?></td></tr>
                    <tr><th>PORT:</th><td><code><?php echo e($case['port'] ?? '-'); ?></code></td></tr>
                    <tr><th>ประเภทงาน:</th><td><?php echo e($case['work_type_name'] ?? '-'); ?></td></tr>
                    <tr><th>สำนักงาน:</th><td><?php echo e($case['office_name'] ?? '-'); ?></td></tr>
                    <tr><th>ศาล:</th><td><?php echo e($case['court_name'] ?? '-'); ?></td></tr>
                    <tr><th>คดีดำ:</th><td><?php echo e($case['black_case'] ?? '-'); ?></td></tr>
                    <tr><th>คดีแดง:</th><td><?php echo e($case['red_case'] ?? '-'); ?></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><th width="35%">ครบกำหนด:</th><td><?php echo formatDate($case['due_date']); ?></td></tr>
                    <tr><th>วันรับเรื่อง:</th><td><?php echo formatDate($case['received_date']); ?></td></tr>
                    <tr><th>วันฟ้อง:</th><td><?php echo formatDate($case['filing_date']); ?></td></tr>
                    <tr><th>วันพิพากษา:</th><td><?php echo formatDate($case['judgment_date']); ?></td></tr>
                    <tr><th>ทนายความ:</th><td><?php echo e($case['lawyer_name'] ?? '-'); ?></td></tr>
                    <tr><th>A/O:</th><td><?php echo e($case['ao_name'] ?? '-'); ?></td></tr>
                    <tr><th>สถานะ:</th><td><span class="badge bg-<?php echo $sl['class']; ?>"><?php echo $sl['text']; ?></span></td></tr>
                </table>
            </div>
        </div>
        
        <hr>
        <h6><i class="fas fa-tasks me-2"></i>การดำเนินการ</h6>
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">การดำเนินการปัจจุบัน</h6>
                        <p class="mb-1"><strong>วันที่:</strong> <?php echo formatDate($case['current_action_date']); ?></p>
                        <p class="mb-0"><?php echo nl2br(e($case['current_action'] ?? '-')); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">การดำเนินการต่อไป</h6>
                        <p class="mb-1"><strong>วันที่:</strong> <?php echo formatDate($case['next_action_date']); ?></p>
                        <p class="mb-0"><?php echo nl2br(e($case['next_action'] ?? '-')); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($case['problems_remarks']): ?>
        <hr>
        <h6><i class="fas fa-exclamation-triangle me-2"></i>ปัญหาอุปสรรค/หมายเหตุ</h6>
        <div class="alert alert-warning"><?php echo nl2br(e($case['problems_remarks'])); ?></div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
