<?php
/**
 * Edit Case
 */
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$id = $_GET['id'] ?? 0;
$case = getById('cases', $id);
if (!$case) { setFlashMessage('danger', 'ไม่พบข้อมูล'); redirect(BASE_URL . 'cases/'); }

$pageTitle = 'แก้ไขคดี';
$errors = [];

$workTypes = getActiveForDropdown('work_types');
$offices = getActiveForDropdown('offices');
$courts = getActiveForDropdown('courts');
$lawyers = getLawyersForDropdown();
$aos = getActiveForDropdown('account_officers');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    $data = [
        'debtor_code' => sanitize($_POST['debtor_code'] ?? ''),
        'debtor_name' => sanitize($_POST['debtor_name'] ?? ''),
        'port' => sanitize($_POST['port'] ?? ''),
        'work_type_id' => $_POST['work_type_id'] ?: null,
        'office_id' => $_POST['office_id'] ?: null,
        'due_date' => $_POST['due_date'] ?: null,
        'received_date' => $_POST['received_date'] ?: null,
        'filing_date' => $_POST['filing_date'] ?: null,
        'judgment_date' => $_POST['judgment_date'] ?: null,
        'court_id' => $_POST['court_id'] ?: null,
        'black_case' => sanitize($_POST['black_case'] ?? ''),
        'red_case' => sanitize($_POST['red_case'] ?? ''),
        'current_action_date' => $_POST['current_action_date'] ?: null,
        'current_action' => sanitize($_POST['current_action'] ?? ''),
        'next_action_date' => $_POST['next_action_date'] ?: null,
        'next_action' => sanitize($_POST['next_action'] ?? ''),
        'lawyer_id' => $_POST['lawyer_id'] ?: null,
        'ao_id' => $_POST['ao_id'] ?: null,
        'problems_remarks' => sanitize($_POST['problems_remarks'] ?? ''),
        'status' => $_POST['status'] ?? 'active'
    ];
    
    $errors = validateRequired($data, ['debtor_code' => 'รหัสลูกหนี้', 'debtor_name' => 'ชื่อลูกหนี้']);
    
    if (empty($errors)) {
        try {
            $db = getDB();
            $sets = [];
            foreach ($data as $key => $value) { $sets[] = "$key = ?"; }
            $stmt = $db->prepare("UPDATE cases SET " . implode(', ', $sets) . " WHERE id = ?");
            $values = array_values($data);
            $values[] = $id;
            $stmt->execute($values);
            logActivity('update', 'cases', $id, $case, $data);
            setFlashMessage('success', 'แก้ไขคดีสำเร็จ');
            redirect(BASE_URL . 'cases/');
        } catch (PDOException $e) {
            $errors['general'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
} else {
    $_POST = $case;
}

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header"><i class="fas fa-edit me-2"></i><?php echo $pageTitle; ?></div>
    <div class="card-body">
        <?php if (isset($errors['general'])): ?><div class="alert alert-danger"><?php echo e($errors['general']); ?></div><?php endif; ?>
        
        <form method="POST">
            <?php echo csrfField(); ?>
            <h6 class="text-primary mb-3"><i class="fas fa-user me-2"></i>ข้อมูลลูกหนี้</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">รหัสลูกหนี้ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="debtor_code" value="<?php echo e($_POST['debtor_code'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">ชื่อลูกหนี้ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="debtor_name" value="<?php echo e($_POST['debtor_name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">PORT</label>
                    <input type="text" class="form-control" name="port" value="<?php echo e($_POST['port'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">ประเภทงาน</label>
                    <select class="form-select select2" name="work_type_id">
                        <option value="">-- เลือก --</option>
                        <?php foreach ($workTypes as $wt): ?>
                        <option value="<?php echo $wt['value']; ?>" <?php echo ($_POST['work_type_id'] ?? '') == $wt['value'] ? 'selected' : ''; ?>><?php echo e($wt['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">สำนักงานเจ้าของงาน</label>
                    <select class="form-select select2" name="office_id">
                        <option value="">-- เลือก --</option>
                        <?php foreach ($offices as $o): ?>
                        <option value="<?php echo $o['value']; ?>" <?php echo ($_POST['office_id'] ?? '') == $o['value'] ? 'selected' : ''; ?>><?php echo e($o['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">ศาล</label>
                    <select class="form-select select2" name="court_id">
                        <option value="">-- เลือก --</option>
                        <?php foreach ($courts as $c): ?>
                        <option value="<?php echo $c['value']; ?>" <?php echo ($_POST['court_id'] ?? '') == $c['value'] ? 'selected' : ''; ?>><?php echo e($c['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <hr>
            <h6 class="text-primary mb-3"><i class="fas fa-calendar me-2"></i>วันที่สำคัญ</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">ให้ดำเนินการภายในวันที่</label>
                    <input type="date" class="form-control" name="due_date" value="<?php echo e($_POST['due_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">วันรับเรื่อง</label>
                    <input type="date" class="form-control" name="received_date" value="<?php echo e($_POST['received_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">วันฟ้อง</label>
                    <input type="date" class="form-control" name="filing_date" value="<?php echo e($_POST['filing_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">วันพิพากษา</label>
                    <input type="date" class="form-control" name="judgment_date" value="<?php echo e($_POST['judgment_date'] ?? ''); ?>">
                </div>
            </div>
            
            <hr>
            <h6 class="text-primary mb-3"><i class="fas fa-gavel me-2"></i>ข้อมูลคดี</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">คดีดำ</label>
                    <input type="text" class="form-control" name="black_case" value="<?php echo e($_POST['black_case'] ?? ''); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">คดีแดง</label>
                    <input type="text" class="form-control" name="red_case" value="<?php echo e($_POST['red_case'] ?? ''); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">ทนายความ</label>
                    <select class="form-select select2" name="lawyer_id">
                        <option value="">-- เลือก --</option>
                        <?php foreach ($lawyers as $l): ?>
                        <option value="<?php echo $l['value']; ?>" <?php echo ($_POST['lawyer_id'] ?? '') == $l['value'] ? 'selected' : ''; ?>><?php echo e($l['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">A/O</label>
                    <select class="form-select select2" name="ao_id">
                        <option value="">-- เลือก --</option>
                        <?php foreach ($aos as $ao): ?>
                        <option value="<?php echo $ao['value']; ?>" <?php echo ($_POST['ao_id'] ?? '') == $ao['value'] ? 'selected' : ''; ?>><?php echo e($ao['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <hr>
            <h6 class="text-primary mb-3"><i class="fas fa-tasks me-2"></i>การดำเนินการ</h6>
            <div class="row">
                <div class="col-md-2 mb-3">
                    <label class="form-label">วันที่ดำเนินการปัจจุบัน</label>
                    <input type="date" class="form-control" name="current_action_date" value="<?php echo e($_POST['current_action_date'] ?? ''); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">การดำเนินการปัจจุบัน</label>
                    <textarea class="form-control" name="current_action" rows="2"><?php echo e($_POST['current_action'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">วันที่ดำเนินการต่อไป</label>
                    <input type="date" class="form-control" name="next_action_date" value="<?php echo e($_POST['next_action_date'] ?? ''); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">การดำเนินการต่อไป</label>
                    <textarea class="form-control" name="next_action" rows="2"><?php echo e($_POST['next_action'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-9 mb-3">
                    <label class="form-label">ปัญหาอุปสรรค/หมายเหตุ</label>
                    <textarea class="form-control" name="problems_remarks" rows="2"><?php echo e($_POST['problems_remarks'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">สถานะ</label>
                    <select class="form-select" name="status">
                        <option value="active" <?php echo ($_POST['status'] ?? '') == 'active' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                        <option value="pending" <?php echo ($_POST['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                        <option value="closed" <?php echo ($_POST['status'] ?? '') == 'closed' ? 'selected' : ''; ?>>ปิดคดี</option>
                    </select>
                </div>
            </div>
            
            <hr>
            <div class="d-flex justify-content-between">
                <a href="<?php echo BASE_URL; ?>cases/" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>กลับ</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>บันทึก</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
