# Plan: 관리자 대시보드

> **Feature**: admin-dashboard
> **Created**: 2026-02-05
> **Phase**: Plan
> **Site**: 두손기획인쇄 (dsp114.co.kr)

---

## 1. 프로젝트 개요

### 1.1 목표
- 기존 구형 frameset 기반 관리자(shop_admin)를 대체할 **현대적인 관리자 대시보드** 구축
- 주문/매출/회원/제품 통계를 **한 눈에** 파악할 수 있는 UI 제공
- 실시간 데이터 기반 의사결정 지원

### 1.2 현재 상태 분석
- **기존 관리자**: `/shop_admin/index.php` (frameset 기반, 2000년대 UI)
- **문제점**:
  - main.php가 "우측내용" 텍스트만 있음
  - 대시보드/통계 기능 없음
  - 모바일 미지원
  - 분산된 관리 기능 (admin/, shop_admin/)

### 1.3 기술 스택
- **Backend**: PHP 7.4 (기존 환경 호환)
- **Frontend**: HTML5 + CSS3 + Vanilla JS
- **Database**: MySQL (기존 테이블 활용)
- **UI Framework**: Tailwind CSS (CDN) 또는 순수 CSS

---

## 2. 핵심 기능

### 2.1 대시보드 위젯 (5개)

| # | 위젯 | 데이터 소스 | 설명 |
|---|------|------------|------|
| 1 | **오늘 매출** | orders.total_amount | 오늘 결제 완료 금액 합계 |
| 2 | **오늘 주문** | orders | 오늘 주문 건수 |
| 3 | **처리 대기** | orders.payment_status | pending 상태 주문 수 |
| 4 | **신규 회원** | users.created_at | 오늘 가입 회원 수 |
| 5 | **총 회원** | users | 전체 회원 수 |

### 2.2 통계 섹션 (4개)

| # | 섹션 | 내용 |
|---|------|------|
| 1 | **최근 주문** | 최근 10건 주문 목록 (테이블) |
| 2 | **주간 매출 차트** | 최근 7일 일별 매출 (바 차트) |
| 3 | **제품별 주문** | 인기 제품 Top 5 (pie/bar) |
| 4 | **최근 가입 회원** | 최근 5명 신규 회원 |

### 2.3 퀵 링크

| 링크 | 대상 | 설명 |
|------|------|------|
| 주문 관리 | shop_admin/post_list.php | 주문 목록 |
| 회원 관리 | admin/member/ | 회원 목록 |
| 상품 관리 | admin/MlangPrintAuto/ | 상품 설정 |
| 설정 | admin/AdminConfig.php | 관리자 설정 |

---

## 3. 데이터베이스 쿼리

### 3.1 오늘 매출
```sql
SELECT COALESCE(SUM(total_amount), 0) as today_sales
FROM orders
WHERE DATE(order_date) = CURDATE()
AND payment_status = 'paid'
```

### 3.2 오늘 주문 수
```sql
SELECT COUNT(*) as today_orders
FROM orders
WHERE DATE(order_date) = CURDATE()
```

### 3.3 처리 대기 주문
```sql
SELECT COUNT(*) as pending_orders
FROM orders
WHERE payment_status = 'pending'
```

### 3.4 신규 회원 (오늘)
```sql
SELECT COUNT(*) as new_members
FROM users
WHERE DATE(created_at) = CURDATE()
```

### 3.5 주간 매출
```sql
SELECT DATE(order_date) as date, SUM(total_amount) as daily_sales
FROM orders
WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
AND payment_status = 'paid'
GROUP BY DATE(order_date)
ORDER BY date
```

### 3.6 제품별 주문 (shop_temp 기준)
```sql
SELECT product_type, COUNT(*) as count
FROM shop_temp
WHERE product_type IS NOT NULL AND product_type != ''
GROUP BY product_type
ORDER BY count DESC
LIMIT 5
```

---

## 4. 파일 구조

```
/admin-new/
├── index.php           # 대시보드 메인
├── includes/
│   ├── auth.php        # 관리자 인증
│   ├── header.php      # 공통 헤더
│   ├── sidebar.php     # 사이드바 네비게이션
│   └── footer.php      # 공통 푸터
├── api/
│   └── stats.php       # 통계 데이터 API (AJAX용)
├── css/
│   └── admin.css       # 관리자 스타일
└── js/
    └── dashboard.js    # 대시보드 스크립트
```

---

## 5. 화면 설계

### 5.1 레이아웃 구조

```
┌─────────────────────────────────────────────────┐
│  Header (로고, 관리자명, 로그아웃)                │
├──────────┬──────────────────────────────────────┤
│          │                                      │
│  Sidebar │       Main Content                   │
│          │                                      │
│ - 대시보드 │  ┌─────┬─────┬─────┬─────┬─────┐   │
│ - 주문관리 │  │오늘  │오늘  │대기  │신규  │총   │   │
│ - 회원관리 │  │매출  │주문  │주문  │회원  │회원 │   │
│ - 상품관리 │  └─────┴─────┴─────┴─────┴─────┘   │
│ - 설정    │                                      │
│          │  ┌──────────────┬──────────────┐    │
│          │  │  최근 주문   │  주간 매출   │    │
│          │  │  (테이블)    │  (차트)      │    │
│          │  └──────────────┴──────────────┘    │
│          │                                      │
│          │  ┌──────────────┬──────────────┐    │
│          │  │  제품별 통계 │  최근 가입   │    │
│          │  └──────────────┴──────────────┘    │
│          │                                      │
└──────────┴──────────────────────────────────────┘
```

### 5.2 디자인 컨셉
- **Primary Color**: #2563eb (비즈니스 블루)
- **Background**: #f3f4f6 (라이트 그레이)
- **Cards**: 흰색 카드, 라운드 코너, 그림자
- **Font**: 시스템 폰트 (Pretendard fallback)

---

## 6. 인증 및 보안

### 6.1 관리자 인증
```php
// users 테이블에서 is_admin = 1 확인
SELECT * FROM users WHERE is_admin = 1 AND username = ?
```

### 6.2 세션 관리
- 기존 `includes/auth.php` 활용
- 8시간 세션 유지
- 관리자 전용 세션 키 분리

---

## 7. 구현 우선순위

### Phase 1: 기본 구조
1. [ ] 디렉토리 생성 (/admin-new/)
2. [ ] 인증 시스템 (auth.php)
3. [ ] 레이아웃 (header, sidebar, footer)
4. [ ] 기본 스타일시트

### Phase 2: 대시보드 위젯
5. [ ] 통계 위젯 5개
6. [ ] 최근 주문 테이블
7. [ ] 최근 가입 회원 목록

### Phase 3: 차트 및 고급 기능
8. [ ] 주간 매출 차트 (Chart.js)
9. [ ] 제품별 통계 차트
10. [ ] 실시간 새로고침 (선택)

---

## 8. 리소스 연동

### 8.1 기존 관리자 링크
| 기능 | URL |
|------|-----|
| 주문 목록 | /shop_admin/post_list.php |
| 주문 상세 | /shop_admin/data_il.php?no={id} |
| 회원 목록 | /admin/member/list.php |
| 상품 관리 | /admin/MlangPrintAuto/CateList.php |

### 8.2 DB 연결
- 기존 `/db.php` 사용
- `$db` 변수로 mysqli 연결

---

## 9. 다음 단계

```bash
/pdca design admin-dashboard
```

Design 단계에서 상세 UI/UX 및 컴포넌트 설계 진행

---

*PDCA Phase: Plan → Design (다음)*
