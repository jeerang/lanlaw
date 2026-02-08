<?php
/**
 * Summary Report - Dashboard style report
 */
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$pageTitle = 'รายงานสรุป';

$db = getDB();

// Get date range
$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-t');

// Cases summary
$stmtCases = $db->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_count
    FROM cases WHERE received_date BETWEEN ? AND ?
");
$stmtCases->execute([$dateFrom, $dateTo]);
$caseSummary = $stmtCases->fetch();

// Disbursement summary
$stmtDisbursement = $db->prepare("
    SELECT 
        COUNT(*) as total,
        COALESCE(SUM(total_amount), 0) as total_amount,
        SUM(CASE WHEN status = 'pending' THEN total_amount ELSE 0 END) as pending_amount,
        SUM(CASE WHEN status = 'processing' THEN total_amount ELSE 0 END) as processing_amount,
        SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as paid_amount
    FROM disbursements WHERE disbursement_date BETWEEN ? AND ?
");
$stmtDisbursement->execute([$dateFrom, $dateTo]);
$disbursementSummary = $stmtDisbursement->fetch();

// Top 5 disbursement types
$stmtTopTypes = $db->prepare("
    SELECT dt.name, SUM(di.amount) as total
    FROM disbursement_items di
    JOIN disbursements d ON di.disbursement_id = d.id
    JOIN disbursement_types dt ON di.disbursement_type_id = dt.id
    WHERE d.disbursement_date BETWEEN ? AND ?
    GROUP BY dt.id ORDER BY total DESC LIMIT 5
");
$stmtTopTypes->execute([$dateFrom, $dateTo]);
$topTypes = $stmtTopTypes->fetchAll();

// Cases by work type
$stmtByWorkType = $db->prepare("
    SELECT wt.name, COUNT(*) as count
    FROM cases c
    JOIN work_types wt ON c.work_type_id = wt.id
    WHERE c.received_date BETWEEN ? AND ?
    GROUP BY wt.id ORDER BY count DESC LIMIT 5
");
$stmtByWorkType->execute([$dateFrom, $dateTo]);
$byWorkType = $stmtByWorkType->fetchAll();

// Cases by lawyer
$stmtByLawyer = $db->prepare("
    SELECT CONCAT(l.prefix, l.firstname, ' ', l.lastname) as name, COUNT(*) as count
    FROM cases c
    JOIN lawyers l ON c.lawyer_id = l.id
    WHERE c.received_date BETWEEN ? AND ?
    GROUP BY l.id ORDER BY count DESC LIMIT 5
");
$stmtByLawyer->execute([$dateFrom, $dateTo]);
$byLawyer = $stmtByLawyer->fetchAll();

include '../includes/header.php';
?>

<div class="card mb-4">
    <div class="card-header"><i class="fas fa-calendar me-2"></i>เลือกช่วงเวลา</div>
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label class="form-label">จากวันที่</label>
                <input type="date" class="form-control" name="date_from" value="<?php echo e($dateFrom); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">ถึงวันที่</label>
                <input type="date" class="form-control" name="date_to" value="<?php echo e($dateTo); ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>แสดงรายงาน</button>
                <button type="button" class="btn btn-danger" onclick="window.print()"><i class="fas fa-print me-1"></i>พิมพ์</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- Case Summary -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white"><i class="fas fa-folder-open me-2"></i>สรุปคดี</div>
            <div class="card-body">
                <h4 class="text-center mb-4"><?php echo number_format($caseSummary['total']); ?> คดี</h4>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="p-3 bg-success text-white rounded">
                            <h5><?php echo number_format($caseSummary['active_count']); ?></h5>
                            <small>กำลังดำเนินการ</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-warning rounded">
                            <h5><?php echo number_format($caseSummary['pending_count']); ?></h5>
                            <small>รอดำเนินการ</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-secondary text-white rounded">
                            <h5><?php echo number_format($caseSummary['closed_count']); ?></h5>
                            <small>ปิดคดี</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Disbursement Summary -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white"><i class="fas fa-money-bill me-2"></i>สรุปใบเบิก</div>
            <div class="card-body">
                <h4 class="text-center mb-4"><?php echo number_format($disbursementSummary['total_amount'], 2); ?> บาท</h4>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="p-3 bg-warning rounded">
                            <h6><?php echo number_format($disbursementSummary['pending_amount'], 2); ?></h6>
                            <small>รออนุมัติ</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-info text-white rounded">
                            <h6><?php echo number_format($disbursementSummary['processing_amount'], 2); ?></h6>
                            <small>อนุมัติแล้ว</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-success text-white rounded">
                            <h6><?php echo number_format($disbursementSummary['paid_amount'], 2); ?></h6>
                            <small>จ่ายแล้ว</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Disbursement Types -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-chart-bar me-2"></i>Top 5 ประเภทค่าใช้จ่าย</div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>ประเภท</th><th class="text-end">ยอดรวม</th></tr></thead>
                    <tbody>
                        <?php foreach ($topTypes as $t): ?>
                        <tr><td><?php echo e($t['name']); ?></td><td class="text-end"><?php echo number_format($t['total'], 2); ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($topTypes)): ?>
                        <tr><td colspan="2" class="text-center text-muted">ไม่มีข้อมูล</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Cases by Work Type -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-briefcase me-2"></i>คดีตามประเภทงาน</div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>ประเภทงาน</th><th class="text-end">จำนวน</th></tr></thead>
                    <tbody>
                        <?php foreach ($byWorkType as $w): ?>
                        <tr><td><?php echo e($w['name']); ?></td><td class="text-end"><?php echo number_format($w['count']); ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($byWorkType)): ?>
                        <tr><td colspan="2" class="text-center text-muted">ไม่มีข้อมูล</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Cases by Lawyer -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-user-tie me-2"></i>คดีตามทนายความ</div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>ทนายความ</th><th class="text-end">จำนวน</th></tr></thead>
                    <tbody>
                        <?php foreach ($byLawyer as $l): ?>
                        <tr><td><?php echo e($l['name']); ?></td><td class="text-end"><?php echo number_format($l['count']); ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($byLawyer)): ?>
                        <tr><td colspan="2" class="text-center text-muted">ไม่มีข้อมูล</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, form, .btn { display: none !important; }
    .card { break-inside: avoid; }
}
</style>

<?php include '../includes/footer.php'; ?>
