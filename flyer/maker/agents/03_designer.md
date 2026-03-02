# 🎨 디자인 에이전트 (Designer Agent)

## 역할
6개 섹션 각각의 비주얼 스타일을 결정하고, 이미지 생성 에이전트를 위한
정밀한 프롬프트를 작성한다. 업종별 컬러 프리셋을 적용하고,
A4 인쇄물에 적합한 레이아웃을 설계한다.

## 파이프라인 위치
```
[1] Collector(0s) → [2] Copywriter(~15s) → [3] Designer(~15s) → [4] Prompter(~120s)
```

## 모델
- `gemini-3-pro-preview`

## 입력
- `flyer_brief.json` (정보수집 에이전트 출력)
- `flyer_copy.json` (카피라이팅 에이전트 출력)

## 출력
- `flyer_design.json` (6개 섹션의 이미지 프롬프트 + 스타일 가이드)

---

## 이미지 규격
- **너비**: 794px
- **높이**: 1123px (A4 세로 비율)
- **해상도**: 300dpi (인쇄용)
- **포맷**: PNG
- **섹션 수**: 6 (앞면 3 + 뒷면 3)
- **최종 합본**: front_page.png (794x3369px), back_page.png (794x3369px)

---

## 업종별 컬러 프리셋

| 업종 코드 | 업종명 | Primary | Secondary | 분위기 |
|-----------|--------|---------|-----------|--------|
| `korean` | 한식 | `#D32F2F` | `#FF8A65` | 따뜻한 빨강+주황 |
| `japanese` | 일식 | `#1A237E` | `#90CAF9` | 깊은 남색+하늘 |
| `chinese` | 중식 | `#E65100` | `#FFD54F` | 진한 주황+금색 |
| `western` | 양식 | `#4E342E` | `#BCAAA4` | 브라운+베이지 |
| `cafe` | 카페 | `#4E342E` | `#BCAAA4` | 브라운+베이지 |
| `chicken` | 치킨 | `#F57F17` | `#D32F2F` | 노랑+빨강 |
| `academy` | 학원 | `#1565C0` | `#42A5F5` | 신뢰 블루 |
| `fitness` | 피트니스 | `#2E7D32` | `#66BB6A` | 에너지 그린 |
| `beauty` | 뷰티 | `#AD1457` | `#F48FB1` | 우아한 핑크 |
| `general` | 일반 | `#37474F` | `#78909C` | 차분한 그레이 |

### 컬러 적용 규칙
```
Primary   → hero 배경, section_title 색상, 강조 요소
Secondary → 서브 배경, 포인트, 아이콘 색상
White     → 본문 텍스트 (어두운 배경 위), 여백
Dark      → 본문 텍스트 (밝은 배경 위): #1A1A1A
Light BG  → 밝은 섹션 배경: #F8F9FA 또는 #FFFFFF
```

---

## 시스템 프롬프트 (Gemini에 전달)

```
당신은 소규모 사업체 홍보 전단지(A4 양면) 전문 디자이너입니다.
카피라이터가 작성한 텍스트와 사업체 정보를 바탕으로,
6개 섹션 각각의 이미지 생성 프롬프트를 작성합니다.

## 반드시 지켜야 할 규칙:

1. 모든 프롬프트는 영어로 작성 (이미지 생성 모델용)
2. 이미지 크기: 794x1123 pixels (A4 세로 비율)
3. 텍스트 생성 금지: 모든 프롬프트에 "No text, no letters, no words, no characters, no numbers" 필수 포함
4. 한국 소규모 사업체 전단지 느낌 유지
5. 업종별 컬러 프리셋 적용 (primary/secondary 색상)
6. 인쇄물 품질 — 선명하고 깨끗한 이미지
7. 카툰/애니메/클립아트 스타일 금지

## 프롬프트 필수 접미사 (모든 섹션에 추가):
"Photorealistic Korean small business flyer style. Professional print quality. No text, no letters, no words, no characters. No cartoon/anime/clipart."

## 섹션 구조 (A4 양면):
앞면: hero(1) → menu(2) → promo_contact(3)
뒷면: features(4) → gallery(5) → location(6)

## 출력 형식:
반드시 지정된 JSON 형식으로만 응답하세요. 설명이나 주석 없이 순수 JSON만 반환하세요.
```

---

## 섹션별 디자인 가이드

### Section 1: hero (시선 캐치) — 앞면 상단
- **레이아웃**: 전면 배경 이미지 + 중앙 상호명 영역(반투명 오버레이)
- **배경**: 업종에 맞는 대표 장면 (식당 내부, 요리, 시설 등)
- **컬러**: Primary 색상 그라데이션 오버레이
- **분위기**: 고객의 mood 키워드 반영
- **프롬프트 핵심**: 업종 대표 비주얼 + 깨끗한 중앙 여백 (텍스트 삽입 공간)

### Section 2: menu (메뉴/서비스 목록) — 앞면 중단
- **레이아웃**: 깔끔한 밝은 배경 + 2단 그리드 구조
- **배경**: White 또는 Light BG (#F8F9FA)
- **포인트**: 메뉴 아이템 간 구분선, 가격 강조 영역
- **프롬프트 핵심**: 깔끔한 메뉴판 배경 이미지 (음식 사진 배치 가능 영역)

### Section 3: promo_contact (프로모션 + 연락처) — 앞면 하단
- **레이아웃**: Primary 색상 배경 + 중앙 CTA 영역
- **배경**: Primary 컬러 풀 배경 또는 그라데이션
- **요소**: 눈에 띄는 연락처 배치 공간
- **프롬프트 핵심**: 강렬한 컬러 배경 + 깨끗한 중앙 영역

### Section 4: features (특장점 3가지) — 뒷면 상단
- **레이아웃**: 3분할 수평 배치 (아이콘 + 제목 + 설명)
- **배경**: Light BG + 각 특장점 카드형 배치
- **포인트**: Secondary 컬러 아이콘 원형 영역 3개
- **프롬프트 핵심**: 3개 원형 아이콘 공간이 있는 깔끔한 레이아웃

### Section 5: gallery (갤러리/분위기) — 뒷면 중단
- **레이아웃**: 3분할 이미지 (가로 3등분 또는 1+2 배치)
- **배경**: 업종에 맞는 실제 장면 3컷
- **분위기**: copy.json의 mood_description 반영
- **프롬프트 핵심**: 3분할 사진 콜라주 — 매장/시설/서비스 장면

### Section 6: location (약도 + 영업정보) — 뒷면 하단
- **레이아웃**: 좌측 약도 영역 + 우측 정보 영역
- **배경**: 차분한 색상 (Light BG 또는 연한 Primary)
- **요소**: 지도 아이콘 영역 + 정보 텍스트 영역 + QR 공간
- **프롬프트 핵심**: 깔끔한 정보 배치용 배경 + 지도/QR 공간

---

## 프롬프트 작성 템플릿

```
Create a 794x1123 pixel image for a Korean small business promotional flyer.
Section: [섹션명] ([앞면/뒷면] [상단/중단/하단])
Business type: [업종 영문]
Style: [modern/warm/clean/bold — mood 기반]
Background: [구체적 색상 코드 또는 장면 설명]
Layout: [구체적 배치 설명 — 텍스트 삽입 공간 명시]
Color scheme: Primary [primary_hex], Secondary [secondary_hex], White #FFFFFF
Must include: [필수 비주얼 요소]
Must NOT include: [금지 요소]
Mood: [분위기 키워드]
Photorealistic Korean small business flyer style. Professional print quality. No text, no letters, no words, no characters. No cartoon/anime/clipart.
```

---

## 출력 JSON 스키마: `flyer_design.json`

```json
{
  "global_style": {
    "primary_color": "#D32F2F",
    "secondary_color": "#FF8A65",
    "industry_code": "korean",
    "mood": "따뜻한",
    "image_size": "794x1123",
    "format": "PNG",
    "font_style": "Modern Korean Gothic (clean, legible for print)"
  },
  "front_sections": [
    {
      "id": 1,
      "name": "hero",
      "side": "front",
      "position": "top",
      "prompt": "Create a 794x1123 pixel image for a Korean small business promotional flyer. Section: Hero banner (front page, top). Business type: Korean restaurant (분식/떡볶이). Style: warm, inviting. Background: A cozy Korean street food restaurant interior with warm lighting, steam rising from cooking pots, clean wooden counter. The center area should have a slightly darker semi-transparent overlay zone for text placement. Color scheme: Primary #D32F2F, Secondary #FF8A65, warm amber lighting. Must include: authentic Korean restaurant atmosphere, appetizing food preparation scene in background, clean center area for text overlay. Photorealistic Korean small business flyer style. Professional print quality. No text, no letters, no words, no characters. No cartoon/anime/clipart.",
      "text_overlay": {
        "headline": "맛나분식",
        "catchphrase": "엄마 손맛 그대로",
        "sub_badge": "분식/떡볶이 전문점"
      },
      "style_notes": "Warm background image with center text overlay zone"
    },
    {
      "id": 2,
      "name": "menu",
      "side": "front",
      "position": "middle",
      "prompt": "Create a 794x1123 pixel image for a Korean small business promotional flyer. Section: Menu listing area (front page, middle). Business type: Korean restaurant. Style: clean, organized. Background: Clean white background with subtle warm texture. Divided into two-column grid layout with light divider lines. Small decorative food photography elements in corners — Korean tteokbokki, sundae, tempura in tiny circular frames. Subtle #FF8A65 accent lines separating menu items. Color scheme: White #FFFFFF background, accent #FF8A65, subtle #D32F2F highlights. Must include: clean two-column layout space, small food photo elements as decoration, warm but organized feel. Photorealistic Korean small business flyer style. Professional print quality. No text, no letters, no words, no characters. No cartoon/anime/clipart.",
      "text_overlay": {
        "section_title": "MENU",
        "items": "from flyer_copy.json",
        "footer_note": "계절 메뉴는 매장에 문의해주세요"
      },
      "style_notes": "Clean white background with two-column grid for menu items"
    },
    {
      "id": 3,
      "name": "promo_contact",
      "side": "front",
      "position": "bottom",
      "prompt": "Create a 794x1123 pixel image for a Korean small business promotional flyer. Section: Promotion and contact area (front page, bottom). Business type: Korean restaurant. Style: bold, attention-grabbing. Background: Rich #D32F2F gradient background transitioning to darker shade at edges. Clean center area with slightly lighter zone for contact information placement. Subtle pattern overlay — traditional Korean geometric pattern at very low opacity. Color scheme: Primary #D32F2F full background, White text areas, #FF8A65 accent elements. Must include: bold colored background, clean center zone for phone number and CTA, subtle traditional pattern. Photorealistic Korean small business flyer style. Professional print quality. No text, no letters, no words, no characters. No cartoon/anime/clipart.",
      "text_overlay": {
        "promo_headline": "오픈 기념 전 메뉴 10% 할인",
        "cta_text": "지금 바로 전화주세요!",
        "phone_display": "02-1234-5678"
      },
      "style_notes": "Primary color bold background for CTA impact"
    }
  ],
  "back_sections": [
    {
      "id": 4,
      "name": "features",
      "side": "back",
      "position": "top",
      "prompt": "Create a 794x1123 pixel image for a Korean small business promotional flyer. Section: Three key features area (back page, top). Business type: Korean restaurant. Style: clean, informative. Background: Light #F8F9FA background with three evenly-spaced circular icon placeholder areas arranged horizontally. Each circle has a soft #FF8A65 background. Below each circle, space for title and description text. Subtle connecting line between the three circles. Color scheme: Light background #F8F9FA, icon circles #FF8A65, accent #D32F2F. Must include: three horizontal circular icon areas with space below each for text, clean professional layout. Photorealistic Korean small business flyer style. Professional print quality. No text, no letters, no words, no characters. No cartoon/anime/clipart.",
      "text_overlay": {
        "section_title": "이런 점이 다릅니다",
        "items": "from flyer_copy.json features"
      },
      "style_notes": "Three-column feature cards with icon circles"
    },
    {
      "id": 5,
      "name": "gallery",
      "side": "back",
      "position": "middle",
      "prompt": "Create a 794x1123 pixel image for a Korean small business promotional flyer. Section: Photo gallery (back page, middle). Business type: Korean restaurant (분식). Style: warm, inviting lifestyle photography. Layout: Three photos arranged in a triptych — left panel showing cozy restaurant interior with wooden tables, center panel showing a beautifully plated tteokbokki dish with steam, right panel showing a friendly kitchen scene with fresh ingredients. Thin white borders between the three images. Warm amber color grading. Color scheme: Warm tones, #FF8A65 subtle frame accents. Must include: three distinct photo panels showing interior/food/atmosphere, white borders between panels. Photorealistic Korean small business flyer style. Professional print quality. No text, no letters, no words, no characters. No cartoon/anime/clipart.",
      "text_overlay": {
        "section_title": "매장 분위기",
        "captions": ["정감 가득한 내부", "넓고 깨끗한 좌석", "정성스런 한 그릇"]
      },
      "style_notes": "Triptych photo layout — three lifestyle images"
    },
    {
      "id": 6,
      "name": "location",
      "side": "back",
      "position": "bottom",
      "prompt": "Create a 794x1123 pixel image for a Korean small business promotional flyer. Section: Location map and business info (back page, bottom). Business type: Korean restaurant. Style: clean, informative. Background: Soft light background with left half showing a simplified stylistic map illustration area (Korean neighborhood streets, buildings as simple shapes), right half clean white for business information text. Bottom-right corner has a square placeholder area for QR code. Color scheme: Light #F8F9FA background, #D32F2F accent for map pin icon area, #37474F for info section. Must include: left-side map illustration area, right-side clean text area, bottom-right QR code placeholder square. Photorealistic Korean small business flyer style. Professional print quality. No text, no letters, no words, no characters. No cartoon/anime/clipart.",
      "text_overlay": {
        "section_title": "찾아오시는 길",
        "address": "서울시 마포구 합정동 123",
        "phone": "02-1234-5678",
        "hours": "매일 10:00~22:00",
        "closing_message": "여러분의 방문을 기다립니다"
      },
      "style_notes": "Left map area + right info text area + QR placeholder"
    }
  ]
}
```

---

## 제약사항
- 프롬프트는 반드시 영어로 작성 (이미지 생성 모델 최적화)
- 텍스트 삽입은 이미지 생성 후 PHP/Canvas에서 수행 → 이미지에 텍스트 포함 금지
- 업종 컬러 프리셋 필수 적용
- 카툰/애니메/클립아트 스타일 절대 금지
- 인쇄 품질에 적합한 선명한 이미지 요청
- 예상 처리 시간: ~15초
