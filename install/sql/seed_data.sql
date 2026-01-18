-- ============================================================
-- Duson Print System - Seed Data
-- Version: 2.0.0
-- Created: 2026-01-18
-- Essential initial data for system operation
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE `dsp1830`;

-- ============================================================
-- 1. ADMIN USER (Default Administrator)
-- Password: admin1234 (should be changed immediately after install)
-- ============================================================
INSERT INTO `users` (`username`, `password`, `is_admin`, `name`, `email`, `level`, `created_at`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'System Admin', 'admin@example.com', '1', NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ============================================================
-- 2. UNIT CODES (Quantity Unit Reference)
-- ============================================================
INSERT INTO `unit_codes` (`code`, `name_ko`, `name_en`, `description`) VALUES
('R', '연', 'Ream', '전단지/리플렛 - 500매 = 1연'),
('S', '매', 'Sheet', '스티커/명함/봉투/포스터/자석스티커/상품권'),
('B', '부', 'Bundle', '카다록'),
('V', '권', 'Volume', 'NCR양식지'),
('P', '장', 'Piece', '개별 인쇄물'),
('E', '개', 'Each', '기타/커스텀 제품')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- ============================================================
-- 3. LEAFLET FOLD OPTIONS
-- ============================================================
INSERT INTO `mlangprintauto_leaflet_fold` (`fold_type`, `fold_name`, `fold_price`, `sort_order`, `is_active`) VALUES
('2fold', '2단접지', 40000, 1, 1),
('3fold', '3단접지', 40000, 2, 1),
('4fold', '4단접지', 80000, 3, 1),
('accordion', '병풍접지', 80000, 4, 1),
('gate', '대문접지', 100000, 5, 1),
('zfold', 'Z접지', 60000, 6, 1)
ON DUPLICATE KEY UPDATE `fold_price` = VALUES(`fold_price`);

-- ============================================================
-- 4. ADDITIONAL OPTIONS CONFIG
-- ============================================================
-- Coating options for leaflets
INSERT INTO `additional_options_config` (`product_type`, `option_type`, `option_name`, `option_value`, `price`, `is_active`) VALUES
('inserted', 'coating', '단면유광코팅', 'single_gloss', 80000, 1),
('inserted', 'coating', '양면유광코팅', 'double_gloss', 160000, 1),
('inserted', 'coating', '단면무광코팅', 'single_matte', 80000, 1),
('inserted', 'coating', '양면무광코팅', 'double_matte', 160000, 1),
('inserted', 'creasing', '오시 1줄', '1', 32000, 1),
('inserted', 'creasing', '오시 2줄', '2', 32000, 1),
('inserted', 'creasing', '오시 3줄', '3', 40000, 1)
ON DUPLICATE KEY UPDATE `price` = VALUES(`price`);

-- ============================================================
-- 5. CATEGORY MAPPING (mlangprintauto_transactioncate)
-- Essential product categories
-- ============================================================

-- Sticker categories
INSERT INTO `mlangprintauto_transactioncate` (`no`, `Ttable`, `BigNo`, `title`, `TreeNo`) VALUES
(260, 'sticker', '0', '일반사각형', ''),
(261, 'sticker', '260', '50X50mm익일배송', ''),
(263, 'sticker', '260', '80X50mm익일배송', ''),
(264, 'sticker', '260', '90X55mm익일배송', ''),
(265, 'sticker', '260', '90X60mm익일배송', ''),
(301, 'sticker', '0', '원형스티커', ''),
(302, 'sticker', '0', '타원형스티커', ''),
(332, 'sticker', '301', '50X50mm', ''),
(334, 'sticker', '301', '60X60mm', ''),
(336, 'sticker', '301', '70X70mm', ''),
(338, 'sticker', '301', '80X80mm', ''),
(340, 'sticker', '301', '90X90mm', ''),
(341, 'sticker', '301', '100X100mm', '')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- Namecard categories
INSERT INTO `mlangprintauto_transactioncate` (`no`, `Ttable`, `BigNo`, `title`, `TreeNo`) VALUES
(275, 'NameCard', '0', '일반명함(쿠폰)', ''),
(276, 'NameCard', '275', '칼라코팅', ''),
(277, 'NameCard', '275', '칼라비코팅', ''),
(278, 'NameCard', '0', '고급수입지', ''),
(279, 'NameCard', '278', '휘라레216g(라레216g)', ''),
(280, 'NameCard', '278', '누브지210g(누브210g)', ''),
(438, 'NameCard', '278', '그레이스-256g', ''),
(439, 'NameCard', '278', '머쉬멜로우209g(머쉬209g)', ''),
(445, 'NameCard', '278', '스타드림240g(스타240g)', ''),
(448, 'NameCard', '278', '키칼라메탈릭200g', '')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- Envelope categories
INSERT INTO `mlangprintauto_transactioncate` (`no`, `Ttable`, `BigNo`, `title`, `TreeNo`) VALUES
(282, 'envelope', '0', '소봉투', ''),
(283, 'envelope', '282', '소봉투(100모조 220*105)', ''),
(284, 'envelope', '282', '레자크(100g 220*105)', ''),
(285, 'envelope', '282', '쟈켓소봉투(100모조 220*105)', ''),
(466, 'envelope', '0', '대봉투', ''),
(473, 'envelope', '466', '대봉투330*243(120g모조)', ''),
(474, 'envelope', '466', '대봉투330*243(110g레자크)', '')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- NCR Form categories
INSERT INTO `mlangprintauto_transactioncate` (`no`, `Ttable`, `BigNo`, `title`, `TreeNo`) VALUES
(475, 'NcrFlambeau', '0', '양식(100매철)', ''),
(476, 'NcrFlambeau', '0', 'NCR 2매(100매철)', ''),
(477, 'NcrFlambeau', '0', 'NCR 3매(150매철)', ''),
(484, 'NcrFlambeau', '475', '계약서(A4).기타서식(A4)', ''),
(485, 'NcrFlambeau', '475', '16절', ''),
(486, 'NcrFlambeau', '475', 'A5', ''),
(487, 'NcrFlambeau', '475', '거래명세표 각종서식 (32절)', ''),
(489, 'NcrFlambeau', '475', '빌지, 영수증', ''),
(505, 'NcrFlambeau', '', '1도', '475'),
(506, 'NcrFlambeau', '', '2도', '475'),
(511, 'NcrFlambeau', '', '1도', '476'),
(512, 'NcrFlambeau', '', '2도', '476'),
(513, 'NcrFlambeau', '', '1도', '477'),
(514, 'NcrFlambeau', '', '2도', '477')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- ============================================================
-- 6. SAMPLE PRICE DATA
-- Basic price entries for each product type
-- ============================================================

-- Leaflet sample prices (전단지)
INSERT INTO `mlangprintauto_inserted` (`MY_type`, `MY_type_name`, `PN_type`, `MY_Fsd`, `MY_Fsd_name`, `MY_amount`, `MY_money`, `MY_moneyTwo`, `POtype`, `TreeSelect`, `DesignMoney`) VALUES
(1, '90g아트지(합판인쇄)', 'A4', 0, '무절', 0.5, 49000, 54000, '1', 0, 10000),
(1, '90g아트지(합판인쇄)', 'A4', 0, '무절', 1.0, 52000, 64000, '1', 0, 10000),
(1, '90g아트지(합판인쇄)', 'A4', 0, '무절', 2.0, 75000, 84000, '1', 0, 10000),
(1, '90g아트지(합판인쇄)', 'A4', 0, '무절', 3.0, 98000, 108000, '1', 0, 10000),
(1, '90g아트지(합판인쇄)', 'A4', 0, '무절', 5.0, 140000, 150000, '1', 0, 10000),
(1, '90g아트지(합판인쇄)', 'B5', 0, '무절', 0.5, 40000, 50000, '1', 0, 10000),
(1, '90g아트지(합판인쇄)', 'B5', 0, '무절', 1.0, 48000, 55000, '1', 0, 10000),
(2, '120g스노우지(합판인쇄)', 'A4', 0, '무절', 0.5, 55000, 60000, '1', 0, 10000),
(2, '120g스노우지(합판인쇄)', 'A4', 0, '무절', 1.0, 60000, 72000, '1', 0, 10000)
ON DUPLICATE KEY UPDATE `MY_money` = VALUES(`MY_money`);

-- Namecard sample prices (명함)
INSERT INTO `mlangprintauto_namecard` (`MY_type`, `MY_type_name`, `Section`, `Section_name`, `POtype`, `MY_amount`, `MY_money`, `DesignMoney`) VALUES
(275, '일반명함(쿠폰)', 276, '칼라코팅', 1, 200, 9000, 10000),
(275, '일반명함(쿠폰)', 276, '칼라코팅', 1, 500, 9000, 10000),
(275, '일반명함(쿠폰)', 276, '칼라코팅', 1, 1000, 15000, 10000),
(275, '일반명함(쿠폰)', 276, '칼라코팅', 2, 200, 13000, 10000),
(275, '일반명함(쿠폰)', 276, '칼라코팅', 2, 500, 13000, 10000),
(275, '일반명함(쿠폰)', 277, '칼라비코팅', 1, 200, 9000, 10000),
(275, '일반명함(쿠폰)', 277, '칼라비코팅', 1, 500, 9000, 10000),
(278, '고급수입지', 279, '휘라레216g', 1, 200, 13000, 10000),
(278, '고급수입지', 280, '누브지210g', 1, 200, 13000, 10000)
ON DUPLICATE KEY UPDATE `MY_money` = VALUES(`MY_money`);

-- Envelope sample prices (봉투)
INSERT INTO `mlangprintauto_envelope` (`MY_type`, `MY_type_name`, `Section`, `Section_name`, `POtype`, `MY_amount`, `MY_money`, `DesignMoney`) VALUES
(282, '소봉투', 283, '소봉투(100모조 220*105)', 1, 500, 30000, 10000),
(282, '소봉투', 283, '소봉투(100모조 220*105)', 1, 1000, 35000, 10000),
(282, '소봉투', 283, '소봉투(100모조 220*105)', 1, 2000, 55000, 10000),
(282, '소봉투', 284, '레자크(100g 220*105)', 1, 500, 35000, 10000),
(282, '소봉투', 284, '레자크(100g 220*105)', 1, 1000, 40000, 10000),
(466, '대봉투', 473, '대봉투330*243(120g모조)', 1, 500, 80000, 10000),
(466, '대봉투', 473, '대봉투330*243(120g모조)', 1, 1000, 110000, 10000)
ON DUPLICATE KEY UPDATE `MY_money` = VALUES(`MY_money`);

-- Sticker sample prices (스티커)
INSERT INTO `mlangprintauto_sticker` (`MY_type`, `MY_type_name`, `garo`, `sero`, `mesu`, `MY_money`, `domusong`) VALUES
(1, '아트지코팅', 50, 50, 500, 25000, '00000'),
(1, '아트지코팅', 50, 50, 1000, 30000, '00000'),
(1, '아트지코팅', 60, 60, 500, 28000, '00000'),
(1, '아트지코팅', 60, 60, 1000, 33000, '00000'),
(1, '아트지코팅', 90, 55, 500, 30000, '00000'),
(1, '아트지코팅', 90, 55, 1000, 35000, '00000'),
(2, '아트지비코팅', 50, 50, 500, 23000, '00000'),
(2, '아트지비코팅', 50, 50, 1000, 28000, '00000'),
(3, '유포지', 50, 50, 500, 35000, '00000'),
(3, '유포지', 50, 50, 1000, 45000, '00000')
ON DUPLICATE KEY UPDATE `MY_money` = VALUES(`MY_money`);

-- ============================================================
-- 7. CHAT STAFF (Default Staff)
-- ============================================================
INSERT INTO `chatstaff` (`staffid`, `staffname`, `email`, `isonline`) VALUES
('staff1', '상담직원1', 'staff1@dsp1830.shop', 1),
('staff2', '상담직원2', 'staff2@dsp1830.shop', 1),
('staff3', '상담직원3', 'staff3@dsp1830.shop', 1)
ON DUPLICATE KEY UPDATE `staffname` = VALUES(`staffname`);

-- ============================================================
-- RESTORE FOREIGN KEY CHECKS
-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SEED DATA INSTALLATION COMPLETE
-- ============================================================
SELECT 'Duson Print System seed data installation complete!' AS message;
SELECT
  (SELECT COUNT(*) FROM users) as users_count,
  (SELECT COUNT(*) FROM unit_codes) as unit_codes_count,
  (SELECT COUNT(*) FROM mlangprintauto_transactioncate) as categories_count,
  (SELECT COUNT(*) FROM mlangprintauto_leaflet_fold) as fold_options_count,
  (SELECT COUNT(*) FROM additional_options_config) as options_config_count;
