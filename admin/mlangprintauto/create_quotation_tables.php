<?php
/**
 * 견적서 관리 테이블 생성 스크립트
 * 실행: http://localhost/admin/mlangprintauto/create_quotation_tables.php
 */

require_once __DIR__ . '/../../db.php';

echo "<h2>견적서 테이블 생성</h2>";
echo "<pre>";

// 1. quotations 테이블 생성
$sql_quotations = "
CREATE TABLE IF NOT EXISTS quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_no VARCHAR(50) UNIQUE NOT NULL COMMENT '견적번호 (QT-YYYYMMDD-NNN)',
    session_id VARCHAR(100) COMMENT '세션 ID',
    customer_name VARCHAR(100) NOT NULL COMMENT '담당자/고객명',
    customer_email VARCHAR(100) COMMENT '이메일',
    customer_phone VARCHAR(20) COMMENT '전화번호',

    -- 장바구니 항목 (JSON)
    cart_items_json LONGTEXT COMMENT '장바구니 상품 JSON',

    -- 택배선불 정보
    delivery_type VARCHAR(20) COMMENT '배송방식 (택배/퀵/다마스/방문)',
    delivery_price INT DEFAULT 0 COMMENT '배송비',

    -- 추가 항목 (JSON 배열)
    custom_items_json TEXT COMMENT '추가 항목 JSON [{item, spec, qty, unit, price}]',

    -- 금액 정보
    total_supply INT DEFAULT 0 COMMENT '공급가액 합계',
    total_vat INT DEFAULT 0 COMMENT '부가세',
    total_price INT DEFAULT 0 COMMENT '총 합계 (VAT 포함)',

    -- 메모 및 상태
    notes TEXT COMMENT '비고',
    status ENUM('draft', 'sent', 'accepted', 'rejected', 'expired') DEFAULT 'draft' COMMENT '상태',

    -- 관리 정보
    created_by INT COMMENT '작성자 user_id',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at DATE COMMENT '유효기간 (기본 7일)',

    INDEX idx_quotation_no (quotation_no),
    INDEX idx_customer_email (customer_email),
    INDEX idx_created_at (created_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='견적서 테이블';
";

if (mysqli_query($db, $sql_quotations)) {
    echo "✅ quotations 테이블 생성 완료\n";
} else {
    echo "❌ quotations 테이블 생성 실패: " . mysqli_error($db) . "\n";
}

// 2. quotation_emails 테이블 생성
$sql_emails = "
CREATE TABLE IF NOT EXISTS quotation_emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_id INT NOT NULL COMMENT '견적서 ID',
    quotation_no VARCHAR(50) COMMENT '견적번호',
    recipient_email VARCHAR(100) NOT NULL COMMENT '수신자 이메일',
    recipient_name VARCHAR(100) COMMENT '수신자명',
    subject VARCHAR(255) COMMENT '이메일 제목',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '발송 시간',
    status ENUM('sent', 'failed', 'pending') DEFAULT 'pending' COMMENT '발송 상태',
    error_message TEXT COMMENT '오류 메시지',
    sent_by INT COMMENT '발송자 user_id',

    INDEX idx_quotation_id (quotation_id),
    INDEX idx_sent_at (sent_at),
    INDEX idx_status (status),
    FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='견적서 이메일 발송 로그';
";

if (mysqli_query($db, $sql_emails)) {
    echo "✅ quotation_emails 테이블 생성 완료\n";
} else {
    echo "❌ quotation_emails 테이블 생성 실패: " . mysqli_error($db) . "\n";
}

// 3. 테이블 구조 확인
echo "\n--- quotations 테이블 구조 ---\n";
$result = mysqli_query($db, "DESCRIBE quotations");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo sprintf("%-20s %-30s %-5s %-10s\n",
            $row['Field'], $row['Type'], $row['Null'], $row['Key']);
    }
}

echo "\n--- quotation_emails 테이블 구조 ---\n";
$result = mysqli_query($db, "DESCRIBE quotation_emails");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo sprintf("%-20s %-30s %-5s %-10s\n",
            $row['Field'], $row['Type'], $row['Null'], $row['Key']);
    }
}

echo "\n</pre>";
echo "<p><a href='quotation_list.php'>견적서 관리 페이지로 이동 →</a></p>";

mysqli_close($db);
?>
