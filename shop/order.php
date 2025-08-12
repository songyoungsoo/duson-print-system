<?php
session_start();
$session_id = session_id();

require_once('../lib/func.php');
require_once('mail/mailer.lib.php');

// 이메일 전송 함수
function sendOrderEmail($order_id, $items, $customer_info) {
    if (!is_array($customer_info) || !is_array($items)) {
        error_log("Invalid parameters for sendOrderEmail");
        return false;
    }

    try {
        
        // 고객용 이메일
        $customer_subject = "[두손기획] 주문이 완료되었습니다 (주문번호: {$order_id})";
        $customer_body = generateEmailBody($order_id, $items, $customer_info, false);
        
        if (!empty($customer_info['customer_email'])) {
            mailer(
                "두손기획",
                "dsp1830@naver.com",
                $customer_info['customer_email'],
                $customer_subject,
                $customer_body,
                1, "", "", ""
            );
        }

        // 관리자용 이메일
        $admin_subject = "[새 주문] 주문번호: {$order_id}";
        $admin_body = generateEmailBody($order_id, $items, $customer_info, true);
        
        mailer(
            "두손기획",
            "dsp1830@naver.com",
            "dsp1830@naver.com",
            $admin_subject,
            $admin_body,
            1, "", "", ""
        );

        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

// 이메일 본문 생성 함수
function generateEmailBody($order_id, $items, $customer_info, $is_admin = false) {
    $body = "<h2>" . ($is_admin ? "[관리자용] 새로운 주문이 접수되었습니다." : "주문이 성공적으로 접수되었습니다.") . "</h2>";
    $body .= "<p><strong>주문번호:</strong> {$order_id}</p>";
    
    // 주문자 정보
    $body .= "<h3>주문자 정보</h3>";
    $body .= "<p>";
    $body .= "이름: " . htmlspecialchars($customer_info['customer_name']) . "<br>";
    $body .= "이메일: " . htmlspecialchars($customer_info['customer_email']) . "<br>";
    $body .= "연락처: " . htmlspecialchars($customer_info['customer_phone']) . "</p>";
    
    // 배송 정보
    $body .= "<h3>배송 정보</h3>";
    $body .= "<p>주소: (" . htmlspecialchars($customer_info['delivery_zipcode']) . ") ";
    $body .= htmlspecialchars($customer_info['delivery_address']) . " ";
    $body .= htmlspecialchars($customer_info['delivery_detail']) . "</p>";
    
    // 주문 상품 정보
    $body .= "<h3>주문 상품 내역</h3>";
    $body .= "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    $body .= "<tr><th>상품명</th><th>수량</th><th>가격</th></tr>";
    
    $total = 0;
    foreach ($items as $item) {
        $body .= "<tr>";
        $body .= "<td>" . htmlspecialchars($item['name']) . "</td>";
        $body .= "<td>" . htmlspecialchars($item['quantity']) . "</td>";
        $body .= "<td>" . number_format($item['price']) . "원</td>";
        $body .= "</tr>";
        $total += ($item['price'] * $item['quantity']);
    }
    
    $body .= "</table>";
    $body .= "<p><strong>총 결제금액:</strong> " . number_format($total) . "원</p>";
    
    return $body;
}

// 메인 실행 부분
try {
    $connect = dbconn();

    // UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, 'utf8');
}

// 장바구니 아이템 조회
$query = "SELECT * FROM shop_temp WHERE session_id='$session_id' ORDER BY no DESC";
$result = mysqli_query($connect, $query);

$items = [];
$total = 0;
$total_vat = 0;

while ($data = mysqli_fetch_array($result)) {
    $product_type = $data['product_type'] ?? 'sticker';
    
    if ($product_type === 'leaflet') {
        // 전단지 데이터 처리
        $items[] = [
            'no' => $data['no'],
            'product_type' => 'leaflet',
            'product_name' => '전단지',
            'options' => getLeafletOrderInfo($connect, $data),
            'st_price' => $data['st_price'],
            'st_price_vat' => $data['st_price_vat']
        ];
    } else {
        // 스티커 데이터 처리
        $items[] = [
            'no' => $data['no'],
            'product_type' => 'sticker',
            'product_name' => '스티커',
            'options' => getStickerOrderInfo($data),
            'st_price' => $data['st_price'],
            'st_price_vat' => $data['st_price_vat']
        ];
    }
    
    $total += $data['st_price'];
    $total_vat += $data['st_price_vat'];
}

// 장바구니가 비어있으면 장바구니로 리다이렉트
if (empty($items)) {
    header('Location: cart.php');
    exit;
}

function getLeafletOrderInfo($connect, $data) {
    $info = [];
    
    // 인쇄색상
    $color_query = "SELECT title FROM MlangPrintAuto_transactionCate WHERE no='{$data['MY_type']}'";
    $color_result = mysqli_query($connect, $color_query);
    if ($color_result && $color_row = mysqli_fetch_array($color_result)) {
        $info['color'] = $color_row['title'];
    }
    
    // 종이종류
    $paper_query = "SELECT title FROM MlangPrintAuto_transactionCate WHERE no='{$data['MY_Fsd']}'";
    $paper_result = mysqli_query($connect, $paper_query);
    if ($paper_result && $paper_row = mysqli_fetch_array($paper_result)) {
        $info['paper_type'] = $paper_row['title'];
    }
    
    // 종이규격
    $size_query = "SELECT title FROM MlangPrintAuto_transactionCate WHERE no='{$data['PN_type']}'";
    $size_result = mysqli_query($connect, $size_query);
    if ($size_result && $size_row = mysqli_fetch_array($size_result)) {
        $info['paper_size'] = $size_row['title'];
    }
    
    // 수량
    $quantity_query = "SELECT quantityTwo FROM MlangPrintAuto_inserted WHERE quantity='{$data['MY_amount']}' LIMIT 1";
    $quantity_result = mysqli_query($connect, $quantity_query);
    if ($quantity_result && $quantity_row = mysqli_fetch_array($quantity_result)) {
        $info['quantity'] = $quantity_row['quantityTwo'] . '매 (' . $data['MY_amount'] . '연)';
    }
    
    $info['sides'] = $data['POtype'] == '1' ? '단면' : '양면';
    $info['order_type'] = $data['ordertype'] === 'print' ? '인쇄만' : '디자인+인쇄';
    
    return $info;
}

function getStickerOrderInfo($data) {
    return [
        'material' => $data['jong'] ?? '스티커',
        'size' => ($data['garo'] ?? '0') . ' × ' . ($data['sero'] ?? '0') . 'mm',
        'quantity' => ($data['mesu'] ?? '0') . '매',
        'options' => $data['domusong'] ?? '',
        'design' => ($data['uhyung'] ?? 0) > 0 ? '디자인+인쇄' : '인쇄만'
    ];
}

// 주문 처리
if (isset($_POST['action']) && $_POST['action'] === 'place_order') {
    $customer_name = $_POST['customer_name'] ?? '';
    $customer_phone = $_POST['customer_phone'] ?? '';
    $customer_email = $_POST['customer_email'] ?? '';
    $delivery_address = $_POST['delivery_address'] ?? '';
    $delivery_detail = $_POST['delivery_detail'] ?? '';
    $delivery_zipcode = $_POST['delivery_zipcode'] ?? '';
    $order_memo = $_POST['order_memo'] ?? '';
    
    // 입력값 검증
    if (empty($customer_name) || empty($customer_phone) || empty($delivery_address)) {
        $error_message = '필수 정보를 모두 입력해주세요.';
    } else {
        // mlangorder_printauto 테이블에 주문 저장
        $order_success = saveOrder($connect, $items, [
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'customer_email' => $customer_email,
            'delivery_address' => $delivery_address,
            'delivery_detail' => $delivery_detail,
            'delivery_zipcode' => $delivery_zipcode,
            'order_memo' => $order_memo,
            'total_price' => $total,
            'total_vat' => $total_vat
        ]);
        
        if ($order_success) {
            // 고객 정보 배열 생성
            $customer_info = array(
                'name' => $customer_name,
                'email' => $customer_email,
                'phone' => $customer_phone,
                'address' => $delivery_address,
                'detail' => $delivery_detail,
                'zipcode' => $delivery_zipcode,
                'memo' => $order_memo
            );
            
            // 이메일 발송
            require_once('include/email_functions.php');
            $email_sent = sendOrderEmail($order_success, $items, $customer_info);
            
            // 장바구니 비우기
            mysqli_query($connect, "DELETE FROM shop_temp WHERE session_id='$session_id'");
            
            // 주문 완료 페이지로 리다이렉트
            header('Location: order_complete.php?order_id=' . $order_success . '&email_sent=' . ($email_sent ? '1' : '0'));
            exit;
        } else {
            $error_message = '주문 처리 중 오류가 발생했습니다: ' . mysqli_error($connect);
        }
    }
}

function saveOrder($connect, $items, $customer_info) {
    // 주문 번호 생성
    $order_id = 'ORD' . date('YmdHis') . rand(100, 999);
    
    // 각 아이템별로 기존 MlangOrder_PrintAuto 테이블에 저장
    foreach ($items as $item) {
        // 제품 옵션을 문자열로 변환
        $product_options_text = '';
        if ($item['product_type'] === 'leaflet') {
            $product_options_text = 
                '인쇄색상: ' . ($item['options']['color'] ?? '') . ' / ' .
                '종이종류: ' . ($item['options']['paper_type'] ?? '') . ' / ' .
                '종이규격: ' . ($item['options']['paper_size'] ?? '') . ' / ' .
                '수량: ' . ($item['options']['quantity'] ?? '') . ' / ' .
                '인쇄면: ' . ($item['options']['sides'] ?? '') . ' / ' .
                '주문방법: ' . ($item['options']['order_type'] ?? '');
        } else {
            $product_options_text = 
                '재질: ' . ($item['options']['material'] ?? '') . ' / ' .
                '크기: ' . ($item['options']['size'] ?? '') . ' / ' .
                '수량: ' . ($item['options']['quantity'] ?? '') . ' / ' .
                '옵션: ' . ($item['options']['options'] ?? '') . ' / ' .
                '주문방법: ' . ($item['options']['design'] ?? '');
        }
        
        // 기존 테이블 구조에 맞춰 데이터 저장
        $insert_query = "INSERT INTO MlangOrder_PrintAuto 
                        (Type, Type_1, money_1, money_2, money_3, money_4, money_5,
                         name, email, zip, zip1, zip2, phone, cont, date, OrderStyle, ThingCate) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
        
        $stmt = mysqli_prepare($connect, $insert_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssssssssssss", 
                $item['product_name'],                           // Type
                $product_options_text,                           // Type_1
                $item['st_price'],                              // money_1 (세전가격)
                $item['st_price_vat'],                          // money_2 (VAT포함가격)
                $customer_info['total_price'],                  // money_3 (총 세전가격)
                $customer_info['total_vat'],                    // money_4 (총 VAT포함가격)
                $order_id,                                      // money_5 (주문번호)
                $customer_info['customer_name'],                // name
                $customer_info['customer_email'],               // email
                $customer_info['delivery_zipcode'],             // zip
                $customer_info['delivery_address'],             // zip1
                $customer_info['delivery_detail'],              // zip2
                $customer_info['customer_phone'],               // phone
                $customer_info['order_memo'],                   // cont
                $item['product_name'],                          // OrderStyle
                $item['product_type']                           // ThingCate
            );
            
            if (!mysqli_stmt_execute($stmt)) {
                error_log("주문 저장 오류: " . mysqli_stmt_error($stmt));
                mysqli_stmt_close($stmt);
                return false;
            }
            mysqli_stmt_close($stmt);
        } else {
            error_log("Prepared statement 생성 오류: " . mysqli_error($connect));
            return false;
        }
    }
    
    return $order_id;
}

function sendOrderEmails($order_id, $items, $customer_info)
{
    include_once('mail/mailer.lib.php');
    
    // 주문 내역 이메일 내용 생성
    $subject = "[두손기획] 주문이 완료되었습니다. (주문번호: {$order_id})";
    
    // HTML 이메일 본문 생성
    $body = "<h2>주문이 성공적으로 접수되었습니다.</h2>";
    $body .= "<p><strong>주문번호:</strong> {$order_id}</p>";
    $body .= "<p><strong>주문자 정보</strong><br>";
    $body .= "이름: {$customer_info['name']}<br>";
    $body .= "이메일: {$customer_info['email']}<br>";
    $body .= "전화번호: {$customer_info['phone']}</p>";
    
    $body .= "<p><strong>배송 정보</strong><br>";
    $body .= "주소: ({$customer_info['zipcode']}) {$customer_info['address']} {$customer_info['detail']}<br>";
    if (!empty($customer_info['memo'])) {
        $body .= "배송메모: {$customer_info['memo']}</p>";
    }
    
    $body .= "<h3>주문 상품 내역</h3>";
    $body .= "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    $body .= "<tr><th>상품명</th><th>수량</th><th>가격</th></tr>";
    
    foreach ($items as $item) {
        $body .= "<tr>";
        $body .= "<td>{$item['name']}</td>";
        $body .= "<td>{$item['quantity']}</td>";
        $body .= "<td>" . number_format($item['price']) . "원</td>";
        $body .= "</tr>";
    }
    
    $body .= "</table>";
    
    // 이메일 발송
    try {
        return mailer(
            "두손기획", // 보내는 사람 이름
            "dsp1830@naver.com", // 보내는 사람 이메일
            $customer_info['email'], // 받는 사람 이메일
            $subject, // 제목
            $body, // 내용
            1, // HTML 타입
            "", // 첨부파일 없음
            "", // CC 없음
            "" // BCC 없음
        );
    } catch (Exception $e) {
        error_log("이메일 발송 실패: " . $e->getMessage());
        return false;
    }
}
        // customer_info 유효성 검사
        if (!is_array($customer_info)) {
            error_log("Error: customer_info is not an array");
            return false;
        }

        // items 유효성 검사
        if (!is_array($items)) {
            error_log("Error: items is not an array");
            return false;
        }

        // 필수 필드 확인
        $required_fields = ['customer_email', 'customer_name', 'customer_phone', 'delivery_address'];
        foreach ($required_fields as $field) {
            if (empty($customer_info[$field])) {
                error_log("Error: Missing required field: " . $field);
                $customer_info[$field] = ''; // 빈 문자열로 설정하여 오류 방지
            }
        }

        // 고객용 이메일 내용 생성
        $customer_email_content = generateCustomerEmailContent($order_id, $items, $customer_info);
        
        // 관리자용 이메일 내용 생성
        $admin_email_content = generateAdminEmailContent($order_id, $items, $customer_info);
        
        $email_success = true;
        
        // 고객에게 주문 확인 이메일 발송
        if (!empty($customer_info['customer_email'])) {
            $customer_result = mailer(
                "두손기획인쇄",                                    // 보내는 사람 이름
                "dsp1830@naver.com",                              // 보내는 사람 이메일
                $customer_info['customer_email'],                 // 받는 사람 이메일
                "[두손기획] 주문이 완료되었습니다 - " . $order_id,  // 제목
                $customer_email_content,                          // 내용
                1                                                 // HTML 타입
            );
            
            if (!$customer_result) {
                $email_success = false;
                error_log("고객 이메일 발송 실패: " . $customer_info['customer_email']);
            }
        }
        
        // 관리자에게 새 주문 알림 이메일 발송
        $admin_result = mailer(
            "두손기획 주문시스템",                                // 보내는 사람 이름
            "dsp1830@naver.com",                                // 보내는 사람 이메일
            "dsp1830@naver.com",                                // 받는 사람 이메일 (관리자)
            "[새 주문] " . $customer_info['customer_name'] . "님의 주문 - " . $order_id, // 제목
            $admin_email_content,                               // 내용
            1                                                   // HTML 타입
        );
        
        if (!$admin_result) {
            $email_success = false;
            error_log("관리자 이메일 발송 실패");
        }
        
        return $email_success;
        
    // UTF-8 설정
    if ($connect) {
        mysqli_set_charset($connect, 'utf8');
    }

    // 장바구니 아이템 조회
    $query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no DESC";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 's', $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $items = [];
    $total = 0;
    $total_vat = 0;

    while ($data = mysqli_fetch_array($result)) {
        $items[] = [
            'name' => $data['name'],
            'quantity' => $data['quantity'],
            'price' => $data['price'],
            'options' => json_decode($data['options'], true)
        ];
        $total += $data['price'] * $data['quantity'];
        $total_vat += $data['price_vat'];
    }

    // POST 요청 처리
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 주문 정보 수집
        $customer_info = [
            'customer_name' => $_POST['customer_name'] ?? '',
            'customer_email' => $_POST['customer_email'] ?? '',
            'customer_phone' => $_POST['customer_phone'] ?? '',
            'delivery_zipcode' => $_POST['delivery_zipcode'] ?? '',
            'delivery_address' => $_POST['delivery_address'] ?? '',
            'delivery_detail' => $_POST['delivery_detail'] ?? '',
            'order_memo' => $_POST['order_memo'] ?? ''
        ];

        // 주문번호 생성
        $order_id = 'ORD' . date('YmdHis') . rand(1000, 9999);

        // 주문 정보 저장
        $query = "INSERT INTO shop_orders SET
            order_id = ?,
            customer_name = ?,
            customer_email = ?,
            customer_phone = ?,
            delivery_zipcode = ?,
            delivery_address = ?,
            delivery_detail = ?,
            order_memo = ?,
            total_price = ?,
            total_vat = ?,
            order_date = NOW()";

        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, 'ssssssssdd',
            $order_id,
            $customer_info['customer_name'],
            $customer_info['customer_email'],
            $customer_info['customer_phone'],
            $customer_info['delivery_zipcode'],
            $customer_info['delivery_address'],
            $customer_info['delivery_detail'],
            $customer_info['order_memo'],
            $total,
            $total_vat
        );

        if (mysqli_stmt_execute($stmt)) {
            // 이메일 발송
            $email_sent = sendOrderEmail($order_id, $items, $customer_info);
            
            // 장바구니 비우기
            mysqli_query($connect, "DELETE FROM shop_temp WHERE session_id = '$session_id'");
            
            // 주문 완료 페이지로 이동
            header('Location: order_complete.php?order_id=' . $order_id . '&email_sent=' . ($email_sent ? '1' : '0'));
            exit;
        }
    }

    // 주문 폼 표시
    include 'templates/header.php';
    include 'templates/order_form.php';
    include 'templates/footer.php';

} catch (Exception $e) {
    error_log("Order processing error: " . $e->getMessage());
    echo "<script>alert('주문 처리 중 오류가 발생했습니다.'); history.back();</script>";
    exit;
}

function generateCustomerEmailContent($order_id, $items, $customer_info)
{
    // 매개변수 유효성 검사
    if (!$order_id || !is_array($items) || !is_array($customer_info)) {
        error_log("Invalid parameters passed to generateCustomerEmailContent");
        return '주문 정보를 생성할 수 없습니다.';
    }

    // 안전하게 배열 요소 접근
    $name = isset($customer_info['customer_name']) ? htmlspecialchars($customer_info['customer_name']) : '고객';
    $email = isset($customer_info['customer_email']) ? htmlspecialchars($customer_info['customer_email']) : '';
    $phone = isset($customer_info['customer_phone']) ? htmlspecialchars($customer_info['customer_phone']) : '';
    $address = isset($customer_info['delivery_address']) ? htmlspecialchars($customer_info['delivery_address']) : '';
    $detail = isset($customer_info['delivery_detail']) ? htmlspecialchars($customer_info['delivery_detail']) : '';
    $zipcode = isset($customer_info['delivery_zipcode']) ? htmlspecialchars($customer_info['delivery_zipcode']) : '';

    $content = "<h2>주문이 성공적으로 접수되었습니다.</h2>";
    $content .= "<p><strong>주문번호:</strong> " . htmlspecialchars($order_id) . "</p>";
    
    // 주문자 정보
    $content .= "<h3>주문자 정보</h3>";
    $content .= "<p>";
    $content .= "이름: {$name}<br>";
    $content .= "이메일: {$email}<br>";
    $content .= "연락처: {$phone}<br>";
    $content .= "</p>";

    // 배송 정보
    $content .= "<h3>배송 정보</h3>";
    $content .= "<p>";
    if ($zipcode) {
        $content .= "우편번호: {$zipcode}<br>";
    }
    $content .= "주소: {$address}";
    if ($detail) {
        $content .= " {$detail}";
    }
    $content .= "</p>";

    // 주문 상품 정보
    $content .= "<h3>주문 상품 정보</h3>";
    $content .= "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    $content .= "<tr><th>상품명</th><th>수량</th><th>가격</th></tr>";

    $total = 0;
    foreach ($items as $item) {
        if (is_array($item)) {
            $item_name = isset($item['name']) ? htmlspecialchars($item['name']) : '상품명 없음';
            $item_quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
            $item_price = isset($item['price']) ? (int)$item['price'] : 0;
            
            $content .= "<tr>";
            $content .= "<td>{$item_name}</td>";
            $content .= "<td>{$item_quantity}</td>";
            $content .= "<td>" . number_format($item_price) . "원</td>";
            $content .= "</tr>";
            
            $total += $item_price * $item_quantity;
        }
    }

    $content .= "</table>";
    $content .= "<p><strong>총 결제금액:</strong> " . number_format($total) . "원</p>";
    
    return $content; {
    $content = '
    <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
        <div style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 28px;">🎉 주문이 완료되었습니다!</h1>
            <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">두손기획인쇄를 이용해 주셔서 감사합니다.</p>
        </div>
        
        <div style="padding: 30px; background: #f8f9fa;">
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h2 style="color: #2c3e50; margin-top: 0;">📋 주문 정보</h2>
                <p><strong>주문번호:</strong> ' . htmlspecialchars($order_id) . '</p>
                <p><strong>주문자:</strong> ' . htmlspecialchars($customer_info['customer_name']) . '</p>
                <p><strong>연락처:</strong> ' . htmlspecialchars($customer_info['customer_phone']) . '</p>
                <p><strong>주문일시:</strong> ' . date('Y-m-d H:i:s') . '</p>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h2 style="color: #2c3e50; margin-top: 0;">📦 주문 상품</h2>';
    
    $total_price = 0;
    foreach ($items as $item) {
        $content .= '
                <div style="border-bottom: 1px solid #eee; padding: 15px 0;">
                    <h3 style="color: #3498db; margin: 0 0 10px 0;">' . htmlspecialchars($item['product_name']) . '</h3>';
        
        if ($item['product_type'] === 'leaflet') {
            $content .= '
                    <p style="margin: 5px 0; color: #666;">인쇄색상: ' . htmlspecialchars($item['options']['color'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">종이종류: ' . htmlspecialchars($item['options']['paper_type'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">종이규격: ' . htmlspecialchars($item['options']['paper_size'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">수량: ' . htmlspecialchars($item['options']['quantity'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">인쇄면: ' . htmlspecialchars($item['options']['sides'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">주문방법: ' . htmlspecialchars($item['options']['order_type'] ?? '') . '</p>';
        } else {
            $content .= '
                    <p style="margin: 5px 0; color: #666;">재질: ' . htmlspecialchars($item['options']['material'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">크기: ' . htmlspecialchars($item['options']['size'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">수량: ' . htmlspecialchars($item['options']['quantity'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">주문방법: ' . htmlspecialchars($item['options']['design'] ?? '') . '</p>';
        }
        
        $content .= '
                    <p style="margin: 10px 0 0 0; font-weight: bold; color: #e74c3c; text-align: right;">
                        ' . number_format($item['st_price_vat']) . '원 (VAT 포함)
                    </p>
                </div>';
        
        $total_price += $item['st_price_vat'];
    }
    
    $content .= '
                <div style="text-align: right; margin-top: 20px; padding-top: 20px; border-top: 2px solid #3498db;">
                    <h3 style="color: #2c3e50; margin: 0;">총 결제금액: ' . number_format($total_price) . '원</h3>
                </div>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h2 style="color: #2c3e50; margin-top: 0;">🚚 배송 정보</h2>
                <p><strong>우편번호:</strong> ' . htmlspecialchars($customer_info['delivery_zipcode']) . '</p>
                <p><strong>주소:</strong> ' . htmlspecialchars($customer_info['delivery_address']) . '</p>
                <p><strong>상세주소:</strong> ' . htmlspecialchars($customer_info['delivery_detail']) . '</p>
            </div>
            
            <div style="background: #e8f5e8; padding: 20px; border-radius: 10px; border-left: 4px solid #27ae60;">
                <h2 style="color: #27ae60; margin-top: 0;">📞 다음 단계</h2>
                <p style="margin: 10px 0;">1. <strong>입금 확인</strong> 후 작업을 시작합니다</p>
                <p style="margin: 10px 0;">2. <strong>1-2시간 내</strong> 담당자가 연락드립니다</p>
                <p style="margin: 10px 0;">3. 디자인 파일이 필요한 경우 별도 안내드립니다</p>
                <p style="margin: 10px 0;">4. <strong>택배비는 착불</strong>입니다</p>
            </div>
        </div>
        
        <div style="background: #2c3e50; color: white; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0;">📞 고객센터</h3>
            <p style="margin: 5px 0;">전화: 1688-2384</p>
            <p style="margin: 5px 0;">팩스: 02-2632-1829</p>
            <p style="margin: 5px 0;">이메일: dsp1830@naver.com</p>
            <p style="margin: 5px 0;">주소: 서울 영등포구 영등포로 36길9 송호빌딩 1층</p>
            <p style="margin: 5px 0;">운영시간: 평일 09:00 - 18:00</p>
        </div>
    </div>';
    
    return $content;
}

function generateAdminEmailContent($order_id, $items, $customer_info)
{
    // 매개변수 유효성 검사
    if (!$order_id || !is_array($items) || !is_array($customer_info)) {
        error_log("Invalid parameters passed to generateAdminEmailContent");
        return '주문 정보를 생성할 수 없습니다.';
    }

    // 안전하게 배열 요소 접근
    $name = isset($customer_info['customer_name']) ? htmlspecialchars($customer_info['customer_name']) : '고객';
    $email = isset($customer_info['customer_email']) ? htmlspecialchars($customer_info['customer_email']) : '';
    $phone = isset($customer_info['customer_phone']) ? htmlspecialchars($customer_info['customer_phone']) : '';
    $address = isset($customer_info['delivery_address']) ? htmlspecialchars($customer_info['delivery_address']) : '';
    $detail = isset($customer_info['delivery_detail']) ? htmlspecialchars($customer_info['delivery_detail']) : '';
    $zipcode = isset($customer_info['delivery_zipcode']) ? htmlspecialchars($customer_info['delivery_zipcode']) : '';
    $memo = isset($customer_info['order_memo']) ? htmlspecialchars($customer_info['order_memo']) : '';

    $content = "<h2>[관리자용] 새로운 주문이 접수되었습니다.</h2>";
    $content .= "<p><strong>주문번호:</strong> " . htmlspecialchars($order_id) . "</p>";
    
    // 주문자 정보
    $content .= "<h3>주문자 정보</h3>";
    $content .= "<p>";
    $content .= "이름: {$name}<br>";
    $content .= "이메일: {$email}<br>";
    $content .= "연락처: {$phone}<br>";
    $content .= "</p>";

    // 배송 정보
    $content .= "<h3>배송 정보</h3>";
    $content .= "<p>";
    if ($zipcode) {
        $content .= "우편번호: {$zipcode}<br>";
    }
    $content .= "주소: {$address}";
    if ($detail) {
        $content .= " {$detail}";
    }
    if ($memo) {
        $content .= "<br>배송 메모: {$memo}";
    }
    $content .= "</p>";

    // 주문 상품 정보
    $content .= "<h3>주문 상품 정보</h3>";
    $content .= "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    $content .= "<tr><th>상품명</th><th>수량</th><th>단가</th><th>소계</th></tr>";

    $total = 0;
    foreach ($items as $item) {
        if (is_array($item)) {
            $item_name = isset($item['name']) ? htmlspecialchars($item['name']) : '상품명 없음';
            $item_quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
            $item_price = isset($item['price']) ? (int)$item['price'] : 0;
            $subtotal = $item_price * $item_quantity;
            
            $content .= "<tr>";
            $content .= "<td>{$item_name}</td>";
            $content .= "<td>{$item_quantity}</td>";
            $content .= "<td>" . number_format($item_price) . "원</td>";
            $content .= "<td>" . number_format($subtotal) . "원</td>";
            $content .= "</tr>";
            
            $total += $subtotal;
        }
    }

    $content .= "</table>";
    $content .= "<p><strong>총 결제금액:</strong> " . number_format($total) . "원</p>";
    
    return $content; {
    $content = '
    <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
        <div style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; padding: 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 28px;">🔔 새로운 주문이 접수되었습니다</h1>
            <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">즉시 확인이 필요합니다.</p>
        </div>
        
        <div style="padding: 30px; background: #f8f9fa;">
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h2 style="color: #2c3e50; margin-top: 0;">👤 고객 정보</h2>
                <p><strong>주문번호:</strong> ' . htmlspecialchars($order_id) . '</p>
                <p><strong>고객명:</strong> ' . htmlspecialchars($customer_info['customer_name']) . '</p>
                <p><strong>연락처:</strong> ' . htmlspecialchars($customer_info['customer_phone']) . '</p>
                <p><strong>이메일:</strong> ' . htmlspecialchars($customer_info['customer_email']) . '</p>
                <p><strong>주문일시:</strong> ' . date('Y-m-d H:i:s') . '</p>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h2 style="color: #2c3e50; margin-top: 0;">📦 주문 상품</h2>';
    
    $total_price = 0;
    foreach ($items as $item) {
        $content .= '
                <div style="border-bottom: 1px solid #eee; padding: 15px 0;">
                    <h3 style="color: #e74c3c; margin: 0 0 10px 0;">' . htmlspecialchars($item['product_name']) . '</h3>';
        
        if ($item['product_type'] === 'leaflet') {
            $content .= '
                    <p style="margin: 5px 0; color: #666;">인쇄색상: ' . htmlspecialchars($item['options']['color'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">종이종류: ' . htmlspecialchars($item['options']['paper_type'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">종이규격: ' . htmlspecialchars($item['options']['paper_size'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">수량: ' . htmlspecialchars($item['options']['quantity'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">인쇄면: ' . htmlspecialchars($item['options']['sides'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">주문방법: ' . htmlspecialchars($item['options']['order_type'] ?? '') . '</p>';
        } else {
            $content .= '
                    <p style="margin: 5px 0; color: #666;">재질: ' . htmlspecialchars($item['options']['material'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">크기: ' . htmlspecialchars($item['options']['size'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">수량: ' . htmlspecialchars($item['options']['quantity'] ?? '') . '</p>
                    <p style="margin: 5px 0; color: #666;">주문방법: ' . htmlspecialchars($item['options']['design'] ?? '') . '</p>';
        }
        
        $content .= '
                    <p style="margin: 10px 0 0 0; font-weight: bold; color: #e74c3c; text-align: right;">
                        ' . number_format($item['st_price_vat']) . '원 (VAT 포함)
                    </p>
                </div>';
        
        $total_price += $item['st_price_vat'];
    }
    
    $content .= '
                <div style="text-align: right; margin-top: 20px; padding-top: 20px; border-top: 2px solid #e74c3c;">
                    <h3 style="color: #2c3e50; margin: 0;">총 주문금액: ' . number_format($total_price) . '원</h3>
                </div>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h2 style="color: #2c3e50; margin-top: 0;">🚚 배송 정보</h2>
                <p><strong>우편번호:</strong> ' . htmlspecialchars($customer_info['delivery_zipcode']) . '</p>
                <p><strong>주소:</strong> ' . htmlspecialchars($customer_info['delivery_address']) . '</p>
                <p><strong>상세주소:</strong> ' . htmlspecialchars($customer_info['delivery_detail']) . '</p>
            </div>';
    
    if (!empty($customer_info['order_memo'])) {
        $content .= '
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h2 style="color: #2c3e50; margin-top: 0;">📝 주문 메모</h2>
                <p style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 0;">' . nl2br(htmlspecialchars($customer_info['order_memo'])) . '</p>
            </div>';
    }
    
    $content .= '
            <div style="background: #fff3cd; padding: 20px; border-radius: 10px; border-left: 4px solid #ffc107;">
                <h2 style="color: #856404; margin-top: 0;">⚡ 처리 필요 사항</h2>
                <p style="margin: 10px 0;">1. <strong>고객에게 연락</strong>하여 주문 확인</p>
                <p style="margin: 10px 0;">2. <strong>입금 확인</strong> 후 작업 시작</p>
                <p style="margin: 10px 0;">3. 디자인 파일 필요 시 고객에게 요청</p>
                <p style="margin: 10px 0;">4. 작업 완료 후 배송 처리</p>
            </div>
        </div>
    </div>';
    
    return $content;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📝 주문하기</title>
    <link rel="stylesheet" href="../css/modern-style.css">
    <style>
        .order-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #3498db;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .form-label.required::after {
            content: ' *';
            color: #e74c3c;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }
        
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .order-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #3498db;
        }
        
        .item-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        .item-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .option-item {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .option-label {
            font-weight: 600;
            color: #495057;
        }
        
        .item-price {
            text-align: right;
            font-size: 1.2rem;
            font-weight: 700;
            color: #e74c3c;
        }
        
        .total-summary {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
        }
        
        .file-upload-area {
            border: 2px dashed #3498db;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .file-upload-area:hover {
            background: #e3f2fd;
            border-color: #2980b9;
        }
        
        .btn-order {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 20px 50px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.3);
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 2rem;
        }
        
        .btn-order:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(231, 76, 60, 0.4);
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 헤더 -->
        <div class="hero-section" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 2rem 0; text-align: center; margin-bottom: 2rem; border-radius: 15px;">
            <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">📝 주문하기</h1>
            <p style="font-size: 1.2rem; opacity: 0.9;">주문 정보를 확인하고 배송 정보를 입력해주세요</p>
        </div>

        <?php if (isset($error_message)): ?>
        <div class="error-message">
            <strong>오류:</strong> <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="place_order">
            
            <!-- 주문 상품 정보 -->
            <div class="order-section">
                <h2 class="section-title">📦 주문 상품</h2>
                
                <?php foreach ($items as $item): ?>
                <div class="order-item">
                    <div class="item-name">
                        <?php echo $item['product_name']; ?>
                    </div>
                    
                    <div class="item-options">
                        <?php if ($item['product_type'] === 'leaflet'): ?>
                            <div class="option-item">
                                <span class="option-label">인쇄색상:</span> <?php echo $item['options']['color'] ?? '-'; ?>
                            </div>
                            <div class="option-item">
                                <span class="option-label">종이종류:</span> <?php echo $item['options']['paper_type'] ?? '-'; ?>
                            </div>
                            <div class="option-item">
                                <span class="option-label">종이규격:</span> <?php echo $item['options']['paper_size'] ?? '-'; ?>
                            </div>
                            <div class="option-item">
                                <span class="option-label">수량:</span> <?php echo $item['options']['quantity'] ?? '-'; ?>
                            </div>
                            <div class="option-item">
                                <span class="option-label">인쇄면:</span> <?php echo $item['options']['sides'] ?? '-'; ?>
                            </div>
                            <div class="option-item">
                                <span class="option-label">주문방법:</span> <?php echo $item['options']['order_type'] ?? '-'; ?>
                            </div>
                        <?php else: ?>
                            <div class="option-item">
                                <span class="option-label">재질:</span> <?php echo $item['options']['material'] ?? '-'; ?>
                            </div>
                            <div class="option-item">
                                <span class="option-label">크기:</span> <?php echo $item['options']['size'] ?? '-'; ?>
                            </div>
                            <div class="option-item">
                                <span class="option-label">수량:</span> <?php echo $item['options']['quantity'] ?? '-'; ?>
                            </div>
                            <div class="option-item">
                                <span class="option-label">옵션:</span> <?php echo $item['options']['options'] ?? '-'; ?>
                            </div>
                            <div class="option-item">
                                <span class="option-label">주문방법:</span> <?php echo $item['options']['design'] ?? '-'; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="item-price">
                        <?php echo number_format($item['st_price_vat']); ?>원 (VAT 포함)
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- 고객 정보 -->
            <div class="order-section">
                <h2 class="section-title">👤 주문자 정보</h2>
                
                <div class="form-group">
                    <label class="form-label required">이름</label>
                    <input type="text" name="customer_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">연락처</label>
                    <input type="tel" name="customer_phone" class="form-input" placeholder="010-0000-0000" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">이메일</label>
                    <input type="email" name="customer_email" class="form-input" placeholder="example@email.com">
                </div>
            </div>

            <!-- 배송 정보 -->
            <div class="order-section">
                <h2 class="section-title">🚚 배송 정보</h2>
                
                <div class="form-group">
                    <label class="form-label">우편번호</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="sample6_postcode" name="delivery_zipcode" class="form-input" placeholder="우편번호" readonly style="flex: 1;">
                        <button type="button" onclick="sample6_execDaumPostcode()" style="padding: 12px 20px; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; white-space: nowrap;">🔍 주소검색</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">주소</label>
                    <input type="text" id="sample6_address" name="delivery_address" class="form-input" placeholder="주소" readonly required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">상세주소</label>
                    <input type="text" id="sample6_detailAddress" name="delivery_detail" class="form-input" placeholder="상세주소를 입력해주세요">
                </div>
                
                <div class="form-group">
                    <label class="form-label">참고항목</label>
                    <input type="text" id="sample6_extraAddress" class="form-input" placeholder="참고항목" readonly>
                </div>
            </div>

            <!-- 파일 업로드 -->
            <div class="order-section">
                <h2 class="section-title">📎 파일 업로드</h2>
                <p style="color: #6c757d; margin-bottom: 1rem;">디자인 파일이나 인쇄할 파일을 업로드해주세요. (선택사항)</p>
                
                <div class="file-upload-area">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📁</div>
                    <input type="file" name="upload_files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.ai,.psd" style="margin-bottom: 1rem;">
                    <p style="color: #6c757d; font-size: 0.9rem;">
                        지원 파일: PDF, JPG, PNG, AI, PSD<br>
                        최대 파일 크기: 10MB
                    </p>
                </div>
            </div>

            <!-- 주문 메모 -->
            <div class="order-section">
                <h2 class="section-title">📝 주문 메모</h2>
                
                <div class="form-group">
                    <label class="form-label">요청사항</label>
                    <textarea name="order_memo" class="form-input form-textarea" placeholder="추가 요청사항이 있으시면 입력해주세요"></textarea>
                </div>
            </div>

            <!-- 주문 요약 -->
            <div class="total-summary">
                <h3 style="margin-bottom: 1.5rem;">💰 결제 정보</h3>
                <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
                    <div>
                        <div style="font-size: 2rem; font-weight: 700;"><?php echo count($items); ?>개</div>
                        <div style="opacity: 0.9;">주문 상품</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: 700;"><?php echo number_format($total); ?>원</div>
                        <div style="opacity: 0.9;">세전 금액</div>
                    </div>
                    <div>
                        <div style="font-size: 2.5rem; font-weight: 700;"><?php echo number_format($total_vat); ?>원</div>
                        <div style="opacity: 0.9;">최종 결제금액</div>
                    </div>
                </div>
                
                <p style="opacity: 0.9; margin-bottom: 0;">
                    📋 입금 후 작업 진행 | 📦 택배비 착불 | 📞 문의: 1688-2384
                </p>
            </div>

            <button type="submit" class="btn-order">
                🚀 주문 완료하기
            </button>
        </form>
    </div>
    
    <!-- 다음 우편번호 API -->
    <script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
    <script>
        function sample6_execDaumPostcode() {
            new daum.Postcode({
                oncomplete: function(data) {
                    var addr = ''; // 주소 변수
                    var extraAddr = ''; // 참고항목 변수

                    if (data.userSelectedType === 'R') { // 사용자가 도로명 주소를 선택했을 경우
                        addr = data.roadAddress;
                    } else { // 사용자가 지번 주소를 선택했을 경우(J)
                        addr = data.jibunAddress;
                    }

                    if(data.userSelectedType === 'R'){
                        if(data.bname !== '' && /[동|로|가]$/g.test(data.bname)){
                            extraAddr += data.bname;
                        }
                        if(data.buildingName !== '' && data.apartment === 'Y'){
                            extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                        }
                        if(extraAddr !== ''){
                            extraAddr = ' (' + extraAddr + ')';
                        }
                        document.getElementById("sample6_extraAddress").value = extraAddr;
                    } else {
                        document.getElementById("sample6_extraAddress").value = '';
                    }

                    document.getElementById('sample6_postcode').value = data.zonecode;
                    document.getElementById("sample6_address").value = addr;
                    document.getElementById("sample6_detailAddress").focus();
                }
            }).open();
        }
    </script>
</body>
</html>
<?php } catch (Exception $e) {
    error_log("Order processing error: " . $e->getMessage());
    echo "<script>alert('주문 처리 중 오류가 발생했습니다.'); history.back();</script>";
    exit;
} ?>