<?php
session_start();
$session_id = session_id();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

// TCPDF 라이브러리 포함 (Composer 또는 직접 다운로드)
// require_once('../../vendor/tcpdf/tcpdf.php'); // Composer 사용 시
// 또는 직접 다운로드한 경우:
// require_once('../../lib/tcpdf/tcpdf.php');

// 임시로 간단한 HTML to PDF 방식 사용 (나중에 TCPDF로 교체 가능)
require_once('../../includes/functions.php');
require_once('../includes/company_info.php');

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, 'utf8');
}

// ID로 한글명 가져오기 함수
function getKoreanName($connect, $id) {
    if (!$connect || !$id) {
        return $id;
    }
    
    $query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? LIMIT 1";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return $id;
    }
    
    mysqli_stmt_bind_param($stmt, 's', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['title'];
    }
    
    mysqli_stmt_close($stmt);
    return $id;
}

// 장바구니 데이터 가져오기
function getCartItemsForQuote($connect, $session_id) {
    $query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no DESC";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return [];
    }
    
    mysqli_stmt_bind_param($stmt, 's', $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $items;
}

// 고객 정보 받기
$customer_info = [
    'name' => $_GET['customer_name'] ?? '고객님',
    'phone' => $_GET['customer_phone'] ?? '',
    'company' => $_GET['customer_company'] ?? '',
    'email' => $_GET['customer_email'] ?? '',
    'memo' => $_GET['quote_memo'] ?? ''
];

// 장바구니 데이터 조회
$cart_items = getCartItemsForQuote($connect, $session_id);

if (empty($cart_items)) {
    die('장바구니가 비어있습니다.');
}

// 견적서 번호 생성
$quote_number = 'Q' . date('YmdHis') . '_' . substr(md5($session_id), 0, 4);

// 견적서 발송 로그 저장
logQuoteGeneration($connect, $quote_number, $customer_info, $cart_items, $session_id);

// 관리자 알림 발송 (선택적)
if (COMPANY_EMAIL) {
    sendAdminQuoteNotification($customer_info, $quote_number, $cart_items);
}

// PDF 생성을 위한 HTML 준비
$html = generateQuoteHTML($cart_items, $connect, $customer_info);

// PDF 생성 방법 1: wkhtmltopdf 사용 (서버에 설치 필요)
if (function_exists('shell_exec') && !empty(shell_exec('which wkhtmltopdf'))) {
    $pdf_path = generatePDFWithWkhtmltopdf($html, $quote_number, $customer_info);
} else {
    // PDF 생성 방법 2: 브라우저 인쇄 기능 활용
    generatePrintableHTML($html);
    $pdf_path = null; // HTML 방식에서는 PDF 파일 없음
}

// 고객에게 견적서 PDF 메일 발송
if ($pdf_path && $customer_info['email']) {
    sendCustomerQuotePDF($customer_info, $quote_number, $cart_items, $pdf_path);
}

function generateQuoteHTML($cart_items, $connect, $customer_info) {
    $total_price = 0;
    $total_vat = 0;
    $quote_date = date('Y년 m월 d일');
    $quote_number = 'Q' . date('YmdHis');
    
    $html = '
    <!DOCTYPE html>
    <html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>견적서 - ' . $quote_number . '</title>
        <style>
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
            
            body {
                font-family: "Malgun Gothic", "맑은 고딕", Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
                margin: 20px;
                color: #333;
            }
            
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #2c5aa0;
                padding-bottom: 20px;
            }
            
            .header h1 {
                font-size: 28px;
                color: #2c5aa0;
                margin: 0;
                font-weight: bold;
            }
            
            .company-info {
                margin-top: 10px;
                font-size: 14px;
                color: #666;
            }
            
            .quote-info {
                display: flex;
                justify-content: space-between;
                margin-bottom: 30px;
                background: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
            }
            
            .quote-info div {
                flex: 1;
            }
            
            .quote-info strong {
                color: #2c5aa0;
            }
            
            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 30px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .items-table th {
                background: #2c5aa0;
                color: white;
                padding: 12px 8px;
                text-align: center;
                font-weight: bold;
                border: 1px solid #ddd;
            }
            
            .items-table td {
                padding: 10px 8px;
                text-align: center;
                border: 1px solid #ddd;
                vertical-align: middle;
            }
            
            .items-table tbody tr:nth-child(even) {
                background: #f8f9fa;
            }
            
            .items-table tbody tr:hover {
                background: #e3f2fd;
            }
            
            .product-name {
                text-align: left !important;
                font-weight: bold;
                color: #2c5aa0;
            }
            
            .product-details {
                text-align: left !important;
                font-size: 11px;
                color: #666;
                line-height: 1.3;
            }
            
            .price {
                text-align: right !important;
                font-weight: bold;
                color: #d32f2f;
            }
            
            .total-section {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 5px;
                margin-bottom: 30px;
            }
            
            .total-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
                font-size: 14px;
            }
            
            .total-row.final {
                border-top: 2px solid #2c5aa0;
                padding-top: 10px;
                font-size: 18px;
                font-weight: bold;
                color: #2c5aa0;
            }
            
            .footer {
                text-align: center;
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
                color: #666;
                font-size: 11px;
            }
            
            .print-button {
                position: fixed;
                top: 20px;
                right: 20px;
                background: #2c5aa0;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            }
            
            .print-button:hover {
                background: #1e3d72;
            }
            
            @page {
                margin: 1cm;
            }
        </style>
    </head>
    <body>
        <button class="print-button no-print" onclick="window.print()">🖨️ 인쇄하기</button>
        
        <div class="header">
            <h1>견 적 서</h1>
            ' . getCompanyInfoHTML('header') . '
        </div>
        
        <div class="quote-info">
            <div>
                <strong>견적번호:</strong> ' . $quote_number . '<br>
                <strong>견적일자:</strong> ' . $quote_date . '<br>
                <strong>유효기간:</strong> ' . date('Y년 m월 d일', strtotime('+30 days')) . '
            </div>
            <div>
                <strong>고객명:</strong> ' . htmlspecialchars($customer_info['name']) . '<br>';
                if (!empty($customer_info['company'])) {
                    $html .= '<strong>회사명:</strong> ' . htmlspecialchars($customer_info['company']) . '<br>';
                }
                if (!empty($customer_info['phone'])) {
                    $html .= '<strong>연락처:</strong> ' . htmlspecialchars($customer_info['phone']) . '<br>';
                }
                if (!empty($customer_info['email'])) {
                    $html .= '<strong>이메일:</strong> ' . htmlspecialchars($customer_info['email']);
                }
            $html .= '</div>
        </div>';
    
    // 요청사항이 있으면 추가 표시
    if (!empty($customer_info['memo'])) {
        $html .= '
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin-bottom: 20px;">
            <strong>📝 요청사항:</strong><br>
            ' . nl2br(htmlspecialchars($customer_info['memo'])) . '
        </div>';
    }
    
    $html .= '
        
        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">번호</th>
                    <th width="25%">상품명</th>
                    <th width="35%">상품 상세</th>
                    <th width="10%">수량</th>
                    <th width="12%">단가</th>
                    <th width="13%">금액</th>
                </tr>
            </thead>
            <tbody>';
    
    $item_number = 1;
    foreach ($cart_items as $item) {
        $product_name = getProductName($item);
        $product_details = getProductDetails($item, $connect);
        $quantity = getQuantity($item);
        $unit_price = intval($item['st_price'] ?? 0);
        $total_item_price = $unit_price;
        
        $total_price += $total_item_price;
        
        $html .= '
                <tr>
                    <td>' . $item_number . '</td>
                    <td class="product-name">' . htmlspecialchars($product_name) . '</td>
                    <td class="product-details">' . $product_details . '</td>
                    <td>' . htmlspecialchars($quantity) . '</td>
                    <td class="price">' . number_format($unit_price) . '원</td>
                    <td class="price">' . number_format($total_item_price) . '원</td>
                </tr>';
        
        $item_number++;
    }
    
    $vat = intval($total_price * 0.1);
    $total_with_vat = $total_price + $vat;
    
    $html .= '
            </tbody>
        </table>
        
        <div class="total-section">
            <div class="total-row">
                <span>공급가액:</span>
                <span>' . number_format($total_price) . '원</span>
            </div>
            <div class="total-row">
                <span>부가세 (10%):</span>
                <span>' . number_format($vat) . '원</span>
            </div>
            <div class="total-row final">
                <span>총 견적금액:</span>
                <span>' . number_format($total_with_vat) . '원</span>
            </div>
        </div>
        
        ' . getPaymentInfoHTML('quote') . '
        
        <div class="footer">
            <p style="font-size: 11px; margin: 8px 0 4px 0;"><strong>※ 안내사항</strong></p>
            <p style="font-size: 10px; margin: 2px 0;">• 본 견적서는 ' . date('Y년 m월 d일', strtotime('+30 days')) . '까지 유효합니다. • 실제 주문 시 디자인 파일 및 세부 사양에 따라 금액이 변동될 수 있습니다.</p>
            <p style="font-size: 10px; margin: 2px 0 8px 0;">' . getCompanyInfoHTML('contact') . '</p>
            ' . getCompanyInfoHTML('footer') . '
        </div>
        
        <script>
            // 페이지 로드 후 자동으로 인쇄 대화상자 표시 (선택사항)
            // window.onload = function() { window.print(); };
        </script>
    </body>
    </html>';
    
    return $html;
}

function getProductName($item) {
    $product_type = $item['product_type'] ?? 'unknown';
    
    switch ($product_type) {
        case 'sticker':
            return '일반 스티커';
        case 'namecard':
            return '명함';
        case 'cadarok':
            return '카다록/리플렛';
        case 'msticker':
            return '자석 스티커';
        case 'inserted':
            return '전단지';
        case 'littleprint':
            return '소량 포스터';
        case 'envelope':
            return '봉투';
        case 'merchandisebond':
            return '상품권';
        case 'ncrflambeau':
            return '양식지/NCR';
        default:
            return '인쇄물';
    }
}

function getProductDetails($item, $connect) {
    $product_type = $item['product_type'] ?? 'unknown';
    $details = [];
    
    switch ($product_type) {
        case 'sticker':
            if (!empty($item['jong'])) $details[] = '재질: ' . $item['jong'];
            if (!empty($item['garo']) && !empty($item['sero'])) {
                $details[] = '크기: ' . $item['garo'] . 'mm × ' . $item['sero'] . 'mm';
            }
            if (!empty($item['domusong'])) $details[] = '모양: ' . $item['domusong'];
            break;
            
        case 'namecard':
            if (!empty($item['MY_type'])) {
                $details[] = '종류: ' . getKoreanName($connect, $item['MY_type']);
            }
            if (!empty($item['Section'])) {
                $details[] = '재질: ' . getKoreanName($connect, $item['Section']);
            }
            if (!empty($item['POtype'])) {
                $details[] = '인쇄면: ' . ($item['POtype'] == '1' ? '단면' : '양면');
            }
            if (!empty($item['ordertype'])) {
                $details[] = '주문방식: ' . ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');
            }
            break;
            
        default:
            if (!empty($item['MY_type'])) {
                $details[] = '구분: ' . getKoreanName($connect, $item['MY_type']);
            }
            if (!empty($item['Section'])) {
                $details[] = '옵션: ' . getKoreanName($connect, $item['Section']);
            }
            if (!empty($item['TreeSelect'])) {
                $details[] = '추가옵션: ' . getKoreanName($connect, $item['TreeSelect']);
            }
            if (!empty($item['POtype'])) {
                $details[] = '인쇄면: ' . ($item['POtype'] == '1' ? '단면' : '양면');
            }
            if (!empty($item['ordertype'])) {
                $details[] = '주문방식: ' . ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');
            }
            break;
    }
    
    return implode('<br>', $details);
}

function getQuantity($item) {
    $product_type = $item['product_type'] ?? 'unknown';
    
    switch ($product_type) {
        case 'sticker':
            return !empty($item['mesu']) ? $item['mesu'] . '매' : '1매';
        case 'namecard':
            return !empty($item['MY_amount']) ? $item['MY_amount'] . '매' : '500매';
        default:
            return !empty($item['MY_amount']) ? $item['MY_amount'] . '개' : '1개';
    }
}

function generatePrintableHTML($html) {
    // 브라우저에서 바로 인쇄할 수 있는 HTML 출력
    echo $html;
}

function generatePDFWithWkhtmltopdf($html, $quote_number, $customer_info) {
    // wkhtmltopdf를 사용한 PDF 생성 (서버에 설치 필요)
    $temp_html = tempnam(sys_get_temp_dir(), 'quote_') . '.html';
    file_put_contents($temp_html, $html);
    
    $pdf_file = tempnam(sys_get_temp_dir(), 'quote_') . '.pdf';
    $command = "wkhtmltopdf --page-size A4 --margin-top 1cm --margin-bottom 1cm --margin-left 1cm --margin-right 1cm '$temp_html' '$pdf_file'";
    
    shell_exec($command);
    
    if (file_exists($pdf_file)) {
        // PDF 파일을 영구 저장할 경로 (메일 발송용)
        $permanent_pdf = './quotes/' . $quote_number . '.pdf';
        
        // quotes 디렉토리가 없으면 생성
        if (!is_dir('./quotes')) {
            mkdir('./quotes', 0755, true);
        }
        
        // 영구 저장
        copy($pdf_file, $permanent_pdf);
        
        // 브라우저로 다운로드
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="견적서_' . $quote_number . '.pdf"');
        header('Content-Length: ' . filesize($pdf_file));
        readfile($pdf_file);
        
        // 임시 파일 삭제
        unlink($temp_html);
        unlink($pdf_file);
        
        return $permanent_pdf; // 메일 발송용 파일 경로 반환
    } else {
        // PDF 생성 실패 시 HTML로 폴백
        generatePrintableHTML($html);
        unlink($temp_html);
        return null;
    }
}

// 견적서 발송 로그 저장 함수
function logQuoteGeneration($connect, $quote_number, $customer_info, $cart_items, $session_id) {
    try {
        // 총 금액 계산
        $total_price = 0;
        $total_price_vat = 0;
        $items_summary = [];
        
        foreach ($cart_items as $item) {
            $price = intval($item['st_price'] ?? 0);
            $price_vat = intval($item['st_price_vat'] ?? 0);
            $total_price += $price;
            $total_price_vat += $price_vat;
            
            // 상품 요약 정보
            $items_summary[] = [
                'product_type' => $item['product_type'] ?? 'unknown',
                'product_name' => getProductName($item),
                'price' => $price,
                'price_vat' => $price_vat
            ];
        }
        
        // IP 주소 가져오기
        $ip_address = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // quote_log 테이블에 기본 정보 저장
        $query = "INSERT INTO quote_log (
            quote_number, session_id, customer_name, customer_phone, 
            customer_company, customer_email, quote_memo, 
            total_items, total_price, total_price_vat, items_summary,
            ip_address, user_agent, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'generated')";
        
        $stmt = mysqli_prepare($connect, $query);
        if ($stmt) {
            $items_json = json_encode($items_summary, JSON_UNESCAPED_UNICODE);
            $total_items = count($cart_items);
            
            // 변수에 먼저 할당 (PHP 참조 전달 요구사항)
            $customer_name = $customer_info['name'];
            $customer_phone = $customer_info['phone'];
            $customer_company = $customer_info['company'];
            $customer_email = $customer_info['email'];
            $customer_memo = $customer_info['memo'];
            
            mysqli_stmt_bind_param($stmt, 'sssssssiisss', 
                $quote_number, $session_id, $customer_name, $customer_phone,
                $customer_company, $customer_email, $customer_memo,
                $total_items, $total_price, $total_price_vat, $items_json,
                $ip_address, $user_agent
            );
            
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            // quote_items 테이블에 상세 상품 정보 저장
            saveQuoteItems($connect, $quote_number, $cart_items);
            
            error_log("견적서 로그 저장 완료: {$quote_number} - 고객: {$customer_info['name']}");
        }
    } catch (Exception $e) {
        error_log("견적서 로그 저장 실패: " . $e->getMessage());
    }
}

// 견적서 상품 상세 정보 저장
function saveQuoteItems($connect, $quote_number, $cart_items) {
    $query = "INSERT INTO quote_items (
        quote_number, product_type, product_name,
        MY_type, MY_Fsd, PN_type, MY_amount, POtype, ordertype,
        jong, garo, sero, mesu, uhyung, domusong,
        st_price, st_price_vat, MY_comment, img
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($connect, $query);
    if ($stmt) {
        foreach ($cart_items as $item) {
            $product_name = getProductName($item);
            
            // 변수에 먼저 할당 (PHP 참조 전달 요구사항)
            $product_type = $item['product_type'] ?? '';
            $my_type = $item['MY_type'] ?? '';
            $my_fsd = $item['MY_Fsd'] ?? '';
            $pn_type = $item['PN_type'] ?? '';
            $my_amount = $item['MY_amount'] ?? '';
            $po_type = $item['POtype'] ?? '';
            $order_type = $item['ordertype'] ?? '';
            $jong = $item['jong'] ?? '';
            $garo = $item['garo'] ?? '';
            $sero = $item['sero'] ?? '';
            $mesu = $item['mesu'] ?? '';
            $uhyung = $item['uhyung'] ?? 0;
            $domusong = $item['domusong'] ?? '';
            $st_price = $item['st_price'] ?? 0;
            $st_price_vat = $item['st_price_vat'] ?? 0;
            $my_comment = $item['MY_comment'] ?? '';
            $img = $item['img'] ?? '';
            
            mysqli_stmt_bind_param($stmt, 'sssssssssssssssssss',
                $quote_number,
                $product_type,
                $product_name,
                $my_type,
                $my_fsd,
                $pn_type,
                $my_amount,
                $po_type,
                $order_type,
                $jong,
                $garo,
                $sero,
                $mesu,
                $uhyung,
                $domusong,
                $st_price,
                $st_price_vat,
                $my_comment,
                $img
            );
            
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
    }
}

// 관리자에게 견적서 발송 알림 메일 전송
function sendAdminQuoteNotification($customer_info, $quote_number, $cart_items) {
    try {
        $admin_email = COMPANY_EMAIL;
        $subject = "[견적서 발송] {$customer_info['name']}님 견적 요청 - {$quote_number}";
        
        $message = "
=== 견적서 발송 알림 ===

📋 견적번호: {$quote_number}
👤 고객명: {$customer_info['name']}
📞 연락처: {$customer_info['phone']}
🏢 회사명: " . ($customer_info['company'] ?: '-') . "
📧 이메일: " . ($customer_info['email'] ?: '-') . "
💬 요청사항: " . ($customer_info['memo'] ?: '-') . "

📦 주문 상품:
";
        
        $total_amount = 0;
        foreach ($cart_items as $item) {
            $product_name = getProductName($item);
            $price = intval($item['st_price_vat'] ?? 0);
            $total_amount += $price;
            
            $message .= "- {$product_name}: " . number_format($price) . "원\n";
        }
        
        $message .= "
💰 총 금액: " . number_format($total_amount) . "원 (VAT 포함)

⏰ 발송시간: " . date('Y-m-d H:i:s') . "

관리자 페이지에서 자세한 내용을 확인하세요.
";
        
        $headers = "From: system@dsp114.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // 기존 mailer 라이브러리 사용
        require_once('../../MlangOrder_PrintAuto/mailer.lib.php');
        
        $result = mailer(
            "두손기획인쇄",           // 보내는 사람 이름
            "dsp1830@naver.com",     // 보내는 사람 메일주소  
            $admin_email,            // 받는 사람 메일주소
            $subject,                // 제목
            $message,                // 내용
            0,                       // text 타입
            ""                       // 첨부파일 없음
        );
        
        if (!$result) {
            error_log("메일 발송 실패: mailer 함수 오류");
        }
        
        error_log("관리자 알림 메일 발송: {$quote_number}");
        
    } catch (Exception $e) {
        error_log("관리자 알림 메일 발송 실패: " . $e->getMessage());
    }
}

// 고객에게 견적서 PDF 메일 발송
function sendCustomerQuotePDF($customer_info, $quote_number, $cart_items, $pdf_path) {
    try {
        require_once('../../MlangOrder_PrintAuto/mailer.lib.php');
        
        $customer_email = $customer_info['email'];
        $customer_name = $customer_info['name'];
        
        $subject = "[견적서] " . $customer_name . "님의 인쇄 견적서 - " . $quote_number;
        
        $message = "안녕하세요 " . $customer_name . "님,

요청하신 인쇄물 견적서를 첨부파일로 보내드립니다.

📋 견적번호: " . $quote_number . "
📧 문의사항이 있으시면 언제든지 연락주세요.

감사합니다.

두손기획인쇄
전화: 032-555-1830
이메일: dsp1830@naver.com";
        
        // PDF 파일을 첨부파일로 준비
        $attachments = array();
        if (file_exists($pdf_path)) {
            $attachments[] = array(
                'name' => "견적서_" . $quote_number . ".pdf",
                'path' => $pdf_path
            );
        }
        
        $result = mailer(
            "두손기획인쇄",           // 보내는 사람 이름
            "dsp1830@naver.com",     // 보내는 사람 메일주소  
            $customer_email,         // 받는 사람 메일주소
            $subject,                // 제목
            $message,                // 내용
            0,                       // text 타입
            $attachments             // 첨부파일
        );
        
        if (!$result) {
            error_log("고객 견적서 메일 발송 실패: " . $customer_email);
        } else {
            error_log("고객 견적서 메일 발송 성공: " . $customer_email);
        }
        
    } catch (Exception $e) {
        error_log("고객 견적서 메일 발송 실패: " . $e->getMessage());
    }
}

mysqli_close($connect);
?>