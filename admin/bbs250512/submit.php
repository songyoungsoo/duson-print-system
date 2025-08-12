<?php
include "../../db.php";

header('Content-Type: text/html; charset=UTF-8');
$title = $_POST['title'] ?? '';
$pass = $_POST['pass'] ?? '';
$skin = $_POST['skin'] ?? 'default';
// 테이블 이름 검증
$table = isset($_POST['table']) ? $_POST['table'] : (isset($_GET['table']) ? $_GET['table'] : '');


if (strtolower($table) === "a") {
    echo ("<script>
        alert('테이블명: $table 은(는) 사용할 수 없는 테이블명입니다.\\n\\n다른 이름으로 시도해주세요.');
        history.go(-1);
    </script>");
    exit;
}
//  Mlang_BBS_Admin 테이블에 중복된 테이블명이 있는지 확인
$stmt = $db->prepare("SELECT * FROM  Mlang_BBS_Admin WHERE id = ?");
if ($stmt === false) {
    die('prepare() failed: ' . htmlspecialchars($db->error));
}
$stmt->bind_param("s", $table);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->num_rows;
$stmt->close();

if ($rows > 0) {
    echo ("<script>
        alert('ERROR(1): $table ID를 가진 테이블이 이미 존재합니다.\\n\\n다른 이름으로 시도해주세요.');
        history.go(-1);
    </script>");
    exit;
}

// Mlang_${table}_bbs 테이블이 존재하는지 확인
$query = "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?";
$stmt = $db->prepare($query);
if ($stmt === false) {
    die('prepare() failed: ' . htmlspecialchars($db->error));
}
$dbname = $dataname; // DB 이름을 적절히 변경하세요
$tablename = "Mlang_" . $table . "_bbs";
$stmt->bind_param("ss", $dbname, $tablename);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo ("<script>
        alert('ERROR(2): $table ID를 가진 테이블이 이미 존재합니다.\\n\\n다른 이름으로 시도해주세요.');
        history.go(-1);
    </script>");
    exit;
}
$stmt->close();

$date = date("Y-m-d");
$dbinsert = "INSERT INTO  Mlang_BBS_Admin 
(title, id, pass, skin, header, footer, header_include, footer_include, file_select, link_select, recnum, lnum, cutlen, New_Article, date_select, name_select, count_select, recommendation_select, secret_select, write_select, view_select, td_width, td_color1, td_color2, MAXFSIZE, PointBoardView, PointBoard, PointComent, ComentStyle, date, NoticeStyle, advance, NoticeStyleSu, BBS_Level) 
VALUES (?, ?, ?, ?, '', '', '', '', 'yes', 'yes', '15', '8', '100', '3', 'yes', 'yes', 'yes', 'yes', 'yes', 'member', 'member', '96%', '237CBE', 'FFFFFF', '2000', '0', '0', '0', 'yes', ?, '', 'no', 'no', '5', '5')";

$stmt = $db->prepare($dbinsert);
if ($stmt === false) {
    die('prepare() failed: ' . htmlspecialchars($db->error));
}
$stmt->bind_param("ssssss", $title, $table, $pass, $skin, $date);
$stmt->execute();
$stmt->close();

// Mlang_${table}_bbs 테이블 생성
$query1 = sprintf("DROP TABLE IF EXISTS Mlang_%s_bbs", $table);
$query2 = sprintf("CREATE TABLE Mlang_%s_bbs (
    Mlang_bbs_no MEDIUMINT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
    Mlang_bbs_member VARCHAR(100) NOT NULL DEFAULT '',
    Mlang_bbs_title TEXT,
    Mlang_bbs_style VARCHAR(100) NOT NULL DEFAULT 'br',
    Mlang_bbs_connent TEXT,
    Mlang_bbs_link TEXT,
    Mlang_bbs_file TEXT,
    Mlang_bbs_pass VARCHAR(100) NOT NULL DEFAULT '',
    Mlang_bbs_count INT(12) NOT NULL DEFAULT '0',
    Mlang_bbs_rec INT(12) NOT NULL DEFAULT '0',
    Mlang_bbs_secret VARCHAR(100) NOT NULL DEFAULT 'yes',
    Mlang_bbs_reply INT(12) NOT NULL DEFAULT '0',
    Mlang_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    CATEGORY TEXT,
    NoticeSelect VARCHAR(100) NOT NULL DEFAULT 'no',
    PRIMARY KEY (Mlang_bbs_no)
)", $table);

if (!mysqli_query($db, $query1)) {
    die('Error executing query1: ' . htmlspecialchars(mysqli_error($db)));
}
if (!mysqli_query($db, $query2)) {
    die('Error executing query2: ' . htmlspecialchars(mysqli_error($db)));
}

// Mlang_${table}_bbs_coment 테이블 생성
$query1 = sprintf("DROP TABLE IF EXISTS Mlang_%s_bbs_coment", $table);
$query2 = sprintf("CREATE TABLE Mlang_%s_bbs_coment (
    Mlang_coment_no MEDIUMINT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
    Mlang_coment_BBS_no VARCHAR(100) NOT NULL DEFAULT '',
    Mlang_coment_member_id VARCHAR(100) NOT NULL DEFAULT '',
    Mlang_coment_title TEXT,
    Mlang_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (Mlang_coment_no)
)", $table);

if (!mysqli_query($db, $query1)) {
    die('Error executing query1: ' . htmlspecialchars(mysqli_error($db)));
}
if (!mysqli_query($db, $query2)) {
    die('Error executing query2: ' . htmlspecialchars(mysqli_error($db)));
}

// 디렉토리 생성 및 권한 설정
$dir = "../bbs/upload/$table";
if (!file_exists($dir)) {
    mkdir($dir, 0755);
    chmod($dir, 0777);
}

echo ("<script>
    alert('테이블명: $table\\n\\n테이블 제목: $title 이(가) 성공적으로 생성되었습니다.');
    window.location.href='$PHP_SELF?mode=list';
</script>");

mysqli_close($db);
?>
