<?php
/**
 * 선택된 포트폴리오 이미지 업로드 API
 * 팝업 갤러리에서 선택한 이미지들을 사용자 업로드 폴더로 복사
 * Created: 2025년 8월 (AI Assistant)
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// 데이터베이스 연결
include "../db.php";

try {
    // POST 데이터 받기
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $images_json = isset($_POST['images']) ? $_POST['images'] : '';
    
    if (empty($category) || empty($images_json)) {
        throw new Exception('카테고리와 이미지 데이터가 필요합니다.');
    }
    
    // JSON 디코딩
    $selected_images = json_decode($images_json, true);
    if (!$selected_images || !is_array($selected_images)) {
        throw new Exception('이미지 데이터가 올바르지 않습니다.');
    }
    
    // 세션 시작 (사용자 구분용)
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $session_id = session_id();
    
    // 업로드 디렉토리 생성
    $base_upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $category;
    $date_dir = date('Y/m/d');
    $user_upload_dir = $base_upload_dir . '/' . $date_dir . '/' . $session_id;
    
    if (!file_exists($user_upload_dir)) {
        if (!mkdir($user_upload_dir, 0755, true)) {
            throw new Exception('업로드 디렉토리를 생성할 수 없습니다.');
        }
    }
    
    $uploaded_files = [];
    $success_count = 0;
    $error_count = 0;
    $errors = [];
    
    foreach ($selected_images as $image) {
        try {
            // 소스 파일 경로 확인
            $source_path = $_SERVER['DOCUMENT_ROOT'] . $image['path'];
            
            if (!file_exists($source_path)) {
                $errors[] = "파일을 찾을 수 없음: " . $image['title'];
                $error_count++;
                continue;
            }
            
            // 파일 정보 가져오기
            $file_info = pathinfo($source_path);
            $file_extension = isset($file_info['extension']) ? $file_info['extension'] : 'jpg';
            
            // 새 파일명 생성 (timestamp + 원본명 일부)
            $timestamp = time() . '_' . mt_rand(10000, 99999);
            $safe_title = preg_replace('/[^a-zA-Z0-9가-힣_-]/', '_', $image['title']);
            $safe_title = substr($safe_title, 0, 30); // 길이 제한
            $new_filename = $timestamp . '_' . $safe_title . '.' . $file_extension;
            
            // 대상 파일 경로
            $target_path = $user_upload_dir . '/' . $new_filename;
            
            // 파일 복사
            if (copy($source_path, $target_path)) {
                $uploaded_files[] = [
                    'id' => $image['id'],
                    'title' => $image['title'],
                    'original_path' => $image['path'],
                    'new_filename' => $new_filename,
                    'new_path' => '/uploads/' . $category . '/' . $date_dir . '/' . $session_id . '/' . $new_filename,
                    'size' => filesize($target_path),
                    'uploaded_at' => date('Y-m-d H:i:s')
                ];
                $success_count++;
            } else {
                $errors[] = "복사 실패: " . $image['title'];
                $error_count++;
            }
            
        } catch (Exception $e) {
            $errors[] = $image['title'] . ': ' . $e->getMessage();
            $error_count++;
        }
    }
    
    // 업로드 로그 저장 (선택적)
    if ($db && $success_count > 0) {
        $log_data = json_encode([
            'category' => $category,
            'session_id' => $session_id,
            'uploaded_files' => $uploaded_files,
            'success_count' => $success_count,
            'error_count' => $error_count,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        
        $insert_query = "INSERT INTO portfolio_upload_log 
                        (session_id, category, file_data, created_at) 
                        VALUES (?, ?, ?, NOW())";
        
        if ($stmt = mysqli_prepare($db, $insert_query)) {
            mysqli_stmt_bind_param($stmt, "sss", $session_id, $category, $log_data);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    // 응답 생성
    $response = [
        'success' => true,
        'message' => "{$success_count}개 이미지가 성공적으로 업로드되었습니다.",
        'data' => [
            'uploaded_files' => $uploaded_files,
            'success_count' => $success_count,
            'error_count' => $error_count,
            'errors' => $errors,
            'upload_dir' => '/uploads/' . $category . '/' . $date_dir . '/' . $session_id,
            'category' => $category,
            'session_id' => $session_id
        ]
    ];
    
    if ($error_count > 0) {
        $response['message'] .= " ({$error_count}개 파일에서 오류 발생)";
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // 오류 응답
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => [
            'uploaded_files' => [],
            'success_count' => 0,
            'error_count' => 0,
            'errors' => [$e->getMessage()]
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} finally {
    // 데이터베이스 연결 종료
    if (isset($db)) {
        mysqli_close($db);
    }
}
?>