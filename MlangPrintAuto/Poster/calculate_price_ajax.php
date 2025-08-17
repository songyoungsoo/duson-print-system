<?php
// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 공통 함수 포함
include "../../includes/functions.php";
include "../../db.php";

// 데이터베이스 연결 체크
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// GET 방식으로 데이터 가져오기
$MY_type = $_GET['MY_type'] ?? '';        // 구분 (590)
$Section = $_GET['Section'] ?? '';        // 재질
$PN_type = $_GET['PN_type'] ?? '';        // 규격
$MY_amount = $_GET['MY_amount'] ?? '';    // 수량
$POtype = $_GET['POtype'] ?? '';          // 인쇄면 (1, 2)
$ordertype = $_GET['ordertype'] ?? '';    // 디자인편집

// Section 매핑 로직 추가 (get_quantities.php와 동일)
$section_mapping = [
    '604' => '610', // 120아트/스노우 → 기본 포스터 데이터
    '605' => '610', // 150아트/스노우 → 기본 포스터 데이터
    '606' => '610', // 180아트/스노우 → 기본 포스터 데이터
    '607' => '610', // 200아트/스노우 → 기본 포스터 데이터
    '608' => '610', // 250아트/스노우 → 기본 포스터 데이터
    '609' => '610', // 300아트/스노우 → 기본 포스터 데이터
    '679' => '610', // 80모조 → 기본 포스터 데이터
    '680' => '610', // 100모조 → 기본 포스터 데이터
    '958' => '610'  // 200g아트/스노우지 → 기본 포스터 데이터
];

// 매핑된 section 값 사용
$mapped_section = $section_mapping[$Section] ?? $Section;

// 입력값 검증
if (empty($MY_type) || empty($Section) || empty($MY_amount) || empty($POtype) || empty($ordertype)) {
    error_response('필수 입력값이 누락되었습니다.');
}

// LittlePrint 테이블 구조에 맞게 조건 설정
$conditions = [
    'style' => $MY_type,
    'TreeSelect' => $Section,    // 재질은 TreeSelect 필드
    'Section' => $PN_type,       // 규격은 Section 필드  
    'quantity' => $MY_amount,
    'POtype' => $POtype
];

// 테이블 우선순위 확인
$possible_tables = [
    "mlangprintauto_littleprint",
    "MlangPrintAuto_littleprint", 
    "mlangprintauto_namecard",
    "MlangPrintAuto_namecard"
];

$price_table = null;
foreach ($possible_tables as $test_table) {
    $table_check = mysqli_query($db, "SHOW TABLES LIKE '$test_table'");
    if (mysqli_num_rows($table_check) > 0) {
        $price_table = $test_table;
        break;
    }
}

if (!$price_table) {
    error_response("가격 계산용 테이블을 찾을 수 없습니다.");
}

$price_result = calculateProductPrice($db, $price_table, $conditions, $ordertype);

if ($price_result) {
    // 성공 응답 (JavaScript 호환 형식)
    $response_data = [
        // JavaScript가 기대하는 필드명들
        'base_price' => $price_result['base_price'],
        'design_price' => $price_result['design_price'],
        'total_price' => $price_result['total_price'],
        'vat' => $price_result['vat'],
        'total_with_vat' => $price_result['total_with_vat'],
        
        // 기존 호환성을 위한 필드들
        'Price' => format_number($price_result['base_price']),
        'DS_Price' => format_number($price_result['design_price']),
        'Order_Price' => format_number($price_result['total_price']),
        'PriceForm' => $price_result['base_price'],
        'DS_PriceForm' => $price_result['design_price'],
        'Order_PriceForm' => $price_result['total_price'],
        'VAT_PriceForm' => $price_result['vat'],
        'Total_PriceForm' => $price_result['total_with_vat'],
        'StyleForm' => $MY_type,
        'SectionForm' => $Section,
        'QuantityForm' => $MY_amount,
        'DesignForm' => $ordertype
    ];
    
    success_response($response_data);
} else {
    error_response('견적을 수행할 관련 정보가 없습니다.\n\n다른 항목으로 견적을 해주시기 바랍니다.');
}

mysqli_close($db);
?>