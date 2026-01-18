# 두손기획인쇄 제품 설정 가이드

이 문서는 9개 제품의 설정 방법과 가격표 관리 방법을 설명합니다.

---

## 목차

1. [제품 체계 개요](#1-제품-체계-개요)
2. [제품별 폴더 구조](#2-제품별-폴더-구조)
3. [가격표 설정](#3-가격표-설정)
4. [카테고리 설정](#4-카테고리-설정)
5. [이미지 업로드 경로](#5-이미지-업로드-경로)
6. [제품별 상세 설정](#6-제품별-상세-설정)

---

## 1. 제품 체계 개요

### 1.1 제품 목록 (9개)

| 번호 | 품목명 | 폴더명 | 단위코드 | 단위명 | 가격표 테이블 |
|------|--------|--------|----------|--------|---------------|
| 1 | 전단지 | `inserted` | R | 연 | mlangprintauto_inserted |
| 2 | 스티커 | `sticker_new` | S | 매 | mlangprintauto_sticker_new |
| 3 | 자석스티커 | `msticker` | S | 매 | mlangprintauto_msticker |
| 4 | 명함 | `namecard` | S | 매 | mlangprintauto_namecard |
| 5 | 봉투 | `envelope` | S | 매 | mlangprintauto_envelope |
| 6 | 포스터 | `littleprint` | S | 매 | mlangprintauto_littleprint |
| 7 | 상품권 | `merchandisebond` | S | 매 | mlangprintauto_merchandisebond |
| 8 | 카다록 | `cadarok` | B | 부 | mlangprintauto_cadarok |
| 9 | NCR양식지 | `ncrflambeau` | V | 권 | mlangprintauto_ncrflambeau |

### 1.2 폴더명 규칙

폴더명은 시스템 전체에서 일관되게 사용되며, **절대 변경하면 안 됩니다**.

| 폴더명 | 사용 금지 명칭 | 작명 유래 |
|--------|---------------|-----------|
| `inserted` | leaflet | 신문 삽입 홍보물 |
| `sticker_new` | sticker | 구형 폴더와 혼동 방지 |
| `msticker` | - | 자석(magnet) + 스티커 |
| `littleprint` | poster | 대량 대비 소량 인쇄 |
| `merchandisebond` | giftcard | 상품권 고유 명칭 |
| `cadarok` | catalog | 발음 기반 작명 |
| `ncrflambeau` | form, ncr | NCR 양식지 고유 명칭 |

### 1.3 단위 코드 체계

```php
const UNIT_CODES = [
    'R' => '연',  // Ream - 전단지
    'S' => '매',  // Sheet - 스티커, 명함, 봉투, 포스터
    'B' => '부',  // Bundle - 카다록
    'V' => '권',  // Volume - NCR양식지
    'P' => '장',  // Piece - 개별 인쇄물
    'E' => '개'   // Each - 기타
];
```

---

## 2. 제품별 폴더 구조

### 2.1 공통 폴더 구조

각 제품 폴더는 다음 구조를 따릅니다:

```
mlangprintauto/[product]/
├── index.php                    # 제품 메인 페이지
├── add_to_basket.php           # 장바구니 추가 API
├── calculate_price_ajax.php    # 가격 계산 API
├── css/                        # 제품별 스타일
├── js/                         # 제품별 스크립트
└── img/                        # 제품별 이미지
```

### 2.2 실제 폴더 위치

```
/var/www/html/mlangprintauto/
├── inserted/          # 전단지
├── sticker_new/       # 스티커
├── msticker/          # 자석스티커
├── namecard/          # 명함
├── envelope/          # 봉투
├── littleprint/       # 포스터
├── merchandisebond/   # 상품권
├── cadarok/           # 카다록
└── ncrflambeau/       # NCR양식지
```

### 2.3 주요 파일 설명

| 파일 | 용도 |
|------|------|
| `index.php` | 제품 선택 및 옵션 UI |
| `add_to_basket.php` | 장바구니에 상품 추가 처리 |
| `calculate_price_ajax.php` | AJAX 가격 계산 요청 처리 |

---

## 3. 가격표 설정

### 3.1 가격표 테이블 구조

모든 가격표 테이블은 공통 구조를 공유합니다:

```sql
CREATE TABLE mlangprintauto_[product] (
    no mediumint unsigned PRIMARY KEY AUTO_INCREMENT,
    style varchar(100),           -- 용지/재질 종류 (FK)
    Section varchar(200),         -- 규격/크기 (FK)
    quantity float DEFAULT 0,     -- 수량
    money varchar(200),           -- 가격
    DesignMoney varchar(100),     -- 디자인 가격
    POtype varchar(100),          -- 1=단면, 2=양면
    INDEX idx_style (style)
);
```

| 컬럼 | 설명 |
|------|------|
| `style` | 용지/재질 카테고리 번호 |
| `Section` | 규격/크기 카테고리 번호 |
| `quantity` | 수량 (단위는 제품별로 다름) |
| `money` | 기본 가격 |
| `DesignMoney` | 디자인 포함 시 추가 가격 |
| `POtype` | 인쇄 방식 (1=단면, 2=양면) |

### 3.2 가격표 데이터 예시

#### 명함 가격표 (mlangprintauto_namecard)

| style | Section | quantity | money | DesignMoney | POtype |
|-------|---------|----------|-------|-------------|--------|
| 275 | 276 | 500 | 9000 | 5000 | 1 |
| 275 | 276 | 1000 | 18000 | 5000 | 1 |
| 275 | 276 | 2000 | 36000 | 5000 | 2 |

#### 전단지 가격표 (mlangprintauto_inserted)

| style | Section | quantity | quantityTwo | money | POtype |
|-------|---------|----------|-------------|-------|--------|
| 10 | 11 | 0.5 | 2000 | 15000 | 1 |
| 10 | 11 | 1 | 4000 | 28000 | 1 |
| 10 | 11 | 2 | 8000 | 52000 | 1 |

전단지의 경우 `quantityTwo` 컬럼에 매수가 저장됩니다.

### 3.3 가격 추가 방법

#### phpMyAdmin 사용

1. phpMyAdmin 접속: `http://localhost/phpmyadmin`
2. 데이터베이스 `dsp1830` 선택
3. 해당 가격표 테이블 선택
4. **삽입** 탭 클릭
5. 데이터 입력 후 저장

#### SQL 직접 입력

```sql
-- 명함 가격 추가
INSERT INTO mlangprintauto_namecard
(style, Section, quantity, money, DesignMoney, POtype)
VALUES
('275', '276', '3000', '54000', '5000', '1');

-- 전단지 가격 추가
INSERT INTO mlangprintauto_inserted
(style, Section, quantity, quantityTwo, money, DesignMoney, POtype)
VALUES
('10', '11', '3', '12000', '75000', '10000', '1');
```

### 3.4 가격 수정 방법

```sql
-- 특정 조건의 가격 수정
UPDATE mlangprintauto_namecard
SET money = '10000'
WHERE style = '275' AND Section = '276' AND quantity = 500;
```

### 3.5 가격 조회 방법

```sql
-- 명함 전체 가격표 조회
SELECT
    t1.title AS 용지,
    t2.title AS 규격,
    p.quantity AS 수량,
    p.money AS 가격,
    CASE p.POtype WHEN '1' THEN '단면' ELSE '양면' END AS 인쇄
FROM mlangprintauto_namecard p
JOIN mlangprintauto_transactioncate t1 ON p.style = t1.no
JOIN mlangprintauto_transactioncate t2 ON p.Section = t2.no
ORDER BY t1.title, t2.title, p.quantity;
```

---

## 4. 카테고리 설정

### 4.1 카테고리 테이블 구조

모든 제품의 옵션은 `mlangprintauto_transactioncate` 테이블에서 관리됩니다.

```sql
CREATE TABLE mlangprintauto_transactioncate (
    no mediumint unsigned PRIMARY KEY AUTO_INCREMENT,
    Ttable varchar(250),      -- 품목 테이블명
    BigNo varchar(100),       -- 부모 카테고리 번호 (0=최상위)
    title varchar(250),       -- 표시명
    TreeNo varchar(100)       -- 정렬 순서
);
```

| 컬럼 | 설명 |
|------|------|
| `Ttable` | 제품 코드 (예: namecard, inserted) |
| `BigNo` | 부모 카테고리 번호 (0이면 최상위) |
| `title` | 화면에 표시되는 이름 |
| `TreeNo` | 정렬 순서 |

### 4.2 트리 구조 예시

```
명함 (NameCard):
├── [275] 일반명함(쿠폰) (BigNo=0)
│   ├── [276] 칼라코팅 (BigNo=275)
│   └── [277] 칼라비코팅 (BigNo=275)
├── [278] 고급수입지 (BigNo=0)
│   ├── [279] 휘라레216g (BigNo=278)
│   └── [280] 마쉬멜로우250g (BigNo=278)
└── ...
```

### 4.3 카테고리 조회

```sql
-- 최상위 카테고리 조회 (용지 종류)
SELECT no, title
FROM mlangprintauto_transactioncate
WHERE Ttable = 'namecard' AND BigNo = '0'
ORDER BY TreeNo;

-- 하위 카테고리 조회 (규격)
SELECT no, title
FROM mlangprintauto_transactioncate
WHERE Ttable = 'namecard' AND BigNo = '275'
ORDER BY TreeNo;
```

### 4.4 카테고리 추가

```sql
-- 최상위 카테고리 추가 (용지 종류)
INSERT INTO mlangprintauto_transactioncate
(Ttable, BigNo, title, TreeNo)
VALUES
('namecard', '0', '신규용지', '100');

-- 하위 카테고리 추가 (규격)
INSERT INTO mlangprintauto_transactioncate
(Ttable, BigNo, title, TreeNo)
VALUES
('namecard', '신규용지의no', '90x50mm', '1');
```

### 4.5 품목별 Ttable 값

| 품목 | Ttable 값 |
|------|-----------|
| 전단지 | `inserted` |
| 스티커 | `sticker` |
| 자석스티커 | `msticker` |
| 명함 | `NameCard` (대문자 주의) |
| 봉투 | `envelope` |
| 포스터 | `LittlePrint` (대문자 주의) |
| 상품권 | `MerchandiseBond` (대문자 주의) |
| 카다록 | `cadarok` |
| NCR양식지 | `NcrFlambeau` (대문자 주의) |

---

## 5. 이미지 업로드 경로

### 5.1 제품 이미지 경로

각 제품의 샘플 이미지는 다음 경로에 저장됩니다:

```
/var/www/html/mlangprintauto/[product]/img/
```

예시:
- 명함 이미지: `/var/www/html/mlangprintauto/namecard/img/`
- 전단지 이미지: `/var/www/html/mlangprintauto/inserted/img/`

### 5.2 주문 파일 업로드 경로

고객이 업로드한 디자인 파일은 다음 경로에 저장됩니다:

```
/var/www/html/upload/printauto/[주문번호]/
```

예시:
- 주문번호 12345의 파일: `/var/www/html/upload/printauto/12345/`

### 5.3 업로드 경로 구조

```
/var/www/html/upload/
├── printauto/
│   ├── 12345/          # 주문번호별 폴더
│   │   ├── file1.pdf
│   │   └── file2.ai
│   ├── 12346/
│   └── ...
└── temp/               # 임시 파일
```

### 5.4 이미지 URL 생성

```php
// 제품 이미지 URL
$product_img_url = "/mlangprintauto/{$product}/img/sample.jpg";

// 업로드 파일 URL
$upload_url = "/upload/printauto/{$order_no}/{$filename}";
```

---

## 6. 제품별 상세 설정

### 6.1 전단지 (inserted)

**특징**: 연(Ream) 단위 사용, 매수는 DB에서 조회

| 설정 항목 | 값 |
|-----------|-----|
| 폴더명 | `inserted` |
| 단위 | 연 (R) |
| 가격표 | `mlangprintauto_inserted` |
| 카테고리 | `Ttable = 'inserted'` |

**수량 변환 규칙**:
- 연수는 `quantity` 컬럼에 저장
- 매수는 `quantityTwo` 컬럼에서 조회 (계산하지 않음)

```
0.5연 = 2,000매
1연 = 4,000매
2연 = 8,000매
```

### 6.2 명함 (namecard)

**특징**: 매(Sheet) 단위, 천단위 변환 로직 있음

| 설정 항목 | 값 |
|-----------|-----|
| 폴더명 | `namecard` |
| 단위 | 매 (S) |
| 가격표 | `mlangprintauto_namecard` |
| 카테고리 | `Ttable = 'NameCard'` (대문자) |

**수량 변환**:
- 입력값이 10 미만이면 x1000
- 예: 1 입력 시 1,000매로 처리

### 6.3 봉투 (envelope)

| 설정 항목 | 값 |
|-----------|-----|
| 폴더명 | `envelope` |
| 단위 | 매 (S) |
| 가격표 | `mlangprintauto_envelope` |
| 카테고리 | `Ttable = 'envelope'` |

**수량 변환**: 명함과 동일 (10 미만 x1000)

### 6.4 스티커 (sticker_new)

| 설정 항목 | 값 |
|-----------|-----|
| 폴더명 | `sticker_new` |
| 단위 | 매 (S) |
| 가격표 | `mlangprintauto_sticker_new` |
| 카테고리 | `Ttable = 'sticker'` |

**특징**: 수학 계산 기반 가격 산출

### 6.5 자석스티커 (msticker)

| 설정 항목 | 값 |
|-----------|-----|
| 폴더명 | `msticker` |
| 단위 | 매 (S) |
| 가격표 | `mlangprintauto_msticker` |
| 카테고리 | `Ttable = 'msticker'` |

### 6.6 포스터 (littleprint)

| 설정 항목 | 값 |
|-----------|-----|
| 폴더명 | `littleprint` |
| 단위 | 매 (S) |
| 가격표 | `mlangprintauto_littleprint` |
| 카테고리 | `Ttable = 'LittlePrint'` (대문자) |

**주의**: 코드에서는 반드시 `littleprint` 사용, `poster` 사용 금지

### 6.7 상품권 (merchandisebond)

| 설정 항목 | 값 |
|-----------|-----|
| 폴더명 | `merchandisebond` |
| 단위 | 매 (S) |
| 가격표 | `mlangprintauto_merchandisebond` |
| 카테고리 | `Ttable = 'MerchandiseBond'` (대문자) |

### 6.8 카다록 (cadarok)

| 설정 항목 | 값 |
|-----------|-----|
| 폴더명 | `cadarok` |
| 단위 | 부 (B) |
| 가격표 | `mlangprintauto_cadarok` |
| 카테고리 | `Ttable = 'cadarok'` |

**특징**: `TreeSelect` 컬럼에 페이지 수 저장

### 6.9 NCR양식지 (ncrflambeau)

| 설정 항목 | 값 |
|-----------|-----|
| 폴더명 | `ncrflambeau` |
| 단위 | 권 (V) |
| 가격표 | `mlangprintauto_ncrflambeau` |
| 카테고리 | `Ttable = 'NcrFlambeau'` (대문자) |

**특징**: `TreeSelect` 컬럼에 복사 매수 저장

---

## 가격 설정 체크리스트

새 제품 가격 추가 시:

- [ ] 카테고리 추가 (용지/재질)
- [ ] 카테고리 추가 (규격/크기)
- [ ] 가격표 데이터 입력
- [ ] 단면/양면 구분 확인
- [ ] 디자인 가격 설정
- [ ] 웹 페이지에서 가격 표시 확인
- [ ] 장바구니 추가 테스트
- [ ] 주문 완료까지 테스트

---

## 문제 해결

### 가격이 표시되지 않는 경우

1. 카테고리 번호 확인:
   ```sql
   SELECT no, title FROM mlangprintauto_transactioncate
   WHERE Ttable = '[품목]';
   ```

2. 가격표 데이터 확인:
   ```sql
   SELECT * FROM mlangprintauto_[품목]
   WHERE style = '[카테고리번호]';
   ```

3. style, Section 값이 카테고리 번호와 일치하는지 확인

### 새 용지 추가 후 선택 불가

1. 카테고리 `BigNo` 값 확인 (최상위는 '0')
2. 가격표에 해당 style 데이터 존재 확인

---

*Version: 1.0*
*Last Updated: 2026-01-18*
