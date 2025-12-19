<?php
/**
 * 상품권/쿠폰(MerchandiseBond) 함수 라이브러리
 * 재사용 가능한 함수들을 중앙화
 * Created: 2025-09-03
 */

// 보안: 직접 접근 방지
if (!defined('MERCHANDISEBOND_PAGE')) {
    die('Direct access not permitted');
}

/**
 * 상품권/쿠폰 카테고리 옵션 로드
 * @param mysqli $connect 데이터베이스 연결
 * @param string $table 테이블명
 * @param string $page 페이지명
 * @return array 카테고리 옵션 배열
 */
function getMerchandiseBondCategoryOptions($connect, $table, $page) {
    $categories = [];
    
    if (!$connect) {
        error_log("Database connection is null in getMerchandiseBondCategoryOptions");
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
        error_log("Failed to prepare statement in getMerchandiseBondCategoryOptions: " . mysqli_error($connect));
    }
    
    return $categories;
}

/**
 * 상품권/쿠폰 재질 옵션 로드
 * @param mysqli $connect 데이터베이스 연결
 * @param string $table 테이블명
 * @param string $categoryNo 카테고리 번호
 * @return array 재질 옵션 배열
 */
function getMerchandiseBondSectionOptions($connect, $table, $categoryNo) {
    $sections = [];
    
    if (!$connect || empty($categoryNo)) {
        return $sections;
    }
    
    $query = "SELECT * FROM $table WHERE Ttable=? AND BigNo=? ORDER BY no ASC";
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        $page = MERCHANDISEBOND_PAGE;
        mysqli_stmt_bind_param($stmt, "ss", $page, $categoryNo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $sections[] = $row;
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Failed to prepare statement in getMerchandiseBondSectionOptions: " . mysqli_error($connect));
    }
    
    return $sections;
}

/**
 * 상품권/쿠폰 수량 옵션 로드
 * @param mysqli $connect 데이터베이스 연결
 * @param array $params 파라미터 (style, Section, POtype)
 * @return array 수량 옵션 배열
 */
function getMerchandiseBondQuantityOptions($connect, $params) {
    $quantities = [];
    
    if (!$connect || empty($params['style']) || empty($params['Section']) || empty($params['POtype'])) {
        return $quantities;
    }
    
    $query = "SELECT DISTINCT quantity, CONCAT(FORMAT(CAST(quantity AS UNSIGNED), 0), '매') as display_text 
              FROM " . MERCHANDISEBOND_PRICE_TABLE . " 
              WHERE style=? AND Section=? AND POtype=? 
              ORDER BY CAST(quantity AS UNSIGNED) ASC";
    
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $params['style'], $params['Section'], $params['POtype']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $quantities[] = [
                'value' => $row['quantity'],
                'text' => $row['display_text']
            ];
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Failed to prepare statement in getMerchandiseBondQuantityOptions: " . mysqli_error($connect));
    }
    
    return $quantities;
}

/**
 * 상품권/쿠폰 가격 계산
 * @param mysqli $connect 데이터베이스 연결
 * @param array $params 계산 파라미터
 * @param string $orderType 주문 타입
 * @return array|false 가격 정보 또는 false
 */
function calculateMerchandiseBondPrice($connect, $params, $orderType = 'print') {
    global $merchandisebond_price_config;
    
    if (!$connect) {
        return false;
    }
    
    // 필수 파라미터 검증
    $required = ['style', 'Section', 'POtype', 'quantity'];
    foreach ($required as $field) {
        if (empty($params[$field])) {
            error_log("Missing required parameter: $field");
            return false;
        }
    }
    
    // 기본 가격 조회
    $query = "SELECT * FROM " . MERCHANDISEBOND_PRICE_TABLE . " 
              WHERE style=? AND Section=? AND POtype=? AND quantity=?";
    
    $stmt = mysqli_prepare($connect, $query);
    
    if (!$stmt) {
        error_log("Failed to prepare price calculation query: " . mysqli_error($connect));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "ssss", 
        $params['style'], 
        $params['Section'], 
        $params['POtype'], 
        $params['quantity']
    );
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $price_data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$price_data) {
        error_log("No price data found for given parameters");
        return false;
    }
    
    $base_price = floatval($price_data['price'] ?? 0);
    $design_fee = 0;
    
    // 편집비 계산
    if ($orderType === 'total') {
        $design_fee = $merchandisebond_price_config['design_fee_basic'];
    }
    
    $subtotal = $base_price + $design_fee;
    $vat = $subtotal * $merchandisebond_price_config['vat_rate'];
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
 * 상품권/쿠폰 기본값 로드
 * @param mysqli $connect 데이터베이스 연결
 * @param array $config 기본 설정
 * @return array 로드된 기본값
 */
function loadMerchandiseBondDefaults($connect, $config) {
    $defaults = $config;
    
    // 기본 카테고리 설정된 경우 하위 옵션들 로드
    if (!empty($defaults['MY_type'])) {
        $sections = getMerchandiseBondSectionOptions($connect, MERCHANDISEBOND_CATEGORY_TABLE, $defaults['MY_type']);
        if (!empty($sections)) {
            $defaults['Section'] = $sections[0]['no'];
            
            // 해당 조합의 기본 수량 로드
            $quantity_params = [
                'style' => $defaults['MY_type'],
                'Section' => $defaults['Section'],
                'POtype' => $defaults['POtype']
            ];
            $quantities = getMerchandiseBondQuantityOptions($connect, $quantity_params);
            if (!empty($quantities)) {
                $defaults['MY_amount'] = $quantities[0]['value'];
            }
        }
    }
    
    return $defaults;
}

/**
 * 상품권/쿠폰 전용 HTML 이스케이프
 * @param string $text 이스케이프할 텍스트
 * @return string 이스케이프된 텍스트
 */
function safe_merchandisebond_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * 상품권/쿠폰 세션 체크
 */
function check_merchandisebond_session() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty(session_id())) {
        session_regenerate_id(true);
    }
}

/**
 * 상품권/쿠폰 데이터베이스 연결 체크
 * @param mysqli $connect 데이터베이스 연결
 */
function check_merchandisebond_db_connection($connect) {
    if (!$connect) {
        error_log("MerchandiseBond: Database connection failed");
        die('데이터베이스 연결에 실패했습니다.');
    }
}

/**
 * 상품권/쿠폰 로그 정보 생성
 * @return array 로그 정보
 */
function generateMerchandiseBondLogInfo() {
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
 * 상품권/쿠폰 페이지 타이틀 생성
 * @return string 페이지 타이틀
 */
function generate_merchandisebond_title() {
    return MERCHANDISEBOND_PAGE_TITLE . ' - 두손기획인쇄';
}

/**
 * 상품권/쿠폰 편집 타입 옵션 로드
 * @return array 편집 타입 옵션
 */
function getMerchandiseBondEditOptions() {
    return [
        'print' => '인쇄만',
        'total' => '디자인+인쇄'
    ];
}

/**
 * 상품권/쿠폰 카테고리명 조회
 * @param mysqli $connect 데이터베이스 연결
 * @param string $categoryNo 카테고리 번호
 * @return string 카테고리명
 */
function getMerchandiseBondCategoryName($connect, $categoryNo) {
    if (!$connect || empty($categoryNo)) {
        return '';
    }
    
    $query = "SELECT title FROM " . MERCHANDISEBOND_CATEGORY_TABLE . " WHERE no=? AND Ttable=?";
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        $page = MERCHANDISEBOND_PAGE;
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
 * 상품권/쿠폰 입력값 검증
 * @param array $data 검증할 데이터
 * @return array 검증 결과
 */
function validateMerchandiseBondInput($data) {
    $errors = [];
    $required_fields = ['MY_type', 'Section', 'POtype', 'MY_amount', 'ordertype'];
    
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
    
    // 인쇄면 검증
    if (!empty($data['POtype'])) {
        if (!in_array($data['POtype'], ['1', '2'])) {
            $errors[] = '올바른 인쇄면을 선택해주세요.';
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * 상품권/쿠폰 장바구니 데이터 포맷
 * @param array $form_data 폼 데이터
 * @param array $price_data 가격 데이터
 * @return array 장바구니 데이터
 */
function formatMerchandiseBondCartData($form_data, $price_data) {
    $product_details = [
        'category' => getMerchandiseBondCategoryName($GLOBALS['connect'], $form_data['MY_type']),
        'section' => $form_data['Section'],
        'print_side' => $form_data['POtype'] == '1' ? '단면' : '양면',
        'quantity' => $form_data['MY_amount'],
        'edit_type' => $form_data['ordertype']
    ];
    
    return [
        'session_id' => session_id(),
        'product_type' => 'merchandisebond',
        'MY_type' => $form_data['MY_type'],
        'Section' => $form_data['Section'],
        'POtype' => $form_data['POtype'],
        'MY_amount' => $form_data['MY_amount'],
        'ordertype' => $form_data['ordertype'],
        'price' => $price_data['subtotal'],
        'vat_price' => $price_data['total'],
        'Type_1' => json_encode($product_details),
        'created_at' => date('Y-m-d H:i:s')
    ];
}

/**
 * 상품권/쿠폰 에러 처리
 * @param string $error_key 에러 키
 * @param string $custom_message 커스텀 메시지
 * @return array 에러 응답
 */
function handleMerchandiseBondError($error_key, $custom_message = '') {
    global $merchandisebond_error_messages;
    
    $message = $custom_message ?: ($merchandisebond_error_messages[$error_key] ?? '알 수 없는 오류가 발생했습니다.');
    
    error_log("MerchandiseBond Error [$error_key]: $message");
    
    return [
        'success' => false,
        'error' => [
            'code' => $error_key,
            'message' => $message
        ]
    ];
}

/**
 * 상품권/쿠폰 성공 응답
 * @param mixed $data 응답 데이터
 * @param string $message 성공 메시지
 * @return array 성공 응답
 */
function handleMerchandiseBondSuccess($data, $message = '') {
    global $merchandisebond_success_messages;
    
    return [
        'success' => true,
        'data' => $data,
        'message' => $message ?: $merchandisebond_success_messages['price_calculated']
    ];
}

/**
 * 상품권/쿠폰 재질명 조회
 * @param mysqli $connect 데이터베이스 연결
 * @param string $sectionNo 재질 번호
 * @return string 재질명
 */
function getMerchandiseBondSectionName($connect, $sectionNo) {
    if (!$connect || empty($sectionNo)) {
        return '';
    }
    
    $query = "SELECT title FROM " . MERCHANDISEBOND_CATEGORY_TABLE . " WHERE no=? AND Ttable=?";
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        $page = MERCHANDISEBOND_PAGE;
        mysqli_stmt_bind_param($stmt, "ss", $sectionNo, $page);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $row['title'] ?? '';
    }
    
    return '';
}

/**
 * 상품권/쿠폰 파일 업로드 검증
 * @param array $file $_FILES 배열의 파일 정보
 * @return array 검증 결과
 */
function validateMerchandiseBondUpload($file) {
    global $merchandisebond_upload_config;
    
    $errors = [];
    
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $errors[] = '파일이 업로드되지 않았습니다.';
        return ['valid' => false, 'errors' => $errors];
    }
    
    // 파일 크기 검증
    if ($file['size'] > $merchandisebond_upload_config['max_file_size']) {
        $errors[] = '파일 크기가 너무 큽니다. (최대 ' . ($merchandisebond_upload_config['max_file_size'] / 1024 / 1024) . 'MB)';
    }
    
    // 파일 타입 검증
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $merchandisebond_upload_config['allowed_types'])) {
        $errors[] = '지원하지 않는 파일 형식입니다. (' . implode(', ', $merchandisebond_upload_config['allowed_types']) . ')';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * 상품권/쿠폰 가격 포맷팅
 * @param float $price 가격
 * @param bool $include_won 원화 표시 포함 여부
 * @return string 포맷된 가격
 */
function formatMerchandiseBondPrice($price, $include_won = true) {
    $formatted = number_format($price);
    return $include_won ? $formatted . '원' : $formatted;
}

/**
 * 상품권/쿠폰 통계 정보 조회
 * @param mysqli $connect 데이터베이스 연결
 * @param string $period 기간 (today, week, month, year)
 * @return array 통계 정보
 */
function getMerchandiseBondStats($connect, $period = 'month') {
    $stats = [
        'total_orders' => 0,
        'total_amount' => 0,
        'avg_order_value' => 0,
        'popular_categories' => []
    ];
    
    if (!$connect) {
        return $stats;
    }
    
    $where_clause = '';
    switch ($period) {
        case 'today':
            $where_clause = "WHERE DATE(created_at) = CURDATE()";
            break;
        case 'week':
            $where_clause = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $where_clause = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            break;
        case 'year':
            $where_clause = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            break;
    }
    
    // 전체 주문 통계
    $query = "SELECT COUNT(*) as total_orders, 
                     SUM(vat_price) as total_amount,
                     AVG(vat_price) as avg_order_value
              FROM shop_temp 
              WHERE product_type = 'merchandisebond' $where_clause";
    
    $result = mysqli_query($connect, $query);
    if ($result && ($row = mysqli_fetch_assoc($result))) {
        $stats['total_orders'] = intval($row['total_orders']);
        $stats['total_amount'] = floatval($row['total_amount']);
        $stats['avg_order_value'] = floatval($row['avg_order_value']);
    }
    
    return $stats;
}
?>