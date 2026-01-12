-- Phase 2: 결제/알림/배송 시스템 마이그레이션
-- 생성일: 2026-01-13
-- 설명: 결제 게이트웨이, 알림 서비스, 배송 추적을 위한 테이블 생성

-- =====================================================
-- 1. payments 테이블 (결제 정보)
-- =====================================================
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL COMMENT '주문번호 (mlangorder_printauto.no)',
    `payment_method` VARCHAR(50) NOT NULL COMMENT '결제수단: bank_transfer, naverpay, kakaopay, card',
    `amount` DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT '결제금액 (VAT 미포함)',
    `amount_vat` DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT '결제금액 (VAT 포함)',
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT '상태: pending, paid, cancelled, refunded, failed',
    `external_provider` VARCHAR(50) NULL COMMENT '외부 결제사: naverpay, kakaopay',
    `external_id` VARCHAR(255) NULL COMMENT '외부 결제 ID (TID, reserveId 등)',
    `meta` TEXT NULL COMMENT '추가 메타데이터 (JSON)',
    `bank_info` TEXT NULL COMMENT '무통장입금 계좌정보 (JSON)',
    `deposit_deadline` DATETIME NULL COMMENT '입금 기한',
    `depositor_name` VARCHAR(100) NULL COMMENT '입금자명',
    `approved_at` DATETIME NULL COMMENT '승인 일시',
    `cancelled_at` DATETIME NULL COMMENT '취소 일시',
    `cancel_reason` TEXT NULL COMMENT '취소 사유',
    `confirmed_by` INT(11) NULL COMMENT '입금확인 관리자 ID',
    `payment_result` TEXT NULL COMMENT '결제 결과 (JSON)',
    `created_at` DATETIME NOT NULL COMMENT '생성일시',
    `updated_at` DATETIME NOT NULL COMMENT '수정일시',
    PRIMARY KEY (`id`),
    INDEX `idx_order_id` (`order_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_payment_method` (`payment_method`),
    INDEX `idx_external_id` (`external_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='결제 정보';

-- =====================================================
-- 2. notification_logs 테이블 (알림 로그)
-- =====================================================
CREATE TABLE IF NOT EXISTS `notification_logs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NULL COMMENT '관련 주문번호',
    `notification_type` VARCHAR(50) NOT NULL COMMENT '알림 타입: kakao_alimtalk, sms, email',
    `template_code` VARCHAR(100) NULL COMMENT '템플릿 코드',
    `recipient` VARCHAR(100) NOT NULL COMMENT '수신자 (전화번호/이메일)',
    `message` TEXT NOT NULL COMMENT '메시지 내용',
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT '상태: pending, sent, failed',
    `result` TEXT NULL COMMENT '발송 결과 (JSON)',
    `created_at` DATETIME NOT NULL COMMENT '생성일시',
    `sent_at` DATETIME NULL COMMENT '발송일시',
    PRIMARY KEY (`id`),
    INDEX `idx_order_id` (`order_id`),
    INDEX `idx_notification_type` (`notification_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='알림 발송 로그';

-- =====================================================
-- 3. shipping_info 테이블 (배송 정보)
-- =====================================================
CREATE TABLE IF NOT EXISTS `shipping_info` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL COMMENT '주문번호',
    `courier_code` VARCHAR(20) NOT NULL COMMENT '택배사 코드: cj, hanjin, lotte, logen, post',
    `tracking_number` VARCHAR(50) NOT NULL COMMENT '운송장 번호',
    `status` VARCHAR(30) NOT NULL DEFAULT 'ready' COMMENT '상태: ready, picked_up, in_transit, out_for_delivery, delivered, failed',
    `created_at` DATETIME NOT NULL COMMENT '생성일시',
    `updated_at` DATETIME NOT NULL COMMENT '수정일시',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_order` (`order_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_tracking` (`courier_code`, `tracking_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='배송 정보';

-- =====================================================
-- 4. tracking_cache 테이블 (배송 조회 캐시)
-- =====================================================
CREATE TABLE IF NOT EXISTS `tracking_cache` (
    `cache_key` VARCHAR(100) NOT NULL COMMENT '캐시 키',
    `data` TEXT NOT NULL COMMENT '캐시 데이터 (JSON)',
    `created_at` DATETIME NOT NULL COMMENT '생성일시',
    PRIMARY KEY (`cache_key`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='배송 조회 캐시';

-- =====================================================
-- 5. mlangorder_printauto 테이블 컬럼 추가
-- =====================================================
-- 결제 상태 컬럼 추가
ALTER TABLE `mlangorder_printauto`
    ADD COLUMN IF NOT EXISTS `payment_status` VARCHAR(20) NULL DEFAULT 'pending' COMMENT '결제상태' AFTER `money_5`,
    ADD COLUMN IF NOT EXISTS `payment_method` VARCHAR(50) NULL COMMENT '결제수단' AFTER `payment_status`;

-- 배송 관련 컬럼 추가
ALTER TABLE `mlangorder_printauto`
    ADD COLUMN IF NOT EXISTS `courier` VARCHAR(50) NULL COMMENT '택배사' AFTER `delivery`,
    ADD COLUMN IF NOT EXISTS `tracking_number` VARCHAR(50) NULL COMMENT '운송장번호' AFTER `courier`,
    ADD COLUMN IF NOT EXISTS `ship_status` VARCHAR(30) NULL DEFAULT 'pending' COMMENT '배송상태' AFTER `tracking_number`,
    ADD COLUMN IF NOT EXISTS `delivery_date` DATETIME NULL COMMENT '배송완료일' AFTER `ship_status`;

-- 인덱스 추가
ALTER TABLE `mlangorder_printauto`
    ADD INDEX IF NOT EXISTS `idx_payment_status` (`payment_status`),
    ADD INDEX IF NOT EXISTS `idx_ship_status` (`ship_status`);

-- =====================================================
-- 6. 캐시 테이블 정리 이벤트 (선택사항)
-- =====================================================
-- MySQL Event Scheduler가 활성화되어 있어야 합니다
-- SET GLOBAL event_scheduler = ON;

DELIMITER //

CREATE EVENT IF NOT EXISTS `cleanup_tracking_cache`
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    DELETE FROM `tracking_cache` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 1 HOUR);
END//

DELIMITER ;

-- =====================================================
-- 완료 메시지
-- =====================================================
SELECT 'Phase 2 마이그레이션 완료: payments, notification_logs, shipping_info, tracking_cache 테이블 생성됨' AS result;
