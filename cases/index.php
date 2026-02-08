<?php
/**
 * Cases List
 */
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$pageTitle = 'รับงาน/คดี';

if (isset($_GET['delete'])) {
    $db = getDB();
    $oldData = getById('cases', $_GET['delete']);
    $db->prepare("DELETE FROM cases WHERE id = ?")->execute([$_GET['delete']]);
    logActivity('delete', 'cases', $_GET['delete'], $oldData);
    setFlashMessage('success', 'ลบข้อมูลสำเร็จ');
    redirect(BASE_URL . 'cases/');
}

// Get offices for dropdown filter
$offices = getActiveForDropdown('offices');

// Filter by office
$officeFilter = $_GET['office_id'] ?? '';

$db = getDB();

// Build query with optional filter
$sql = "
    SELECT c.*, wt.name as work_type_name, o.name as office_name, ct.name as court_name,
           CONCAT(l.prefix, l.firstname, ' ', l.lastname) as lawyer_name, ao.name as ao_name
    FROM cases c
    LEFT JOIN work_types wt ON c.work_type_id = wt.id
    LEFT JOIN offices o ON c.office_id = o.id
    LEFT JOIN courts ct ON c.court_id = ct.id
    LEFT JOIN lawyers l ON c.lawyer_id = l.id
    LEFT JOIN account_officers ao ON c.ao_id = ao.id
";

$params = [];
if (!empty($officeFilter)) {
    $sql .= " WHERE c.office_id = ?";
    $params[] = $officeFilter;
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$cases = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-folder-open me-2"></i>รายการคดี</span>
        <a href="add.php" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>เพิ่มคดีใหม่</a>
    </div>
    <div class="card-body">
        <!-- Filter Section -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">กรองตามสำนักงานเจ้าของงาน</label>
                <select class="form-select" id="officeFilter" onchange="filterByOffice()">
                    <option value="">-- ทั้งหมด --</option>
                    <?php foreach ($offices as $office): ?>
                    <option value="<?php echo $office['value']; ?>" <?php echo $officeFilter == $office['value'] ? 'selected' : ''; ?>>
                        <?php echo e($office['text']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <?php if (!empty($officeFilter)): ?>
                <a href="<?php echo BASE_URL; ?>cases/" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>ล้างตัวกรอง
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover table-sm datatable" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>รหัสลูกหนี้</th>
                        <th>ชื่อลูกหนี้</th>
                        <th>PORT</th>
                        <th>ประเภทงาน</th>
                        <th>สำนักงาน</th>
                        <th>ครบกำหนด</th>
                        <th>ศาล</th>
                        <th>คดีดำ</th>
                        <th>คดีแดง</th>
                        <th>ทนายความ</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cases as $i => $case): 
                        global $STATUS_LABELS;
                        $sl = $STATUS_LABELS[$case['status']] ?? ['text'=>$case['status'],'class'=>'secondary'];
                    ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><a href="view.php?id=<?php echo $case['id']; ?>"><?php echo e($case['debtor_code']); ?></a></td>
                        <td><?php echo e(mb_substr($case['debtor_name'], 0, 30)); ?><?php echo mb_strlen($case['debtor_name']) > 30 ? '...' : ''; ?></td>
                        <td><code><?php echo e($case['port']); ?></code></td>
                        <td><?php echo e($case['work_type_name'] ?? '-'); ?></td>
                        <td><?php echo e($case['office_name'] ?? '-'); ?></td>
                        <td><?php echo formatDate($case['due_date']); ?></td>
                        <td><?php echo e($case['court_name'] ?? '-'); ?></td>
                        <td><?php echo e($case['black_case'] ?? '-'); ?></td>
                        <td><?php echo e($case['red_case'] ?? '-'); ?></td>
                        <td><?php echo e($case['lawyer_name'] ?? '-'); ?></td>
                        <td><span class="badge bg-<?php echo $sl['class']; ?>"><?php echo $sl['text']; ?></span></td>
                        <td>
                            <a href="view.php?id=<?php echo $case['id']; ?>" class="btn btn-info btn-sm" title="ดู"><i class="fas fa-eye"></i></a>
                            <a href="edit.php?id=<?php echo $case['id']; ?>" class="btn btn-warning btn-sm" title="แก้ไข"><i class="fas fa-edit"></i></a>
                            <a href="?delete=<?php echo $case['id']; ?>" class="btn btn-danger btn-sm btn-delete" data-name="<?php echo e($case['debtor_code']); ?>"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
function filterByOffice() {
    var officeId = document.getElementById('officeFilter').value;
    if (officeId) {
        window.location.href = '<?php echo BASE_URL; ?>cases/?office_id=' + officeId;
    } else {
        window.location.href = '<?php echo BASE_URL; ?>cases/';
    }
}
</script>
