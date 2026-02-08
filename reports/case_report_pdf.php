<?php
/**
 * Case Report PDF Export
 * กระดาษ Legal 8.5x14 นิ้ว แนวนอน
 */
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../vendor/autoload.php';
requireLogin();

// Filter params
$filters = [
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'work_type_id' => $_GET['work_type_id'] ?? '',
    'court_id' => $_GET['court_id'] ?? '',
    'lawyer_id' => $_GET['lawyer_id'] ?? '',
    'ao_id' => $_GET['ao_id'] ?? '',
    'office_id' => $_GET['office_id'] ?? '',
    'status' => $_GET['status'] ?? ''
];

// Report date (วันที่ออกรายงาน)
$reportDate = $_GET['report_date'] ?? date('Y-m-d');
$reportDateObj = new DateTime($reportDate);

// Get office name for title
$officeName = 'ทั้งหมด';
if ($filters['office_id']) {
    $officeData = getById('offices', $filters['office_id']);
    $officeName = $officeData['name'] ?? 'ทั้งหมด';
}

// Query data
$db = getDB();
$sql = "SELECT c.*, wt.name as work_type_name, o.name as office_name, ct.name as court_name,
               CONCAT(l.prefix, l.firstname, ' ', l.lastname) as lawyer_name, ao.name as ao_name
        FROM cases c
        LEFT JOIN work_types wt ON c.work_type_id = wt.id
        LEFT JOIN offices o ON c.office_id = o.id
        LEFT JOIN courts ct ON c.court_id = ct.id
        LEFT JOIN lawyers l ON c.lawyer_id = l.id
        LEFT JOIN account_officers ao ON c.ao_id = ao.id
        WHERE 1=1";
$params = [];

if ($filters['date_from']) { $sql .= " AND c.received_date >= ?"; $params[] = $filters['date_from']; }
if ($filters['date_to']) { $sql .= " AND c.received_date <= ?"; $params[] = $filters['date_to']; }
if ($filters['work_type_id']) { $sql .= " AND c.work_type_id = ?"; $params[] = $filters['work_type_id']; }
if ($filters['court_id']) { $sql .= " AND c.court_id = ?"; $params[] = $filters['court_id']; }
if ($filters['lawyer_id']) { $sql .= " AND c.lawyer_id = ?"; $params[] = $filters['lawyer_id']; }
if ($filters['ao_id']) { $sql .= " AND c.ao_id = ?"; $params[] = $filters['ao_id']; }
if ($filters['office_id']) { $sql .= " AND c.office_id = ?"; $params[] = $filters['office_id']; }
if ($filters['status']) { $sql .= " AND c.status = ?"; $params[] = $filters['status']; }

$sql .= " ORDER BY c.received_date DESC, c.debtor_code";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$cases = $stmt->fetchAll();

// Create PDF - Legal size (8.5 x 14 inches) Landscape
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('freeserif', 'B', 14);
        $this->Cell(0, 6, 'ลัลฌา สำนักงานกฏหมายและธุรกิจ', 0, 1, 'C');
        $this->SetFont('freeserif', 'B', 12);
        $reportDateObj = $GLOBALS['reportDateObj'];
        $this->Cell(0, 5, 'รายงานผลการดำเนินคดีเบี่ยง ณ วันที่ ' . $reportDateObj->format('j') . ' ' . getThaiMonth((int)$reportDateObj->format('n')) . ' ' . ($reportDateObj->format('Y') + 543) . ' สำนักงาน: ' . $GLOBALS['officeName'], 0, 1, 'C');
        $this->Ln(1);
    }
    
    public function Footer() {
        $this->SetY(-18);
        $this->SetFont('freeserif', '', 9);
        $this->Cell(0, 4, '........................................................................', 0, 1, 'R');
        $this->Cell(0, 4, '(นางสาวลัลฌา ขัดสุรินทร์)', 0, 1, 'R');
        $this->Cell(0, 4, 'หัวหน้าสำนักงาน', 0, 1, 'R');
    }
}

function getThaiMonth($m) {
    $months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 
               'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    return $months[$m];
}

function formatThaiDate($date) {
    if (!$date) return '-';
    $d = new DateTime($date);
    return $d->format('j') . ' ' . getThaiMonth((int)$d->format('n')) . ' ' . ($d->format('Y') + 543 - 2500);
}

// Create new PDF document - Legal size landscape (355.6mm x 215.9mm)
$pdf = new MYPDF('L', 'mm', 'LEGAL', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('LanLaw System');
$pdf->SetAuthor('LanLaw');
$pdf->SetTitle('รายงานคดี');

// Set margins (left, top, right) - Legal landscape width = 355.6mm, usable = 335.6mm
$pdf->SetMargins(10, 32, 10);
$pdf->SetHeaderMargin(8);
$pdf->SetFooterMargin(20);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 25);

// Add Thai font
$pdf->SetFont('freeserif', '', 11);

// Add a page
$pdf->AddPage();

// Table header - ปรับให้ตรงกับรูปแบบที่แนบมา
$header = ['ลำดับ', 'รหัสลูกหนี้', 'ชื่อลูกหนี้', 'PORT', 'ประเภทงาน', 'ให้ดำเนินการภายในวันที่', 'วันรับเรื่อง', 'วันฟ้อง', 'วันที่พิพากษา', 'ศาล', 'คดีดำ', 'คดีแดง', 'วันที่ดำเนินการปัจจุบัน', 'การดำเนินการปัจจุบัน', 'วันที่ดำเนินการต่อไป', 'การดำเนินการต่อไป', 'ทนายความ A/O', 'ปัญหา/หมายเหตุ'];

// Column widths - Legal landscape usable width = 335.6mm (total = 335.6)
$w = [10, 22, 38, 14, 22, 20, 18, 18, 18, 22, 16, 16, 18, 26, 18, 26, 20, 20];
// Total = 362 -> need adjustment

// Recalculate to fit 335mm
$w = [8, 20, 34, 12, 20, 18, 16, 16, 18, 20, 15, 15, 17, 24, 17, 24, 18, 23];
// Total = 335

// Print header
$pdf->SetFillColor(220, 220, 220);
$pdf->SetFont('freeserif', 'B', 7);

for ($i = 0; $i < count($header); $i++) {
    $pdf->MultiCell($w[$i], 10, $header[$i], 1, 'C', true, 0);
}
$pdf->Ln();

// Print data
$pdf->SetFont('freeserif', '', 7);
$pdf->SetFillColor(255, 255, 255);

foreach ($cases as $i => $c) {
    $rowData = [
        $i + 1,
        $c['debtor_code'] ?? '-',
        mb_substr($c['debtor_name'] ?? '-', 0, 22),
        $c['port'] ?? '-',
        mb_substr($c['work_type_name'] ?? '-', 0, 12),
        formatThaiDate($c['due_date']),
        formatThaiDate($c['received_date']),
        formatThaiDate($c['filing_date']),
        formatThaiDate($c['judgment_date']),
        mb_substr($c['court_name'] ?? '-', 0, 12),
        $c['black_case'] ?? '-',
        $c['red_case'] ?? '-',
        formatThaiDate($c['current_action_date']),
        mb_substr($c['current_action'] ?? '-', 0, 15),
        formatThaiDate($c['next_action_date']),
        mb_substr($c['next_action'] ?? '-', 0, 15),
        mb_substr($c['ao_name'] ?? '-', 0, 12),
        mb_substr($c['problems_remarks'] ?? '-', 0, 14)
    ];
    
    // Calculate row height
    $maxLines = 1;
    foreach ($rowData as $idx => $val) {
        $lines = $pdf->getNumLines($val, $w[$idx]);
        if ($lines > $maxLines) $maxLines = $lines;
    }
    $rowHeight = max(5, $maxLines * 4);
    
    // Check page break
    if ($pdf->GetY() + $rowHeight > $pdf->getPageHeight() - 28) {
        $pdf->AddPage();
        // Reprint header
        $pdf->SetFont('freeserif', 'B', 7);
        $pdf->SetFillColor(220, 220, 220);
        for ($j = 0; $j < count($header); $j++) {
            $pdf->MultiCell($w[$j], 10, $header[$j], 1, 'C', true, 0);
        }
        $pdf->Ln();
        $pdf->SetFont('freeserif', '', 7);
        $pdf->SetFillColor(255, 255, 255);
    }
    
    // Print row
    $startY = $pdf->GetY();
    $startX = $pdf->GetX();
    
    for ($j = 0; $j < count($rowData); $j++) {
        $align = ($j == 0) ? 'C' : 'L';
        $pdf->MultiCell($w[$j], $rowHeight, $rowData[$j], 1, $align, false, 0);
    }
    $pdf->Ln();
}

// Output PDF
$pdf->Output('case_report_' . date('Ymd_His') . '.pdf', 'D');
