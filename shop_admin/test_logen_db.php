<?php
/**
 * 로젠택배 DB 테이블 생성 - 디버그 버전
 */

// 에러 표시 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== 로젠택배 테이블 생성 디버그 ===<br><br>";

// 1. DB 연결 확인
echo "1. DB 연결 시도...<br>";
try {
    require_once __DIR__ . '/../db.php';
    echo "✅ DB 연결 성공<br><br>";
} catch (Exception $e) {
    die("❌ DB 연결 실패: " . $e->getMessage());
}

// 2. 연결 객체 확인
if (!isset($db)) {
    die("❌ \$db 변수가 정의되지 않았습니다");
}

echo "2. DB 연결 객체 확인: ";
echo is_object($db) ? "✅ 객체<br><br>" : "❌ 객체 아님<br><br>";

// 3. 테이블 생성
echo "3. logen_shipment 테이블 생성 시도...<br>";

$createTable = "
CREATE TABLE IF NOT EXISTS logen_shipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_no INT NOT NULL COMMENT '주문번호',
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

$result = mysqli_query($db, $createTable);

if ($result) {
    echo "✅ 테이블 생성 성공<br><br>";

    // 4. 테이블 구조 확인
    echo "4. 테이블 구조 확인:<br>";
    $desc = mysqli_query($db, "DESCRIBE logen_shipment");

    if ($desc) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background:#f0f0f0;'><th>필드</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th></tr>";

        while ($row = mysqli_fetch_assoc($desc)) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>" . ($row['Default'] ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }

    echo "<h3>✅ 설치 완료!</h3>";
    echo "<p><a href='post_list52.php'>로젠 주소 추출 페이지로 이동</a></p>";

} else {
    echo "❌ 테이블 생성 실패<br>";
    echo "오류: " . mysqli_error($db) . "<br>";
    echo "쿼리: <pre>" . htmlspecialchars($createTable) . "</pre>";
}

mysqli_close($db);
?>
