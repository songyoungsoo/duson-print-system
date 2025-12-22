<?php
header('Content-Type: text/plain; charset=utf-8');

include 'db.php';

if (!$db) {
    die("데이터베이스 연결 실패\n");
}

echo "=== mlangorder_printauto 테이블 구조 ===\n\n";

$result = mysqli_query($db, "SHOW COLUMNS FROM mlangorder_printauto");
$fields = [];
$i = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $fields[] = $row['Field'];
    printf("%2d. %-30s %-30s %-10s\n", $i++, $row['Field'], $row['Type'], $row['Null']);
}

echo "\n총 필드 수: " . count($fields) . "개\n";

mysqli_close($db);
?>
