<?php
/**
 * Grand Design Migration CLI
 *
 * Usage: php migrate_cli.php [limit] [offset]
 * Example: php migrate_cli.php 1000 0
 */

if (php_sapi_name() !== 'cli') {
    die("CLI only\n");
}

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/DataAdapter.php';
require_once __DIR__ . '/../../includes/QuantityFormatter.php';
require_once __DIR__ . '/../../includes/ProductSpecFormatter.php';

$limit = intval($argv[1] ?? 1000);
$offset = intval($argv[2] ?? 0);

/**
 * Type 필드(한글) → product_type 코드 매핑
 */
function detectProductType($legacy) {
    // 1. product_type이 이미 있으면 사용
    if (!empty($legacy['product_type'])) {
        return $legacy['product_type'];
    }

    // 2. Type 필드에서 한글명으로 매핑
    $type = $legacy['Type'] ?? '';
    $typeLower = mb_strtolower($type);

    // 전단지/리플렛 계열
    if (preg_match('/(전단|리플렛|inserted|leaflet)/ui', $type)) {
        return 'inserted';
    }

    // 스티커 계열 (자석스티커 제외)
    if (preg_match('/(자석스티커|종이자석|전체자석|msticker)/ui', $type)) {
        return 'msticker';
    }
    if (preg_match('/(스티커|스티카|sticker|유포지|투명|은데드롱|비코팅|강접|아트지|금지|원형|띠지|라벨|도무송)/ui', $type)) {
        return 'sticker_new';
    }

    // 명함
    if (preg_match('/(명함|namecard)/ui', $type)) {
        return 'namecard';
    }

    // 봉투
    if (preg_match('/(봉투|envelope)/ui', $type)) {
        return 'envelope';
    }

    // 카다록
    if (preg_match('/(카다록|카다로그|cadarok)/ui', $type)) {
        return 'cadarok';
    }

    // NCR/양식지
    if (preg_match('/(ncr|양식지|양식|빌지|ncrflambeau)/ui', $type)) {
        return 'ncrflambeau';
    }

    // 포스터
    if (preg_match('/(포스터|littleprint|poster)/ui', $type)) {
        return 'littleprint';
    }

    // 상품권/쿠폰/할인권
    if (preg_match('/(상품권|쿠폰|할인권|merchandisebond|교환권|입장권|티켓|식권|회원권)/ui', $type)) {
        return 'merchandisebond';
    }

    // 기타 인쇄물 (전단지로 처리)
    if (preg_match('/(품질보증서|설명서|안내문|메뉴판|주보|책자|팜플렛|브로셔|보증서|표지|내지|인쇄물)/ui', $type)) {
        return 'inserted';
    }

    // 3. ImgFolder에서 추론
    $imgFolder = $legacy['ImgFolder'] ?? '';
    if (strpos($imgFolder, 'sticker') !== false) return 'sticker_new';
    if (strpos($imgFolder, 'inserted') !== false) return 'inserted';
    if (strpos($imgFolder, 'namecard') !== false) return 'namecard';
    if (strpos($imgFolder, 'envelope') !== false) return 'envelope';
    if (strpos($imgFolder, 'msticker') !== false) return 'msticker';

    // 4. 빈 Type은 스킵
    if (empty($type)) {
        return null;
    }

    // 5. 기타 (Type이 있지만 매핑 안 됨)
    return 'other';
}

echo "=== Grand Design Migration ===\n";
echo "Limit: {$limit}, Offset: {$offset}\n\n";

// 레거시 데이터 범위 확인
$result = mysqli_query($db, "SELECT MIN(no) as min_no, MAX(no) as max_no, COUNT(*) as total FROM mlangorder_printauto");
$stats = mysqli_fetch_assoc($result);
echo "Legacy: {$stats['total']} records (no: {$stats['min_no']} ~ {$stats['max_no']})\n";

// 이미 마이그레이션된 건수
$result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM order_items WHERE legacy_no IS NOT NULL");
$migrated = mysqli_fetch_assoc($result)['cnt'];
echo "Already migrated: {$migrated}\n\n";

// 마이그레이션 대상 조회
// $startNo, $endNo가 지정되면 해당 범위만 처리
$startNo = intval($argv[3] ?? 59981);  // 기본값: 교정이미지 있는 최소 번호
$endNo = intval($argv[4] ?? 84405);    // 기본값: 교정이미지 있는 최대 번호
$whereClause = "no NOT IN (SELECT legacy_no FROM order_items WHERE legacy_no IS NOT NULL)";
$whereClause .= " AND no >= {$startNo} AND no <= {$endNo}";
$query = "SELECT * FROM mlangorder_printauto
          WHERE {$whereClause}
          ORDER BY no ASC
          LIMIT {$limit} OFFSET {$offset}";
$result = mysqli_query($db, $query);

$processed = 0;
$success = 0;
$failed = 0;
$errors = [];

echo "Processing...\n";

while ($legacy = mysqli_fetch_assoc($result)) {
    $processed++;
    $legacyNo = $legacy['no'];

    try {
        // 제품 타입 결정 (한글 Type 필드 매핑 사용)
        $productType = detectProductType($legacy);

        if (empty($productType)) {
            throw new Exception("제품 타입 없음 (Type=" . ($legacy['Type'] ?? 'NULL') . ")");
        }

        if ($productType === 'other') {
            throw new Exception("매핑 안 됨 (Type=" . $legacy['Type'] . ")");
        }

        // 정규화
        $normalized = DataAdapter::legacyToNormalized($legacy, $productType);

        if (empty($normalized['qty_value']) || $normalized['qty_value'] <= 0) {
            throw new Exception("수량 값 없음 (qty_value={$normalized['qty_value']})");
        }

        // 트랜잭션
        mysqli_begin_transaction($db);

        try {
            // 1. orders 테이블에 먼저 삽입 (각 레거시 레코드당 하나의 주문)
            $orderSql = "INSERT INTO orders (
                legacy_no, customer_name, customer_email, customer_phone, customer_mobile,
                shipping_postcode, shipping_address, shipping_detail,
                total_supply, total_vat, total_amount,
                order_date, data_version
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 3)";

            $orderStmt = mysqli_prepare($db, $orderSql);
            if (!$orderStmt) {
                throw new Exception("Order Prepare: " . mysqli_error($db));
            }

            $o_legacy_no = $legacyNo;
            $o_name = $legacy['name'] ?? '';
            $o_email = $legacy['email'] ?? '';
            $o_phone = $legacy['phone'] ?? '';
            $o_mobile = $legacy['Hendphone'] ?? '';
            $o_postcode = $legacy['zip'] ?? '';
            $o_address = $legacy['zip1'] ?? '';
            $o_detail = $legacy['zip2'] ?? '';
            $o_supply = intval($normalized['price_supply'] ?? 0);
            $o_vat = intval($normalized['price_vat'] ?? 0);
            $o_total = $o_vat > 0 ? $o_vat : $o_supply;
            $o_date = $legacy['date'] ?? date('Y-m-d H:i:s');

            mysqli_stmt_bind_param($orderStmt, "isssssssiiis",
                $o_legacy_no, $o_name, $o_email, $o_phone, $o_mobile,
                $o_postcode, $o_address, $o_detail,
                $o_supply, $o_vat, $o_total, $o_date
            );

            if (!mysqli_stmt_execute($orderStmt)) {
                throw new Exception("Order Execute: " . mysqli_stmt_error($orderStmt));
            }

            $orderId = mysqli_insert_id($db);
            mysqli_stmt_close($orderStmt);

            // 2. order_items 삽입
            $sql = "INSERT INTO order_items (
                order_id, legacy_no, product_type, product_type_display,
                spec_type, spec_material, spec_size, spec_sides, spec_design,
                qty_value, qty_unit_code, qty_sheets,
                price_supply, price_vat, price_unit,
                img_folder, thing_cate, ordertype, work_memo, legacy_data
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($db, $sql);
            if (!$stmt) {
                throw new Exception("Prepare: " . mysqli_error($db));
            }

            // 변수 할당 (bind_param은 참조 필요)
            $v_order_id = $orderId;
            $v_legacy_no = $legacyNo;
            $v_product_type = $normalized['product_type'] ?? '';
            $v_product_type_display = $normalized['product_type_display'] ?? '';
            $v_spec_type = $normalized['spec_type'] ?? '';
            $v_spec_material = $normalized['spec_material'] ?? '';
            $v_spec_size = $normalized['spec_size'] ?? '';
            $v_spec_sides = $normalized['spec_sides'] ?? '';
            $v_spec_design = $normalized['spec_design'] ?? '';
            $v_qty_value = floatval($normalized['qty_value'] ?? 0);
            $v_qty_unit_code = $normalized['qty_unit_code'] ?? 'E';
            $v_qty_sheets = $normalized['qty_sheets'] !== null ? intval($normalized['qty_sheets']) : null;
            $v_price_supply = intval($normalized['price_supply'] ?? 0);
            $v_price_vat = intval($normalized['price_vat'] ?? 0);
            $v_price_unit = $normalized['price_unit'] !== null ? intval($normalized['price_unit']) : null;
            $v_img_folder = $normalized['img_folder'] ?? '';
            $v_thing_cate = $normalized['thing_cate'] ?? '';
            $v_ordertype = $normalized['ordertype'] ?? '';
            $v_work_memo = $normalized['work_memo'] ?? '';
            $v_legacy_data = $normalized['legacy_data'] ?? '{}';

            // 타입: i(order_id) + i(legacy_no) + s×7(specs) + d(qty_value) + s(unit_code) + i×4(qty,prices) + s×5(files,etc) = 20개
            mysqli_stmt_bind_param($stmt, "iisssssssdsiiiiissss",
                $v_order_id,
                $v_legacy_no,
                $v_product_type,
                $v_product_type_display,
                $v_spec_type,
                $v_spec_material,
                $v_spec_size,
                $v_spec_sides,
                $v_spec_design,
                $v_qty_value,
                $v_qty_unit_code,
                $v_qty_sheets,
                $v_price_supply,
                $v_price_vat,
                $v_price_unit,
                $v_img_folder,
                $v_thing_cate,
                $v_ordertype,
                $v_work_memo,
                $v_legacy_data
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Execute: " . mysqli_stmt_error($stmt));
            }

            $itemId = mysqli_insert_id($db);
            mysqli_stmt_close($stmt);

            // 마이그레이션 로그
            $logSql = "INSERT INTO migration_log (legacy_no, new_order_id, new_item_id, status) VALUES (?, ?, ?, 'success')";
            $logStmt = mysqli_prepare($db, $logSql);
            mysqli_stmt_bind_param($logStmt, "iii", $legacyNo, $orderId, $itemId);
            mysqli_stmt_execute($logStmt);
            mysqli_stmt_close($logStmt);

            mysqli_commit($db);
            $success++;

            if ($success % 100 === 0) {
                echo "  ✓ {$success} records migrated...\n";
            }

        } catch (Exception $e) {
            mysqli_rollback($db);
            throw $e;
        }

    } catch (Exception $e) {
        $failed++;
        $errors[] = "No {$legacyNo}: " . $e->getMessage();

        // 실패 로그
        $errMsg = mysqli_real_escape_string($db, $e->getMessage());
        mysqli_query($db, "INSERT INTO migration_log (legacy_no, status, error_message) VALUES ({$legacyNo}, 'failed', '{$errMsg}')");
    }
}

echo "\n=== 결과 ===\n";
echo "처리: {$processed}\n";
echo "성공: {$success}\n";
echo "실패: {$failed}\n";

if (!empty($errors)) {
    echo "\n오류 (최대 20개):\n";
    foreach (array_slice($errors, 0, 20) as $error) {
        echo "  - {$error}\n";
    }
}

// 최종 통계
$result = mysqli_query($db, "SELECT COUNT(*) as cnt FROM order_items WHERE legacy_no IS NOT NULL");
$totalMigrated = mysqli_fetch_assoc($result)['cnt'];
echo "\n총 마이그레이션 완료: {$totalMigrated}건\n";

// 샘플 출력
echo "\n=== 샘플 데이터 (최근 5건) ===\n";
$sampleQuery = "SELECT item_id, legacy_no, product_type, qty_value, qty_unit_code, qty_sheets, price_vat
                FROM order_items ORDER BY item_id DESC LIMIT 5";
$sampleResult = mysqli_query($db, $sampleQuery);
while ($row = mysqli_fetch_assoc($sampleResult)) {
    $display = QuantityFormatter::format(
        floatval($row['qty_value']),
        $row['qty_unit_code'],
        $row['qty_sheets']
    );
    echo sprintf("  #%d (legacy:%d) %s: %s = %s\n",
        $row['item_id'],
        $row['legacy_no'],
        $row['product_type'],
        $display,
        number_format($row['price_vat']) . '원'
    );
}
