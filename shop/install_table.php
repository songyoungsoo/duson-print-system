<?php
/**
 * shop_temp 테이블 설치 스크립트
 * 주의: 기존 데이터가 있다면 백업 후 실행하세요!
 */

// 에러 표시 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 데이터베이스 연결
include "../lib/func.php";
$connect = dbconn();

if (!$connect) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

echo "<h2>shop_temp 테이블 설치</h2>";

// 1. 기존 테이블 확인
$check_query = "SHOW TABLES LIKE 'shop_temp'";
$result = mysqli_query($connect, $check_query);

if (mysqli_num_rows($result) > 0) {
    echo "<p style='color: orange;'>⚠️ 기존 shop_temp 테이블이 존재합니다.</p>";
    
    // 기존 데이터 개수 확인
    $count_query = "SELECT COUNT(*) as count FROM shop_temp";
    $count_result = mysqli_query($connect, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    
    echo "<p>기존 데이터 개수: {$count_row['count']}개</p>";
    
    if ($count_row['count'] > 0) {
        echo "<p style='color: red;'>❌ 기존 데이터가 있습니다. 수동으로 백업 후 진행하세요.</p>";
        echo "<p>백업 명령어: <code>CREATE TABLE shop_temp_backup AS SELECT * FROM shop_temp;</code></p>";
        exit;
    }
    
    // 기존 테이블 삭제
    if (mysqli_query($connect, "DROP TABLE shop_temp")) {
        echo "<p style='color: green;'>✅ 기존 테이블 삭제 완료</p>";
    } else {
        echo "<p style='color: red;'>❌ 기존 테이블 삭제 실패: " . mysqli_error($connect) . "</p>";
        exit;
    }
}

// 2. 새 테이블 생성
$create_sql = "
CREATE TABLE `shop_temp` (
  `no` int(11) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
  `session_id` varchar(100) NOT NULL COMMENT '세션ID',
  `order_id` varchar(50) DEFAULT NULL COMMENT '주문ID (주문 시 생성)',
  `parent` varchar(50) DEFAULT NULL COMMENT '부모 정보',
  
  -- 상품 기본 정보
  `product_type` varchar(50) NOT NULL DEFAULT 'sticker' COMMENT '상품유형 (sticker, leaflet, cadarok, namecard, envelope 등)',
  
  -- 스티커 전용 필드들 (기존 호환성 유지)
  `jong` varchar(200) DEFAULT NULL COMMENT '스티커 종류 (스티커만 사용)',
  `garo` varchar(50) DEFAULT NULL COMMENT '가로 (스티커용)',
  `sero` varchar(50) DEFAULT NULL COMMENT '세로 (스티커용)', 
  `mesu` varchar(50) DEFAULT NULL COMMENT '수량 (스티커용)',
  `domusong` varchar(200) DEFAULT NULL COMMENT '옵션 정보 (스티커용)',
  `uhyung` int(1) DEFAULT 0 COMMENT '디자인 여부 (0:인쇄만, 1:디자인+인쇄)',
  
  -- 공통 카테고리 매핑 정보 (transactioncate 테이블 참조)
  `MY_type` varchar(50) DEFAULT NULL COMMENT '카테고리 번호1 (색상, 타입 등)',
  `MY_Fsd` varchar(50) DEFAULT NULL COMMENT '카테고리 번호2 (용지, 스타일 등)', 
  `PN_type` varchar(50) DEFAULT NULL COMMENT '카테고리 번호3 (사이즈, 섹션 등)',
  `MY_amount` varchar(50) DEFAULT NULL COMMENT '수량 번호',
  `POtype` varchar(10) DEFAULT NULL COMMENT 'PO타입 (단면/양면 등)',
  `ordertype` varchar(50) DEFAULT NULL COMMENT '주문타입 (print/design)',
  
  -- 가격 정보
  `st_price` decimal(10,2) DEFAULT 0.00 COMMENT '기본 가격 (VAT 제외)',
  `st_price_vat` decimal(10,2) DEFAULT 0.00 COMMENT 'VAT 포함 가격',
  
  -- 추가 정보
  `MY_comment` text DEFAULT NULL COMMENT '요청사항/메모',
  `img` varchar(200) DEFAULT NULL COMMENT '이미지 파일명',
  `regdate` int(11) DEFAULT NULL COMMENT '등록시간 (timestamp)',
  
  PRIMARY KEY (`no`),
  KEY `idx_session` (`session_id`),
  KEY `idx_product_type` (`product_type`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='통합 임시 장바구니 테이블'
";

if (mysqli_query($connect, $create_sql)) {
    echo "<p style='color: green;'>✅ 새로운 shop_temp 테이블 생성 완료!</p>";
    
    // 테이블 구조 확인
    $desc_result = mysqli_query($connect, "DESCRIBE shop_temp");
    echo "<h3>생성된 테이블 구조:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>필드명</th><th>타입</th><th>Null</th><th>Key</th><th>기본값</th><th>설명</th></tr>";
    
    while ($row = mysqli_fetch_assoc($desc_result)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>-</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>다음 단계:</h3>";
    echo "<ol>";
    echo "<li><a href='usage_example.php'>사용 예시 테스트</a></li>";
    echo "<li>기존 장바구니 코드를 새 테이블에 맞게 수정</li>";
    echo "<li>shop_temp_helper.php 함수들 활용</li>";
    echo "</ol>";
    
} else {
    echo "<p style='color: red;'>❌ 테이블 생성 실패: " . mysqli_error($connect) . "</p>";
}

mysqli_close($connect);
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
code { background-color: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
</style>