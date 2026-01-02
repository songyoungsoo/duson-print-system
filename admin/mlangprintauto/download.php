<?php
/**
 * 파일 다운로드 시스템 (개선 버전)
 * 다중 디렉토리 지원 및 보안 강화
 */

ob_start();

// 기본 디렉토리 설정
$base_dir = "../../";

// 파라미터 받기
$downfile = $_GET['downfile'] ?? '';
$path = $_GET['path'] ?? 'shop/data';
$no = $_GET['no'] ?? '';

// 1. 파일명 검증
if (empty($downfile)) {
    die("<script>alert('파일명이 지정되지 않았습니다.'); history.back();</script>");
}

// 2. 보안: 경로 조작 방지 (Path Traversal Attack)
$downfile = basename($downfile); // 파일명만 추출
$path = str_replace(['../', '..\\', './'], '', $path); // 상위 디렉토리 접근 차단

// 3. Referer 체크 (기본 보안)
if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_HOST'])) {
    if (!preg_match('/' . preg_quote($_SERVER['HTTP_HOST'], '/') . '/i', $_SERVER['HTTP_REFERER'])) {
        die("<script>alert('외부에서는 다운로드 받으실 수 없습니다.'); history.back();</script>");
    }
}

// 4. 허용된 경로 목록 (화이트리스트 방식)
$allowed_paths = [
    'shop/data',
    'mlangorder_printauto/upload',
    'uploads/sticker_new',
    'uploads/orders',
    'uploads/namecard',
    'uploads/envelope',
    'uploads/cadarok',
    'uploads/leaflet',
    'uploads/littleprint',
    'uploads/merchandisebond',
    'uploads/msticker',
    'uploads',  // 날짜별 업로드 폴더 (uploads/2025/10/09/IP/) 허용
    'ImgFolder'  // 레거시 경로 형식 지원 (_MlangPrintAuto_*_index.php/...)
];

// 경로 검증
$path_allowed = false;
foreach ($allowed_paths as $allowed) {
    if (strpos($path, $allowed) === 0) {
        $path_allowed = true;
        break;
    }
}

if (!$path_allowed) {
    die("<script>alert('허용되지 않은 경로입니다.'); history.back();</script>");
}

// 5. 파일 경로 생성
$downfiledir = $base_dir . $path . '/';
$full_path = $downfiledir . $downfile;

// 6. 📋 JSON 기반 절대 경로 우선 확인 (StandardUploadHandler 표준화된 주문)
$json_path_found = false;
$db_img_folder = ''; // DB에서 조회한 ImgFolder 정보 저장
if (!empty($no)) {
    // mlangorder_printauto 테이블에서 uploaded_files와 ImgFolder 조회
    include $base_dir . "db.php";
    if (isset($db) && $db) {
        $query = "SELECT uploaded_files, ImgFolder FROM mlangorder_printauto WHERE no = ? LIMIT 1";
        $stmt = mysqli_prepare($db, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $no);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($row) {
                // ImgFolder 정보 저장 (폴백 경로에서 사용)
                $db_img_folder = $row['ImgFolder'] ?? '';

                // uploaded_files JSON 확인
                if (!empty($row['uploaded_files']) && $row['uploaded_files'] !== '0') {
                    $uploaded_files = json_decode($row['uploaded_files'], true);
                    if (is_array($uploaded_files)) {
                        foreach ($uploaded_files as $file_info) {
                            // saved_name과 일치하는 파일 찾기
                            if (isset($file_info['saved_name']) && $file_info['saved_name'] === $downfile) {
                                // 절대 경로 확인
                                if (isset($file_info['path']) && file_exists($file_info['path'])) {
                                    // 보안: 경로가 서버 루트 아래인지 확인
                                    $real_path = realpath($file_info['path']);
                                    $document_root = realpath($_SERVER['DOCUMENT_ROOT']);
                                    if ($real_path && strpos($real_path, $document_root) === 0) {
                                        $full_path = $real_path;
                                        $json_path_found = true;
                                        error_log("Download: JSON 절대 경로 사용 - $full_path");
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        mysqli_close($db);
    }
}

// 7. 파일 존재 확인 (레거시 호환 경로 지원)
if (!$json_path_found && !file_exists($full_path)) {
    // 대체 경로 시도 (여러 패턴 지원)
    $alternative_paths = [];

    // Pattern 0: DB에서 조회한 ImgFolder 경로 (최우선 폴백)
    if (!empty($db_img_folder)) {
        $alternative_paths[] = $base_dir . "ImgFolder/" . $db_img_folder . "/";
        error_log("Download 폴백: DB ImgFolder 경로 시도 - " . $base_dir . "ImgFolder/" . $db_img_folder . "/");
    }

    // Pattern 1: 주문번호 기반 경로
    if (!empty($no)) {
        $alternative_paths[] = $base_dir . "mlangorder_printauto/upload/$no/";
        $alternative_paths[] = $base_dir . "uploads/orders/$no/";
    }

    // Pattern 2: ImgFolder 기반 경로 (StandardUploadHandler 형식)
    // 예: _MlangPrintAuto_namecard_index.php/2025/1119/ipv6_1/1763508971/
    if (!empty($path) && strpos($path, '_MlangPrintAuto_') !== false) {
        $alternative_paths[] = $base_dir . "ImgFolder/" . $path . "/";
        $alternative_paths[] = $base_dir . $path . "/"; // 상대 경로도 시도

        // IPv6 주소 변환 대체 경로 (양방향 호환)
        if (strpos($path, '::1') !== false) {
            $ipv6_converted_path = str_replace('::1', 'ipv6_1', $path);
            $alternative_paths[] = $base_dir . "ImgFolder/" . $ipv6_converted_path . "/";
        }
        if (strpos($path, 'ipv6_1') !== false) {
            $ipv6_original_path = str_replace('ipv6_1', '::1', $path);
            $alternative_paths[] = $base_dir . "ImgFolder/" . $ipv6_original_path . "/";
        }
    }

    // Pattern 3: 레거시 ImgFolder 경로
    if (strpos($path, 'ImgFolder/') === 0) {
        $clean_path = str_replace('ImgFolder/', '', $path);
        $alternative_paths[] = $base_dir . "ImgFolder/" . $clean_path . "/";
    }

    // 모든 대체 경로 시도
    foreach ($alternative_paths as $alt_dir) {
        if (file_exists($alt_dir . $downfile)) {
            $full_path = $alt_dir . $downfile;
            error_log("Download: 대체 경로 사용 - $full_path");
            break;
        }
    }

    if (!file_exists($full_path)) {
        // NAS 폴백: 구서버에서 마이그레이션된 파일 (ipTIME NAS)
        $nas_host = 'dsp1830.ipdisk.co.kr';
        $nas_user = 'admin';
        $nas_pass = '1830';
        $nas_base = '/HDD2/share/ImgFolder_old';

        $nas_paths = [];
        if (!empty($db_img_folder)) {
            $nas_paths[] = $nas_base . '/' . $db_img_folder . '/' . $downfile;
        }
        if (!empty($path) && strpos($path, '_MlangPrintAuto_') !== false) {
            $nas_paths[] = $nas_base . '/' . $path . '/' . $downfile;
        }

        foreach ($nas_paths as $nas_path) {
            $ftp_url = "ftp://{$nas_user}:{$nas_pass}@{$nas_host}{$nas_path}";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $ftp_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FTP_USE_EPSV, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            $file_content = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($file_content !== false && strlen($file_content) > 0 && empty($err)) {
                error_log("Download: NAS 폴백 성공 - $nas_path");
                $enc_name = preg_match('/MSIE|Trident/i', $_SERVER['HTTP_USER_AGENT'] ?? '')
                    ? str_replace('+', '%20', urlencode($downfile)) : $downfile;
                header("Content-Type: application/octet-stream");
                header("Content-Disposition: attachment; filename=\"$enc_name\"");
                header("Content-Length: " . strlen($file_content));
                echo $file_content;
                ob_end_flush();
                exit;
            }
        }

        error_log("Download 실패: path=$path, file=$downfile, no=$no");
        die("<script>alert('존재하지 않는 파일입니다.'); history.back();</script>");
    }
}

// 8. 파일 타입 검증 (추가 보안)
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'ai', 'psd', 'zip', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'pptx', 'ppt', 'hwp', 'eps', 'tif', 'tiff', 'bmp', 'svg'];
$file_ext = strtolower(pathinfo($downfile, PATHINFO_EXTENSION));
if (!in_array($file_ext, $allowed_extensions)) {
    die("<script>alert('허용되지 않은 파일 형식입니다.'); history.back();</script>");
}

// 9. 파일 다운로드 처리
$file_size = filesize($full_path);

// UTF-8 파일명 처리 (한글 파일명 지원)
$encoded_filename = urlencode($downfile);
if (preg_match('/MSIE|Trident/i', $_SERVER['HTTP_USER_AGENT'])) {
    // IE 브라우저
    $encoded_filename = str_replace('+', '%20', $encoded_filename);
} else {
    // 기타 브라우저
    $encoded_filename = $downfile;
}

// HTTP 헤더 설정
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$encoded_filename\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . $file_size);
header("Cache-Control: cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 파일 전송
$fp = fopen($full_path, "rb");
if ($fp) {
    while (!feof($fp)) {
        echo fread($fp, 100 * 1024); // 100KB씩 전송
    }
    fclose($fp);
    flush();
} else {
    die("<script>alert('파일을 열 수 없습니다.'); history.back();</script>");
}

ob_end_flush();
?>
