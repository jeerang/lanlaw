<?php
/**
 * Edit Disbursement
 */
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$id = $_GET['id'] ?? 0;
$db = getDB();

$stmt = $db->prepare("SELECT * FROM disbursements WHERE id = ?");
$stmt->execute([$id]);
$disbursement = $stmt->fetch();

if (!$disbursement) { setFlashMessage('danger', 'ไม่พบข้อมูล'); redirect(BASE_URL . 'disbursements/'); }
if ($disbursement['status'] != 'pending') { setFlashMessage('danger', 'ไม่สามารถแก้ไขใบเบิกที่อนุมัติแล้ว'); redirect(BASE_URL . 'disbursements/'); }

// Get case info
$case = null;
if ($disbursement['case_id']) {
    $case = getCaseForDisbursement($disbursement['case_id']);
}

// Get existing items
$stmtItems = $db->prepare("SELECT * FROM disbursement_items WHERE disbursement_id = ?");
$stmtItems->execute([$id]);
$existingItems = $stmtItems->fetchAll();

$pageTitle = 'แก้ไขใบเบิก: ' . $disbursement['disbursement_number'];
$errors = [];

$disbursementTypes = getActiveForDropdown('disbursement_types');
$courts = getActiveForDropdown('courts');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    $caseId = $_POST['case_id'] ?: null;
    $disbursementDate = $_POST['disbursement_date'] ?? date('Y-m-d');
    $courtId = $_POST['court_id'] ?: null;
    $blackCase = sanitize($_POST['black_case'] ?? '');
    $remarks = sanitize($_POST['remarks'] ?? '');
    
    $items = [];
    $totalAmount = 0;
    if (!empty($_POST['type_id'])) {
        foreach ($_POST['type_id'] as $i => $typeId) {
            if (!empty($typeId) && !empty($_POST['amount'][$i])) {
                $amt = floatval($_POST['amount'][$i]);
                $items[] = [
                    'disbursement_type_id' => $typeId,
                    'description' => sanitize($_POST['description'][$i] ?? ''),
                    'amount' => $amt
                ];
                $totalAmount += $amt;
            }
        }
    }
    
    if (empty($items)) {
        $errors['general'] = 'กรุณาเพิ่มรายการเบิกอย่างน้อย 1 รายการ';
    }
    
    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            $stmt = $db->prepare("UPDATE disbursements SET case_id=?, disbursement_date=?, court_id=?, black_case=?, total_amount=?, remarks=? WHERE id=?");
            $stmt->execute([$caseId, $disbursementDate, $courtId, $blackCase, $totalAmount, $remarks, $id]);
            
            // Delete old items and insert new
            $db->prepare("DELETE FROM disbursement_items WHERE disbursement_id = ?")->execute([$id]);
            
            $stmtItem = $db->prepare("INSERT INTO disbursement_items (disbursement_id, disbursement_type_id, description, amount) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmtItem->execute([$id, $item['disbursement_type_id'], $item['description'], $item['amount']]);
            }
            
            $db->commit();
            logActivity('update', 'disbursements', $id);
            setFlashMessage('success', 'แก้ไขใบเบิกสำเร็จ');
            redirect(BASE_URL . 'disbursements/');
        } catch (PDOException $e) {
            $db->rollBack();
            $errors['general'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header"><i class="fas fa-edit me-2"></i><?php echo $pageTitle; ?></div>
    <div class="card-body">
        <?php if (isset($errors['general'])): ?><div class="alert alert-danger"><?php echo e($errors['general']); ?></div><?php endif; ?>
        
        <form method="POST" id="disbursementForm">
            <?php echo csrfField(); ?>
            <h6 class="text-primary mb-3"><i class="fas fa-search me-2"></i>ข้อมูลลูกหนี้</h6>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">ค้นหาลูกหนี้ (พิมพ์รหัส/ชื่อ)</label>
                    <select class="form-select" id="caseSearch" name="case_id" style="width:100%">
                        <?php if ($case): ?>
                        <option value="<?php echo $case['id']; ?>" selected><?php echo e($case['debtor_code'] . ' - ' . $case['debtor_name']); ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">วันที่ใบเบิก</label>
                    <input type="date" class="form-control" name="disbursement_date" value="<?php echo e($disbursement['disbursement_date']); ?>" required>
                </div>
            </div>
            
            <div class="row" id="caseInfo" <?php echo $case ? '' : 'style="display:none;"'; ?>>
                <div class="col-md-3 mb-3">
                    <label class="form-label">รหัสลูกหนี้</label>
                    <input type="text" class="form-control" id="debtor_code" readonly value="<?php echo e($case['debtor_code'] ?? ''); ?>">
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">ชื่อลูกหนี้</label>
                    <input type="text" class="form-control" id="debtor_name" readonly value="<?php echo e($case['debtor_name'] ?? ''); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">A/O</label>
                    <input type="text" class="form-control" id="ao_name" readonly value="<?php echo e($case['ao_name'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="row" id="caseInfo2" <?php echo $case ? '' : 'style="display:none;"'; ?>>
                <div class="col-md-4 mb-3">
                    <label class="form-label">ศาล</label>
                    <select class="form-select select2" name="court_id" id="court_id">
                        <option value="">-- เลือก --</option>
                        <?php foreach ($courts as $c): ?>
                        <option value="<?php echo $c['value']; ?>" <?php echo ($disbursement['court_id'] ?? '') == $c['value'] ? 'selected' : ''; ?>><?php echo e($c['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">คดีดำ</label>
                    <input type="text" class="form-control" name="black_case" id="black_case" value="<?php echo e($disbursement['black_case'] ?? $case['black_case'] ?? ''); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">คดีแดง</label>
                    <input type="text" class="form-control" id="red_case" readonly value="<?php echo e($case['red_case'] ?? ''); ?>">
                </div>
            </div>
            
            <hr>
            <h6 class="text-primary mb-3"><i class="fas fa-list me-2"></i>รายการเบิก</h6>
            
            <table class="table table-bordered" id="itemsTable">
                <thead class="table-light">
                    <tr>
                        <th width="30">#</th>
                        <th width="35%">ประเภทค่าใช้จ่าย</th>
                        <th width="35%">รายละเอียด</th>
                        <th width="15%">จำนวนเงิน</th>
                        <th width="50"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <?php foreach ($existingItems as $i => $item): ?>
                    <tr class="item-row" data-row="<?php echo $i+1; ?>">
                        <td class="text-center row-number"><?php echo $i+1; ?></td>
                        <td>
                            <select class="form-select form-select-sm" name="type_id[]" required>
                                <option value="">-- เลือก --</option>
                                <?php foreach ($disbursementTypes as $dt): ?>
                                <option value="<?php echo $dt['value']; ?>" <?php echo $item['disbursement_type_id'] == $dt['value'] ? 'selected' : ''; ?>><?php echo e($dt['text']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" class="form-control form-control-sm" name="description[]" value="<?php echo e($item['description']); ?>"></td>
                        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm text-end item-amount" name="amount[]" value="<?php echo $item['amount']; ?>" required></td>
                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fas fa-times"></i></button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>รวมทั้งสิ้น:</strong></td>
                        <td class="text-end"><strong id="totalAmount"><?php echo number_format($disbursement['total_amount'], 2); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            
            <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="addRowBtn"><i class="fas fa-plus me-1"></i>เพิ่มรายการ</button>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">หมายเหตุ</label>
                    <textarea class="form-control" name="remarks" rows="2"><?php echo e($disbursement['remarks']); ?></textarea>
                </div>
            </div>
            
            <hr>
            <div class="d-flex justify-content-between">
                <a href="<?php echo BASE_URL; ?>disbursements/" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>กลับ</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>บันทึกใบเบิก</button>
            </div>
        </form>
    </div>
</div>

<script>
var disbursementTypes = <?php echo json_encode($disbursementTypes); ?>;
var rowCount = <?php echo count($existingItems); ?>;

$(document).ready(function() {
    $('#caseSearch').select2({
        placeholder: 'พิมพ์รหัสหรือชื่อลูกหนี้...',
        minimumInputLength: 2,
        ajax: {
            url: '<?php echo BASE_URL; ?>cases/ajax/search.php',
            dataType: 'json',
            delay: 300,
            data: function(params) { return { q: params.term }; },
            processResults: function(data) { return data; }
        }
    }).on('select2:select', function(e) {
        var d = e.params.data;
        $('#debtor_code').val(d.debtor_code);
        $('#debtor_name').val(d.debtor_name);
        $('#ao_name').val(d.ao_name || '-');
        $('#black_case').val(d.black_case);
        $('#red_case').val(d.red_case);
        if (d.court_id) $('#court_id').val(d.court_id).trigger('change');
        $('#caseInfo, #caseInfo2').slideDown();
    });
    
    $('#addRowBtn').click(function() {
        rowCount++;
        var options = '<option value="">-- เลือก --</option>';
        disbursementTypes.forEach(function(dt) { options += '<option value="' + dt.value + '">' + dt.text + '</option>'; });
        var row = `<tr class="item-row" data-row="${rowCount}">
            <td class="text-center row-number">${rowCount}</td>
            <td><select class="form-select form-select-sm" name="type_id[]" required>${options}</select></td>
            <td><input type="text" class="form-control form-control-sm" name="description[]"></td>
            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm text-end item-amount" name="amount[]" required></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fas fa-times"></i></button></td>
        </tr>`;
        $('#itemsBody').append(row);
        reNumberRows();
    });
    
    $(document).on('click', '.btn-remove-row', function() {
        if ($('#itemsBody tr').length > 1) { $(this).closest('tr').remove(); reNumberRows(); calcTotal(); }
    });
    
    $(document).on('input', '.item-amount', function() { calcTotal(); });
    
    function reNumberRows() { $('#itemsBody tr').each(function(i) { $(this).find('.row-number').text(i + 1); }); }
    function calcTotal() { var t = 0; $('.item-amount').each(function() { t += parseFloat($(this).val()) || 0; }); $('#totalAmount').text(t.toFixed(2)); }
    
    calcTotal();
});
</script>

<?php include '../includes/footer.php'; ?>
