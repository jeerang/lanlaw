<?php
/**
 * Backup & Import Management
 * ระบบสำรองและนำเข้าข้อมูล
 */
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireAdmin();

$pageTitle = 'สำรองและนำเข้าข้อมูล';

// Get list of backup files
$backupDir = ROOT_PATH . 'backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$backupFiles = [];
$files = glob($backupDir . '/*.{sql,csv,zip}', GLOB_BRACE);
if ($files) {
    foreach ($files as $file) {
        $backupFiles[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => filemtime($file),
            'type' => pathinfo($file, PATHINFO_EXTENSION)
        ];
    }
    // Sort by date descending
    usort($backupFiles, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

// Available tables for backup
$tables = [
    'users' => 'ผู้ใช้งาน',
    'work_types' => 'ประเภทงาน',
    'courts' => 'ศาล',
    'disbursement_types' => 'ประเภทการเบิก',
    'lawyers' => 'ทนายความ',
    'account_officers' => 'A/O',
    'offices' => 'สำนักงานเจ้าของงาน',
    'cases' => 'คดี',
    'disbursements' => 'ใบเบิก',
    'disbursement_items' => 'รายการเบิก',
    'activity_logs' => 'Log การใช้งาน'
];

include '../../includes/header.php';
?>

<div class="row">
    <!-- Export Section -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-download me-2"></i>สำรองข้อมูล (Export)
            </div>
            <div class="card-body">
                <form action="export.php" method="POST" id="exportForm">
                    <?php echo csrfField(); ?>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">เลือกตารางที่ต้องการสำรอง</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label fw-bold text-primary" for="selectAll">เลือกทั้งหมด</label>
                        </div>
                        <hr class="my-2">
                        <div class="row">
                            <?php foreach ($tables as $table => $label): ?>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input table-checkbox" type="checkbox" name="tables[]" value="<?php echo $table; ?>" id="table_<?php echo $table; ?>">
                                    <label class="form-check-label" for="table_<?php echo $table; ?>"><?php echo e($label); ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">รูปแบบไฟล์</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="format" value="sql" id="formatSql" checked>
                            <label class="form-check-label" for="formatSql">
                                <i class="fas fa-database text-primary me-1"></i>SQL (แนะนำสำหรับ Backup ทั้งหมด)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="format" value="csv" id="formatCsv">
                            <label class="form-check-label" for="formatCsv">
                                <i class="fas fa-file-csv text-success me-1"></i>CSV (แยกไฟล์ตามตาราง, รวม ZIP)
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-download me-2"></i>ดาวน์โหลด Backup
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Import Section -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <i class="fas fa-upload me-2"></i>นำเข้าข้อมูล (Import)
            </div>
            <div class="card-body">
                <form action="import.php" method="POST" enctype="multipart/form-data" id="importForm">
                    <?php echo csrfField(); ?>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>คำเตือน:</strong> การนำเข้าข้อมูลอาจเขียนทับข้อมูลเดิม กรุณาสำรองข้อมูลก่อนดำเนินการ
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">เลือกไฟล์</label>
                        <input type="file" class="form-control" name="import_file" accept=".sql,.csv,.zip" required>
                        <small class="text-muted">รองรับไฟล์ .sql, .csv หรือ .zip</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">ตารางเป้าหมาย (สำหรับ CSV)</label>
                        <select class="form-select" name="target_table" id="targetTable">
                            <option value="">-- ตรวจจับอัตโนมัติ --</option>
                            <?php foreach ($tables as $table => $label): ?>
                            <option value="<?php echo $table; ?>"><?php echo e($label); ?> (<?php echo $table; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">ระบุเมื่อ import ไฟล์ CSV เดี่ยว</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">โหมดการนำเข้า</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="import_mode" value="append" id="modeAppend" checked>
                            <label class="form-check-label" for="modeAppend">
                                <i class="fas fa-plus text-success me-1"></i>เพิ่มข้อมูลใหม่ (Append)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="import_mode" value="replace" id="modeReplace">
                            <label class="form-check-label" for="modeReplace">
                                <i class="fas fa-sync text-warning me-1"></i>แทนที่ข้อมูลเดิม (Replace)
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการนำเข้าข้อมูล?');">
                        <i class="fas fa-upload me-2"></i>นำเข้าข้อมูล
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Backup History -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-history me-2"></i>ประวัติการสำรองข้อมูล
    </div>
    <div class="card-body">
        <?php if (empty($backupFiles)): ?>
        <div class="text-center text-muted py-4">
            <i class="fas fa-folder-open fa-3x mb-3"></i>
            <p>ยังไม่มีไฟล์สำรองข้อมูล</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ชื่อไฟล์</th>
                        <th>ประเภท</th>
                        <th>ขนาด</th>
                        <th>วันที่สร้าง</th>
                        <th width="150">การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backupFiles as $file): ?>
                    <tr>
                        <td>
                            <i class="fas fa-<?php echo $file['type'] == 'sql' ? 'database' : ($file['type'] == 'zip' ? 'file-archive' : 'file-csv'); ?> me-2"></i>
                            <?php echo e($file['name']); ?>
                        </td>
                        <td><span class="badge bg-<?php echo $file['type'] == 'sql' ? 'primary' : ($file['type'] == 'zip' ? 'warning' : 'success'); ?>"><?php echo strtoupper($file['type']); ?></span></td>
                        <td><?php echo formatFileSize($file['size']); ?></td>
                        <td><?php echo date('d/m/Y H:i:s', $file['date']); ?></td>
                        <td>
                            <a href="download.php?file=<?php echo urlencode($file['name']); ?>" class="btn btn-sm btn-primary" title="ดาวน์โหลด">
                                <i class="fas fa-download"></i>
                            </a>
                            <a href="delete.php?file=<?php echo urlencode($file['name']); ?>" class="btn btn-sm btn-danger" title="ลบ" onclick="return confirm('ต้องการลบไฟล์นี้?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    // Select all checkbox
    $('#selectAll').change(function() {
        $('.table-checkbox').prop('checked', $(this).is(':checked'));
    });
    
    $('.table-checkbox').change(function() {
        if (!$(this).is(':checked')) {
            $('#selectAll').prop('checked', false);
        } else if ($('.table-checkbox:checked').length === $('.table-checkbox').length) {
            $('#selectAll').prop('checked', true);
        }
    });
    
    // Validate export form
    $('#exportForm').submit(function(e) {
        if ($('.table-checkbox:checked').length === 0) {
            alert('กรุณาเลือกตารางอย่างน้อย 1 ตาราง');
            e.preventDefault();
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
