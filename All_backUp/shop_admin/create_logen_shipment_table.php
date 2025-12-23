<?php
/**
 * 로젠택배 배송 정보 테이블 생성
 *
 * 실행: http://localhost/shop_admin/create_logen_shipment_table.php
 */

include "lib.php";  // 관리자 인증
require_once __DIR__ . '/../db.php';

$createTable = "
CREATE TABLE IF NOT EXISTS logen_shipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_no INT NOT NULL COMMENT '주문번호 (mlangorder_printauto.no)',
    invoice_no VARCHAR(20) COMMENT '송장번호',
    custCd VARCHAR(20) NOT NULL COMMENT '고객사 코드',
    shipment_status VARCHAR(50) DEFAULT '접수대기' COMMENT '배송 상태',
    registered_at DATETIME COMMENT 'API 등록 시각',
    last_updated DATETIME COMMENT '마지막 상태 업데이트',
    api_response TEXT COMMENT 'API 응답 JSON',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_order_no (order_no),
    INDEX idx_invoice_no (invoice_no),
    INDEX idx_status (shipment_status),
    INDEX idx_registered_at (registered_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='로젠택배 배송 정보';
";

if (mysqli_query($db, $createTable)) {
    echo "✅ logen_shipment 테이블 생성 성공<br>";

    // 테이블 구조 확인
    $result = mysqli_query($db, "DESCRIBE logen_shipment");
    echo "<br><strong>테이블 구조:</strong><br>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>필드</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";

} else {
    echo "❌ 테이블 생성 실패: " . mysqli_error($db) . "<br>";
}

mysqli_close($db);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>로젠택배 테이블 생성 완료</title>
</head>
<body>
    <h2>📦 로젠택배 배송 관리 시스템 설치 완료</h2>
    <p>이제 <a href="logen_auto_register.php">배송 자동 접수</a> 페이지를 사용할 수 있습니다.</p>
</body>
</html>
