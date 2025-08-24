<?php
/**
 * shop_temp 테이블 강제 설치 (기존 데이터 삭제)
 * 주의: 기존 데이터가 모두 삭제됩니다!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../../db.php";
$connect = $db;

if (!$connect) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

echo "<h2>⚠️ 강제 테이블 설치</h2>";
echo "<p style='color: red;'><strong>주의: 기존 데이터가 모두 삭제됩니다!</strong></p>";

if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    try {
        // 기존 테이블 삭제
        echo "<p>🗑️ 기존 테이블 삭제 중...</p>";
        if (mysqli_query($connect, "DROP TABLE IF EXISTS shop_temp")) {
            echo "<p style='color: green;'>✅ 기존 테이블 삭제 완료</p>";
        }
        
        // 새 테이블 생성
        echo "<p>🔧 새 테이블 생성 중...</p>";
        $create_sql = "
        CREATE TABLE `shop_temp` (
          `no` int(11) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
          `session_id` varchar(100) NOT NULL COMMENT '세션ID',
          `order_id` varchar(50) DEFAULT NULL COMMENT '주문ID',
          `parent` varchar(50) DEFAULT NULL COMMENT '부모 정보',
          
          `product_type` varchar(50) NOT NULL DEFAULT 'sticker' COMMENT '상품유형',
          
          `jong` varchar(200) DEFAULT NULL COMMENT '스티커 종류',
          `garo` varchar(50) DEFAULT NULL COMMENT '가로',
          `sero` varchar(50) DEFAULT NULL COMMENT '세로', 
          `mesu` varchar(50) DEFAULT NULL COMMENT '수량',
          `domusong` varchar(200) DEFAULT NULL COMMENT '옵션 정보',
          `uhyung` int(1) DEFAULT 0 COMMENT '디자인 여부',
          
          `MY_type` varchar(50) DEFAULT NULL COMMENT '카테고리 번호1',
          `MY_Fsd` varchar(50) DEFAULT NULL COMMENT '카테고리 번호2', 
          `PN_type` varchar(50) DEFAULT NULL COMMENT '카테고리 번호3',
          `MY_amount` varchar(50) DEFAULT NULL COMMENT '수량 번호',
          `POtype` varchar(10) DEFAULT NULL COMMENT 'PO타입',
          `ordertype` varchar(50) DEFAULT NULL COMMENT '주문타입',
          
          `st_price` decimal(10,2) DEFAULT 0.00 COMMENT '기본 가격',
          `st_price_vat` decimal(10,2) DEFAULT 0.00 COMMENT 'VAT 포함 가격',
          
          `MY_comment` text DEFAULT NULL COMMENT '요청사항',
          `img` varchar(200) DEFAULT NULL COMMENT '이미지 파일명',
          `regdate` int(11) DEFAULT NULL COMMENT '등록시간',
          
          PRIMARY KEY (`no`),
          KEY `idx_session` (`session_id`),
          KEY `idx_product_type` (`product_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        if (mysqli_query($connect, $create_sql)) {
            echo "<p style='color: green;'>✅ 새 테이블 생성 완료!</p>";
            echo "<h3>🎉 설치 완료!</h3>";
            echo "<p><a href='cart.php' target='_blank'>통합 장바구니 확인하기</a></p>";
        } else {
            throw new Exception("테이블 생성 실패: " . mysqli_error($connect));
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ 오류: " . $e->getMessage() . "</p>";
    }
} else {
    // 확인 폼 표시
    echo "<form method='post'>";
    echo "<p>기존 데이터 3개가 모두 삭제됩니다. 계속하시겠습니까?</p>";
    echo "<label><input type='checkbox' name='confirm' value='yes' required> 네, 기존 데이터를 삭제하고 새로 설치합니다</label><br><br>";
    echo "<button type='submit' style='background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>강제 설치 실행</button>";
    echo "</form>";
}

mysqli_close($connect);
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
</style>