<?php
/**
 * 주문내역 이메일 발송 API
 * 간소화 버전 - 2026-02-05
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

include "../db.php";
require_once __DIR__ . "/../includes/quantity_formatter.php";
require 'mailer.lib.php';

try {
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
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('올바른 이메일 주소가 아닙니다.');
    }
    
    // 간소화된 HTML 이메일 템플릿
    $emailHtml = '<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주문 완료 안내</title>
</head>
<body style="margin:0;padding:20px;background-color:#f8f9fa;font-family:-apple-system,BlinkMacSystemFont,sans-serif;">
    <div style="max-width:700px;margin:0 auto;background:white;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
        
        <!-- 헤더 -->
        <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:30px 20px;text-align:center;">
            <h1 style="margin:0 0 10px 0;font-size:24px;color:white;">🎉 주문이 완료되었습니다!</h1>
            <p style="margin:0;font-size:16px;color:rgba(255,255,255,0.9);">' . htmlspecialchars($name) . ' 고객님, 소중한 주문 감사합니다.</p>
        </div>
        
        <!-- 요약 -->
        <div style="padding:20px;background:#f8f9fa;border-bottom:1px solid #e1e8ed;text-align:center;">
            <span style="display:inline-block;margin:0 20px;">
                <strong style="font-size:24px;color:#2c3e50;">' . count($orderList) . '건</strong><br>
                <span style="font-size:14px;color:#566a7e;">주문 건수</span>
            </span>
            <span style="display:inline-block;margin:0 20px;">
                <strong style="font-size:24px;color:#e74c3c;">' . number_format($totalAmountVat) . '원</strong><br>
                <span style="font-size:14px;color:#566a7e;">결제 금액 (VAT포함)</span>
            </span>
            <span style="display:inline-block;margin:0 20px;">
                <strong style="font-size:16px;color:#566a7e;">' . date('Y.m.d H:i') . '</strong><br>
                <span style="font-size:14px;color:#566a7e;">주문 일시</span>
            </span>
        </div>
        
        <!-- 주문 목록 -->
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:linear-gradient(135deg,#E6F3FF 0%,#E6FFF0 100%);">
                    <th style="padding:12px 8px;text-align:center;font-size:14px;color:#2c3e50;border-bottom:2px solid #e1e8ed;">주문번호</th>
                    <th style="padding:12px 8px;text-align:left;font-size:14px;color:#2c3e50;border-bottom:2px solid #e1e8ed;">상품명</th>
                    <th style="padding:12px 8px;text-align:left;font-size:14px;color:#2c3e50;border-bottom:2px solid #e1e8ed;">상세</th>
                    <th style="padding:12px 8px;text-align:right;font-size:14px;color:#2c3e50;border-bottom:2px solid #e1e8ed;">금액</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($orderList as $index => $order) {
        $bgColor = ($index % 2 == 0) ? '#E6FFF0' : '#FFFCE6';
        
        // Type_1에서 상세 정보 추출
        $details = '';
        if (!empty($order['Type_1'])) {
            $json_data = json_decode($order['Type_1'], true);
            if ($json_data && is_array($json_data)) {
                $parts = [];
                if (!empty($json_data['spec_material'])) $parts[] = $json_data['spec_material'];
                if (!empty($json_data['spec_size'])) $parts[] = $json_data['spec_size'];
                if (!empty($json_data['spec_sides'])) $parts[] = $json_data['spec_sides'];
                if (!empty($json_data['quantity_display'])) $parts[] = $json_data['quantity_display'];
                $details = implode(' / ', $parts);
            } else {
                $details = mb_substr(strip_tags($order['Type_1']), 0, 50);
            }
        }
        
        $emailHtml .= '<tr style="background:' . $bgColor . ';">
            <td style="padding:12px 8px;text-align:center;border-bottom:1px solid #e1e8ed;font-weight:600;color:#667eea;">#' . htmlspecialchars($order['no']) . '</td>
            <td style="padding:12px 8px;border-bottom:1px solid #e1e8ed;font-weight:600;">' . htmlspecialchars($order['Type']) . '</td>
            <td style="padding:12px 8px;border-bottom:1px solid #e1e8ed;font-size:13px;color:#566a7e;">' . htmlspecialchars($details) . '</td>
            <td style="padding:12px 8px;text-align:right;border-bottom:1px solid #e1e8ed;font-weight:600;color:#e74c3c;">' . number_format($order['money_5'] ?? 0) . '원</td>
        </tr>';
    }
    
    $emailHtml .= '</tbody>
        </table>
        
        <!-- 고객정보 & 입금안내 -->
        <div style="padding:20px;background:#f8f9fa;">
            <table style="width:100%;">
                <tr>
                    <td style="width:50%;vertical-align:top;padding-right:20px;">
                        <h3 style="margin:0 0 15px 0;font-size:16px;color:#2c3e50;">👤 고객 정보</h3>
                        <p style="margin:5px 0;font-size:14px;"><strong>성명:</strong> ' . htmlspecialchars($orderList[0]['name'] ?? $name) . '</p>
                        <p style="margin:5px 0;font-size:14px;"><strong>이메일:</strong> ' . htmlspecialchars($orderList[0]['email'] ?? $email) . '</p>
                        <p style="margin:5px 0;font-size:14px;"><strong>연락처:</strong> ' . htmlspecialchars($orderList[0]['Hendphone'] ?? $orderList[0]['phone'] ?? '') . '</p>
                        <p style="margin:5px 0;font-size:14px;"><strong>주소:</strong> ' . htmlspecialchars(($orderList[0]['zip1'] ?? '') . ' ' . ($orderList[0]['zip2'] ?? '')) . '</p>
                    </td>
                    <td style="width:50%;vertical-align:top;padding-left:20px;border-left:1px solid #e1e8ed;">
                        <h3 style="margin:0 0 15px 0;font-size:16px;color:#2c3e50;">💳 입금 안내</h3>
                        <p style="margin:5px 0;font-size:14px;"><strong>예금주:</strong> 두손기획인쇄 차경선</p>
                        <p style="margin:5px 0;font-size:14px;"><strong>국민은행:</strong> 999-1688-2384</p>
                        <p style="margin:5px 0;font-size:14px;"><strong>신한은행:</strong> 110-342-543507</p>
                        <p style="margin:5px 0;font-size:14px;"><strong>농협:</strong> 301-2632-1830-11</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- 푸터 -->
        <div style="padding:20px;text-align:center;background:linear-gradient(135deg,#E6F3FF 0%,#F0E6FF 100%);">
            <p style="margin:0 0 10px 0;font-weight:bold;color:#e74c3c;">⚠️ 입금 확인 후 작업이 시작됩니다.</p>
            <p style="margin:0 0 15px 0;color:#566a7e;font-size:14px;">입금자명을 주문자명과 동일하게 해주세요.</p>
            <div style="padding-top:15px;border-top:1px solid #e1e8ed;">
                <p style="margin:5px 0;font-weight:bold;color:#2c3e50;">두손기획인쇄</p>
                <p style="margin:5px 0;font-size:14px;color:#566a7e;">📞 02-2632-1830, 1688-2384</p>
                <p style="margin:5px 0;font-size:14px;color:#566a7e;">📍 서울 영등포구 영등포로 36길 9, 송호빌딩 1F</p>
            </div>
        </div>
        
    </div>
</body>
</html>';
    
    $subject = '[두손기획인쇄] 주문 완료 안내 - ' . $name . ' 고객님 (' . count($orderList) . '건)';
    $from_name = '두손기획인쇄';
    $from_email = 'dsp1830@naver.com';
    
    $result = mailer($from_name, $from_email, $email, $subject, $emailHtml, 1, "");
    
    if ($result) {
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => '주문내역이 이메일로 발송되었습니다.',
            'data' => [
                'email' => $email,
                'orderCount' => count($orderList),
                'sentAt' => date('Y-m-d H:i:s')
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('이메일 발송에 실패했습니다.');
    }
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
}
?>
