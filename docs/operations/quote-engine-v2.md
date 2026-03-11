## 견적서 엔진 V2 (Quote Engine V2)

### 개요
독립형 견적서/거래명세서 생성 시스템. 기존 홈페이지 코드와 완전 분리.

**배포일**: 2026-03-11
**접근**: 관리자 대시보드 → 소통·견적 → "견적서 엔진" / "거래처 관리"

### 핵심 원칙
- 기존 코드 수정 없음 (config.php 사이드바 2줄만 추가)
- DB는 `qe_` prefix 테이블만 사용 (기존 테이블은 SELECT-only)
- 장바구니 없음, 주문 전환 없음 — 견적서/거래명세서 생성 전용
- 가격 계산기는 독립 모듈 (`QE_PriceCalculator`) — 기존 `PriceCalculationService` 미참조

### 파일 구조

```
includes/quote-engine/           ← 코어 엔진 (3파일)
├── PriceCalculator.php          # 9개 제품 가격 계산 (DB lookup + 스티커 공식)
├── QuoteEngine.php              # 견적서 CRUD, 번호 생성, 변환
└── CustomerManager.php          # 거래처 관리

api/quote-engine/                ← API 엔드포인트 (8파일)
├── calculate.php                # POST — 가격 계산
├── options.php                  # GET  — 드롭다운/수량/프리미엄옵션
├── save.php                     # POST — 견적서 저장/수정
├── delete.php                   # POST — 견적서 삭제
├── status.php                   # POST — 상태 변경 + 거래명세서 변환
├── customers.php                # GET/POST — 거래처 CRUD
├── pdf.php                      # GET  — PDF 다운로드 (mPDF)
└── email.php                    # POST — 이메일 발송 (PHPMailer)

dashboard/quote-engine/          ← UI 페이지 (5파일)
├── index.php                    # 견적서 목록 (필터/검색/페이지네이션)
├── create.php                   # 새 견적서 (품목계산+수동+추가항목)
├── detail.php                   # 상세 보기 (PDF/이메일/인쇄/변환)
├── customers.php                # 거래처 관리
└── setup.php                    # DB 테이블 생성 (1회 실행)
```

### DB 테이블 (4개)

| 테이블 | 용도 | 주요 컬럼 |
|--------|------|----------|
| `qe_customers` | 거래처 | company, name, phone, email, business_number |
| `qe_quotes` | 견적서/거래명세서 | quote_no, doc_type, customer_*, totals, status |
| `qe_items` | 품목 행 | quote_id, type(product/manual/extra), prices |
| `qe_templates` | 템플릿 (미사용) | name, items_json |

### 견적서 번호 체계
- 견적서: `QE-YYYYMMDD-NNN` (예: QE-20260311-001)
- 거래명세서: `TX-YYYYMMDD-NNN` (예: TX-20260311-001)

### 상태 흐름
```
draft(임시저장) → completed(작성완료) → sent(발송됨) → expired(만료)
                                         ↓
                              convertToTransaction → TX-* 생성 (원본 유지)
```

### 가격 계산 방식
- **8개 제품** (명함, 전단지, 봉투, 포스터, 상품권, 카다록, NCR, 자석스티커): DB 테이블 lookup
- **스티커**: 수학 공식 (shop_d1~d4 요율 × 사이즈 × 수량 + 도무송 + 특수용지)
- 제품 코드: `sticker` (NOT `sticker_new` — PriceCalculator 내부 키)

### API 사용법

```
# 제품 목록
GET /api/quote-engine/options.php?action=products

# 드롭다운 캐스케이드
GET /api/quote-engine/options.php?action=dropdown&product=namecard&parent=0
GET /api/quote-engine/options.php?action=dropdown&product=namecard&parent=275

# 수량
GET /api/quote-engine/options.php?action=quantities&product=namecard&style=275&Section=276

# 가격 계산
POST /api/quote-engine/calculate.php
Body: {"product":"namecard","style":275,"section":276,"quantity":500}

# 스티커 가격
POST /api/quote-engine/calculate.php
Body: {"product":"sticker","jong":"jil","garo":50,"sero":50,"mesu":1000,"domusong":"00000","uhyung":0}

# 견적서 저장
POST /api/quote-engine/save.php
Body: { customer_*, doc_type, valid_days, items: [...] }

# 거래명세서 변환
POST /api/quote-engine/status.php
Body: { "id": 1, "action": "convert" }
```

### 주의사항
1. `setup.php`는 최초 1회만 실행 (IF NOT EXISTS — 재실행 안전)
2. PDF는 mPDF + NanumGothic 폰트 사용 (`/usr/share/fonts/truetype/nanum/`)
3. 이메일은 PHPMailer + SMTP (네이버) 사용
4. 기존 견적서 시스템(`quote-system.md`)과 완전 독립 — 테이블/코드 공유 없음
