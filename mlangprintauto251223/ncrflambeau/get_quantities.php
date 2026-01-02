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
$section = $_GET['section'] ?? '';
$treeselect = $_GET['treeselect'] ?? '';

if (empty($style) || empty($section) || empty($treeselect)) {
    safe_json_response(false, null, '모든 옵션을 선택해주세요.');
}

try {
    $TABLE = "mlangprintauto_ncrflambeau";
    $query = "SELECT DISTINCT quantity 
              FROM $TABLE 
              WHERE style='" . mysqli_real_escape_string($db, $style) . "' 
              AND Section='" . mysqli_real_escape_string($db, $section) . "' 
              AND TreeSelect='" . mysqli_real_escape_string($db, $treeselect) . "'
              AND quantity IS NOT NULL 
              ORDER BY CAST(quantity AS UNSIGNED) ASC";

    $result = mysqli_query($db, $query);
    $quantities = [];

    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $quantities[] = [
                'value' => $row['quantity'],
                'text' => format_number($row['quantity']) . '권'
            ];
        }
    }
    
    error_log("NcrFlambeau 수량 옵션 조회: style=$style, section=$section, treeselect=$treeselect, 결과=" . count($quantities) . "개");
    
    safe_json_response(true, $quantities, '수량 옵션 조회 완료');
    
} catch (Exception $e) {
    error_log("NcrFlambeau 수량 옵션 조회 오류: " . $e->getMessage());
    safe_json_response(false, null, '수량 옵션 조회 중 오류가 발생했습니다.');
}

mysqli_close($db);
?>