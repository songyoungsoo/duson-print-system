<?php
/**
 * 고객 견적서 응답 API
 * 승인(accept), 거절(reject), 협의요청(negotiate) 처리
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../db.php';

// JSON 입력 받기
$input = json_decode(file_get_contents('php://input'), true);

$token = $input['token'] ?? '';
$action = $input['action'] ?? '';
$notes = trim($input['notes'] ?? '');

// 유효성 검사
if (empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '토큰이 필요합니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!in_array($action, ['accept', 'reject', 'negotiate'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '유효하지 않은 액션입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 견적서 조회
$stmt = mysqli_prepare($db, "SELECT * FROM quotations WHERE public_token = ?");
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quotation = mysqli_fetch_assoc($result);

if (!$quotation) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => '견적서를 찾을 수 없습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 이미 응답한 경우
if (in_array($quotation['customer_response'], ['accepted', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => '이미 응답하신 견적서입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 만료된 경우
if (strtotime($quotation['expires_at']) < strtotime('today')) {
    echo json_encode(['success' => false, 'message' => '만료된 견적서입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 응답 처리
$response_map = [
    'accept' => 'accepted',
    'reject' => 'rejected',
    'negotiate' => 'negotiate'
];
$customer_response = $response_map[$action];

// 상태 업데이트
$new_status = ($action === 'accept') ? 'accepted' : (($action === 'reject') ? 'rejected' : 'sent');

$update_stmt = mysqli_prepare($db, "
    UPDATE quotations
    SET customer_response = ?,
        response_date = NOW(),
        response_notes = ?,
        status = ?
    WHERE id = ?
");
mysqli_stmt_bind_param($update_stmt, "sssi", $customer_response, $notes, $new_status, $quotation['id']);
$update_result = mysqli_stmt_execute($update_stmt);

if (!$update_result) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '응답 처리 중 오류가 발생했습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 관리자에게 알림 이메일 발송
$admin_email = 'dsp1830@naver.com';
$action_text = [
    'accept' => '✅ 승인',
    'reject' => '❌ 거절',
    'negotiate' => '💬 협의요청'
];

$email_subject = "[두손기획인쇄] 견적서 {$action_text[$action]} - {$quotation['quotation_no']}";
$email_body = "
<html>
<body style='font-family: sans-serif;'>
<h2>{$action_text[$action]} 알림</h2>
<table style='border-collapse: collapse;'>
<tr><td style='padding:8px;border:1px solid #ddd;'>견적번호</td><td style='padding:8px;border:1px solid #ddd;'><strong>{$quotation['quotation_no']}</strong></td></tr>
<tr><td style='padding:8px;border:1px solid #ddd;'>담당자</td><td style='padding:8px;border:1px solid #ddd;'>{$quotation['customer_name']}</td></tr>
<tr><td style='padding:8px;border:1px solid #ddd;'>이메일</td><td style='padding:8px;border:1px solid #ddd;'>{$quotation['customer_email']}</td></tr>
<tr><td style='padding:8px;border:1px solid #ddd;'>금액</td><td style='padding:8px;border:1px solid #ddd;'>" . number_format($quotation['total_price']) . "원</td></tr>
<tr><td style='padding:8px;border:1px solid #ddd;'>응답일시</td><td style='padding:8px;border:1px solid #ddd;'>" . date('Y-m-d H:i:s') . "</td></tr>
" . ($notes ? "<tr><td style='padding:8px;border:1px solid #ddd;'>고객 메모</td><td style='padding:8px;border:1px solid #ddd;'>{$notes}</td></tr>" : "") . "
</table>
<p style='margin-top:20px;'><a href='http://dsp114.com/admin/mlangprintauto/quotation_list.php'>관리자 페이지에서 확인하기</a></p>
</body>
</html>
";

// PHPMailer로 관리자 알림 발송 (선택적)
try {
    require_once __DIR__ . '/../../mlangorder_printauto/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/../../mlangorder_printauto/PHPMailer/SMTP.php';
    require_once __DIR__ . '/../../mlangorder_printauto/PHPMailer/Exception.php';

    use PHPMailer\PHPMailer\PHPMailer;

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.naver.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'dsp1830';
    $mail->Password = 'MC8T8Z83B149';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('dsp1830@naver.com', '두손기획인쇄');
    $mail->addAddress($admin_email);
    $mail->isHTML(true);
    $mail->Subject = $email_subject;
    $mail->Body = $email_body;

    $mail->send();
} catch (Exception $e) {
    // 알림 실패해도 응답 처리는 성공으로 처리
    error_log("견적서 알림 이메일 발송 실패: " . $e->getMessage());
}

// 성공 응답
$messages = [
    'accept' => '견적서를 승인하셨습니다. 담당자가 곧 주문 처리를 진행합니다.',
    'reject' => '견적서를 거절하셨습니다. 더 나은 조건으로 새 견적을 요청하실 수 있습니다.',
    'negotiate' => '협의 요청이 접수되었습니다. 담당자가 곧 연락드리겠습니다.'
];

echo json_encode([
    'success' => true,
    'message' => $messages[$action],
    'action' => $action
], JSON_UNESCAPED_UNICODE);
?>
