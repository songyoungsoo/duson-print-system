<?php
/**
 * 주문의 모든 파일을 ZIP으로 다운로드
 */

session_start();

// 관리자 권한 체크 (필요시 활성화)
// include "../../includes/auth.php";
// if (!isAdmin()) {
//     die("권한이 없습니다.");
// }

$no = isset($_GET['no']) ? intval($_GET['no']) : 0;

if ($no <= 0) {
    die("주문 번호가 필요합니다.");
}

// DB에서 주문 정보 조회
include "../../db.php";

$stmt = $db->prepare("SELECT ImgFolder, name, Type FROM mlangorder_printauto WHERE no = ?");
$stmt->bind_param("i", $no);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row || empty($row['ImgFolder'])) {
    die("주문 정보를 찾을 수 없습니다.");
}

$img_folder = $row['ImgFolder'];
$customer_name = $row['name'];
$product_type = $row['Type'];

// 파일 경로 자동 감지
$folder_path = null;
$possible_paths = [
    "../../ImgFolder/{$img_folder}",
    "../../{$img_folder}",
    "../../mlangorder_printauto/upload/{$no}"
];

foreach ($possible_paths as $path) {
    if (is_dir($path)) {
        $folder_path = $path;
        break;
    }
}

if (!$folder_path) {
    die("파일 폴더를 찾을 수 없습니다: {$img_folder}");
}

// 폴더 내 파일 목록
$files = array_diff(scandir($folder_path), ['.', '..']);

if (empty($files)) {
    die("다운로드할 파일이 없습니다.");
}

// ZIP 파일명 생성
$safe_customer_name = preg_replace('/[^a-zA-Z0-9가-힣]/', '_', $customer_name);
$zip_filename = "{$safe_customer_name}_{$no}_{$product_type}.zip";

// 임시 ZIP 파일 경로
$temp_zip = sys_get_temp_dir() . '/' . uniqid('order_') . '.zip';

// ZIP 생성
$zip = new ZipArchive();
if ($zip->open($temp_zip, ZipArchive::CREATE) !== TRUE) {
    die("ZIP 파일 생성 실패");
}

// 파일 추가
$file_count = 0;
foreach ($files as $file) {
    $file_path = $folder_path . '/' . $file;
    if (is_file($file_path)) {
        $zip->addFile($file_path, $file);
        $file_count++;
    }
}

$zip->close();

if ($file_count == 0) {
    unlink($temp_zip);
    die("압축할 파일이 없습니다.");
}

// ZIP 다운로드
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
header('Content-Length: ' . filesize($temp_zip));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');

readfile($temp_zip);

// 임시 파일 삭제
unlink($temp_zip);

exit;
?>
