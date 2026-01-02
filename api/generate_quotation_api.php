<?php
/**
 * 견적서 PDF 생성 API
 */

if (!isset($_GET['download']) || $_GET['download'] != '1') {
    header('Content-Type: application/json; charset=UTF-8');
}

require_once __DIR__ . "/../db.php";

$order_no = $_GET['order_no'] ?? '';
$download = isset($_GET['download']) && $_GET['download'] == '1';

// 입력 검증
if (empty($order_no) || !preg_match('/^[0-9]+$/', $order_no)) {
    echo json_encode([
        'success' => false,
        'error' => '잘못된 주문번호',
        'order_no' => $order_no
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 주문 조회
$query = "SELECT * FROM mlangorder_printauto WHERE no = ?";
$stmt = mysqli_prepare($db, $query);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'DB error'], JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $order_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    echo json_encode(['success' => false, 'error' => '주문 없음'], JSON_UNESCAPED_UNICODE);
    exit;
}

// JSON 생성
$json_file = "/tmp/order_" . $order_no . ".json";
file_put_contents($json_file, json_encode($order, JSON_UNESCAPED_UNICODE));

// PDF 생성 (절대 경로 사용)
$python = "/var/www/html/.venv/bin/python3";
$script = "/var/www/html/scripts/generate_quotation_from_db.py";
$pdf_out = "/tmp/quote_" . $order_no . ".pdf";

// 실행 가능 여부 확인
if (!file_exists($python)) {
    echo json_encode(['success' => false, 'error' => 'Python not found: ' . $python], JSON_UNESCAPED_UNICODE);
    @unlink($json_file);
    exit;
}

if (!is_executable($python)) {
    echo json_encode(['success' => false, 'error' => 'Python not executable'], JSON_UNESCAPED_UNICODE);
    @unlink($json_file);
    exit;
}

// 명령 실행
$cmd = escapeshellarg($python) . " " . escapeshellarg($script) . " " . escapeshellarg($json_file) . " " . escapeshellarg($pdf_out) . " 2>&1";
$output = [];
$return_code = 0;
$result_output = null;
$last_line = passthru($cmd, $return_code);

@unlink($json_file);

// PDF 확인
if (!file_exists($pdf_out)) {
    echo json_encode([
        'success' => false,
        'error' => 'PDF 생성 실패',
        'return_code' => $return_code,
        'command' => $cmd,
        'python_exists' => file_exists($python),
        'python_executable' => is_executable($python),
        'script_exists' => file_exists($script)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 다운로드 모드
if ($download) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="quotation_' . $order_no . '.pdf"');
    header('Content-Length: ' . filesize($pdf_out));
    readfile($pdf_out);
    @unlink($pdf_out);
    exit;
}

// JSON 응답
$size = filesize($pdf_out);
$data = base64_encode(file_get_contents($pdf_out));
@unlink($pdf_out);

echo json_encode([
    'success' => true,
    'order_no' => $order_no,
    'customer' => $order['name'] ?? '',
    'pdf_size_kb' => round($size / 1024, 2),
    'pdf_data' => $data
], JSON_UNESCAPED_UNICODE);
?>
