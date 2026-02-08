<?php
/**
 * AJAX - Search Cases for Disbursement
 */
require_once '../../config/config.php';
require_once '../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$keyword = trim($_GET['q'] ?? $_GET['term'] ?? '');

if (mb_strlen($keyword) < 1) {
    echo json_encode(['results' => []]);
    exit;
}

try {
    $db = getDB();
    $searchKeyword = '%' . $keyword . '%';
    
    $stmt = $db->prepare("
        SELECT c.id, c.debtor_code, c.debtor_name, c.black_case, c.red_case, c.court_id,
               ct.name as court_name,
               ao.id as ao_id, ao.name as ao_name
        FROM cases c
        LEFT JOIN courts ct ON c.court_id = ct.id
        LEFT JOIN account_officers ao ON c.ao_id = ao.id
        WHERE c.debtor_code LIKE ? OR c.debtor_name LIKE ?
        ORDER BY c.debtor_code
        LIMIT 20
    ");
    $stmt->execute([$searchKeyword, $searchKeyword]);
    $cases = $stmt->fetchAll();

    $results = [];
    foreach ($cases as $case) {
        $results[] = [
            'id' => $case['id'],
            'text' => $case['debtor_code'] . ' - ' . $case['debtor_name'],
            'debtor_code' => $case['debtor_code'],
            'debtor_name' => $case['debtor_name'],
            'black_case' => $case['black_case'] ?? '',
            'red_case' => $case['red_case'] ?? '',
            'court_id' => $case['court_id'],
            'court_name' => $case['court_name'] ?? '',
            'ao_id' => $case['ao_id'],
            'ao_name' => $case['ao_name'] ?? ''
        ];
    }

    echo json_encode(['results' => $results], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['results' => [], 'error' => $e->getMessage()]);
}
