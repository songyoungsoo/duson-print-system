<?php
/**
 * quote_items 테이블 마이그레이션 스크립트
 * 날짜: 2025-12-18
 * 목적: 기존 68개 레코드의 product_type, unit, formatted_display 업데이트
 */

require_once __DIR__ . '/../../../db.php';

echo "=== quote_items 마이그레이션 시작 ===\n\n";

// Step 1: 백업 확인
$backup_check = mysqli_query($db, "SHOW TABLES LIKE 'quote_items_backup%'");
$backup_count = mysqli_num_rows($backup_check);
echo "Step 1: 백업 테이블 확인 - {$backup_count}개 발견\n";

if ($backup_count == 0) {
    die("❌ 백업 테이블이 없습니다! ALTER TABLE 전에 백업을 생성하세요.\n");
}

// Step 2: 기존 레코드 조회
$query = "SELECT id, product_name, specification, unit FROM quote_items WHERE product_type IS NULL OR product_type = ''";
$result = mysqli_query($db, $query);
$total_records = mysqli_num_rows($result);

echo "Step 2: 업데이트 대상 레코드 - {$total_records}개\n\n";

if ($total_records == 0) {
    echo "✅ 모든 레코드가 이미 마이그레이션 되었습니다.\n";
    exit(0);
}

// Step 3: product_name 기반 product_type 매핑
$product_type_mapping = [
    '전단지' => 'inserted',
    '리플렛' => 'leaflet',
    '명함' => 'namecard',
    '스티커' => 'sticker',
    '투명스티커' => 'sticker',
    '유포지스티커' => 'sticker',
    '봉투' => 'envelope',
    '자석스티커' => 'msticker',
    '카다록' => 'cadarok',
    '카탈로그' => 'cadarok',
    '포스터' => 'littleprint',
    'LittlePrint' => 'littleprint',
    '상품권' => 'merchandisebond',
    '쿠폰' => 'merchandisebond',
    '양식지' => 'ncrflambeau',
    'NCR' => 'ncrflambeau'
];

// Step 4: 각 레코드 업데이트
$updated_count = 0;
$error_count = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
    $product_name = $row['product_name'];
    $specification = $row['specification'];
    $current_unit = $row['unit'];

    // product_type 추정
    $product_type = 'general'; // 기본값
    foreach ($product_type_mapping as $keyword => $type) {
        if (stripos($product_name, $keyword) !== false) {
            $product_type = $type;
            break;
        }
    }

    // unit 설정 (기존 unit이 '개'이거나 비어있으면 product_type 기반 설정)
    $unit = $current_unit;
    if ($current_unit == '개' || empty($current_unit)) {
        switch ($product_type) {
            case 'inserted':
            case 'leaflet':
                $unit = '연';
                break;
            case 'cadarok':
                $unit = '부';
                break;
            case 'ncrflambeau':
                $unit = '권';
                break;
            default:
                $unit = '매';
        }
    }

    // formatted_display 생성 (product_name + specification 결합)
    $formatted_display = $product_name;
    if (!empty($specification)) {
        $formatted_display .= "\n" . $specification;
    }

    // UPDATE 쿼리 실행
    $update_query = "UPDATE quote_items SET
        product_type = ?,
        unit = ?,
        formatted_display = ?
        WHERE id = ?";

    $stmt = mysqli_prepare($db, $update_query);
    mysqli_stmt_bind_param($stmt, "sssi", $product_type, $unit, $formatted_display, $id);

    if (mysqli_stmt_execute($stmt)) {
        $updated_count++;
        echo "✅ ID {$id}: {$product_name} → {$product_type} ({$unit})\n";
    } else {
        $error_count++;
        echo "❌ ID {$id}: 업데이트 실패 - " . mysqli_error($db) . "\n";
    }

    mysqli_stmt_close($stmt);
}

// Step 5: 결과 요약
echo "\n=== 마이그레이션 완료 ===\n";
echo "총 대상: {$total_records}개\n";
echo "성공: {$updated_count}개\n";
echo "실패: {$error_count}개\n";

// Step 6: 검증 쿼리
echo "\n=== 검증 ===\n";
$verification_query = "SELECT product_type, COUNT(*) as count FROM quote_items GROUP BY product_type";
$verification_result = mysqli_query($db, $verification_query);

echo "제품 타입별 레코드 수:\n";
while ($row = mysqli_fetch_assoc($verification_result)) {
    $type = $row['product_type'] ?: '(NULL)';
    $count = $row['count'];
    echo "  {$type}: {$count}개\n";
}

// Step 7: unit 분포 확인
echo "\n단위별 레코드 수:\n";
$unit_query = "SELECT unit, COUNT(*) as count FROM quote_items GROUP BY unit";
$unit_result = mysqli_query($db, $unit_query);

while ($row = mysqli_fetch_assoc($unit_result)) {
    $unit = $row['unit'] ?: '(NULL)';
    $count = $row['count'];
    echo "  {$unit}: {$count}개\n";
}

echo "\n✅ 마이그레이션이 성공적으로 완료되었습니다!\n";

mysqli_close($db);
?>
