<?php
/**
 * 보안이 강화된 이미지 업로드 처리
 * 포트폴리오 게시판 전용
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 업로드 설정
$max_file_size = 5 * 1024 * 1024; // 5MB
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
$upload_base_dir = __DIR__ . '/upload/';

/**
 * 안전한 파일명 생성
 */
function generateSafeFileName($original_name) {
    $path_info = pathinfo($original_name);
    $extension = strtolower($path_info['extension'] ?? '');
    
    // 타임스탬프와 랜덤 문자열로 안전한 파일명 생성
    $safe_name = date('YmdHis') . '_' . uniqid() . '.' . $extension;
    
    return $safe_name;
}

/**
 * 파일 확장자 검증
 */
function validateFileExtension($filename, $allowed_extensions) {
    $path_info = pathinfo($filename);
    $extension = strtolower($path_info['extension'] ?? '');
    
    return in_array($extension, $allowed_extensions);
}

/**
 * 이미지 파일 검증
 */
function validateImageFile($tmp_path) {
    // getimagesize로 실제 이미지 파일인지 확인
    $image_info = getimagesize($tmp_path);
    if ($image_info === false) {
        return false;
    }
    
    // MIME 타입 검증
    $allowed_mime_types = [
        'image/jpeg',
        'image/png', 
        'image/gif',
        'image/bmp',
        'image/x-ms-bmp'
    ];
    
    return in_array($image_info['mime'], $allowed_mime_types);
}

/**
 * 썸네일 이미지 업로드 처리
 */
function processContentUpload($table) {
    global $max_file_size, $allowed_extensions, $upload_base_dir;
    
    if (!isset($_FILES['CONTENT']) || $_FILES['CONTENT']['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => '파일이 업로드되지 않았습니다.'];
    }
    
    $file = $_FILES['CONTENT'];
    
    // 파일 크기 검증
    if ($file['size'] > $max_file_size) {
        return ['success' => false, 'error' => '파일 크기가 5MB를 초과합니다.'];
    }
    
    // 확장자 검증
    if (!validateFileExtension($file['name'], $allowed_extensions)) {
        return ['success' => false, 'error' => '허용되지 않는 파일 형식입니다.'];
    }
    
    // 실제 이미지 파일인지 검증
    if (!validateImageFile($file['tmp_name'])) {
        return ['success' => false, 'error' => '유효한 이미지 파일이 아닙니다.'];
    }
    
    // 업로드 디렉토리 생성
    $upload_dir = $upload_base_dir . $table . '/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return ['success' => false, 'error' => '업로드 디렉토리 생성에 실패했습니다.'];
        }
    }
    
    // 안전한 파일명 생성
    $safe_filename = generateSafeFileName($file['name']);
    $upload_path = $upload_dir . $safe_filename;
    
    // 파일 이동
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // 파일 권한 설정
        chmod($upload_path, 0644);
        
        return [
            'success' => true, 
            'filename' => $safe_filename,
            'size' => $file['size'],
            'path' => $upload_path
        ];
    } else {
        return ['success' => false, 'error' => '파일 업로드에 실패했습니다.'];
    }
}

/**
 * 상세 이미지 업로드 처리
 */
function processDetailUpload($table) {
    global $max_file_size, $allowed_extensions, $upload_base_dir;
    
    if (!isset($_FILES['upfile']) || $_FILES['upfile']['error'] !== UPLOAD_ERR_OK) {
        return ['success' => true, 'filename' => '']; // 선택사항이므로 성공으로 처리
    }
    
    $file = $_FILES['upfile'];
    
    // 파일 크기 검증
    if ($file['size'] > $max_file_size) {
        return ['success' => false, 'error' => '상세 이미지 크기가 5MB를 초과합니다.'];
    }
    
    // 확장자 검증
    if (!validateFileExtension($file['name'], $allowed_extensions)) {
        return ['success' => false, 'error' => '허용되지 않는 파일 형식입니다.'];
    }
    
    // 실제 이미지 파일인지 검증
    if (!validateImageFile($file['tmp_name'])) {
        return ['success' => false, 'error' => '유효한 이미지 파일이 아닙니다.'];
    }
    
    // 업로드 디렉토리 생성
    $upload_dir = $upload_base_dir . $table . '/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return ['success' => false, 'error' => '업로드 디렉토리 생성에 실패했습니다.'];
        }
    }
    
    // 안전한 파일명 생성
    $safe_filename = generateSafeFileName($file['name']);
    $upload_path = $upload_dir . $safe_filename;
    
    // 파일 이동
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // 파일 권한 설정
        chmod($upload_path, 0644);
        
        return [
            'success' => true, 
            'filename' => $safe_filename,
            'size' => $file['size'],
            'path' => $upload_path
        ];
    } else {
        return ['success' => false, 'error' => '상세 이미지 업로드에 실패했습니다.'];
    }
}

/**
 * 기존 파일 삭제
 */
function deleteOldFile($table, $filename) {
    global $upload_base_dir;
    
    if (empty($filename)) {
        return true;
    }
    
    $file_path = $upload_base_dir . $table . '/' . $filename;
    
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    
    return true;
}

// 전역 변수로 결과 저장
$CONTENTNAME = '';
$UPFILENAME = '';
$FILE_CONTENTSIZE = 0;
$FILESIZE = 0;
$upload_errors = [];

// 테이블명 가져오기
$table = isset($_POST['table']) ? $_POST['table'] : '';

if (empty($table)) {
    $upload_errors[] = '테이블명이 지정되지 않았습니다.';
} else {
    // 썸네일 이미지 업로드 처리
    $content_result = processContentUpload($table);
    if ($content_result['success']) {
        $CONTENTNAME = $content_result['filename'];
        $FILE_CONTENTSIZE = $content_result['size'] ?? 0;
    } else {
        $upload_errors[] = $content_result['error'];
    }
    
    // 상세 이미지 업로드 처리 (선택사항)
    $detail_result = processDetailUpload($table);
    if ($detail_result['success']) {
        $UPFILENAME = $detail_result['filename'] ?? '';
        $FILESIZE = $detail_result['size'] ?? 0;
    } else {
        $upload_errors[] = $detail_result['error'];
    }
}

// 오류가 있는 경우 처리
if (!empty($upload_errors)) {
    $error_message = implode('\\n', $upload_errors);
    echo "<script>
        alert('" . addslashes($error_message) . "');
        history.go(-1);
    </script>";
    exit;
}
?>