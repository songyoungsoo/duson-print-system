<?php
/**
 * 범용 파일 업로드 핸들러
 * 모든 품목에서 사용 가능
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// 통합 업로드 설정 포함
include "upload_config.php";

// 데이터베이스 연결
include "../db.php";
$connect = $db;

if (!$connect) {
    echo json_encode(['success' => false, 'message' => '데이터베이스 연결에 실패했습니다.']);
    exit;
}

mysqli_set_charset($connect, "utf8");

// POST 데이터 받기
$product_type = $_POST['product_type'] ?? 'general';
$session_id = $_POST['session_id'] ?? session_id();

// 파일 업로드 검증
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => '파일 업로드에 실패했습니다.']);
    exit;
}

$file = $_FILES['file'];
$fileName = $file['name'];
$fileSize = $file['size'];
$fileTmpName = $file['tmp_name'];
$fileType = $file['type'];

// 상품별 설정 가져오기
$config = getProductUploadConfig($product_type);

// 파일 크기 검증
if ($fileSize > $config['max_file_size']) {
    $maxSizeMB = $config['max_file_size'] / 1024 / 1024;
    echo json_encode(['success' => false, 'message' => "파일 크기가 {$maxSizeMB}MB를 초과합니다."]);
    exit;
}

// 파일 형식 검증
if (!in_array($fileType, $config['allowed_types'])) {
    $allowedFormats = implode(', ', $config['allowed_extensions']);
    echo json_encode(['success' => false, 'message' => "지원하지 않는 파일 형식입니다. ({$allowedFormats}만 허용)"]);
    exit;
}

// 파일 확장자 검증
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
if (!in_array($fileExtension, $config['allowed_extensions'])) {
    echo json_encode(['success' => false, 'message' => '지원하지 않는 파일 확장자입니다.']);
    exit;
}

// 새로운 통합 업로드 시스템 사용
$uploadDir = getTempUploadPath($session_id);
if (!createUploadDirectory($uploadDir)) {
    echo json_encode(['success' => false, 'message' => '업로드 디렉토리 생성에 실패했습니다.']);
    exit;
}

// 파일명 중복 방지
$uniqueFileName = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
$uploadPath = $uploadDir . $uniqueFileName;

// 파일 이동
if (!move_uploaded_file($fileTmpName, $uploadPath)) {
    echo json_encode(['success' => false, 'message' => '파일 저장에 실패했습니다.']);
    exit;
}

// 데이터베이스에 파일 정보 저장
$relativePath = str_replace('../', '', $uploadPath);

// 파일 업로드 테이블이 없으면 생성
createUploadedFilesTable($connect);

// 파일 정보 저장
$insertQuery = "INSERT INTO uploaded_files (session_id, product_type, original_name, file_name, file_path, file_size, file_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($connect, $insertQuery);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssis", 
        $session_id, $product_type, $fileName, $uniqueFileName, $relativePath, $fileSize, $fileType);
    
    if (mysqli_stmt_execute($stmt)) {
        $fileId = mysqli_insert_id($connect);
        
        // 성공 응답
        echo json_encode([
            'success' => true,
            'message' => '파일이 성공적으로 업로드되었습니다.',
            'file_info' => [
                'id' => $fileId,
                'original_name' => $fileName,
                'file_name' => $uniqueFileName,
                'file_path' => $relativePath,
                'file_size' => $fileSize,
                'file_type' => $fileType,
                'upload_date' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        // 파일 삭제 (DB 저장 실패 시)
        unlink($uploadPath);
        echo json_encode(['success' => false, 'message' => '파일 정보 저장에 실패했습니다.']);
    }
    
    mysqli_stmt_close($stmt);
} else {
    // 파일 삭제 (쿼리 준비 실패 시)
    unlink($uploadPath);
    echo json_encode(['success' => false, 'message' => '데이터베이스 오류가 발생했습니다.']);
}

mysqli_close($connect);

/**
 * 상품별 업로드 설정 가져오기
 */
function getProductUploadConfig($product_type) {
    $configs = [
        'sticker' => [
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf']
        ],
        'leaflet' => [
            'max_file_size' => 15 * 1024 * 1024, // 15MB
            'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/zip'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'zip']
        ],
        'namecard' => [
            'max_file_size' => 5 * 1024 * 1024, // 5MB
            'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf']
        ],
        'cadarok' => [
            'max_file_size' => 20 * 1024 * 1024, // 20MB
            'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/zip'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'zip']
        ],
        'littleprint' => [
            'max_file_size' => 20 * 1024 * 1024, // 20MB
            'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/zip'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'zip']
        ],
        'envelope' => [
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf']
        ],
        'ncrflambeau' => [
            'max_file_size' => 15 * 1024 * 1024, // 15MB
            'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/zip'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'zip']
        ],
        'merchandisebond' => [
            'max_file_size' => 8 * 1024 * 1024, // 8MB
            'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf']
        ],
        'msticker' => [
            'max_file_size' => 12 * 1024 * 1024, // 12MB
            'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf']
        ]
    ];
    
    // 기본 설정
    $default = [
        'max_file_size' => 10 * 1024 * 1024,
        'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf']
    ];
    
    return $configs[$product_type] ?? $default;
}

/**
 * 업로드 파일 테이블 생성
 */
function createUploadedFilesTable($connect) {
    $createTableQuery = "CREATE TABLE IF NOT EXISTS uploaded_files (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(255) NOT NULL,
        product_type VARCHAR(50) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_size INT NOT NULL,
        file_type VARCHAR(100) NOT NULL,
        upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_session (session_id),
        INDEX idx_product_type (product_type),
        INDEX idx_session_product (session_id, product_type)
    )";
    
    mysqli_query($connect, $createTableQuery);
}
?>