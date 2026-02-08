<?php
/**
 * Logout Page
 * ระบบงานเอกสารสำนักงานทนายความ
 */

require_once 'config/config.php';
require_once 'includes/auth.php';

logout();

setFlashMessage('success', 'ออกจากระบบสำเร็จ');
redirect(BASE_URL . 'index.php');
?>
