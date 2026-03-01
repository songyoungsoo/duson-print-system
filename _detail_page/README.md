# 상세페이지 자동 생성 에이전트 팀

> 빌더 조쉬 방식 기반 — Gemini 이미지 생성 + 파이썬 합치기
> 출처: https://www.youtube.com/watch?v=ysA5Wd_o4_Q

## 핵심 개념

**5개 에이전트가 역할을 나눠 협업하여 상세페이지를 자동 생성합니다.**

```
정보수집 → 리서치 → 카피라이팅 ─┐
                                 ├→ 프롬프팅 → 이미지 13장 → 합치기 → 완성
                     디자인 ─────┘
```

## 에이전트 팀 구성

| # | 에이전트 | 역할 | 모델 |
|---|---------|------|------|
| 1 | 📋 정보수집 | 제품 DB 데이터, 기존 페이지 분석 | PHP (LLM 없음) |
| 2 | 🔍 리서치 | 경쟁사 분석, SEO 키워드, 시장 트렌드 | Gemini 3.1 Pro + Search |
| 3 | ✍️ 카피라이팅 | 섹션별 판매 문구 생성 | Gemini 3.1 Pro |
| 4 | 🎨 디자인 | 색상/폰트/레이아웃 + 이미지 프롬프트 | Gemini 3.1 Pro |
| 5 | 💻 프롬프팅 | Gemini 이미지 API로 13장 생성 | Gemini 3.1 Flash Image |

## 13섹션 상세페이지 구조

1. **긴급성 헤더** — 기간한정/수량한정 배너
2. **공감 섹션** — 타겟 고객의 고민/상황
3. **문제 정의** — 기존 인쇄 서비스의 문제점
4. **솔루션 제시** — 두손기획인쇄의 차별점
5. **제품 소개** — 핵심 특징 3가지
6. **스펙 상세** — 용지/사이즈/후가공 옵션
7. **비포&애프터** — 활용 사례 이미지
8. **가격 안내** — 수량별 가격표
9. **제작 과정** — 주문→디자인→인쇄→배송
10. **고객 후기** — 별점 + 실제 리뷰
11. **FAQ** — 자주 묻는 질문
12. **신뢰 배지** — 인증/보증/배송 아이콘
13. **최종 CTA** — 지금 주문하기 버튼

## 실행 방법

```bash
# 1. Gemini API 키 설정
cp .env.example .env
# .env 파일에 GEMINI_API_KEY 입력

# 2. 파이썬 의존성 설치
pip install Pillow requests

# 3. 상세페이지 생성 (예: 명함)
python scripts/orchestrator.py --product namecard

# 4. 결과물 확인
ls output/namecard/
# → final_detail_page.png (합쳐진 최종 이미지)
# → sections/ (개별 섹션 이미지 13장)
# → metadata.json (생성 로그)
```

## 폴더 구조

```
_detail_page/
├── README.md                  ← 이 파일
├── .env.example               ← API 키 템플릿
├── agents/                    ← 에이전트 정의 (.md)
│   ├── 01_collector.md        ← 📋 정보수집
│   ├── 02_researcher.md       ← 🔍 리서치
│   ├── 03_copywriter.md       ← ✍️ 카피라이팅
│   ├── 04_designer.md         ← 🎨 디자인
│   └── 05_prompter.md         ← 💻 프롬프팅 (이미지 생성)
├── prompts/
│   └── landing_page_plan.md   ← 13섹션 구조 시스템 프롬프트
├── config/
│   ├── products.json          ← 9개 인쇄 제품 설정
│   └── settings.json          ← 전역 설정
├── scripts/
│   ├── orchestrator.py        ← 메인 실행 스크립트
│   ├── gemini_client.py       ← Gemini API 래퍼
│   └── image_stitcher.py      ← 이미지 합치기
├── output/                    ← 생성 결과물
└── logs/                      ← 실행 로그
```

## 비용

- 텍스트 생성 (3회): ~$0.18
- 이미지 생성 (13장): ~$0.87
- **합계: ~$1.05/페이지** (약 1,400원)
- 9개 제품 전체: ~$9.45 (약 12,600원)

## 참고

- 영상 원본: 빌더 조쉬 (Builder Josh) 채널
- 모델: Gemini 3.1 Pro (텍스트), Gemini 3.1 Flash Image (이미지)
- 이미지 크기: 1100×1100px per section
- 최종 출력: 1100×14300px (13장 합본)
