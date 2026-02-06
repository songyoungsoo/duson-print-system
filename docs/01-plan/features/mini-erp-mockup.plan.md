# Plan: 미니 ERP 서비스 목업

> **Feature**: mini-erp-mockup
> **Created**: 2026-02-05
> **Phase**: Plan
> **Type**: HTML/CSS 목업 (프로토타입)

---

## 1. 프로젝트 개요

### 1.1 목표
- 소규모 회사용 **미니 ERP** UI 프로토타입 제작
- HTML/CSS만으로 **빠른 목업** 구현
- 주문/판매 관리 중심 기능

### 1.2 타겟 사용자
- **관리자 1-3명** (소규모)
- 대표/관리자 위주 사용

### 1.3 기술 스택
- HTML5
- CSS3 (Flexbox, Grid)
- JavaScript 없음 (순수 목업)

---

## 2. 추천 기능 (핵심 5개 + 보조 3개)

### 2.1 핵심 기능 (Must-Have)

| # | 기능 | 화면 | 설명 |
|---|------|------|------|
| 1 | **대시보드** | dashboard.html | 오늘 매출, 주문 현황, 알림 |
| 2 | **주문 목록** | orders.html | 전체 주문 리스트, 상태 필터 |
| 3 | **주문 상세** | order-detail.html | 주문 정보, 고객 정보, 이력 |
| 4 | **고객 관리** | customers.html | 고객 목록, 검색, 등급 |
| 5 | **매출 현황** | sales.html | 일/월 매출, 간단한 차트 |

### 2.2 보조 기능 (Nice-to-Have)

| # | 기능 | 화면 | 설명 |
|---|------|------|------|
| 6 | 상품 관리 | products.html | 상품 목록, 가격, 재고 |
| 7 | 설정 | settings.html | 회사 정보, 계정 설정 |
| 8 | 로그인 | login.html | 로그인 페이지 |

---

## 3. 화면 구성

### 3.1 레이아웃 구조

```
┌──────────────────────────────────────────┐
│  Header (로고, 사용자 정보)               │
├────────┬─────────────────────────────────┤
│        │                                 │
│  Side  │       Main Content              │
│  Nav   │                                 │
│        │                                 │
│  - 대시보드                               │
│  - 주문관리                               │
│  - 고객관리                               │
│  - 매출현황                               │
│  - 상품관리                               │
│  - 설정                                  │
│        │                                 │
└────────┴─────────────────────────────────┘
```

### 3.2 디자인 컨셉
- **컬러**: 비즈니스 블루 (#2563eb) + 화이트
- **폰트**: Pretendard (시스템 폰트 fallback)
- **스타일**: 깔끔한 카드 기반 UI

---

## 4. 파일 구조

```
mini-erp/
├── index.html          # 대시보드 (메인)
├── orders.html         # 주문 목록
├── order-detail.html   # 주문 상세
├── customers.html      # 고객 관리
├── sales.html          # 매출 현황
├── products.html       # 상품 관리
├── settings.html       # 설정
├── login.html          # 로그인
└── css/
    └── style.css       # 통합 스타일시트
```

---

## 5. 구현 우선순위

### Phase 1: 기본 구조 (먼저)
1. [x] 공통 CSS 스타일시트
2. [ ] 레이아웃 (헤더, 사이드바)
3. [ ] 대시보드 (index.html)

### Phase 2: 핵심 기능
4. [ ] 주문 목록 (orders.html)
5. [ ] 주문 상세 (order-detail.html)
6. [ ] 고객 관리 (customers.html)

### Phase 3: 부가 기능
7. [ ] 매출 현황 (sales.html)
8. [ ] 상품 관리 (products.html)
9. [ ] 설정/로그인

---

## 6. 다음 단계

바로 **Do 단계**로 진행 (목업이므로 Design 단계 생략)

```
/pdca do mini-erp-mockup
```

또는 바로 구현 시작 요청

---

*PDCA Phase: Plan → Do (바로 구현)*
