<?php
/**
 * 견적서 하이브리드 데이터 모델 업그레이드
 *
 * 변경사항:
 * 1. quote_items 테이블에 qty_val, qty_unit, is_manual 필드 추가
 * 2. 기존 데이터 마이그레이션
 *
 * 실행: php upgrade_hybrid_model.php 또는 ?key=upgrade2026
 *
 * @since 2026-01-14
 */

// CLI 또는 인증된 접근만 허용
if (php_sapi_name() !== 'cli') {
    $key = $_GET['key'] ?? '';
    if ($key !== 'upgrade2026') {
        die('Unauthorized. Use ?key=upgrade2026');
    }
}

require_once __DIR__ . '/../../db.php';

echo "<pre>\n";
echo "=== 견적서 하이브리드 모델 업그레이드 ===\n\n";

// 1. quote_items 테이블 컬럼 추가 (MySQL 5.7 호환)
$columnsToAdd = [
    'qty_val' => "DECIMAL(10,2) DEFAULT NULL COMMENT '표준화된 수량값 (0.5, 1000 등)' AFTER quantity",
    'qty_unit' => "CHAR(1) DEFAULT 'E' COMMENT '단위 코드 (R=연, S=매, B=부, V=권, P=장, E=개)' AFTER qty_val",
    'qty_sheets' => "INT DEFAULT NULL COMMENT '연-매 환산 매수' AFTER qty_unit",
    'is_manual' => "TINYINT(1) DEFAULT 0 COMMENT '수동 입력 여부 (1=비규격 품목)' AFTER source_type"
];

// 기존 컬럼 확인
$existingColumns = [];
$columnsResult = mysqli_query($db, "DESCRIBE quote_items");
while ($col = mysqli_fetch_assoc($columnsResult)) {
    $existingColumns[] = $col['Field'];
}

foreach ($columnsToAdd as $colName => $colDef) {
    if (in_array($colName, $existingColumns)) {
        echo "⏭️ 이미 존재: {$colName}\n";
        continue;
    }

    $query = "ALTER TABLE quote_items ADD COLUMN {$colName} {$colDef}";
    if (mysqli_query($db, $query)) {
        echo "✅ 컬럼 추가 완료: {$colName}\n";
    } else {
        echo "❌ 오류 ({$colName}): " . mysqli_error($db) . "\n";
    }
}

// 2. 기존 데이터 마이그레이션
echo "\n=== 기존 데이터 마이그레이션 ===\n";

// 단위 텍스트 → 코드 매핑
$unitTextToCode = [
    '연' => 'R',
    '매' => 'S',
    '부' => 'B',
    '권' => 'V',
    '장' => 'P',
    '개' => 'E',
    '식' => 'E',
    '세트' => 'E',
    '박스' => 'E',
    '롤' => 'E',
    'm²' => 'E',
    '헤베' => 'E'
];

// qty_val/qty_unit이 NULL인 레코드 조회
$query = "SELECT id, quantity, unit, product_type, source_type FROM quote_items WHERE qty_val IS NULL";
$result = mysqli_query($db, $query);
$migratedCount = 0;

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $quantity = floatval($row['quantity']);
        $unitText = $row['unit'];
        $productType = $row['product_type'];
        $sourceType = $row['source_type'];

        // 단위 코드 결정
        $unitCode = $unitTextToCode[$unitText] ?? 'E';

        // is_manual 결정 (source_type이 'manual' 또는 'custom'이면 수동 입력)
        $isManual = in_array($sourceType, ['manual', 'custom']) ? 1 : 0;

        // 업데이트
        $updateQuery = "UPDATE quote_items SET qty_val = ?, qty_unit = ?, is_manual = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $updateQuery);
        mysqli_stmt_bind_param($stmt, "dsii", $quantity, $unitCode, $isManual, $id);

        if (mysqli_stmt_execute($stmt)) {
            $migratedCount++;
        }
        mysqli_stmt_close($stmt);
    }
}

echo "✅ 마이그레이션 완료: {$migratedCount}개 레코드\n";

// 3. 인덱스 추가 (MySQL 5.7 호환)
echo "\n=== 인덱스 추가 ===\n";

// 기존 인덱스 확인
$indexExists = false;
$indexResult = mysqli_query($db, "SHOW INDEX FROM quote_items WHERE Key_name = 'idx_is_manual'");
if ($indexResult && mysqli_num_rows($indexResult) > 0) {
    $indexExists = true;
}

if ($indexExists) {
    echo "⏭️ idx_is_manual 인덱스 이미 존재\n";
} else {
    $indexQuery = "CREATE INDEX idx_is_manual ON quote_items(is_manual)";
    if (mysqli_query($db, $indexQuery)) {
        echo "✅ idx_is_manual 인덱스 생성 완료\n";
    } else {
        echo "❌ 인덱스 오류: " . mysqli_error($db) . "\n";
    }
}

echo "\n=== 업그레이드 완료 ===\n";
echo "
[다음 단계]
1. QuoteManager.php에서 QuantityFormatter 사용하도록 수정
2. detail.php에서 QuantityFormatter::format() 호출
3. CSS 변수 적용 (SP Protocol)
";
echo "</pre>";

mysqli_close($db);
?>
