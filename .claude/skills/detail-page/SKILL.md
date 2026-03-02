---
name: detail-page
description: "상세페이지 자동 생성 파이프라인. /detail-page {제품코드} {버전} 형식으로 호출. 예: /detail-page namecard 2"
user-invocable: true
allowed-tools: Bash, Read, Write, Agent, TaskCreate, TaskUpdate, TaskList
---

# 상세페이지 생성 파이프라인

## 호출 형식
```
/detail-page {제품코드} {버전}

예시:
  /detail-page namecard 2      ← 명함 v2 생성 (v1 보존)
  /detail-page namecard 1      ← 명함 v1 재생성
  /detail-page all 2           ← 전체 9개 제품 v2 생성
```

## 제품 코드 목록
| 코드 | 제품명 |
|------|--------|
| namecard | 명함 |
| inserted | 전단지 |
| sticker_new | 스티커 |
| msticker | 자석스티커 |
| envelope | 봉투 |
| littleprint | 포스터 |
| merchandisebond | 상품권 |
| cadarok | 카다록 |
| ncrflambeau | NCR양식지 |

---

## 파이프라인 실행 절차

**인자 파싱**
- PRODUCT = $ARGUMENTS[0]  (예: namecard)
- VERSION = $ARGUMENTS[1]  (예: 2, 기본값: 1)

**사전 검사**
- v2 실행 시: 기존 `output/{PRODUCT}/` 폴더 보존 확인
- v2 출력 경로: `output/{PRODUCT}/v2/`

**Task 등록** (TaskCreate × 5)
1. Phase 1: 정보수집 (detail-page-collector)
2. Phase 2: 리서치 (detail-page-researcher)
3. Phase 3: 카피라이팅 (detail-page-copywriter) — Phase 2 완료 후
4. Phase 4: 디자인 (detail-page-designer) — Phase 3 완료 후
5. Phase 5: 이미지생성 (detail-page-prompter) — Phase 4 완료 후

**에이전트 순차 실행**

Phase 1 → Phase 2 → Phase 3 → Phase 4 → Phase 5

각 Phase는 이전 Phase 결과에 의존하므로 순차 실행.
Phase 5는 13장 이미지 생성으로 오래 걸림 (약 8~10분).

**완료 보고**
```
✅ {제품명} V{VERSION} 상세페이지 생성 완료
   경로: _detail_page/output/{product}/[v2/]final_detail_page.png
   섹션: 13장
   크기: 1100 × 11700px
```

---

## V2 핵심 변경사항

```
V1: 캔버스 전체(1100px) 사용, 폰트 제한 없음
V2: 내용 영역 900px (좌우 100px 여백), 폰트 상한 적용

폰트 상한:
  헤드라인  ≤ 48px
  서브헤딩  ≤ 32px
  본문      ≤ 20px
  캡션      ≤ 16px
```

## 주의사항
- v2 실행이 v1 output을 덮어쓰지 않도록 경로 분리
- `output/{product}/` = v1 보존 (수정 금지)
- `output/{product}/v2/` = v2 생성 경로
