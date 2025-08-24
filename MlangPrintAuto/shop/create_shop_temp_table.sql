-- 통합 임시 장바구니 테이블 생성
-- 기존 shop_temp 테이블이 있다면 백업 후 삭제하고 새로 생성

-- 기존 테이블 백업 (선택사항)
-- CREATE TABLE shop_temp_backup AS SELECT * FROM shop_temp;

-- 기존 테이블 삭제 (주의: 데이터 손실 가능)
-- DROP TABLE IF EXISTS shop_temp;

-- 새로운 통합 shop_temp 테이블 생성
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='통합 임시 장바구니 테이블';

-- 테이블 생성 완료 메시지
SELECT '통합 shop_temp 테이블이 성공적으로 생성되었습니다.' as message;