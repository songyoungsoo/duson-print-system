<?php
// ScriptSearch 파일 출력 테스트
include"../../db.php";

$TIO_CODE = "envelope";
$Ttable = "envelope";
$GGTABLE = "mlangprintauto_transactioncate";

echo "<h2>envelope_ScriptSearch.php 출력 테스트</h2>";
echo "<hr>";
echo "<h3>실제 출력 결과:</h3>";
echo "<div style='border: 2px solid #ccc; padding: 10px; background: #f9f9f9;'>";

// ScriptSearch 파일 include
ob_start();
include "envelope_ScriptSearch.php";
$output = ob_get_clean();

echo "<pre>" . htmlspecialchars($output) . "</pre>";
echo "</div>";

echo "<hr>";
echo "<h3>HTML로 렌더링된 결과:</h3>";
echo "<div style='border: 2px solid #ccc; padding: 10px; background: #fff;'>";
echo $output;
echo "</div>";

mysqli_close($db);
?>
