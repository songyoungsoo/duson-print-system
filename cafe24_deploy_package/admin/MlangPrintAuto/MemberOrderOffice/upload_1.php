<?php
if (isset($_FILES['photofile_1']) && is_uploaded_file($_FILES['photofile_1']['tmp_name'])) {
    $file = $_FILES['photofile_1'];
    $photofile_1_name = basename($file['name']);
    $photofile_1_tmp  = $file['tmp_name'];
    $photofile_1_size = $file['size'];

    $upload_dir = $upload_dir ?? "./upload"; // 기본 업로드 디렉토리 지정
    $MAXFSIZE = $MAXFSIZE ?? 2048; // 최대 크기 (KB)

    $file_info = pathinfo($photofile_1_name);
    $file_extension = strtolower($file_info['extension'] ?? '');

    // 위험 확장자 차단
    $blocked_ext = ['php', 'php3', 'phtml', 'inc', 'asp'];
    if (in_array($file_extension, $blocked_ext)) {
        echo "<script>alert('? 보안상의 이유로 .php, .asp 등은 업로드할 수 없습니다. 확장자를 변경해주세요.');</script>";
        exit;
    }

    // 파일 사이즈 체크
    if ($photofile_1_size > ($MAXFSIZE * 1024)) {
        $size_kb = intval($photofile_1_size / 1024);
        echo "<script>alert('? 파일 크기 초과: {$size_kb}KB (최대 허용: {$MAXFSIZE}KB)');</script>";
        exit;
    }

    // 한글 파일명 변환 처리
    if (preg_match('/[^a-zA-Z0-9_\-\.]/', $file_info['filename'])) {
        $unique_code = date("YmdHis") . rand(1000, 9999);
        $photofile_1_name = "{$unique_code}.{$file_extension}";
    }

    // 동일 파일 존재 시 이름 변경
    $destination = "{$upload_dir}/{$photofile_1_name}";
    if (file_exists($destination)) {
        $photofile_1_name = time() . "_{$photofile_1_name}";
        $destination = "{$upload_dir}/{$photofile_1_name}";
    }

    // 실제 파일 이동
    if (!move_uploaded_file($photofile_1_tmp, $destination)) {
        echo "<script>alert('? 파일 이동 실패. 권한 또는 경로를 확인하세요.');</script>";
        exit;
    }

    $photofile_1Name = $photofile_1_name;
}
?>
