# Phase 1 실행 가이드 - 데이터 구조 표준화 Foundation

**목표**: 레거시 시스템에 영향 없이 표준 데이터 구조 기반 마련
**위험도**: 🟢 제로 (기존 시스템 영향 없음)
**예상 소요시간**: 1-2시간

---

## ✅ Phase 1에서 생성한 파일들

### 1. PHP 변환 레이어
📄 **`/var/www/html/includes/DataAdapter.php`** (550줄)
- 11개 제품의 레거시 필드 → 표준 필드 변환
- 명함: `MY_type, Section, price` → `spec_type, spec_material, price_supply`
- 스티커: `jong, garo, mesu, price (문자열!)` → `spec_material, spec_size, quantity_value, price_supply (정수)`
- 전단지: `MY_Fsd, PN_type, Order_PriceForm` → `spec_material, spec_size, price_supply`
- ... 8개 제품 더

### 2. JavaScript 가격 정규화
📄 **`/var/www/html/js/price-data-adapter.js`** (200줄)
- 5가지 가격 필드명 → 통일된 `{supply, vat, vatAmount}` 포맷
- `window.PriceDataAdapter` 글로벌 객체 제공
- 자동 제품 타입 감지 및 변환

### 3. 데이터베이스 스키마 변경
📄 **`/var/www/html/sql/phase1_add_standard_columns.sql`**
- `shop_temp` 테이블: 17개 컬럼 추가
- `mlangorder_printauto` 테이블: 16개 컬럼 추가
- 인덱스 4개 추가 (성능 최적화)

### 4. 롤백 스크립트
📄 **`/var/www/html/sql/phase1_rollback.sql`**
- 안전 확인 쿼리 포함 (Phase 2 배포 후 실행 방지)
- 모든 표준 컬럼 및 인덱스 완전 제거

---

## 🚀 실행 순서

### Step 1: 로컬 환경 테스트 (필수)

```bash
# 1. 로컬 데이터베이스 백업
mysqldump -u root -p dsp1830 > backup_phase1_local_$(date +%Y%m%d).sql

# 2. SQL 스크립트 실행 (로컬)
mysql -u root -p dsp1830 < /var/www/html/sql/phase1_add_standard_columns.sql

# 3. 검증 쿼리 실행
mysql -u root -p dsp1830 -e "SHOW COLUMNS FROM shop_temp LIKE 'spec_%';"
mysql -u root -p dsp1830 -e "SHOW COLUMNS FROM shop_temp LIKE 'data_version';"

# 4. DataAdapter 테스트
php -r "
require '/var/www/html/includes/DataAdapter.php';
\$legacy = ['MY_type' => 'A001', 'Section' => 'B001', 'price' => 10000, 'vat_price' => 11000];
\$standard = DataAdapter::legacyToStandard(\$legacy, 'namecard');
print_r(\$standard);
"

# 예상 출력:
# Array (
#     [product_type] => namecard
#     [data_version] => 2
#     [spec_type] => A001
#     [spec_material] => B001
#     [price_supply] => 10000
#     [price_vat] => 11000
#     ...
# )
```

### Step 2: JavaScript 테스트

```bash
# 브라우저 콘솔에서 테스트 (http://localhost/mlangprintauto/namecard/)
```

```javascript
// 1. 스크립트 로드 확인
console.log(typeof PriceDataAdapter);  // "object"

// 2. 명함 가격 정규화 테스트
const namecardData = {price: "10,000", vat_price: "11,000"};
const result = PriceDataAdapter.normalize(namecardData);
console.log(result);
// {supply: 10000, vat: 11000, vatAmount: 1000}

// 3. 전단지 가격 정규화 테스트
const flyerData = {Order_PriceForm: 50000, Total_PriceForm: 55000};
const result2 = PriceDataAdapter.normalize(flyerData);
console.log(result2);
// {supply: 50000, vat: 55000, vatAmount: 5000}

// 4. 스티커 (문자열 가격) 테스트
const stickerData = {price: "20,000", price_vat: "22,000"};
const result3 = PriceDataAdapter.normalize(stickerData);
console.log(result3);
// {supply: 20000, vat: 22000, vatAmount: 2000}
```

### Step 3: 프로덕션 배포 (로컬 테스트 성공 후)

```bash
# 1. 프로덕션 데이터베이스 백업 (중요!)
ssh dsp1830@dsp1830.shop
mysqldump -u dsp1830 -p dsp1830 > ~/backup_phase1_prod_$(date +%Y%m%d_%H%M).sql

# 2. 백업 파일 크기 확인 (10MB 이상이면 정상)
ls -lh ~/backup_phase1_prod_*.sql

# 3. 기존 레코드 수 확인 (실행 전)
mysql -u dsp1830 -p dsp1830 -e "
SELECT COUNT(*) as shop_temp_count FROM shop_temp;
SELECT COUNT(*) as order_count FROM mlangorder_printauto;
SELECT MAX(no) as latest_order FROM mlangorder_printauto;
"

# 4. SQL 스크립트 업로드
scp /var/www/html/sql/phase1_add_standard_columns.sql \
    dsp1830@dsp1830.shop:~/sql/

# 5. 프로덕션 실행
ssh dsp1830@dsp1830.shop
mysql -u dsp1830 -p dsp1830 < ~/sql/phase1_add_standard_columns.sql

# 6. 검증 (컬럼 추가 확인)
mysql -u dsp1830 -p dsp1830 -e "
SHOW COLUMNS FROM shop_temp LIKE 'spec_%';
SHOW COLUMNS FROM shop_temp LIKE 'data_version';
SELECT COUNT(*) FROM shop_temp;  -- 기존과 동일해야 함
"
```

### Step 4: 코드 파일 배포

```bash
# 1. DataAdapter.php 업로드
scp /var/www/html/includes/DataAdapter.php \
    dsp1830@dsp1830.shop:/home/hosting_users/dsp1830/www/includes/

# 2. price-data-adapter.js 업로드
scp /var/www/html/js/price-data-adapter.js \
    dsp1830@dsp1830.shop:/home/hosting_users/dsp1830/www/js/

# 3. 파일 권한 설정
ssh dsp1830@dsp1830.shop
chmod 644 /home/hosting_users/dsp1830/www/includes/DataAdapter.php
chmod 644 /home/hosting_users/dsp1830/www/js/price-data-adapter.js

# 4. 배포 확인
curl -I http://dsp1830.shop/js/price-data-adapter.js
# HTTP/1.1 200 OK
```

---

## 🧪 검증 체크리스트

### 데이터베이스 검증
- [ ] `shop_temp`에 `spec_type`, `spec_material`, `spec_size` 컬럼 존재
- [ ] `shop_temp`에 `quantity_value`, `quantity_unit` 컬럼 존재
- [ ] `shop_temp`에 `price_supply`, `price_vat`, `price_vat_amount` 컬럼 존재
- [ ] `shop_temp`에 `data_version` 컬럼 존재 (DEFAULT 1)
- [ ] `mlangorder_printauto`에 위와 동일한 컬럼 존재
- [ ] 인덱스 `idx_shop_temp_data_version` 존재
- [ ] 인덱스 `idx_order_data_version` 존재
- [ ] 기존 레코드 수 변화 없음
- [ ] 기존 주문 조회 정상 작동

### PHP 코드 검증
- [ ] `DataAdapter::legacyToStandard()` 명함 변환 성공
- [ ] `DataAdapter::legacyToStandard()` 스티커 변환 성공 (문자열→정수)
- [ ] `DataAdapter::legacyToStandard()` 전단지 변환 성공
- [ ] NCR 변환 시 `MY_type`이 "도수"로 인식됨
- [ ] 11개 제품 모두 변환 로직 존재

### JavaScript 검증
- [ ] `PriceDataAdapter` 글로벌 객체 로드됨
- [ ] 명함 가격 정규화 성공 (`price` → `supply`)
- [ ] 전단지 가격 정규화 성공 (`Order_PriceForm` → `supply`)
- [ ] 스티커 문자열 가격 정규화 성공 (`"10,000"` → `10000`)
- [ ] `detectProductType()` 자동 감지 작동
- [ ] 브라우저 콘솔 에러 없음

---

## ⚠️ 롤백 절차 (문제 발생 시)

```bash
# 1. 즉시 롤백 결정 (Phase 2 배포 전에만 가능!)
mysql -u dsp1830 -p dsp1830 -e "
SELECT COUNT(*) FROM shop_temp WHERE data_version = 2;
"
# ⚠️ 결과가 0이면 롤백 가능, 0보다 크면 데이터 손실 발생!

# 2. 롤백 스크립트 실행
mysql -u dsp1830 -p dsp1830 < /var/www/html/sql/phase1_rollback.sql

# 3. 롤백 검증
mysql -u dsp1830 -p dsp1830 -e "
SHOW COLUMNS FROM shop_temp LIKE 'spec_%';  -- 0 rows
SHOW COLUMNS FROM shop_temp LIKE 'data_version';  -- 0 rows
SELECT COUNT(*) FROM shop_temp;  -- 기존과 동일
"

# 4. 백업에서 복원 (롤백도 실패한 경우)
mysql -u dsp1830 -p dsp1830 < ~/backup_phase1_prod_*.sql
```

---

## 📊 예상 결과

### 스키마 변경 요약
```
shop_temp:
  - 기존 컬럼: 약 30개
  - 추가 컬럼: 17개
  - 총 컬럼: 약 47개
  - 인덱스: +2개

mlangorder_printauto:
  - 기존 컬럼: 약 50개
  - 추가 컬럼: 16개
  - 총 컬럼: 약 66개
  - 인덱스: +2개
```

### 기존 시스템 영향
- **장바구니**: 변화 없음 (레거시 필드 계속 사용)
- **주문 페이지**: 변화 없음
- **주문 완료**: 변화 없음
- **관리자**: 변화 없음
- **가격 계산**: 변화 없음

### 디스크 사용량
- 예상 증가: 약 50MB (10만 건 기준)
- 인덱스: 약 10MB 추가

---

## 🎯 Phase 1 완료 조건

- [x] DataAdapter.php 생성 완료
- [x] price-data-adapter.js 생성 완료
- [x] SQL 스크립트 작성 완료
- [x] 롤백 스크립트 작성 완료
- [ ] 로컬 테스트 성공
- [ ] 프로덕션 백업 완료
- [ ] 프로덕션 스키마 변경 완료
- [ ] 프로덕션 코드 배포 완료
- [ ] 검증 체크리스트 100% 통과

**Phase 1 완료 후**: Phase 2 (Dual-Write) 시작 가능

---

## 📞 문제 발생 시 대응

### SQL 실행 실패
```
ERROR 1060: Duplicate column name 'spec_type'
→ 이미 실행됨, SHOW COLUMNS로 확인
```

### PHP 에러
```
Fatal error: Class 'DataAdapter' not found
→ require_once 경로 확인: '../../includes/DataAdapter.php'
```

### JavaScript 에러
```
Uncaught ReferenceError: PriceDataAdapter is not defined
→ <script src="/js/price-data-adapter.js"></script> 추가 확인
```

---

**작성일**: 2026-01-04
**버전**: 1.0
**다음 단계**: Phase 2 - Dual-Write 구현
