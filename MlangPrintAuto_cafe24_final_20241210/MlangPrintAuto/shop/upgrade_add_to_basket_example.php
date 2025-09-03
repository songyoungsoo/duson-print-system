<?php
/**
 * 파일 업로드 기능이 포함된 장바구니 추가 예시
 * 경로: MlangPrintAuto/shop/upgrade_add_to_basket_example.php
 * 
 * 기존 add_to_basket.php 파일들을 이 방식으로 업그레이드하면 됩니다.
 */

session_start();
include "../../db.php";
include "../../includes/functions.php";
include "../shop_temp_helper.php";
include "file_management_helper.php";

header('Content-Type: application/json; charset=utf-8');

try {
    check_db_connection($db);
    mysqli_set_charset($db, "utf8");
    
    $session_id = session_id();
    
    // 기본 장바구니 데이터 수집
    $product_type = $_POST['product_type'] ?? 'msticker';
    $MY_type = $_POST['MY_type'] ?? '';
    $MY_Fsd = $_POST['MY_Fsd'] ?? '';
    $MY_amount = $_POST['MY_amount'] ?? '';
    $ordertype = $_POST['ordertype'] ?? 'print';
    $st_price = intval($_POST['st_price'] ?? 0);
    $st_price_vat = intval($_POST['st_price_vat'] ?? 0);
    $MY_comment = $_POST['MY_comment'] ?? '';
    
    // 필수 필드 검증
    if (empty($MY_type) || empty($MY_Fsd) || empty($MY_amount)) {
        error_response('필수 옵션을 모두 선택해주세요.');
    }
    
    if ($st_price <= 0) {
        error_response('올바른 가격 정보가 필요합니다.');
    }
    
    // 장바구니 데이터 준비
    $cart_data = [
        'MY_type' => $MY_type,
        'MY_Fsd' => $MY_Fsd,
        'MY_amount' => $MY_amount,
        'ordertype' => $ordertype,
        'st_price' => $st_price,
        'st_price_vat' => $st_price_vat,
        'MY_comment' => $MY_comment
    ];
    
    // 상품별 장바구니 추가 함수 호출
    $cart_item_no = null;
    switch ($product_type) {
        case 'msticker':
            if (addMstickerToCart($db, $session_id, $cart_data)) {
                // 방금 추가된 아이템의 번호 가져오기
                $cart_item_no = mysqli_insert_id($db);
            }
            break;
            
        case 'ncrflambeau':
            if (addNcrflambeauToCart($db, $session_id, $cart_data)) {
                $cart_item_no = mysqli_insert_id($db);
            }
            break;
            
        case 'littleprint':
            if (addLittleprintToCart($db, $session_id, $cart_data)) {
                $cart_item_no = mysqli_insert_id($db);
            }
            break;
            
        default:
            error_response('지원하지 않는 상품 유형입니다.');
    }
    
    if (!$cart_item_no) {
        error_response('장바구니 추가에 실패했습니다.');
    }
    
    // 파일 업로드 처리 (있는 경우)
    $uploaded_files = [];
    if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
        $log_info = generateFileLogInfo($product_type);
        $upload_dir = createFileUploadDirectory($log_info);
        
        $files = $_FILES['files'];
        $file_count = count($files['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            
            $original_name = $files['name'][$i];
            $tmp_name = $files['tmp_name'][$i];
            $file_size = $files['size'][$i];
            $file_type = $files['type'][$i];
            
            // 파일 검증 및 저장
            $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'ai', 'psd'];
            
            if (!in_array($file_ext, $allowed_extensions) || $file_size > 50 * 1024 * 1024) {
                continue;
            }
            
            $safe_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_name);
            $saved_name = date('His') . '_' . $safe_name;
            $upload_path = "$upload_dir/$saved_name";
            
            if (move_uploaded_file($tmp_name, $upload_path)) {
                $file_info = [
                    'original_name' => $original_name,
                    'saved_name' => $saved_name,
                    'file_size' => $file_size,
                    'file_type' => $file_type,
                    'upload_path' => $upload_path
                ];
                
                if (addFileToCartItem($db, $cart_item_no, $file_info, $log_info)) {
                    $uploaded_files[] = $original_name;
                }
            }
        }
    }
    
    // 성공 응답
    success_response([
        'cart_item_no' => $cart_item_no,
        'product_type' => $product_type,
        'uploaded_files' => $uploaded_files,
        'file_count' => count($uploaded_files)
    ], '장바구니에 추가되었습니다.');
    
} catch (Exception $e) {
    error_response('장바구니 추가 중 오류가 발생했습니다: ' . $e->getMessage());
}
?>