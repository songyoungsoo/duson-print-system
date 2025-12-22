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
function processDetailUpload($table, $required = false) {
    global $max_file_size, $allowed_extensions, $upload_base_dir;
    
    if (!isset($_FILES['upfile']) || $_FILES['upfile']['error'] !== UPLOAD_ERR_OK) {
        if ($required) {
            return ['success' => false, 'error' => '포트폴리오 이미지를 업로드해주세요.'];
        }
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
 * 썸네일 이미지 자동 생성
 * @param string $source_filename 원본 이미지 파일명
 * @param string $table 테이블명 (폴더명)
 * @param string $bbs_dir BBS 디렉토리 경로
 * @return string 생성된 썸네일 파일명
 */
function createThumbnail($source_filename, $table, $bbs_dir = '.') {
    global $upload_base_dir;
    
    if (empty($source_filename)) {
        return '';
    }
    
    $source_path = $upload_base_dir . $table . '/' . $source_filename;
    
    // 원본 파일이 존재하지 않으면 빈 문자열 반환
    if (!file_exists($source_path)) {
        return '';
    }
    
    // 썸네일 파일명 생성 (thumb_ 접두사 추가)
    $path_info = pathinfo($source_filename);
    $thumbnail_filename = 'thumb_' . $path_info['filename'] . '.jpg'; // 썸네일은 항상 JPG로 저장
    $thumbnail_path = $upload_base_dir . $table . '/' . $thumbnail_filename;
    
    // 이미지 정보 가져오기
    $image_info = getimagesize($source_path);
    if ($image_info === false) {
        return '';
    }
    
    list($source_width, $source_height, $image_type) = $image_info;
    
    // 썸네일 크기 설정 (최대 200x200, 비율 유지)
    $max_width = 200;
    $max_height = 200;
    
    // 비율 계산
    $ratio = min($max_width / $source_width, $max_height / $source_height);
    $thumb_width = round($source_width * $ratio);
    $thumb_height = round($source_height * $ratio);
    
    // 원본 이미지 리소스 생성
    $source_image = null;
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source_path);
            break;
        case IMAGETYPE_BMP:
            // BMP는 PHP 7.2+ 에서 지원
            if (function_exists('imagecreatefrombmp')) {
                $source_image = imagecreatefrombmp($source_path);
            }
            break;
    }
    
    if (!$source_image) {
        return '';
    }
    
    // 썸네일 이미지 리소스 생성
    $thumbnail_image = imagecreatetruecolor($thumb_width, $thumb_height);
    
    // PNG 투명도 처리
    if ($image_type == IMAGETYPE_PNG) {
        imagealphablending($thumbnail_image, false);
        imagesavealpha($thumbnail_image, true);
        $transparent = imagecolorallocatealpha($thumbnail_image, 255, 255, 255, 127);
        imagefill($thumbnail_image, 0, 0, $transparent);
    } else {
        // 흰색 배경
        $white = imagecolorallocate($thumbnail_image, 255, 255, 255);
        imagefill($thumbnail_image, 0, 0, $white);
    }
    
    // 이미지 리샘플링
    imagecopyresampled(
        $thumbnail_image, $source_image,
        0, 0, 0, 0,
        $thumb_width, $thumb_height,
        $source_width, $source_height
    );
    
    // 썸네일 저장 (JPG 품질 85)
    $success = imagejpeg($thumbnail_image, $thumbnail_path, 85);
    
    // 메모리 해제
    imagedestroy($source_image);
    imagedestroy($thumbnail_image);
    
    if ($success) {
        // 파일 권한 설정
        chmod($thumbnail_path, 0644);
        return $thumbnail_filename;
    }
    
    return '';
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
    // 포트폴리오 이미지 업로드 처리 (단일 업로드)
    // POST mode 확인하여 새 글 작성시에는 이미지 필수로 처리
    $mode = isset($_POST['mode']) ? $_POST['mode'] : '';
    $is_new_post = ($mode === 'write_ok');
    
    $detail_result = processDetailUpload($table, $is_new_post);
    if ($detail_result['success']) {
        $UPFILENAME = $detail_result['filename'] ?? '';
        $FILESIZE = $detail_result['size'] ?? 0;
        
        // 썸네일 자동 생성 (업로드가 성공한 경우에만)
        if (!empty($UPFILENAME)) {
            $CONTENTNAME = createThumbnail($UPFILENAME, $table);
            if (empty($CONTENTNAME)) {
                $CONTENTNAME = $UPFILENAME; // 썸네일 생성 실패시 원본 사용
            }
        }
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