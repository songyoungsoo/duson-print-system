## 📋 견적서 시스템

### 시스템 아키텍처 (2026-02-27)

**3개의 독립적 견적서 경로:**

| 경로 | 임시 테이블 | 최종 테이블 | 가격 계산 |
|------|-----------|-----------|----------|
| 고객 견적 | `quotation_temp` (68컬럼) | `quotes` + `quote_items` | 프론트 계산기 → POST 전달 (dumb storage) |
| 관리자 견적 | `admin_quotation_temp` | `admin_quotes` + `admin_quote_items` | Adapter → 품목 계산기 API 호출 |
| 플로팅 견적받기 | — | `quote_requests` | 프론트 계산기 → POST 전달 |

**데이터 흐름 (고객 경로):**
```
품목 index.php → JS 계산기 → addToQuotation()
  → POST to quote/add_to_quotation_temp.php
    → quotation_temp 저장 (가격 그대로, 재계산 안 함)
      → create.php에서 합산 → quotes+quote_items 저장
```

**핵심 원칙**: 견적서는 가격을 자체 계산하지 않음. 품목 계산기가 계산한 값을 POST로 받아 저장만 함.

**필드명 계약 (add_to_quotation_temp.php):**
- `calculated_price` / `price` / `st_price` → 공급가액 (우선순위 순)
- `calculated_vat_price` / `vat_price` / `st_price_vat` → VAT포함가
- `premium_options_data` / `premium_options` → 프리미엄옵션 JSON
- `premium_options_total` → 옵션 합계

**⚠️ 고객 견적 경로는 참조용** — 관리자 이메일 알림으로 누가 뭘 받아갔는지 확인용. 실제 주문은 별도 진행.

---

### 견적서 상태 흐름 (Admin Quotes)

### 견적서 상태 흐름 (CRITICAL)

```
생성/저장 → draft (임시저장)
            ↓ "발송" 버튼 클릭 (이메일 발송)
          sent (발송됨)
            ↓ 고객 열람
          viewed (열람)
            ↓ 고객 승인/거절
          accepted / rejected
```

### 상태 변경 규칙
```php
// ✅ CORRECT: 저장 시 무조건 draft
$status = 'draft';  // saveQuote()

// ✅ CORRECT: sent는 이메일 발송 API에서만 변경
$manager->updateStatus($quoteId, 'sent');  // send_email.php

// ❌ WRONG: 저장 시 sent 설정 (이메일 안 보냈는데 "발송됨" 표시)
$status = $isDraft ? 'draft' : 'sent';
```

### 견적서 테마 — 홈페이지 헤더색 통일 (2026-02-15)

**두 개의 독립적 견적서 이메일 시스템이 홈페이지 헤더색(`#1E4E79`)으로 통일됨:**

| 시스템 | 파일 | 용도 |
|--------|------|------|
| 관리자 견적서 | `QuoteRenderer.php` | 다중 품목, 회사정보, PDF 첨부 (HTML/Email/PDF 3가지 출력) |
| 플로팅 견적받기 | `quote_request_api.php` | 단일 품목, 공급받는자/공급자 50:50 테이블, `$customerBody` 인라인 HTML |

**컬러 팔레트 (홈페이지 헤더 `#1E4E79` 기준):**

| 용도 | 색상 코드 |
|------|----------|
| 헤더/라벨 셀 배경 | `#1E4E79` (홈페이지 `.top-header` 동일) |
| 테이블 외곽선 | `#2a6496` |
| 테이블 내부선 | `#94a3b8` |
| 헤더 내부선 | `#3a7ab5` |
| 품목 테이블 내부선 | `#cbd5e1` |
| 연한 블루 배경 (합계행/공급자라벨) | `#e8eff7` |
| 값 셀 배경 | `#f8fafc` |
| 헤더 글씨 | `#ffffff` |
| PDF Fill | `SetFillColor(30, 78, 121)` |
| PDF Draw | `SetDrawColor(42, 100, 150)` |

**플로팅 견적서 이메일 구조:**
- 공급받는자 (50%): 견적일, 상호/성명, 연락처, 이메일
- 공급자 (50%): 등록번호, 상호, 대표자, 연락처
- company_info.php SSOT 활용 (`getCompanyInfo()`)

**QuoteRenderer 출력별 테마 적용:**

| 출력 형식 | 메서드 | 스타일 방식 |
|----------|--------|------------|
| HTML 미리보기 | `renderLegacyHTML()` | CSS 클래스 기반 |
| 이메일 발송 | `renderEmailBody()` | 인라인 스타일 (이메일 호환) |
| Legacy PDF | `renderLegacyPDF()` | TCPDF SetFillColor/SetTextColor/SetDrawColor |
| Standard PDF | `renderStandardPDF()` | mPDF CSS |

**관련 파일:**
- `admin/mlangprintauto/quote/includes/QuoteRenderer.php` — 관리자 견적서 렌더러
- `mlangprintauto/quote/standard/layout.php` — 브라우저 미리보기 CSS
- `includes/quote_request_api.php` — 플로팅 견적받기 고객 이메일
- `mlangprintauto/includes/company_info.php` — 회사 정보 SSOT

### 견적번호 체계 (2026-02-16)

**3가지 독립적 번호 체계:**

| 시스템 | 접두어 | 형식 | 예시 | 테이블 |
|--------|--------|------|------|--------|
| 관리자 견적서 | `AQ` | `AQ-YYYYMMDD-NNNN` | `AQ-20260208-0004` | `admin_quotes.quote_no` |
| 플로팅 견적받기 | `FQ` | `FQ-YYYYMMDD-NNN` | `FQ-20260216-001` | `quote_requests.quote_no` |
| 세금계산서 | `TAX` | `TAXYYYYMMDDNNNNNN` | `TAX20241109000001` | `tax_invoices.invoice_number` |

**FQ 번호 생성 로직** (`includes/quote_request_api.php`):
```php
// 당일 MAX 순번 조회 → +1
$fqPrefix = 'FQ-' . date('Ymd') . '-';
$seqQuery = "SELECT quote_no FROM quote_requests WHERE quote_no LIKE ? ORDER BY quote_no DESC LIMIT 1";
// → FQ-20260216-001, FQ-20260216-002, ...
```

### 견적 삭제 기능 (2026-02-19)

**대시보드 견적 목록** (`/dashboard/quotes/index.php`):
- 개별 삭제: 각 행 액션 컬럼의 빨간 "삭제" 링크
- 일괄 삭제: 행 앞 체크박스 선택 → 하단 빨간 바에서 "선택 삭제"
- 전체선택: thead 체크박스로 현재 페이지 전체 선택/해제

**API**: `/dashboard/api/quotes.php`
| action | 입력 | 동작 |
|--------|------|------|
| `delete` | `{ id: N }` | 단일 견적 삭제 (items → quotes 순서) |
| `bulk_delete` | `{ ids: [N, ...] }` | 일괄 삭제 |

**⚠️ 하드 삭제** — `admin_quotes` + `admin_quote_items` 에서 완전 삭제 (복구 불가)

### 견적서 팝업 창 (2026-02-21)

**대시보드 견적 목록** (`/dashboard/quotes/index.php`)에서 상세/수정/미리보기/새 견적 클릭 시 팝업 창으로 열림.

**동작 방식**:
- `window.open()` 팝업 (초기 960px × 화면 92%)
- 각 페이지 로드 후 콘텐츠 높이/너비 측정 → `window.resizeTo()` 자동 조절 + 화면 중앙 재배치
- `window.opener` 존재 시에만 리사이즈 실행 (직접 URL 접속 시에는 일반 페이지)

**적용 파일 (5개)**:
| 파일 | 팝업 이름 |
|------|----------|
| `dashboard/quotes/index.php` | `openQuotePopup()` 공통 함수 |
| `admin/mlangprintauto/quote/detail.php` | 견적 상세 |
| `admin/mlangprintauto/quote/edit.php` | 견적 수정 |
| `admin/mlangprintauto/quote/preview.php` | 견적 미리보기 |
| `admin/mlangprintauto/quote/create.php` | 새 견적 작성 |

### 이메일 발송 제한
- SMTP: 네이버 (`smtp.naver.com:465/ssl`, dsp1830)
- 네이버→네이버: ✅ 정상
- 네이버→Gmail: ⚠️ Gmail 스팸 필터에 의해 차단됨 (미해결)
- 향후: Gmail SMTP 이중 발송 구현 예정

### 대시보드 iframe 임베드
```
dashboard/embed.php?url=/admin/mlangprintauto/admin.php  → 주문 관리(구)
dashboard/embed.php?url=/admin/mlangprintauto/admin.php?mode=sian  → 교정 관리(구)
dashboard/embed.php?url=/admin/mlangprintauto/quote/  → 견적서(구)
dashboard/embed.php?url=/admin/mlangprintauto/option_prices.php  → 옵션 가격
```

