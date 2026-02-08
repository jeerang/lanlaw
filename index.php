<?php
/**
 * Login Page
 * ระบบงานเอกสารสำนักงานทนายความ
 */

require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . 'dashboard.php');
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'คำขอไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
        } else {
            if (authenticate($username, $password)) {
                redirect(BASE_URL . 'dashboard.php');
            } else {
                $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --accent-color: #3498db;
        }
        
        body {
            font-family: 'Sarabun', sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, #1a252f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        
        .login-card {
            background: #fff;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo i {
            font-size: 50px;
            color: var(--accent-color);
            margin-bottom: 15px;
        }
        
        .login-logo h4 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .login-logo p {
            color: #7f8c8d;
            font-size: 13px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-floating .form-control {
            border-radius: 10px;
            height: 55px;
            border: 2px solid #e9ecef;
        }
        
        .form-floating .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.15);
        }
        
        .btn-login {
            background: var(--accent-color);
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(52, 152, 219, 0.4);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: rgba(255,255,255,0.6);
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <i class="fas fa-balance-scale"></i>
                <h4><?php echo SITE_NAME; ?></h4>
                <p><?php echo COMPANY_NAME; ?></p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo e($error); ?>
            </div>
            <?php endif; ?>
            
            <?php $flash = getFlashMessage(); if ($flash): ?>
            <div class="alert alert-<?php echo e($flash['type']); ?>">
                <?php echo e($flash['message']); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?php echo csrfField(); ?>
                <div class="form-floating">
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="ชื่อผู้ใช้" value="<?php echo e($_POST['username'] ?? ''); ?>" required autofocus>
                    <label for="username"><i class="fas fa-user me-2"></i>ชื่อผู้ใช้</label>
                </div>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="รหัสผ่าน" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>รหัสผ่าน</label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ
                </button>
            </form>
        </div>
        
        <p class="footer-text">
            &copy; <?php echo date('Y'); ?> <?php echo SITE_SHORT_NAME; ?> v<?php echo SITE_VERSION; ?>
        </p>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
