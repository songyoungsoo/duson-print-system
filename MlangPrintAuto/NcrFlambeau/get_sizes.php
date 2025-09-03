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

$style = $_GET['style'] ?? '';
if (empty($style)) {
    safe_json_response(false, null, '구분을 선택해주세요.');
}

try {
    $options = getDropdownOptions($db, "mlangprintauto_transactioncate", [
        'Ttable' => 'NcrFlambeau',
        'BigNo' => $style
    ], 'no ASC');
    
    error_log("NcrFlambeau 규격 옵션 조회: style=$style, 결과=" . count($options) . "개");
    
    safe_json_response(true, $options, '규격 옵션 조회 완료');
    
} catch (Exception $e) {
    error_log("NcrFlambeau 규격 옵션 조회 오류: " . $e->getMessage());
    safe_json_response(false, null, '규격 옵션 조회 중 오류가 발생했습니다.');
}

mysqli_close($db);
?>