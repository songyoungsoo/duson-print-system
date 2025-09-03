<?php
ob_start();

// 데이터베이스 연결
include "../../db.php";

// 주문 번호와 파일명 가져오기
$no = $_GET['no'] ?? 0;
$downfile = $_GET['downfile'] ?? '';

if (!$no || !$downfile) {
    echo "<script>alert('잘못된 파일 요청입니다.'); history.back();</script>";
    exit;
}

// 파일명 필터링 (보안)
if (preg_match('/[\\/:*?"<>|]/', $downfile)) {
    echo "<script>alert('잘못된 파일명입니다.'); history.back();</script>";
    exit;
}

// 외부에서 접근 방지 (HTTP_REFERER 체크)
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
    echo "<script>alert('외부에서는 다운로드 받으실 수 없습니다.'); history.back();</script>";
    exit;
}

// 데이터베이스에서 해당 주문의 파일 정보 확인
$stmt = $db->prepare("SELECT ThingCate FROM MlangOrder_PrintAuto WHERE no = ?");
$stmt->bind_param("i", $no);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $db_filename = $row['ThingCate'];
    
    // 요청한 파일명과 DB의 파일명이 일치하는지 확인
    if ($db_filename !== $downfile) {
        echo "<script>alert('파일 접근 권한이 없습니다.'); history.back();</script>";
        exit;
    }
    
    // 파일 경로 설정 (주문번호별 폴더)
    $filepath = "../../MlangOrder_PrintAuto/upload/$no/" . basename($downfile);
    
    // 파일 존재 확인
    if (file_exists($filepath)) {
        // 파일명 안전 처리 (한글 파일명 지원)
        $safe_filename = basename($downfile);
        
        // 브라우저별 한글 파일명 처리
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false) {
            $safe_filename = urlencode($safe_filename);
        } else {
            $safe_filename = $safe_filename;
        }

        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"$safe_filename\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($filepath));
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");
        header("Expires: 0");

        // 파일 출력
        readfile($filepath);
        flush();
        exit;
    } else {
        echo "<script>alert('파일이 존재하지 않습니다: $filepath'); history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('해당 주문을 찾을 수 없습니다.'); history.back();</script>";
    exit;
}

$stmt->close();
$db->close();
?>

