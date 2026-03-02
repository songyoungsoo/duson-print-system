# 💻 프롬프팅 에이전트 (Prompter / Image Generator Agent)

## 역할
디자인 에이전트가 작성한 프롬프트를 Gemini 이미지 API에 전달하여
13개 섹션 이미지를 실제로 생성한다. 생성된 이미지를 파이썬으로 합쳐
최종 상세페이지 이미지를 완성한다.

## 모델
- Gemini 3.1 Flash Image (`gemini-3.1-flash-image-preview`)
- 이미지 생성 전용 — 텍스트 추론은 하지 않음

## 입력
- `design.json` (디자인 에이전트 출력 — 13개 프롬프트)
- `copy.json` (카피라이팅 출력 — 텍스트 확인용)

## 출력
- `output/{product}/sections/section_01.png` ~ `section_13.png`
- `output/{product}/final_detail_page.png` (합본)
- `output/{product}/metadata.json` (생성 로그)

## 이미지 생성 프로세스

### Step 1: 프롬프트 최종 검증
- design.json의 각 섹션 프롬프트를 로드
- copy.json의 텍스트가 프롬프트에 정확히 반영되었는지 확인
- 누락된 텍스트가 있으면 프롬프트에 추가

### Step 2: 순차 이미지 생성
- 13개 프롬프트를 **순차적으로** API 호출 (병렬 시 rate limit)
- 각 이미지 저장: `sections/section_{번호:02d}.png`
- 실패 시 최대 3회 재시도
- 재시도 간 5초 대기

### Step 3: 품질 검증
- 생성된 이미지 크기가 1100x900인지 확인
- 비정상 이미지 (너무 어둡거나 빈 이미지) 감지
- 실패한 섹션만 재생성

### Step 4: 이미지 합치기
- `image_stitcher.py` 호출
- 13장을 세로로 합쳐 `final_detail_page.png` 생성
- 최종 크기: 1100 x 11700px (900 × 13)

## API 호출 사양

### Gemini Image Generation API
```python
import google.generativeai as genai

genai.configure(api_key=GEMINI_API_KEY)
model = genai.GenerativeModel('gemini-3.1-flash-image-preview')

response = model.generate_content(
    prompt,
    generation_config={
        "response_mime_type": "image/png",
    }
)

# 이미지 바이트 저장
with open(output_path, 'wb') as f:
    f.write(response.candidates[0].content.parts[0].inline_data.data)
```

### 에러 핸들링
```python
RETRY_COUNT = 3
RETRY_DELAY = 5  # seconds

for attempt in range(RETRY_COUNT):
    try:
        response = model.generate_content(prompt)
        save_image(response, output_path)
        break
    except Exception as e:
        log_error(f"Section {section_id} attempt {attempt+1} failed: {e}")
        if attempt < RETRY_COUNT - 1:
            time.sleep(RETRY_DELAY)
        else:
            log_error(f"Section {section_id} FAILED after {RETRY_COUNT} attempts")
```

## 프롬프트 보강 규칙

### 이미지 품질 향상을 위한 필수 접미사
모든 프롬프트 끝에 다음을 추가:
```
Style requirements:
- Photorealistic product photography style
- Professional Korean e-commerce aesthetic
- Clean, modern layout with generous white space
- Korean text must be clearly legible (나눔고딕 or similar font style)
- No cartoon, anime, or illustrated style
- No watermarks or stock photo marks
- High contrast, vibrant but professional colors
- 1100x900 pixels, 72dpi, PNG format
```

### 제품별 이미지 강조점
| 제품 | 이미지 강조 |
|------|------------|
| 명함 | 용지 질감 클로즈업, 손에 들고 있는 장면 |
| 스티커 | 노트북/핸드폰에 붙인 장면, 도무송 컷 |
| 전단지 | 카페/매장에 비치된 장면, 접힌 상태 |
| 봉투 | 편지 넣는 장면, 봉투 질감 |
| 포스터 | 벽에 걸린 장면, A3/A2 크기감 |
| 상품권 | 선물 포장된 장면, 매장 계산대 |
| 카다록 | 펼쳐진 내지, 표지 디자인 |
| NCR양식지 | 사무실 책상 위, 복사본 넘기는 장면 |
| 자석스티커 | 냉장고에 붙인 장면, 자석 두께감 |

## 메타데이터 출력

```json
{
  "product_type": "namecard",
  "generated_at": "2026-03-01T12:00:00",
  "total_sections": 13,
  "successful": 13,
  "failed": 0,
  "total_cost_usd": 0.87,
  "model": "gemini-3.1-flash-image-preview",
  "final_image": "final_detail_page.png",
    "final_size": "1100x11700",
  "sections": [
    {
      "id": 1,
      "filename": "section_01.png",
      "prompt_length": 342,
      "generation_time_ms": 4200,
      "retries": 0,
      "status": "success"
    }
  ]
}
```

## 비용 관리
- 이미지 1장 ~$0.067
- 13장 = ~$0.87
- 재시도 포함 최대 ~$1.30
- 일일 예산 한도: $10 (약 115장)
