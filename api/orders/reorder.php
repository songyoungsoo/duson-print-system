<?php
/**
 * 주문 재주문 API 엔드포인트
 * 경로: /api/orders/reorder.php
 */

session_start();
include "../../db.php";
include "../../includes/functions.php";

header('Content-Type: application/json; charset=utf-8');

// 로그인 체크
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => '로그인이 필요합니다.'
    ]);
    exit;
}

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => '허용되지 않은 요청 방식입니다.'
    ]);
    exit;
}

// JSON 입력 파싱
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['order_no'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => '주문번호가 필요합니다.'
    ]);
    exit;
}

$order_no = $input['order_no'];
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';
$session_id = session_id();

try {
    // 원본 주문 데이터 조회 (본인 주문인지 확인)
    $query = "SELECT * FROM MlangOrder_PrintAuto WHERE order_no = ? AND customer_name = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ss", $order_no, $user_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $original_order = mysqli_fetch_assoc($result);
    
    if (!$original_order) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => '해당 주문을 찾을 수 없거나 권한이 없습니다.'
        ]);
        exit;
    }
    
    // 장바구니에 추가하기 위한 데이터 구성
    $product_data = [];
    
    // 상품 타입별로 데이터 매핑
    $product_type = $original_order['product_code'];
    $Type_1 = $original_order['Type_1']; // JSON 형태의 상세 정보
    
    // Type_1이 JSON인 경우 파싱
    $detail_options = [];
    if (!empty($Type_1)) {
        $decoded = json_decode($Type_1, true);
        if ($decoded) {
            $detail_options = $decoded;
        }
    }
    
    // shop_temp 테이블에 추가할 기본 데이터
    $insert_data = [
        'session_id' => $session_id,
        'product_type' => $product_type,
        'product_name' => $original_order['product_name'],
        'options_summary' => $original_order['options_summary'],
        'price' => $original_order['unit_price'],
        'vat_price' => $original_order['total_price'],
        'qty' => $original_order['qty'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // 상품별 특정 필드 추가
    switch ($product_type) {
        case 'sticker':
            if (!empty($detail_options)) {
                $insert_data['jong'] = $detail_options['jong'] ?? '';
                $insert_data['garo'] = $detail_options['garo'] ?? 0;
                $insert_data['sero'] = $detail_options['sero'] ?? 0;
                $insert_data['mesu'] = $detail_options['mesu'] ?? 0;
                $insert_data['uhyung'] = $detail_options['uhyung'] ?? '';
                $insert_data['domusong'] = $detail_options['domusong'] ?? '';
            }
            break;
            
        case 'leaflet':
        case 'inserted':
            if (!empty($detail_options)) {
                $insert_data['MY_type'] = $detail_options['MY_type'] ?? '';
                $insert_data['MY_Fsd'] = $detail_options['MY_Fsd'] ?? '';
                $insert_data['PN_type'] = $detail_options['PN_type'] ?? '';
                $insert_data['POtype'] = $detail_options['POtype'] ?? '';
                $insert_data['MY_amount'] = $detail_options['MY_amount'] ?? '';
                $insert_data['ordertype'] = $detail_options['ordertype'] ?? '';
            }
            break;
            
        case 'namecard':
            if (!empty($detail_options)) {
                $insert_data['card_type'] = $detail_options['card_type'] ?? '';
                $insert_data['card_size'] = $detail_options['card_size'] ?? '';
                $insert_data['card_paper'] = $detail_options['card_paper'] ?? '';
            }
            break;
            
        case 'envelope':
            if (!empty($detail_options)) {
                $insert_data['envelope_type'] = $detail_options['envelope_type'] ?? '';
                $insert_data['envelope_paper'] = $detail_options['envelope_paper'] ?? '';
            }
            break;
    }
    
    // 장바구니에 기존 동일 항목이 있는지 확인
    $check_query = "SELECT no FROM shop_temp WHERE session_id = ? AND product_type = ? AND options_summary = ?";
    $check_stmt = mysqli_prepare($db, $check_query);
    mysqli_stmt_bind_param($check_stmt, "sss", $session_id, $product_type, $insert_data['options_summary']);
    mysqli_stmt_execute($check_stmt);
    $existing_item = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($existing_item) > 0) {
        // 기존 항목이 있다면 수량 증가
        $existing_row = mysqli_fetch_assoc($existing_item);
        $update_query = "UPDATE shop_temp SET qty = qty + ?, updated_at = NOW() WHERE no = ?";
        $update_stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($update_stmt, "ii", $insert_data['qty'], $existing_row['no']);
        mysqli_stmt_execute($update_stmt);
        
        $message = '장바구니의 기존 항목에 수량이 추가되었습니다.';
    } else {
        // 새 항목으로 추가
        $columns = array_keys($insert_data);
        $placeholders = str_repeat('?,', count($insert_data) - 1) . '?';
        $values = array_values($insert_data);
        
        $insert_query = "INSERT INTO shop_temp (" . implode(',', $columns) . ") VALUES ($placeholders)";
        $insert_stmt = mysqli_prepare($db, $insert_query);
        
        // 타입 문자열 생성
        $types = str_repeat('s', count($values));
        mysqli_stmt_bind_param($insert_stmt, $types, ...$values);
        mysqli_stmt_execute($insert_stmt);
        
        $message = '장바구니에 추가되었습니다.';
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'redirect_url' => '/mlangprintauto/shop/cart.php'
    ]);
    
} catch (Exception $e) {
    error_log("재주문 오류: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '재주문 처리 중 오류가 발생했습니다.'
    ]);
}

// 데이터베이스 연결 종료
if (isset($stmt)) mysqli_stmt_close($stmt);
if (isset($check_stmt)) mysqli_stmt_close($check_stmt);
if (isset($update_stmt)) mysqli_stmt_close($update_stmt);
if (isset($insert_stmt)) mysqli_stmt_close($insert_stmt);
mysqli_close($db);
?>