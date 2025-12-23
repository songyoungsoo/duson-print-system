<?php
include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 파라미터 받기 (자석스티커는 Section 필드 사용)
$MY_type = $_GET['MY_type'] ?? '';
$Section = $_GET['Section'] ?? '';
$POtype = $_GET['POtype'] ?? '';
$MY_amount = $_GET['MY_amount'] ?? '';
$ordertype = $_GET['ordertype'] ?? '';

if (empty($MY_type) || empty($Section) || empty($POtype) || empty($MY_amount) || empty($ordertype)) {
    error_response('모든 옵션을 선택해주세요.');
}

$TABLE = "mlangprintauto_msticker";

// 데이터베이스에서 가격 정보 조회
$query = "SELECT money, DesignMoney 
          FROM $TABLE 
          WHERE style='" . mysqli_real_escape_string($db, $MY_type) . "' 
          AND Section='" . mysqli_real_escape_string($db, $Section) . "' 
          AND quantity='" . mysqli_real_escape_string($db, $MY_amount) . "'";

$result = mysqli_query($db, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    
    // 자석스티커 가격 구조 (money, DesignMoney 컬럼 사용)
    $base_price = (int)$row['money'];
    $design_price_db = (int)$row['DesignMoney'];
    
    // 편집비 적용 (ordertype이 'total'일 때만)
    $design_price = ($ordertype === 'total') ? $design_price_db : 0;
    
    $total_price = $base_price + $design_price;
    $total_with_vat = $total_price * 1.1;

    $response_data = [
        'base_price' => $base_price,
        'design_price' => $design_price,
        'total_price' => $total_price,
        'total_with_vat' => (int)$total_with_vat
    ];
    
    success_response($response_data, '가격 계산 완료');

} else {
    error_response('해당 조건의 가격 정보를 찾을 수 없습니다. (종류: ' . $MY_type . ', 규격: ' . $Section . ', 수량: ' . $MY_amount . ')');
}

mysqli_close($db);
?>