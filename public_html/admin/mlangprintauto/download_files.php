<?php
/**
 * 통합 파일 다운로드 시스템
 * 단일/다중 파일 다운로드 및 ZIP 압축 다운로드 지원
 */

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/UploadPathHelper.php';

// 요청 파라미터
$action = $_GET['action'] ?? 'single';
$orderNo = intval($_GET['order_no'] ?? 0);
$filename = $_GET['filename'] ?? '';

// 액션별 처리
switch ($action) {
    case 'single':
        // 단일 파일 다운로드
        downloadSingleFile($db, $orderNo, $filename);
        break;

    case 'zip':
        // 주문의 모든 파일을 ZIP으로 다운로드
        downloadOrderFilesAsZip($db, $orderNo);
        break;

    case 'preview':
        // 파일 미리보기 (이미지만)
        previewFile($db, $orderNo, $filename);
        break;

    default:
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

/**
 * 단일 파일 다운로드
 */
function downloadSingleFile($db, $orderNo, $filename) {
    if (!$orderNo || !$filename) {
        header('HTTP/1.1 400 Bad Request');
        echo 'Missing required parameters';
        exit;
    }

    // DB에서 파일 정보 조회
    $stmt = mysqli_prepare($db, "
        SELECT ImgFolder, ThingCate
        FROM mlangorder_printauto
        WHERE no = ? AND ThingCate = ?
    ");

    mysqli_stmt_bind_param($stmt, "is", $orderNo, $filename);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row) {
        header('HTTP/1.1 404 Not Found');
        echo 'File not found in database';
        exit;
    }

    // 파일 경로 복원
    $fileInfo = UploadPathHelper::getFilePathFromDB($row['ImgFolder'], $row['ThingCate']);

    if (!$fileInfo['exists']) {
        header('HTTP/1.1 404 Not Found');
        echo 'File does not exist on disk';
        exit;
    }

    // 파일 다운로드 전송
    $filePath = $fileInfo['full_path'];
    $downloadName = basename($fileInfo['web_path']);

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');

    readfile($filePath);
    exit;
}

/**
 * 주문의 모든 파일을 ZIP으로 다운로드
 */
function downloadOrderFilesAsZip($db, $orderNo) {
    if (!$orderNo) {
        header('HTTP/1.1 400 Bad Request');
        echo 'Missing order number';
        exit;
    }

    // 주문의 모든 파일 조회
    $files = UploadPathHelper::getOrderFiles($db, $orderNo);

    if (empty($files)) {
        header('HTTP/1.1 404 Not Found');
        echo '다운로드할 파일이 없습니다';
        exit;
    }

    // ZIP 파일 생성
    $zipFilename = 'order_' . $orderNo . '_files_' . date('Ymd_His') . '.zip';
    $zipResult = UploadPathHelper::createZipArchive($files, $zipFilename);

    if (!$zipResult['success']) {
        header('HTTP/1.1 500 Internal Server Error');
        echo $zipResult['error'];
        exit;
    }

    // ZIP 파일 다운로드 전송
    UploadPathHelper::sendZipDownload($zipResult['zip_path'], $zipFilename);
    exit;
}

/**
 * 파일 미리보기 (이미지만)
 */
function previewFile($db, $orderNo, $filename) {
    if (!$orderNo || !$filename) {
        header('HTTP/1.1 400 Bad Request');
        echo 'Missing required parameters';
        exit;
    }

    // DB에서 파일 정보 조회
    $stmt = mysqli_prepare($db, "
        SELECT ImgFolder, ThingCate
        FROM mlangorder_printauto
        WHERE no = ? AND ThingCate = ?
    ");

    mysqli_stmt_bind_param($stmt, "is", $orderNo, $filename);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row) {
        header('HTTP/1.1 404 Not Found');
        echo 'File not found';
        exit;
    }

    // 파일 경로 복원
    $fileInfo = UploadPathHelper::getFilePathFromDB($row['ImgFolder'], $row['ThingCate']);

    if (!$fileInfo['exists']) {
        header('HTTP/1.1 404 Not Found');
        echo 'File does not exist';
        exit;
    }

    // 이미지 파일인지 확인
    $filePath = $fileInfo['full_path'];
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    if (!in_array($extension, $imageExtensions)) {
        header('HTTP/1.1 415 Unsupported Media Type');
        echo 'Preview only available for images';
        exit;
    }

    // MIME 타입 설정
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'bmp' => 'image/bmp'
    ];

    header('Content-Type: ' . ($mimeTypes[$extension] ?? 'image/jpeg'));
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: max-age=3600');

    readfile($filePath);
    exit;
}
