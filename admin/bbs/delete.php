<?php
include "../../db.php";
include "../config.php"; // 세션 로그인

$id = $_GET['id'] ?? '';

if (empty($id)) {
    echo "
    <html>
    <script language='javascript'>
    window.alert('유효한 ID가 아닙니다.');
    </script>
    <meta http-equiv='Refresh' content='0; URL=../bbs_admin.php?mode=list'>
    </html>
    ";
    exit;
}

// Ensure $id is safe to use in queries
$id = $db->real_escape_string($id);

// Board 테이블 삭제
$dropTableQuery = "DROP TABLE IF EXISTS Mlang_{$id}_bbs";
if (!$db->query($dropTableQuery)) {
    die("Error dropping table: " . $db->error);
}

// Coment 테이블 삭제
$dropComentTableQuery = "DROP TABLE IF EXISTS Mlang_{$id}_bbs_coment";
if (!$db->query($dropComentTableQuery)) {
    die("Error dropping coment table: " . $db->error);
}

// 게시판 관리자 정보 삭제
$stmt = $db->prepare("DELETE FROM  Mlang_BBS_Admin WHERE id=?");
if ($stmt) {
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->close();
} else {
    die("Error preparing statement: " . $db->error);
}

// 연결 종료
$db->close();

// 업로드된 파일 삭제
$upload_dir = "../../bbs/upload/$id";
if (is_dir($upload_dir)) {
    $files = array_diff(scandir($upload_dir), array('.', '..'));
    foreach ($files as $file) {
        $file_path = "$upload_dir/$file";
        if (is_file($file_path)) {
            unlink($file_path);
        }
    }
    rmdir($upload_dir);
}

echo "
<html>
<script language='javascript'>
window.alert('성공적으로 게시판의 모든 데이터를 삭제했습니다.');
</script>
<meta http-equiv='Refresh' content='0; URL=../bbs_admin.php?mode=list'>
</html>
";
exit;
?>
