# 디렉토리 구조 가이드

두손기획인쇄 시스템의 디렉토리 구조 및 핵심 파일 안내서

**생성일**: 2026-01-17
**Document Root**: `/var/www/html`

---

## 목차

1. [전체 구조 개요](#1-전체-구조-개요)
2. [핵심 디렉토리](#2-핵심-디렉토리)
3. [제품별 폴더](#3-제품별-폴더)
4. [주문 처리 폴더](#4-주문-처리-폴더)
5. [관리자 폴더](#5-관리자-폴더)
6. [파일명 규칙](#6-파일명-규칙)

---

## 1. 전체 구조 개요

```
/var/www/html/
├── db.php                      # DB 연결 (환경 자동 감지)
├── config.env.php              # 환경 설정
├── CLAUDE.md                   # Claude Code 개발 가이드
│
├── includes/                   # 공용 컴포넌트 (SSOT)
├── lib/                        # 코어 라이브러리
├── mlangprintauto/             # 제품 페이지 (프론트엔드)
├── mlangorder_printauto/       # 주문 처리
├── mlang_admin/                # 관리자 페이지
├── css/                        # 전역 스타일시트
├── js/                         # 전역 JavaScript
├── images/                     # 정적 이미지
├── upload/                     # 업로드 파일 저장소
└── CLAUDE_DOCS/                # 기술 문서
```

---

## 2. 핵심 디렉토리

### 2.1 /includes/ - 공용 컴포넌트

시스템 전체에서 사용하는 공용 클래스와 함수

```
includes/
├── QuantityFormatter.php       # [SSOT] 수량/단위 포맷팅
├── ProductSpecFormatter.php    # [SSOT] 제품 사양 표시
├── PriceCalculationService.php # [SSOT] 가격 계산
├── DataAdapter.php             # 데이터 표준화
├── OrderDataService.php        # 주문 데이터 서비스
│
├── auth.php                    # 세션/인증 (8시간)
├── auth_functions.php          # 인증 헬퍼 함수
│
├── StandardUploadHandler.php   # 파일 업로드 표준
├── UploadPathHelper.php        # 업로드 경로 생성
├── ImagePathResolver.php       # 이미지 경로 해석
│
├── header.php                  # 공용 헤더
├── footer.php                  # 공용 푸터
├── nav.php                     # 네비게이션
├── sidebar.php                 # 사이드바
│
├── functions.php               # 범용 헬퍼 함수
├── db_constants.php            # DB 상수 정의
└── safe_json_response.php      # 안전한 JSON 응답
```

**중요 SSOT 파일**:
| 파일 | 역할 | 사용처 |
|------|------|--------|
| `QuantityFormatter.php` | 수량 표시 (1,000매, 0.5연) | 전체 시스템 |
| `ProductSpecFormatter.php` | 규격/옵션 2줄 표시 | 장바구니, 주문서, 견적서 |
| `PriceCalculationService.php` | 가격 계산 | 모든 제품 페이지 |

### 2.2 /lib/ - 코어 라이브러리

```
lib/
└── core_print_logic.php        # 중앙 진입점 (파사드)
```

**core_print_logic.php 함수**:
```php
duson_format_qty(500, 'S');     // "500매"
duson_lookup_sheets(0.5);       // 2000 (전단지 매수 조회)
duson_get_unit('inserted');     // "연"
```

### 2.3 /css/ - 스타일시트

```
css/
├── common-styles.css           # 전역 공통 스타일
├── cart.css                    # 장바구니 스타일
├── order.css                   # 주문서 스타일
├── admin.css                   # 관리자 스타일
└── responsive.css              # 반응형 스타일
```

### 2.4 /js/ - JavaScript

```
js/
├── common.js                   # 공통 함수
├── cart.js                     # 장바구니 로직
├── order.js                    # 주문 로직
├── price-calculator.js         # 가격 계산 (프론트)
└── premium-options-loader.js   # 프리미엄 옵션 DB 로더 (공통)
```

---

## 3. 제품별 폴더

### 3.1 제품 폴더 매핑 (필수 준수)

| 품목명 | 폴더명 | 금지 명칭 |
|--------|--------|-----------|
| 전단지 | `inserted` | leaflet |
| 스티커 | `sticker_new` | sticker |
| 자석스티커 | `msticker` | - |
| 명함 | `namecard` | - |
| 봉투 | `envelope` | - |
| 포스터 | `littleprint` | poster |
| 상품권 | `merchandisebond` | giftcard |
| 카다록 | `cadarok` | catalog |
| NCR양식지 | `ncrflambeau` | form, ncr |

### 3.2 제품 폴더 구조

```
mlangprintauto/
├── inserted/                   # 전단지 (리플렛 포함)
│   ├── index.php               # 메인 페이지
│   ├── calculate_price_ajax.php # 가격 계산 API
│   ├── add_to_basket.php       # 장바구니 담기 API
│   ├── inc.php                 # 설정/초기화
│   └── ajax/                   # AJAX 핸들러
│       └── update_order_with_options.php
│
├── namecard/                   # 명함
│   ├── index.php
│   ├── calculate_price_ajax.php
│   └── add_to_basket.php
│
├── envelope/                   # 봉투
├── sticker_new/                # 스티커
├── msticker/                   # 자석스티커
├── littleprint/                # 포스터
├── cadarok/                    # 카다록
├── ncrflambeau/                # NCR양식지
├── merchandisebond/            # 상품권
│
├── cart.php                    # 장바구니 페이지
├── quote/                      # 견적서
│   ├── quote_view.php          # 견적서 보기
│   └── quote_pdf.php           # PDF 생성
│
└── shop/                       # 공통 유틸리티
    └── shop_temp_helper.php    # 임시 데이터 헬퍼
```

### 3.3 제품 페이지 표준 구조

각 제품 폴더의 `index.php` 표준 구조:

```php
<?php
// 1. 설정 로드
include "inc.php";
include "../../db.php";

// 2. 인증 (필요시)
require_once "../../includes/auth.php";

// 3. 데이터 조회 (옵션, 가격표 등)

// 4. HTML 출력
?>
<!DOCTYPE html>
<html>
<head>...</head>
<body>
    <!-- 제품 선택 폼 -->
    <!-- 가격 표시 -->
    <!-- 장바구니 버튼 -->
</body>
</html>
```

---

## 4. 주문 처리 폴더

### 4.1 구조

```
mlangorder_printauto/
├── ProcessOrder_unified.php    # 주문 처리 (INSERT)
├── OrderFormOrderTree.php      # 주문서 폼
├── OrderComplete_universal.php # 주문 완료 페이지
├── OrderFormPrint.php          # 주문서 출력
├── OrderFormPrintExcel.php     # 엑셀 출력
│
├── OnlineOrder.php             # 온라인 주문 엔트리
├── OnlineOrder_unified.php     # 통합 온라인 주문
│
├── WindowSian.php              # 시안 확인 팝업
└── check_proofreading_status.php # 교정 상태 확인
```

### 4.2 주문 처리 흐름

```
1. 장바구니 (cart.php)
   └── 세션에 상품 저장
        ↓
2. 주문서 작성 (OrderFormOrderTree.php)
   └── 배송정보 입력
        ↓
3. 주문 처리 (ProcessOrder_unified.php)
   └── mlangorder_printauto 테이블에 INSERT
        ↓
4. 주문 완료 (OrderComplete_universal.php)
   └── 주문번호 표시, 결제 안내
```

### 4.3 핵심 파일 상세

**ProcessOrder_unified.php**:
- POST 데이터 수신
- 장바구니 아이템 순회
- 각 아이템별 DB INSERT
- 파일 업로드 처리 (StandardUploadHandler)
- 주문 완료 리다이렉트

**OrderComplete_universal.php**:
- 주문번호로 DB 조회
- ProductSpecFormatter로 사양 표시
- 결제 정보 안내

---

## 5. 관리자 폴더

### 5.1 구조

```
dashboard/                         # 신규 관리자 대시보드 (Tailwind CSS)
├── index.php                      # 메인 대시보드
├── includes/
│   ├── header.php                 # 공통 헤더
│   ├── sidebar.php                # 사이드바 메뉴
│   ├── footer.php                 # 푸터
│   ├── config.php                 # 대시보드 설정
│   └── auth.php                   # 인증 가드
├── api/
│   ├── base.php                   # API 공통 베이스
│   ├── premium_options.php        # 프리미엄 옵션 CRUD + 재계산
│   └── gallery.php                # 갤러리 관리 API
├── premium-options/
│   └── index.php                  # 프리미엄 옵션 관리 UI
├── gallery/
│   └── index.php                  # 갤러리 관리 UI
├── orders/
│   └── view.php                   # 주문 상세 보기
├── proofs/
│   ├── index.php                  # 교정 관리
│   └── api.php                    # 교정 API
├── stats/
│   └── index.php                  # 주문 통계
├── visitors/
│   └── index.php                  # 방문자 분석
└── embed.php                      # 레거시 관리 iframe 임베드

api/                               # 고객용 공개 API
├── premium_options.php            # 프리미엄 옵션 가격 조회 (캐시 5분)
├── quote/
│   └── calculate_price.php        # 견적 가격 계산
├── get_portfolio_gallery.php      # 포트폴리오 갤러리
├── get_leaflet_samples.php        # 전단지 샘플
├── get_sticker_gallery.php        # 스티커 갤러리
├── gallery_items.php              # 갤러리 항목
├── generate_quotation_api.php     # 견적서 PDF 생성
└── orders/
    ├── reorder.php                # 재주문
    └── cancel.php                 # 주문 취소

mlang_admin/
├── index.php                   # 관리자 대시보드 (레거시)
├── login.php                   # 관리자 로그인
├── logout.php                  # 로그아웃
│
├── order_list.php              # 주문 목록
├── order_view.php              # 주문 상세
├── order_edit.php              # 주문 수정
│
├── product_list.php            # 제품 관리
├── price_manage.php            # 가격표 관리
├── category_manage.php         # 카테고리 관리
│
├── member_list.php             # 회원 목록
├── member_view.php             # 회원 상세
│
├── css/                        # 관리자 스타일
├── js/                         # 관리자 스크립트
└── includes/                   # 관리자 전용 컴포넌트
```

### 5.2 관리자 인증

```php
// 관리자 페이지 상단
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
```

---

## 6. 파일명 규칙

### 6.1 필수 규칙

| 규칙 | 설명 | 예시 |
|------|------|------|
| 소문자 필수 | Linux 대소문자 구분 | `auth.php` (O), `Auth.php` (X) |
| 언더스코어 | 단어 구분 | `order_list.php` |
| 테이블명 | 항상 소문자 | `mlangorder_printauto` |

### 6.2 파일 유형별 접두사

| 접두사 | 용도 | 예시 |
|--------|------|------|
| `calculate_` | 가격 계산 | `calculate_price_ajax.php` |
| `add_to_` | 추가 작업 | `add_to_basket.php` |
| `get_` | 데이터 조회 | `get_paper_types.php` |
| `update_` | 수정 작업 | `update_order.php` |
| `delete_` | 삭제 작업 | `delete_file.php` |

### 6.3 Include 경로 규칙

```php
// 상대 경로 사용 (프로젝트 내)
include "../db.php";
include "../../includes/auth.php";

// 절대 경로 사용 (권장)
require_once __DIR__ . '/../includes/QuantityFormatter.php';
```

---

## 7. 업로드 디렉토리

### 7.1 구조

```
upload/
├── namecard/                   # 명함 파일
│   └── 2026/01/17/session_id/  # 날짜/세션별 구분
├── inserted/                   # 전단지 파일
├── envelope/                   # 봉투 파일
├── sticker_new/                # 스티커 파일
└── ...
```

### 7.2 경로 생성 규칙

```php
// UploadPathHelper 사용
$paths = UploadPathHelper::generateUploadPath('namecard');
// 결과:
// $paths['full_path'] = '/var/www/html/upload/namecard/2026/01/17/abc123/'
// $paths['db_path'] = 'upload/namecard/2026/01/17/abc123/'
```

---

## 8. 문서 디렉토리

```
CLAUDE_DOCS/
├── Duson_System_Master_Spec_v1.0.md  # 마스터 명세서
├── DB_SCHEMA.md                       # DB 스키마
├── API_SPEC.md                        # API 명세서
├── DATA_LINEAGE.md                    # 데이터 흐름
├── DIRECTORY_STRUCTURE.md             # 이 문서
├── COMPONENT_REFERENCE.md             # 컴포넌트 레퍼런스
├── CSS_DEBUG_LESSONS.md               # CSS 디버깅 교훈
├── EMAIL_CAMPAIGN_SYSTEM.md           # 이메일 캠페인 시스템
└── 00_Legacy_Archive/                 # 레거시 문서 보관
```

---

## 부록: 빠른 참조

### 자주 사용하는 경로

| 용도 | 경로 |
|------|------|
| DB 연결 | `/var/www/html/db.php` |
| 인증 | `/var/www/html/includes/auth.php` |
| 수량 포맷 | `/var/www/html/includes/QuantityFormatter.php` |
| 주문 처리 | `/var/www/html/mlangorder_printauto/ProcessOrder_unified.php` |
| 장바구니 | `/var/www/html/mlangprintauto/cart.php` |

### 파일 찾기 팁

```bash
# 특정 함수 찾기
grep -r "function formatQuantity" /var/www/html/includes/

# 특정 테이블 사용처 찾기
grep -r "mlangorder_printauto" /var/www/html/ --include="*.php"

# 특정 클래스 찾기
grep -r "class QuantityFormatter" /var/www/html/
```

---

*Document Version: 1.1*
*Last Updated: 2026-02-13*
