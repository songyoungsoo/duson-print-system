# Phase A: DB 스키마 확장 마이그레이션

**작성일**: 2025-12-26  
**목적**: 수동입력 견적서의 주문 전환 지원을 위한 DB 스키마 확장  
**실행 완료**: 2025-12-26 08:30 (성공)

---

## 📋 변경 사항 요약

### 1. mlangorder_printauto 테이블
- **product_type**: VARCHAR(50) 추가 (shop_temp의 product_type 값 저장)
- **is_custom_product**: TINYINT(1) 추가 (표준/수동 구분 플래그)
- **custom_product_name**: VARCHAR(200) 기존 필드 활용
- **custom_specification**: TEXT 기존 필드 활용
- **인덱스 3개**: 조회 성능 최적화
  - idx_product_type
  - idx_is_custom_product
  - idx_product_type_custom

### 2. quotes 테이블
- **quote_source**: ENUM('customer', 'admin_auto', 'admin_manual') 추가
- **인덱스 2개**: 조회 성능 최적화
  - idx_quote_source
  - idx_quote_source_status

### 3. quote_items 테이블
- **is_manual_entry**: TINYINT(1) 추가 (자동계산/수동입력 구분)
- **인덱스 2개**: 조회 성능 최적화
  - idx_is_manual_entry
  - idx_product_type_manual

---

## ✅ 실행 결과

### 백업 정보
- **백업 위치**: `/var/www/html/database/backups/phase_a_20251226_082731/`
- **백업 파일**:
  - mlangorder_printauto.sql (26MB, 61,257건)
  - quotes.sql (16KB, 25건)
  - quote_items.sql (32KB, 64건)

### 테이블 변경 완료
- **mlangorder_printauto**: product_type, is_custom_product 필드 추가 완료
- **quotes**: quote_source 필드 추가 완료
- **quote_items**: is_manual_entry 필드 추가 완료
- **인덱스**: 총 7개 인덱스 생성 완료

### 데이터 마이그레이션
- **mlangorder_printauto**: 61,257건 모두 is_custom_product=0 (표준제품)
- **quotes**: 25건 모두 quote_source='customer'
- **quote_items**: 64건 모두 is_manual_entry=0 (자동계산)

---

## 📂 파일 구조

```
phase_a_custom_products/
├── README.md                              # 이 파일
├── README_updated.md                      # 업데이트된 README
├── 01_backup.sh                           # 백업 스크립트
├── 02_alter_mlangorder_printauto.sql      # 주문 테이블 수정 (원본)
├── 02_alter_mlangorder_printauto_revised.sql  # 주문 테이블 수정 (실행본)
├── 03_alter_quotes_quote_items.sql        # 견적 테이블 수정
├── 04_rollback.sql                        # 롤백 SQL
├── 05_execute_migration.sh                # 통합 실행 스크립트
└── 06_restore_from_backup.sh              # 백업 복원 스크립트
```

---

## 🔄 롤백 방법

### 방법 1: 백업에서 복원 (권장)
```bash
bash 06_restore_from_backup.sh
```

### 방법 2: SQL 스크립트로 롤백
```bash
# product_type, is_custom_product 필드 제거
mysql -u dsp1830 -pds701018 dsp1830 -e "
ALTER TABLE mlangorder_printauto
DROP COLUMN product_type,
DROP COLUMN is_custom_product;

DROP INDEX idx_product_type ON mlangorder_printauto;
DROP INDEX idx_is_custom_product ON mlangorder_printauto;
DROP INDEX idx_product_type_custom ON mlangorder_printauto;
"

# quote_source, is_manual_entry 필드 제거
mysql -u dsp1830 -pds701018 dsp1830 -e "
ALTER TABLE quotes DROP COLUMN quote_source;
ALTER TABLE quote_items DROP COLUMN is_manual_entry;

DROP INDEX idx_quote_source ON quotes;
DROP INDEX idx_quote_source_status ON quotes;
DROP INDEX idx_is_manual_entry ON quote_items;
DROP INDEX idx_product_type_manual ON quote_items;
"
```

---

## 🧪 검증 쿼리

### mlangorder_printauto 검증
```sql
-- 신규 필드 확인
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dsp1830'
AND TABLE_NAME = 'mlangorder_printauto'
AND COLUMN_NAME IN ('product_type', 'is_custom_product');

-- 인덱스 확인
SHOW INDEX FROM mlangorder_printauto
WHERE Key_name IN ('idx_product_type', 'idx_is_custom_product', 'idx_product_type_custom');

-- 데이터 분포
SELECT 
    is_custom_product,
    COUNT(*) as count
FROM mlangorder_printauto
GROUP BY is_custom_product;
```

### quotes 검증
```sql
-- quote_source 필드 확인
DESCRIBE quotes;

-- 데이터 분포
SELECT quote_source, COUNT(*)
FROM quotes
GROUP BY quote_source;
```

### quote_items 검증
```sql
-- is_manual_entry 필드 확인
DESCRIBE quote_items;

-- 데이터 분포
SELECT is_manual_entry, COUNT(*)
FROM quote_items
GROUP BY is_manual_entry;
```

---

## 📊 예상 영향

### 긍정적 영향
- ✅ 수동입력 견적서 주문 전환 가능
- ✅ 견적서 생성 방식 추적 가능 (고객/관리자 자동/관리자 수동)
- ✅ 제품 타입별 통계 정확도 향상
- ✅ 확장성 확보 (custom 제품 지원)

### 다음 단계 (Phase B)
- **convert_to_order.php 수정**: product_type, is_custom_product 값 저장 로직 추가
- **custom 제품 처리**: product_type='custom'인 경우 custom_product_name, custom_specification 활용
- **견적서 생성 시**: quote_source, is_manual_entry 값 자동 설정

---

## 🔗 관련 문서

- 전체 전략: `/var/www/html/docs/plans/STRATEGY_quotation-types-handling.md`
- Phase 1 계획: `/var/www/html/docs/plans/PLAN_order-quotation-mode-separation.md`

---

**Last Updated**: 2025-12-26 08:30
**Status**: ✅ 성공적으로 완료
