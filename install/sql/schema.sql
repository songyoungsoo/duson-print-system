-- ============================================================
-- Duson Print System - Database Schema
-- Version: 2.0.0
-- Created: 2026-01-18
-- Encoding: utf8mb4
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ============================================================
-- Database Creation
-- ============================================================
CREATE DATABASE IF NOT EXISTS `dsp1830`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `dsp1830`;

-- ============================================================
-- 1. USERS TABLE (Central Authentication)
-- ============================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL COMMENT 'Login ID',
  `password` VARCHAR(255) NOT NULL COMMENT 'Hashed password',
  `is_admin` TINYINT(1) DEFAULT 0 COMMENT '1 for admin users',
  `name` VARCHAR(100) NOT NULL COMMENT 'Display name',
  `email` VARCHAR(200) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `postcode` VARCHAR(20) DEFAULT NULL,
  `address` VARCHAR(200) DEFAULT NULL,
  `detail_address` VARCHAR(200) DEFAULT NULL,
  `extra_address` VARCHAR(200) DEFAULT NULL,
  `business_number` VARCHAR(50) DEFAULT NULL COMMENT 'Business registration number',
  `business_name` VARCHAR(100) DEFAULT NULL COMMENT 'Company name',
  `business_owner` VARCHAR(100) DEFAULT NULL COMMENT 'Owner name',
  `business_type` VARCHAR(100) DEFAULT NULL COMMENT 'Business type',
  `business_item` VARCHAR(100) DEFAULT NULL COMMENT 'Business item',
  `business_address` VARCHAR(300) DEFAULT NULL COMMENT 'Business address',
  `tax_invoice_email` VARCHAR(200) DEFAULT NULL COMMENT 'Tax invoice email',
  `level` VARCHAR(10) DEFAULT '5' COMMENT '1=admin, 2=manager, 5=regular',
  `login_count` INT DEFAULT 0,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `migrated_from_member` TINYINT(1) DEFAULT 0,
  `original_member_no` INT DEFAULT NULL,
  `business_cert_path` VARCHAR(255) DEFAULT NULL COMMENT 'Business cert file path',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User accounts';

-- ============================================================
-- 2. MEMBER TABLE (Legacy - Backwards Compatibility)
-- ============================================================
DROP TABLE IF EXISTS `member`;
CREATE TABLE `member` (
  `no` MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id` VARCHAR(20) NOT NULL DEFAULT '',
  `pass` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Legacy password field',
  `name` VARCHAR(40) NOT NULL DEFAULT '',
  `phone1` VARCHAR(10) DEFAULT NULL,
  `phone2` VARCHAR(10) DEFAULT NULL,
  `phone3` VARCHAR(10) DEFAULT NULL,
  `hendphone1` VARCHAR(10) DEFAULT NULL,
  `hendphone2` VARCHAR(10) DEFAULT NULL,
  `hendphone3` VARCHAR(10) DEFAULT NULL,
  `email` VARCHAR(200) NOT NULL DEFAULT '',
  `sample6_postcode` VARCHAR(100) NOT NULL DEFAULT '',
  `sample6_address` VARCHAR(100) NOT NULL DEFAULT '',
  `sample6_detailAddress` VARCHAR(100) NOT NULL DEFAULT '',
  `sample6_extraAddress` VARCHAR(100) NOT NULL DEFAULT '',
  `po1` VARCHAR(100) DEFAULT NULL COMMENT 'Business Number',
  `po2` VARCHAR(100) DEFAULT NULL COMMENT 'Company Name',
  `po3` VARCHAR(100) DEFAULT NULL COMMENT 'Owner Name',
  `po4` VARCHAR(100) DEFAULT NULL COMMENT 'Business Type',
  `po5` VARCHAR(100) DEFAULT NULL COMMENT 'Business Item',
  `po6` VARCHAR(100) DEFAULT NULL COMMENT 'Business Address',
  `po7` VARCHAR(100) DEFAULT NULL,
  `connent` TEXT,
  `date` DATETIME DEFAULT NULL,
  `level` VARCHAR(10) NOT NULL DEFAULT '1',
  `Logincount` INT(11) NOT NULL DEFAULT 0,
  `EndLogin` DATETIME DEFAULT NULL,
  PRIMARY KEY (`no`),
  UNIQUE KEY `uk_id` (`id`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Legacy member table';

-- ============================================================
-- 3. ORDER TABLE (Main Orders)
-- ============================================================
DROP TABLE IF EXISTS `mlangorder_printauto`;
CREATE TABLE `mlangorder_printauto` (
  `no` INT(11) NOT NULL AUTO_INCREMENT,
  `Type` VARCHAR(100) DEFAULT NULL COMMENT 'Order type',
  `ImgFolder` TEXT COMMENT 'Image folder path',
  `uploaded_files` TEXT COMMENT 'Uploaded files JSON',
  `Type_1` TEXT COMMENT 'Product details JSON',
  `mesu` VARCHAR(100) DEFAULT NULL COMMENT 'Quantity display',
  `money_1` INT(11) DEFAULT 0,
  `money_2` INT(11) DEFAULT 0,
  `money_3` INT(11) DEFAULT 0,
  `money_4` INT(11) DEFAULT 0 COMMENT 'Price (excl VAT)',
  `money_5` INT(11) DEFAULT 0 COMMENT 'Price (incl VAT)',
  `payment_status` VARCHAR(20) DEFAULT 'pending' COMMENT 'pending, paid, cancelled, refunded',
  `payment_method` VARCHAR(50) DEFAULT NULL COMMENT 'bank_transfer, card, naverpay, etc',
  `name` VARCHAR(100) DEFAULT NULL COMMENT 'Customer name',
  `email` VARCHAR(150) DEFAULT NULL COMMENT 'Customer email',
  `zip` VARCHAR(20) DEFAULT NULL COMMENT 'Postcode',
  `zip1` TEXT COMMENT 'Address 1',
  `zip2` TEXT COMMENT 'Address 2',
  `phone` VARCHAR(50) DEFAULT NULL COMMENT 'Phone',
  `Hendphone` VARCHAR(50) DEFAULT NULL COMMENT 'Mobile',
  `delivery` VARCHAR(100) DEFAULT NULL COMMENT 'Delivery method',
  `courier` VARCHAR(50) DEFAULT NULL COMMENT 'Courier company',
  `tracking_number` VARCHAR(50) DEFAULT NULL COMMENT 'Tracking number',
  `ship_status` VARCHAR(30) DEFAULT 'pending' COMMENT 'ready, shipped, delivered',
  `delivery_date` DATETIME DEFAULT NULL COMMENT 'Delivery date',
  `bizname` TEXT COMMENT 'Business name',
  `bank` VARCHAR(100) DEFAULT NULL COMMENT 'Bank name',
  `bankname` VARCHAR(100) DEFAULT NULL COMMENT 'Depositor name',
  `cont` TEXT COMMENT 'Customer notes',
  `date` DATETIME DEFAULT NULL COMMENT 'Order date',
  `regdate` INT DEFAULT NULL COMMENT 'Registration timestamp',
  `OrderStyle` VARCHAR(10) DEFAULT '2' COMMENT '1:new, 2:confirmed, 3:processing, 4:completed',
  `ThingCate` VARCHAR(255) DEFAULT NULL COMMENT 'Product category',
  `product_type` VARCHAR(50) DEFAULT NULL COMMENT 'Product type',
  `is_custom_product` TINYINT(1) DEFAULT 0 COMMENT 'Custom product flag',
  `pass` VARCHAR(100) DEFAULT NULL COMMENT 'Order password',
  `Gensu` VARCHAR(50) DEFAULT NULL COMMENT 'Confirmed date',
  `Designer` VARCHAR(100) DEFAULT NULL COMMENT 'Designer name',
  `PMmember` VARCHAR(100) DEFAULT NULL COMMENT 'PM member',
  -- Coating options
  `coating_enabled` TINYINT(1) DEFAULT 0,
  `coating_type` VARCHAR(20) DEFAULT NULL,
  `coating_price` INT DEFAULT 0,
  -- Folding options
  `folding_enabled` TINYINT(1) DEFAULT 0,
  `folding_type` VARCHAR(20) DEFAULT NULL,
  `folding_price` INT DEFAULT 0,
  -- Creasing options
  `creasing_enabled` TINYINT(1) DEFAULT 0,
  `creasing_lines` INT DEFAULT 0,
  `creasing_price` INT DEFAULT 0,
  `additional_options_total` INT DEFAULT 0,
  -- Premium options
  `premium_options` TEXT,
  `premium_options_total` INT DEFAULT 0,
  -- Envelope tape
  `tape_enabled` TINYINT(1) DEFAULT 0,
  `tape_quantity` VARCHAR(20) DEFAULT NULL,
  `tape_price` INT DEFAULT 0,
  PRIMARY KEY (`no`),
  KEY `idx_date` (`date`),
  KEY `idx_email` (`email`),
  KEY `idx_order_style` (`OrderStyle`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_ship_status` (`ship_status`),
  KEY `idx_product_type` (`product_type`)
) ENGINE=InnoDB AUTO_INCREMENT=100001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Main order table';

-- ============================================================
-- 4. SHOPPING CART TABLE
-- ============================================================
DROP TABLE IF EXISTS `shop_temp`;
CREATE TABLE `shop_temp` (
  `no` INT(11) NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(100) NOT NULL COMMENT 'Session ID',
  `order_id` VARCHAR(50) DEFAULT NULL COMMENT 'Order ID (on checkout)',
  `parent` VARCHAR(50) DEFAULT NULL,
  `product_type` VARCHAR(50) NOT NULL DEFAULT 'sticker' COMMENT 'Product type',
  -- Sticker specific fields
  `jong` VARCHAR(200) DEFAULT NULL COMMENT 'Sticker material type',
  `garo` VARCHAR(50) DEFAULT NULL COMMENT 'Width (mm)',
  `sero` VARCHAR(50) DEFAULT NULL COMMENT 'Height (mm)',
  `mesu` VARCHAR(50) DEFAULT NULL COMMENT 'Quantity',
  `domusong` VARCHAR(200) DEFAULT NULL COMMENT 'Die-cut option code',
  `uhyung` INT(1) DEFAULT 0 COMMENT '0:print only, 1:design+print',
  -- Common category mapping
  `MY_type` VARCHAR(50) DEFAULT NULL COMMENT 'Type/material code',
  `MY_Fsd` VARCHAR(50) DEFAULT NULL COMMENT 'Fold/style code',
  `PN_type` VARCHAR(50) DEFAULT NULL COMMENT 'Size code',
  `Section` VARCHAR(50) DEFAULT NULL COMMENT 'Section code',
  `TreeSelect` VARCHAR(50) DEFAULT NULL,
  `MY_amount` DECIMAL(10,2) DEFAULT NULL COMMENT 'Amount value',
  `unit` VARCHAR(10) DEFAULT 'pcs' COMMENT 'Unit (sheets, pcs, bundles)',
  `POtype` VARCHAR(10) DEFAULT NULL COMMENT 'Print sides (1:single, 2:double)',
  `ordertype` VARCHAR(50) DEFAULT NULL COMMENT 'print, design, total',
  -- Price
  `st_price` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Price (excl VAT)',
  `st_price_vat` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Price (incl VAT)',
  -- Upload info
  `MY_comment` TEXT DEFAULT NULL COMMENT 'Customer notes',
  `work_memo` TEXT DEFAULT NULL COMMENT 'Work memo',
  `img` VARCHAR(200) DEFAULT NULL COMMENT 'Image filename',
  `upload_method` VARCHAR(20) DEFAULT 'upload',
  `uploaded_files_info` TEXT COMMENT 'Upload info',
  `upload_folder` VARCHAR(255) DEFAULT NULL,
  `uploaded_files` TEXT COMMENT 'Uploaded files JSON',
  `ThingCate` VARCHAR(255) DEFAULT NULL COMMENT 'Category path',
  `ImgFolder` VARCHAR(255) DEFAULT NULL COMMENT 'Image folder path',
  -- Coating options
  `coating_enabled` TINYINT(1) DEFAULT 0,
  `coating_type` VARCHAR(20) DEFAULT NULL,
  `coating_price` INT DEFAULT 0,
  -- Folding options
  `folding_enabled` TINYINT(1) DEFAULT 0,
  `folding_type` VARCHAR(20) DEFAULT NULL,
  `folding_price` INT DEFAULT 0,
  -- Creasing options
  `creasing_enabled` TINYINT(1) DEFAULT 0,
  `creasing_lines` INT DEFAULT 0,
  `creasing_price` INT DEFAULT 0,
  `additional_options_total` INT DEFAULT 0,
  -- Selected options
  `selected_options` TEXT COMMENT 'Selected options JSON',
  -- Premium options
  `premium_options` TEXT COMMENT 'Premium options JSON',
  `premium_options_total` INT DEFAULT 0,
  -- Envelope tape options
  `envelope_tape_enabled` TINYINT(1) DEFAULT 0,
  `envelope_tape_quantity` INT DEFAULT 0,
  `envelope_tape_price` INT DEFAULT 0,
  `envelope_additional_options_total` INT DEFAULT 0,
  -- Display names (for UI)
  `MY_type_name` VARCHAR(100) DEFAULT NULL,
  `Section_name` VARCHAR(100) DEFAULT NULL,
  `POtype_name` VARCHAR(50) DEFAULT NULL,
  `customer_name` VARCHAR(100) DEFAULT NULL,
  `customer_phone` VARCHAR(50) DEFAULT NULL,
  `additional_options` TEXT,
  `original_filename` VARCHAR(255) DEFAULT NULL,
  `regdate` INT DEFAULT NULL COMMENT 'Registration timestamp',
  PRIMARY KEY (`no`),
  KEY `idx_session` (`session_id`),
  KEY `idx_product_type` (`product_type`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_regdate` (`regdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Shopping cart';

-- ============================================================
-- 5. QUOTATION TEMP (Clone of shop_temp)
-- ============================================================
DROP TABLE IF EXISTS `quotation_temp`;
CREATE TABLE `quotation_temp` LIKE `shop_temp`;
ALTER TABLE `quotation_temp` COMMENT = 'Quotation cart';

-- ============================================================
-- 6. CATEGORY MAPPING TABLE
-- ============================================================
DROP TABLE IF EXISTS `mlangprintauto_transactioncate`;
CREATE TABLE `mlangprintauto_transactioncate` (
  `no` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `Ttable` VARCHAR(250) DEFAULT NULL COMMENT 'Product table name',
  `BigNo` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Parent category ID',
  `title` VARCHAR(250) DEFAULT NULL COMMENT 'Category title',
  `TreeNo` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Tree hierarchy',
  PRIMARY KEY (`no`),
  KEY `idx_ttable` (`Ttable`),
  KEY `idx_bigno` (`BigNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Category mapping';

-- ============================================================
-- 7. LEAFLET PRICE TABLE (전단지)
-- ============================================================
DROP TABLE IF EXISTS `mlangprintauto_inserted`;
CREATE TABLE `mlangprintauto_inserted` (
  `no` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `MY_type` INT DEFAULT NULL COMMENT 'Paper type code',
  `MY_type_name` VARCHAR(100) DEFAULT NULL COMMENT 'Paper name',
  `PN_type` VARCHAR(20) DEFAULT NULL COMMENT 'Size (A4, B5, etc)',
  `MY_Fsd` INT DEFAULT NULL COMMENT 'Fold count code',
  `MY_Fsd_name` VARCHAR(50) DEFAULT NULL COMMENT 'Fold name',
  `MY_amount` DECIMAL(10,2) DEFAULT NULL COMMENT 'Ream count',
  `MY_money` INT DEFAULT 0 COMMENT 'Single-sided price',
  `MY_moneyTwo` INT DEFAULT 0 COMMENT 'Double-sided price',
  `POtype` VARCHAR(10) DEFAULT NULL COMMENT 'Print sides',
  `TreeSelect` INT DEFAULT 0,
  `DesignMoney` INT NOT NULL DEFAULT 10000 COMMENT 'Design fee',
  PRIMARY KEY (`no`),
  KEY `idx_type` (`MY_type`, `PN_type`, `MY_Fsd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Leaflet prices';

-- ============================================================
-- 8. STICKER PRICE TABLE (스티커)
-- ============================================================
DROP TABLE IF EXISTS `mlangprintauto_sticker`;
CREATE TABLE `mlangprintauto_sticker` (
  `no` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `MY_type` INT DEFAULT NULL COMMENT 'Sticker type code',
  `MY_type_name` VARCHAR(100) DEFAULT NULL COMMENT 'Sticker type name',
  `garo` INT DEFAULT NULL COMMENT 'Width (mm)',
  `sero` INT DEFAULT NULL COMMENT 'Height (mm)',
  `mesu` INT DEFAULT NULL COMMENT 'Quantity',
  `MY_money` INT DEFAULT 0 COMMENT 'Price',
  `domusong` VARCHAR(100) DEFAULT NULL COMMENT 'Die-cut option',
  PRIMARY KEY (`no`),
  KEY `idx_type` (`MY_type`),
  KEY `idx_size` (`garo`, `sero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sticker prices';

-- ============================================================
-- 9. NAMECARD PRICE TABLE (명함)
-- ============================================================
DROP TABLE IF EXISTS `mlangprintauto_namecard`;
CREATE TABLE `mlangprintauto_namecard` (
  `no` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `MY_type` INT DEFAULT NULL COMMENT 'Paper type code',
  `MY_type_name` VARCHAR(100) DEFAULT NULL COMMENT 'Paper name',
  `Section` INT DEFAULT NULL COMMENT 'Section code',
  `Section_name` VARCHAR(100) DEFAULT NULL COMMENT 'Section name',
  `POtype` INT DEFAULT NULL COMMENT 'Print sides (1:single, 2:double)',
  `MY_amount` INT DEFAULT NULL COMMENT 'Sheet count',
  `MY_money` INT DEFAULT 0 COMMENT 'Price',
  `DesignMoney` INT DEFAULT 10000 COMMENT 'Design fee',
  PRIMARY KEY (`no`),
  KEY `idx_type` (`MY_type`, `Section`, `POtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Business card prices';

-- ============================================================
-- 10. ENVELOPE PRICE TABLE (봉투)
-- ============================================================
DROP TABLE IF EXISTS `mlangprintauto_envelope`;
CREATE TABLE `mlangprintauto_envelope` (
  `no` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `MY_type` INT DEFAULT NULL COMMENT 'Envelope type code',
  `MY_type_name` VARCHAR(100) DEFAULT NULL COMMENT 'Envelope type name',
  `Section` INT DEFAULT NULL COMMENT 'Size code',
  `Section_name` VARCHAR(100) DEFAULT NULL COMMENT 'Size name',
  `POtype` INT DEFAULT NULL COMMENT 'Print sides',
  `MY_amount` INT DEFAULT NULL COMMENT 'Sheet count',
  `MY_money` INT DEFAULT 0 COMMENT 'Price',
  `DesignMoney` INT DEFAULT 10000 COMMENT 'Design fee',
  PRIMARY KEY (`no`),
  KEY `idx_type` (`MY_type`, `Section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Envelope prices';

-- ============================================================
-- 11. CADAROK PRICE TABLE (카다록)
-- ============================================================
DROP TABLE IF EXISTS `mlangprintauto_cadarok`;
CREATE TABLE `mlangprintauto_cadarok` (
  `no` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `MY_type` INT DEFAULT NULL COMMENT 'Paper type code',
  `MY_type_name` VARCHAR(100) DEFAULT NULL COMMENT 'Paper name',
  `PN_type` VARCHAR(20) DEFAULT NULL COMMENT 'Size',
  `MY_Fsd` INT DEFAULT NULL COMMENT 'Page count',
  `MY_amount` INT DEFAULT NULL COMMENT 'Copy count',
  `MY_money` INT DEFAULT 0 COMMENT 'Price',
  `POtype` VARCHAR(10) DEFAULT NULL COMMENT 'Print sides',
  `DesignMoney` INT DEFAULT 10000 COMMENT 'Design fee',
  PRIMARY KEY (`no`),
  KEY `idx_type` (`MY_type`, `PN_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catalog prices';

-- ============================================================
-- 12. LITTLEPRINT PRICE TABLE (포스터)
-- ============================================================
DROP TABLE IF EXISTS `mlangprintauto_littleprint`;
CREATE TABLE `mlangprintauto_littleprint` (
  `no` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `MY_type` INT DEFAULT NULL COMMENT 'Paper type code',
  `MY_type_name` VARCHAR(100) DEFAULT NULL COMMENT 'Paper name',
  `style` VARCHAR(100) DEFAULT NULL,
  `Section` VARCHAR(200) DEFAULT NULL,
  `PN_type` VARCHAR(20) DEFAULT NULL COMMENT 'Size',
  `MY_amount` INT DEFAULT NULL COMMENT 'Sheet count',
  `money` VARCHAR(200) DEFAULT NULL COMMENT 'Price',
  `MY_money` INT DEFAULT 0 COMMENT 'Price',
  `POtype` VARCHAR(100) DEFAULT NULL COMMENT 'Print sides',
  `TreeSelect` VARCHAR(200) DEFAULT NULL,
  `DesignMoney` VARCHAR(100) DEFAULT NULL COMMENT 'Design fee',
  `quantityTwo` VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`no`),
  KEY `idx_type` (`MY_type`, `PN_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Poster prices';

-- ============================================================
-- 13. MERCHANDISEBOND PRICE TABLE (상품권)
-- ============================================================
DROP TABLE IF EXISTS `mlangprintauto_merchandisebond`;
CREATE TABLE `mlangprintauto_merchandisebond` (
  `no` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `MY_type` INT DEFAULT NULL COMMENT 'Type code',
  `MY_type_name` VARCHAR(100) DEFAULT NULL COMMENT 'Type name',
  `Section` INT DEFAULT NULL COMMENT 'Section code',
  `Section_name` VARCHAR(100) DEFAULT NULL COMMENT 'Section name',
  `POtype` INT DEFAULT NULL COMMENT 'Print sides',
  `MY_amount` INT DEFAULT NULL COMMENT 'Sheet count',
  `MY_money` INT DEFAULT 0 COMMENT 'Price',
  `quantity` INT NOT NULL DEFAULT 0,
  `DesignMoney` INT DEFAULT 10000 COMMENT 'Design fee',
  PRIMARY KEY (`no`),
  KEY `idx_type` (`MY_type`, `Section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gift card prices';

-- ============================================================
-- 14. NCRFLAMBEAU PRICE TABLE (NCR양식지)
-- ============================================================
DROP TABLE IF EXISTS `mlangprintauto_ncrflambeau`;
CREATE TABLE `mlangprintauto_ncrflambeau` (
  `no` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `MY_type` INT DEFAULT NULL COMMENT 'Type code',
  `MY_type_name` VARCHAR(100) DEFAULT NULL COMMENT 'Type name',
  `PN_type` VARCHAR(20) DEFAULT NULL COMMENT 'Size',
  `MY_Fsd` INT DEFAULT NULL COMMENT 'Copy sheet count',
  `MY_amount` INT DEFAULT NULL COMMENT 'Set count',
  `MY_money` INT DEFAULT 0 COMMENT 'Price',
  `DesignMoney` INT DEFAULT 10000 COMMENT 'Design fee',
  PRIMARY KEY (`no`),
  KEY `idx_type` (`MY_type`, `PN_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='NCR form prices';

-- ============================================================
-- 15. MSTICKER PRICE TABLE (자석스티커)
-- ============================================================
DROP TABLE IF EXISTS `mlangprintauto_msticker`;
CREATE TABLE `mlangprintauto_msticker` (
  `no` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `MY_type` INT DEFAULT NULL COMMENT 'Type code',
  `MY_type_name` VARCHAR(100) DEFAULT NULL COMMENT 'Type name',
  `style` VARCHAR(100) DEFAULT NULL,
  `Section` VARCHAR(200) DEFAULT NULL COMMENT 'Size code',
  `Section_name` VARCHAR(100) DEFAULT NULL COMMENT 'Size name',
  `POtype` INT DEFAULT NULL COMMENT 'Print sides',
  `MY_amount` INT DEFAULT NULL COMMENT 'Sheet count',
  `money` VARCHAR(200) DEFAULT NULL,
  `MY_money` INT DEFAULT 0 COMMENT 'Price',
  `DesignMoney` VARCHAR(100) DEFAULT NULL COMMENT 'Design fee',
  PRIMARY KEY (`no`),
  KEY `idx_type` (`MY_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Magnet sticker prices';

-- ============================================================
-- 16. LEAFLET FOLD OPTIONS
-- ============================================================
DROP TABLE IF EXISTS `mlangprintauto_leaflet_fold`;
CREATE TABLE `mlangprintauto_leaflet_fold` (
  `no` INT NOT NULL AUTO_INCREMENT,
  `fold_type` VARCHAR(50) NOT NULL COMMENT 'Fold type code',
  `fold_name` VARCHAR(100) NOT NULL COMMENT 'Fold display name',
  `fold_price` INT DEFAULT 0 COMMENT 'Additional price',
  `sort_order` INT DEFAULT 0 COMMENT 'Sort order',
  `is_active` TINYINT(1) DEFAULT 1 COMMENT 'Active flag',
  PRIMARY KEY (`no`),
  KEY `idx_fold_type` (`fold_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Leaflet fold options';

-- ============================================================
-- 17. ADDITIONAL OPTIONS CONFIG
-- ============================================================
DROP TABLE IF EXISTS `additional_options_config`;
CREATE TABLE `additional_options_config` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `product_type` VARCHAR(50) NOT NULL COMMENT 'Product type',
  `option_type` VARCHAR(50) NOT NULL COMMENT 'Option type',
  `option_name` VARCHAR(100) NOT NULL COMMENT 'Display name',
  `option_value` VARCHAR(100) DEFAULT NULL COMMENT 'Value',
  `price` INT DEFAULT 0 COMMENT 'Additional price',
  `is_active` TINYINT(1) DEFAULT 1 COMMENT 'Active flag',
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_type`, `option_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Additional options config';

-- ============================================================
-- 18. QUOTATIONS TABLE (견적서)
-- ============================================================
DROP TABLE IF EXISTS `quotations`;
CREATE TABLE `quotations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `quotation_no` VARCHAR(50) NOT NULL COMMENT 'Quote number (QT-YYYYMMDD-NNN)',
  `quote_type` VARCHAR(20) DEFAULT 'quotation',
  `public_token` VARCHAR(64) DEFAULT NULL COMMENT 'Public access token',
  `session_id` VARCHAR(100) DEFAULT NULL,
  `customer_name` VARCHAR(100) NOT NULL COMMENT 'Customer name',
  `customer_email` VARCHAR(100) DEFAULT NULL,
  `recipient_email` VARCHAR(100) DEFAULT NULL,
  `customer_phone` VARCHAR(20) DEFAULT NULL,
  `cart_items_json` LONGTEXT COMMENT 'Cart items JSON',
  `delivery_type` VARCHAR(20) DEFAULT NULL COMMENT 'Delivery method',
  `delivery_address` TEXT,
  `delivery_price` INT DEFAULT 0,
  `delivery_vat` INT DEFAULT 0,
  `custom_items_json` TEXT COMMENT 'Custom items JSON',
  `supply_total` INT DEFAULT 0,
  `total_supply` INT DEFAULT 0 COMMENT 'Supply amount total',
  `total_vat` INT DEFAULT 0 COMMENT 'VAT total',
  `discount_amount` INT DEFAULT 0,
  `discount_reason` VARCHAR(255) DEFAULT NULL,
  `grand_total` INT DEFAULT 0,
  `total_price` INT DEFAULT 0 COMMENT 'Grand total (incl VAT)',
  `notes` TEXT COMMENT 'Notes',
  `payment_terms` VARCHAR(100) DEFAULT 'Valid for 7 days',
  `valid_days` INT DEFAULT 7,
  `valid_until` DATE DEFAULT NULL,
  `status` ENUM('draft','sent','accepted','rejected','expired') DEFAULT 'draft',
  `customer_response` ENUM('pending','accepted','rejected','negotiate') DEFAULT 'pending',
  `created_by` INT DEFAULT NULL COMMENT 'Created by user_id',
  `quote_source` VARCHAR(20) DEFAULT 'admin_manual',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_quotation_no` (`quotation_no`),
  KEY `idx_status` (`status`),
  KEY `idx_customer_email` (`customer_email`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quotations';

-- ============================================================
-- 19. QUOTE ITEMS TABLE
-- ============================================================
DROP TABLE IF EXISTS `quote_items`;
CREATE TABLE `quote_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `quote_id` INT(11) NOT NULL COMMENT 'quotations.id reference',
  `item_no` INT(11) DEFAULT 1 COMMENT 'Item sequence',
  `product_type` VARCHAR(50) DEFAULT '' COMMENT 'Product type',
  `product_name` VARCHAR(200) NOT NULL COMMENT 'Product name',
  `specification` TEXT COMMENT 'Specifications (JSON or text)',
  `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  `quantity_display` VARCHAR(50) DEFAULT NULL COMMENT 'Display quantity (e.g., 0.5연 (2,000매))',
  `unit` VARCHAR(10) DEFAULT 'pcs' COMMENT 'Unit',
  `unit_price` DECIMAL(10,2) DEFAULT 0.00,
  `supply_price` INT(11) DEFAULT 0 COMMENT 'Supply amount',
  `vat_amount` INT(11) DEFAULT 0 COMMENT 'VAT amount',
  `total_price` INT(11) DEFAULT 0 COMMENT 'Total (incl VAT)',
  `source_type` ENUM('cart','manual','custom') DEFAULT 'manual',
  `source_id` INT(11) DEFAULT NULL COMMENT 'shop_temp.no reference',
  `source_data` TEXT COMMENT 'Original data snapshot (JSON)',
  `notes` TEXT COMMENT 'Item notes',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_quote_id` (`quote_id`),
  KEY `idx_product_type` (`product_type`),
  CONSTRAINT `fk_quote_items_quote` FOREIGN KEY (`quote_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quote items';

-- ============================================================
-- 20. CHAT SYSTEM TABLES
-- ============================================================
DROP TABLE IF EXISTS `chatrooms`;
CREATE TABLE `chatrooms` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `roomname` VARCHAR(255) NOT NULL COMMENT 'Room name',
  `roomtype` ENUM('admin_user','user_user','group') NOT NULL DEFAULT 'group',
  `createdby` VARCHAR(100) NOT NULL COMMENT 'Creator ID',
  `createdat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updatedat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isactive` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_createdby` (`createdby`),
  KEY `idx_updatedat` (`updatedat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chat rooms';

DROP TABLE IF EXISTS `chatparticipants`;
CREATE TABLE `chatparticipants` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `roomid` INT(11) NOT NULL COMMENT 'Room ID',
  `userid` VARCHAR(100) NOT NULL COMMENT 'User ID',
  `username` VARCHAR(100) NOT NULL COMMENT 'User name',
  `isadmin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Is admin/staff',
  `lastreadat` TIMESTAMP NULL DEFAULT NULL,
  `joinedat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_roomid` (`roomid`),
  KEY `idx_userid` (`userid`),
  CONSTRAINT `fk_chatparticipants_room` FOREIGN KEY (`roomid`) REFERENCES `chatrooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chat participants';

DROP TABLE IF EXISTS `chatmessages`;
CREATE TABLE `chatmessages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `roomid` INT(11) NOT NULL COMMENT 'Room ID',
  `senderid` VARCHAR(100) NOT NULL COMMENT 'Sender ID',
  `sendername` VARCHAR(100) NOT NULL COMMENT 'Sender name',
  `messagetype` ENUM('text','image','file','system') NOT NULL DEFAULT 'text',
  `message` TEXT COMMENT 'Message content',
  `filepath` VARCHAR(500) DEFAULT NULL COMMENT 'File path',
  `filename` VARCHAR(255) DEFAULT NULL COMMENT 'Original filename',
  `filesize` INT(11) DEFAULT NULL COMMENT 'File size (bytes)',
  `isread` TINYINT(1) NOT NULL DEFAULT 0,
  `createdat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_roomid` (`roomid`),
  KEY `idx_createdat` (`createdat`),
  CONSTRAINT `fk_chatmessages_room` FOREIGN KEY (`roomid`) REFERENCES `chatrooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chat messages';

DROP TABLE IF EXISTS `chatstaff`;
CREATE TABLE `chatstaff` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `staffid` VARCHAR(50) NOT NULL COMMENT 'Staff ID',
  `staffname` VARCHAR(50) NOT NULL COMMENT 'Staff name',
  `email` VARCHAR(100) DEFAULT NULL,
  `isonline` TINYINT(1) DEFAULT 1,
  `lastseen` TIMESTAMP NULL DEFAULT NULL,
  `createdat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_staffid` (`staffid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chat staff';

DROP TABLE IF EXISTS `chatsettings`;
CREATE TABLE `chatsettings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `userid` VARCHAR(100) NOT NULL COMMENT 'User ID',
  `isminimized` TINYINT(1) DEFAULT 0,
  `notificationenabled` TINYINT(1) DEFAULT 1,
  `soundenabled` TINYINT(1) DEFAULT 1,
  `createdat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updatedat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chat settings';

-- ============================================================
-- 21. PAYMENTS TABLE
-- ============================================================
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL COMMENT 'Order number',
  `payment_method` VARCHAR(50) NOT NULL COMMENT 'bank_transfer, naverpay, kakaopay, card',
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `amount_vat` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `external_provider` VARCHAR(50) NULL,
  `external_id` VARCHAR(255) NULL COMMENT 'External payment ID',
  `meta` TEXT NULL COMMENT 'Metadata (JSON)',
  `bank_info` TEXT NULL COMMENT 'Bank account info (JSON)',
  `deposit_deadline` DATETIME NULL,
  `depositor_name` VARCHAR(100) NULL,
  `approved_at` DATETIME NULL,
  `cancelled_at` DATETIME NULL,
  `cancel_reason` TEXT NULL,
  `confirmed_by` INT(11) NULL,
  `payment_result` TEXT NULL COMMENT 'Payment result (JSON)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_method` (`payment_method`),
  KEY `idx_external_id` (`external_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payment records';

-- ============================================================
-- 22. SHIPPING INFO TABLE
-- ============================================================
DROP TABLE IF EXISTS `shipping_info`;
CREATE TABLE `shipping_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `courier_code` VARCHAR(20) NOT NULL COMMENT 'cj, hanjin, lotte, logen, post',
  `tracking_number` VARCHAR(50) NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'ready',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order` (`order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_tracking` (`courier_code`, `tracking_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Shipping info';

-- ============================================================
-- 23. NOTIFICATION LOGS TABLE
-- ============================================================
DROP TABLE IF EXISTS `notification_logs`;
CREATE TABLE `notification_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NULL,
  `notification_type` VARCHAR(50) NOT NULL COMMENT 'kakao_alimtalk, sms, email',
  `template_code` VARCHAR(100) NULL,
  `recipient` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `result` TEXT NULL COMMENT 'Send result (JSON)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_notification_type` (`notification_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notification logs';

-- ============================================================
-- 24. TRACKING CACHE TABLE
-- ============================================================
DROP TABLE IF EXISTS `tracking_cache`;
CREATE TABLE `tracking_cache` (
  `cache_key` VARCHAR(100) NOT NULL COMMENT 'Cache key',
  `data` TEXT NOT NULL COMMENT 'Cache data (JSON)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cache_key`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracking cache';

-- ============================================================
-- 25. EMAIL SEND LOG TABLE
-- ============================================================
DROP TABLE IF EXISTS `email_send_log`;
CREATE TABLE `email_send_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_numbers` VARCHAR(500) NOT NULL,
  `recipient_email` VARCHAR(255) NOT NULL,
  `recipient_name` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(500) NOT NULL,
  `sent_at` DATETIME NOT NULL,
  `status` ENUM('success','failed') NOT NULL DEFAULT 'success',
  `error_message` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recipient_email` (`recipient_email`),
  KEY `idx_sent_at` (`sent_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Email send logs';

-- ============================================================
-- 26. PORTFOLIO UPLOAD LOG TABLE
-- ============================================================
DROP TABLE IF EXISTS `portfolio_upload_log`;
CREATE TABLE `portfolio_upload_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(100) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `file_data` LONGTEXT NOT NULL COMMENT 'Uploaded file info (JSON)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_category` (`category`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Portfolio upload logs';

-- ============================================================
-- 27. UNIT CODES TABLE (Grand Design)
-- ============================================================
DROP TABLE IF EXISTS `unit_codes`;
CREATE TABLE `unit_codes` (
  `code` CHAR(1) NOT NULL,
  `name_ko` VARCHAR(10) NOT NULL,
  `name_en` VARCHAR(20) NOT NULL,
  `description` VARCHAR(100) NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Unit code reference';

-- ============================================================
-- RESTORE FOREIGN KEY CHECKS
-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- INSTALLATION COMPLETE MESSAGE
-- ============================================================
SELECT 'Duson Print System schema installation complete!' AS message;
