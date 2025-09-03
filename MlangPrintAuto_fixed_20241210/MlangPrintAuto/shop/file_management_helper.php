<?php
/**
 * shop_temp 테이블 파일 관리 헬퍼 함수들
 * 경로: MlangPrintAuto/shop/file_management_helper.php
 * 
 * 파일 업로드, 저장, 조회, 삭제 등의 기능을 제공
 */

/**
 * 장바구니 아이템에 파일 정보 추가
 * 
 * @param mysqli $connect 데이터베이스 연결
 * @param int $cart_item_no 장바구니 아이템 번호
 * @param array $file_info 파일 정보
 * @param array $log_info 로그 정보
 * @return bool 성공 여부
 */
function addFileToCartItem($connect, $cart_item_no, $file_info, $log_info) {
    // 기존 파일 정보 조회
    $query = "SELECT img, file_info, file_path FROM shop_temp WHERE no = ?";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) return false;
    
    mysqli_stmt_bind_param($stmt, 'i', $cart_item_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $current_data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$current_data) return false;
    
    // 기존 파일 목록에 새 파일 추가
    $existing_files = [];
    if (!empty($current_data['file_info'])) {
        $existing_files = json_decode($current_data['file_info'], true) ?: [];
    }
    
    // 새 파일 정보 추가
    $new_file = [
        'original_name' => $file_info['original_name'],
        'saved_name' => $file_info['saved_name'],
        'file_size' => $file_info['file_size'],
        'file_type' => $file_info['file_type'],
        'upload_time' => time(),
        'upload_path' => $file_info['upload_path']
    ];
    
    $existing_files[] = $new_file;
    
    // 파일명 목록 업데이트 (기존 호환성)
    $file_names = array_column($existing_files, 'saved_name');
    $img_value = implode(',', $file_names);
    
    // 파일 경로 생성
    $file_path = "{$log_info['url']}/{$log_info['y']}/{$log_info['md']}/{$log_info['ip']}/{$log_info['time']}";
    
    // 데이터베이스 업데이트
    $update_query = "UPDATE shop_temp SET 
        img = ?, 
        file_info = ?, 
        file_path = ?,
        log_url = ?,
        log_y = ?,
        log_md = ?,
        log_ip = ?,
        log_time = ?
        WHERE no = ?";
    
    $stmt = mysqli_prepare($connect, $update_query);
    if (!$stmt) return false;
    
    $file_info_json = json_encode($existing_files, JSON_UNESCAPED_UNICODE);
    
    mysqli_stmt_bind_param($stmt, 'ssssssssi', 
        $img_value, 
        $file_info_json, 
        $file_path,
        $log_info['url'],
        $log_info['y'],
        $log_info['md'],
        $log_info['ip'],
        $log_info['time'],
        $cart_item_no
    );
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * 장바구니 아이템의 파일 목록 조회
 * 
 * @param mysqli $connect 데이터베이스 연결
 * @param int $cart_item_no 장바구니 아이템 번호
 * @return array 파일 목록
 */
function getCartItemFiles($connect, $cart_item_no) {
    $query = "SELECT file_info, file_path, img FROM shop_temp WHERE no = ?";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) return [];
    
    mysqli_stmt_bind_param($stmt, 'i', $cart_item_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$data || empty($data['file_info'])) {
        return [];
    }
    
    $files = json_decode($data['file_info'], true);
    return is_array($files) ? $files : [];
}

/**
 * 장바구니 아이템에서 특정 파일 삭제
 * 
 * @param mysqli $connect 데이터베이스 연결
 * @param int $cart_item_no 장바구니 아이템 번호
 * @param string $file_name 삭제할 파일명
 * @return bool 성공 여부
 */
function removeFileFromCartItem($connect, $cart_item_no, $file_name) {
    // 현재 파일 정보 조회
    $files = getCartItemFiles($connect, $cart_item_no);
    if (empty($files)) return false;
    
    // 해당 파일 제거
    $updated_files = array_filter($files, function($file) use ($file_name) {
        return $file['saved_name'] !== $file_name;
    });
    
    // 인덱스 재정렬
    $updated_files = array_values($updated_files);
    
    // 파일명 목록 업데이트
    $file_names = array_column($updated_files, 'saved_name');
    $img_value = implode(',', $file_names);
    
    // 데이터베이스 업데이트
    $update_query = "UPDATE shop_temp SET img = ?, file_info = ? WHERE no = ?";
    $stmt = mysqli_prepare($connect, $update_query);
    if (!$stmt) return false;
    
    $file_info_json = json_encode($updated_files, JSON_UNESCAPED_UNICODE);
    
    mysqli_stmt_bind_param($stmt, 'ssi', $img_value, $file_info_json, $cart_item_no);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    // 실제 파일도 삭제 (선택사항)
    if ($result) {
        $file_to_delete = array_filter($files, function($file) use ($file_name) {
            return $file['saved_name'] === $file_name;
        });
        
        if (!empty($file_to_delete)) {
            $file_to_delete = array_values($file_to_delete)[0];
            if (isset($file_to_delete['upload_path']) && file_exists($file_to_delete['upload_path'])) {
                unlink($file_to_delete['upload_path']);
            }
        }
    }
    
    return $result;
}

/**
 * 세션의 모든 장바구니 아이템 파일 정보 조회
 * 
 * @param mysqli $connect 데이터베이스 연결
 * @param string $session_id 세션 ID
 * @return array 파일 정보가 포함된 장바구니 아이템들
 */
function getCartItemsWithFiles($connect, $session_id) {
    $query = "SELECT no, product_type, img, file_info, file_path FROM shop_temp WHERE session_id = ? ORDER BY no DESC";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) return [];
    
    mysqli_stmt_bind_param($stmt, 's', $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['files'] = [];
        if (!empty($row['file_info'])) {
            $files = json_decode($row['file_info'], true);
            $row['files'] = is_array($files) ? $files : [];
        }
        $items[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $items;
}

/**
 * 주문 완료 시 파일 정보를 주문 테이블로 이전
 * 
 * @param mysqli $connect 데이터베이스 연결
 * @param string $session_id 세션 ID
 * @param string $order_id 주문 ID
 * @return array 이전된 파일 정보
 */
function transferFilesToOrder($connect, $session_id, $order_id) {
    $cart_items = getCartItemsWithFiles($connect, $session_id);
    $transferred_files = [];
    
    foreach ($cart_items as $item) {
        if (!empty($item['files'])) {
            $transferred_files[$item['no']] = [
                'product_type' => $item['product_type'],
                'file_path' => $item['file_path'],
                'files' => $item['files']
            ];
        }
    }
    
    return $transferred_files;
}

/**
 * 장바구니 비우기 시 관련 파일들 정리
 * 
 * @param mysqli $connect 데이터베이스 연결
 * @param string $session_id 세션 ID
 * @param bool $delete_files 실제 파일도 삭제할지 여부
 * @return bool 성공 여부
 */
function cleanupCartFiles($connect, $session_id, $delete_files = false) {
    if ($delete_files) {
        $cart_items = getCartItemsWithFiles($connect, $session_id);
        
        foreach ($cart_items as $item) {
            if (!empty($item['files'])) {
                foreach ($item['files'] as $file) {
                    if (isset($file['upload_path']) && file_exists($file['upload_path'])) {
                        unlink($file['upload_path']);
                    }
                }
            }
        }
    }
    
    return true;
}

/**
 * 파일 정보를 기존 parentList 형식으로 변환 (호환성)
 * 
 * @param array $files 파일 정보 배열
 * @return string parentList용 JavaScript 코드
 */
function convertFilesToParentListJS($files) {
    if (empty($files)) {
        return "// 첨부된 파일이 없습니다.";
    }
    
    $js_code = "// 장바구니에서 로드된 파일 목록\n";
    $js_code .= "if (typeof parentList !== 'undefined' && parentList) {\n";
    
    foreach ($files as $file) {
        $file_name = addslashes($file['saved_name']);
        $js_code .= "    parentList.options[parentList.options.length] = new Option('$file_name', '$file_name');\n";
    }
    
    $js_code .= "}\n";
    
    return $js_code;
}

/**
 * 로그 정보 생성 (기존 시스템 호환)
 * 
 * @param string $page_name 페이지 이름
 * @return array 로그 정보
 */
function generateFileLogInfo($page_name = '') {
    if (empty($page_name)) {
        $page_name = basename($_SERVER['PHP_SELF'], '.php');
    }
    
    // IP 주소를 안전한 파일명으로 변환
    $raw_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $safe_ip = sanitizeIpForFilename($raw_ip);
    
    return [
        'url' => $page_name,
        'y' => date('Y'),
        'md' => date('md'),
        'ip' => $safe_ip,
        'time' => time()
    ];
}

/**
 * IP 주소를 파일명에 안전한 형태로 변환
 * 
 * @param string $ip IP 주소
 * @return string 안전한 파일명
 */
function sanitizeIpForFilename($ip) {
    // IPv6 주소 처리
    if (strpos($ip, ':') !== false) {
        if ($ip === '::1') {
            return '127.0.0.1'; // IPv6 localhost를 IPv4로 변환
        }
        // 다른 IPv6 주소는 콜론을 언더스코어로 변경
        return str_replace(':', '_', $ip);
    }
    
    // IPv4 주소는 그대로 사용 (점은 파일명에 사용 가능)
    return $ip;
}

/**
 * 파일 업로드 디렉토리 생성
 * 
 * @param array $log_info 로그 정보
 * @return string 생성된 디렉토리 경로
 */
function createFileUploadDirectory($log_info) {
    // 기존 시스템과 동일한 경로 사용
    $base_path = "../../ImgFolder";
    
    // 절대 경로로 변환하여 확인
    $real_base_path = realpath($base_path);
    if (!$real_base_path) {
        // realpath가 실패하면 직접 경로 구성
        $current_dir = dirname(__FILE__);
        $real_base_path = realpath($current_dir . "/" . $base_path);
    }
    
    if (!$real_base_path || !is_dir($real_base_path)) {
        throw new Exception("ImgFolder 디렉토리를 찾을 수 없습니다. 경로: $base_path");
    }
    
    // 하위 디렉토리 경로 구성 (기존 시스템과 동일)
    $sub_path = "{$log_info['url']}/{$log_info['y']}/{$log_info['md']}/{$log_info['ip']}/{$log_info['time']}";
    $full_path = $real_base_path . "/" . $sub_path;
    
    // Windows 경로 정규화
    $full_path = str_replace('\\', '/', $full_path);
    
    // 디렉토리가 없으면 생성 (recursive)
    if (!file_exists($full_path)) {
        if (!mkdir($full_path, 0755, true)) {
            throw new Exception("디렉토리를 생성할 수 없습니다: $full_path");
        }
        // Windows에서 권한 설정
        if (PHP_OS_FAMILY === 'Windows') {
            chmod($full_path, 0777);
        }
    }
    
    return $full_path;
}
?>