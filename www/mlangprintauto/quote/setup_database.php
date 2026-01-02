<?php
/**
 * 견적/거래명세표 시스템 데이터베이스 설정
 * 실행: php setup_database.php 또는 브라우저에서 ?key=setup2025
 */

// CLI 또는 인증된 접근만 허용
if (php_sapi_name() !== 'cli') {
    $key = $_GET['key'] ?? '';
    if ($key !== 'setup2025') {
        die('Unauthorized. Use ?key=setup2025');
    }
}

require_once __DIR__ . '/../../db.php';

echo "<pre>\n";
echo "=== 견적/거래명세표 시스템 DB 설정 ===\n\n";

// 1. company_settings 테이블
$sql_company = "CREATE TABLE IF NOT EXISTS company_settings (
    id INT PRIMARY KEY DEFAULT 1,
    company_name VARCHAR(100) NOT NULL DEFAULT '두손기획인쇄',
    business_number VARCHAR(20) DEFAULT '107-06-45106',
    representative VARCHAR(50) DEFAULT '차경선(직인생략)',
    address TEXT,
    phone VARCHAR(20) DEFAULT '02-2632-1830',
    fax VARCHAR(20) DEFAULT '02-2632-1831',
    email VARCHAR(100) DEFAULT 'dsp1830@naver.com',
    bank_name VARCHAR(50) DEFAULT '',
    bank_account VARCHAR(50) DEFAULT '',
    account_holder VARCHAR(50) DEFAULT '',
    logo_path VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($db, $sql_company)) {
    echo "✅ company_settings 테이블 생성 완료\n";
} else {
    echo "❌ company_settings 오류: " . mysqli_error($db) . "\n";
}

// 기본 회사 정보 삽입
$sql_company_data = "INSERT IGNORE INTO company_settings (id, address) VALUES (1, '서울 영등포구 영등포로36길9 송호빌딩 1층')";
mysqli_query($db, $sql_company_data);

// 2. quotes 테이블 (견적서/거래명세표 통합)
$sql_quotes = "CREATE TABLE IF NOT EXISTS quotes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quote_no VARCHAR(50) NOT NULL UNIQUE COMMENT '견적번호 QT-YYYYMMDD-NNN 또는 TX-YYYYMMDD-NNN',
    quote_type ENUM('quotation', 'transaction', 'tax_invoice') DEFAULT 'quotation' COMMENT '문서 유형',
    public_token VARCHAR(64) UNIQUE COMMENT '공개 링크용 토큰',

    -- 고객 정보
    customer_id INT DEFAULT NULL COMMENT 'member_user.no 참조 (선택)',
    customer_name VARCHAR(100) NOT NULL COMMENT '고객명/담당자명',
    customer_company VARCHAR(100) DEFAULT '' COMMENT '회사명',
    customer_phone VARCHAR(30) DEFAULT '' COMMENT '연락처',
    customer_email VARCHAR(100) DEFAULT '' COMMENT '고객 이메일',
    recipient_email VARCHAR(100) DEFAULT '' COMMENT '발송 대상 이메일',

    -- 배송 정보
    delivery_type VARCHAR(30) DEFAULT '' COMMENT '배송방식',
    delivery_address TEXT COMMENT '배송지 주소',
    delivery_price INT DEFAULT 0 COMMENT '배송비',

    -- 금액 정보
    supply_total INT DEFAULT 0 COMMENT '공급가액 합계',
    vat_total INT DEFAULT 0 COMMENT '부가세 합계',
    discount_amount INT DEFAULT 0 COMMENT '할인금액',
    discount_reason VARCHAR(255) DEFAULT '' COMMENT '할인사유',
    grand_total INT DEFAULT 0 COMMENT '총액 (VAT 포함)',

    -- 조건 및 기간
    payment_terms VARCHAR(100) DEFAULT '발행일로부터 7일' COMMENT '결제조건',
    valid_days INT DEFAULT 7 COMMENT '유효기간(일)',
    valid_until DATE COMMENT '유효기간 만료일',

    -- 상태 관리
    status ENUM('draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired', 'converted') DEFAULT 'draft',
    customer_response ENUM('pending', 'accepted', 'rejected', 'negotiate') DEFAULT 'pending',
    response_date DATETIME DEFAULT NULL,
    response_notes TEXT COMMENT '고객 응답 메모',

    -- 참조 정보
    session_id VARCHAR(100) DEFAULT '' COMMENT '장바구니 세션',
    source_quote_id INT DEFAULT NULL COMMENT '원본 견적서 ID (전환 시)',
    converted_order_no VARCHAR(50) DEFAULT '' COMMENT '전환된 주문번호',

    -- 파일 및 메타
    notes TEXT COMMENT '관리자 메모',
    pdf_path VARCHAR(255) DEFAULT '' COMMENT 'PDF 파일 경로',

    -- 감사
    created_by INT DEFAULT 0 COMMENT '작성자 ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_quote_no (quote_no),
    INDEX idx_quote_type (quote_type),
    INDEX idx_status (status),
    INDEX idx_customer_email (customer_email),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($db, $sql_quotes)) {
    echo "✅ quotes 테이블 생성 완료\n";
} else {
    echo "❌ quotes 오류: " . mysqli_error($db) . "\n";
}

// 3. quote_items 테이블 (품목 상세)
$sql_items = "CREATE TABLE IF NOT EXISTS quote_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quote_id INT NOT NULL COMMENT 'quotes.id 참조',
    item_no INT DEFAULT 1 COMMENT '품목 순번',

    -- 상품 정보
    product_type VARCHAR(50) DEFAULT '' COMMENT '상품 유형 (sticker, namecard, etc)',
    product_name VARCHAR(200) NOT NULL COMMENT '품명',
    specification TEXT COMMENT '규격/사양 (JSON 또는 텍스트)',

    -- 수량 및 가격
    quantity INT DEFAULT 1 COMMENT '수량',
    unit VARCHAR(10) DEFAULT '개' COMMENT '단위 (매, 개, 부, 식)',
    unit_price INT DEFAULT 0 COMMENT '단가',
    supply_price INT DEFAULT 0 COMMENT '공급가액',
    vat_amount INT DEFAULT 0 COMMENT '부가세',
    total_price INT DEFAULT 0 COMMENT '합계 (VAT 포함)',

    -- 원본 참조
    source_type ENUM('cart', 'manual', 'custom') DEFAULT 'manual' COMMENT '입력 방식',
    source_id INT DEFAULT NULL COMMENT 'shop_temp.no 참조',
    source_data JSON COMMENT '원본 데이터 스냅샷',

    -- 메타
    notes TEXT COMMENT '품목별 비고',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_quote_id (quote_id),
    INDEX idx_product_type (product_type),
    FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($db, $sql_items)) {
    echo "✅ quote_items 테이블 생성 완료\n";
} else {
    echo "❌ quote_items 오류: " . mysqli_error($db) . "\n";
}

// 4. quote_emails 테이블 (발송 이력)
$sql_emails = "CREATE TABLE IF NOT EXISTS quote_emails (
    id INT PRIMARY KEY AUTO_INCREMENT,
    quote_id INT NOT NULL COMMENT 'quotes.id 참조',
    quote_no VARCHAR(50) NOT NULL COMMENT '견적번호 (비정규화)',

    -- 발송 정보
    recipient_email VARCHAR(100) NOT NULL COMMENT '수신자 이메일',
    recipient_name VARCHAR(100) DEFAULT '' COMMENT '수신자 이름',
    cc_email VARCHAR(100) DEFAULT '' COMMENT 'CC 이메일',
    subject VARCHAR(255) NOT NULL COMMENT '이메일 제목',

    -- 상태
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    error_message TEXT COMMENT '오류 메시지',

    -- 감사
    sent_by INT DEFAULT 0 COMMENT '발송자 ID',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_quote_id (quote_id),
    INDEX idx_recipient (recipient_email),
    INDEX idx_sent_at (sent_at),
    FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($db, $sql_emails)) {
    echo "✅ quote_emails 테이블 생성 완료\n";
} else {
    echo "❌ quote_emails 오류: " . mysqli_error($db) . "\n";
}

echo "\n=== 데이터베이스 설정 완료 ===\n";
echo "</pre>";

mysqli_close($db);
?>
