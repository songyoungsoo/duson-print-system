# 두손기획 DB 스키마 문서

> **생성일**: 2026-01-17
> **DB**: dsp1830 (MySQL 5.7+, utf8mb4)

---

## 목차

1. [테이블 개요](#1-테이블-개요)
2. [핵심 테이블 상세](#2-핵심-테이블-상세)
3. [가격표 테이블](#3-가격표-테이블)
4. [카테고리 테이블](#4-카테고리-테이블)
5. [게시판 테이블](#5-게시판-테이블)
6. [테이블 관계도](#6-테이블-관계도)

---

## 1. 테이블 개요

### 1.1 테이블 분류

| 분류 | 테이블 수 | 용도 |
|------|----------|------|
| 주문 | 2 | 주문 데이터 저장 |
| 가격표 | 9 | 품목별 가격 정보 |
| 카테고리 | 1 | 품목 옵션 트리 구조 |
| 게시판 | 16 | 포트폴리오, 게시판 등 |
| **총계** | **28** | |

### 1.2 전체 테이블 목록

```
주문 관련:
├── mlangorder_printauto          # 메인 주문 테이블
└── mlangorder_printauto_backup_* # 백업 테이블

가격표 (품목별):
├── mlangprintauto_namecard       # 명함
├── mlangprintauto_envelope       # 봉투
├── mlangprintauto_inserted       # 전단지/리플렛
├── mlangprintauto_msticker       # 자석스티커
├── mlangprintauto_sticker        # 스티커 (레거시)
├── mlangprintauto_cadarok        # 카다록
├── mlangprintauto_ncrflambeau    # NCR양식지
├── mlangprintauto_littleprint    # 포스터
└── mlangprintauto_merchandisebond # 상품권

카테고리:
└── mlangprintauto_transactioncate # 품목 옵션 카테고리

게시판:
├── mlang_portfolio_bbs           # 포트폴리오
├── mlang_leaflet_bbs             # 전단지 갤러리
├── mlang_correct_bbs             # 시안확인
├── mlang_file_bbs                # 파일전송
├── mlang_job_bbs                 # 구인구직
├── mlang_hj_bbs                  # 홍진게시판
├── mlang_websildesign_bbs        # 웹실디자인
├── mlang_board_bbs               # 일반게시판
├── mlang_bbs_admin               # 관리자 게시판
└── *_coment                      # 각 게시판 댓글 테이블
```

---

## 2. 핵심 테이블 상세

### 2.1 mlangorder_printauto (주문 테이블)

> **핵심**: 모든 주문 데이터가 저장되는 메인 테이블

#### 컬럼 정의

| 컬럼명 | 타입 | NULL | 설명 |
|--------|------|------|------|
| **no** | mediumint unsigned | NO | PK, AUTO_INCREMENT |
| **Type** | varchar(250) | YES | 품목 타입 코드 (예: namecard) |
| **product_type** | varchar(50) | YES | 품목 타입 (신규 필드) |
| **date** | datetime | NO | 주문 일시 |
| **OrderStyle** | varchar(100) | YES | 주문 상태 (no/yes/...) |

#### 주문자 정보

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| name | varchar(250) | 주문자명 |
| email | text | 이메일 |
| phone | varchar(20) | 전화번호 |
| Hendphone | varchar(20) | 휴대폰 |
| zip | varchar(10) | 우편번호 |
| zip1 | varchar(250) | 기본주소 |
| zip2 | varchar(250) | 상세주소 |
| bizname | varchar(50) | 사업자명 |

#### 금액 정보

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| money_1 | varchar(20) | 기본 금액 |
| money_2 | varchar(20) | 디자인 금액 |
| money_3 | varchar(20) | 옵션 금액 |
| money_4 | varchar(20) | 배송비 |
| money_5 | varchar(20) | 총 금액 |
| price_supply | int | 공급가 (신규) |
| price_vat | int | VAT 포함 총액 (신규) |
| price_vat_amount | int | VAT 금액 (신규) |

#### 제품 사양 (신규 스키마 v2)

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| spec_type | varchar(50) | 용지 종류 |
| spec_material | varchar(50) | 재질 |
| spec_size | varchar(100) | 크기 |
| spec_sides | varchar(20) | 단면/양면 |
| spec_design | varchar(20) | 디자인 포함 여부 |
| quantity_value | decimal(10,2) | 수량 값 |
| quantity_unit | varchar(10) | 단위 (매/연/부/권) |
| quantity_sheets | int | 매수 (전단지용) |
| quantity_display | varchar(100) | 표시용 수량 문자열 |

#### 추가 옵션

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| coating_enabled | tinyint(1) | 코팅 여부 |
| coating_type | varchar(20) | 코팅 종류 |
| coating_price | int | 코팅 가격 |
| folding_enabled | tinyint(1) | 접지 여부 |
| folding_type | varchar(20) | 접지 종류 |
| folding_price | int | 접지 가격 |
| creasing_enabled | tinyint(1) | 오시 여부 |
| creasing_lines | int | 오시 줄 수 |
| creasing_price | int | 오시 가격 |

#### 배송 정보

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| delivery | varchar(30) | 배송 방법 |
| delivery_company | varchar(50) | 배송 회사 |
| logen_box_qty | tinyint | 로젠 박스 수량 |
| logen_delivery_fee | int | 로젠 배송비 |
| logen_tracking_no | varchar(50) | 운송장 번호 |
| waybill_date | datetime | 운송장 발행일 |

#### 파일 및 메모

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| ImgFolder | text | 업로드 파일 경로 |
| uploaded_files | text | 업로드 파일 목록 (JSON) |
| cont | text | 작업 메모 |
| product_data_json | text | 전체 제품 데이터 (JSON) |

#### 인덱스

```sql
PRIMARY KEY (no)
INDEX idx_Type (Type)
INDEX idx_email (email(100))
INDEX idx_date (date)
INDEX idx_product_type (product_type)
INDEX idx_is_custom_product (is_custom_product)
INDEX idx_quote_id (quote_id)
INDEX idx_order_group_id (order_group_id)
INDEX idx_data_version (data_version)
```

---

## 3. 가격표 테이블

### 3.1 공통 구조

모든 가격표 테이블은 다음 기본 구조를 공유합니다:

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| no | mediumint unsigned | PK |
| style | varchar(100) | 용지 종류 (FK → transactioncate.no) |
| Section | varchar(200) | 규격 (FK → transactioncate.no) |
| quantity | float/int | 수량 |
| money | varchar(200) | 가격 |
| DesignMoney | varchar(100) | 디자인 가격 |
| POtype | varchar(100) | 단면(1)/양면(2) |

### 3.2 품목별 가격표

#### mlangprintauto_namecard (명함)

```sql
CREATE TABLE mlangprintauto_namecard (
    no mediumint unsigned PRIMARY KEY AUTO_INCREMENT,
    style varchar(100),           -- 용지 종류 (FK)
    Section varchar(200),         -- 규격 (FK)
    quantity float DEFAULT 0,     -- 수량 (500, 1000, 2000...)
    money varchar(200),           -- 가격
    DesignMoney varchar(100),     -- 디자인 가격
    POtype varchar(100),          -- 1=단면, 2=양면
    INDEX idx_style (style)
);
```

**샘플 데이터:**
| style | Section | quantity | money | DesignMoney | POtype |
|-------|---------|----------|-------|-------------|--------|
| 275 | 276 | 500 | 9000 | 5000 | 1 |
| 275 | 276 | 1000 | 18000 | 5000 | 1 |
| 275 | 276 | 2000 | 36000 | 5000 | 1 |

#### mlangprintauto_inserted (전단지/리플렛)

```sql
CREATE TABLE mlangprintauto_inserted (
    no mediumint unsigned PRIMARY KEY AUTO_INCREMENT,
    style varchar(100),
    Section varchar(200),
    quantity float DEFAULT 0,      -- 연 단위 (0.25, 0.5, 1, 2...)
    money varchar(200),
    TreeSelect int DEFAULT 0,      -- 접지 옵션
    DesignMoney int DEFAULT 10000,
    POtype varchar(50),            -- 1=단면, 2=양면
    quantityTwo varchar(100),      -- 매수 표시용
    INDEX idx_style (style)
);
```

#### mlangprintauto_msticker (자석스티커)

```sql
CREATE TABLE mlangprintauto_msticker (
    no mediumint unsigned PRIMARY KEY AUTO_INCREMENT,
    style varchar(100),            -- 자석 종류
    Section varchar(200),          -- 규격
    quantity float DEFAULT 0,      -- 수량
    money varchar(200),
    DesignMoney varchar(100),
    INDEX idx_style (style)
);
```

#### mlangprintauto_cadarok (카다록)

```sql
CREATE TABLE mlangprintauto_cadarok (
    no mediumint unsigned PRIMARY KEY AUTO_INCREMENT,
    style varchar(100),
    Section varchar(200),
    quantity float DEFAULT 0,      -- 부 단위
    money varchar(200),
    TreeSelect varchar(200),       -- 페이지 수
    DesignMoney varchar(100),
    POtype varchar(100),
    quantityTwo varchar(100),
    INDEX idx_style (style)
);
```

#### mlangprintauto_ncrflambeau (NCR양식지)

```sql
CREATE TABLE mlangprintauto_ncrflambeau (
    no mediumint unsigned PRIMARY KEY AUTO_INCREMENT,
    style varchar(100),
    Section varchar(200),
    quantity float DEFAULT 0,      -- 권 단위
    money varchar(200),
    TreeSelect varchar(200),       -- 복사 매수
    DesignMoney varchar(100),
    POtype varchar(100),
    quantityTwo varchar(100),
    INDEX idx_style (style)
);
```

---

## 4. 카테고리 테이블

### 4.1 mlangprintauto_transactioncate

> **핵심**: 모든 품목의 옵션을 트리 구조로 관리

```sql
CREATE TABLE mlangprintauto_transactioncate (
    no mediumint unsigned PRIMARY KEY AUTO_INCREMENT,
    Ttable varchar(250),      -- 품목 테이블명
    BigNo varchar(100),       -- 부모 카테고리 no (0=최상위)
    title varchar(250),       -- 표시명
    TreeNo varchar(100)       -- 정렬 순서
);
```

### 4.2 품목별 카테고리 수

| Ttable | 카테고리 수 | 설명 |
|--------|------------|------|
| inserted | 21 | 전단지 용지/규격 |
| sticker | 149 | 스티커 옵션 |
| NameCard | 32 | 명함 용지 |
| envelope | 20 | 봉투 종류 |
| NcrFlambeau | 61 | NCR 옵션 |
| LittlePrint | 15 | 포스터 옵션 |
| cadarok | 16 | 카다록 옵션 |
| MerchandiseBond | 14 | 상품권 옵션 |
| msticker | 17 | 자석스티커 옵션 |

### 4.3 트리 구조 예시

```
명함 (NameCard):
├── [275] 일반명함(쿠폰) (BigNo=0, 최상위)
│   ├── [276] 칼라코팅 (BigNo=275)
│   └── [277] 칼라비코팅 (BigNo=275)
├── [278] 고급수입지 (BigNo=0, 최상위)
│   └── [279] 휘라레216g (BigNo=278)
└── ...
```

### 4.4 조회 쿼리 예시

```sql
-- 최상위 카테고리 (용지 종류)
SELECT no, title FROM mlangprintauto_transactioncate
WHERE Ttable='namecard' AND BigNo='0';

-- 하위 카테고리 (규격)
SELECT no, title FROM mlangprintauto_transactioncate
WHERE Ttable='namecard' AND BigNo='275';

-- 가격 조회
SELECT * FROM mlangprintauto_namecard
WHERE style='275' AND Section='276' AND POtype='1';
```

---

## 5. 게시판 테이블

### 5.1 공통 구조 (mlang_*_bbs)

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| no | int | PK |
| title | varchar(255) | 제목 |
| content | text | 내용 |
| writer | varchar(100) | 작성자 |
| date | datetime | 작성일 |
| hit | int | 조회수 |
| file1~5 | varchar(255) | 첨부파일 |

### 5.2 댓글 테이블 (*_coment)

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| no | int | PK |
| bbs_no | int | 원글 번호 (FK) |
| content | text | 댓글 내용 |
| writer | varchar(100) | 작성자 |
| date | datetime | 작성일 |

---

## 6. 테이블 관계도

```
┌─────────────────────────────────────────────────────────────────┐
│                    mlangprintauto_transactioncate               │
│  (품목 카테고리 마스터)                                          │
│  ┌─────┬─────────┬───────┬──────────────┬────────┐             │
│  │ no  │ Ttable  │ BigNo │ title        │ TreeNo │             │
│  ├─────┼─────────┼───────┼──────────────┼────────┤             │
│  │ 275 │NameCard │ 0     │ 일반명함     │        │             │
│  │ 276 │NameCard │ 275   │ 칼라코팅     │        │             │
│  └─────┴─────────┴───────┴──────────────┴────────┘             │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            │ FK (style, Section)
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                    mlangprintauto_[product]                     │
│  (품목별 가격표)                                                 │
│  ┌─────┬───────┬─────────┬──────────┬───────┬────────┐         │
│  │ no  │ style │ Section │ quantity │ money │ POtype │         │
│  ├─────┼───────┼─────────┼──────────┼───────┼────────┤         │
│  │ 1   │ 275   │ 276     │ 500      │ 9000  │ 1      │         │
│  │ 2   │ 275   │ 276     │ 1000     │ 18000 │ 1      │         │
│  └─────┴───────┴─────────┴──────────┴───────┴────────┘         │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            │ 가격 조회 결과
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                    mlangorder_printauto                         │
│  (주문 테이블)                                                   │
│  ┌─────┬──────────┬─────────────┬──────────┬─────────┐         │
│  │ no  │ Type     │ product_type│ money_1  │ date    │         │
│  ├─────┼──────────┼─────────────┼──────────┼─────────┤         │
│  │ 1   │ 275/276  │ namecard    │ 9000     │ 2026-.. │         │
│  └─────┴──────────┴─────────────┴──────────┴─────────┘         │
└─────────────────────────────────────────────────────────────────┘
```

### 6.1 주요 관계

1. **transactioncate → 가격표**
   - `transactioncate.no` → `가격표.style` (용지 종류)
   - `transactioncate.no` → `가격표.Section` (규격)

2. **가격표 → 주문**
   - 가격 계산 결과가 `mlangorder_printauto`에 저장

3. **트리 구조 (Self-Reference)**
   - `transactioncate.BigNo` → `transactioncate.no`

---

## 부록: 유용한 쿼리

### A. 품목별 주문 통계

```sql
SELECT product_type, COUNT(*) as cnt, SUM(price_vat) as total
FROM mlangorder_printauto
WHERE date >= '2026-01-01'
GROUP BY product_type
ORDER BY cnt DESC;
```

### B. 가격표 조회 (명함 예시)

```sql
SELECT
    t1.title AS 용지,
    t2.title AS 규격,
    p.quantity AS 수량,
    p.money AS 가격,
    CASE p.POtype WHEN '1' THEN '단면' ELSE '양면' END AS 인쇄
FROM mlangprintauto_namecard p
JOIN mlangprintauto_transactioncate t1 ON p.style = t1.no
JOIN mlangprintauto_transactioncate t2 ON p.Section = t2.no
WHERE p.style = '275'
ORDER BY p.quantity;
```

### C. 카테고리 트리 조회

```sql
-- 재귀 CTE (MySQL 8.0+)
WITH RECURSIVE category_tree AS (
    SELECT no, title, BigNo, 0 AS level
    FROM mlangprintauto_transactioncate
    WHERE Ttable = 'namecard' AND BigNo = '0'

    UNION ALL

    SELECT c.no, c.title, c.BigNo, ct.level + 1
    FROM mlangprintauto_transactioncate c
    JOIN category_tree ct ON c.BigNo = ct.no
    WHERE c.Ttable = 'namecard'
)
SELECT * FROM category_tree ORDER BY level, no;
```

---

*문서 생성: 2026-01-17*
*DB 버전: MySQL 5.7+*
