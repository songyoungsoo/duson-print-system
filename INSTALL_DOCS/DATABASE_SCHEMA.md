# Duson Print System Database Schema

**Version**: 2.0.0
**Created**: 2026-01-18
**Encoding**: utf8mb4
**Engine**: InnoDB (recommended) / MyISAM (legacy)

---

## Table of Contents

1. [Overview](#overview)
2. [Core Tables](#core-tables)
3. [Product Price Tables](#product-price-tables)
4. [Quotation System Tables](#quotation-system-tables)
5. [Chat System Tables](#chat-system-tables)
6. [Payment and Shipping Tables](#payment-and-shipping-tables)
7. [Grand Design Tables](#grand-design-tables-future)
8. [Legacy Tables](#legacy-tables)
9. [Installation Guide](#installation-guide)

---

## Overview

Duson Print System uses MySQL/MariaDB with utf8mb4 encoding for full Korean character support.

### Database Information

| Attribute | Value |
|-----------|-------|
| Database Name | `dsp1830` |
| Default Charset | `utf8mb4` |
| Default Collation | `utf8mb4_unicode_ci` |
| Engine | InnoDB (recommended) |

### Product Type Mapping (9 Products)

| # | Product Name | Folder Name | Table Suffix |
|---|--------------|-------------|--------------|
| 1 | 전단지 (Leaflet) | `inserted` | `_inserted` |
| 2 | 스티커 (Sticker) | `sticker_new` | `_sticker` |
| 3 | 자석스티커 (Magnet Sticker) | `msticker` | `_msticker` |
| 4 | 명함 (Business Card) | `namecard` | `_namecard` |
| 5 | 봉투 (Envelope) | `envelope` | `_envelope` |
| 6 | 포스터 (Poster) | `littleprint` | `_littleprint` |
| 7 | 상품권 (Gift Card) | `merchandisebond` | `_merchandisebond` |
| 8 | 카다록 (Catalog) | `cadarok` | `_cadarok` |
| 9 | NCR양식지 (NCR Form) | `ncrflambeau` | `_ncrflambeau` |

---

## Core Tables

### 1. users - User Account Table

Central user authentication table (replaces legacy `member` table).

```sql
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `is_admin` TINYINT(1) DEFAULT 0,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(200) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `postcode` VARCHAR(20) DEFAULT NULL,
  `address` VARCHAR(200) DEFAULT NULL,
  `detail_address` VARCHAR(200) DEFAULT NULL,
  `extra_address` VARCHAR(200) DEFAULT NULL,
  `business_number` VARCHAR(50) DEFAULT NULL,
  `business_name` VARCHAR(100) DEFAULT NULL,
  `business_owner` VARCHAR(100) DEFAULT NULL,
  `business_type` VARCHAR(100) DEFAULT NULL,
  `business_item` VARCHAR(100) DEFAULT NULL,
  `business_address` VARCHAR(300) DEFAULT NULL,
  `tax_invoice_email` VARCHAR(200) DEFAULT NULL COMMENT 'Tax invoice email',
  `level` VARCHAR(10) DEFAULT '5',
  `login_count` INT DEFAULT 0,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `migrated_from_member` TINYINT(1) DEFAULT 1,
  `original_member_no` INT DEFAULT NULL,
  `business_cert_path` VARCHAR(255) DEFAULT NULL COMMENT 'Business cert file path',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Fields:**
- `username`: Login ID (unique)
- `password`: Hashed password
- `is_admin`: 1 for admin users
- `level`: User level (1=admin, 2=manager, 5=regular)

---

### 2. member - Legacy Member Table

Original member table (kept for backwards compatibility).

```sql
CREATE TABLE `member` (
  `no` MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id` VARCHAR(20) NOT NULL DEFAULT '',
  `pass` VARCHAR(20) NOT NULL DEFAULT '',
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
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `level` VARCHAR(10) NOT NULL DEFAULT '1',
  `Logincount` INT(11) NOT NULL DEFAULT 0,
  `EndLogin` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`no`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 3. mlangorder_printauto - Order Table

Main order storage table.

```sql
CREATE TABLE `mlangorder_printauto` (
  `no` INT(11) NOT NULL AUTO_INCREMENT,
  `Type` VARCHAR(50) DEFAULT NULL COMMENT 'Order type',
  `ImgFolder` TEXT COMMENT 'Image folder path',
  `uploaded_files` TEXT COMMENT 'Uploaded files JSON',
  `Type_1` TEXT COMMENT 'Product details JSON',
  `mesu` VARCHAR(100) DEFAULT NULL COMMENT 'Quantity',
  `money_1` INT(11) DEFAULT 0,
  `money_2` INT(11) DEFAULT 0,
  `money_3` INT(11) DEFAULT 0,
  `money_4` INT(11) DEFAULT 0 COMMENT 'Price (excl VAT)',
  `money_5` INT(11) DEFAULT 0 COMMENT 'Price (incl VAT)',
  `payment_status` VARCHAR(20) DEFAULT 'pending' COMMENT 'Payment status',
  `payment_method` VARCHAR(50) DEFAULT NULL COMMENT 'Payment method',
  `name` VARCHAR(100) DEFAULT NULL COMMENT 'Customer name',
  `email` VARCHAR(100) DEFAULT NULL COMMENT 'Email',
  `zip` VARCHAR(20) DEFAULT NULL COMMENT 'Postcode',
  `zip1` TEXT COMMENT 'Address 1',
  `zip2` TEXT COMMENT 'Address 2',
  `phone` VARCHAR(50) DEFAULT NULL COMMENT 'Phone',
  `Hendphone` VARCHAR(50) DEFAULT NULL COMMENT 'Mobile',
  `delivery` VARCHAR(100) DEFAULT NULL COMMENT 'Delivery method',
  `courier` VARCHAR(50) DEFAULT NULL COMMENT 'Courier company',
  `tracking_number` VARCHAR(50) DEFAULT NULL COMMENT 'Tracking number',
  `ship_status` VARCHAR(30) DEFAULT 'pending' COMMENT 'Shipping status',
  `delivery_date` DATETIME DEFAULT NULL COMMENT 'Delivery date',
  `bizname` TEXT COMMENT 'Business name',
  `bank` VARCHAR(100) DEFAULT NULL COMMENT 'Bank name',
  `bankname` VARCHAR(100) DEFAULT NULL COMMENT 'Depositor name',
  `cont` TEXT COMMENT 'Customer notes',
  `date` DATETIME DEFAULT NULL COMMENT 'Order date',
  `OrderStyle` VARCHAR(10) DEFAULT NULL COMMENT 'Order status',
  `ThingCate` VARCHAR(255) DEFAULT NULL COMMENT 'Product category',
  `product_type` VARCHAR(50) DEFAULT NULL COMMENT 'Product type',
  `is_custom_product` TINYINT(1) DEFAULT 0 COMMENT 'Custom product flag',
  `pass` VARCHAR(100) DEFAULT NULL COMMENT 'Order password',
  `Gensu` VARCHAR(50) DEFAULT NULL COMMENT 'Confirmed date',
  `Designer` VARCHAR(100) DEFAULT NULL COMMENT 'Designer',
  `PMmember` VARCHAR(100) DEFAULT NULL COMMENT 'PM member',
  `coating_enabled` TINYINT(1) DEFAULT 0,
  `coating_type` VARCHAR(20) DEFAULT NULL,
  `coating_price` INT DEFAULT 0,
  `folding_enabled` TINYINT(1) DEFAULT 0,
  `folding_type` VARCHAR(20) DEFAULT NULL,
  `folding_price` INT DEFAULT 0,
  `creasing_enabled` TINYINT(1) DEFAULT 0,
  `creasing_lines` INT DEFAULT 0,
  `creasing_price` INT DEFAULT 0,
  `additional_options_total` INT DEFAULT 0,
  `tape_enabled` TINYINT(1) DEFAULT 0 COMMENT 'Envelope tape option',
  `tape_quantity` VARCHAR(20) DEFAULT NULL,
  `tape_price` INT DEFAULT 0,
  PRIMARY KEY (`no`),
  KEY `idx_date` (`date`),
  KEY `idx_email` (`email`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_ship_status` (`ship_status`)
) ENGINE=InnoDB AUTO_INCREMENT=84000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**OrderStyle Values:**
- `1`: New order
- `2`: Confirmed
- `3`: Processing
- `4`: Completed
- `5`: Cancelled

---

### 4. shop_temp - Shopping Cart Table

Temporary cart storage.

```sql
CREATE TABLE `shop_temp` (
  `no` INT(11) NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(100) NOT NULL COMMENT 'Session ID',
  `order_id` VARCHAR(50) DEFAULT NULL COMMENT 'Order ID',
  `parent` VARCHAR(50) DEFAULT NULL,
  `product_type` VARCHAR(50) NOT NULL DEFAULT 'sticker',
  -- Sticker specific fields
  `jong` VARCHAR(200) DEFAULT NULL COMMENT 'Sticker type',
  `garo` VARCHAR(50) DEFAULT NULL COMMENT 'Width (mm)',
  `sero` VARCHAR(50) DEFAULT NULL COMMENT 'Height (mm)',
  `mesu` VARCHAR(50) DEFAULT NULL COMMENT 'Quantity',
  `domusong` VARCHAR(200) DEFAULT NULL COMMENT 'Die-cut option',
  `uhyung` INT(1) DEFAULT 0 COMMENT 'Design type',
  -- Common category mapping
  `MY_type` VARCHAR(50) DEFAULT NULL COMMENT 'Type code',
  `MY_Fsd` VARCHAR(50) DEFAULT NULL COMMENT 'Material code',
  `PN_type` VARCHAR(50) DEFAULT NULL COMMENT 'Size code',
  `Section` VARCHAR(50) DEFAULT NULL COMMENT 'Section code',
  `MY_amount` DECIMAL(10,2) DEFAULT NULL COMMENT 'Amount',
  `POtype` VARCHAR(10) DEFAULT NULL COMMENT 'Print sides',
  `ordertype` VARCHAR(50) DEFAULT NULL COMMENT 'Order type',
  -- Price
  `st_price` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Price (excl VAT)',
  `st_price_vat` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Price (incl VAT)',
  -- Upload info
  `work_memo` TEXT DEFAULT NULL,
  `upload_method` VARCHAR(20) DEFAULT 'upload',
  `uploaded_files` TEXT COMMENT 'Uploaded files JSON',
  `ThingCate` VARCHAR(255) DEFAULT NULL,
  `ImgFolder` VARCHAR(255) DEFAULT NULL,
  -- Additional options
  `coating_enabled` TINYINT(1) DEFAULT 0,
  `coating_type` VARCHAR(20) DEFAULT NULL,
  `coating_price` INT DEFAULT 0,
  `folding_enabled` TINYINT(1) DEFAULT 0,
  `folding_type` VARCHAR(20) DEFAULT NULL,
  `folding_price` INT DEFAULT 0,
  `creasing_enabled` TINYINT(1) DEFAULT 0,
  `creasing_lines` INT DEFAULT 0,
  `creasing_price` INT DEFAULT 0,
  `additional_options_total` INT DEFAULT 0,
  `premium_options` TEXT,
  `premium_options_total` INT DEFAULT 0,
  `envelope_tape_enabled` TINYINT(1) DEFAULT 0,
  `envelope_tape_quantity` INT DEFAULT 0,
  `envelope_tape_price` INT DEFAULT 0,
  `envelope_additional_options_total` INT DEFAULT 0,
  `regdate` INT DEFAULT NULL COMMENT 'Registration timestamp',
  PRIMARY KEY (`no`),
  KEY `idx_session` (`session_id`),
  KEY `idx_product_type` (`product_type`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 5. mlangprintauto_transactioncate - Category Mapping Table

Category hierarchy for products.

```sql
CREATE TABLE `mlangprintauto_transactioncate` (
  `no` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `Ttable` VARCHAR(250) DEFAULT NULL COMMENT 'Product table name',
  `BigNo` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Parent category ID',
  `title` VARCHAR(250) DEFAULT NULL COMMENT 'Category title',
  `TreeNo` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Tree hierarchy number',
  PRIMARY KEY (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Ttable Values:**
- `sticker`, `NameCard`, `envelope`, `NcrFlambeau`, etc.

---

## Product Price Tables

### 6. mlangprintauto_inserted - Leaflet Price Table

```sql
CREATE TABLE `mlangprintauto_inserted` (
  `no` INT NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 7. mlangprintauto_sticker - Sticker Price Table

```sql
CREATE TABLE `mlangprintauto_sticker` (
  `no` INT NOT NULL AUTO_INCREMENT,
  `MY_type` INT DEFAULT NULL COMMENT 'Sticker type code',
  `MY_type_name` VARCHAR(100) DEFAULT NULL COMMENT 'Sticker type name',
  `garo` INT DEFAULT NULL COMMENT 'Width (mm)',
  `sero` INT DEFAULT NULL COMMENT 'Height (mm)',
  `mesu` INT DEFAULT NULL COMMENT 'Quantity',
  `MY_money` INT DEFAULT 0 COMMENT 'Price',
  `domusong` VARCHAR(100) DEFAULT NULL COMMENT 'Die-cut option',
  PRIMARY KEY (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 8. mlangprintauto_namecard - Business Card Price Table

```sql
CREATE TABLE `mlangprintauto_namecard` (
  `no` INT NOT NULL AUTO_INCREMENT,
  `MY_type` INT DEFAULT NULL COMMENT 'Paper type code',
  `MY_type_name` VARCHAR(100) DEFAULT NULL COMMENT 'Paper name',
  `Section` INT DEFAULT NULL COMMENT 'Section code',
  `Section_name` VARCHAR(100) DEFAULT NULL COMMENT 'Section name',
  `POtype` INT DEFAULT NULL COMMENT 'Print sides (1:single, 2:double)',
  `MY_amount` INT DEFAULT NULL COMMENT 'Sheet count',
  `MY_money` INT DEFAULT 0 COMMENT 'Price',
  PRIMARY KEY (`no`),
  KEY `idx_type` (`MY_type`, `Section`, `POtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 9. mlangprintauto_envelope - Envelope Price Table

```sql
CREATE TABLE `mlangprintauto_envelope` (
  `no` INT NOT NULL AUTO_INCREMENT,
  `MY_type` INT DEFAULT NULL COMMENT 'Envelope type code',
  `MY_type_name` VARCHAR(100) DEFAULT NULL COMMENT 'Envelope type name',
  `Section` INT DEFAULT NULL COMMENT 'Size code',
  `Section_name` VARCHAR(100) DEFAULT NULL COMMENT 'Size name',
  `POtype` INT DEFAULT NULL COMMENT 'Print sides',
  `MY_amount` INT DEFAULT NULL COMMENT 'Sheet count',
  `MY_money` INT DEFAULT 0 COMMENT 'Price',
  PRIMARY KEY (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 10-15. Other Product Tables

Following the same pattern:
- `mlangprintauto_cadarok` - Catalog prices
- `mlangprintauto_littleprint` - Poster prices
- `mlangprintauto_merchandisebond` - Gift card prices
- `mlangprintauto_ncrflambeau` - NCR form prices
- `mlangprintauto_msticker` - Magnet sticker prices
- `mlangprintauto_leaflet_fold` - Leaflet folding options

---

## Quotation System Tables

### 16. quotations - Quotation Master Table

```sql
CREATE TABLE `quotations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `quotation_no` VARCHAR(50) NOT NULL COMMENT 'Quote number (QT-YYYYMMDD-NNN)',
  `quote_type` VARCHAR(20) DEFAULT 'quotation',
  `public_token` VARCHAR(64) DEFAULT NULL,
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
  UNIQUE KEY `quotation_no` (`quotation_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 17. quote_items - Quotation Items Table

```sql
CREATE TABLE `quote_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `quote_id` INT(11) NOT NULL COMMENT 'quotes.id reference',
  `item_no` INT(11) DEFAULT 1 COMMENT 'Item sequence',
  `product_type` VARCHAR(50) DEFAULT '' COMMENT 'Product type',
  `product_name` VARCHAR(200) NOT NULL COMMENT 'Product name',
  `specification` TEXT COMMENT 'Specifications (JSON or text)',
  `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  `unit` VARCHAR(10) DEFAULT 'pcs' COMMENT 'Unit (sheets, pcs, bundles)',
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
  FOREIGN KEY (`quote_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 18. quotation_temp - Temporary Quotation Cart

Clone of `shop_temp` for quotation workflow.

```sql
-- Created via: CREATE TABLE quotation_temp LIKE shop_temp;
```

---

## Chat System Tables

### 19-23. Chat Tables

```sql
-- chatrooms: Chat room information
-- chatparticipants: Chat participants
-- chatmessages: Chat messages
-- chatstaff: Staff information
-- chatsettings: User chat settings
```

See `/var/www/html/chat/create_chat_system.sql` for full definitions.

---

## Payment and Shipping Tables

### 24. payments - Payment Information

```sql
CREATE TABLE `payments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL COMMENT 'Order number',
  `payment_method` VARCHAR(50) NOT NULL COMMENT 'bank_transfer, naverpay, kakaopay, card',
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `amount_vat` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `external_provider` VARCHAR(50) NULL,
  `external_id` VARCHAR(255) NULL,
  `meta` TEXT NULL COMMENT 'Metadata (JSON)',
  `bank_info` TEXT NULL COMMENT 'Bank account info (JSON)',
  `deposit_deadline` DATETIME NULL,
  `depositor_name` VARCHAR(100) NULL,
  `approved_at` DATETIME NULL,
  `cancelled_at` DATETIME NULL,
  `cancel_reason` TEXT NULL,
  `confirmed_by` INT(11) NULL,
  `payment_result` TEXT NULL COMMENT 'Payment result (JSON)',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_method` (`payment_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 25. shipping_info - Shipping Information

```sql
CREATE TABLE `shipping_info` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `courier_code` VARCHAR(20) NOT NULL COMMENT 'cj, hanjin, lotte, logen, post',
  `tracking_number` VARCHAR(50) NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'ready',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order` (`order_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 26. notification_logs - Notification Logs

```sql
CREATE TABLE `notification_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NULL,
  `notification_type` VARCHAR(50) NOT NULL COMMENT 'kakao_alimtalk, sms, email',
  `template_code` VARCHAR(100) NULL,
  `recipient` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `result` TEXT NULL COMMENT 'Send result (JSON)',
  `created_at` DATETIME NOT NULL,
  `sent_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_notification_type` (`notification_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Grand Design Tables (Future)

Normalized schema for future migration:

### 27. unit_codes - Unit Code Reference

```sql
CREATE TABLE `unit_codes` (
  `code` CHAR(1) PRIMARY KEY,
  `name_ko` VARCHAR(10) NOT NULL,
  `name_en` VARCHAR(20) NOT NULL,
  `description` VARCHAR(100) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- R=Ream, S=Sheet, B=Bundle, V=Volume, P=Piece, E=Each
```

### 28. orders - Normalized Order Table

### 29. order_items - Normalized Order Items

### 30. order_options - Order Options Table

See `/var/www/html/database/migrations/grand_design/01_schema.sql` for full definitions.

---

## Legacy Tables

### Additional Options Config

```sql
CREATE TABLE `additional_options_config` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `product_type` VARCHAR(50) NOT NULL,
  `option_type` VARCHAR(50) NOT NULL,
  `option_name` VARCHAR(100) NOT NULL,
  `option_value` VARCHAR(100) DEFAULT NULL,
  `price` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_type`, `option_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Email Send Log

```sql
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
  KEY `idx_recipient_email` (`recipient_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Installation Guide

### Step 1: Create Database

```sql
CREATE DATABASE IF NOT EXISTS `dsp1830`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `dsp1830`;
```

### Step 2: Run Schema Script

```bash
mysql -u root -p dsp1830 < /var/www/html/install/sql/schema.sql
```

### Step 3: Load Seed Data

```bash
mysql -u root -p dsp1830 < /var/www/html/install/sql/seed_data.sql
```

### Step 4: Verify Installation

```sql
SHOW TABLES;
SELECT COUNT(*) FROM mlangprintauto_transactioncate;
SELECT COUNT(*) FROM users;
```

---

## Notes

### Naming Conventions

- **Table names**: Lowercase (e.g., `mlangorder_printauto`, `shop_temp`)
- **Column names**: Mixed case preserved for legacy compatibility
- **Indexes**: Prefixed with `idx_` or `uk_` (unique)

### Character Set

All tables use `utf8mb4` encoding for full Unicode/emoji support.

### Foreign Keys

Foreign keys are used sparingly for performance. Main relationships:
- `quote_items.quote_id` -> `quotations.id` (CASCADE DELETE)

### Sensitive Data

The following fields contain sensitive data and should be handled carefully:
- `users.password` - Hashed passwords
- `member.pass` - Legacy plaintext passwords (should be migrated)
- Business registration info fields

---

*Last Updated: 2026-01-18*
