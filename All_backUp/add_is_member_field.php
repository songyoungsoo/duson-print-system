<?php
/**
 * mlangorder_printauto 테이블에 is_member 필드 추가
 * 로컬과 프로덕션 양쪽에서 실행 가능
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h2>mlangorder_printauto 테이블 is_member 필드 추가</h2>";
echo "<pre>";

// 데이터베이스 연결
include 'db.php';

if (!$db) {
    die("❌ 데이터베이스 연결 실패\n");
}

echo "✅ 데이터베이스 연결 성공\n\n";

// 실행 확인
$confirm = $_GET['confirm'] ?? '';
if ($confirm !== 'yes') {
    echo "⚠️ 이 작업은 mlangorder_printauto 테이블에 is_member 필드를 추가합니다.\n\n";
    echo "실행하려면 URL에 ?confirm=yes를 추가하세요.\n";
    echo "예: " . $_SERVER['PHP_SELF'] . "?confirm=yes\n";
    exit;
}

echo "=== is_member 필드 추가 시작 ===\n\n";

// 1. 현재 테이블 구조 확인
echo "1. 현재 테이블 구조 확인:\n";
$result = mysqli_query($db, "SHOW COLUMNS FROM mlangorder_printauto");
$fields = [];
while ($row = mysqli_fetch_assoc($result)) {
    $fields[] = $row['Field'];
}
echo "   📊 현재 필드 수: " . count($fields) . "개\n";

// is_member 필드가 이미 있는지 확인
if (in_array('is_member', $fields)) {
    echo "   ℹ️ is_member 필드가 이미 존재합니다.\n";
    mysqli_close($db);
    exit;
}

// 2. is_member 필드 추가
echo "\n2. is_member 필드 추가 중...\n";

$sql = "ALTER TABLE mlangorder_printauto
        ADD COLUMN is_member TINYINT(1) DEFAULT NULL
        AFTER envelope_additional_options_total";

if (mysqli_query($db, $sql)) {
    echo "   ✅ is_member 필드 추가 완료\n";
} else {
    die("   ❌ 필드 추가 실패: " . mysqli_error($db) . "\n");
}

// 3. 검증
echo "\n3. 테이블 구조 재확인:\n";
$result = mysqli_query($db, "SHOW COLUMNS FROM mlangorder_printauto");
$new_fields = [];
while ($row = mysqli_fetch_assoc($result)) {
    $new_fields[] = $row['Field'];
}
echo "   📊 업데이트 후 필드 수: " . count($new_fields) . "개\n";

// is_member 필드 확인
if (in_array('is_member', $new_fields)) {
    echo "   ✅ is_member 필드 확인 완료\n";

    // 필드 상세 정보
    $result = mysqli_query($db, "SHOW COLUMNS FROM mlangorder_printauto LIKE 'is_member'");
    $row = mysqli_fetch_assoc($result);
    echo "\n   필드 상세:\n";
    echo "   - Field: {$row['Field']}\n";
    echo "   - Type: {$row['Type']}\n";
    echo "   - Null: {$row['Null']}\n";
    echo "   - Default: {$row['Default']}\n";
}

mysqli_close($db);

echo "\n✅ 작업 완료!\n";
echo "</pre>";
?>
