<?php
include "../../db.php";
include "../config.php"; // 세션 로그인

header("Content-Disposition: attachment; filename=$TableName.sql");
header("Content-Type: application/octet-stream");
header("Pragma: no-cache");
header("Expires: 0");

$pResult = mysqli_query($db, "SHOW VARIABLES");

echo "-- 프로그램명: Mlang Web 프로그램3.0 - $TableName MySql DataBase DUMP\n";
echo "\n";
echo "-- 프로그램/홈페이지 제작 : http://www.websil.net - webmaster@websil.net\n";
echo "\n";
echo "-- Source from  <MySQL DUMP [Mlang (http://www.script.ne.kr-webmaster@script.ne.kr)]>\n";
echo "\n";

$bindir = "";

while ($rowArray = mysqli_fetch_assoc($pResult)) {
    if ($rowArray['Variable_name'] == 'basedir') {
        $bindir = rtrim($rowArray['Value'], '/') . "/bin/";
        break;
    }
}

mysqli_free_result($pResult);

if ($bindir) {
    $command = escapeshellcmd("{$bindir}mysqldump --user=$user --password=$password $dataname $TableName");
    passthru($command);
} else {
    echo "-- Error: Cannot find MySQL base directory.\n";
}

mysqli_close($db);
?>
