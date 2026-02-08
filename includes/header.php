<?php
/**
 * Header Template
 * ระบบงานเอกสารสำนักงานทนายความ
 */

if (!defined('ROOT_PATH')) {
    require_once dirname(__DIR__) . '/config/config.php';
}

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    
    <!-- jQuery (loaded early for inline scripts) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- Thai Font -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --sidebar-width: 260px;
        }
        
        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 14px;
            background-color: #f8f9fa;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: #fff;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s;
        }
        
        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand h4 {
            margin: 0;
            font-weight: 600;
            font-size: 18px;
        }
        
        .sidebar-brand small {
            color: rgba(255,255,255,0.7);
            font-size: 12px;
        }
        
        .sidebar-menu {
            padding: 15px 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu .nav-link:hover,
        .sidebar-menu .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-left-color: var(--accent-color);
        }
        
        .sidebar-menu .nav-link i {
            width: 25px;
            margin-right: 10px;
            text-align: center;
        }
        
        .sidebar-menu .menu-header {
            color: rgba(255,255,255,0.5);
            font-size: 11px;
            text-transform: uppercase;
            padding: 15px 20px 8px;
            letter-spacing: 1px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        /* Top Navbar */
        .top-navbar {
            background: #fff;
            padding: 15px 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .user-dropdown .dropdown-toggle {
            background: none;
            border: none;
            padding: 5px 10px;
            color: var(--primary-color);
        }
        
        .user-dropdown .dropdown-toggle::after {
            display: none;
        }
        
        /* Content Area */
        .content-area {
            padding: 25px;
        }
        
        /* Cards */
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-radius: 10px;
        }
        
        .card-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            font-weight: 600;
        }
        
        /* Buttons */
        .btn {
            border-radius: 5px;
            font-weight: 500;
            padding: 8px 16px;
        }
        
        .btn-primary {
            background: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .btn-primary:hover {
            background: #2980b9;
            border-color: #2980b9;
        }
        
        /* Tables */
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        /* Forms */
        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .form-control, .form-select {
            border-radius: 5px;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        /* Status badges */
        .badge {
            padding: 5px 10px;
            font-weight: 500;
            border-radius: 5px;
        }
        
        /* Stat cards */
        .stat-card {
            padding: 20px;
            border-radius: 10px;
            color: #fff;
        }
        
        .stat-card.bg-primary { background: linear-gradient(135deg, #3498db, #2980b9) !important; }
        .stat-card.bg-success { background: linear-gradient(135deg, #2ecc71, #27ae60) !important; }
        .stat-card.bg-warning { background: linear-gradient(135deg, #f1c40f, #f39c12) !important; }
        .stat-card.bg-info { background: linear-gradient(135deg, #1abc9c, #16a085) !important; }
        
        .stat-card .stat-icon {
            font-size: 40px;
            opacity: 0.3;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .stat-card .stat-number {
            font-size: 28px;
            font-weight: 700;
        }
        
        .stat-card .stat-label {
            font-size: 13px;
            opacity: 0.9;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
        
        /* Print */
        @media print {
            .sidebar, .top-navbar, .btn, .pagination {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
        }
    </style>
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <!-- Sidebar -->
    <?php include INCLUDES_PATH . 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-link d-lg-none me-2" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title"><?php echo isset($pageTitle) ? e($pageTitle) : 'หน้าหลัก'; ?></h1>
            </div>
            
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">
                    <i class="far fa-calendar-alt me-1"></i>
                    <?php echo date('d/m/Y'); ?>
                </span>
                
                <div class="dropdown user-dropdown">
                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg me-1"></i>
                        <?php echo e($currentUser['fullname']); ?>
                        <i class="fas fa-chevron-down ms-1" style="font-size: 10px;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile.php">
                            <i class="fas fa-user me-2"></i>โปรไฟล์
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>change_password.php">
                            <i class="fas fa-key me-2"></i>เปลี่ยนรหัสผ่าน
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            <?php 
            $flash = getFlashMessage();
            if ($flash): 
            ?>
            <div class="alert alert-<?php echo e($flash['type']); ?> alert-dismissible fade show" role="alert">
                <?php echo e($flash['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
    <?php endif; ?>
