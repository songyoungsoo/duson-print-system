<?php
/**
 * 자석스티커(MSticker) 함수 라이브러리
 * 재사용 가능한 함수들을 모듈화
 * Created: 2025-09-03
 */

// 설정 파일이 로드되었는지 확인
if (!defined('MSTICKER_PAGE')) {
    die('Configuration not loaded. Please include msticker.config.php first.');
}

/**
 * 자석스티커 세션 체크
 */
function check_msticker_session() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // 세션 보안 강화
    if (!isset($_SESSION['msticker_csrf_token'])) {
        $_SESSION['msticker_csrf_token'] = bin2hex(random_bytes(32));
    }
}

/**
 * 자석스티커 데이터베이스 연결 확인
 */
function check_msticker_db_connection($connect) {
    if (!$connect) {
        error_log("MSticker DB Connection failed: " . mysqli_connect_error());
        die("데이터베이스 연결에 실패했습니다.");
    }
    return true;
}

/**
 * 자석스티커 로그 정보 생성
 */
function generateMStickerLogInfo() {
    return [
        'url' => $_SERVER['HTTP_HOST'] ?? 'localhost',
        'y' => date('Y'),
        'md' => date('md'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        'time' => time()
    ];
}

/**
 * 자석스티커 페이지 제목 생성
 */
function generate_msticker_title() {
    return MSTICKER_PAGE_TITLE;
}

/**
 * 자석스티커 카테고리 옵션 가져오기 (보안 강화)
 */
function getMStickerCategoryOptions($connect, $table, $page) {
    $query = "SELECT no, title FROM $table WHERE Ttable=? AND BigNo='0' ORDER BY CASE WHEN title LIKE '%종이%' THEN 1 ELSE 2 END, no ASC";
    $stmt = mysqli_prepare($connect, $query);
    
    if (!$stmt) {
        error_log("MStickerCategoryOptions prepare failed: " . mysqli_error($connect));
        return [];
    }
    
    mysqli_stmt_bind_param($stmt, "s", $page);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = [
            'no' => $row['no'],
            'title' => htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8')
        ];
    }
    
    mysqli_stmt_close($stmt);
    return $categories;
}

/**
 * 자석스티커 섹션(규격) 옵션 가져오기
 */
function getMStickerSectionOptions($connect, $table, $type_id) {
    $query = "SELECT no, title FROM $table WHERE Ttable='msticker' AND BigNo=? ORDER BY no ASC";
    $stmt = mysqli_prepare($connect, $query);
    
    if (!$stmt) {
        error_log("MStickerSectionOptions prepare failed: " . mysqli_error($connect));
        return [];
    }
    
    mysqli_stmt_bind_param($stmt, "s", $type_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $sections = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $sections[] = [
            'no' => $row['no'],
            'title' => htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8')
        ];
    }
    
    mysqli_stmt_close($stmt);
    return $sections;
}

/**
 * 자석스티커 수량 옵션 가져오기
 */
function getMStickerQuantityOptions($connect, $price_table, $style, $section) {
    $query = "SELECT DISTINCT quantity FROM $price_table WHERE style=? AND Section=? ORDER BY CASE WHEN quantity='100' THEN 1 ELSE 2 END, CAST(quantity AS UNSIGNED) ASC";
    $stmt = mysqli_prepare($connect, $query);
    
    if (!$stmt) {
        error_log("MStickerQuantityOptions prepare failed: " . mysqli_error($connect));
        return [];
    }
    
    mysqli_stmt_bind_param($stmt, "ss", $style, $section);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $quantities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $quantities[] = [
            'value' => $row['quantity'],
            'text' => number_format($row['quantity']) . '매'
        ];
    }
    
    mysqli_stmt_close($stmt);
    return $quantities;
}

/**
 * 자석스티커 가격 계산
 */
function calculateMStickerPrice($connect, $params, $orderType = 'print') {
    global $msticker_price_config;
    
    // 입력 검증
    $required_params = ['MY_type', 'Section', 'POtype', 'MY_amount'];
    foreach ($required_params as $param) {
        if (empty($params[$param])) {
            return ['success' => false, 'message' => "필수 매개변수 '$param'이 누락되었습니다."];
        }
    }
    
    // 가격 조회 쿼리
    $query = "SELECT price FROM " . MSTICKER_PRICE_TABLE . " 
              WHERE style=? AND Section=? AND quantity=? AND potype=?";
    $stmt = mysqli_prepare($connect, $query);
    
    if (!$stmt) {
        error_log("MStickerPrice prepare failed: " . mysqli_error($connect));
        return ['success' => false, 'message' => '가격 조회 중 오류가 발생했습니다.'];
    }
    
    mysqli_stmt_bind_param($stmt, "ssss", 
        $params['MY_type'], 
        $params['Section'], 
        $params['MY_amount'], 
        $params['POtype']
    );
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $price_data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$price_data) {
        return ['success' => false, 'message' => '해당 조건의 가격 정보를 찾을 수 없습니다.'];
    }
    
    // 가격 계산
    $base_price = floatval($price_data['price'] ?? 0);
    $design_fee = 0;
    
    if ($orderType === 'total') {
        $design_fee = $msticker_price_config['design_fee_basic'];
    }
    
    $subtotal = $base_price + $design_fee;
    $vat = $subtotal * $msticker_price_config['vat_rate'];
    $total = $subtotal + $vat;
    
    return [
        'success' => true,
        'base_price' => $base_price,
        'design_fee' => $design_fee,
        'subtotal' => $subtotal,
        'vat' => $vat,
        'total' => $total,
        'formatted' => [
            'base_price' => number_format($base_price) . '원',
            'design_fee' => number_format($design_fee) . '원',
            'subtotal' => number_format($subtotal) . '원',
            'vat' => number_format($vat) . '원',
            'total' => number_format($total) . '원'
        ]
    ];
}

/**
 * 자석스티커 기본값 로드
 */
function loadMStickerDefaults($connect, $defaults) {
    // 첫 번째 자석스티커 종류 가져오기 (종이자석 우선)
    $categories = getMStickerCategoryOptions($connect, MSTICKER_CATEGORY_TABLE, MSTICKER_PAGE);
    
    if (!empty($categories)) {
        $defaults['MY_type'] = $categories[0]['no'];
        
        // 해당 종류의 첫 번째 규격 가져오기
        $sections = getMStickerSectionOptions($connect, MSTICKER_CATEGORY_TABLE, $categories[0]['no']);
        
        if (!empty($sections)) {
            $defaults['Section'] = $sections[0]['no'];
            
            // 해당 조합의 기본 수량 가져오기 (100매 우선)
            $quantities = getMStickerQuantityOptions($connect, MSTICKER_PRICE_TABLE, $categories[0]['no'], $sections[0]['no']);
            
            if (!empty($quantities)) {
                $defaults['MY_amount'] = $quantities[0]['value'];
            }
        }
    }
    
    return $defaults;
}

/**
 * 안전한 HTML 출력 (자석스티커용)
 */
function safe_msticker_html($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * 자석스티커 CSRF 토큰 생성
 */
function generateMStickerCSRFToken() {
    if (!isset($_SESSION['msticker_csrf_token'])) {
        $_SESSION['msticker_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['msticker_csrf_token'];
}

/**
 * 자석스티커 CSRF 토큰 검증
 */
function validateMStickerCSRFToken($token) {
    return isset($_SESSION['msticker_csrf_token']) && 
           hash_equals($_SESSION['msticker_csrf_token'], $token);
}

/**
 * 자석스티커 파일 유효성 검사
 */
function validateMStickerFile($file) {
    global $msticker_upload_config;
    
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['valid' => false, 'message' => '파일이 선택되지 않았습니다.'];
    }
    
    // 파일 크기 확인
    if ($file['size'] > $msticker_upload_config['max_file_size']) {
        $max_size = $msticker_upload_config['max_file_size'] / (1024 * 1024);
        return ['valid' => false, 'message' => "파일 크기가 {$max_size}MB를 초과합니다."];
    }
    
    // 파일 확장자 확인
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $msticker_upload_config['allowed_types'])) {
        $allowed = implode(', ', $msticker_upload_config['allowed_types']);
        return ['valid' => false, 'message' => "허용되지 않는 파일 형식입니다. 허용 형식: {$allowed}"];
    }
    
    // MIME 타입 확인
    $allowed_mime_types = [
        'pdf' => 'application/pdf',
        'ai' => 'application/postscript',
        'psd' => 'application/octet-stream',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'eps' => 'application/postscript'
    ];
    
    if (function_exists('mime_content_type')) {
        $mime_type = mime_content_type($file['tmp_name']);
        if (isset($allowed_mime_types[$file_ext]) && 
            !in_array($mime_type, array_values($allowed_mime_types))) {
            return ['valid' => false, 'message' => '파일 내용이 확장자와 일치하지 않습니다.'];
        }
    }
    
    return ['valid' => true, 'message' => '유효한 파일입니다.'];
}

/**
 * 자석스티커 장바구니 데이터 준비
 */
function prepareMStickerCartData($params, $calculated_price) {
    global $msticker_defaults;
    
    // 공통 장바구니 데이터
    $cart_data = [
        'session_id' => session_id(),
        'product_type' => MSTICKER_PAGE,
        'MY_type' => $params['MY_type'] ?? '',
        'Section' => $params['Section'] ?? '',
        'POtype' => $params['POtype'] ?? $msticker_defaults['POtype'],
        'MY_amount' => $params['MY_amount'] ?? '',
        'ordertype' => $params['ordertype'] ?? $msticker_defaults['ordertype'],
        'st_price' => $calculated_price['subtotal'] ?? 0,
        'st_price_vat' => $calculated_price['total'] ?? 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // 자석스티커 특화 데이터 (JSON 형태로 저장)
    $type_1_data = [
        'product_name' => '자석스티커',
        'magnet_type' => $params['MY_type'] ?? '',
        'section' => $params['Section'] ?? '',
        'print_sides' => $params['POtype'] ?? '',
        'quantity' => $params['MY_amount'] ?? '',
        'order_type' => $params['ordertype'] ?? '',
        'price_breakdown' => $calculated_price,
        'special_requirements' => $params['special_requirements'] ?? ''
    ];
    
    $cart_data['Type_1'] = json_encode($type_1_data, JSON_UNESCAPED_UNICODE);
    
    return $cart_data;
}

/**
 * 자석스티커 에러 처리
 */
function handleMStickerError($error_code, $custom_message = '') {
    global $msticker_error_messages;
    
    $message = $custom_message ?: ($msticker_error_messages[$error_code] ?? '알 수 없는 오류가 발생했습니다.');
    
    error_log("MSticker Error [$error_code]: $message");
    
    return [
        'success' => false,
        'error_code' => $error_code,
        'message' => $message
    ];
}

/**
 * 자석스티커 성공 응답
 */
function mStickerSuccessResponse($data, $message_code = 'success') {
    global $msticker_success_messages;
    
    $message = $msticker_success_messages[$message_code] ?? '작업이 성공적으로 완료되었습니다.';
    
    return [
        'success' => true,
        'message' => $message,
        'data' => $data
    ];
}

/**
 * 자석스티커 입력값 정화
 */
function sanitizeMStickerInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeMStickerInput', $input);
    }
    
    return trim(htmlspecialchars(strip_tags($input ?? ''), ENT_QUOTES, 'UTF-8'));
}

/**
 * 자석스티커 로깅 함수
 */
function logMStickerActivity($action, $data = [], $user_id = null) {
    global $msticker_log_settings;
    
    if (!$msticker_log_settings['enable_logging']) {
        return;
    }
    
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'user_id' => $user_id ?? ($_SESSION['duson_member_id'] ?? 'guest'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'data' => $data
    ];
    
    $log_file = __DIR__ . '/../logs/msticker_' . date('Y-m') . '.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    error_log(json_encode($log_entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, 3, $log_file);
}

/**
 * 자석스티커 갤러리 이미지 정보 가져오기
 */
function getMStickerGalleryImages($type = 'msticker', $page = 1, $limit = 8) {
    // 갤러리 이미지 목록 반환 (실제 구현에서는 데이터베이스나 파일 시스템에서 가져옴)
    $images = [];
    $image_dir = "../../uploads/gallery/{$type}/";
    
    if (is_dir($image_dir)) {
        $files = array_diff(scandir($image_dir), ['.', '..']);
        $files = array_filter($files, function($file) {
            return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
        });
        
        $start = ($page - 1) * $limit;
        $files = array_slice($files, $start, $limit);
        
        foreach ($files as $file) {
            $images[] = [
                'src' => $image_dir . $file,
                'alt' => '자석스티커 샘플',
                'title' => pathinfo($file, PATHINFO_FILENAME)
            ];
        }
    }
    
    return $images;
}

/**
 * 자석스티커 세션 정리
 */
function cleanupMStickerSession() {
    $keys_to_remove = [
        'msticker_temp_data',
        'msticker_upload_progress',
        'msticker_calculation_cache'
    ];
    
    foreach ($keys_to_remove as $key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
}

// 세션 종료 시 정리 함수 등록
register_shutdown_function('cleanupMStickerSession');
?>