<?php
/**
 * mlangorder_printauto 테이블에 추가옵션 컬럼 추가
 * shop_temp 테이블에는 이미 컬럼이 있지만, mlangorder_printauto 테이블에는 없음
 */

// 데이터베이스 연결
include __DIR__ . '/../db.php';

if (!$db) {
    die("데이터베이스 연결 실패\n");
}

echo "====================================================================\n";
echo "mlangorder_printauto 테이블에 추가옵션 컬럼 추가\n";
echo "====================================================================\n\n";

// 추가할 컬럼 정의
$columns_to_add = [
    'coating_enabled' => "TINYINT(1) DEFAULT 0 COMMENT '코팅 사용 여부'",
    'coating_type' => "VARCHAR(50) NULL COMMENT '코팅 종류 (single, double, single_matte, double_matte)'",
    'coating_price' => "INT DEFAULT 0 COMMENT '코팅 가격'",
    'folding_enabled' => "TINYINT(1) DEFAULT 0 COMMENT '접지 사용 여부'",
    'folding_type' => "VARCHAR(50) NULL COMMENT '접지 종류 (2fold, 3fold, 4fold)'",
    'folding_price' => "INT DEFAULT 0 COMMENT '접지 가격'",
    'creasing_enabled' => "TINYINT(1) DEFAULT 0 COMMENT '오시 사용 여부'",
    'creasing_lines' => "INT DEFAULT 0 COMMENT '오시 줄 수'",
    'creasing_price' => "INT DEFAULT 0 COMMENT '오시 가격'",
    'additional_options_total' => "INT DEFAULT 0 COMMENT '추가옵션 총액'"
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
echo "🔍 추가옵션 컬럼 확인:\n";
echo "--------------------------------------------------------------------\n";

$verify_query = "SHOW COLUMNS FROM mlangorder_printauto WHERE
    Field LIKE '%coating%' OR
    Field LIKE '%folding%' OR
    Field LIKE '%creasing%' OR
    Field LIKE '%additional%'";

$verify_result = mysqli_query($db, $verify_query);

if (mysqli_num_rows($verify_result) > 0) {
    while ($row = mysqli_fetch_assoc($verify_result)) {
        echo "  " . $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "  ⚠️ 추가옵션 컬럼을 찾을 수 없습니다.\n";
}

echo "====================================================================\n";

mysqli_close($db);
?>
