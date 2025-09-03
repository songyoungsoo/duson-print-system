<?php
/**
 * shop_temp 테이블 마이그레이션 스크립트
 * 기존 데이터를 보존하면서 새 구조로 업그레이드
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../../db.php";
$connect = $db;

if (!$connect) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

echo "<h2>shop_temp 테이블 마이그레이션</h2>";

try {
    // 1. 기존 데이터 백업
    echo "<p>🔄 기존 데이터 백업 중...</p>";
    $backup_table = "shop_temp_backup_" . date('YmdHis');
    $backup_query = "CREATE TABLE $backup_table AS SELECT * FROM shop_temp";
    
    if (mysqli_query($connect, $backup_query)) {
        echo "<p style='color: green;'>✅ 백업 완료: $backup_table</p>";
    } else {
        throw new Exception("백업 실패: " . mysqli_error($connect));
    }
    
    // 2. 기존 데이터 조회
    echo "<p>📋 기존 데이터 조회 중...</p>";
    $existing_data = [];
    $result = mysqli_query($connect, "SELECT * FROM shop_temp");
    while ($row = mysqli_fetch_assoc($result)) {
        $existing_data[] = $row;
    }
    echo "<p>기존 데이터 개수: " . count($existing_data) . "개</p>";
    
    // 3. 기존 테이블 삭제
    echo "<p>🗑️ 기존 테이블 삭제 중...</p>";
    if (mysqli_query($connect, "DROP TABLE shop_temp")) {
        echo "<p style='color: green;'>✅ 기존 테이블 삭제 완료</p>";
    } else {
        throw new Exception("테이블 삭제 실패: " . mysqli_error($connect));
    }
    
    // 4. 새 테이블 생성
    echo "<p>🔧 새 테이블 생성 중...</p>";
    $create_sql = "
    CREATE TABLE `shop_temp` (
      `no` int(11) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
      `session_id` varchar(100) NOT NULL COMMENT '세션ID',
      `order_id` varchar(50) DEFAULT NULL COMMENT '주문ID (주문 시 생성)',
      `parent` varchar(50) DEFAULT NULL COMMENT '부모 정보',
      
      -- 상품 기본 정보
      `product_type` varchar(50) NOT NULL DEFAULT 'sticker' COMMENT '상품유형',
      
      -- 스티커 전용 필드들 (기존 호환성 유지)
      `jong` varchar(200) DEFAULT NULL COMMENT '스티커 종류 (스티커만 사용)',
      `garo` varchar(50) DEFAULT NULL COMMENT '가로 (스티커용)',
      `sero` varchar(50) DEFAULT NULL COMMENT '세로 (스티커용)', 
      `mesu` varchar(50) DEFAULT NULL COMMENT '수량 (스티커용)',
      `domusong` varchar(200) DEFAULT NULL COMMENT '옵션 정보 (스티커용)',
      `uhyung` int(1) DEFAULT 0 COMMENT '디자인 여부 (0:인쇄만, 1:디자인+인쇄)',
      
      -- 공통 카테고리 매핑 정보
      `MY_type` varchar(50) DEFAULT NULL COMMENT '카테고리 번호1',
      `MY_Fsd` varchar(50) DEFAULT NULL COMMENT '카테고리 번호2', 
      `PN_type` varchar(50) DEFAULT NULL COMMENT '카테고리 번호3',
      `MY_amount` varchar(50) DEFAULT NULL COMMENT '수량 번호',
      `POtype` varchar(10) DEFAULT NULL COMMENT 'PO타입',
      `ordertype` varchar(50) DEFAULT NULL COMMENT '주문타입',
      
      -- 가격 정보
      `st_price` decimal(10,2) DEFAULT 0.00 COMMENT '기본 가격',
      `st_price_vat` decimal(10,2) DEFAULT 0.00 COMMENT 'VAT 포함 가격',
      
      -- 추가 정보
      `MY_comment` text DEFAULT NULL COMMENT '요청사항',
      `img` varchar(200) DEFAULT NULL COMMENT '이미지 파일명',
      `regdate` int(11) DEFAULT NULL COMMENT '등록시간',
      
      PRIMARY KEY (`no`),
      KEY `idx_session` (`session_id`),
      KEY `idx_product_type` (`product_type`),
      KEY `idx_order_id` (`order_id`),
      KEY `idx_regdate` (`regdate`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='통합 임시 장바구니 테이블'
    ";
    
    if (mysqli_query($connect, $create_sql)) {
        echo "<p style='color: green;'>✅ 새 테이블 생성 완료</p>";
    } else {
        throw new Exception("테이블 생성 실패: " . mysqli_error($connect));
    }
    
    // 5. 기존 데이터 마이그레이션
    echo "<p>📦 기존 데이터 마이그레이션 중...</p>";
    $migrated_count = 0;
    
    foreach ($existing_data as $old_data) {
        // 기존 데이터를 새 구조에 맞게 변환
        $new_data = [
            'session_id' => $old_data['session_id'] ?? '',
            'order_id' => $old_data['order_id'] ?? null,
            'parent' => $old_data['parent'] ?? null,
            'product_type' => 'sticker', // 기존 데이터는 모두 스티커로 가정
            'jong' => $old_data['jong'] ?? null,
            'garo' => $old_data['garo'] ?? null,
            'sero' => $old_data['sero'] ?? null,
            'mesu' => $old_data['mesu'] ?? null,
            'domusong' => $old_data['domusong'] ?? null,
            'uhyung' => $old_data['uhyung'] ?? 0,
            'st_price' => $old_data['st_price'] ?? 0,
            'st_price_vat' => $old_data['st_price_vat'] ?? 0,
            'img' => $old_data['img'] ?? null,
            'regdate' => $old_data['regdate'] ?? time()
        ];
        
        // 새 테이블에 삽입
        $insert_query = "INSERT INTO shop_temp (
            session_id, order_id, parent, product_type, jong, garo, sero, mesu, domusong, uhyung,
            st_price, st_price_vat, img, regdate
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($connect, $insert_query);
        mysqli_stmt_bind_param($stmt, 'sssssssssiidsi',
            $new_data['session_id'], $new_data['order_id'], $new_data['parent'], $new_data['product_type'],
            $new_data['jong'], $new_data['garo'], $new_data['sero'], $new_data['mesu'], $new_data['domusong'],
            $new_data['uhyung'], $new_data['st_price'], $new_data['st_price_vat'], $new_data['img'], $new_data['regdate']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $migrated_count++;
        }
        mysqli_stmt_close($stmt);
    }
    
    echo "<p style='color: green;'>✅ 데이터 마이그레이션 완료: {$migrated_count}개</p>";
    
    // 6. 결과 확인
    $verify_result = mysqli_query($connect, "SELECT COUNT(*) as count FROM shop_temp");
    $verify_row = mysqli_fetch_assoc($verify_result);
    
    echo "<h3>🎉 마이그레이션 완료!</h3>";
    echo "<p><strong>백업 테이블:</strong> $backup_table</p>";
    echo "<p><strong>마이그레이션된 데이터:</strong> {$verify_row['count']}개</p>";
    
    echo "<h3>다음 단계:</h3>";
    echo "<ol>";
    echo "<li><a href='cart.php' target='_blank'>통합 장바구니 확인</a></li>";
    echo "<li><a href='../usage_example.php' target='_blank'>테스트 페이지</a></li>";
    echo "<li><a href='../cadarok/index_new.php' target='_blank'>카다록 주문 테스트</a></li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
    echo "<p>백업 테이블에서 복구하려면:</p>";
    echo "<code>DROP TABLE shop_temp; RENAME TABLE {$backup_table} TO shop_temp;</code>";
}

mysqli_close($connect);
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
code { background-color: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
</style>