# poster-factory — 범용 포스터 공장 V2

> 어떤 업종이든 **1장 인쇄용 포스터 (SVG, 2130×3000px)**를 자동 생성하는 오케스트레이션 스킬
> "카페 포스터 만들어줘" → 인터뷰 → 카피 → 이미지 프롬프트 → Python 이미지 생성 → SVG 조립 → 완성
> 출력물을 Adobe Illustrator에서 열어 텍스트/이미지를 자유롭게 편집 가능

## 트리거

사용자가 다음과 같이 요청할 때 이 스킬을 사용합니다:
- "포스터 만들어줘", "홍보물 만들어줘"
- "{업종} 포스터 생성", "{업종} 전단지"
- "poster factory", "포스터 공장"

## 핵심 원칙

1. **Claude = 두뇌** (인터뷰, 카피, 프롬프트, 레이아웃 결정)
2. **Python + Gemini = 손** (이미지 생성 + SVG 조립)
3. **SVG = 편집 가능 출력** (일러스트레이터에서 텍스트/이미지 수정)
4. **JSON 계약**: Claude → JSON → Python (중간에 사람이 수정 가능)
5. **이미지 보존**: 덮어쓰지 않음, 마음에 들면 수정하는 방식

## 포스터 구조 (2130×3000px)

```
┌──────────────────────────────────────┐ 0px
│         HERO IMAGE                   │
│    (메인 이미지 + 상호명 + 카피)      │  ← Gemini가 텍스트 포함 생성
│    aspect_ratio: "5:4"               │
│    ~55% of poster height             │
├──────────────────────────────────────┤ ~1650px
│ ✦ 특징1  ·  특징2  ·  특징3          │  ← SVG 텍스트 (편집 가능)
├──────────────────────────────────────┤
│  ┌────┐  ┌────┐  ┌────┐  ┌────┐    │
│  │img1│  │img2│  │img3│  │img4│    │  ← 품목별 이미지 (Gemini, 1:1)
│  └────┘  └────┘  └────┘  └────┘    │
│  품목1   품목2   품목3   품목4       │  ← SVG 텍스트 (편집 가능)
│  설명     설명    설명    설명       │
├──────────────────────────────────────┤ ~2700px
│  CTA: 상호명, 전화번호, 주소, 시간   │  ← SVG 텍스트 (편집 가능)
└──────────────────────────────────────┘ 3000px
```

## 파이프라인 (7단계)

```
Phase 1: 인터뷰 (Claude ↔ 사용자)
  ↓ brief.json
Phase 2: 카피 작성 (Claude)
  ↓ copy.json
Phase 3: 아트디렉팅 ⭐ NEW (Claude = 아트디렉터)
  ↓ layout_spec.json
  │  레이아웃 선택, 존(zone) 배치, 타이포그래피 확정,
  │  히어로 제약조건, 색상 흐름, 기승전결 매핑
  │  → 이미지 생성 전에 전체 구도를 확정!
Phase 4: 이미지 프롬프트 (Claude — layout_spec 기반으로 작성)
  ↓ design.json
Phase 5: 사용자 리뷰 (선택)
  ↓ 수정 반영
Phase 6: 이미지 생성 + SVG 조립 (Python + Gemini)
  ↓ images/*.png + poster.svg
Phase 7: 결과 확인 + 후속 (Claude ↔ 사용자)
```

---

## Phase 1: 인터뷰

사용자에게 **한 번에** 아래 정보를 물어봅니다 (한 질문씩 하지 말 것):

```
포스터를 만들기 위해 몇 가지 알려주세요:

1. **상호명**: (예: 뷰티네일)
2. **업종**: (예: 네일샵, 카페, 학원, 병원...)
3. **영업종목** (3~6개): (예: 젤네일, 페디큐어, 네일아트, 속눈썹)
4. **특징/강점** (2~4개): (예: 강남역 1분, 10년 경력)
5. **타겟 고객**: (예: 20-40대 여성)
6. **분위기/톤**: (예: 세련된, 따뜻한, 활기찬, 고급스러운)
7. **연락처**: 전화번호, 주소, 영업시간 (선택)
8. **원하는 색상**: 브랜드 컬러가 있으면 (선택, 없으면 업종에 맞게 추천)
```

### brief.json 형식

```json
{
  "business_name": "다음카페",
  "industry": "카페/음료",
  "items": [
    {"name": "아메리카노", "description": "자가로스팅 원두로 내린 깊은 풍미"},
    {"name": "라떼", "description": "부드러운 우유 거품과 에스프레소의 조화"},
    {"name": "디저트", "description": "매일 구워내는 수제 케이크와 쿠키"},
    {"name": "브런치", "description": "정성 가득한 모닝 플레이트"}
  ],
  "features": ["자가로스팅 원두", "당산역 도보 1분", "반려묘 동반 가능"],
  "target_audience": "직장인, 가족, 학생",
  "tone": "모던하고 따뜻한",
  "contact": {
    "phone": "02-2632-1830",
    "address": "서울 영등포구 당산동 (당산역 1번출구)"
  },
  "style": {
    "brand_color": "#2D3436",
    "accent_color": "#D4A373",
    "mood": "modern, warm, cozy, sophisticated"
  }
}
```

**저장 경로**: `_poster_factory/output/{business_name}_{YYYYMMDD_HHmm}/brief.json`

---

## Phase 2: 카피 작성

### copy.json 형식 (V2 — hero + items + features + cta)

```json
{
  "hero": {
    "headline": "한 잔의 여유, 다음카페",
    "subtext": "당산역 1분 · 자가로스팅 · 반려묘 환영",
    "badge": "SPECIALTY COFFEE"
  },
  "items": [
    {"name": "아메리카노", "description": "자가로스팅 싱글오리진 원두"},
    {"name": "라떼", "description": "국내산 우유 라떼아트"},
    {"name": "디저트", "description": "매일 굽는 수제 케이크"},
    {"name": "브런치", "description": "정성 가득 모닝 플레이트"}
  ],
  "features": ["자가로스팅 원두", "당산역 도보 1분", "반려묘 동반 가능"],
  "cta": {
    "headline": "오늘, 다음카페에서",
    "phone": "02-2632-1830",
    "address": "당산역 1번출구 도보 1분",
    "hours": "매일 08:00 - 22:00"
  }
}
```

### 카피 규칙
- items: `name` (34pt, bold) + `description` (22pt, 간결하게)
- features: 3~4개, "·"로 연결 (30pt, accent color)
- cta: headline + phone + address + hours

---

## Phase 3: 아트디렉팅 (Art Directing) ⭐ NEW

> **핵심**: 이미지 생성 전에 전체 구도를 확정한다.
> 히어로는 전체의 일부 — "따로 놀면 안 돼."

### 입력
- `brief.json` + `copy.json`
- `config/typography_scale.json` (타이포 스케일)
- `config/layout_patterns.json` (레이아웃 패턴 DB)

### 결정 사항 (6가지 — 이 단계에서 모두 확정)

1. **레이아웃 선택** — 업종+품목수+분위기 기반 decision_tree
2. **존(zone) 배치** — y_px, h_px 확정 + 기승전결(起承轉結) 매핑
   - 기(起) 25-35%: 도입/시선 사로잡기
   - 승(承) 35-45%: 핵심 정보 전달
   - 전(轉) 10-15%: 행동 유발 트리거
   - 결(結) 12-18%: CTA
3. **타이포그래피** — 스케일 선택 + 각 텍스트에 L1-L5 위계 배정 + size_px 확정
4. **히어로 이미지 제약** — fill_mode: full_bleed, 톤/구도/텍스트 안전영역/금지사항
5. **색상 흐름** — 존 간 전환 방식 (seamless/gradient/hard_cut/accent_line)
6. **품목 이미지 통일** — 히어로와 같은 조명/톤/배경

### 출력: layout_spec.json

스키마: `config/layout_spec_schema.json` 참조 (예시 포함)

저장 경로: `{workdir}/layout_spec.json`

---

## Phase 4: 이미지 프롬프트 (layout_spec 기반)

### design.json 형식 (V2)

```json
{
  "poster_version": 2,
  "canvas": {"width": 2130, "height": 3000},
  "style": {
    "brand_color": "#2D3436",
    "accent_color": "#D4A373",
    "bg_color": "#FAFAF8",
    "text_color": "#2D3436"
  },
  "hero": {
    "prompt": "영문 프롬프트 (한국어 텍스트는 원문 그대로 포함)",
    "aspect_ratio": "5:4"
  },
  "items": [
    {
      "name": "아메리카노",
      "prompt": "제품 사진 프롬프트 (텍스트 없이)",
      "aspect_ratio": "1:1"
    }
  ]
}
```

### 프롬프트 규칙 (layout_spec.json 기반으로 작성)

**Hero (메인 이미지)** — layout_spec.image_directives.hero 참조:
- **영어**로 작성, **한국어 상호명+카피**는 원문 그대로 포함
- `aspect_ratio`: layout_spec에서 지정한 값 사용 (5:4 고정이 아님!)
- `fill_mode: full_bleed` — 좌우폭을 꽉 채우는 배경 이미지로 생성
- `content_focus`: 피사체 위치 + 텍스트 안전영역 지시
- `tone_constraint`: 상하 영역과의 색상 연결 지시
- `forbidden`: [자체 텍스트, 테두리, 프레임, 흰색 여백]
- **끝에 반드시**: `No pixel labels, no guidelines, no ruler marks, no watermarks`

**Items (품목 이미지)** — layout_spec.image_directives.items 참조:
- **텍스트 없는** 순수 제품/서비스 사진
- `aspect_ratio`: layout_spec에서 지정한 값 사용
- `style_constraint`: 히어로와 같은 조명/톤/배경 통일
- **끝에 반드시**: `No text, no labels, no overlays`

---

## Phase 5: 사용자 리뷰 (선택)

```
📋 포스터 구성:
- 업체: {business_name}
- 히어로: {headline}
- 품목: {N}개 ({item_names})
- 색상: {brand_color} / {accent_color}
- 예상 비용: ~${0.10 * (N+1)} (히어로 + 품목 N개)
- 예상 시간: ~{(N+1) * 25}초

이대로 생성할까요?
```

---

## Phase 6: 이미지 생성 + SVG 조립 (Python)

### 실행 방법

```bash
# 전체 생성 (히어로 + 품목 이미지 → SVG 조립)
source /var/www/html/.env 2>/dev/null
export GEMINI_API_KEY
export PYTHONPATH=/home/ysung/.local/lib/python3.12/site-packages:$PYTHONPATH
python3 _poster_factory/scripts/poster_generator.py \
  --workdir "_poster_factory/output/{job_dir}"

# SVG만 재조립 (텍스트/레이아웃 변경 후, 이미지 재생성 없이)
python3 _poster_factory/scripts/poster_generator.py \
  --workdir "{workdir}" --rebuild-svg

# 히어로 이미지만 재생성
python3 _poster_factory/scripts/poster_generator.py \
  --workdir "{workdir}" --regen-hero

# 품목 N번 이미지만 재생성
python3 _poster_factory/scripts/poster_generator.py \
  --workdir "{workdir}" --regen-item 2

# 임베드 버전 (단일 파일, base64 인코딩)
python3 _poster_factory/scripts/poster_generator.py \
  --workdir "{workdir}" --rebuild-svg --embed
```

---

## Phase 7: 결과 확인

```
✅ 포스터 생성 완료!
- 업체: {business_name}
- 이미지: 히어로 + {N}개 품목
- 소요시간: {elapsed}초
- 비용: ~${cost}

📁 결과물:
  poster.svg          ← 일러스트레이터에서 편집
  images/hero.png     ← 히어로 이미지
  images/item_01.png  ← 품목 이미지들
  images/item_02.png
  ...

수정 요청:
- "히어로 이미지 다시 만들어줘" → --regen-hero
- "2번 품목 이미지 바꿔줘" → --regen-item 2
- "카피 수정했으니 SVG만 다시 만들어줘" → --rebuild-svg
```

---

## 파일 구조

```
_poster_factory/
├── scripts/
│   └── poster_generator.py      # SVG 포스터 생성 엔진 V2
├── config/
│   ├── defaults.json             # 기본 설정 (캔버스, 생성 파라미터)
│   ├── layout_patterns.json      # 5가지 레이아웃 + 업종별 선택 규칙
│   ├── typography_scale.json     # ⭐ 모듈러 스케일 5단계 타이포 위계
│   ├── layout_spec_schema.json   # ⭐ 아트디렉터 출력 스키마 (예시 포함)
│   └── color_palettes.json       # 14개 업종별 색상 팔레트
├── output/
│   └── {business}_{timestamp}/   # 작업 디렉토리
│       ├── brief.json            # Phase 1: 업종 정보
│       ├── copy.json             # Phase 2: 텍스트 카피
│       ├── layout_spec.json      # ⭐ Phase 3: 아트디렉터 구도 결정
│       ├── design.json           # Phase 4: 이미지 프롬프트
│       ├── poster.svg            # ⭐ Phase 6: 최종 포스터
│       ├── poster_preview.png    # PNG 미리보기 (선택)
│       ├── images/
│       │   ├── hero.png          # 히어로 이미지
│       │   ├── item_01.png       # 품목 이미지
│       │   └── ...
│       └── metadata.json         # 생성 로그
└── logs/
    └── poster_generator.log
```

## 비용/시간 기준

| 항목 | 값 |
|------|-----|
| 히어로 1장 | ~$0.10, ~15초 |
| 품목 1장 | ~$0.10, ~15초 (+ 10초 딜레이) |
| 4품목 포스터 | ~$0.50, ~2분 |
| 6품목 포스터 | ~$0.70, ~3분 |

## 재사용 모듈

| 모듈 | 위치 | 용도 |
|------|------|------|
| `GeminiClient` | `_detail_page/scripts/gemini_client.py` | Gemini API (텍스트+이미지), aspect_ratio/resize_to 파라미터 지원 |

## 주의사항

- Gemini API 키: `.env`에 `GEMINI_API_KEY=...`
- 이미지 모델 `gemini-3.1-flash-image-preview`는 유료 결제 필요
- 한글: 히어로에만 Gemini가 텍스트 렌더링, 나머지는 SVG 텍스트로 처리
- SVG 텍스트 폰트: Pretendard > Noto Sans KR > NanumGothic > Malgun Gothic
- rate limit: 이미지 간 10초 대기 (Gemini API 제한)
- SVG 편집: poster.svg를 일러스트레이터에서 열면 텍스트/이미지 모두 편집 가능
- 이미지 교체: images/ 폴더의 PNG를 직접 교체한 후 `--rebuild-svg`
