<?php
/**
 * Update Disbursement Status
 */
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();

$id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? '';
$allowed = ['processing', 'paid', 'rejected'];

if (!in_array($status, $allowed)) {
    setFlashMessage('danger', 'สถานะไม่ถูกต้อง');
    redirect(BASE_URL . 'disbursements/');
}

$disbursement = getById('disbursements', $id);
if (!$disbursement) {
    setFlashMessage('danger', 'ไม่พบข้อมูล');
    redirect(BASE_URL . 'disbursements/');
}

$db = getDB();
$stmt = $db->prepare("UPDATE disbursements SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

logActivity('update', 'disbursements', $id, $disbursement, ['status' => $status]);

global $DISBURSEMENT_STATUSES;
setFlashMessage('success', 'เปลี่ยนสถานะเป็น "' . $DISBURSEMENT_STATUSES[$status] . '" สำเร็จ');
redirect(BASE_URL . 'disbursements/view.php?id=' . $id);
