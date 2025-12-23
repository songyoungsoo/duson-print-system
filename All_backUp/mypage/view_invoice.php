<?php
/**
 * 세금계산서 보기 (출력용)
 * 경로: /mypage/view_invoice.php
 */

session_start();

// 로그인 확인
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/member/login.php';</script>";
    exit;
}

// 데이터베이스 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/TaxInvoiceTemplate.php';

$user_id = $_SESSION['user_id'];
$invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$invoice_id) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

// 세금계산서 정보 조회
$query = "SELECT ti.*, u.business_number as buyer_business_number, u.business_name as buyer_business_name,
                 u.name as buyer_name, u.phone as buyer_phone, u.email as buyer_email,
                 CONCAT(u.address, ' ', u.detail_address) as buyer_address,
                 o.name as item_name, o.Type as item_type
          FROM tax_invoices ti
          LEFT JOIN users u ON ti.user_id = u.id
          LEFT JOIN mlangorder_printauto o ON ti.order_no = o.no
          WHERE ti.id = ? AND ti.user_id = ?";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "ii", $invoice_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$invoice = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$invoice) {
    echo "<script>alert('세금계산서를 찾을 수 없습니다.'); history.back();</script>";
    exit;
}

// 세금계산서가 발급되지 않은 경우
if ($invoice['status'] != 'issued') {
    echo "<script>alert('아직 발급되지 않은 세금계산서입니다.'); history.back();</script>";
    exit;
}

// 템플릿 생성 및 출력
$template = new TaxInvoiceTemplate($invoice);
echo $template->generate();

mysqli_close($db);
?>
