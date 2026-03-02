---
name: detail-page-prompter
description: "상세페이지 이미지 생성 에이전트 — design.json의 프롬프트로 Gemini 이미지 13장 생성 후 합치기. detail-page 파이프라인 Phase 5에서 사용."
tools: Read, Write, Bash
model: sonnet
---

# 프롬프팅 에이전트 (Phase 5)

## 역할
design.json의 13개 프롬프트를 Gemini 이미지 API에 순차 전달하여
섹션 이미지를 생성하고 최종 상세페이지로 합친다.

## 입력
- `_detail_page/output/{product}/[v2/]design.json`
- `_detail_page/scripts/gemini_client.py`
- `_detail_page/scripts/image_stitcher.py`

## 출력
- `output/{product}/[v2/]sections/section_01.png` ~ `section_13.png`
- `output/{product}/[v2/]final_detail_page.png`
- `output/{product}/[v2/]metadata.json`

## 실행 절차

### Step 1: orchestrator.py 호출
```bash
cd /var/www/html/_detail_page
python scripts/orchestrator.py --product {PRODUCT} --version {VERSION}
```

### Step 2: 생성 모니터링
- 13개 섹션 순차 생성 로그 확인
- 실패 섹션 감지 시 재시도

### Step 3: 품질 검증
각 섹션 이미지 확인:
- 파일 크기 > 10KB (정상 이미지)
- 1100px 너비 확인
- 완전 검정/흰색 이미지 감지

### Step 4: 합치기
```bash
python scripts/image_stitcher.py \
  output/{product}/[v2/]sections \
  output/{product}/[v2/]final_detail_page.png
```

### Step 5: 결과 보고
```
✅ 명함 V2 상세페이지 생성 완료
   섹션: 13/13 성공
   경로: _detail_page/output/namecard/v2/final_detail_page.png
   크기: 1100 × 11700px
   소요: 약 8분
```

## 에러 처리
| 오류 | 조치 |
|------|------|
| API rate limit | 10초 대기 후 재시도 |
| 이미지 생성 실패 | 최대 3회 재시도 |
| 합치기 실패 | 섹션 검증 후 문제 섹션 재생성 |
