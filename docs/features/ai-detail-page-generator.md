# AI 상세페이지 자동 생성 공장

## 개요

업종 + 상품명만 입력하면 13장 기승전결 상세페이지(카피 + 이미지 + HTML)를 완전 자동으로 생성합니다.
두손기획인쇄 9개 품목뿐 아니라 음식점, 학원, 피트니스 등 **모든 업종** 지원.

## 핵심 파일

| 파일 | 역할 |
|------|------|
| `scripts/ai_detail_page.py` | 메인 공장 스크립트 |
| `scripts/ab_rotation.json` | A/B 로테이션 상태 |
| `scripts/detail_page_rotation.json` | 주간 로테이션 큐 |

## 폴더 구조

```
ImgFolder/
  detail_page_staging/   ← AI 생성 작업공간
    namecard/
      section_01.jpg ~ section_13.jpg
      copy.json
      detail.html
  detail_page/           ← 실제 서비스 (live)
  detail_page_v_a/       ← 기존 페이지 (A버전)
  detail_page_v_b/       ← AI 생성 페이지 (B버전)
```

## 사용법

```bash
cd /var/www/html

# ── 범용 (모든 업종) ──────────────────────────
python3 scripts/ai_detail_page.py generate "음식점" "시그니처 파스타"
python3 scripts/ai_detail_page.py generate "피트니스" "3개월 멤버십" luxury
python3 scripts/ai_detail_page.py generate "보석" "18K 다이아 반지" luxury
python3 scripts/ai_detail_page.py generate "커피 전문점" "시그니처 에스프레소" food

# ── 두손기획인쇄 내장 품목 ─────────────────────
python3 scripts/ai_detail_page.py generate-builtin namecard
python3 scripts/ai_detail_page.py generate-builtin namecard images-only  # 이미지만 재생성
python3 scripts/ai_detail_page.py generate-all-builtin                   # 9개 병렬 생성

# ── A/B 로테이션 ──────────────────────────────
python3 scripts/ai_detail_page.py ab-setup    # 최초 1회: 기존→V_A, staging→V_B
python3 scripts/ai_detail_page.py ab-rotate   # A↔B 수동 전환
python3 scripts/ai_detail_page.py ab-status   # 현재 상태 확인

# ── 기타 ──────────────────────────────────────
python3 scripts/ai_detail_page.py status-all
python3 scripts/ai_detail_page.py swap "음식점" "파스타"
```

## 팔레트 스타일

| 스타일 | 적합 업종 |
|--------|---------|
| `default` | 인쇄업, 일반 |
| `luxury` | 보석, 고급 브랜드, 금/은 취급 |
| `vivid` | 스포츠, 엔터테인먼트 |
| `pastel` | 유아, 뷰티, 카페 |
| `food` | 음식점, 카페, F&B |

## AI 파이프라인

```
업종 + 상품명
    ↓
gen_product_info()   → 상품 특징·타겟·가격대 분석
    ↓
gen_scenarios()      → 13개 기승전결 섹션 설계 (arc: 기/승/전/결)
    ↓
gen_copy() × 13      → 카피 생성 (순차, 반복방지)
    ↓
gen_image() × 13     → 이미지 병렬 생성 (MAX_PARALLEL=2)
    ↓
build_html()         → detail.html 조립
```

## A/B 주간 로테이션

매주 월요일 오전 9시 cron 자동 실행:
```
1주차: 기존 페이지 (V_A)
2주차: AI 생성 페이지 (V_B)
3주차: 기존 페이지 (V_A)
...
```

cron 등록 확인: `crontab -l | grep ab-rotate`

## 주의사항

- Gemini Image API: 분당 10회(종량제) 제한 → MAX_PARALLEL=2 설정
- 일일 쿼터 초과 시 429 오류 → 다음날 재시도
- 상세페이지 1개 생성 소요시간: 약 5~6분
- 9개 전품목 병렬 생성: 약 10~15분

## 내장 품목 (두손기획인쇄 9개)

| 코드 | 품목 | 주문 URL |
|------|------|---------|
| namecard | 명함 | /mlangprintauto/namecard/ |
| sticker_new | 스티커 | /mlangprintauto/sticker_new/ |
| msticker | 자석스티커 | /mlangprintauto/msticker/ |
| inserted | 전단지 | /mlangprintauto/inserted/ |
| envelope | 봉투 | /mlangprintauto/envelope/ |
| littleprint | 포스터 | /mlangprintauto/littleprint/ |
| merchandisebond | 상품권 | /mlangprintauto/merchandisebond/ |
| cadarok | 카다록 | /mlangprintauto/cadarok/ |
| ncrflambeau | NCR양식지 | /mlangprintauto/ncrflambeau/ |
