<?php
/**
 * 관리자 견적서 시스템 - 테이블 생성 스크립트
 *
 * 테이블:
 * - admin_quotes: 견적서 메인
 * - admin_quote_items: 견적 품목
 * - admin_quotation_temp: 임시 품목 (계산기 연동)
 */

session_start();

// Admin authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die('관리자 로그인이 필요합니다.');
}

require_once __DIR__ . '/../../../db.php';

if (!$db) {
    die('데이터베이스 연결 실패');
}

mysqli_set_charset($db, 'utf8mb4');

$results = [];

// 1. admin_quotes 테이블
$sql_quotes = "
CREATE TABLE IF NOT EXISTS `admin_quotes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `quote_no` VARCHAR(20) NOT NULL COMMENT '견적번호 (AQ-YYYYMMDD-XXXX)',
  `revision_no` TINYINT(2) DEFAULT 0 COMMENT '수정 버전 (0=원본)',
  `parent_quote_id` INT(11) DEFAULT NULL COMMENT '원본 견적 ID (수정본인 경우)',

  -- 고객 정보
  `customer_company` VARCHAR(200) DEFAULT '' COMMENT '회사명',
  `customer_name` VARCHAR(100) NOT NULL COMMENT '담당자명',
  `customer_phone` VARCHAR(50) DEFAULT '' COMMENT '연락처',
  `customer_email` VARCHAR(100) DEFAULT '' COMMENT '이메일',
  `customer_address` TEXT COMMENT '주소',

  -- 금액 (저장 시점 확정)
  `supply_total` INT(11) DEFAULT 0 COMMENT '공급가액 합계',
  `vat_total` INT(11) DEFAULT 0 COMMENT 'VAT 합계',
  `grand_total` INT(11) DEFAULT 0 COMMENT '총액 (공급가액 + VAT)',

  -- 상태 관리
  `status` ENUM('draft','sent','viewed','accepted','rejected','expired','converted') DEFAULT 'draft',
  `valid_until` DATE DEFAULT NULL COMMENT '유효기간',

  -- 발송 이력
  `sent_at` DATETIME DEFAULT NULL COMMENT '발송 시각',
  `viewed_at` DATETIME DEFAULT NULL COMMENT '열람 시각',
  `accepted_at` DATETIME DEFAULT NULL COMMENT '승인 시각',

  -- 주문 전환
  `converted_order_no` VARCHAR(50) DEFAULT NULL COMMENT '전환된 주문번호',
  `converted_at` DATETIME DEFAULT NULL COMMENT '주문 전환 시각',

  -- 메모
  `admin_memo` TEXT COMMENT '관리자 내부 메모',
  `customer_memo` TEXT COMMENT '고객 요청사항',

  -- 작성자
  `created_by` VARCHAR(100) DEFAULT NULL COMMENT '작성 관리자',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_quote_no` (`quote_no`),
  KEY `idx_status` (`status`),
  KEY `idx_customer_name` (`customer_name`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_parent_quote` (`parent_quote_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='관리자 견적서 메인 테이블';
";

if (mysqli_query($db, $sql_quotes)) {
    $results[] = "admin_quotes 테이블 생성 완료";
} else {
    $results[] = "admin_quotes 테이블 생성 실패: " . mysqli_error($db);
}

// 2. admin_quote_items 테이블
$sql_items = "
CREATE TABLE IF NOT EXISTS `admin_quote_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `quote_id` INT(11) NOT NULL COMMENT 'admin_quotes.id 참조',
  `item_no` TINYINT(3) DEFAULT 1 COMMENT '품목 순번',

  -- 품목 출처
  `source_type` ENUM('manual','calculator','cart') DEFAULT 'manual' COMMENT '입력 방식',
  `product_type` VARCHAR(50) DEFAULT '' COMMENT '상품 유형 (sticker, inserted, etc.)',

  -- 품목 정보
  `product_name` VARCHAR(200) NOT NULL COMMENT '품명',
  `specification` TEXT COMMENT '규격 및 사양',

  -- 수량/단가/금액
  `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1 COMMENT '수량',
  `unit` VARCHAR(10) DEFAULT '개' COMMENT '단위 (매, 연, 부, 권, 개, 장)',
  `quantity_display` VARCHAR(50) DEFAULT '' COMMENT '표시용 수량 (0.5연 (2,000매))',
  `unit_price` DECIMAL(12,2) DEFAULT 0 COMMENT '단가',
  `supply_price` INT(11) DEFAULT 0 COMMENT '공급가액',

  -- 원본 데이터 (계산기에서 가져온 경우)
  `source_data` JSON COMMENT '계산기/장바구니 원본 데이터 스냅샷',

  -- 메모
  `notes` TEXT COMMENT '품목별 비고',

  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_quote_id` (`quote_id`),
  KEY `idx_product_type` (`product_type`),
  CONSTRAINT `fk_admin_quote_items_quote`
    FOREIGN KEY (`quote_id`) REFERENCES `admin_quotes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='관리자 견적서 품목 테이블';
";

if (mysqli_query($db, $sql_items)) {
    $results[] = "admin_quote_items 테이블 생성 완료";
} else {
    $results[] = "admin_quote_items 테이블 생성 실패: " . mysqli_error($db);
}

// 3. admin_quotation_temp 테이블 (임시 품목)
$sql_temp = "
CREATE TABLE IF NOT EXISTS `admin_quotation_temp` (
  `no` INT(11) NOT NULL AUTO_INCREMENT,
  `admin_session_id` VARCHAR(100) NOT NULL COMMENT '관리자 세션 ID',
  `draft_quote_id` INT(11) DEFAULT NULL COMMENT '임시저장 견적 ID',

  -- shop_temp와 동일한 레거시 필드 (계산기 호환)
  `product_type` VARCHAR(50) NOT NULL DEFAULT 'sticker',
  `jong` VARCHAR(200) DEFAULT NULL,
  `garo` VARCHAR(50) DEFAULT NULL,
  `sero` VARCHAR(50) DEFAULT NULL,
  `mesu` VARCHAR(50) DEFAULT NULL,
  `domusong` VARCHAR(200) DEFAULT NULL,
  `uhyung` INT DEFAULT 0,
  `MY_type` VARCHAR(50) DEFAULT NULL,
  `MY_Fsd` VARCHAR(50) DEFAULT NULL,
  `PN_type` VARCHAR(50) DEFAULT NULL,
  `MY_amount` VARCHAR(50) DEFAULT NULL,
  `POtype` VARCHAR(10) DEFAULT NULL,
  `ordertype` VARCHAR(50) DEFAULT NULL,
  `st_price` DECIMAL(10,2) DEFAULT 0,
  `st_price_vat` DECIMAL(10,2) DEFAULT 0,
  `Section` VARCHAR(50) DEFAULT NULL,
  `MY_comment` TEXT,

  -- 표준 필드 (Phase 3 호환)
  `spec_type` VARCHAR(100) DEFAULT NULL,
  `spec_material` VARCHAR(100) DEFAULT NULL,
  `spec_size` VARCHAR(100) DEFAULT NULL,
  `spec_sides` VARCHAR(50) DEFAULT NULL,
  `spec_design` VARCHAR(50) DEFAULT NULL,
  `quantity_display` VARCHAR(50) DEFAULT NULL,
  `data_version` TINYINT DEFAULT 2,

  -- 추가 옵션
  `additional_options` TEXT,
  `premium_options` TEXT,
  `coating_enabled` TINYINT(1) DEFAULT 0,
  `coating_type` VARCHAR(20) DEFAULT NULL,
  `coating_price` INT DEFAULT 0,
  `folding_enabled` TINYINT(1) DEFAULT 0,
  `folding_type` VARCHAR(20) DEFAULT NULL,
  `folding_price` INT DEFAULT 0,
  `creasing_enabled` TINYINT(1) DEFAULT 0,
  `creasing_lines` INT DEFAULT 0,
  `creasing_price` INT DEFAULT 0,
  `additional_options_total` INT DEFAULT 0,

  -- 수동 입력 필드 (임의 품목용)
  `is_manual` TINYINT(1) DEFAULT 0 COMMENT '수동 입력 여부',
  `manual_product_name` VARCHAR(200) DEFAULT NULL,
  `manual_specification` TEXT,
  `manual_quantity` DECIMAL(10,2) DEFAULT NULL,
  `manual_unit` VARCHAR(10) DEFAULT NULL,
  `manual_supply_price` INT DEFAULT NULL,

  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`no`),
  KEY `idx_admin_session` (`admin_session_id`),
  KEY `idx_draft_quote` (`draft_quote_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='관리자 견적 임시 품목 테이블';
";

if (mysqli_query($db, $sql_temp)) {
    $results[] = "admin_quotation_temp 테이블 생성 완료";
} else {
    $results[] = "admin_quotation_temp 테이블 생성 실패: " . mysqli_error($db);
}

// 결과 출력
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>테이블 생성 결과</title>
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        .result { padding: 10px 15px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .back-link { margin-top: 20px; display: block; color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>관리자 견적서 시스템 - 테이블 생성</h1>
        <?php foreach ($results as $result): ?>
            <div class="result <?php echo strpos($result, '완료') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($result); ?>
            </div>
        <?php endforeach; ?>
        <a href="index.php" class="back-link">← 견적 목록으로</a>
    </div>
</body>
</html>
<?php
mysqli_close($db);
?>
