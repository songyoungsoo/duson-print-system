<?php
$MAXFSIZE = 200 * 1024;

if (isset($_FILES['File5']) && is_uploaded_file($_FILES['File5']['tmp_name'])) {
    $file = $_FILES['File5'];
    $filename = basename($file['name']);
    $tmp_name = $file['tmp_name'];
    $file_size = $file['size'];
    $upload_dir = "./upload";

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $blocked_exts = ['php', 'php3', 'phtml', 'inc', 'asp', 'html'];

    if (in_array($ext, $blocked_exts)) {
        echo "<script>alert('보안상 php, asp 등 확장자는 업로드할 수 없습니다.'); history.go(-1);</script>";
        exit;
    }

    if ($file_size > $MAXFSIZE) {
        $kb = intval($file_size / 1024);
        echo "<script>alert('파일 용량 초과: {$kb}KB (최대 " . ($MAXFSIZE / 1024) . "KB)'); history.go(-1);</script>";
        exit;
    }

    if (file_exists("$upload_dir/$filename")) {
        $filename = time() . "_$filename";
    }

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (move_uploaded_file($tmp_name, "$upload_dir/$filename")) {
        $File5NAME = $filename;
    } else {
        echo "<script>alert('업로드 실패'); history.go(-1);</script>";
        exit;
    }
}
?>