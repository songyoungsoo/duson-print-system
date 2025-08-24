<?php
/**
 * 주문내역 이메일 발송 API
 * 사무용 표형태 주문완료 페이지용
 * Created: 2025년 8월 (AI Assistant)
 */

// 에러 출력 제어 - JSON 응답 보장
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// 출력 버퍼 시작 - 불필요한 출력 방지
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// 데이터베이스 연결
include "../db.php";

// PHPMailer 라이브러리 포함
require 'mailer.lib250802.php';

try {
    // POST 데이터 받기
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('잘못된 요청 데이터입니다.');
    }
    
    $orders = $data['orders'] ?? '';
    $email = $data['email'] ?? '';
    $name = $data['name'] ?? '';
    $orderList = $data['orderList'] ?? [];
    $totalAmount = $data['totalAmount'] ?? 0;
    $totalAmountVat = $data['totalAmountVat'] ?? 0;
    
    if (empty($email) || empty($name) || empty($orderList)) {
        throw new Exception('필수 정보가 누락되었습니다.');
    }
    
    // 이메일 유효성 검사
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('올바른 이메일 주소가 아닙니다.');
    }
    
    // 카테고리 이름 조회 함수
    function getCategoryName($connect, $category_no) {
        if (!$category_no) return '';
        
        $query = "SELECT title FROM MlangPrintAuto_transactionCate WHERE no = ? LIMIT 1";
        $stmt = mysqli_prepare($connect, $query);
        if (!$stmt) return $category_no;
        
        mysqli_stmt_bind_param($stmt, 's', $category_no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['title'];
        }
        
        mysqli_stmt_close($stmt);
        return $category_no;
    }
    
    // HTML 이메일 템플릿 생성
    $emailHtml = '
    <!DOCTYPE html>
    <html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>주문 완료 안내</title>
        <style>
            body {
                font-family: "Noto Sans KR", "Malgun Gothic", sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 20px;
                background-color: #f8f9fa;
            }
            .email-container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .email-header {
                background: linear-gradient(135deg, #E6F3FF 0%, #F0E6FF 100%);
                padding: 30px 20px;
                text-align: center;
                border-bottom: 1px solid #e1e8ed;
            }
            .email-header h1 {
                margin: 0 0 10px 0;
                font-size: 1.8rem;
                color: #2c3e50;
                font-weight: 700;
            }
            .email-header p {
                margin: 0;
                font-size: 1.1rem;
                color: #566a7e;
            }
            .summary-section {
                padding: 20px;
                background: #f8f9fa;
                border-bottom: 1px solid #e1e8ed;
            }
            .summary-stats {
                display: flex;
                justify-content: space-around;
                text-align: center;
                flex-wrap: wrap;
                gap: 20px;
            }
            .summary-stat {
                flex: 1;
                min-width: 120px;
            }
            .summary-stat .value {
                font-size: 1.5rem;
                font-weight: 700;
                color: #2c3e50;
                margin-bottom: 5px;
            }
            .summary-stat .label {
                font-size: 0.9rem;
                color: #566a7e;
            }
            .order-table {
                width: 100%;
                border-collapse: collapse;
                margin: 0;
            }
            .order-table thead th {
                background: linear-gradient(135deg, #E6F3FF 0%, #E6FFF0 100%);
                color: #2c3e50;
                font-weight: 700;
                padding: 12px 8px;
                font-size: 0.9rem;
                text-align: center;
                border-bottom: 2px solid #e1e8ed;
            }
            .order-table tbody tr:nth-child(even) {
                background: #FFFCE6;
            }
            .order-table tbody tr:nth-child(odd) {
                background: #E6FFF0;
            }
            .order-table td {
                padding: 12px 8px;
                border-bottom: 1px solid #e1e8ed;
                font-size: 0.85rem;
                vertical-align: top;
            }
            .col-order-no {
                width: 10%;
                text-align: center;
                font-weight: 600;
                color: #667eea;
            }
            .col-product {
                width: 25%;
                font-weight: 600;
            }
            .col-details {
                width: 35%;
                line-height: 1.4;
            }
            .col-quantity {
                width: 10%;
                text-align: center;
            }
            .col-price {
                width: 15%;
                text-align: right;
                font-weight: 600;
                color: #e74c3c;
            }
            .col-date {
                width: 15%;
                text-align: center;
                font-size: 0.8rem;
            }
            .product-options {
                margin-top: 5px;
                padding: 8px;
                background: rgba(255,255,255,0.7);
                border-radius: 4px;
                font-size: 0.75rem;
                line-height: 1.3;
            }
            .option-item {
                display: inline-block;
                margin-right: 10px;
                margin-bottom: 3px;
                padding: 2px 6px;
                background: rgba(102, 126, 234, 0.1);
                border-radius: 3px;
                color: #566a7e;
            }
            .request-note {
                margin-top: 5px;
                padding: 6px;
                background: #FFFCE6;
                border-left: 3px solid #ffc107;
                border-radius: 3px;
                font-size: 0.75rem;
                color: #856404;
            }
            .info-section {
                padding: 20px;
                display: flex;
                gap: 40px;
                background: #f8f9fa;
                border-top: 1px solid #e1e8ed;
            }
            .info-column {
                flex: 1;
            }
            .info-column h3 {
                margin: 0 0 15px 0;
                font-size: 1.1rem;
                color: #2c3e50;
                font-weight: 600;
            }
            .info-row {
                display: flex;
                margin-bottom: 8px;
                font-size: 0.9rem;
            }
            .info-label {
                width: 80px;
                font-weight: 600;
                color: #566a7e;
            }
            .info-value {
                flex: 1;
                color: #2c3e50;
            }
            .footer-section {
                padding: 20px;
                text-align: center;
                background: linear-gradient(135deg, #E6F3FF 0%, #F0E6FF 100%);
                color: #566a7e;
                font-size: 0.85rem;
                line-height: 1.5;
            }
            .company-info {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid #e1e8ed;
            }
            @media (max-width: 600px) {
                .summary-stats {
                    flex-direction: column;
                }
                .info-section {
                    flex-direction: column;
                    gap: 20px;
                }
                .order-table {
                    font-size: 0.75rem;
                }
                .order-table td {
                    padding: 8px 4px;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <!-- 헤더 -->
            <div class="email-header">
                <h1>🎉 주문이 완료되었습니다!</h1>
                <p>' . htmlspecialchars($name) . ' 고객님, 소중한 주문 감사합니다.</p>
            </div>
            
            <!-- 요약 섹션 -->
            <div class="summary-section">
                <div class="summary-stats">
                    <div class="summary-stat">
                        <div class="value">' . count($orderList) . '건</div>
                        <div class="label">주문 건수</div>
                    </div>
                    <div class="summary-stat">
                        <div class="value">' . number_format($totalAmount) . '원</div>
                        <div class="label">총 주문금액</div>
                    </div>
                    <div class="summary-stat">
                        <div class="value">' . number_format($totalAmountVat) . '원</div>
                        <div class="label">VAT 포함 총액</div>
                    </div>
                </div>
            </div>
            
            <!-- 주문 상세 테이블 -->
            <table class="order-table">
                <thead>
                    <tr>
                        <th class="col-order-no">주문번호</th>
                        <th class="col-product">상품명</th>
                        <th class="col-details">상세 옵션</th>
                        <th class="col-quantity">수량</th>
                        <th class="col-price">금액(VAT포함)</th>
                        <th class="col-date">주문일시</th>
                    </tr>
                </thead>
                <tbody>';
    
    // 각 주문 항목 처리
    foreach ($orderList as $order) {
        $emailHtml .= '<tr>';
        
        // 주문번호
        $emailHtml .= '<td class="col-order-no">#' . htmlspecialchars($order['no']) . '</td>';
        
        // 상품명
        $emailHtml .= '<td class="col-product">' . htmlspecialchars($order['Type']) . '</td>';
        
        // 상세 옵션
        $emailHtml .= '<td class="col-details">';
        
        if (!empty($order['Type_1'])) {
            $type_data = $order['Type_1'];
            $json_data = json_decode($type_data, true);
            
            $emailHtml .= '<div class="product-options">';
            
            if ($json_data && is_array($json_data)) {
                // JSON 데이터 처리
                $product_type = $json_data['product_type'] ?? '';
                
                switch($product_type) {
                    case 'sticker':
                        // 실제 데이터 구조에 맞게 수정
                        $details = $json_data['order_details'] ?? $json_data;
                        if (isset($details['jong'])) $emailHtml .= '<span class="option-item">재질: ' . htmlspecialchars($details['jong']) . '</span>';
                        if (isset($details['garo']) && isset($details['sero'])) {
                            $emailHtml .= '<span class="option-item">크기: ' . htmlspecialchars($details['garo']) . '×' . htmlspecialchars($details['sero']) . 'mm</span>';
                        }
                        if (isset($details['mesu'])) $emailHtml .= '<span class="option-item">수량: ' . number_format($details['mesu']) . '매</span>';
                        if (isset($details['uhyung'])) $emailHtml .= '<span class="option-item">편집: ' . htmlspecialchars($details['uhyung']) . '</span>';
                        if (isset($details['domusong'])) $emailHtml .= '<span class="option-item">모양: ' . htmlspecialchars($details['domusong']) . '</span>';
                        break;
                        
                    case 'envelope':
                        if (isset($json_data['MY_type'])) $emailHtml .= '<span class="option-item">타입: ' . getCategoryName($db, $json_data['MY_type']) . '</span>';
                        if (isset($json_data['MY_Fsd'])) $emailHtml .= '<span class="option-item">용지: ' . getCategoryName($db, $json_data['MY_Fsd']) . '</span>';
                        if (isset($json_data['MY_amount'])) $emailHtml .= '<span class="option-item">수량: ' . number_format($json_data['MY_amount']) . '매</span>';
                        if (isset($json_data['POtype'])) $emailHtml .= '<span class="option-item">인쇄: ' . ($json_data['POtype'] == '1' ? '단면' : '양면') . '</span>';
                        break;
                        
                    case 'namecard':
                        if (isset($json_data['MY_type'])) $emailHtml .= '<span class="option-item">타입: ' . getCategoryName($db, $json_data['MY_type']) . '</span>';
                        if (isset($json_data['Section'])) $emailHtml .= '<span class="option-item">용지: ' . getCategoryName($db, $json_data['Section']) . '</span>';
                        if (isset($json_data['MY_amount'])) $emailHtml .= '<span class="option-item">수량: ' . number_format($json_data['MY_amount']) . '매</span>';
                        if (isset($json_data['POtype'])) $emailHtml .= '<span class="option-item">인쇄: ' . ($json_data['POtype'] == '1' ? '단면' : '양면') . '</span>';
                        break;
                        
                    default:
                        foreach ($json_data as $key => $value) {
                            if (!empty($value) && $key != 'product_type') {
                                $display_key = ucfirst($key);
                                $display_value = is_numeric($value) && in_array($key, ['MY_type', 'MY_Fsd', 'PN_type']) 
                                    ? getCategoryName($db, $value) 
                                    : $value;
                                $emailHtml .= '<span class="option-item">' . htmlspecialchars($display_key) . ': ' . htmlspecialchars($display_value) . '</span>';
                            }
                        }
                        break;
                }
            } else {
                // 일반 텍스트 데이터 처리 (전단지 등)
                $lines = explode("\n", $type_data);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        $emailHtml .= '<span class="option-item">' . htmlspecialchars($line) . '</span>';
                    }
                }
            }
            
            $emailHtml .= '</div>';
        }
        
        // 요청사항 표시
        if (!empty($order['cont'])) {
            $emailHtml .= '<div class="request-note">';
            $emailHtml .= '<strong>💬 요청사항:</strong><br>';
            $emailHtml .= nl2br(htmlspecialchars($order['cont']));
            $emailHtml .= '</div>';
        }
        
        $emailHtml .= '</td>';
        
        // 수량
        $quantity = 1;
        if (!empty($order['Type_1'])) {
            $json_data = json_decode($order['Type_1'], true);
            if ($json_data && is_array($json_data)) {
                // JSON 데이터에서 수량 추출
                $details = $json_data['order_details'] ?? $json_data;
                if (isset($details['MY_amount'])) {
                    $quantity = $details['MY_amount'];
                } elseif (isset($details['mesu'])) {
                    $quantity = $details['mesu'];
                }
            } else {
                // 일반 텍스트에서 수량 추출
                if (preg_match('/수량:\s*([0-9.]+)매/', $order['Type_1'], $matches)) {
                    $quantity = floatval($matches[1]);
                }
            }
        }
        $emailHtml .= '<td class="col-quantity">' . number_format($quantity) . '</td>';
        
        // 금액
        $emailHtml .= '<td class="col-price">' . number_format($order['money_5']) . '원</td>';
        
        // 주문일시
        $order_date = '';
        if (isset($order['date']) && !empty($order['date']) && $order['date'] !== '0000-00-00 00:00:00') {
            $order_date = date('m/d H:i', strtotime($order['date']));
        } else {
            $order_date = date('m/d H:i'); // 현재 시간
        }
        $emailHtml .= '<td class="col-date">' . $order_date . '</td>';
        
        $emailHtml .= '</tr>';
    }
    
    $emailHtml .= '</tbody>
            </table>
            
            <!-- 고객정보 및 입금안내 -->
            <div class="info-section">
                <div class="info-column">
                    <h3>👤 고객 정보</h3>
                    <div class="info-row">
                        <div class="info-label">성명:</div>
                        <div class="info-value">' . htmlspecialchars($orderList[0]['name']) . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">이메일:</div>
                        <div class="info-value">' . htmlspecialchars($orderList[0]['email']) . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">연락처:</div>
                        <div class="info-value">';
    
    if (!empty($orderList[0]['Hendphone'])) {
        $emailHtml .= htmlspecialchars($orderList[0]['Hendphone']);
    } elseif (!empty($orderList[0]['phone'])) {
        $emailHtml .= htmlspecialchars($orderList[0]['phone']);
    }
    
    $emailHtml .= '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">주소:</div>
                        <div class="info-value">';
    
    if (!empty($orderList[0]['zip'])) {
        $emailHtml .= '(' . htmlspecialchars($orderList[0]['zip']) . ') ';
    }
    $emailHtml .= htmlspecialchars($orderList[0]['zip1'] . ' ' . $orderList[0]['zip2']);
    
    $emailHtml .= '</div>
                    </div>
                </div>
                
                <div class="info-column">
                    <h3>💳 입금 안내</h3>
                    <div class="info-row">
                        <div class="info-label">예금주:</div>
                        <div class="info-value">두손기획인쇄 차경선</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">국민은행:</div>
                        <div class="info-value">999-1688-2384</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">신한은행:</div>
                        <div class="info-value">110-342-543507</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">농협:</div>
                        <div class="info-value">301-2632-1829</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">카드결제:</div>
                        <div class="info-value">1688-2384 전화</div>
                    </div>
                </div>
            </div>
            
            <!-- 푸터 -->
            <div class="footer-section">
                <p><strong>⚠️ 입금 확인 후 작업이 시작됩니다.</strong></p>
                <p>입금자명을 주문자명과 동일하게 해주세요.</p>
                
                <div class="company-info">
                    <p><strong>두손기획인쇄</strong></p>
                    <p>📞 02-2632-1830, 1688-2384</p>
                    <p>📍 서울 영등포구 영등포로 36길 9, 송호빌딩 1F</p>
                    <p>🌐 www.dsp114.com</p>
                </div>
            </div>
        </div>
    </body>
    </html>';
    
    // 이메일 발송
    $subject = '[두손기획인쇄] 주문 완료 안내 - ' . $name . ' 고객님 (' . count($orderList) . '건)';
    $from_name = '두손기획인쇄';
    $from_email = 'dsp1830@naver.com';
    
    // 로컬 환경에서는 이메일 발송을 시뮬레이션
    if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
        // 로컬 환경: 이메일 발송 시뮬레이션
        $result = true;
        
        // 이메일 내용을 파일로 저장 (디버그용)
        $email_file = "debug_email_" . date('Y-m-d_H-i-s') . ".html";
        file_put_contents($email_file, $emailHtml);
        
    } else {
        // 실제 서버 환경: PHPMailer 사용
        $result = mailer($from_name, $from_email, $email, $subject, $emailHtml, 1);
    }
    
    if ($result) {
        // 이메일 발송 로그 저장
        if ($db) {
            $log_query = "INSERT INTO email_send_log (order_numbers, recipient_email, recipient_name, subject, sent_at, status) VALUES (?, ?, ?, ?, NOW(), 'success')";
            $stmt = mysqli_prepare($db, $log_query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssss", $orders, $email, $name, $subject);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        
        // 출력 버퍼 정리
        ob_clean();
        
        echo json_encode([
            'success' => true,
            'message' => '주문내역이 성공적으로 이메일로 발송되었습니다.',
            'data' => [
                'email' => $email,
                'orderCount' => count($orderList),
                'sentAt' => date('Y-m-d H:i:s')
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('이메일 발송에 실패했습니다. 잠시 후 다시 시도해주세요.');
    }
    
} catch (Exception $e) {
    // 에러 로그 저장
    if (isset($db) && isset($orders) && isset($email) && isset($name)) {
        $log_query = "INSERT INTO email_send_log (order_numbers, recipient_email, recipient_name, subject, sent_at, status, error_message) VALUES (?, ?, ?, ?, NOW(), 'failed', ?)";
        $stmt = mysqli_prepare($db, $log_query);
        if ($stmt) {
            $subject = isset($subject) ? $subject : '주문 완료 안내';
            mysqli_stmt_bind_param($stmt, "sssss", $orders, $email, $name, $subject, $e->getMessage());
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    // 출력 버퍼 정리
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
} finally {
    // 데이터베이스 연결 종료
    if (isset($db)) {
        mysqli_close($db);
    }
}
?>