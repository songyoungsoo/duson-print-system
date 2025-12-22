<?php
/**
 * 카다록/리플렛 공통 함수 라이브러리
 * 다른 제품에서도 활용 가능한 공통 함수들
 * Created: 2025-09-03
 */

/**
 * 카다록 카테고리 옵션을 가져오는 함수
 * 
 * @param mysqli $connect 데이터베이스 연결
 * @param string $table 테이블명
 * @param string $page 페이지명 (cadarok)
 * @return array 카테고리 배열
 */
function getCadarokCategoryOptions($connect, $table, $page) {
    $options = [];
    $query = "SELECT * FROM $table WHERE Ttable=? AND BigNo='0' ORDER BY no ASC";
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $page);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = [
                'no' => $row['no'],
                'title' => $row['title'],
                'data' => $row // 전체 데이터도 포함
            ];
        }
        mysqli_stmt_close($stmt);
    }
    
    return $options;
}

/**
 * 카다록 재질 옵션을 가져오는 함수
 * 
 * @param mysqli $connect 데이터베이스 연결
 * @param string $table 테이블명
 * @param string $category_no 카테고리 번호
 * @return array 재질 배열
 */
function getCadarokSectionOptions($connect, $table, $category_no) {
    $options = [];
    $query = "SELECT * FROM $table WHERE BigNo=? ORDER BY no ASC";
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $category_no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = [
                'no' => $row['no'],
                'title' => $row['title'],
                'data' => $row // 전체 데이터도 포함
            ];
        }
        mysqli_stmt_close($stmt);
    }
    
    return $options;
}

/**
 * 카다록 수량 옵션을 가져오는 함수
 * 
 * @param mysqli $connect 데이터베이스 연결
 * @param string $table 테이블명 (mlangprintauto_cadarok)
 * @param string $style 스타일 번호
 * @param string $section 재질 번호
 * @return array 수량 배열
 */
function getCadarokQuantityOptions($connect, $table, $style, $section) {
    $options = [];
    $query = "SELECT DISTINCT quantity FROM $table WHERE style=? AND Section=? ORDER BY CASE WHEN quantity='500' THEN 1 ELSE 2 END, CAST(quantity AS UNSIGNED) ASC";
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $style, $section);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $options[] = [
                'quantity' => $row['quantity'],
                'display' => $row['quantity'] . '매'
            ];
        }
        mysqli_stmt_close($stmt);
    }
    
    return $options;
}

/**
 * 카다록 가격 계산 함수
 * 
 * @param mysqli $connect 데이터베이스 연결
 * @param string $table 가격 테이블명
 * @param array $params 가격 계산 파라미터
 * @return array 가격 정보
 */
function calculateCadarokPrice($connect, $table, $params) {
    $result = [
        'success' => false,
        'price' => 0,
        'vat_price' => 0,
        'message' => ''
    ];
    
    // 필수 파라미터 검증
    $required_params = ['style', 'section', 'quantity', 'po_type', 'order_type'];
    foreach ($required_params as $param) {
        if (!isset($params[$param]) || $params[$param] === '') {
            $result['message'] = "필수 파라미터가 누락되었습니다: $param";
            return $result;
        }
    }
    
    // 데이터베이스에서 가격 조회
    $query = "SELECT * FROM $table WHERE style=? AND Section=? AND quantity=? AND POtype=? LIMIT 1";
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", 
            $params['style'], 
            $params['section'], 
            $params['quantity'], 
            $params['po_type']
        );
        mysqli_stmt_execute($stmt);
        $price_result = mysqli_stmt_get_result($stmt);
        
        if ($price_row = mysqli_fetch_assoc($price_result)) {
            $base_price = intval($price_row['price']);
            
            // 편집 디자인비 추가
            $design_fee = 0;
            if ($params['order_type'] === 'total') {
                $design_fee = 10000; // 기본 편집비
            }
            
            $total_price = $base_price + $design_fee;
            $vat_price = intval($total_price * 1.1); // 부가세 10% 포함
            
            $result = [
                'success' => true,
                'price' => $total_price,
                'vat_price' => $vat_price,
                'base_price' => $base_price,
                'design_fee' => $design_fee,
                'message' => '가격 계산 완료'
            ];
        } else {
            $result['message'] = '해당 조건의 가격을 찾을 수 없습니다.';
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $result['message'] = '데이터베이스 쿼리 실행에 실패했습니다.';
    }
    
    return $result;
}

/**
 * 기본값 설정을 위한 초기 데이터 로딩
 * 
 * @param mysqli $connect 데이터베이스 연결
 * @param array $config 설정 배열
 * @return array 기본값 배열
 */
function loadCadarokDefaults($connect, $config) {
    $defaults = $config;
    
    // 첫 번째 카다록 종류 가져오기
    $categories = getCadarokCategoryOptions($connect, CADAROK_CATEGORY_TABLE, CADAROK_PAGE);
    if (!empty($categories)) {
        $defaults['MY_type'] = $categories[0]['no'];
        
        // 해당 카다록 종류의 첫 번째 재질 가져오기
        $sections = getCadarokSectionOptions($connect, CADAROK_CATEGORY_TABLE, $categories[0]['no']);
        if (!empty($sections)) {
            $defaults['Section'] = $sections[0]['no'];
            
            // 해당 조합의 기본 수량 가져오기 (500매 우선)
            $quantities = getCadarokQuantityOptions($connect, CADAROK_PRICE_TABLE, $categories[0]['no'], $sections[0]['no']);
            if (!empty($quantities)) {
                $defaults['MY_amount'] = $quantities[0]['quantity'];
            }
        }
    }
    
    return $defaults;
}

/**
 * 안전한 HTML 출력
 * 
 * @param string $text 출력할 텍스트
 * @return string 이스케이프된 HTML
 */
function safe_cadarok_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * 가격 포맷팅 (카다록 전용)
 * 
 * @param int|float $price 가격
 * @param bool $include_won 원 단위 포함 여부
 * @return string 포맷된 가격
 */
function format_cadarok_price($price, $include_won = true) {
    $formatted = number_format($price);
    return $include_won ? $formatted . '원' : $formatted;
}

/**
 * 카다록 페이지 타이틀 생성
 * 
 * @param string $title 기본 타이틀
 * @return string 완성된 페이지 타이틀
 */
function generate_cadarok_title($title = "") {
    if (empty($title)) {
        $title = CADAROK_PAGE_TITLE;
    }
    return $title . " | 두손기획인쇄";
}

/**
 * 세션 상태 확인 (카다록 전용)
 */
function check_cadarok_session() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * 데이터베이스 연결 상태 확인 (카다록 전용)
 * 
 * @param mysqli $db 데이터베이스 연결
 */
function check_cadarok_db_connection($db) {
    if (!$db) {
        die("카다록 시스템: 데이터베이스 연결에 실패했습니다: " . mysqli_connect_error());
    }
}

/**
 * 로그 정보 생성 (카다록 전용)
 * 
 * @return array 로그 정보
 */
function generateCadarokLogInfo() {
    return [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'url' => $_SERVER['REQUEST_URI'] ?? '',
        'timestamp' => date('Y-m-d H:i:s'),
        'session_id' => session_id(),
        'y' => date('Y'),
        'md' => date('m-d'),
        'time' => date('H:i:s'),
    ];
}

/**
 * AJAX 응답 전송 (카다록 전용)
 * 
 * @param bool $success 성공 여부
 * @param mixed $data 응답 데이터
 * @param string $message 메시지
 */
function send_cadarok_ajax_response($success, $data = null, $message = '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 입력값 검증 (카다록 전용)
 * 
 * @param mixed $value 검증할 값
 * @param string $type 타입 (string, int, email 등)
 * @return mixed 검증된 값 또는 false
 */
function validate_cadarok_input($value, $type = 'string') {
    switch ($type) {
        case 'int':
            return filter_var($value, FILTER_VALIDATE_INT);
        case 'email':
            return filter_var($value, FILTER_VALIDATE_EMAIL);
        case 'url':
            return filter_var($value, FILTER_VALIDATE_URL);
        case 'cadarok_no':
            // 카다록 번호 검증 (숫자만 허용)
            return preg_match('/^[0-9]+$/', $value) ? $value : false;
        case 'quantity':
            // 수량 검증 (100-50000 범위)
            $quantity = filter_var($value, FILTER_VALIDATE_INT);
            return ($quantity >= 100 && $quantity <= 50000) ? $quantity : false;
        case 'string':
        default:
            return filter_var($value, FILTER_SANITIZE_STRING);
    }
}

/**
 * 파일 업로드 경로 생성 (카다록 전용)
 * 
 * @param string $base_path 기본 경로
 * @return string 생성된 업로드 경로
 */
function generate_cadarok_upload_path($base_path = '../../uploads/') {
    $date = date('Y/m/d');
    $ip = str_replace('.', '_', $_SERVER['REMOTE_ADDR']);
    $timestamp = time();
    
    $path = $base_path . 'cadarok/' . $date . '/' . $ip . '/' . $timestamp . '/';
    
    // 디렉토리 생성
    if (!file_exists($path)) {
        mkdir($path, 0755, true);
    }
    
    return $path;
}

/**
 * 카다록 제품 카드 HTML 생성
 * 
 * @param array $product_info 제품 정보
 * @return string HTML
 */
function generate_cadarok_product_card($product_info) {
    $color_class = $product_info['color'] ?? 'primary';
    $html = '<div class="product-card">';
    $html .= '<div class="product-card-header ' . $color_class . '">';
    $html .= '<h3>' . safe_cadarok_html($product_info['title']) . '</h3>';
    $html .= '</div>';
    $html .= '<div class="product-card-divider ' . $color_class . '"></div>';
    $html .= '<div class="product-card-body">';
    $html .= '<p>' . safe_cadarok_html($product_info['description']) . '</p>';
    
    // 추가 정보가 있으면 표시
    if (!empty($product_info['features'])) {
        $html .= '<div class="product-info-box">';
        $html .= '<h4>⚙️ 특징</h4>';
        $html .= '<ul>';
        foreach ($product_info['features'] as $feature) {
            $html .= '<li>' . safe_cadarok_html($feature) . '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
    }
    
    if (!empty($product_info['tip'])) {
        $html .= '<div class="product-tip-box ' . $color_class . '">';
        $html .= '<p>' . safe_cadarok_html($product_info['tip']) . '</p>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

/**
 * 카다록 갤러리 이미지 경로 생성
 * 
 * @param string $category 카테고리
 * @param string $filename 파일명
 * @return string 이미지 경로
 */
function get_cadarok_image_path($category = 'cadarok', $filename = '') {
    $base_path = '../../images/gallery/';
    return $base_path . $category . '/' . $filename;
}

/**
 * 에러 처리 함수 (카다록 전용)
 * 
 * @param string $error_type 에러 타입
 * @param string $custom_message 커스텀 메시지
 */
function handle_cadarok_error($error_type = 'general', $custom_message = '') {
    global $cadarok_error_messages;
    
    $message = $custom_message ?: ($cadarok_error_messages[$error_type] ?? '알 수 없는 오류가 발생했습니다.');
    
    error_log("카다록 시스템 오류 [$error_type]: $message");
    
    if (defined('AJAX_REQUEST') && AJAX_REQUEST) {
        send_cadarok_ajax_response(false, null, $message);
    } else {
        // 일반 페이지에서는 알림창 표시
        echo "<script>alert('" . addslashes($message) . "');</script>";
    }
}

/**
 * 카다록 갤러리 데이터 가져오기
 * 
 * @param mysqli $connect 데이터베이스 연결
 * @param int $page 페이지 번호
 * @param int $per_page 페이지당 아이템 수
 * @return array 갤러리 데이터
 */
function getCadarokGalleryData($connect, $page = 1, $per_page = 12) {
    $offset = ($page - 1) * $per_page;
    
    // 총 개수 조회
    $count_query = "SELECT COUNT(*) as total FROM mlangorder_printauto WHERE product_type = 'cadarok' AND ThingCate != ''";
    $count_result = mysqli_query($connect, $count_query);
    $total_count = $count_result ? mysqli_fetch_assoc($count_result)['total'] : 0;
    
    // 데이터 조회
    $query = "SELECT No, ThingCate, ImgFolder, Type_1 FROM mlangorder_printauto 
              WHERE product_type = 'cadarok' AND ThingCate != '' 
              ORDER BY No DESC LIMIT $offset, $per_page";
    
    $result = mysqli_query($connect, $query);
    $items = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = [
                'id' => $row['No'],
                'title' => $row['ThingCate'] ?: '카다록 샘플',
                'path' => $row['ImgFolder'],
                'details' => $row['Type_1']
            ];
        }
    }
    
    return [
        'items' => $items,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $per_page,
            'total_count' => $total_count,
            'total_pages' => ceil($total_count / $per_page),
            'has_prev' => $page > 1,
            'has_next' => $page < ceil($total_count / $per_page)
        ]
    ];
}
?>