# 배송 관리 시스템 (Delivery Management System)

**작성일**: 2025-12-12
**목적**: 로젠택배 연동 및 운송장 관리 시스템

---

## 📦 시스템 개요

두손기획인쇄의 주문 건에 대해 로젠택배 발송을 관리하는 통합 시스템입니다.

### 핵심 기능

1. **로젠택배 양식 내보내기** - 주문 데이터를 로젠 시스템 양식으로 변환
2. **운송장 번호 일괄 등록** - 로젠 시스템에서 발급된 운송장 번호를 주문에 자동 매칭
3. **배송비 자동 계산** - 제품 타입에 따른 박스 수량 및 택배비 자동 산출
4. **발송 현황 관리** - 발송 대기/완료 상태 추적

---

## 🗂️ 핵심 파일

### 관리자 페이지

| 파일 | 위치 | 설명 |
|------|------|------|
| **delivery_manager.php** | `/shop_admin/` | 배송 관리 메인 페이지 |
| **post_list52.php** | `/shop_admin/` | 로젠 주소 추출 페이지 (검색/필터링) |

### 엑셀 내보내기

| 파일 | 형식 | 컬럼 수 | 용도 |
|------|------|---------|------|
| **export_logen_excel.php** | XLS | 11개 | 로젠택배 전체 양식 |
| **export_logen_format.php** | CSV | 8개 | 로젠 iLOGEN 업로드용 |

### 설정 파일

| 파일 | 위치 | 설명 |
|------|------|------|
| **delivery_rules_config.php** | `/shop_admin/` | 제품별 택배비 규칙 |
| **delivery_calculator.php** | `/shop_admin/` | 택배비 계산 헬퍼 함수 |

---

## 💰 택배비 자동 계산 규칙

**기준**: `delivery_rules_config.php`

### 제품별 규칙 (2025-12-12 기준)

| 제품 타입 | 박스 수량 | 택배비 | 패턴 매칭 |
|-----------|----------|--------|----------|
| **명함** | 1 | 3,000원 | NameCard, 명함 |
| **상품권** | 1 | 3,000원 | MerchandiseBond, 상품권, 쿠폰 |
| **스티커** | 1 | 3,000원 | sticker, 스티커, 스티카 |
| **봉투** | 1 | 3,000원 | envelope, 봉투 |
| **전단지 16절** | 2 | 3,000원 | Type_1에 "16절" 포함 |
| **전단지 A4** | 1 | 4,000원 | Type_1에 "a4" 또는 "A4" 포함 |
| **전단지 A5** | 1 | 4,000원 | Type_1에 "a5" 또는 "A5" 포함 |
| **기타** | 1 | 3,000원 | 기본값 |

**최저 택배비**: 3,000원 (2025-12-10 통일)

---

## 📄 로젠택배 엑셀 양식

### XLS 양식 (11개 컬럼)

**파일**: `export_logen_excel.php`

| 순서 | 컬럼명 | 데이터 소스 | 비고 |
|------|--------|-------------|------|
| 1 | 수하인명 | `name` | - |
| 2 | 우편번호 | `zip` | 텍스트 형식 |
| 3 | 주소 | `zip1 + zip2` | 합쳐서 표시 |
| 4 | 전화 | `phone` | 텍스트 형식 |
| 5 | 핸드폰 | `Hendphone` | 텍스트 형식 |
| 6 | 박스수량 | 자동 계산 | 규칙 기반 |
| 7 | 택배비 | 자동 계산 | 규칙 기반 |
| 8 | 운임구분 | `착불` | 고정값 |
| 9 | 품목명 | `Type_1` JSON 파싱 | formatted_display |
| 10 | 기타 | `no` | 주문번호 |
| 11 | 배송메세지 | `Type` | - |

### CSV 양식 (8개 컬럼, iLOGEN 표준)

**파일**: `export_logen_format.php`

| 순서 | 컬럼명 | 비고 |
|------|--------|------|
| 1 | 주문번호 | `no` |
| 2 | 수하인명 | `name` |
| 3 | 수하인전화 | `phone` (텍스트) |
| 4 | 수하인휴대폰 | `Hendphone` (텍스트) |
| 5 | 수하인주소 | `zip1 + zip2` |
| 6 | 물품명 | `Type_1` JSON |
| 7 | 수량(박스) | 자동 계산 |
| 8 | 배송메세지 | `Type` |

**인코딩**: UTF-8 with BOM (엑셀 한글 호환)

---

## 🔄 운송장 등록 프로세스

### 1단계: 로젠택배 엑셀 내보내기

```
delivery_manager.php 또는 post_list52.php
→ "로젠택배 엑셀/CSV 내보내기" 버튼
→ export_logen_excel.php 또는 export_logen_format.php
→ 엑셀/CSV 파일 다운로드
```

**검색 조건**:
- 날짜 범위 (기본: 최근 7일)
- 상태: 발송 대기 (운송장 미등록) 또는 전체
- 이름, 회사명, 주문번호 검색 (post_list52.php)

### 2단계: 로젠택배 시스템 업로드

1. https://logis.ilogen.com/ 로그인
2. 엑셀/CSV 파일 업로드
3. 접수 완료 후 운송장 번호 발급
4. 운송장 번호가 포함된 엑셀 다운로드

### 3단계: 운송장 번호 일괄 등록

**지원 파일 형식**:
- ✅ `.txt` (텍스트 탭 구분) - **권장**
- ✅ `.csv` (쉼표 구분)
- ❌ `.xlsx`, `.xls` - 지원 안 함 (텍스트로 변환 필요)

**변환 방법**:
1. 엑셀 파일 열기
2. "다른 이름으로 저장" 클릭
3. 파일 형식: **"텍스트(탭으로 분리)(*.txt)"** 선택
4. 저장

**업로드 프로세스**:
```php
delivery_manager.php
→ "운송장 번호 일괄 등록" 섹션
→ .txt 파일 선택
→ 자동 컬럼 인식 (주문번호, 운송장번호)
→ DB UPDATE 실행
```

**DB 업데이트**:
```sql
UPDATE mlangorder_printauto
SET waybill_no = ?,           -- 운송장 번호
    waybill_date = NOW(),     -- 등록 일시
    delivery_company = '로젠'  -- 택배사
WHERE no = ?                  -- 주문번호
```

---

## 📊 데이터베이스 스키마

### mlangorder_printauto 테이블

**배송 관련 주요 컬럼**:

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| `no` | INT | 주문번호 (PK) |
| `name` | VARCHAR | 수하인명 |
| `zip` | VARCHAR | 우편번호 |
| `zip1` | VARCHAR | 주소 1 |
| `zip2` | VARCHAR | 주소 2 (상세주소) |
| `phone` | VARCHAR | 전화번호 |
| `Hendphone` | VARCHAR | 휴대폰번호 |
| `Type` | VARCHAR | 제품 타입 |
| `Type_1` | TEXT | 제품 상세 (JSON) |
| `cont` | TEXT | 배송 메시지 |
| `waybill_no` | VARCHAR | 운송장 번호 |
| `waybill_date` | DATETIME | 운송장 등록일시 |
| `delivery_company` | VARCHAR | 택배사명 |
| `date` | DATETIME | 주문일시 |

**JSON 데이터 예시** (`Type_1`):
```json
{
  "formatted_display": "90g아트지 A4 1연\n양면인쇄",
  "paper": "90g아트지",
  "size": "A4",
  "quantity": "1연"
}
```

---

## 🔍 검색 및 필터링 (post_list52.php)

### 검색 조건

| 필터 | 입력 형식 | 예시 |
|------|-----------|------|
| **이름** | 텍스트 | "홍길동" |
| **회사** | 텍스트 | "두손기획" |
| **날짜** | YYYY-MM-DD ~ YYYY-MM-DD | 2025-12-01 ~ 2025-12-12 |
| **주문번호** | 숫자 범위 | 84000 ~ 84500 |

### 선택 항목 내보내기

1. 체크박스로 주문 선택
2. "로젠택배 CSV (선택)" 또는 "로젠택배 엑셀 (선택)" 클릭
3. 선택된 항목만 내보내기

**전송 데이터**:
```javascript
formData.append('selected_nos', '84272,84273,84274');
formData.append('box_qty_json', JSON.stringify({84272: 2, 84273: 1}));
formData.append('delivery_fee_json', JSON.stringify({84272: 3000, 84273: 4000}));
formData.append('fee_type_json', JSON.stringify({84272: '착불', 84273: '선불'}));
```

---

## 🛠️ 주요 함수

### delivery_calculator.php

```php
/**
 * 제품 타입에 따른 배송 정보 계산
 *
 * @param array $data 주문 데이터 (Type, Type_1 포함)
 * @param array $deliveryRules 배송 규칙 배열
 * @return array ['box' => 박스수량, 'price' => 택배비]
 */
function getDeliveryInfo($data, $deliveryRules)
```

### delivery_rules_config.php

```php
/**
 * 제품별 배송비 규칙
 *
 * @return array [
 *   'namecard' => ['box' => 1, 'price' => 3000],
 *   'inserted_b5_16' => ['box' => 2, 'price' => 3000],
 *   ...
 * ]
 */
return [
    'namecard' => ['box' => 1, 'price' => 3000],
    'merchandisebond' => ['box' => 1, 'price' => 3000],
    'sticker' => ['box' => 1, 'price' => 3000],
    // ...
];
```

---

## 📝 Type_1 JSON 처리

**문제**: Type_1 필드가 JSON 형식이고 줄바꿈(`\n`) 포함

**해결**:
```php
// Type_1 JSON 파싱
$type_1_display = $data['Type_1'];
if (!empty($data['Type_1']) && substr(trim($data['Type_1']), 0, 1) === '{') {
    $json_data = json_decode($data['Type_1'], true);
    if ($json_data && isset($json_data['formatted_display'])) {
        // 줄바꿈 제거하고 공백으로 변경 (한 줄 표시)
        $type_1_display = str_replace(array("\r\n", "\r", "\n"), ' ', $json_data['formatted_display']);
    }
}
```

**적용 파일**:
- `post_list52.php` (라인 357-364)
- `export_logen_excel.php` (라인 184-191)
- `export_logen_format.php` (라인 150-157)
- `delivery_manager.php` (라인 90-98, 331-338)

---

## ⚙️ 설정 및 인증

### Basic Auth 인증

```php
// delivery_manager.php
$admin_id = "duson1830";
$admin_pw = "du1830";

if (!isset($_SERVER['PHP_AUTH_USER']) ||
    $_SERVER['PHP_AUTH_USER'] !== $admin_id ||
    $_SERVER['PHP_AUTH_PW'] !== $admin_pw) {
    header('WWW-Authenticate: Basic realm="관리자모드"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
}
```

### 발송인 정보

```php
$sender = [
    'name' => '두손기획인쇄',
    'phone' => '02-2272-1830',
    'mobile' => '010-3305-1830',
    'zipcode' => '04563',
    'address' => '서울특별시 중구 을지로33길 33 두손빌딩'
];
```

---

## 📈 통계 및 모니터링

### delivery_manager.php 통계

**최근 30일 집계**:
```sql
SELECT
    COUNT(*) as total,                    -- 전체 주문
    SUM(CASE WHEN waybill_no IS NOT NULL
        AND waybill_no != '' THEN 1 ELSE 0 END) as shipped,  -- 발송 완료
    SUM(CASE WHEN (waybill_no IS NULL OR waybill_no = '')
        AND zip1 IS NOT NULL AND zip1 != '' THEN 1 ELSE 0 END) as pending  -- 발송 대기
FROM mlangorder_printauto
WHERE date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
```

**표시 항목**:
- 전체 주문 건수
- 발송 대기 건수 (운송장 미등록)
- 발송 완료 건수 (운송장 등록됨)

### 최근 발송 목록

**조회 쿼리**:
```sql
SELECT no, date, name, Type, Type_1, zip1, waybill_no, waybill_date
FROM mlangorder_printauto
WHERE zip1 IS NOT NULL AND zip1 != '' AND zip1 != '0'
ORDER BY no DESC LIMIT 20
```

**운송장 추적 링크**:
```
https://www.ilogen.com/web/personal/trace/{운송장번호}
```

---

## 🐛 문제 해결 (Troubleshooting)

### 1. "주문번호와 운송장번호 컬럼을 찾을 수 없습니다"

**원인**: 엑셀 파일(.xlsx)을 직접 업로드했거나, 헤더 컬럼명이 다름

**해결**:
1. 엑셀을 텍스트 파일(.txt)로 변환
2. 컬럼명 확인:
   - 주문번호: "주문번호", "주문NO", "OrderNo"
   - 운송장: "운송장", "송장번호", "waybill"

**디버그 모드**:
```php
error_log("=== 운송장 업로드 디버그 ===");
error_log("파일 형식: $file_ext");
error_log("총 컬럼 수: " . count($header));
foreach ($header as $idx => $col) {
    error_log("컬럼 $idx: [" . $col . "]");
}
```

### 2. "택배비가 2500원으로 표시됩니다"

**원인**: 하드코딩된 구버전 택배비 (2025-12-10 이전)

**해결**:
- `delivery_rules_config.php` 확인
- 최저 택배비 3,000원 통일 (2025-12-10 수정됨)

### 3. "품목명에 \n이 표시됩니다"

**원인**: Type_1 JSON의 formatted_display에 줄바꿈 문자 포함

**해결**: 줄바꿈 제거 코드 적용
```php
$type_1_display = str_replace(array("\r\n", "\r", "\n"), ' ', $json_data['formatted_display']);
```

### 4. "500 Internal Server Error"

**원인**: PHP 문법 오류 또는 라이브러리 로딩 실패

**해결**:
1. 로컬 테스트: `php -l delivery_manager.php`
2. 에러 로그 확인: `/var/log/apache2/error.log`
3. SimpleXLSX 등 외부 라이브러리 제거 (안정성 우선)

---

## 🔗 관련 링크

### 로젠택배

- **기업 로그인**: https://logis.ilogen.com/
- **배송 조회**: https://www.ilogen.com/web/personal/trace/{운송장번호}
- **시스템 매뉴얼**: https://www.ilogen.com/web/enterprise/system

### 내부 페이지

- **배송 관리**: http://dsp1830.shop/shop_admin/delivery_manager.php
- **로젠 주소 추출**: http://dsp1830.shop/shop_admin/post_list52.php

---

## 📅 변경 이력

| 날짜 | 변경 내용 | 파일 |
|------|----------|------|
| 2025-12-12 | .xlsx 직접 업로드 지원 제거, 텍스트 변환 안내 추가 | delivery_manager.php |
| 2025-12-10 | 택배비 최저금액 3000원 통일 | delivery_rules_config.php, post_list52.php |
| 2025-12-10 | 기타 컬럼에 주문번호 표시 | post_list52.php, export_logen_excel.php |
| 2025-12-10 | Type_1 JSON 줄바꿈 제거 처리 | 모든 엑셀 내보내기 파일 |
| 2025-12-03 | 로젠 주소 추출 시스템 구축 | post_list52.php 외 5개 파일 |

---

**문서 관리**: /var/www/html/CLAUDE_DOCS/04_OPERATIONS/DELIVERY_SYSTEM.md
