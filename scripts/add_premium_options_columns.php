<?php
/**
 * mlangorder_printauto 테이블에 프리미엄 옵션 컬럼 추가
 * 명함 제품의 프리미엄 옵션(박, 넘버링, 미싱, 모서리 라운딩, 오시) 저장용
 */

include __DIR__ . '/../db.php';

if (!$db) {
    die("데이터베이스 연결 실패\n");
}

echo "====================================================================\n";
echo "mlangorder_printauto 테이블에 프리미엄 옵션 컬럼 추가\n";
echo "====================================================================\n\n";

// 추가할 컬럼 정의
$columns_to_add = [
    'premium_options' => "TEXT COMMENT '명함 프리미엄 옵션 JSON 데이터'",
    'premium_options_total' => "INT DEFAULT 0 COMMENT '프리미엄 옵션 총액'"
];

$added_count = 0;
$skipped_count = 0;

foreach ($columns_to_add as $column_name => $column_definition) {
    // 컬럼이 이미 존재하는지 확인
    $check_query = "SHOW COLUMNS FROM mlangorder_printauto LIKE '$column_name'";
    $result = mysqli_query($db, $check_query);

    if (mysqli_num_rows($result) > 0) {
        echo "⏭️  컬럼 '$column_name' 이미 존재\n";
        $skipped_count++;
        continue;
    }

    // 컬럼 추가
    $alter_query = "ALTER TABLE mlangorder_printauto ADD COLUMN $column_name $column_definition";

    if (mysqli_query($db, $alter_query)) {
        echo "✅ 컬럼 '$column_name' 추가 성공\n";
        $added_count++;
    } else {
        echo "❌ 컬럼 '$column_name' 추가 실패: " . mysqli_error($db) . "\n";
    }
}

echo "\n====================================================================\n";
echo "작업 완료!\n";
echo "====================================================================\n";
echo "추가된 컬럼: $added_count 개\n";
echo "건너뛴 컬럼: $skipped_count 개\n";
echo "\n";

// 최종 테이블 구조 확인
echo "🔍 프리미엄 옵션 컬럼 확인:\n";
echo "--------------------------------------------------------------------\n";

$verify_query = "SHOW COLUMNS FROM mlangorder_printauto WHERE Field LIKE '%premium%'";
$verify_result = mysqli_query($db, $verify_query);

if (mysqli_num_rows($verify_result) > 0) {
    while ($row = mysqli_fetch_assoc($verify_result)) {
        echo "  " . $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "  ⚠️ 프리미엄 옵션 컬럼을 찾을 수 없습니다.\n";
}

echo "====================================================================\n";

mysqli_close($db);
?>
