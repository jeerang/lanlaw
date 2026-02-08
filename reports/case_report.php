<?php
/**
 * Case Report
 */
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$pageTitle = 'รายงานคดี';

$workTypes = getActiveForDropdown('work_types');
$courts = getActiveForDropdown('courts');
$lawyers = getLawyersForDropdown();
$aos = getActiveForDropdown('account_officers');
$offices = getActiveForDropdown('offices');

// Filter params
$filters = [
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'work_type_id' => $_GET['work_type_id'] ?? '',
    'court_id' => $_GET['court_id'] ?? '',
    'lawyer_id' => $_GET['lawyer_id'] ?? '',
    'ao_id' => $_GET['ao_id'] ?? '',
    'office_id' => $_GET['office_id'] ?? '',
    'status' => $_GET['status'] ?? ''
];

$cases = [];
$showReport = !empty($_GET);

if ($showReport) {
    $db = getDB();
    $sql = "SELECT c.*, wt.name as work_type_name, o.name as office_name, ct.name as court_name,
                   CONCAT(l.prefix, l.firstname, ' ', l.lastname) as lawyer_name, ao.name as ao_name
            FROM cases c
            LEFT JOIN work_types wt ON c.work_type_id = wt.id
            LEFT JOIN offices o ON c.office_id = o.id
            LEFT JOIN courts ct ON c.court_id = ct.id
            LEFT JOIN lawyers l ON c.lawyer_id = l.id
            LEFT JOIN account_officers ao ON c.ao_id = ao.id
            WHERE 1=1";
    $params = [];
    
    if ($filters['date_from']) { $sql .= " AND c.received_date >= ?"; $params[] = $filters['date_from']; }
    if ($filters['date_to']) { $sql .= " AND c.received_date <= ?"; $params[] = $filters['date_to']; }
    if ($filters['work_type_id']) { $sql .= " AND c.work_type_id = ?"; $params[] = $filters['work_type_id']; }
    if ($filters['court_id']) { $sql .= " AND c.court_id = ?"; $params[] = $filters['court_id']; }
    if ($filters['lawyer_id']) { $sql .= " AND c.lawyer_id = ?"; $params[] = $filters['lawyer_id']; }
    if ($filters['ao_id']) { $sql .= " AND c.ao_id = ?"; $params[] = $filters['ao_id']; }
    if ($filters['office_id']) { $sql .= " AND c.office_id = ?"; $params[] = $filters['office_id']; }
    if ($filters['status']) { $sql .= " AND c.status = ?"; $params[] = $filters['status']; }
    
    $sql .= " ORDER BY c.received_date DESC, c.debtor_code";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $cases = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<div class="card mb-4">
    <div class="card-header"><i class="fas fa-filter me-2"></i>ตัวกรองรายงาน</div>
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-2 mb-2">
                    <label class="form-label">วันที่รับเรื่อง (จาก)</label>
                    <input type="date" class="form-control form-control-sm" name="date_from" value="<?php echo e($filters['date_from']); ?>">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">ถึง</label>
                    <input type="date" class="form-control form-control-sm" name="date_to" value="<?php echo e($filters['date_to']); ?>">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">ประเภทงาน</label>
                    <select class="form-select form-select-sm" name="work_type_id">
                        <option value="">-- ทั้งหมด --</option>
                        <?php foreach ($workTypes as $wt): ?>
                        <option value="<?php echo $wt['value']; ?>" <?php echo $filters['work_type_id'] == $wt['value'] ? 'selected' : ''; ?>><?php echo e($wt['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">ศาล</label>
                    <select class="form-select form-select-sm" name="court_id">
                        <option value="">-- ทั้งหมด --</option>
                        <?php foreach ($courts as $c): ?>
                        <option value="<?php echo $c['value']; ?>" <?php echo $filters['court_id'] == $c['value'] ? 'selected' : ''; ?>><?php echo e($c['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">ทนายความ</label>
                    <select class="form-select form-select-sm" name="lawyer_id">
                        <option value="">-- ทั้งหมด --</option>
                        <?php foreach ($lawyers as $l): ?>
                        <option value="<?php echo $l['value']; ?>" <?php echo $filters['lawyer_id'] == $l['value'] ? 'selected' : ''; ?>><?php echo e($l['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">สถานะ</label>
                    <select class="form-select form-select-sm" name="status">
                        <option value="">-- ทั้งหมด --</option>
                        <option value="active" <?php echo $filters['status'] == 'active' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                        <option value="pending" <?php echo $filters['status'] == 'pending' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                        <option value="closed" <?php echo $filters['status'] == 'closed' ? 'selected' : ''; ?>>ปิดคดี</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2 mb-2">
                    <label class="form-label">A/O</label>
                    <select class="form-select form-select-sm" name="ao_id">
                        <option value="">-- ทั้งหมด --</option>
                        <?php foreach ($aos as $ao): ?>
                        <option value="<?php echo $ao['value']; ?>" <?php echo $filters['ao_id'] == $ao['value'] ? 'selected' : ''; ?>><?php echo e($ao['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">สำนักงาน</label>
                    <select class="form-select form-select-sm" name="office_id">
                        <option value="">-- ทั้งหมด --</option>
                        <?php foreach ($offices as $o): ?>
                        <option value="<?php echo $o['value']; ?>" <?php echo $filters['office_id'] == $o['value'] ? 'selected' : ''; ?>><?php echo e($o['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i>แสดงรายงาน</button>
                <a href="case_report.php" class="btn btn-secondary btn-sm"><i class="fas fa-redo me-1"></i>รีเซ็ต</a>
                <?php if ($showReport && count($cases) > 0): ?>
                <button type="button" class="btn btn-success btn-sm" onclick="exportTable()"><i class="fas fa-file-excel me-1"></i>Export Excel</button>
                <div class="d-flex align-items-center gap-1">
                    <label class="form-label mb-0 small">วันที่ออกรายงาน:</label>
                    <input type="date" class="form-control form-control-sm" id="report_date" value="<?php echo date('Y-m-d'); ?>" style="width: 150px;">
                    <button type="button" class="btn btn-danger btn-sm" onclick="exportPDF()"><i class="fas fa-file-pdf me-1"></i>Export PDF</button>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>พิมพ์</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if ($showReport): ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-file-alt me-2"></i>ผลลัพธ์: <?php echo count($cases); ?> รายการ</span>
    </div>
    <div class="card-body">
        <?php if (count($cases) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-sm" id="reportTable" style="font-size: 12px;">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>รหัสลูกหนี้</th>
                        <th>ชื่อลูกหนี้</th>
                        <th>PORT</th>
                        <th>ประเภทงาน</th>
                        <th>ครบกำหนด</th>
                        <th>วันรับ</th>
                        <th>วันฟ้อง</th>
                        <th>ศาล</th>
                        <th>คดีดำ</th>
                        <th>คดีแดง</th>
                        <th>การดำเนินการปัจจุบัน</th>
                        <th>การดำเนินการต่อไป</th>
                        <th>ทนายความ</th>
                        <th>หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cases as $i => $c): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><?php echo e($c['debtor_code']); ?></td>
                        <td><?php echo e($c['debtor_name']); ?></td>
                        <td><?php echo e($c['port']); ?></td>
                        <td><?php echo e($c['work_type_name']); ?></td>
                        <td><?php echo formatDate($c['due_date']); ?></td>
                        <td><?php echo formatDate($c['received_date']); ?></td>
                        <td><?php echo formatDate($c['filing_date']); ?></td>
                        <td><?php echo e($c['court_name']); ?></td>
                        <td><?php echo e($c['black_case']); ?></td>
                        <td><?php echo e($c['red_case']); ?></td>
                        <td><?php echo e(mb_substr($c['current_action'] ?? '', 0, 30)); ?></td>
                        <td><?php echo e(mb_substr($c['next_action'] ?? '', 0, 30)); ?></td>
                        <td><?php echo e($c['lawyer_name']); ?></td>
                        <td><?php echo e(mb_substr($c['problems_remarks'] ?? '', 0, 30)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>ไม่พบข้อมูลตามเงื่อนไขที่เลือก</div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
function exportTable() {
    var table = document.getElementById('reportTable');
    var html = table.outerHTML;
    var blob = new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'case_report_<?php echo date('Ymd_His'); ?>.xls';
    a.click();
}

function exportPDF() {
    var reportDate = document.getElementById('report_date').value;
    var baseUrl = 'case_report_pdf.php?<?php echo http_build_query($filters); ?>';
    var url = baseUrl + '&report_date=' + reportDate;
    window.open(url, '_blank');
}
</script>

<style>
@media print {
    .sidebar, .card-header button, form, .btn { display: none !important; }
    .card { border: none !important; }
    table { font-size: 10px !important; }
}
</style>

<?php include '../includes/footer.php'; ?>
