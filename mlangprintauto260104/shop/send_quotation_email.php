<?php
/**
 * 견적서 이메일 발송 API
 * POST JSON 데이터를 받아 견적서를 저장하고 이메일로 발송
 */

// PHPMailer 네임스페이스 (파일 로드 전 선언)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();
header('Content-Type: application/json; charset=utf-8');

// 관리자 권한 확인
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => '관리자 권한이 필요합니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 에러 응답 함수
function jsonError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// 성공 응답 함수
function jsonSuccess($data) {
    echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

// 숫자를 한글 금액으로 변환
function numberToKorean($number) {
    $number = intval($number);
    if ($number == 0) return '영원';

    $units = ['', '만', '억', '조'];
    $digits = ['', '일', '이', '삼', '사', '오', '육', '칠', '팔', '구'];
    $subUnits = ['', '십', '백', '천'];

    $result = '';
    $unitIndex = 0;

    while ($number > 0) {
        $chunk = $number % 10000;
        $number = intval($number / 10000);

        if ($chunk > 0) {
            $chunkStr = '';
            $subIndex = 0;
            while ($chunk > 0) {
                $digit = $chunk % 10;
                $chunk = intval($chunk / 10);
                if ($digit > 0) {
                    $digitStr = ($digit == 1 && $subIndex > 0) ? '' : $digits[$digit];
                    $chunkStr = $digitStr . $subUnits[$subIndex] . $chunkStr;
                }
                $subIndex++;
            }
            $result = $chunkStr . $units[$unitIndex] . $result;
        }
        $unitIndex++;
    }

    return $result . '원';
}

// DB 연결
require_once __DIR__ . '/../../db.php';

if (!$db) {
    jsonError('데이터베이스 연결 실패', 500);
}

mysqli_set_charset($db, 'utf8mb4');

// POST JSON 데이터 파싱
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    jsonError('잘못된 요청 형식입니다.');
}

// 필수 필드 검증
$customerName = trim($data['customerName'] ?? '');
$recipientEmail = trim($data['recipientEmail'] ?? '');

if (empty($customerName)) {
    jsonError('고객명은 필수입니다.');
}

if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
    jsonError('올바른 이메일 주소를 입력해주세요.');
}

// 세션 ID
$session_id = session_id();

// 데이터 추출
$customerEmail = trim($data['customerEmail'] ?? ''); // 고객 이메일 (선택사항, CC용)
$deliveryType = trim($data['deliveryType'] ?? '');
$deliveryPrice = intval($data['deliveryPrice'] ?? 0);
$deliveryVat = intval($data['deliveryVat'] ?? round($deliveryPrice * 0.1));
$customItems = $data['customItems'] ?? [];
$totalSupply = intval($data['totalSupply'] ?? 0);
$totalVat = intval($data['totalVat'] ?? 0);
$totalPrice = intval($data['totalPrice'] ?? 0);

// 장바구니 아이템 조회
$cart_items = [];
$cart_query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no ASC";
$stmt = mysqli_prepare($db, $cart_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $cart_items[] = $row;
    }
    mysqli_stmt_close($stmt);
}

if (empty($cart_items)) {
    jsonError('장바구니가 비어있습니다.');
}

// 견적번호 생성 (QT-YYYYMMDD-NNN)
$today = date('Ymd');
$quotation_no_prefix = "QT-{$today}-";

$count_query = "SELECT COUNT(*) as cnt FROM quotations WHERE quotation_no LIKE ?";
$stmt = mysqli_prepare($db, $count_query);
$like_pattern = $quotation_no_prefix . '%';
mysqli_stmt_bind_param($stmt, "s", $like_pattern);
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$count_row = mysqli_fetch_assoc($count_result);
$next_number = intval($count_row['cnt']) + 1;
mysqli_stmt_close($stmt);

$quotation_no = $quotation_no_prefix . str_pad($next_number, 3, '0', STR_PAD_LEFT);

// 중복 체크
$check_query = "SELECT id FROM quotations WHERE quotation_no = ?";
while (true) {
    $stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $quotation_no);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($check_result) == 0) {
        mysqli_stmt_close($stmt);
        break;
    }
    mysqli_stmt_close($stmt);
    $next_number++;
    $quotation_no = $quotation_no_prefix . str_pad($next_number, 3, '0', STR_PAD_LEFT);
}

// JSON 변환
$cart_items_json = json_encode($cart_items, JSON_UNESCAPED_UNICODE);
$custom_items_json = json_encode($customItems, JSON_UNESCAPED_UNICODE);

$created_by = intval($_SESSION['user_id']);
$expires_at = date('Y-m-d', strtotime('+7 days'));

// 견적서 저장
$insert_query = "INSERT INTO quotations (
    quotation_no, session_id, customer_name, customer_email,
    cart_items_json, delivery_type, delivery_price, delivery_vat, custom_items_json,
    total_supply, total_vat, total_price,
    status, created_by, expires_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'sent', ?, ?)";

$stmt = mysqli_prepare($db, $insert_query);
if (!$stmt) {
    jsonError('쿼리 준비 실패: ' . mysqli_error($db), 500);
}

// 14 params: quotation_no(s) + session_id(s) + customer_name(s) + customer_email(s) +
// cart_items_json(s) + delivery_type(s) + delivery_price(i) + delivery_vat(i) + custom_items_json(s) +
// total_supply(i) + total_vat(i) + total_price(i) + created_by(i) + expires_at(s)
mysqli_stmt_bind_param($stmt, "ssssssiiisiiiis",
    $quotation_no,
    $session_id,
    $customerName,
    $recipientEmail,
    $cart_items_json,
    $deliveryType,
    $deliveryPrice,
    $deliveryVat,
    $custom_items_json,
    $totalSupply,
    $totalVat,
    $totalPrice,
    $created_by,
    $expires_at
);

if (!mysqli_stmt_execute($stmt)) {
    jsonError('견적서 저장 실패: ' . mysqli_stmt_error($stmt), 500);
}

$quotation_id = mysqli_insert_id($db);
mysqli_stmt_close($stmt);

// 이메일 HTML 생성
$email_date = date('Y년 m월 d일');
$koreanTotal = numberToKorean($totalPrice);

$itemRows = '';
$itemNo = 1;
foreach ($cart_items as $item) {
    $productType = $item['product_type'] ?? '상품';

    // 제품별 규격 생성
    switch ($productType) {
        case 'sticker':
            // 일반 스티커: jong(재질), garo x sero, domusong(모양)
            $jong = $item['jong'] ?? '';
            // "jil " 접두어 제거
            $jong = preg_replace('/^jil\s*/i', '', $jong);
            $specParts = [];
            if (!empty($jong)) $specParts[] = '재질: ' . $jong;
            if (!empty($item['garo']) && !empty($item['sero'])) {
                $specParts[] = '크기: ' . $item['garo'] . '×' . $item['sero'] . 'mm';
            }
            // domusong 처리: "00000 사각" → "사각", "0" → 제외
            $domusong = $item['domusong'] ?? '';
            $domusong = preg_replace('/^[0\s]+/', '', $domusong); // 앞의 0과 공백 제거
            if (!empty($domusong)) {
                $specParts[] = '모양: ' . $domusong;
            }
            $spec = implode(' / ', $specParts);
            break;
        case 'msticker':
        case 'msticker_01':
            // 자석스티커: MY_type, Section 사용 (코드번호)
            $specParts = [];
            if (!empty($item['MY_type'])) $specParts[] = '종류: ' . $item['MY_type'];
            if (!empty($item['Section'])) $specParts[] = '규격: ' . $item['Section'];
            if (!empty($item['POtype'])) $specParts[] = '인쇄: ' . ($item['POtype'] == '1' ? '단면' : '양면');
            $spec = implode(' / ', $specParts);
            break;
        case 'namecard':
            $specParts = [];
            if (!empty($item['MY_type'])) $specParts[] = '타입: ' . $item['MY_type'];
            if (!empty($item['Section'])) $specParts[] = '재질: ' . $item['Section'];
            if (!empty($item['POtype'])) $specParts[] = '인쇄: ' . ($item['POtype'] == '1' ? '단면' : '양면');
            $spec = implode(' / ', $specParts);
            break;
        case 'envelope':
            $specParts = [];
            if (!empty($item['MY_type'])) $specParts[] = '종류: ' . $item['MY_type'];
            if (!empty($item['Section'])) $specParts[] = '재질: ' . $item['Section'];
            if (!empty($item['POtype'])) $specParts[] = '인쇄: ' . ($item['POtype'] == '1' ? '단면' : '양면');
            $spec = implode(' / ', $specParts);
            break;
        default:
            $specParts = [];
            if (!empty($item['MY_type'])) $specParts[] = $item['MY_type'];
            if (!empty($item['Section'])) $specParts[] = $item['Section'];
            if (!empty($item['ordertype'])) {
                $orderTypeText = $item['ordertype'] == 'total' ? '디자인+인쇄' : ($item['ordertype'] == 'print' ? '인쇄만' : $item['ordertype']);
                $specParts[] = $orderTypeText;
            }
            $spec = implode(' / ', $specParts);
    }
    // 빈 슬래시 정리
    $spec = preg_replace('/\s*\/\s*\/\s*/', ' / ', $spec);
    $spec = trim($spec, ' /');

    $qty = $item['MY_amount'] ?? 1;
    $supply = intval($item['st_price'] ?? 0);

    // 전단지는 매수 기준 단가 계산 (소수점 1자리)
    if (($item['product_type'] ?? '') === 'inserted' && !empty($item['mesu']) && intval($item['mesu']) > 0) {
        $price = round($supply / intval($item['mesu']), 1);
        $priceDisplay = number_format($price, 1);
    } else {
        $price = $supply;
        $priceDisplay = number_format($price);
    }

    $itemRows .= "<tr>
        <td style='border: 1px solid #000; padding: 6px; text-align: center;'>{$itemNo}</td>
        <td style='border: 1px solid #000; padding: 6px;'>{$productType}</td>
        <td style='border: 1px solid #000; padding: 6px;'>{$spec}</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: center;'>{$qty}</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: center;'>부</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: right;'>{$priceDisplay}</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: right;'>" . number_format($supply) . " 원</td>
        <td style='border: 1px solid #000; padding: 6px;'></td>
    </tr>";
    $itemNo++;
}

// 배송비 행 (공급가 + VAT)
if (!empty($deliveryType) && $deliveryPrice > 0) {
    $deliveryTotal = $deliveryPrice + $deliveryVat;
    $itemRows .= "<tr>
        <td style='border: 1px solid #000; padding: 6px; text-align: center;'>-</td>
        <td style='border: 1px solid #000; padding: 6px;'>택배선불</td>
        <td style='border: 1px solid #000; padding: 6px;'>{$deliveryType} (공급가 " . number_format($deliveryPrice) . "원 + VAT)</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: center;'>1</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: center;'>식</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: right;'>" . number_format($deliveryPrice) . "</td>
        <td style='border: 1px solid #000; padding: 6px; text-align: right;'>" . number_format($deliveryPrice) . " 원</td>
        <td style='border: 1px solid #000; padding: 6px;'>+VAT " . number_format($deliveryVat) . "원</td>
    </tr>";
}

// 추가 항목 행
foreach ($customItems as $customItem) {
    if (!empty($customItem['item']) && intval($customItem['price']) > 0) {
        $customSupply = intval($customItem['qty']) * intval($customItem['price']);
        $itemRows .= "<tr>
            <td style='border: 1px solid #000; padding: 6px; text-align: center;'>+</td>
            <td style='border: 1px solid #000; padding: 6px;'>" . htmlspecialchars($customItem['item']) . "</td>
            <td style='border: 1px solid #000; padding: 6px;'>" . htmlspecialchars($customItem['spec'] ?? '') . "</td>
            <td style='border: 1px solid #000; padding: 6px; text-align: center;'>" . intval($customItem['qty']) . "</td>
            <td style='border: 1px solid #000; padding: 6px; text-align: center;'>" . htmlspecialchars($customItem['unit'] ?? '개') . "</td>
            <td style='border: 1px solid #000; padding: 6px; text-align: right;'>" . number_format($customItem['price']) . "</td>
            <td style='border: 1px solid #000; padding: 6px; text-align: right;'>" . number_format($customSupply) . " 원</td>
            <td style='border: 1px solid #000; padding: 6px;'></td>
        </tr>";
    }
}

$emailBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>견적서 - {$quotation_no}</title>
</head>
<body style="font-family: 'Noto Sans KR', 'Noto Sans', sans-serif; padding: 12px; max-width: 750px; margin: 0 auto; font-size: 13px; line-height: 1.5; background: #f8f9fa; color: #333;">
    <div style="background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
    <div style="text-align: center; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid #2c3e50;">
        <h1 style="margin: 0; font-size: 18px; font-weight: 700; letter-spacing: -0.3px; color: #2c3e50;">견 적 서</h1>
    </div>

    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <table style="width: 100%;">
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px; background: #f5f5f5; width: 80px;">견적번호</td>
                        <td style="border: 1px solid #000; padding: 6px;">{$quotation_no}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px; background: #f5f5f5;">견적일자</td>
                        <td style="border: 1px solid #000; padding: 6px;">{$email_date}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px; background: #f5f5f5;">유효기간</td>
                        <td style="border: 1px solid #000; padding: 6px;">발행일로부터 7일</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px; background: #f5f5f5;">고객명</td>
                        <td style="border: 1px solid #000; padding: 6px;">{$customerName} 귀하</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                <table style="width: 100%;">
                    <tr>
                        <td colspan="2" style="border: 1px solid #000; padding: 6px; background: #f5f5f5; text-align: center;">공급자</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; width: 80px;">등록번호</td>
                        <td style="border: 1px solid #000; padding: 4px;">607-26-76968</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px;">상호</td>
                        <td style="border: 1px solid #000; padding: 4px;">두손기획인쇄</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px;">대표자</td>
                        <td style="border: 1px solid #000; padding: 4px;">이두선</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px;">주소</td>
                        <td style="border: 1px solid #000; padding: 4px;">부산시 북구 금곡동 144-30 102호</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px;">전화</td>
                        <td style="border: 1px solid #000; padding: 4px;">051-341-1830</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table style="width: 100%; text-align: center; margin-bottom: 20px; border: 2px solid #000;">
        <tr>
            <td style="padding: 15px; font-size: 16px;">
                <strong>합계금액(VAT포함):</strong>
                <span style="font-size: 18px; color: #c00;">{$koreanTotal}</span>
                (₩ {number_format($totalPrice)})
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
            <tr style="background: #f5f5f5;">
                <th style="border: 1px solid #000; padding: 8px; width: 40px;">NO</th>
                <th style="border: 1px solid #000; padding: 8px;">품목</th>
                <th style="border: 1px solid #000; padding: 8px;">규격 및 사양</th>
                <th style="border: 1px solid #000; padding: 8px; width: 50px;">수량</th>
                <th style="border: 1px solid #000; padding: 8px; width: 40px;">단위</th>
                <th style="border: 1px solid #000; padding: 8px; width: 80px;">단가</th>
                <th style="border: 1px solid #000; padding: 8px; width: 100px;">공급가액</th>
                <th style="border: 1px solid #000; padding: 8px; width: 60px;">비고</th>
            </tr>
        </thead>
        <tbody>
            {$itemRows}
        </tbody>
        <tfoot>
            <tr style="background: #fffde7; font-weight: bold;">
                <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right;">합계</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">{number_format($totalSupply)} 원</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right;">부가세 (10%)</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right;">{number_format($totalVat)} 원</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
            <tr style="background: #e8f5e9; font-weight: bold;">
                <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: right;">총 합계 (VAT 포함)</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right; color: #c00;">{number_format($totalPrice)} 원</td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
        </tfoot>
    </table>

    <table style="width: 100%; margin-top: 20px; border: 1px solid #000;">
        <tr>
            <td style="padding: 10px; background: #f5f5f5; width: 80px; border-right: 1px solid #000;">입금계좌</td>
            <td style="padding: 10px;">
                <strong>농협은행 301-0185-6461-71</strong> 예금주: 이두선 (두손기획인쇄)
            </td>
        </tr>
    </table>

    <p style="margin-top: 30px; text-align: center; color: #666; font-size: 12px;">
        본 견적서는 발행일로부터 7일간 유효합니다.<br>
        문의사항은 051-341-1830으로 연락 부탁드립니다.
    </p>
</body>
</html>
HTML;

// number_format 호출 처리 (PHP 문자열 내 호출 불가하므로 미리 변환)
$emailBody = str_replace('{number_format($totalSupply)}', number_format($totalSupply), $emailBody);
$emailBody = str_replace('{number_format($totalVat)}', number_format($totalVat), $emailBody);
$emailBody = str_replace('{number_format($totalPrice)}', number_format($totalPrice), $emailBody);

// PHPMailer 로드 및 이메일 발송 (네임스페이스 버전)
require_once __DIR__ . '/../../mlangorder_printauto/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../mlangorder_printauto/PHPMailer/SMTP.php';
require_once __DIR__ . '/../../mlangorder_printauto/PHPMailer/Exception.php';

try {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->Host = 'smtp.naver.com';
    $mail->Port = 465;
    $mail->SMTPSecure = 'ssl';
    $mail->SMTPAuth = true;
    $mail->Username = 'dsp1830';
    $mail->Password = 'MC8T8Z83B149';
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('dsp1830@naver.com', '두손기획인쇄');
    $mail->addReplyTo('dsp1830@naver.com', '두손기획인쇄');
    $mail->addAddress($recipientEmail, $customerName);

    // 고객 이메일이 있고 유효하면 CC로 추가
    if (!empty($customerEmail) && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        $mail->addCC($customerEmail, $customerName . ' (고객)');
    }

    $mail->Subject = "[두손기획인쇄] 견적서 ({$quotation_no})";
    $mail->isHTML(true);
    $mail->msgHTML($emailBody);
    $mail->AltBody = "두손기획인쇄 견적서\n견적번호: {$quotation_no}\n합계금액: " . number_format($totalPrice) . "원 (VAT포함)";

    $emailSent = $mail->send();
    $emailError = $mail->ErrorInfo;
} catch (Exception $e) {
    $emailSent = false;
    $emailError = $e->getMessage();
}

// 이메일 발송 로그 저장
$email_status = $emailSent ? 'sent' : 'failed';
$email_subject = "[두손기획인쇄] 견적서 ({$quotation_no})";

$log_query = "INSERT INTO quotation_emails (
    quotation_id, quotation_no, recipient_email, recipient_name,
    subject, status, error_message, sent_by
) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($db, $log_query);
mysqli_stmt_bind_param($stmt, "issssssi",
    $quotation_id,
    $quotation_no,
    $recipientEmail,
    $customerName,
    $email_subject,
    $email_status,
    $emailError,
    $created_by
);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($emailSent) {
    jsonSuccess([
        'quotation_no' => $quotation_no,
        'quotation_id' => $quotation_id,
        'email' => $recipientEmail,
        'message' => '견적서가 이메일로 발송되었습니다.'
    ]);
} else {
    // 견적서는 저장됨, 이메일만 실패
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'quotation_no' => $quotation_no,
        'message' => '이메일 발송 실패: ' . $emailError
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
