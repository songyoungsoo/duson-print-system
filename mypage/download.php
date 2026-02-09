<?php
/**
 * 고객 전용 파일 다운로드
 * - 로그인 필수
 * - 본인 주문 파일만 다운로드 가능 (email 또는 name 매칭)
 */

require_once __DIR__ . '/auth_required.php';
require_once __DIR__ . '/../includes/ImagePathResolver.php';

ob_start();

// 파라미터
$order_no = intval($_GET['no'] ?? 0);
$downfile = $_GET['downfile'] ?? '';

if ($order_no <= 0 || empty($downfile)) {
    die("<script>alert('잘못된 요청입니다.'); history.back();</script>");
}

// 보안: 파일명에서 경로 제거
$downfile = basename($downfile);

// 본인 주문 확인 (order_detail.php와 동일한 소유권 검증)
$user_email = $current_user['email'] ?? '';
$user_name = $current_user['name'] ?? '';

$query = "SELECT * FROM mlangorder_printauto WHERE no = ?";
$params = [$order_no];
$types = "i";

if (!empty($user_email)) {
    $query .= " AND email = ?";
    $params[] = $user_email;
    $types .= "s";
} elseif (!empty($user_name)) {
    $query .= " AND name = ?";
    $params[] = $user_name;
    $types .= "s";
} else {
    die("<script>alert('사용자 정보를 확인할 수 없습니다.'); history.back();</script>");
}

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    die("<script>alert('본인의 주문만 다운로드할 수 있습니다.'); history.back();</script>");
}

// ImagePathResolver로 파일 경로 해석
$file_result = ImagePathResolver::getFilesFromRow($order, false);
$target_file = null;

foreach ($file_result['files'] as $f) {
    $fname = $f['saved_name'] ?? $f['name'] ?? '';
    if ($fname === $downfile && !empty($f['path']) && file_exists($f['path'])) {
        $target_file = $f['path'];
        break;
    }
}

if (!$target_file) {
    die("<script>alert('파일을 찾을 수 없습니다.'); history.back();</script>");
}

// 보안: 파일이 서버 루트 내에 있는지 확인
$real_path = realpath($target_file);
$document_root = realpath($_SERVER['DOCUMENT_ROOT']);
if (!$real_path || strpos($real_path, $document_root) !== 0) {
    die("<script>alert('허용되지 않은 경로입니다.'); history.back();</script>");
}

// 파일 확장자 검증
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'ai', 'psd', 'zip', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'pptx', 'ppt', 'hwp', 'eps', 'tif', 'tiff', 'bmp', 'svg'];
$file_ext = strtolower(pathinfo($downfile, PATHINFO_EXTENSION));
if (!in_array($file_ext, $allowed_extensions)) {
    die("<script>alert('허용되지 않은 파일 형식입니다.'); history.back();</script>");
}

// 파일 다운로드
$file_size = filesize($real_path);

// UTF-8 파일명 처리
if (preg_match('/MSIE|Trident/i', $_SERVER['HTTP_USER_AGENT'] ?? '')) {
    $encoded_filename = str_replace('+', '%20', urlencode($downfile));
} else {
    $encoded_filename = $downfile;
}

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$encoded_filename\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . $file_size);
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$fp = fopen($real_path, "rb");
if ($fp) {
    while (!feof($fp)) {
        echo fread($fp, 100 * 1024);
    }
    fclose($fp);
    flush();
} else {
    die("<script>alert('파일을 열 수 없습니다.'); history.back();</script>");
}

ob_end_flush();
