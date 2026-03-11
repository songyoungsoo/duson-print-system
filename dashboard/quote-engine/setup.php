<?php
/**
 * Quote Engine — DB 테이블 초기 설정
 * 경로: /dashboard/quote-engine/setup.php
 *
 * 실행: 브라우저에서 1회 접속 또는 CLI `php setup.php`
 * 모든 테이블은 CREATE TABLE IF NOT EXISTS → 멱등(idempotent)
 *
 * 테이블 4개:
 *   qe_customers   — 거래처/고객
 *   qe_quotes      — 견적서/거래명세서 마스터
 *   qe_items       — 품목 라인아이템
 *   qe_templates   — 품목 템플릿 (2차)
 */

// CLI 실행 감지
$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    require_once __DIR__ . '/../../admin/includes/admin_auth.php';
    requireAdminAuth();
}

require_once __DIR__ . '/../../db.php';

// ─── 테이블 정의 ────────────────────────────────────────────────

$tables = [

    'qe_customers' => "
        CREATE TABLE IF NOT EXISTS `qe_customers` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `company` VARCHAR(100) DEFAULT NULL COMMENT '회사명/상호',
            `name` VARCHAR(50) NOT NULL COMMENT '담당자명',
            `phone` VARCHAR(20) DEFAULT NULL COMMENT '전화번호',
            `email` VARCHAR(100) DEFAULT NULL COMMENT '이메일',
            `address` TEXT DEFAULT NULL COMMENT '주소',
            `business_number` VARCHAR(20) DEFAULT NULL COMMENT '사업자등록번호',
            `memo` TEXT DEFAULT NULL COMMENT '메모',
            `use_count` INT NOT NULL DEFAULT 0 COMMENT '사용 횟수',
            `last_used_at` DATETIME DEFAULT NULL COMMENT '마지막 사용일시',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='견적엔진 거래처'
    ",

    'qe_quotes' => "
        CREATE TABLE IF NOT EXISTS `qe_quotes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `quote_no` VARCHAR(30) NOT NULL UNIQUE COMMENT '견적번호 QE-/TX-',
            `doc_type` ENUM('quotation','transaction') NOT NULL DEFAULT 'quotation' COMMENT '문서유형',
            `customer_id` INT DEFAULT NULL COMMENT '거래처 FK (nullable)',
            `customer_company` VARCHAR(100) DEFAULT NULL,
            `customer_name` VARCHAR(50) DEFAULT NULL,
            `customer_phone` VARCHAR(20) DEFAULT NULL,
            `customer_email` VARCHAR(100) DEFAULT NULL,
            `customer_address` TEXT DEFAULT NULL,
            `customer_biz_no` VARCHAR(20) DEFAULT NULL COMMENT '사업자등록번호',
            `supply_total` INT NOT NULL DEFAULT 0 COMMENT '공급가액 합계',
            `vat_total` INT NOT NULL DEFAULT 0 COMMENT 'VAT 합계',
            `discount_amount` INT NOT NULL DEFAULT 0 COMMENT '할인금액',
            `discount_reason` VARCHAR(100) DEFAULT NULL COMMENT '할인사유',
            `grand_total` INT NOT NULL DEFAULT 0 COMMENT '최종합계(VAT포함-할인)',
            `valid_days` INT NOT NULL DEFAULT 7 COMMENT '유효기간(일)',
            `valid_until` DATE DEFAULT NULL COMMENT '유효기한',
            `payment_terms` VARCHAR(100) DEFAULT '발행일로부터 7일' COMMENT '결제조건',
            `customer_memo` TEXT DEFAULT NULL COMMENT '고객전달 메모',
            `admin_memo` TEXT DEFAULT NULL COMMENT '관리자 내부 메모',
            `status` ENUM('draft','completed','sent','expired') NOT NULL DEFAULT 'draft',
            `sent_at` DATETIME DEFAULT NULL COMMENT '발송일시',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_quote_no` (`quote_no`),
            INDEX `idx_customer_id` (`customer_id`),
            INDEX `idx_status` (`status`),
            INDEX `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='견적엔진 견적서/거래명세서'
    ",

    'qe_items' => "
        CREATE TABLE IF NOT EXISTS `qe_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `quote_id` INT NOT NULL COMMENT '견적서 FK',
            `item_no` INT NOT NULL COMMENT '품목 순번',
            `item_type` ENUM('product','manual','extra') NOT NULL COMMENT '품목유형',
            `product_type` VARCHAR(30) DEFAULT NULL COMMENT '제품코드 (inserted, namecard …)',
            `product_name` VARCHAR(200) NOT NULL COMMENT '품명',
            `specification` TEXT DEFAULT NULL COMMENT '규격/사양',
            `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1.00 COMMENT '수량',
            `unit` VARCHAR(10) NOT NULL DEFAULT '개' COMMENT '단위',
            `unit_price` INT NOT NULL DEFAULT 0 COMMENT '단가',
            `supply_price` INT NOT NULL DEFAULT 0 COMMENT '공급가액',
            `extra_category` VARCHAR(20) DEFAULT NULL COMMENT '부가품목 카테고리 (배송,후가공 등)',
            `note` TEXT DEFAULT NULL COMMENT '비고',
            `source_data` JSON DEFAULT NULL COMMENT '원본 계산 파라미터 스냅샷',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_quote` (`quote_id`),
            CONSTRAINT `fk_qe_items_quote` FOREIGN KEY (`quote_id`)
                REFERENCES `qe_quotes`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='견적엔진 품목 라인아이템'
    ",

    'qe_templates' => "
        CREATE TABLE IF NOT EXISTS `qe_templates` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL COMMENT '템플릿명',
            `items` JSON NOT NULL COMMENT '품목 데이터 배열',
            `use_count` INT NOT NULL DEFAULT 0 COMMENT '사용 횟수',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='견적엔진 품목 템플릿'
    ",
];

// ─── 실행 ──────────────────────────────────────────────────────

$results = [];

foreach ($tables as $name => $sql) {
    $ok = mysqli_query($db, $sql);
    $results[$name] = $ok ? 'OK' : mysqli_error($db);
}

// ─── 출력 ──────────────────────────────────────────────────────

if ($isCli) {
    // CLI 출력
    echo "\n=== Quote Engine DB Setup ===\n\n";
    foreach ($results as $name => $status) {
        $icon = ($status === 'OK') ? '✅' : '❌';
        echo "  {$icon} {$name}: {$status}\n";
    }
    $allOk = !in_array(false, array_map(fn($s) => $s === 'OK', $results));
    echo "\n" . ($allOk ? '모든 테이블이 정상 생성되었습니다.' : '일부 테이블에서 오류가 발생했습니다.') . "\n\n";
    exit($allOk ? 0 : 1);
}

// 웹 출력
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Quote Engine — DB Setup</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; max-width: 600px; margin: 40px auto; padding: 0 20px; color: #1a1a1a; }
        h1 { font-size: 1.4rem; border-bottom: 2px solid #2563eb; padding-bottom: 8px; }
        .table-row { display: flex; align-items: center; padding: 10px 12px; border-radius: 6px; margin-bottom: 6px; }
        .table-row.ok { background: #ecfdf5; }
        .table-row.err { background: #fef2f2; }
        .icon { font-size: 1.2rem; margin-right: 10px; }
        .name { font-weight: 600; flex: 1; }
        .status { font-size: 0.85rem; color: #666; }
        .status.err { color: #dc2626; }
        .summary { margin-top: 20px; padding: 12px 16px; border-radius: 6px; font-weight: 600; }
        .summary.ok { background: #dbeafe; color: #1e40af; }
        .summary.err { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <h1>🔧 Quote Engine — DB Setup</h1>
    <?php
    $allOk = true;
    foreach ($results as $name => $status):
        $ok = ($status === 'OK');
        if (!$ok) $allOk = false;
    ?>
    <div class="table-row <?= $ok ? 'ok' : 'err' ?>">
        <span class="icon"><?= $ok ? '✅' : '❌' ?></span>
        <span class="name"><?= htmlspecialchars($name) ?></span>
        <span class="status <?= $ok ? '' : 'err' ?>"><?= htmlspecialchars($status) ?></span>
    </div>
    <?php endforeach; ?>

    <div class="summary <?= $allOk ? 'ok' : 'err' ?>">
        <?= $allOk
            ? '✅ 모든 테이블이 정상 생성되었습니다. (IF NOT EXISTS — 재실행 안전)'
            : '⚠️ 일부 테이블에서 오류가 발생했습니다. 위 에러 메시지를 확인하세요.' ?>
    </div>
</body>
</html>
