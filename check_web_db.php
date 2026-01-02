<?php
/**
 * 웹 서버 DB 상태 확인
 */

include "/var/www/html/db.php";
$connect = $db;

mysqli_set_charset($connect, 'utf8mb4');

echo "=== 웹 서버 DB 확인 ===\n\n";

// 1. 최근 주문 확인
echo "1. 최근 주문 10개:\n";
$query = "SELECT no, Type, date FROM mlangorder_printauto ORDER BY no DESC LIMIT 10";
$result = mysqli_query($connect, $query);
while ($row = mysqli_fetch_assoc($result)) {
    echo "  - 주문 #{$row['no']}: {$row['Type']} ({$row['date']})\n";
}

echo "\n2. Type='기타' 주문 개수:\n";
$query = "SELECT COUNT(*) as cnt FROM mlangorder_printauto WHERE Type = '기타'";
$result = mysqli_query($connect, $query);
$row = mysqli_fetch_assoc($result);
echo "  - 총 {$row['cnt']}개\n";

if ($row['cnt'] > 0) {
    echo "\n3. Type='기타' 주문 샘플:\n";
    $query = "SELECT no, Type, LEFT(Type_1, 100) as preview FROM mlangorder_printauto WHERE Type = '기타' ORDER BY no DESC LIMIT 5";
    $result = mysqli_query($connect, $query);
    while ($r = mysqli_fetch_assoc($result)) {
        echo "  - 주문 #{$r['no']}: {$r['preview']}...\n";
    }
}

echo "\n4. 포스터 관련 주문 (Type LIKE '%포스터%'):\n";
$query = "SELECT COUNT(*) as cnt FROM mlangorder_printauto WHERE Type LIKE '%포스터%'";
$result = mysqli_query($connect, $query);
$row = mysqli_fetch_assoc($result);
echo "  - 총 {$row['cnt']}개\n";

// 5. 103968번 주문 확인
echo "\n5. 주문 #103968 확인:\n";
$query = "SELECT no, Type, LEFT(Type_1, 100) as preview FROM mlangorder_printauto WHERE no = 103968";
$result = mysqli_query($connect, $query);
if ($row = mysqli_fetch_assoc($result)) {
    echo "  - 존재함: Type={$row['Type']}\n";
    echo "  - Type_1 preview: {$row['preview']}...\n";
} else {
    echo "  - 존재하지 않음\n";
}

// 6. DB 정보
echo "\n6. 현재 연결된 DB 정보:\n";
$query = "SELECT DATABASE() as db_name";
$result = mysqli_query($connect, $query);
$row = mysqli_fetch_assoc($result);
echo "  - 데이터베이스: {$row['db_name']}\n";

mysqli_close($connect);
?>
