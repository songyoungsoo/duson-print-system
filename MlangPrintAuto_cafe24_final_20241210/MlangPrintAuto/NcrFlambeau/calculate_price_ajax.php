<?php
// 명함 성공 패턴 적용 - 안전한 JSON 응답 처리
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 안전한 JSON 응답 함수 (명함 패턴)
function safe_json_response($success = true, $data = null, $message = '') {
    ob_clean(); // 이전 출력 완전 정리
    
    $response = array(
        'success' => $success,
        'message' => $message
    );
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// 파라미터 받기
$MY_type = $_POST['MY_type'] ?? '';
$MY_Fsd = $_POST['MY_Fsd'] ?? '';
$PN_type = $_POST['PN_type'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? '';
$ordertype = $_POST['ordertype'] ?? '';

// 필수 파라미터 검증
if (empty($MY_type) || empty($MY_Fsd) || empty($PN_type) || empty($MY_amount) || empty($ordertype)) {
    safe_json_response(false, null, '모든 옵션을 선택해주세요.');
}

// 디버그 로그
error_log("NcrFlambeau 가격 계산 요청: MY_type=$MY_type, MY_Fsd=$MY_Fsd, PN_type=$PN_type, MY_amount=$MY_amount, ordertype=$ordertype");

try {
    // 가격 계산 쿼리
    $query = "SELECT * FROM MlangPrintAuto_NcrFlambeau 
              WHERE style = ? AND Section = ? AND TreeSelect = ? AND quantity = ?";
    
    $stmt = mysqli_prepare($db, $query);
    if (!$stmt) {
        throw new Exception('쿼리 준비 실패: ' . mysqli_error($db));
    }
    
    mysqli_stmt_bind_param($stmt, "ssss", $MY_type, $MY_Fsd, $PN_type, $MY_amount);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('쿼리 실행 실패: ' . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // 가격 계산
        $base_price = intval($row['money']);
        $design_price = ($ordertype === 'total') ? intval($row['DesignMoney']) : 0;
        $total_price = $base_price + $design_price;
        $vat_price = intval($total_price * 1.1);
        
        $price_data = [
            'base_price' => $base_price,
            'design_price' => $design_price,
            'total_price' => $total_price,
            'vat_price' => $vat_price,
            'formatted' => [
                'base_price' => number_format($base_price) . '원',
                'design_price' => number_format($design_price) . '원',
                'total_price' => number_format($total_price) . '원',
                'vat_price' => number_format($vat_price) . '원'
            ]
        ];
        
        // 성공 로그
        error_log("NcrFlambeau 가격 계산 성공: " . json_encode($price_data));
        
        safe_json_response(true, $price_data, '가격 계산 완료');
        
    } else {
        // 데이터 없음
        error_log("NcrFlambeau 가격 데이터 없음: style=$MY_type, section=$MY_Fsd, treeselect=$PN_type, quantity=$MY_amount");
        safe_json_response(false, null, '해당 조건의 가격 정보를 찾을 수 없습니다.');
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    error_log("NcrFlambeau 가격 계산 오류: " . $e->getMessage());
    safe_json_response(false, null, '가격 계산 중 오류가 발생했습니다: ' . $e->getMessage());
}

mysqli_close($db);
?>