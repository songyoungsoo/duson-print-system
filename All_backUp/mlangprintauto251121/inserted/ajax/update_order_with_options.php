<?php
/**
 * 주문 완료 시 추가 옵션 정보를 mlangorder_printauto 테이블에 저장
 * 
 * 목적: shop_temp → mlangorder_printauto 데이터 이전 시 추가 옵션 포함
 * 특징: 기존 주문 처리 시스템과 완벽 통합
 * 
 * @version 1.0
 * @date 2025-01-08
 */

session_start();

// 데이터베이스 연결
include "../../../db.php";
$connect = $db;

if (!$connect) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 연결 실패']);
    exit;
}

mysqli_set_charset($connect, "utf8");

/**
 * shop_temp에서 주문 데이터 가져오기 (추가 옵션 포함)
 */
function getOrderDataWithOptions($connect, $order_id) {
    $query = "SELECT * FROM shop_temp WHERE id = ? LIMIT 1";
    $stmt = safe_mysqli_prepare($connect, $query);
    
    if (!$stmt) {
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return false;
}

/**
 * mlangorder_printauto에 주문 데이터 저장 (추가 옵션 포함)
 */
function saveOrderWithOptions($connect, $order_data, $user_info = []) {
    // 기본 주문 정보
    $session_id = $order_data['session_id'];
    $product_type = $order_data['product_type'];
    $total_price = $order_data['st_price_vat'];
    
    // 추가 옵션 정보
    $coating_enabled = $order_data['coating_enabled'] ?? 0;
    $coating_type = $order_data['coating_type'] ?? null;
    $coating_price = $order_data['coating_price'] ?? 0;
    
    $folding_enabled = $order_data['folding_enabled'] ?? 0;
    $folding_type = $order_data['folding_type'] ?? null;
    $folding_price = $order_data['folding_price'] ?? 0;
    
    $creasing_enabled = $order_data['creasing_enabled'] ?? 0;
    $creasing_lines = $order_data['creasing_lines'] ?? 0;
    $creasing_price = $order_data['creasing_price'] ?? 0;
    
    $additional_options_total = $order_data['additional_options_total'] ?? 0;
    
    // 사용자 정보
    $user_name = $user_info['name'] ?? '비회원';
    $user_email = $user_info['email'] ?? '';
    $user_phone = $user_info['phone'] ?? '';
    
    $insert_query = "INSERT INTO mlangorder_printauto (
        session_id, product_type, user_name, user_email, user_phone,
        MY_type, PN_type, MY_Fsd, MY_amount, POtype, ordertype,
        order_price, order_price_vat, work_memo, upload_method, uploaded_files_info,
        coating_enabled, coating_type, coating_price,
        folding_enabled, folding_type, folding_price,
        creasing_enabled, creasing_lines, creasing_price,
        additional_options_total,
        order_date, order_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')";
    
    $stmt = safe_mysqli_prepare($connect, $insert_query);
    
    if (!$stmt) {
        error_log("주문 저장 쿼리 준비 실패: " . mysqli_error($connect));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "sssssssssssiisssisisiiii",
        $session_id, $product_type, $user_name, $user_email, $user_phone,
        $order_data['MY_type'], $order_data['PN_type'], $order_data['MY_Fsd'], 
        $order_data['MY_amount'], $order_data['POtype'], $order_data['ordertype'],
        $order_data['st_price'], $total_price, $order_data['work_memo'], 
        $order_data['upload_method'], $order_data['uploaded_files_info'],
        $coating_enabled, $coating_type, $coating_price,
        $folding_enabled, $folding_type, $folding_price,
        $creasing_enabled, $creasing_lines, $creasing_price,
        $additional_options_total
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $order_id = mysqli_insert_id($connect);
        error_log("주문 저장 성공 - Order ID: $order_id, 추가옵션총액: $additional_options_total");
        return $order_id;
    } else {
        error_log("주문 저장 실패: " . mysqli_stmt_error($stmt));
        return false;
    }
}

/**
 * 주문 완료 후 shop_temp 데이터 정리
 */
function cleanupTempOrder($connect, $session_id) {
    $query = "DELETE FROM shop_temp WHERE session_id = ?";
    $stmt = safe_mysqli_prepare($connect, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $session_id);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_affected_rows($stmt) > 0;
    }
    
    return false;
}

// AJAX 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $temp_order_id = intval($_POST['temp_order_id'] ?? 0);
    $user_info = [
        'name' => $_POST['user_name'] ?? '비회원',
        'email' => $_POST['user_email'] ?? '',
        'phone' => $_POST['user_phone'] ?? ''
    ];
    
    if ($temp_order_id <= 0) {
        echo json_encode(['success' => false, 'message' => '올바르지 않은 주문 ID입니다.']);
        exit;
    }
    
    // 임시 주문 데이터 가져오기
    $order_data = getOrderDataWithOptions($connect, $temp_order_id);
    
    if (!$order_data) {
        echo json_encode(['success' => false, 'message' => '주문 데이터를 찾을 수 없습니다.']);
        exit;
    }
    
    // 주문 데이터 저장
    $final_order_id = saveOrderWithOptions($connect, $order_data, $user_info);
    
    if ($final_order_id) {
        // 임시 데이터 정리
        cleanupTempOrder($connect, $order_data['session_id']);
        
        echo json_encode([
            'success' => true, 
            'message' => '주문이 완료되었습니다.',
            'order_id' => $final_order_id,
            'additional_options_total' => $order_data['additional_options_total'] ?? 0
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => '주문 처리 중 오류가 발생했습니다.']);
    }
    
    exit;
}

// GET 요청: 임시 주문 정보 조회 (추가 옵션 포함)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $temp_order_id = intval($_GET['temp_order_id'] ?? 0);
    
    if ($temp_order_id <= 0) {
        echo json_encode(['success' => false, 'message' => '올바르지 않은 주문 ID입니다.']);
        exit;
    }
    
    $order_data = getOrderDataWithOptions($connect, $temp_order_id);
    
    if ($order_data) {
        // 추가 옵션 정보 포함하여 반환
        $response_data = [
            'success' => true,
            'data' => $order_data,
            'additional_options' => [
                'coating' => [
                    'enabled' => $order_data['coating_enabled'] ?? 0,
                    'type' => $order_data['coating_type'] ?? null,
                    'price' => $order_data['coating_price'] ?? 0
                ],
                'folding' => [
                    'enabled' => $order_data['folding_enabled'] ?? 0,
                    'type' => $order_data['folding_type'] ?? null,
                    'price' => $order_data['folding_price'] ?? 0
                ],
                'creasing' => [
                    'enabled' => $order_data['creasing_enabled'] ?? 0,
                    'lines' => $order_data['creasing_lines'] ?? 0,
                    'price' => $order_data['creasing_price'] ?? 0
                ],
                'total' => $order_data['additional_options_total'] ?? 0
            ]
        ];
        
        echo json_encode($response_data);
    } else {
        echo json_encode(['success' => false, 'message' => '주문 정보를 찾을 수 없습니다.']);
    }
    
    exit;
}

mysqli_close($connect);
?>