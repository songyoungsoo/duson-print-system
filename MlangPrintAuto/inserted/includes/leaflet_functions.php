<?php
/**
 * 전단지 공통 함수 라이브러리
 * 다른 제품에서도 활용 가능한 공통 함수들
 * Created: 2025-09-02
 */

/**
 * 드롭다운 옵션을 가져오는 범용 함수
 * 
 * @param mysqli $connect 데이터베이스 연결
 * @param string $table 테이블명
 * @param array $conditions WHERE 조건 배열
 * @param string $order_by 정렬 조건
 * @return array 옵션 배열
 */
function getDropdownOptions($connect, $table, $conditions = [], $order_by = 'no ASC') {
    $options = [];
    
    // WHERE 절 생성
    $where_clauses = [];
    foreach ($conditions as $key => $value) {
        $where_clauses[] = "$key = '" . mysqli_real_escape_string($connect, $value) . "'";
    }
    $where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";
    
    // 쿼리 실행
    $query = "SELECT * FROM $table $where_sql ORDER BY $order_by";
    $result = mysqli_query($connect, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'no' => $row['no'],
                'title' => $row['title'],
                'data' => $row // 전체 데이터도 포함
            ];
        }
    }
    
    return $options;
}

/**
 * 전단지 색상 옵션 가져오기
 */
function getLeafletColorOptions($connect, $table, $page) {
    return getDropdownOptions($connect, $table, [
        'Ttable' => $page,
        'BigNo' => '0'
    ]);
}

/**
 * 전단지 종이 종류 가져오기
 */
function getLeafletPaperTypes($connect, $table, $color_no) {
    return getDropdownOptions($connect, $table, [
        'TreeNo' => $color_no
    ]);
}

/**
 * 전단지 종이 크기 가져오기
 */
function getLeafletPaperSizes($connect, $table, $color_no) {
    return getDropdownOptions($connect, $table, [
        'BigNo' => $color_no
    ]);
}

/**
 * 안전한 HTML 출력
 * 
 * @param string $text 출력할 텍스트
 * @return string 이스케이프된 HTML
 */
function safe_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * 가격 포맷팅
 * 
 * @param int|float $price 가격
 * @param bool $include_won 원 단위 포함 여부
 * @return string 포맷된 가격
 */
function format_price($price, $include_won = true) {
    $formatted = number_format($price);
    return $include_won ? $formatted . '원' : $formatted;
}

/**
 * 세션 체크
 */
function check_session() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * 데이터베이스 연결 체크
 * 
 * @param mysqli $db 데이터베이스 연결
 */
function check_db_connection($db) {
    if (!$db) {
        die("데이터베이스 연결에 실패했습니다: " . mysqli_connect_error());
    }
}

/**
 * 로그 정보 생성
 * 
 * @return array 로그 정보
 */
function generateLogInfo() {
    return [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'timestamp' => date('Y-m-d H:i:s'),
        'session_id' => session_id(),
    ];
}

/**
 * AJAX 응답 전송
 * 
 * @param bool $success 성공 여부
 * @param mixed $data 응답 데이터
 * @param string $message 메시지
 */
function send_ajax_response($success, $data = null, $message = '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

/**
 * 입력값 검증
 * 
 * @param mixed $value 검증할 값
 * @param string $type 타입 (string, int, email 등)
 * @return mixed 검증된 값 또는 false
 */
function validate_input($value, $type = 'string') {
    switch ($type) {
        case 'int':
            return filter_var($value, FILTER_VALIDATE_INT);
        case 'email':
            return filter_var($value, FILTER_VALIDATE_EMAIL);
        case 'url':
            return filter_var($value, FILTER_VALIDATE_URL);
        case 'string':
        default:
            return filter_var($value, FILTER_SANITIZE_STRING);
    }
}

/**
 * 파일 업로드 경로 생성
 * 
 * @param string $base_path 기본 경로
 * @return string 생성된 업로드 경로
 */
function generate_upload_path($base_path = '../../uploads/') {
    $date = date('Y/m/d');
    $ip = str_replace('.', '_', $_SERVER['REMOTE_ADDR']);
    $timestamp = time();
    
    $path = $base_path . $date . '/' . $ip . '/' . $timestamp . '/';
    
    // 디렉토리 생성
    if (!file_exists($path)) {
        mkdir($path, 0755, true);
    }
    
    return $path;
}

/**
 * 제품 카드 HTML 생성 (재사용 가능한 컴포넌트)
 * 
 * @param array $product_info 제품 정보
 * @return string HTML
 */
function generate_product_card($product_info) {
    $color_class = $product_info['color'] ?? 'primary';
    $html = '<div class="product-card">';
    $html .= '<div class="product-card-header ' . $color_class . '">';
    $html .= '<h3>' . safe_html($product_info['title']) . '</h3>';
    $html .= '</div>';
    $html .= '<div class="product-card-divider ' . $color_class . '"></div>';
    $html .= '<div class="product-card-body">';
    $html .= '<p>' . safe_html($product_info['description']) . '</p>';
    
    // 추가 정보가 있으면 표시
    if (!empty($product_info['features'])) {
        $html .= '<div class="product-info-box">';
        $html .= '<h4>⚙️ 특징</h4>';
        $html .= '<ul>';
        foreach ($product_info['features'] as $feature) {
            $html .= '<li>' . safe_html($feature) . '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
    }
    
    if (!empty($product_info['tip'])) {
        $html .= '<div class="product-tip-box ' . $color_class . '">';
        $html .= '<p>' . safe_html($product_info['tip']) . '</p>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}
?>