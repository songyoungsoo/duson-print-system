<?php
/**
 * 파일 다운로드 핸들러
 * 구버전 호환 + 다중 파일 지원
 */

session_start();

// 관리자 권한 체크 (필요시 활성화)
// include "../../includes/auth.php";
// if (!isAdmin()) {
//     die("권한이 없습니다.");
// }

// 파라미터 받기
$no = isset($_GET['no']) ? intval($_GET['no']) : 0;
$downfile = isset($_GET['downfile']) ? $_GET['downfile'] : '';
$path = isset($_GET['path']) ? $_GET['path'] : '';

if (empty($downfile)) {
    die("파일명이 지정되지 않았습니다.");
}

// 파일 경로 구성
if (!empty($path)) {
    // 경로가 지정된 경우
    $file_path = "../../" . urldecode($path) . "/" . urldecode($downfile);
} else {
    // 경로가 없으면 DB에서 조회
    include "../../db.php";
    
    if ($no > 0) {
        $stmt = $db->prepare("SELECT ImgFolder FROM mlangorder_printauto WHERE no = ?");
        $stmt->bind_param("i", $no);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row && !empty($row['ImgFolder'])) {
            $img_folder = $row['ImgFolder'];
            
            // 경로 자동 감지
            if (file_exists("../../ImgFolder/{$img_folder}/{$downfile}")) {
                $file_path = "../../ImgFolder/{$img_folder}/{$downfile}";
            } elseif (file_exists("../../{$img_folder}/{$downfile}")) {
                $file_path = "../../{$img_folder}/{$downfile}";
            } elseif (file_exists("../../mlangorder_printauto/upload/{$no}/{$downfile}")) {
                $file_path = "../../mlangorder_printauto/upload/{$no}/{$downfile}";
            } else {
                die("파일을 찾을 수 없습니다: {$img_folder}/{$downfile}");
            }
        } else {
            die("주문 정보를 찾을 수 없습니다.");
        }
    } else {
        die("주문 번호가 필요합니다.");
    }
}

// 파일 존재 확인
if (!file_exists($file_path)) {
    die("파일이 존재하지 않습니다: " . htmlspecialchars($file_path));
}

// 파일 정보
$file_size = filesize($file_path);
$file_name = basename($file_path);

// MIME 타입 감지
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file_path);
finfo_close($finfo);

// 기본 MIME 타입 설정
if (!$mime_type) {
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $mime_types = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'pdf' => 'application/pdf',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'psd' => 'image/vnd.adobe.photoshop',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed'
    ];
    $mime_type = $mime_types[$ext] ?? 'application/octet-stream';
}

// 다운로드 헤더 설정
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . $file_size);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');
header('Expires: 0');

// 파일 출력 (대용량 파일 지원)
$handle = fopen($file_path, 'rb');
if ($handle) {
    while (!feof($handle)) {
        echo fread($handle, 8192); // 8KB씩 읽기
        flush();
    }
    fclose($handle);
} else {
    die("파일을 열 수 없습니다.");
}

exit;
?>
