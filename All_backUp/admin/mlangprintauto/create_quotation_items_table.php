<?php
/**
 * quotation_items 테이블 생성 스크립트
 * 견적서 품목을 개별 레코드로 관리하는 테이블
 *
 * 실행 방법: http://localhost/admin/mlangprintauto/create_quotation_items_table.php
 * 주의: 한 번만 실행하세요!
 */

session_start();
require_once __DIR__ . '/../../db.php';

echo "<!DOCTYPE html>
<html lang='ko'>
<head>
    <meta charset='UTF-8'>
    <title>quotation_items 테이블 생성</title>
    <style>
        body { font-family: 'Noto Sans', sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
        .info { color: #0066cc; padding: 10px; background: #e7f3ff; border-radius: 5px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white;
               text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
<div class='container'>
    <h1>📋 quotation_items 테이블 생성</h1>";

// 1. 기존 테이블 확인
echo "<h2>1️⃣ 기존 테이블 확인</h2>";
$check_sql = "SHOW TABLES LIKE 'quotation_items'";
$result = mysqli_query($db, $check_sql);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<div class='info'>";
    echo "✅ quotation_items 테이블이 이미 존재합니다.<br>";
    echo "테이블 구조를 확인합니다...<br><br>";

    $describe_sql = "DESCRIBE quotation_items";
    $desc_result = mysqli_query($db, $describe_sql);

    if ($desc_result) {
        echo "<strong>현재 테이블 구조:</strong><br>";
        echo "<pre>";
        printf("%-25s %-20s %-10s %-10s\n", "Field", "Type", "Null", "Key");
        echo str_repeat("-", 70) . "\n";
        while ($row = mysqli_fetch_assoc($desc_result)) {
            printf("%-25s %-20s %-10s %-10s\n",
                   $row['Field'],
                   $row['Type'],
                   $row['Null'],
                   $row['Key']);
        }
        echo "</pre>";
    }
    echo "</div>";

    echo "<a href='/admin/mlangprintauto/admin.php' class='btn'>← 관리자 페이지로 돌아가기</a>";
    echo "</div></body></html>";
    exit;
}

// 2. 테이블 생성
echo "<div class='info'>quotation_items 테이블을 생성합니다...</div>";

$create_sql = "CREATE TABLE quotation_items (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '품목 ID',
    quotation_id INT NOT NULL COMMENT '견적서 ID',

    -- 품목 기본 정보
    item_type ENUM('cart', 'manual', 'calculator') NOT NULL COMMENT '품목 출처 (장바구니/직접입력/계산기)',
    product_type VARCHAR(50) COMMENT '제품 유형 (inserted, namecard, envelope 등)',
    product_name VARCHAR(100) NOT NULL COMMENT '품목명',
    specification TEXT COMMENT '규격/사양 (간단 텍스트)',

    -- 수량 및 금액
    quantity INT NOT NULL DEFAULT 1 COMMENT '수량',
    unit VARCHAR(20) DEFAULT '개' COMMENT '단위',
    unit_price DECIMAL(10,2) DEFAULT 0 COMMENT '단가',
    supply_price INT NOT NULL DEFAULT 0 COMMENT '공급가액',
    vat_price INT DEFAULT 0 COMMENT '부가세',
    total_price INT NOT NULL DEFAULT 0 COMMENT '합계 (공급가+VAT)',

    -- 참조 정보
    source_cart_id INT COMMENT 'shop_temp.no (cart 품목인 경우)',
    calculator_session TEXT COMMENT '계산기 세션 데이터 (복원용)',

    -- 제품별 세부 옵션 (JSON)
    details JSON COMMENT '제품별 옵션 및 계산 데이터 {MY_type, Section, POtype, premium_options, uploaded_files 등}',

    -- 정렬 및 메타
    sort_order INT DEFAULT 0 COMMENT '품목 표시 순서',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '생성 일시',

    -- 인덱스
    INDEX idx_quotation_id (quotation_id),
    INDEX idx_product_type (product_type),
    INDEX idx_item_type (item_type),

    -- 외래키 (quotations 테이블 존재 시)
    FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='견적서 품목 테이블'";

if (mysqli_query($db, $create_sql)) {
    echo "<div class='success'>";
    echo "<h3>✅ quotation_items 테이블 생성 성공!</h3>";

    // 3. 생성된 테이블 구조 확인
    echo "<h3>📊 생성된 테이블 구조</h3>";
    $describe_sql = "DESCRIBE quotation_items";
    $desc_result = mysqli_query($db, $describe_sql);

    if ($desc_result) {
        echo "<pre>";
        printf("%-25s %-30s %-10s %-10s %-15s\n", "Field", "Type", "Null", "Key", "Extra");
        echo str_repeat("-", 95) . "\n";

        while ($row = mysqli_fetch_assoc($desc_result)) {
            printf("%-25s %-30s %-10s %-10s %-15s\n",
                   $row['Field'],
                   $row['Type'],
                   $row['Null'],
                   $row['Key'],
                   $row['Extra']);
        }
        echo "</pre>";
    }

    // 4. 인덱스 확인
    echo "<h3>🔍 생성된 인덱스</h3>";
    $index_sql = "SHOW INDEX FROM quotation_items";
    $index_result = mysqli_query($db, $index_sql);

    if ($index_result) {
        echo "<pre>";
        printf("%-25s %-20s %-20s\n", "Key Name", "Column", "Index Type");
        echo str_repeat("-", 70) . "\n";

        while ($row = mysqli_fetch_assoc($index_result)) {
            printf("%-25s %-20s %-20s\n",
                   $row['Key_name'],
                   $row['Column_name'],
                   $row['Index_type']);
        }
        echo "</pre>";
    }

    echo "<p><strong>✨ 테이블 특징:</strong></p>";
    echo "<ul>";
    echo "<li>품목별 독립 레코드로 관리 (정규화)</li>";
    echo "<li>item_type으로 출처 구분 (장바구니/직접입력/계산기)</li>";
    echo "<li>details JSON 컬럼으로 제품별 옵션 유연하게 저장</li>";
    echo "<li>quotation_id 외래키로 견적서와 연결</li>";
    echo "<li>CASCADE DELETE로 견적서 삭제 시 품목도 자동 삭제</li>";
    echo "</ul>";

    echo "</div>";

} else {
    echo "<div class='error'>";
    echo "<h3>❌ 테이블 생성 실패</h3>";
    echo "<p><strong>오류:</strong> " . mysqli_error($db) . "</p>";
    echo "<p><strong>가능한 원인:</strong></p>";
    echo "<ul>";
    echo "<li>quotations 테이블이 존재하지 않음 (외래키 제약)</li>";
    echo "<li>권한 부족</li>";
    echo "<li>MySQL 버전 호환성 문제</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<div class='info' style='margin-top: 30px;'>";
echo "<strong>⚠️ 보안 권장사항:</strong><br>";
echo "이 스크립트는 한 번만 실행하면 됩니다.<br>";
echo "실행 후 FTP에서 <code>/admin/mlangprintauto/create_quotation_items_table.php</code> 파일을 삭제하세요.";
echo "</div>";

echo "<a href='/admin/mlangprintauto/admin.php' class='btn'>← 관리자 페이지로 돌아가기</a>";

echo "</div>
</body>
</html>";

mysqli_close($db);
?>
