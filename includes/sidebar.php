<?php
/**
 * Sidebar Template
 * ระบบงานเอกสารสำนักงานทนายความ
 */

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h4><i class="fas fa-balance-scale me-2"></i><?php echo SITE_SHORT_NAME; ?></h4>
        <small><?php echo SITE_NAME; ?></small>
    </div>
    
    <div class="sidebar-menu">
        <!-- Dashboard -->
        <a href="<?php echo BASE_URL; ?>dashboard.php" 
           class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>หน้าหลัก</span>
        </a>
        
        <?php if (isAdmin()): ?>
        <!-- Admin Menu -->
        <div class="menu-header">จัดการข้อมูลพื้นฐาน</div>
        
        <a href="<?php echo BASE_URL; ?>admin/users/" 
           class="nav-link <?php echo $currentDir === 'users' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>ผู้ใช้งาน</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>admin/work_types/" 
           class="nav-link <?php echo $currentDir === 'work_types' ? 'active' : ''; ?>">
            <i class="fas fa-tasks"></i>
            <span>ประเภทงาน</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>admin/courts/" 
           class="nav-link <?php echo $currentDir === 'courts' ? 'active' : ''; ?>">
            <i class="fas fa-gavel"></i>
            <span>ศาล</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>admin/disbursement_types/" 
           class="nav-link <?php echo $currentDir === 'disbursement_types' ? 'active' : ''; ?>">
            <i class="fas fa-receipt"></i>
            <span>ประเภทการเบิก</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>admin/lawyers/" 
           class="nav-link <?php echo $currentDir === 'lawyers' ? 'active' : ''; ?>">
            <i class="fas fa-user-tie"></i>
            <span>ทนายความ</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>admin/account_officers/" 
           class="nav-link <?php echo $currentDir === 'account_officers' ? 'active' : ''; ?>">
            <i class="fas fa-user-cog"></i>
            <span>A/O</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>admin/offices/" 
           class="nav-link <?php echo $currentDir === 'offices' ? 'active' : ''; ?>">
            <i class="fas fa-building"></i>
            <span>สำนักงานเจ้าของงาน</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>admin/backup/" 
           class="nav-link <?php echo $currentDir === 'backup' ? 'active' : ''; ?>">
            <i class="fas fa-database"></i>
            <span>สำรอง/นำเข้าข้อมูล</span>
        </a>
        <?php endif; ?>
        
        <!-- Cases Menu -->
        <div class="menu-header">งานคดี</div>
        
        <a href="<?php echo BASE_URL; ?>cases/" 
           class="nav-link <?php echo $currentDir === 'cases' ? 'active' : ''; ?>">
            <i class="fas fa-folder-open"></i>
            <span>รับงาน/คดี</span>
        </a>
        
        <!-- Disbursements Menu -->
        <div class="menu-header">การเบิก</div>
        
        <a href="<?php echo BASE_URL; ?>disbursements/" 
           class="nav-link <?php echo $currentDir === 'disbursements' ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>ใบเบิก</span>
        </a>
        
        <!-- Reports Menu -->
        <div class="menu-header">รายงาน</div>
        
        <a href="<?php echo BASE_URL; ?>reports/case_report.php" 
           class="nav-link <?php echo $currentPage === 'case_report' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span>รายงานคดี</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>reports/disbursement_report.php" 
           class="nav-link <?php echo $currentPage === 'disbursement_report' ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie"></i>
            <span>รายงานการเบิก</span>
        </a>
    </div>
</div>
