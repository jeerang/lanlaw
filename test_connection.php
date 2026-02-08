<?php
/**
 * Database Connection Test
 * ระบบงานเอกสารสำนักงานทนายความ
 */

require_once 'config/config.php';

$pageTitle = 'ทดสอบการเชื่อมต่อระบบ';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background: #f8f9fa;
            padding: 40px 0;
        }
        .test-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        .test-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .test-item:last-child {
            border-bottom: none;
        }
        .test-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 20px;
        }
        .test-icon.success { background: #d4edda; color: #28a745; }
        .test-icon.danger { background: #f8d7da; color: #dc3545; }
        .test-icon.warning { background: #fff3cd; color: #ffc107; }
        .test-icon.info { background: #d1ecf1; color: #17a2b8; }
        .test-name { font-weight: 600; }
        .test-status { margin-left: auto; }
        .header-icon {
            font-size: 60px;
            color: #3498db;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <i class="fas fa-cogs header-icon"></i>
                    <h2><?php echo $pageTitle; ?></h2>
                    <p class="text-muted"><?php echo SITE_NAME; ?></p>
                </div>
                
                <!-- Connection Test -->
                <div class="test-card">
                    <h5 class="mb-4"><i class="fas fa-database me-2"></i>การเชื่อมต่อฐานข้อมูล</h5>
                    
                    <?php
                    $dbTest = testConnection();
                    ?>
                    <div class="test-item">
                        <div class="test-icon <?php echo $dbTest['status'] ? 'success' : 'danger'; ?>">
                            <i class="fas <?php echo $dbTest['status'] ? 'fa-check' : 'fa-times'; ?>"></i>
                        </div>
                        <div>
                            <div class="test-name">MySQL Connection</div>
                            <small class="text-muted"><?php echo e($dbTest['message']); ?></small>
                        </div>
                        <div class="test-status">
                            <span class="badge bg-<?php echo $dbTest['status'] ? 'success' : 'danger'; ?>">
                                <?php echo $dbTest['status'] ? 'สำเร็จ' : 'ล้มเหลว'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if ($dbTest['status']): ?>
                    <div class="test-item">
                        <div class="test-icon info">
                            <i class="fas fa-info"></i>
                        </div>
                        <div>
                            <div class="test-name">Database Info</div>
                            <small class="text-muted">
                                Host: <?php echo DB_HOST; ?> | 
                                Database: <?php echo DB_NAME; ?> | 
                                Charset: <?php echo DB_CHARSET; ?>
                            </small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Tables Check -->
                <?php if ($dbTest['status']): ?>
                <div class="test-card">
                    <h5 class="mb-4"><i class="fas fa-table me-2"></i>ตรวจสอบตาราง</h5>
                    
                    <?php
                    $tables = [
                        'users' => 'ผู้ใช้งาน',
                        'work_types' => 'ประเภทงาน',
                        'courts' => 'ศาล',
                        'disbursement_types' => 'ประเภทการเบิก',
                        'lawyers' => 'ทนายความ',
                        'account_officers' => 'A/O',
                        'offices' => 'สำนักงาน',
                        'cases' => 'คดี',
                        'disbursements' => 'ใบเบิก',
                        'disbursement_items' => 'รายการเบิกย่อย',
                        'activity_logs' => 'Log กิจกรรม'
                    ];
                    
                    $db = getDB();
                    foreach ($tables as $table => $name):
                        try {
                            $stmt = $db->query("SELECT COUNT(*) as count FROM {$table}");
                            $count = $stmt->fetch()['count'];
                            $exists = true;
                        } catch (Exception $e) {
                            $exists = false;
                            $count = 0;
                        }
                    ?>
                    <div class="test-item">
                        <div class="test-icon <?php echo $exists ? 'success' : 'danger'; ?>">
                            <i class="fas <?php echo $exists ? 'fa-check' : 'fa-times'; ?>"></i>
                        </div>
                        <div>
                            <div class="test-name"><?php echo e($name); ?> (<?php echo $table; ?>)</div>
                            <small class="text-muted">
                                <?php echo $exists ? "จำนวน {$count} รายการ" : "ไม่พบตาราง"; ?>
                            </small>
                        </div>
                        <div class="test-status">
                            <span class="badge bg-<?php echo $exists ? 'success' : 'danger'; ?>">
                                <?php echo $exists ? 'พร้อมใช้งาน' : 'ไม่พบ'; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- System Check -->
                <div class="test-card">
                    <h5 class="mb-4"><i class="fas fa-server me-2"></i>ข้อมูลระบบ</h5>
                    
                    <div class="test-item">
                        <div class="test-icon info">
                            <i class="fab fa-php"></i>
                        </div>
                        <div>
                            <div class="test-name">PHP Version</div>
                            <small class="text-muted"><?php echo phpversion(); ?></small>
                        </div>
                        <div class="test-status">
                            <span class="badge bg-<?php echo version_compare(phpversion(), '8.0', '>=') ? 'success' : 'warning'; ?>">
                                <?php echo version_compare(phpversion(), '8.0', '>=') ? 'รองรับ' : 'แนะนำ 8.0+'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="test-item">
                        <div class="test-icon <?php echo extension_loaded('pdo_mysql') ? 'success' : 'danger'; ?>">
                            <i class="fas <?php echo extension_loaded('pdo_mysql') ? 'fa-check' : 'fa-times'; ?>"></i>
                        </div>
                        <div>
                            <div class="test-name">PDO MySQL Extension</div>
                            <small class="text-muted">สำหรับเชื่อมต่อฐานข้อมูล</small>
                        </div>
                        <div class="test-status">
                            <span class="badge bg-<?php echo extension_loaded('pdo_mysql') ? 'success' : 'danger'; ?>">
                                <?php echo extension_loaded('pdo_mysql') ? 'ติดตั้งแล้ว' : 'ไม่พบ'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="test-item">
                        <div class="test-icon <?php echo extension_loaded('mbstring') ? 'success' : 'warning'; ?>">
                            <i class="fas <?php echo extension_loaded('mbstring') ? 'fa-check' : 'fa-exclamation'; ?>"></i>
                        </div>
                        <div>
                            <div class="test-name">Mbstring Extension</div>
                            <small class="text-muted">สำหรับรองรับภาษาไทย</small>
                        </div>
                        <div class="test-status">
                            <span class="badge bg-<?php echo extension_loaded('mbstring') ? 'success' : 'warning'; ?>">
                                <?php echo extension_loaded('mbstring') ? 'ติดตั้งแล้ว' : 'ไม่พบ'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="test-item">
                        <div class="test-icon info">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div class="test-name">Timezone</div>
                            <small class="text-muted"><?php echo date_default_timezone_get(); ?></small>
                        </div>
                    </div>
                    
                    <div class="test-item">
                        <div class="test-icon info">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div>
                            <div class="test-name">วันที่/เวลาปัจจุบัน</div>
                            <small class="text-muted"><?php echo date('d/m/Y H:i:s'); ?></small>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="text-center mt-4">
                    <?php if ($dbTest['status']): ?>
                    <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-primary btn-lg me-2">
                        <i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ
                    </a>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>คำแนะนำ:</strong> กรุณานำเข้าไฟล์ <code>database/database.sql</code> เข้าสู่ MySQL ก่อน
                    </div>
                    <?php endif; ?>
                    
                    <a href="<?php echo BASE_URL; ?>test_connection.php" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-sync-alt me-2"></i>ทดสอบอีกครั้ง
                    </a>
                </div>
                
                <!-- Login Info -->
                <?php if ($dbTest['status']): ?>
                <div class="alert alert-info mt-4">
                    <h6><i class="fas fa-info-circle me-2"></i>ข้อมูลสำหรับเข้าสู่ระบบ</h6>
                    <p class="mb-1"><strong>Admin:</strong> username: <code>admin</code> | password: <code>admin123</code></p>
                    <p class="mb-0"><strong>User:</strong> username: <code>user1</code> | password: <code>admin123</code></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
