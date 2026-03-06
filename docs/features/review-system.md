# 고객 리뷰 시스템

## 개요
고객이 제품별로 리뷰를 작성하고, 관리자가 승인 후 공개하는 시스템.
별점(1-5) + 텍스트 + 포토(최대 5장) + 구매인증 + 좋아요 + 관리자 답변.

## 구조

### DB 테이블 (3개, 자동 생성)
- `reviews` — 리뷰 본문 (별점, 내용, 승인상태, 관리자 답변)
- `review_photos` — 리뷰 첨부 사진 (FK → reviews.id, CASCADE)
- `review_likes` — 좋아요 (회원 user_id 또는 비회원 IP hash, UNIQUE)

테이블 자동 생성: `includes/review_schema.php` → `ensureReviewTables($db)`

### 파일 구조

```
includes/
├── review_schema.php          # DB 테이블 자동 생성 (SSOT)
├── review_widget.php          # 프론트엔드 위젯 PHP (include용)
├── css/review_widget.css      # 위젯 스타일 (857줄, !important 없음)
└── js/review_widget.js        # 위젯 JS (788줄, Vanilla, IIFE)

api/
└── reviews.php                # 고객 API (list, summary, create, like)

admin/mlangprintauto/
├── api/reviews.php            # 관리자 API (list, approve, reject, reply, delete)
└── review_manager.php         # 관리자 리뷰 관리 페이지 (882줄)

uploads/reviews/{review_id}/   # 리뷰 사진 저장 경로 (자동 생성)
```

### 제품 페이지 통합 (9개)

각 제품 `index.php`에 `quote_gauge.php` include 직전에 삽입:

```php
<!-- 고객 리뷰 섹션 -->
<?php if (!$isQuotationMode && !$isAdminQuoteMode): ?>
<?php $product_type = '{폴더명}'; include __DIR__ . '/../../includes/review_widget.php'; ?>
<?php endif; ?>
```

## 데이터 흐름

### 리뷰 작성 (고객)
```
제품 페이지 → "리뷰 쓰기" 클릭 → 폼 표시 (로그인 필수)
→ 별점 + 제목 + 내용 + 사진 + 주문번호(선택) 입력
→ POST /api/reviews.php (action=create, FormData)
→ 구매인증: order_id → mlangorder_printauto에서 사용자 확인
→ is_approved=0 (대기) 상태로 저장
→ "관리자 승인 후 게시됩니다" 토스트
```

### 관리자 승인 흐름
```
/admin/mlangprintauto/review_manager.php
→ 대기(0) / 승인(1) / 반려(2) 탭 필터
→ 승인 → is_approved=1 → 제품 페이지에 노출
→ 반려 → is_approved=2 → 비공개
→ 답변 → admin_reply 저장 → "사장님 답변" 표시
→ 삭제 → reviews + photos + likes CASCADE 삭제 + 파일 물리 삭제
```

### 프론트엔드 표시
```
페이지 로드 → JS init()
→ GET /api/reviews.php?action=summary → 평균별점 + 분포 차트
→ GET /api/reviews.php?action=list → 승인된 리뷰 목록 (AJAX)
→ 정렬: 최신순 / 별점높은순 / 별점낮은순 / 도움순
→ 페이지네이션 (10건/페이지)
→ 좋아요: POST action=like → 토글 (로그인 불필요, IP hash 사용)
→ 사진 클릭 → 라이트박스 (prev/next/ESC)
```

## API 스펙

### 고객 API (`/api/reviews.php`)

| Action | Method | Params | 인증 |
|--------|--------|--------|------|
| `list` | GET | product_type, page, per_page, sort | 불필요 |
| `summary` | GET | product_type | 불필요 |
| `create` | POST | product_type, rating, title, content, order_id, photos[] | 로그인 필수 |
| `like` | POST | review_id | 불필요 |

### 관리자 API (`/admin/mlangprintauto/api/reviews.php`)

| Action | Method | Params | 인증 |
|--------|--------|--------|------|
| `list` | GET/POST | status, product_type, page, per_page | 관리자 세션 |
| `approve` | POST | review_id | 관리자 세션 |
| `reject` | POST | review_id | 관리자 세션 |
| `reply` | POST | review_id, reply_text | 관리자 세션 |
| `delete` | POST | review_id | 관리자 세션 |

## 제약사항
- 리뷰 내용: 최대 5,000자
- 사진: 최대 5장, 각 5MB, JPG/PNG/WEBP만 허용
- 이름 마스킹: "김영수" → "김**" (프론트엔드 JS)
- 관리자 답변: 최대 5,000자
- 좋아요: 리뷰당 사용자 1회 (토글)

## 주의사항
- `sticker_new/index.php`는 변수명이 다름: `$is_quotation_mode` / `$is_admin_quote_mode` (snake_case)
- `review_widget.php`는 DB 쿼리 직접 안 함 — 모든 데이터 AJAX로 로드
- `uploads/reviews/` 디렉토리는 리뷰 작성 시 자동 생성 (mkdir recursive)
- CSS에 `!important` 없음 — `.review-widget` 스코프로 명시도 해결

---
마지막 업데이트: 2026-03-07
