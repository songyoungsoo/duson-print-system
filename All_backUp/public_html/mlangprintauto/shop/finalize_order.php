<?php
/**
 * 주문 확정 처리 - 장바구니에서 최종 주문으로 전환
 * - shop_temp 데이터를 mlangorder_printauto로 이동
 * - 임시 업로드 폴더를 주문번호 폴더로 변경
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

include "../db.php";
$connect = $db;

if (!$connect) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 연결 실패']);
    exit;
}

mysqli_set_charset($connect, "utf8mb4");

$session_id = session_id();

// POST 데이터 받기
$customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
$customer_phone = isset($_POST['customer_phone']) ? trim($_POST['customer_phone']) : '';
$customer_email = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
$customer_zip = isset($_POST['customer_zip']) ? trim($_POST['customer_zip']) : '';
$customer_address1 = isset($_POST['customer_address1']) ? trim($_POST['customer_address1']) : '';
$customer_address2 = isset($_POST['customer_address2']) ? trim($_POST['customer_address2']) : '';
$delivery_method = isset($_POST['delivery_method']) ? trim($_POST['delivery_method']) : '택배';
$payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '무통장입금';
$order_memo = isset($_POST['order_memo']) ? trim($_POST['order_memo']) : '';

// 입력값 검증
if (empty($customer_name) || empty($customer_phone)) {
    echo json_encode(['success' => false, 'message' => '이름과 연락처는 필수입니다.']);
    exit;
}

// 장바구니 데이터 조회
$query = "SELECT * FROM shop_temp WHERE session_id = ?";
$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, "s", $session_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => '장바구니가 비어있습니다.']);
    exit;
}

$cart_items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $cart_items[] = $row;
}
mysqli_stmt_close($stmt);

// 트랜잭션 시작
mysqli_begin_transaction($connect);

try {
    $order_ids = [];
    
    foreach ($cart_items as $item) {
        // 주문번호 생성 (mlangorder_printauto의 다음 auto_increment 값)
        $result = mysqli_query($connect, "SELECT MAX(no) as max_no FROM mlangorder_printauto");
        $row = mysqli_fetch_assoc($result);
        $order_no = ($row['max_no'] ?? 0) + 1;
        
        // 제품 타입 결정
        $product_type = $item['product_type'] ?? 'leaflet';
        $type_map = [
            'leaflet' => '전단지',
            'namecard' => '명함',
            'envelope' => '봉투',
            'sticker' => '스티커'
        ];
        $type_display = $type_map[$product_type] ?? '전단지';
        
        // Type_1 구성 (주문 상세 정보)
        $type_1_parts = [];
        if (!empty($item['MY_type'])) $type_1_parts[] = "색상: " . $item['MY_type'];
        if (!empty($item['MY_Fsd'])) $type_1_parts[] = "용지: " . $item['MY_Fsd'];
        if (!empty($item['PN_type'])) $type_1_parts[] = "규격: " . $item['PN_type'];
        if (!empty($item['POtype'])) $type_1_parts[] = "인쇄면: " . ($item['POtype'] == '1' ? '단면' : '양면');
        if (!empty($item['MY_amount'])) $type_1_parts[] = "수량: " . $item['MY_amount'];
        if (!empty($item['ordertype'])) $type_1_parts[] = "주문타입: " . $item['ordertype'];
        
        $type_1 = implode(" | ", $type_1_parts);
        
        // 가격 정보
        $money_4 = intval($item['st_price'] ?? 0); // 인쇄비
        $money_2 = 0; // 디자인비 (필요시 계산)
        $money_5 = intval($item['st_price_vat'] ?? 0); // 부가세 포함 총액
        
        // 추가 옵션 총액
        $additional_options_total = intval($item['additional_options_total'] ?? 0);
        if ($additional_options_total > 0) {
            $money_4 += $additional_options_total;
            $money_5 = intval($money_4 * 1.1);
        }
        
        // 📁 파일 업로드 폴더 처리 (구버전 방식)
        // ✅ 구버전: 폴더 이동 없이 경로만 참조
        $img_folder = $item['upload_folder'] ?? $item['ImgFolder'] ?? '';
        $thing_cate = '';
        
        if (!empty($img_folder)) {
            // ImgFolder 경로에서 파일 목록 가져오기
            $full_path = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $img_folder;
            
            if (is_dir($full_path)) {
                $files = array_diff(scandir($full_path), ['.', '..']);
                if (!empty($files)) {
                    $thing_cate = reset($files);
                }
                error_log("✅ 구버전 경로 사용: ImgFolder/{$img_folder}");
            } else {
                error_log("⚠️ 폴더 없음: {$full_path}");
            }
        }
        
        // mlangorder_printauto에 삽입
        $insert_query = "INSERT INTO mlangorder_printauto (
            no, Type, ImgFolder, Type_1, money_2, money_4, money_5,
            name, email, zip, zip1, zip2, phone, Hendphone,
            delivery, bank, cont, date, OrderStyle, ThingCate,
            coating_enabled, coating_type, coating_price,
            folding_enabled, folding_type, folding_price,
            creasing_enabled, creasing_lines, creasing_price,
            premium_options
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, NOW(), '2', ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?
        )";
        
        $stmt = mysqli_prepare($connect, $insert_query);
        
        // 추가 옵션 데이터 파싱
        $additional_options = json_decode($item['additional_options'] ?? '{}', true);
        $coating_enabled = intval($additional_options['coating_enabled'] ?? 0);
        $coating_type = $additional_options['coating_type'] ?? '';
        $coating_price = intval($additional_options['coating_price'] ?? 0);
        $folding_enabled = intval($additional_options['folding_enabled'] ?? 0);
        $folding_type = $additional_options['folding_type'] ?? '';
        $folding_price = intval($additional_options['folding_price'] ?? 0);
        $creasing_enabled = intval($additional_options['creasing_enabled'] ?? 0);
        $creasing_lines = intval($additional_options['creasing_lines'] ?? 0);
        $creasing_price = intval($additional_options['creasing_price'] ?? 0);
        $premium_options_json = $item['additional_options'] ?? '{}';
        
        mysqli_stmt_bind_param($stmt, "isssiiiisssssssssissiisiisiis",
            $order_no, $type_display, $img_folder, $type_1, $money_2, $money_4, $money_5,
            $customer_name, $customer_email, $customer_zip, $customer_address1, $customer_address2,
            $customer_phone, $customer_phone, $delivery_method, $payment_method, $order_memo,
            $thing_cate,
            $coating_enabled, $coating_type, $coating_price,
            $folding_enabled, $folding_type, $folding_price,
            $creasing_enabled, $creasing_lines, $creasing_price,
            $premium_options_json
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("주문 저장 실패: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        $order_ids[] = $order_no;
        
        error_log("✅ 주문 생성 완료: 주문번호 $order_no");
    }
    
    // 장바구니 비우기
    $delete_query = "DELETE FROM shop_temp WHERE session_id = ?";
    $stmt = mysqli_prepare($connect, $delete_query);
    mysqli_stmt_bind_param($stmt, "s", $session_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    // 트랜잭션 커밋
    mysqli_commit($connect);
    
    echo json_encode([
        'success' => true,
        'message' => '주문이 성공적으로 완료되었습니다.',
        'order_ids' => $order_ids
    ]);
    
} catch (Exception $e) {
    // 트랜잭션 롤백
    mysqli_rollback($connect);
    
    error_log("❌ 주문 처리 오류: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($connect);
?>
