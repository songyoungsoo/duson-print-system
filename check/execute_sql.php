<?php
/**
 * SQL 실행 스크립트
 * 경로: execute_sql.php
 * 사용법: http://localhost/execute_sql.php
 */

// 데이터베이스 연결
include "db.php";

// UTF-8 설정
mysqli_set_charset($db, 'utf8mb4');

echo "<h2>is_member 필드 추가 작업</h2>";
echo "<pre>";

// 1. is_member 컬럼 추가
$sql1 = "ALTER TABLE mlangorder_printauto
         ADD COLUMN is_member TINYINT(1) DEFAULT 0 COMMENT '회원여부: 0=비회원, 1=회원'";

echo "1. is_member 컬럼 추가 시도...\n";
if (mysqli_query($db, $sql1)) {
    echo "✅ is_member 컬럼이 추가되었습니다.\n\n";
} else {
    $error = mysqli_error($db);
    if (strpos($error, "Duplicate column") !== false) {
        echo "ℹ️  is_member 컬럼이 이미 존재합니다.\n\n";
    } else {
        echo "❌ 오류: " . $error . "\n\n";
    }
}

// 2. 기존 주문 데이터 업데이트 (email이 users 테이블에 있는 경우 회원으로 표시)
$sql2 = "UPDATE mlangorder_printauto o
         SET is_member = 1
         WHERE EXISTS (
             SELECT 1 FROM users u WHERE u.email = o.email
         )";

echo "2. 기존 주문 데이터 업데이트...\n";
if (mysqli_query($db, $sql2)) {
    $affected = mysqli_affected_rows($db);
    echo "✅ $affected 개의 주문이 회원 주문으로 업데이트되었습니다.\n\n";
} else {
    echo "❌ 오류: " . mysqli_error($db) . "\n\n";
}

// 3. 결과 확인
$sql3 = "SELECT
    COUNT(*) as total_orders,
    SUM(CASE WHEN is_member = 1 THEN 1 ELSE 0 END) as member_orders,
    SUM(CASE WHEN is_member = 0 THEN 1 ELSE 0 END) as guest_orders
FROM mlangorder_printauto";

echo "3. 결과 확인:\n";
$result = mysqli_query($db, $sql3);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "총 주문 수: " . $row['total_orders'] . "\n";
    echo "회원 주문: " . $row['member_orders'] . "\n";
    echo "비회원 주문: " . $row['guest_orders'] . "\n\n";
} else {
    echo "❌ 오류: " . mysqli_error($db) . "\n\n";
}

// 4. 테이블 구조 확인
$sql4 = "DESCRIBE mlangorder_printauto";
echo "4. 테이블 구조 확인 (is_member 필드):\n";
$result = mysqli_query($db, $sql4);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['Field'] === 'is_member') {
            echo "필드명: " . $row['Field'] . "\n";
            echo "타입: " . $row['Type'] . "\n";
            echo "Null: " . $row['Null'] . "\n";
            echo "기본값: " . $row['Default'] . "\n";
            echo "설명: " . $row['Comment'] . "\n";
            break;
        }
    }
}

echo "\n✅ 모든 작업이 완료되었습니다!\n";
echo "</pre>";

mysqli_close($db);
?>
