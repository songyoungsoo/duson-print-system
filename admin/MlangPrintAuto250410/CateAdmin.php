<?php
include_once __DIR__ . "/../../db.php";
include_once __DIR__ . "/../config.php";

// $TtableTitles = include_once __DIR__ . "/table_title_config.php";

$mode       = $_GET['mode'] ?? $_POST['mode'] ?? '';
$code       = $_GET['code'] ?? $_POST['code'] ?? '';
$Ttable     = $_GET['Ttable'] ?? $_POST['Ttable'] ?? '';
$TreeSelect = $_GET['TreeSelect'] ?? $_POST['TreeSelect'] ?? '';
$ACate      = $_GET['ACate'] ?? $_POST['ACate'] ?? '';
$ATreeNo    = $_GET['ATreeNo'] ?? $_POST['ATreeNo'] ?? '';
$no         = $_GET['no'] ?? $_POST['no'] ?? '';
$title      = $_POST['title'] ?? '';
$BigNo      = $_POST['BigNo'] ?? $_GET['BigNo'] ?? '0';
$PHP_SELF   = htmlspecialchars($_SERVER['PHP_SELF'] ?? '');
$View_TtableC = htmlspecialchars($Ttable);

$GGTABLE = "MlangPrintAuto_" . $Ttable;
$View_title = '';
include "CateAdmin_title.php";
$Ttable     = $_GET['Ttable'] ?? $_POST['Ttable'] ?? '';
$DF_Tatle_1 = $TtableTitles[$Ttable][0] ?? '';
$DF_Tatle_2 = $TtableTitles[$Ttable][1] ?? '';
$DF_Tatle_3 = $TtableTitles[$Ttable][2] ?? '';

$mysqli = new mysqli($host, $user, $password, $dataname);
if ($mysqli->connect_error) {
    die("DB 연결 실패: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8");

if ($mode === "save") {
    $title = trim($title);
    if ($code === "modify" && $no) {
        $stmt = $mysqli->prepare("UPDATE $GGTABLE SET title=?, BigNo=? WHERE no=?");
        $stmt->bind_param("sii", $title, $BigNo, $no);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO $GGTABLE (title, BigNo, Ttable) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $title, $BigNo, $Ttable);
    }

    if ($stmt->execute()) {
        echo "<script>alert('저장되었습니다.'); opener.parent.location.reload(); window.close();</script>";
    } else {
        echo "<script>alert('저장 실패: {$stmt->error}');</script>";
    }
    exit;
}

if ($mode === "form") {
    if ($code === "modify" && $no) {
        $res = $mysqli->query("SELECT * FROM $GGTABLE WHERE no = " . (int)$no);
        $row = $res->fetch_assoc();
        $View_title = htmlspecialchars($row['title'] ?? '');
    }
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>카테고리 관리</title>
</head>
<body>
<form name="form1" method="post" action="<?php echo  $PHP_SELF ?>">
    <input type="hidden" name="mode" value="save">
    <input type="hidden" name="code" value="<?php echo  htmlspecialchars($code) ?>">
    <input type="hidden" name="Ttable" value="<?php echo  htmlspecialchars($Ttable) ?>">
    <label><?php echo  $DF_Tatle_1 ?>: <input type="text" name="title" value="<?php echo  $View_title ?>"></label><br>
    <input type="submit" value="저장">
</form>
</body>
</html>
<?php } ?>
