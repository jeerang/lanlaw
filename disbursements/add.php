<?php
/**
 * Add Disbursement
 */
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$pageTitle = 'สร้างใบเบิกค่าใช้จ่ายใหม่';
$errors = [];

$disbursementTypes = getActiveForDropdown('disbursement_types');
$courts = getActiveForDropdown('courts');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    $db = getDB();
    
    $caseId = $_POST['case_id'] ?: null;
    $disbursementDate = $_POST['disbursement_date'] ?? date('Y-m-d');
    $courtId = $_POST['court_id'] ?: null;
    $blackCase = sanitize($_POST['black_case'] ?? '');
    $remarks = sanitize($_POST['remarks'] ?? '');
    
    // Get items
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
            
            $disbursementNumber = generateDisbursementNumber($caseId);
            
            $stmt = $db->prepare("INSERT INTO disbursements (disbursement_number, case_id, disbursement_date, court_id, black_case, total_amount, remarks, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$disbursementNumber, $caseId, $disbursementDate, $courtId, $blackCase, $totalAmount, $remarks, $_SESSION['user_id']]);
            $disbursementId = $db->lastInsertId();
            
            // Insert items
            $stmtItem = $db->prepare("INSERT INTO disbursement_items (disbursement_id, disbursement_type_id, description, amount) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmtItem->execute([$disbursementId, $item['disbursement_type_id'], $item['description'], $item['amount']]);
            }
            
            $db->commit();
            logActivity('create', 'disbursements', $disbursementId);
            setFlashMessage('success', 'สร้างใบเบิกสำเร็จ เลขที่: ' . $disbursementNumber);
            redirect(BASE_URL . 'disbursements/');
        } catch (PDOException $e) {
            $db->rollBack();
            $errors['general'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<style>
    .item-row { background: #f8f9fa; }
    .item-row:nth-child(odd) { background: #fff; }
</style>

<div class="card">
    <div class="card-header"><i class="fas fa-plus-circle me-2"></i><?php echo $pageTitle; ?></div>
    <div class="card-body">
        <?php if (isset($errors['general'])): ?><div class="alert alert-danger"><?php echo e($errors['general']); ?></div><?php endif; ?>
        
        <form method="POST" id="disbursementForm">
            <?php echo csrfField(); ?>
            <h6 class="text-primary mb-3"><i class="fas fa-search me-2"></i>ค้นหาข้อมูลลูกหนี้</h6>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">ค้นหาลูกหนี้ (พิมพ์รหัส/ชื่อ)</label>
                    <select class="form-select" id="caseSearch" name="case_id" style="width:100%"></select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">วันที่ใบเบิก</label>
                    <input type="date" class="form-control" name="disbursement_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            
            <div class="row" id="caseInfo" style="display:none;">
                <div class="col-md-3 mb-3">
                    <label class="form-label">รหัสลูกหนี้</label>
                    <input type="text" class="form-control" id="debtor_code" readonly>
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">ชื่อลูกหนี้</label>
                    <input type="text" class="form-control" id="debtor_name" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">A/O</label>
                    <input type="text" class="form-control" id="ao_name" readonly>
                </div>
            </div>
            
            <div class="row" id="caseInfo2" style="display:none;">
                <div class="col-md-4 mb-3">
                    <label class="form-label">ศาล</label>
                    <select class="form-select select2" name="court_id" id="court_id">
                        <option value="">-- เลือก --</option>
                        <?php foreach ($courts as $c): ?>
                        <option value="<?php echo $c['value']; ?>"><?php echo e($c['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">คดีดำ</label>
                    <input type="text" class="form-control" name="black_case" id="black_case">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">คดีแดง</label>
                    <input type="text" class="form-control" id="red_case" readonly>
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
                    <tr class="item-row" data-row="1">
                        <td class="text-center row-number">1</td>
                        <td>
                            <select class="form-select form-select-sm" name="type_id[]" required>
                                <option value="">-- เลือก --</option>
                                <?php foreach ($disbursementTypes as $dt): ?>
                                <option value="<?php echo $dt['value']; ?>"><?php echo e($dt['text']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" class="form-control form-control-sm" name="description[]" placeholder="รายละเอียดเพิ่มเติม"></td>
                        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm text-end item-amount" name="amount[]" required></td>
                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fas fa-times"></i></button></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>รวมทั้งสิ้น:</strong></td>
                        <td class="text-end"><strong id="totalAmount">0.00</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            
            <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="addRowBtn"><i class="fas fa-plus me-1"></i>เพิ่มรายการ</button>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">หมายเหตุ</label>
                    <textarea class="form-control" name="remarks" rows="2"></textarea>
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

<?php include '../includes/footer.php'; ?>

<script>
var disbursementTypes = <?php echo json_encode($disbursementTypes); ?>;
var rowCount = 1;
var searchUrl = '<?php echo BASE_URL; ?>cases/ajax/search.php';

$(document).ready(function() {
    // Search case with Select2
    $('#caseSearch').select2({
        theme: 'bootstrap-5',
        placeholder: 'พิมพ์รหัสหรือชื่อลูกหนี้...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: searchUrl,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(data) {
                console.log('Search results:', data);
                return { results: data.results || [] };
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        }
    }).on('select2:select', function(e) {
        var d = e.params.data;
        console.log('Selected:', d);
        $('#debtor_code').val(d.debtor_code);
        $('#debtor_name').val(d.debtor_name);
        $('#ao_name').val(d.ao_name || '-');
        $('#black_case').val(d.black_case);
        $('#red_case').val(d.red_case);
        if (d.court_id) $('#court_id').val(d.court_id).trigger('change');
        $('#caseInfo, #caseInfo2').slideDown();
    });
    
    // Add row
    $('#addRowBtn').click(function() {
        rowCount++;
        var options = '<option value="">-- เลือก --</option>';
        disbursementTypes.forEach(function(dt) {
            options += '<option value="' + dt.value + '">' + dt.text + '</option>';
        });
        var row = `<tr class="item-row" data-row="${rowCount}">
            <td class="text-center row-number">${rowCount}</td>
            <td><select class="form-select form-select-sm" name="type_id[]" required>${options}</select></td>
            <td><input type="text" class="form-control form-control-sm" name="description[]" placeholder="รายละเอียดเพิ่มเติม"></td>
            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm text-end item-amount" name="amount[]" required></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove-row"><i class="fas fa-times"></i></button></td>
        </tr>`;
        $('#itemsBody').append(row);
        reNumberRows();
    });
    
    // Remove row
    $(document).on('click', '.btn-remove-row', function() {
        if ($('#itemsBody tr').length > 1) {
            $(this).closest('tr').remove();
            reNumberRows();
            calcTotal();
        }
    });
    
    // Calculate total
    $(document).on('input', '.item-amount', function() {
        calcTotal();
    });
    
    function reNumberRows() {
        $('#itemsBody tr').each(function(i) {
            $(this).find('.row-number').text(i + 1);
        });
    }
    
    function calcTotal() {
        var total = 0;
        $('.item-amount').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        $('#totalAmount').text(total.toFixed(2));
    }
});
</script>
