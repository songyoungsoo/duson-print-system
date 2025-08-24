-- 통합 주문 시스템 테이블 구조
-- 기존 MlangOrder_PrintAuto 테이블을 마스터 테이블로 사용하고
-- 주문 상세 테이블을 추가하여 마스터-디테일 구조 구현

-- 1. 주문 마스터 테이블 (기존 MlangOrder_PrintAuto 활용)
-- 주문 그룹 ID 컬럼 추가
ALTER TABLE MlangOrder_PrintAuto 
ADD COLUMN order_group_id VARCHAR(50) DEFAULT NULL COMMENT '주문 그룹 ID (같은 장바구니 주문)',
ADD COLUMN total_items INT DEFAULT 1 COMMENT '총 주문 품목 수',
ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0 COMMENT '총 주문 금액',
ADD COLUMN total_amount_vat DECIMAL(10,2) DEFAULT 0 COMMENT '총 주문 금액 (VAT 포함)',
ADD COLUMN order_status VARCHAR(20) DEFAULT 'pending' COMMENT '주문 상태',
ADD INDEX idx_order_group (order_group_id);

-- 2. 주문 상세 테이블 생성
CREATE TABLE `mlangorder_printauto_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '상세 항목 ID',
  `order_no` int(11) NOT NULL COMMENT '주문 번호 (MlangOrder_PrintAuto.no)',
  `order_group_id` varchar(50) NOT NULL COMMENT '주문 그룹 ID',
  `product_type` varchar(50) NOT NULL COMMENT '상품 유형',
  `product_name` varchar(200) NOT NULL COMMENT '상품명',
  `product_details` text DEFAULT NULL COMMENT '상품 상세 정보 (JSON)',
  `quantity` varchar(50) DEFAULT NULL COMMENT '수량',
  `unit_price` decimal(10,2) DEFAULT 0 COMMENT '단가',
  `design_price` decimal(10,2) DEFAULT 0 COMMENT '디자인비',
  `item_price` decimal(10,2) DEFAULT 0 COMMENT '품목 금액',
  `item_price_vat` decimal(10,2) DEFAULT 0 COMMENT '품목 금액 (VAT 포함)',
  `special_requests` text DEFAULT NULL COMMENT '특별 요청사항',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_id`),
  KEY `idx_order_no` (`order_no`),
  KEY `idx_order_group` (`order_group_id`),
  KEY `idx_product_type` (`product_type`),
  FOREIGN KEY (`order_no`) REFERENCES `MlangOrder_PrintAuto`(`no`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='주문 상세 항목 테이블';

-- 3. 주문 그룹 ID 생성 함수 (PHP에서 사용)
-- 형식: ORD_YYYYMMDD_HHMMSS_세션ID앞4자리
-- 예: ORD_20250802_143052_A1B2

-- 4. 사용 예시 데이터
-- 주문 마스터 (고객 정보, 배송 정보)
INSERT INTO MlangOrder_PrintAuto (
    no, order_group_id, Type, name, email, phone, zip1, zip2, 
    total_items, total_amount, total_amount_vat, date, order_status
) VALUES (
    1001, 'ORD_20250802_143052_A1B2', '통합주문', '홍길동', 'test@test.com', 
    '010-1234-5678', '서울시 강남구', '테헤란로 123', 
    3, 500000, 550000, NOW(), 'pending'
);

-- 주문 상세 (각 상품별 정보)
INSERT INTO mlangorder_printauto_items (
    order_no, order_group_id, product_type, product_name, product_details,
    quantity, unit_price, design_price, item_price, item_price_vat, special_requests
) VALUES 
(1001, 'ORD_20250802_143052_A1B2', 'sticker', '투명스티커', 
 '{"jong":"jsp 투명스티커","garo":"100","sero":"100","domusong":"사각"}', 
 '1000매', 150000, 0, 150000, 165000, '급하게 부탁드립니다'),
(1001, 'ORD_20250802_143052_A1B2', 'cadarok', '카다록 12페이지', 
 '{"MY_type":"691","MY_Fsd":"697","PN_type":"699"}', 
 '500부', 200000, 30000, 230000, 253000, '컬러 선명하게'),
(1001, 'ORD_20250802_143052_A1B2', 'namecard', '일반명함', 
 '{"MY_type":"275","MY_Fsd":"993","POtype":"1"}', 
 '1000매', 120000, 0, 120000, 132000, NULL);

-- 5. 조회 쿼리 예시
-- 주문 마스터 + 상세 조회
SELECT 
    m.no, m.order_group_id, m.name, m.email, m.phone,
    m.total_items, m.total_amount_vat, m.order_status, m.date,
    i.product_type, i.product_name, i.quantity, i.item_price_vat, i.special_requests
FROM MlangOrder_PrintAuto m
LEFT JOIN mlangorder_printauto_items i ON m.no = i.order_no
WHERE m.order_group_id = 'ORD_20250802_143052_A1B2'
ORDER BY i.item_id;

-- 6. 주문 상태 업데이트
UPDATE MlangOrder_PrintAuto 
SET order_status = 'confirmed' 
WHERE order_group_id = 'ORD_20250802_143052_A1B2';