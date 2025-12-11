<?php
/**
 * 공통 PHP 함수들 - 노토 폰트 환경에 최적화
 * 모든 품목에서 공통으로 사용할 수 있는 함수들
 */

// 사용자 IP 주소 가져오기
if (!function_exists('getUserIP')) {
    function getUserIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}

// 로그 정보 생성
function generateLogInfo() {
    $log_info = array(
        'url' => str_replace("\/", "_", $_SERVER['PHP_SELF']),
        'y' => date("Y"),
        'md' => date("md"),
        'ip' => getUserIP(),
        'time' => time()  // ✅ Unix timestamp 사용 (구버전 호환)
    );
    
    // IPv6 localhost를 IPv4로 변환
    if ($log_info['ip'] === "::1") {
        $log_info['ip'] = "127.0.0.1";
    }
    
    return $log_info;
}

// 안전한 HTML 출력
function safe_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// 숫자 포맷팅 (천단위 콤마)
function format_number($number) {
    return number_format($number);
}

// 가격 포맷팅
function format_price($price) {
    return number_format($price) . '원';
}

// 페이지 제목 생성
function generate_page_title($product_name = '', $default_title = '두손기획인쇄 - 견적안내') {
    if (!empty($product_name)) {
        return "두손기획인쇄 - {$product_name} 견적안내";
    }
    return $default_title;
}

// CSS 경로 생성
function get_css_path($relative_path = '') {
    if (!empty($relative_path)) {
        return $relative_path . '/css/styles.css';
    }
    return '/css/styles.css';
}

// JavaScript 경로 생성
function get_js_path($filename) {
    return '/js/' . $filename;
}

// 세션 체크
function check_session() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return session_id();
}

// 데이터베이스 연결 체크
function check_db_connection($db) {
    if (!$db) {
        die("데이터베이스 연결에 실패했습니다: " . mysqli_connect_error());
    }
    return true;
}

// 파일 업로드 디렉토리 생성
function create_upload_directory($log_info) {
    $upload_path = "uploads/{$log_info['url']}/{$log_info['y']}/{$log_info['md']}/{$log_info['ip']}/{$log_info['time']}";
    
    if (!file_exists($upload_path)) {
        mkdir($upload_path, 0755, true);
    }
    
    return $upload_path;
}

// 옵션 배열을 HTML select 옵션으로 변환
function array_to_options($array, $value_key = 'no', $text_key = 'title', $selected_value = '') {
    $options = '';
    foreach ($array as $item) {
        $value = safe_html($item[$value_key]);
        $text = safe_html($item[$text_key]);
        $selected = ($value == $selected_value) ? ' selected' : '';
        $options .= "<option value=\"{$value}\"{$selected}>{$text}</option>\n";
    }
    return $options;
}

// JSON 응답 생성
function json_response($success = true, $data = null, $message = '') {
    $response = array(
        'success' => $success,
        'message' => $message
    );
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// 에러 응답 생성
function error_response($message, $code = 400) {
    http_response_code($code);
    json_response(false, null, $message);
}

// 성공 응답 생성
function success_response($data = null, $message = '성공') {
    json_response(true, $data, $message);
}

// 페이지 파라미터 가져오기
function get_page_param($default = 'inserted') {
    return $_GET['page'] ?? $default;
}

// 현재 페이지가 활성 메뉴인지 확인
function is_active_menu($menu_path, $current_path = null) {
    if ($current_path === null) {
        $current_path = $_SERVER['REQUEST_URI'];
    }
    return strpos($current_path, $menu_path) !== false;
}

// 디버그 로그 (개발 환경에서만)
function debug_log($message, $data = null) {
    if (defined('DEBUG') && DEBUG === true) {
        $log_message = date('Y-m-d H:i:s') . ' - ' . $message;
        if ($data !== null) {
            $log_message .= ' - ' . print_r($data, true);
        }
        error_log($log_message);
    }
}

// 파일 확장자 검증
function validate_file_extension($filename, $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'ai', 'psd')) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $allowed_extensions);
}

// 파일 크기 포맷팅
function format_file_size($bytes) {
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

// 브라우저 정보 가져오기
function get_browser_info() {
    return array(
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
        'referer' => $_SERVER['HTTP_REFERER'] ?? ''
    );
}

// 모바일 기기 감지
function is_mobile() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return (
        strpos($user_agent, 'Mobile') !== false ||
        strpos($user_agent, 'Android') !== false ||
        strpos($user_agent, 'iPhone') !== false ||
        strpos($user_agent, 'iPad') !== false
    );
}

// 한국어 요일 반환
function get_korean_day($date = null) {
    $days = array('일', '월', '화', '수', '목', '금', '토');
    $day_index = date('w', $date ? strtotime($date) : time());
    return $days[$day_index];
}

// 한국어 날짜 포맷
function format_korean_date($date = null, $format = 'Y년 m월 d일') {
    return date($format, $date ? strtotime($date) : time());
}

// 드롭다운 옵션 조회 (범용)
function getDropdownOptions($db, $table, $where_conditions = [], $order_by = 'no ASC') {
    $where_clause = '';
    if (!empty($where_conditions)) {
        $conditions = [];
        foreach ($where_conditions as $key => $value) {
            $conditions[] = "$key='" . mysqli_real_escape_string($db, $value) . "'";
        }
        $where_clause = 'WHERE ' . implode(' AND ', $conditions);
    }
    
    $query = "SELECT * FROM $table $where_clause ORDER BY $order_by";
    $result = mysqli_query($db, $query);
    $options = [];
    
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'no' => $row['no'],
                'title' => $row['title']
            ];
        }
    }
    
    return $options;
}

// 카테고리 옵션 조회
function getCategoryOptions($db, $table, $page) {
    return getDropdownOptions($db, $table, [
        'Ttable' => $page,
        'BigNo' => '0'
    ]);
}

// 종이종류 옵션 조회 (BigNo 기준)
function getPaperTypes($db, $table, $category_no) {
    return getDropdownOptions($db, $table, [
        'Ttable' => 'LittlePrint',
        'BigNo' => $category_no
    ]);
}

// 종이규격 옵션 조회 (TreeNo 기준)
function getPaperSizes($db, $table, $category_no) {
    return getDropdownOptions($db, $table, [
        'Ttable' => 'LittlePrint',
        'TreeNo' => $category_no
    ]);
}

// 수량 옵션 조회
function getQuantityOptions($db, $table = "mlangprintauto_littleprint") {
    $query = "SELECT DISTINCT quantity FROM $table WHERE quantity IS NOT NULL ORDER BY CAST(quantity AS UNSIGNED) ASC";
    $result = mysqli_query($db, $query);
    $options = [];
    
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'quantity' => $row['quantity'],
                'text' => format_number($row['quantity']) . '매'
            ];
        }
    }
    
    return $options;
}

// 가격 계산 공통 로직
function calculateProductPrice($db, $table, $conditions, $ordertype = 'total') {
    $where_conditions = [];
    foreach ($conditions as $key => $value) {
        $where_conditions[] = "$key='" . mysqli_real_escape_string($db, $value) . "'";
    }
    
    $query = "SELECT * FROM $table WHERE " . implode(' AND ', $where_conditions);
    $result = mysqli_query($db, $query);
    
    if ($result && $row = mysqli_fetch_array($result)) {
        $base_price = intval($row['money']);
        $design_price = intval($row['DesignMoney']);
        
        // 주문 타입에 따른 가격 계산
        switch ($ordertype) {
            case 'print':
                $final_base = $base_price;
                $final_design = 0;
                break;
            case 'design':
                $final_base = 0;
                $final_design = $design_price;
                break;
            default: // total
                $final_base = $base_price;
                $final_design = $design_price;
        }
        
        $total = $final_base + $final_design;
        $vat = $total * 0.1;
        $total_with_vat = $total + $vat;
        
        return [
            'base_price' => $final_base,
            'design_price' => $final_design,
            'total_price' => $total,
            'vat' => $vat,
            'total_with_vat' => $total_with_vat,
            'formatted' => [
                'base_price' => format_price($final_base),
                'design_price' => format_price($final_design),
                'total_price' => format_price($total),
                'total_with_vat' => format_price($total_with_vat)
            ]
        ];
    }
    
    return null;
}
?>