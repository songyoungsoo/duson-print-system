<?php
/**
 * 프리미엄 옵션 시스템 DB 설치 스크립트 (v2)
 * - premium_option_prices 테이블 제거 (미사용)
 * - product_type 버그 수정 (포스터/카다록이 inserted를 참조하던 문제)
 * - 깔끔한 데이터 구조
 *
 * Created: 2026-02-12
 * Updated: 2026-02-13
 * Branch: feature/premium-options-db
 */

require_once __DIR__ . '/../../db.php';

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "프리미엄 옵션 시스템 설치 시작\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// 1. 테이블 생성
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

mysqli_query($db, "DROP TABLE IF EXISTS premium_option_variants");
mysqli_query($db, "DROP TABLE IF EXISTS premium_option_prices");
mysqli_query($db, "DROP TABLE IF EXISTS premium_options");

$sql1 = "CREATE TABLE premium_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_type VARCHAR(50) NOT NULL,
    option_name VARCHAR(100) NOT NULL,
    option_type ENUM('premium', 'eco', 'other') DEFAULT 'premium',
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_product_option (product_type, option_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql2 = "CREATE TABLE premium_option_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    option_id INT NOT NULL,
    variant_name VARCHAR(100) NOT NULL,
    display_order INT DEFAULT 0,
    pricing_config JSON NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (option_id) REFERENCES premium_options(id) ON DELETE CASCADE,
    UNIQUE KEY uk_option_variant (option_id, variant_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!mysqli_query($db, $sql1)) {
    die("❌ premium_options 테이블 생성 실패: " . mysqli_error($db) . "\n");
}
if (!mysqli_query($db, $sql2)) {
    die("❌ premium_option_variants 테이블 생성 실패: " . mysqli_error($db) . "\n");
}

echo "✅ 테이블 2개 생성 완료\n\n";

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// 2. 헬퍼 함수
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

/**
 * 옵션 마스터 INSERT 후 ID 반환
 */
function insertOption($db, $product_type, $option_name, $sort_order) {
    $stmt = mysqli_prepare($db, "INSERT INTO premium_options (product_type, option_name, option_type, sort_order) VALUES (?, ?, 'premium', ?)");
    mysqli_stmt_bind_param($stmt, "ssi", $product_type, $option_name, $sort_order);
    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($db);
    mysqli_stmt_close($stmt);
    if (!$id) {
        echo "  ⚠️ 옵션 INSERT 실패: {$product_type}.{$option_name} - " . mysqli_error($db) . "\n";
    }
    return $id;
}

/**
 * variant INSERT (prepared statement)
 */
function insertVariant($db, $option_id, $variant_name, $pricing_config, $is_default = false, $display_order = 0) {
    $json = json_encode($pricing_config, JSON_UNESCAPED_UNICODE);
    $default_val = $is_default ? 1 : 0;
    $stmt = mysqli_prepare($db, "INSERT INTO premium_option_variants (option_id, variant_name, pricing_config, is_default, display_order) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issii", $option_id, $variant_name, $json, $default_val, $display_order);
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        echo "  ⚠️ Variant INSERT 실패: {$variant_name} - " . mysqli_error($db) . "\n";
    }
    mysqli_stmt_close($stmt);
    return $result;
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// 3. 시드 데이터 정의
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

// === 패턴 A: base_perunit (명함, 상품권) ===
// 공식: qty <= 500 → base_500 / qty > 500 → base_500 + (qty-500) * per_unit + additional_fee

$pattern_a_options = [
    '박' => [
        'sort_order' => 1,
        'variants' => [
            ['금박무광',  ['base_500' => 30000, 'per_unit' => 12, 'additional_fee' => 0], true, 0],
            ['금박유광',  ['base_500' => 30000, 'per_unit' => 12, 'additional_fee' => 0], false, 1],
            ['은박무광',  ['base_500' => 30000, 'per_unit' => 12, 'additional_fee' => 0], false, 2],
            ['은박유광',  ['base_500' => 30000, 'per_unit' => 12, 'additional_fee' => 0], false, 3],
            ['청박',      ['base_500' => 30000, 'per_unit' => 12, 'additional_fee' => 0], false, 4],
            ['적박',      ['base_500' => 30000, 'per_unit' => 12, 'additional_fee' => 0], false, 5],
            ['녹박',      ['base_500' => 30000, 'per_unit' => 12, 'additional_fee' => 0], false, 6],
            ['먹박',      ['base_500' => 30000, 'per_unit' => 12, 'additional_fee' => 0], false, 7],
        ]
    ],
    '넘버링' => [
        'sort_order' => 2,
        'variants' => [
            ['1개', ['base_500' => 60000, 'per_unit' => 12, 'additional_fee' => 0], true, 0],
            ['2개', ['base_500' => 75000, 'per_unit' => 12, 'additional_fee' => 15000], false, 1],
        ]
    ],
    '미싱' => [
        'sort_order' => 3,
        'variants' => [
            ['1개', ['base_500' => 20000, 'per_unit' => 25, 'additional_fee' => 0], true, 0],
            ['2개', ['base_500' => 35000, 'per_unit' => 25, 'additional_fee' => 15000], false, 1],
        ]
    ],
    '귀돌이' => [
        'sort_order' => 4,
        'variants' => [
            ['전체', ['base_500' => 6000, 'per_unit' => 12, 'additional_fee' => 0], true, 0],
        ]
    ],
    '오시' => [
        'sort_order' => 5,
        'variants' => [
            ['1줄', ['base_500' => 20000, 'per_unit' => 25, 'additional_fee' => 0], true, 0],
            ['2줄', ['base_500' => 20000, 'per_unit' => 25, 'additional_fee' => 0], false, 1],
            ['3줄', ['base_500' => 35000, 'per_unit' => 25, 'additional_fee' => 15000], false, 2],
        ]
    ],
];

// === 패턴 B: multiplier (전단지, 포스터, 카다록) ===
// 공식: base_price * max(qty/unit_size, 1)

$pattern_b_options = [
    '코팅' => [
        'sort_order' => 1,
        'variants' => [
            ['단면유광', ['base_price' => 80000, 'per_unit' => 80000], true, 0],
            ['양면유광', ['base_price' => 160000, 'per_unit' => 160000], false, 1],
            ['단면무광', ['base_price' => 90000, 'per_unit' => 90000], false, 2],
            ['양면무광', ['base_price' => 180000, 'per_unit' => 180000], false, 3],
        ]
    ],
    '접지' => [
        'sort_order' => 2,
        'variants' => [
            ['2단', ['base_price' => 40000, 'per_unit' => 40000], true, 0],
            ['3단', ['base_price' => 40000, 'per_unit' => 40000], false, 1],
            ['병풍', ['base_price' => 70000, 'per_unit' => 70000], false, 2],
            ['대문', ['base_price' => 100000, 'per_unit' => 100000], false, 3],
        ]
    ],
    '오시' => [
        'sort_order' => 3,
        'variants' => [
            ['1줄', ['base_price' => 30000, 'per_unit' => 30000], true, 0],
            ['2줄', ['base_price' => 30000, 'per_unit' => 30000], false, 1],
            ['3줄', ['base_price' => 45000, 'per_unit' => 45000], false, 2],
        ]
    ],
];

// === 패턴 C: tiered (봉투) ===
// 공식: 수량 구간별 고정가 or 매당 40원

$pattern_c_options = [
    '양면테이프' => [
        'sort_order' => 1,
        'variants' => [
            ['기본', ['tiers' => [
                ['max_qty' => 500,  'price' => 25000],
                ['max_qty' => 1000, 'price' => 40000],
            ], 'over_1000_per_unit' => 40], true, 0],
        ]
    ],
];

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// 4. 데이터 이관 실행
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

/**
 * 옵션 세트를 특정 product_type에 INSERT
 */
function seedOptions($db, $product_type, $options) {
    $count = 0;
    foreach ($options as $option_name => $config) {
        $option_id = insertOption($db, $product_type, $option_name, $config['sort_order']);
        if (!$option_id) continue;

        foreach ($config['variants'] as $variant) {
            list($variant_name, $pricing_config, $is_default, $display_order) = $variant;
            insertVariant($db, $option_id, $variant_name, $pricing_config, $is_default, $display_order);
            $count++;
        }
    }
    return $count;
}

// 명함 (패턴 A)
$count = seedOptions($db, 'namecard', $pattern_a_options);
echo "✅ 명함(namecard): {$count}개 variant\n";

// 상품권 (패턴 A - 명함과 동일)
$count = seedOptions($db, 'merchandisebond', $pattern_a_options);
echo "✅ 상품권(merchandisebond): {$count}개 variant\n";

// 전단지 (패턴 B)
$count = seedOptions($db, 'inserted', $pattern_b_options);
echo "✅ 전단지(inserted): {$count}개 variant\n";

// 포스터 (패턴 B - 전단지와 동일)
$count = seedOptions($db, 'littleprint', $pattern_b_options);
echo "✅ 포스터(littleprint): {$count}개 variant\n";

// 카다록 (패턴 B - 전단지와 동일)
$count = seedOptions($db, 'cadarok', $pattern_b_options);
echo "✅ 카다록(cadarok): {$count}개 variant\n";

// 봉투 (패턴 C)
$count = seedOptions($db, 'envelope', $pattern_c_options);
echo "✅ 봉투(envelope): {$count}개 variant\n";

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// 5. 검증
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "설치 검증\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// 제품별 옵션 수
$product_types = ['namecard', 'merchandisebond', 'inserted', 'littleprint', 'cadarok', 'envelope'];
$total_options = 0;
$total_variants = 0;

foreach ($product_types as $type) {
    $r1 = mysqli_query($db, "SELECT COUNT(*) AS cnt FROM premium_options WHERE product_type='{$type}'");
    $opt_count = mysqli_fetch_assoc($r1)['cnt'];

    $r2 = mysqli_query($db, "
        SELECT COUNT(*) AS cnt FROM premium_option_variants v
        JOIN premium_options o ON v.option_id = o.id
        WHERE o.product_type = '{$type}'
    ");
    $var_count = mysqli_fetch_assoc($r2)['cnt'];

    echo "  {$type}: 옵션 {$opt_count}개, variant {$var_count}개\n";
    $total_options += $opt_count;
    $total_variants += $var_count;
}

echo "\n  총계: 옵션 {$total_options}개, variant {$total_variants}개\n";

// FK 무결성 검증
$r3 = mysqli_query($db, "
    SELECT v.id, v.variant_name, v.option_id
    FROM premium_option_variants v
    LEFT JOIN premium_options o ON v.option_id = o.id
    WHERE o.id IS NULL
");
$orphans = mysqli_num_rows($r3);
if ($orphans > 0) {
    echo "\n  ❌ FK 무결성 오류: 고아 variant {$orphans}개 발견!\n";
} else {
    echo "\n  ✅ FK 무결성 검증 통과\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ 프리미엄 옵션 시스템 설치 완료!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

mysqli_close($db);
