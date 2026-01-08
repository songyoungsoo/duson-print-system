<?php
/**
 * 양식지(NcrFlambeau) 함수 라이브러리
 * 재사용 가능한 함수들을 중앙화
 * Created: 2025-09-03
 */

// 보안: 직접 접근 방지
if (!defined('NCRFLAMBEAU_PAGE')) {
    die('Direct access not permitted');
}

/**
 * 양식지 카테고리 옵션 로드
 * @param mysqli $connect 데이터베이스 연결
 * @param string $table 테이블명
 * @param string $page 페이지명
 * @return array 카테고리 옵션 배열
 */
function getNcrflambeauCategoryOptions($connect, $table, $page) {
    $categories = [];
    
    if (!$connect) {
        error_log("Database connection is null in getNcrflambeauCategoryOptions");
        return $categories;
    }
    
    $query = "SELECT * FROM $table WHERE Ttable=? AND BigNo='0' ORDER BY no ASC";
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $page);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Failed to prepare statement in getNcrflambeauCategoryOptions: " . mysqli_error($connect));
    }
    
    return $categories;
}

/**
 * 양식지 규격 옵션 로드
 * @param mysqli $connect 데이터베이스 연결
 * @param string $table 테이블명
 * @param string $categoryNo 카테고리 번호
 * @return array 규격 옵션 배열
 */
function getNcrflambeauSizeOptions($connect, $table, $categoryNo) {
    $sizes = [];
    
    if (!$connect || empty($categoryNo)) {
        return $sizes;
    }
    
    $query = "SELECT * FROM $table WHERE Ttable=? AND BigNo=? ORDER BY no ASC";
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        $page = NCRFLAMBEAU_PAGE;
        mysqli_stmt_bind_param($stmt, "ss", $page, $categoryNo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $sizes[] = $row;
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Failed to prepare statement in getNcrflambeauSizeOptions: " . mysqli_error($connect));
    }
    
    return $sizes;
}

/**
 * 양식지 색상 옵션 로드
 * @param mysqli $connect 데이터베이스 연결
 * @param string $table 테이블명
 * @param string $sizeNo 규격 번호
 * @return array 색상 옵션 배열
 */
function getNcrflambeauColorOptions($connect, $table, $sizeNo) {
    $colors = [];
    
    if (!$connect || empty($sizeNo)) {
        return $colors;
    }
    
    $query = "SELECT * FROM $table WHERE Ttable=? AND BigNo=? ORDER BY no ASC";
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        $page = NCRFLAMBEAU_PAGE;
        mysqli_stmt_bind_param($stmt, "ss", $page, $sizeNo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $colors[] = $row;
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Failed to prepare statement in getNcrflambeauColorOptions: " . mysqli_error($connect));
    }
    
    return $colors;
}

/**
 * 양식지 수량 옵션 로드
 * @param mysqli $connect 데이터베이스 연결
 * @param array $params 파라미터 (MY_type, MY_Fsd, PN_type)
 * @return array 수량 옵션 배열
 */
function getNcrflambeauQuantityOptions($connect, $params) {
    $quantities = [];
    
    if (!$connect || empty($params['MY_type']) || empty($params['MY_Fsd']) || empty($params['PN_type'])) {
        return $quantities;
    }
    
    $query = "SELECT DISTINCT MY_amount, CONCAT(FORMAT(MY_amount, 0), '매') as display_text 
              FROM " . NCRFLAMBEAU_PRICE_TABLE . " 
              WHERE MY_type=? AND MY_Fsd=? AND PN_type=? 
              ORDER BY CAST(MY_amount AS UNSIGNED) ASC";
    
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $params['MY_type'], $params['MY_Fsd'], $params['PN_type']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $quantities[] = [
                'value' => $row['MY_amount'],
                'text' => $row['display_text']
            ];
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Failed to prepare statement in getNcrflambeauQuantityOptions: " . mysqli_error($connect));
    }
    
    return $quantities;
}

/**
 * 양식지 가격 계산
 * @param mysqli $connect 데이터베이스 연결
 * @param array $params 계산 파라미터
 * @param string $orderType 주문 타입
 * @return array|false 가격 정보 또는 false
 */
function calculateNcrflambeauPrice($connect, $params, $orderType = 'print') {
    global $ncrflambeau_price_config;
    
    if (!$connect) {
        return false;
    }
    
    // 필수 파라미터 검증
    $required = ['MY_type', 'MY_Fsd', 'PN_type', 'MY_amount'];
    foreach ($required as $field) {
        if (empty($params[$field])) {
            error_log("Missing required parameter: $field");
            return false;
        }
    }
    
    // 기본 가격 조회
    $query = "SELECT * FROM " . NCRFLAMBEAU_PRICE_TABLE . " 
              WHERE MY_type=? AND MY_Fsd=? AND PN_type=? AND MY_amount=?";
    
    $stmt = mysqli_prepare($connect, $query);
    
    if (!$stmt) {
        error_log("Failed to prepare price calculation query: " . mysqli_error($connect));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "ssss", 
        $params['MY_type'], 
        $params['MY_Fsd'], 
        $params['PN_type'], 
        $params['MY_amount']
    );
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $price_data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$price_data) {
        error_log("No price data found for given parameters");
        return false;
    }
    
    $base_price = floatval($price_data['OrderPrice'] ?? 0);
    $design_fee = 0;
    
    // 편집비 계산
    if ($orderType === 'total') {
        $design_fee = $ncrflambeau_price_config['design_fee_basic'];
    } elseif ($orderType === 'design') {
        $design_fee = $ncrflambeau_price_config['design_fee_premium'];
    }
    
    $subtotal = $base_price + $design_fee;
    $vat = $subtotal * $ncrflambeau_price_config['vat_rate'];
    $total = $subtotal + $vat;
    
    return [
        'base_price' => $base_price,
        'design_fee' => $design_fee,
        'subtotal' => $subtotal,
        'vat' => $vat,
        'total' => $total,
        'Order_Price' => number_format($subtotal),
        'Total_PriceForm' => $total,
        'raw_data' => $price_data
    ];
}

/**
 * 양식지 기본값 로드
 * @param mysqli $connect 데이터베이스 연결
 * @param array $config 기본 설정
 * @return array 로드된 기본값
 */
function loadNcrflambeauDefaults($connect, $config) {
    $defaults = $config;
    
    // 기본 카테고리 설정된 경우 하위 옵션들 로드
    if (!empty($defaults['MY_type'])) {
        $sizes = getNcrflambeauSizeOptions($connect, NCRFLAMBEAU_CATEGORY_TABLE, $defaults['MY_type']);
        if (!empty($sizes)) {
            $defaults['MY_Fsd'] = $sizes[0]['no'];
            
            $colors = getNcrflambeauColorOptions($connect, NCRFLAMBEAU_CATEGORY_TABLE, $defaults['MY_Fsd']);
            if (!empty($colors)) {
                $defaults['PN_type'] = $colors[0]['no'];
            }
        }
    }
    
    return $defaults;
}

/**
 * 양식지 전용 HTML 이스케이프
 * @param string $text 이스케이프할 텍스트
 * @return string 이스케이프된 텍스트
 */
function safe_ncrflambeau_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * 양식지 세션 체크
 */
function check_ncrflambeau_session() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty(session_id())) {
        session_regenerate_id(true);
    }
}

/**
 * 양식지 데이터베이스 연결 체크
 * @param mysqli $connect 데이터베이스 연결
 */
function check_ncrflambeau_db_connection($connect) {
    if (!$connect) {
        error_log("NcrFlambeau: Database connection failed");
        die('데이터베이스 연결에 실패했습니다.');
    }
}

/**
 * 양식지 로그 정보 생성
 * @return array 로그 정보
 */
function generateNcrflambeauLogInfo() {
    $url = $_SERVER['REQUEST_URI'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $timestamp = date('Y-m-d H:i:s');
    $y = date('Y');
    $md = date('md');
    $time = date('His');
    
    return [
        'url' => $url,
        'ip' => $ip,
        'user_agent' => $user_agent,
        'timestamp' => $timestamp,
        'y' => $y,
        'md' => $md,
        'time' => $time
    ];
}

/**
 * 양식지 페이지 타이틀 생성
 * @return string 페이지 타이틀
 */
function generate_ncrflambeau_title() {
    return NCRFLAMBEAU_PAGE_TITLE . ' - 두손기획인쇄';
}

/**
 * 양식지 편집 타입 옵션 로드
 * @return array 편집 타입 옵션
 */
function getNcrflambeauEditOptions() {
    return [
        'print' => '인쇄만',
        'total' => '디자인+인쇄',
        'design' => '고급디자인+인쇄'
    ];
}

/**
 * 양식지 카테고리명 조회
 * @param mysqli $connect 데이터베이스 연결
 * @param string $categoryNo 카테고리 번호
 * @return string 카테고리명
 */
function getNcrflambeauCategoryName($connect, $categoryNo) {
    if (!$connect || empty($categoryNo)) {
        return '';
    }
    
    $query = "SELECT title FROM " . NCRFLAMBEAU_CATEGORY_TABLE . " WHERE no=? AND Ttable=?";
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        $page = NCRFLAMBEAU_PAGE;
        mysqli_stmt_bind_param($stmt, "ss", $categoryNo, $page);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $row['title'] ?? '';
    }
    
    return '';
}

/**
 * 양식지 입력값 검증
 * @param array $data 검증할 데이터
 * @return array 검증 결과
 */
function validateNcrflambeauInput($data) {
    $errors = [];
    $required_fields = ['MY_type', 'MY_Fsd', 'PN_type', 'MY_amount', 'ordertype'];
    
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $errors[] = "{$field} 값이 필요합니다.";
        }
    }
    
    // 수량 검증
    if (!empty($data['MY_amount'])) {
        $amount = intval($data['MY_amount']);
        if ($amount < 100) {
            $errors[] = '최소 주문 수량은 100매입니다.';
        }
        if ($amount > 50000) {
            $errors[] = '최대 주문 수량은 50,000매입니다.';
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * 양식지 장바구니 데이터 포맷
 * @param array $form_data 폼 데이터
 * @param array $price_data 가격 데이터
 * @return array 장바구니 데이터
 */
function formatNcrflambeauCartData($form_data, $price_data) {
    $product_details = [
        'category' => getNcrflambeauCategoryName($GLOBALS['connect'], $form_data['MY_type']),
        'size' => $form_data['MY_Fsd'],
        'color' => $form_data['PN_type'],
        'quantity' => $form_data['MY_amount'],
        'edit_type' => $form_data['ordertype']
    ];
    
    return [
        'session_id' => session_id(),
        'product_type' => 'ncrflambeau',
        'MY_type' => $form_data['MY_type'],
        'MY_Fsd' => $form_data['MY_Fsd'],
        'PN_type' => $form_data['PN_type'],
        'MY_amount' => $form_data['MY_amount'],
        'ordertype' => $form_data['ordertype'],
        'price' => $price_data['subtotal'],
        'vat_price' => $price_data['total'],
        'Type_1' => json_encode($product_details),
        'created_at' => date('Y-m-d H:i:s')
    ];
}

/**
 * 양식지 에러 처리
 * @param string $error_key 에러 키
 * @param string $custom_message 커스텀 메시지
 * @return array 에러 응답
 */
function handleNcrflambeauError($error_key, $custom_message = '') {
    global $ncrflambeau_error_messages;
    
    $message = $custom_message ?: ($ncrflambeau_error_messages[$error_key] ?? '알 수 없는 오류가 발생했습니다.');
    
    error_log("NcrFlambeau Error [$error_key]: $message");
    
    return [
        'success' => false,
        'error' => [
            'code' => $error_key,
            'message' => $message
        ]
    ];
}

/**
 * 양식지 성공 응답
 * @param mixed $data 응답 데이터
 * @param string $message 성공 메시지
 * @return array 성공 응답
 */
function handleNcrflambeauSuccess($data, $message = '') {
    global $ncrflambeau_success_messages;
    
    return [
        'success' => true,
        'data' => $data,
        'message' => $message ?: $ncrflambeau_success_messages['price_calculated']
    ];
}
?>