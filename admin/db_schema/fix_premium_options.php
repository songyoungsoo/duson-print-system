<?php
require_once __DIR__ . '/../../db.php';

try {
    echo "이관 중...\n\n";

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 명함 넘버링 (1개, 2개)
    echo "명함 넘버링/미싱/귀돌이/오시 이관...\n";

    $namecard_opts = ['넘버링' => ['1개' => 60000, '2개' => 75000],
                      '미싱' => ['1개' => 20000, '2개' => 35000],
                      '귀돌이' => ['전체' => 6000],
                      '오시' => ['1줄' => 20000, '2줄' => 20000, '3줄' => 35000]];

    foreach ($namecard_opts as $name => $variants) {
        foreach ($variants as $vname => $price) {
            $price_config = json_encode(['base_500' => $price, 'per_unit' => 12,
                                         'additional_fee' => ($name === '넘버링' && $vname === '2개' ||
                                                              $name === '미싱' && $vname === '2개' ||
                                                              $name === '오시' && $vname === '3줄') ? 15000 : 0]);
            $default = ($name === '넘버링' && $vname === '1개' ||
                       $name === '미싱' && $vname === '1개' ||
                       $name === '귀돌이' && $vname === '전체' ||
                       $name === '오시' && $vname === '1줄') ? TRUE : FALSE;

            mysqli_query($db, "
                INSERT INTO premium_option_variants (option_id, variant_name, pricing_config, is_default)
                SELECT id, '$vname', '$price_config', $default
                FROM premium_options WHERE product_type='namecard' AND option_name='$name'
                WHERE NOT EXISTS (
                    SELECT 1 FROM premium_option_variants
                    WHERE option_id = (SELECT id FROM premium_options WHERE product_type='namecard' AND option_name='$name')
                    AND variant_name='$vname'
                )
            ");
        }
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 상품권 박 (8종) - 명함과 동일 데이터
    echo "상품권 박 8종 이관...\n";

    mysqli_query($db, "
        INSERT INTO premium_options (product_type, option_name, option_type, sort_order)
        SELECT 'merchandisebond', option_name, option_type, sort_order
        FROM premium_options WHERE product_type='namecard' AND option_name='박'
        WHERE NOT EXISTS (SELECT 1 FROM premium_options WHERE product_type='merchandisebond' AND option_name='박')
    ");

    $mb_option_id = mysqli_insert_id($db);

    mysqli_query($db, "
        INSERT INTO premium_option_variants (option_id, variant_name, pricing_config, is_default) VALUES
        ($mb_option_id, '금박무광', '{\"base_500\":30000,\"per_unit\":12,\"additional_fee\":0}', TRUE),
        ($mb_option_id, '금박유광', '{\"base_500\":30000,\"per_unit\":12,\"additional_fee\":0}', FALSE),
        ($mb_option_id, '은박무광', '{\"base_500\":30000,\"per_unit\":12,\"additional_fee\":0}', FALSE),
        ($mb_option_id, '은박유광', '{\"base_500\":30000,\"per_unit\":12,\"additional_fee\":0}', FALSE),
        ($mb_option_id, '청박', '{\"base_500\":30000,\"per_unit\":12,\"additional_fee\":0}', FALSE),
        ($mb_option_id, '적박', '{\"base_500\":30000,\"per_unit\":12,\"additional_fee\":0}', FALSE),
        ($mb_option_id, '녹박', '{\"base_500\":30000,\"per_unit\":12,\"additional_fee\":0}', FALSE),
        ($mb_option_id, '먹박', '{\"base_500\":30000,\"per_unit\":12,\"additional_fee\":0}', FALSE)
    ");

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 상품권 넘버링/미싱/귀돌이/오시 - 명함과 동일 데이터
    echo "상품권 넘버링/미싱/귀돌이/오시 이관...\n";

    foreach ($namecard_opts as $name => $variants) {
        foreach ($variants as $vname => $price) {
            $price_config = json_encode(['base_500' => $price, 'per_unit' => 12,
                                         'additional_fee' => ($name === '넘버링' && $vname === '2개' ||
                                                              $name === '미싱' && $vname === '2개' ||
                                                              $name === '오시' && $vname === '3줄') ? 15000 : 0]);
            $default = ($name === '넴버링' && $vname === '1개' ||
                       $name === '미싱' && $vname === '1개' ||
                       $name === '귀돌이' && $vname === '전체' ||
                       $name === '오시' && $vname === '1줄') ? TRUE : FALSE;

            mysqli_query($db, "
                INSERT INTO premium_option_variants (option_id, variant_name, pricing_config, is_default)
                SELECT id, '$vname', '$price_config', $default
                FROM premium_options WHERE product_type='merchandisebond' AND option_name='$name'
                WHERE NOT EXISTS (
                    SELECT 1 FROM premium_option_variants
                    WHERE option_id = (SELECT id FROM premium_options WHERE product_type='merchandisebond' AND option_name='$name')
                    AND variant_name='$vname'
                )
            ");
        }
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 전단지 코팅 (4종)
    echo "전단지 데이터 이관...\n";

    $inserted_opts = ['코팅' => ['단면유광' => 80000, '양면유광' => 160000, '단면무광' => 90000, '양면무광' => 180000],
                      '접지' => ['2단' => 40000, '3단' => 40000, '병풍' => 70000, '대문' => 100000],
                      '오시' => ['1줄' => 30000, '2줄' => 30000, '3줄' => 45000]];

    foreach ($inserted_opts as $name => $variants) {
        foreach ($variants as $vname => $price) {
            $price_config = json_encode(['base_price' => $price, 'per_unit' => $price]);
            $default = (in_array($vname, ['단면유광', '2단', '1줄'])) ? TRUE : FALSE;

            mysqli_query($db, "
                INSERT INTO premium_option_variants (option_id, variant_name, pricing_config, is_default)
                SELECT id, '$vname', '$price_config', $default
                FROM premium_options WHERE product_type='inserted' AND option_name='$name'
                WHERE NOT EXISTS (
                    SELECT 1 FROM premium_option_variants
                    WHERE option_id = (SELECT id FROM premium_options WHERE product_type='inserted' AND option_name='$name')
                    AND variant_name='$vname'
                )
            ");
        }
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 포스터 데이터는 전단지와 동일
    echo "포스터 데이터 이관...\n";

    foreach ($inserted_opts as $name => $variants) {
        foreach ($variants as $vname => $price) {
            $price_config = json_encode(['base_price' => $price, 'per_unit' => $price]);
            $default = (in_array($vname, ['단면유광', '2단', '1줄'])) ? TRUE : FALSE;

            mysqli_query($db, "
                INSERT INTO premium_option_variants (option_id, variant_name, pricing_config, is_default)
                SELECT id, '$vname', '$price_config', $default
                FROM premium_options WHERE product_type='inserted' AND option_name='$name'
                WHERE NOT EXISTS (
                    SELECT 1 FROM premium_option_variants
                    WHERE option_id = (SELECT id FROM premium_options WHERE product_type='littleprint' AND option_name='$name')
                    AND variant_name='$vname'
                )
            ");
        }
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 카다록 데이터는 포스터와 동일
    echo "카다록 데이터 이관...\n";

    foreach ($inserted_opts as $name => $variants) {
        foreach ($variants as $vname => $price) {
            $price_config = json_encode(['base_price' => $price, 'per_unit' => $price]);
            $default = (in_array($vname, ['단면유광', '2단', '1줄'])) ? TRUE : FALSE;

            mysqli_query($db, "
                INSERT INTO premium_option_variants (option_id, variant_name, pricing_config, is_default)
                SELECT id, '$vname', '$price_config', $default
                FROM premium_options WHERE product_type='inserted' AND option_name='$name'
                WHERE NOT EXISTS (
                    SELECT 1 FROM premium_option_variants
                    WHERE option_id = (SELECT id FROM premium_options WHERE product_type='cadarok' AND option_name='$name')
                    AND variant_name='$vname'
                )
            ");
        }
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 봉투 데이터 이관
    echo "봉투 데이터 이관...\n";

    mysqli_query($db, "
        INSERT INTO premium_option_variants (option_id, variant_name, pricing_config, is_default) VALUES
        (1, '500매 이하', '{\"tier_1_max\":500,\"tier_1_price\":25000,\"tier_2_min\":501,\"tier_2_price\":40000}', TRUE),
        (1, '501~1000매', '{\"tier_1_max\":1000,\"tier_1_price\":40000,\"tier_2_max\":1000,\"tier_2_price\":40000}', FALSE),
        (1, '1000매 초과', '{\"tier_1_max\":1000,\"tier_1_price\":40000,\"tier_2_min\":1001,\"tier_2_price\":40000,\"per_unit\":40}', FALSE)
    ");

    echo "\n✅ 모든 데이터 이관 완료!\n\n";

    // 최종 확인
    $result = mysqli_query($db, "SELECT product_type, COUNT(*) AS count FROM premium_options GROUP BY product_type");
    echo "=== 제품별 옵션 수 ===\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- {$row['product_type']}: {$row['count']}개\n";
    }

    $result = mysqli_query($db, "SELECT product_type, option_name, COUNT(*) AS variant_count FROM premium_option_variants GROUP BY product_type, option_name ORDER BY product_type, option_name");
    echo "\n=== 옵션별 종류 수 ===\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "- {$row['product_type']}.{$row['option_name']}: {$row['variant_count']}종\n";
    }

    echo "\n✅ 완료!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "SQL State: " . $e->getCode() . "\n";
}

mysqli_close($db);
?>
