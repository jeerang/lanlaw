<?php
/**
 * Disbursement Report
 */
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$pageTitle = 'รายงานการเบิกจ่าย';

$courts = getActiveForDropdown('courts');
$disbursementTypes = getActiveForDropdown('disbursement_types');
$offices = getActiveForDropdown('offices');
global $DISBURSEMENT_STATUSES;

$filters = [
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'court_id' => $_GET['court_id'] ?? '',
    'status' => $_GET['status'] ?? '',
    'type_id' => $_GET['type_id'] ?? '',
    'office_id' => $_GET['office_id'] ?? ''
];

$disbursements = [];
$totalSum = 0;
$showReport = !empty($_GET);

if ($showReport) {
    $db = getDB();
    $sql = "SELECT d.*, c.debtor_code, c.debtor_name, ct.name as court_name, u.fullname as created_by_name, o.name as office_name
            FROM disbursements d
            LEFT JOIN cases c ON d.case_id = c.id
            LEFT JOIN courts ct ON d.court_id = ct.id
            LEFT JOIN users u ON d.created_by = u.id
            LEFT JOIN offices o ON c.office_id = o.id
            WHERE 1=1";
    $params = [];
    
    if ($filters['date_from']) { $sql .= " AND d.disbursement_date >= ?"; $params[] = $filters['date_from']; }
    if ($filters['date_to']) { $sql .= " AND d.disbursement_date <= ?"; $params[] = $filters['date_to']; }
    if ($filters['court_id']) { $sql .= " AND d.court_id = ?"; $params[] = $filters['court_id']; }
    if ($filters['status']) { $sql .= " AND d.status = ?"; $params[] = $filters['status']; }
    if ($filters['type_id']) {
        $sql .= " AND d.id IN (SELECT disbursement_id FROM disbursement_items WHERE disbursement_type_id = ?)";
        $params[] = $filters['type_id'];
    }
    if ($filters['office_id']) { $sql .= " AND c.office_id = ?"; $params[] = $filters['office_id']; }
    
    $sql .= " ORDER BY d.disbursement_date DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $disbursements = $stmt->fetchAll();
    
    foreach ($disbursements as $d) {
        $totalSum += $d['total_amount'];
    }
}

include '../includes/header.php';
?>

<div class="card mb-4">
    <div class="card-header"><i class="fas fa-filter me-2"></i>ตัวกรองรายงาน</div>
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-2 mb-2">
                    <label class="form-label">วันที่ใบเบิก (จาก)</label>
                    <input type="date" class="form-control form-control-sm" name="date_from" value="<?php echo e($filters['date_from']); ?>">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">ถึง</label>
                    <input type="date" class="form-control form-control-sm" name="date_to" value="<?php echo e($filters['date_to']); ?>">
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
                    <label class="form-label">ประเภทค่าใช้จ่าย</label>
                    <select class="form-select form-select-sm" name="type_id">
                        <option value="">-- ทั้งหมด --</option>
                        <?php foreach ($disbursementTypes as $dt): ?>
                        <option value="<?php echo $dt['value']; ?>" <?php echo $filters['type_id'] == $dt['value'] ? 'selected' : ''; ?>><?php echo e($dt['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">สถานะ</label>
                    <select class="form-select form-select-sm" name="status">
                        <option value="">-- ทั้งหมด --</option>
                        <?php foreach ($DISBURSEMENT_STATUSES as $k => $v): ?>
                        <option value="<?php echo $k; ?>" <?php echo $filters['status'] == $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">สำนักงานเจ้าของงาน</label>
                    <select class="form-select form-select-sm" name="office_id">
                        <option value="">-- ทั้งหมด --</option>
                        <?php foreach ($offices as $o): ?>
                        <option value="<?php echo $o['value']; ?>" <?php echo $filters['office_id'] == $o['value'] ? 'selected' : ''; ?>><?php echo e($o['text']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i>แสดงรายงาน</button>
                <a href="disbursement_report.php" class="btn btn-secondary btn-sm"><i class="fas fa-redo me-1"></i>รีเซ็ต</a>
                <?php if ($showReport && count($disbursements) > 0): ?>
                <button type="button" class="btn btn-success btn-sm" onclick="exportTable()"><i class="fas fa-file-excel me-1"></i>Export Excel</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>พิมพ์</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if ($showReport): ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-file-alt me-2"></i>ผลลัพธ์: <?php echo count($disbursements); ?> รายการ</span>
        <span class="badge bg-primary fs-6">ยอดรวม: <?php echo number_format($totalSum, 2); ?> บาท</span>
    </div>
    <div class="card-body">
        <?php if (count($disbursements) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-sm" id="reportTable" style="font-size: 12px;">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>เลขที่ใบเบิก</th>
                        <th>วันที่</th>
                        <th>รหัสลูกหนี้</th>
                        <th>ชื่อลูกหนี้</th>
                        <th>ศาล</th>
                        <th>คดีดำ</th>
                        <th class="text-end">ยอดรวม</th>
                        <th>สถานะ</th>
                        <th>ผู้สร้าง</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($disbursements as $i => $d): 
                        $statusClass = ['pending'=>'warning','processing'=>'info','paid'=>'success','rejected'=>'danger'][$d['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><?php echo e($d['disbursement_number']); ?></td>
                        <td><?php echo formatDate($d['disbursement_date']); ?></td>
                        <td><?php echo e($d['debtor_code']); ?></td>
                        <td><?php echo e($d['debtor_name']); ?></td>
                        <td><?php echo e($d['court_name']); ?></td>
                        <td><?php echo e($d['black_case']); ?></td>
                        <td class="text-end"><?php echo number_format($d['total_amount'], 2); ?></td>
                        <td><span class="badge bg-<?php echo $statusClass; ?>"><?php echo $DISBURSEMENT_STATUSES[$d['status']]; ?></span></td>
                        <td><?php echo e($d['created_by_name']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="7" class="text-end"><strong>รวมทั้งสิ้น:</strong></td>
                        <td class="text-end"><strong><?php echo number_format($totalSum, 2); ?></strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>ไม่พบข้อมูลตามเงื่อนไขที่เลือก</div>
        <?php endif; ?>
    </div>
</div>

<!-- Summary by Type -->
<?php if (count($disbursements) > 0): 
    $db = getDB();
    $summaryParams = [];
    $summarySql = "SELECT dt.name, SUM(di.amount) as total_amount, COUNT(DISTINCT d.id) as count
                   FROM disbursement_items di
                   JOIN disbursements d ON di.disbursement_id = d.id
                   JOIN disbursement_types dt ON di.disbursement_type_id = dt.id
                   WHERE 1=1";
    if ($filters['date_from']) { $summarySql .= " AND d.disbursement_date >= ?"; $summaryParams[] = $filters['date_from']; }
    if ($filters['date_to']) { $summarySql .= " AND d.disbursement_date <= ?"; $summaryParams[] = $filters['date_to']; }
    if ($filters['status']) { $summarySql .= " AND d.status = ?"; $summaryParams[] = $filters['status']; }
    $summarySql .= " GROUP BY dt.id ORDER BY total_amount DESC";
    $stmt = $db->prepare($summarySql);
    $stmt->execute($summaryParams);
    $summary = $stmt->fetchAll();
?>
<div class="card mt-4">
    <div class="card-header"><i class="fas fa-chart-pie me-2"></i>สรุปตามประเภทค่าใช้จ่าย</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th>ประเภทค่าใช้จ่าย</th>
                        <th class="text-center">จำนวนใบเบิก</th>
                        <th class="text-end">ยอดรวม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary as $s): ?>
                    <tr>
                        <td><?php echo e($s['name']); ?></td>
                        <td class="text-center"><?php echo number_format($s['count']); ?></td>
                        <td class="text-end"><?php echo number_format($s['total_amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<script>
function exportTable() {
    var table = document.getElementById('reportTable');
    var html = table.outerHTML;
    var blob = new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'disbursement_report_<?php echo date('Ymd_His'); ?>.xls';
    a.click();
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
