<?php
/**
 * Dashboard Page
 * ระบบงานเอกสารสำนักงานทนายความ
 */

require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireLogin();

$pageTitle = 'หน้าหลัก';
$stats = getDashboardStats();

include 'includes/header.php';
?>

<div class="row g-4 mb-4">
    <!-- Total Cases -->
    <div class="col-md-6 col-xl-3">
        <div class="stat-card bg-primary position-relative">
            <i class="fas fa-folder-open stat-icon"></i>
            <div class="stat-number"><?php echo number_format($stats['total_cases']); ?></div>
            <div class="stat-label">คดีที่กำลังดำเนินการ</div>
        </div>
    </div>
    
    <!-- Pending Disbursements -->
    <div class="col-md-6 col-xl-3">
        <div class="stat-card bg-warning position-relative">
            <i class="fas fa-file-invoice-dollar stat-icon"></i>
            <div class="stat-number"><?php echo number_format($stats['pending_disbursements']); ?></div>
            <div class="stat-label">ใบเบิกรอดำเนินการ</div>
        </div>
    </div>
    
    <!-- Pending Amount -->
    <div class="col-md-6 col-xl-3">
        <div class="stat-card bg-success position-relative">
            <i class="fas fa-coins stat-icon"></i>
            <div class="stat-number"><?php echo number_format($stats['pending_amount'], 2); ?></div>
            <div class="stat-label">ยอดเงินรอเบิก (บาท)</div>
        </div>
    </div>
    
    <!-- Total Users -->
    <div class="col-md-6 col-xl-3">
        <div class="stat-card bg-info position-relative">
            <i class="fas fa-users stat-icon"></i>
            <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
            <div class="stat-label">ผู้ใช้งานในระบบ</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Cases -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-folder-open me-2"></i>คดีล่าสุด</span>
                <a href="<?php echo BASE_URL; ?>cases/" class="btn btn-sm btn-primary">ดูทั้งหมด</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>รหัสลูกหนี้</th>
                                <th>ชื่อลูกหนี้</th>
                                <th>ประเภทงาน</th>
                                <th>วันที่รับ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stats['recent_cases'])): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">ไม่มีข้อมูล</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($stats['recent_cases'] as $case): ?>
                            <tr>
                                <td><a href="<?php echo BASE_URL; ?>cases/view.php?id=<?php echo $case['id']; ?>"><?php echo e($case['debtor_code']); ?></a></td>
                                <td><?php echo e(mb_substr($case['debtor_name'], 0, 30)); ?><?php echo mb_strlen($case['debtor_name']) > 30 ? '...' : ''; ?></td>
                                <td><?php echo e($case['work_type_name'] ?? '-'); ?></td>
                                <td><?php echo formatDate($case['received_date']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Disbursements -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-invoice-dollar me-2"></i>ใบเบิกล่าสุด</span>
                <a href="<?php echo BASE_URL; ?>disbursements/" class="btn btn-sm btn-primary">ดูทั้งหมด</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>เลขที่ใบเบิก</th>
                                <th>ลูกหนี้</th>
                                <th>จำนวนเงิน</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stats['recent_disbursements'])): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">ไม่มีข้อมูล</td>
                            </tr>
                            <?php else: ?>
                            <?php 
                            global $STATUS_LABELS;
                            foreach ($stats['recent_disbursements'] as $disb): 
                                $statusLabel = $STATUS_LABELS[$disb['status']] ?? ['text' => $disb['status'], 'class' => 'secondary'];
                            ?>
                            <tr>
                                <td><a href="<?php echo BASE_URL; ?>disbursements/view.php?id=<?php echo $disb['id']; ?>"><?php echo e($disb['disbursement_number']); ?></a></td>
                                <td><?php echo e(mb_substr($disb['debtor_name'], 0, 25)); ?><?php echo mb_strlen($disb['debtor_name']) > 25 ? '...' : ''; ?></td>
                                <td class="text-end"><?php echo formatNumber($disb['total_amount']); ?></td>
                                <td><span class="badge bg-<?php echo $statusLabel['class']; ?>"><?php echo $statusLabel['text']; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-bolt me-2"></i>การดำเนินการด่วน
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>cases/add.php" class="btn btn-outline-primary w-100 py-3">
                    <i class="fas fa-plus-circle fa-2x mb-2 d-block"></i>
                    เพิ่มคดีใหม่
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>disbursements/add.php" class="btn btn-outline-success w-100 py-3">
                    <i class="fas fa-file-invoice-dollar fa-2x mb-2 d-block"></i>
                    สร้างใบเบิก
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>reports/case_report.php" class="btn btn-outline-info w-100 py-3">
                    <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                    รายงานคดี
                </a>
            </div>
            <div class="col-md-3">
                <a href="<?php echo BASE_URL; ?>reports/disbursement_report.php" class="btn btn-outline-warning w-100 py-3">
                    <i class="fas fa-chart-pie fa-2x mb-2 d-block"></i>
                    รายงานเบิก
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
