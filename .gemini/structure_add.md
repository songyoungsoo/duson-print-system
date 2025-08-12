# 🖨️ 인쇄 자동화 시스템 데이터베이스 구조 분석

---

## 📋 프로젝트 개요

**목적:** 인쇄 자동화 시스템의 복잡한 데이터베이스 구조를 분석하고 관계를 정리

**분석 대상:** 인쇄물 주문 관리 시스템

**작성일:** 2025년 8월 3일

---

## 🏢 시스템 아키텍처

### 전체 구조도

```
📊 카테고리 관리 (mlangprintauto_transactioncate)
    ↓ (계층적 분류)
🛍️ 품목별 세부 테이블 (8개 독립 테이블)
    ↓ (주문 생성)
📦 주문 통합 관리 (MlangOrder_PrintAuto)
```

---

## 🗂️ 핵심 테이블 분석

### 1️⃣ 카테고리 관리 테이블

**테이블명:** ``mlangprintauto_transactioncate``

**역할:** 인쇄물 카테고리의 계층 구조 관리

**주요 필드:**

- ``no``: 카테고리 고유번호
- ``Ttable``: 카테고리 유형 (품목 테이블과 연결)
- ``BigNo``: 상위 카테고리 번호 (0이면 최상위)
- ``title``: 카테고리명
- ``TreeNo``: 트리 구조 관리용

### 2️⃣ 품목별 세부 테이블 (8개)

| **테이블명** | **카테고리** | **설명** |
| --- | --- | --- |
| `mlangprintauto_inserted` | 칼라인쇄(CMYK) | 일반 칼라 인쇄물 |
| `mlangprintauto_namecard` | 명함 | 각종 명함 유형 |
| `mlangprintauto_littleprint` | 소량포스터 | 소량 포스터 인쇄 |
| `mlangprintauto_envelope` | 봉투 | 각종 봉투 인쇄 |
| `mlangprintauto_cadarok` | 카다록/리플렛 | 책자형 인쇄물 |
| `mlangprintauto_merchandisebond` | 상품권 | 상품권 인쇄 |
| `mlangprintauto_msticker` | 자석스티커 | 자석 스티커 |
| `mlangprintauto_ncrflambeau` | 양식/NCR | 복사용지 양식 |

**공통 필드 구조:**

- ``style``: 상위 카테고리 번호 (transactioncate.no와 연결)
- ``Section``: 세부 옵션 번호 (transactioncate.no와 연결)
- ``quantity``: 주문 수량
- ``money``: 기본 가격
- ``TreeSelect``: 추가 옵션 (용지, 재질 등)
- ``DesignMoney``: 디자인 비용
- ``POtype``: 주문 유형

---

## 🔄 데이터 관계 매핑

### 연결 구조

1. **카테고리 → 품목 연결**
- ``transactioncate.no`` ↔ ``품목테이블.style`` (메인 카테고리)
- ``transactioncate.no`` ↔ ``품목테이블.Section`` (세부 옵션)
- ``transactioncate.no`` ↔ ``품목테이블.TreeSelect`` (추가 옵션)
1. **품목 → 주문 연결**
- 품목 테이블의 설정값들이 주문 테이블로 통합

### 계층 구조 예시

```
칼라인쇄(CMYK) [no: 802]
├── A4 (210x297) [no: 821]
├── B4(8절) 257x367 [no: 823]
├── 120g아트지,스노우지 [no: 714]
└── 100모조 [no: 808]
```

---

## 📊 실제 데이터 예시

### 명함 주문 케이스

**주문 설정:**

- 카테고리: 일반명함(쿠폰) [no: 275]
- 옵션: 칼라코팅 [no: 276]
- 수량: 500매
- **가격: 9,000원 + 디자인비 5,000원 = 촕14,000원**

### 칼라인쇄 주문 케이스

**주문 설정:**

- 카테고리: 칼라인쇄(CMYK) [no: 802]
- 크기: A4 [no: 821]
- 용지: 120g아트지,스노우지 [no: 714]
- 수량: 28,000매
- **가격: 640,000원 + 디자인비 30,000원 = 총 670,000원**

---

## 🔍 두 파일 관계 분석

### 1. mlangprintauto_transactioncate (카테고리 관리)

<aside>
📂 역할: 전체 시스템의 카테고리 계층 구조를 정의하는 마스터 테이블

</aside>

- 8개 품목 카테고리 관리 (Ttable 필드로 구분)
- 계층 구조: BigNo로 상위-하위 관계 정의
- TreeNo로 트리 구조 관리

### 2. mlangprintauto_cadarok (카다록 품목 테이블)

<aside>
📋 역할: 카다록/리플렛 품목의 구체적인 가격 정보를 저장하는 세부 테이블

</aside>

- 카테고리 번호 691(카다록,리플렛)과 연결
- 수량별, 옵션별 세부 가격 정보 관리
- 용지, 사이즈, 페이지 수에 따른 가격 매트릭스

### 🔗 연결 관계

```
transactioncate 테이블:
- no: 691 → Ttable: 'cadarok' → title: '카다록,리플렟'
- no: 692 → BigNo: '691' → title: '24절(127*260)3단'
- no: 699 → TreeNo: '691' → title: '150g(A/T,S/W)'

cadarok 테이블:
- style: '691' (최상위 카테고리)
- Section: '692' (크기 옵션)
- TreeSelect: '699' (용지 옵션)
- quantity: 1000, money: '268000'
```

### 📊 데이터 플로우

1. 카테고리 설정: transactioncate에서 카다록 관련 카테고리 정의
2. 가격 매핑: cadarok에서 각 옵션 조합별 가격 저장
3. 주문 처리: 사용자 선택에 따른 실시간 가격 계산
4. 주문 완료: 최종 주문 정보를 통합 주문 테이블로 전송

### 📦 주문 예시: 카다록 주문

고객이 다음과 같이 주문하는 경우:

- 상품: 카다록/리플렟
- 크기: 24절(127*260) 3단 접지
- 용지: 150g 아트지/스노우지
- 수량: 1,000부

시스템 처리 과정:

1. transactioncate에서 카테고리 조회 (691, 692, 699)
2. cadarok에서 해당 조합 검색 (style=691, Section=692, TreeSelect=699, quantity=1000)
3. 가격 반환: 268,000원
4. 주문 확정 시 통합 주문 테이블로 데이터 전송
