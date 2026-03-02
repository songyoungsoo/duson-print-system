# 💻 프롬프팅 에이전트 (Prompter / Image Generator Agent)

## 역할
디자인 에이전트가 작성한 프롬프트를 Gemini 이미지 생성 API에 전달하여
6개 섹션 이미지를 실제로 생성한다. 생성된 이미지를 앞면(3장)과 뒷면(3장)으로
합쳐서 최종 전단지 이미지를 완성한다.

## 파이프라인 위치
```
[1] Collector(0s) → [2] Copywriter(~15s) → [3] Designer(~15s) → [4] Prompter(~120s)
```

## 모델
- `gemini-3-pro-image-preview` (이미지 생성 전용)

## 입력
- `flyer_design.json` (디자인 에이전트 출력 — 6개 프롬프트)
- `flyer_copy.json` (카피라이팅 출력 — 텍스트 확인용)

## 출력
- `output/{job_id}/sections/section_01_hero.png` ~ `section_06_location.png`
- `output/{job_id}/front_page.png` (앞면 합본: 794x3369px)
- `output/{job_id}/back_page.png` (뒷면 합본: 794x3369px)
- `output/{job_id}/metadata.json` (생성 로그)

---

## 이미지 생성 프로세스

### Step 1: 프롬프트 최종 조립
```
1. flyer_design.json에서 6개 섹션 프롬프트 로드
2. 각 프롬프트 끝에 품질 접미사 확인:
   "Photorealistic Korean small business flyer style. Professional print quality.
    No text, no letters, no words, no characters. No cartoon/anime/clipart."
3. 누락 시 자동 추가
4. 프롬프트 길이 검증: 최소 100자, 최대 2000자
```

### Step 2: 순차 이미지 생성 (6장)
```
생성 순서: section_01 → section_02 → ... → section_06
각 호출 간 10초 대기 (rate limit 방지)
실패 시 최대 3회 재시도 (재시도 간 15초 대기)
3회 모두 실패 시 → 폴백 이미지 생성
```

### Step 3: 폴백 이미지 처리
```
API 실패 시 PHP GD로 단색 배경 이미지 생성:
- 배경색: global_style.primary_color
- 크기: 794x1123px
- 중앙에 섹션명 표시 (디버그용)
→ 전체 파이프라인이 중단되지 않도록 보장
```

### Step 4: 이미지 합치기 (Stitching)
```
앞면 합본 (front_page.png):
  section_01_hero.png      (794x1123)
  + section_02_menu.png    (794x1123)
  + section_03_promo.png   (794x1123)
  = front_page.png         (794x3369)

뒷면 합본 (back_page.png):
  section_04_features.png  (794x1123)
  + section_05_gallery.png (794x1123)
  + section_06_location.png(794x1123)
  = back_page.png          (794x3369)

합치기 방식: 세로(vertical) 연결, 간격 없음
도구: PHP GD imagecopy() 또는 Python Pillow
```

### Step 5: 텍스트 오버레이 (Post-Processing)
```
이미지 생성 후, flyer_copy.json의 텍스트를 이미지 위에 렌더링.
이 단계는 Prompter가 아닌 별도 렌더러(PHP/Canvas)에서 처리.
Prompter는 이미지 생성 + 합치기까지만 담당.
```

---

## API 호출 사양

### Gemini Image Generation API (PHP cURL)
```php
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3-pro-image-preview:generateContent";

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ],
    "generationConfig" => [
        "responseModalities" => ["IMAGE", "TEXT"],
        "imageDimension" => [
            "width" => 794,
            "height" => 1123
        ]
    ]
];

$ch = curl_init($url . "?key=" . $apiKey);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
```

### 응답 파싱
```php
$data = json_decode($response, true);

// 이미지 데이터 추출
foreach ($data['candidates'][0]['content']['parts'] as $part) {
    if (isset($part['inlineData'])) {
        $imageBytes = base64_decode($part['inlineData']['data']);
        $mimeType = $part['inlineData']['mimeType']; // "image/png" 또는 "image/jpeg"
        file_put_contents($outputPath, $imageBytes);
        break;
    }
}
```

### Python 대체 방식
```python
import google.generativeai as genai
import time

genai.configure(api_key=GEMINI_API_KEY)
model = genai.GenerativeModel('gemini-3-pro-image-preview')

for i, section in enumerate(design_sections):
    if i > 0:
        time.sleep(10)  # 10초 간격

    response = model.generate_content(
        section['prompt'],
        generation_config={
            "response_mime_type": "image/png",
        }
    )

    image_data = response.candidates[0].content.parts[0].inline_data.data
    with open(f"sections/section_{i+1:02d}_{section['name']}.png", 'wb') as f:
        f.write(image_data)
```

---

## 에러 핸들링

### 재시도 로직
```php
$maxRetries = 3;
$retryDelay = 15; // seconds
$callInterval = 10; // seconds between sections

function generateSectionImage($prompt, $outputPath, $sectionId) {
    global $maxRetries, $retryDelay;

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            $result = callGeminiImageAPI($prompt);

            if ($result['success']) {
                file_put_contents($outputPath, $result['image_data']);
                return [
                    'success' => true,
                    'attempts' => $attempt,
                    'filename' => basename($outputPath)
                ];
            }
        } catch (Exception $e) {
            logError("Section {$sectionId} attempt {$attempt} failed: " . $e->getMessage());
        }

        if ($attempt < $maxRetries) {
            sleep($retryDelay);
        }
    }

    // 3회 모두 실패 → 폴백 이미지
    return generateFallbackImage($outputPath, $sectionId);
}
```

### 폴백 이미지 생성
```php
function generateFallbackImage($outputPath, $sectionId) {
    global $primaryColor;

    $img = imagecreatetruecolor(794, 1123);

    // Primary 컬러 배경
    list($r, $g, $b) = sscanf($primaryColor, "#%02x%02x%02x");
    $bgColor = imagecolorallocate($img, $r, $g, $b);
    imagefill($img, 0, 0, $bgColor);

    imagepng($img, $outputPath);
    imagedestroy($img);

    return [
        'success' => false,
        'fallback' => true,
        'attempts' => 3,
        'filename' => basename($outputPath)
    ];
}
```

### HTTP 상태별 처리
| HTTP Code | 의미 | 처리 |
|-----------|------|------|
| 200 | 성공 | 이미지 저장 |
| 400 | 프롬프트 거부 | 프롬프트 수정 후 재시도 (민감 키워드 제거) |
| 429 | Rate Limit | 30초 대기 후 재시도 |
| 500 | 서버 에러 | 15초 대기 후 재시도 |
| Timeout | 60초 초과 | 15초 대기 후 재시도 |

### 프롬프트 거부 시 수정 전략
```
400 에러 수신 시:
1. "person", "human", "face" 등 민감 키워드 제거
2. "food", "interior", "product" 등 안전한 키워드로 교체
3. 프롬프트 단순화 (장면 설명 축소)
4. 수정된 프롬프트로 재시도
```

---

## 이미지 합치기 (Stitching)

### PHP GD 방식
```php
function stitchSections($sectionPaths, $outputPath) {
    $width = 794;
    $sectionHeight = 1123;
    $totalHeight = $sectionHeight * count($sectionPaths); // 3 * 1123 = 3369

    $canvas = imagecreatetruecolor($width, $totalHeight);

    foreach ($sectionPaths as $index => $path) {
        $section = imagecreatefrompng($path);
        $srcWidth = imagesx($section);
        $srcHeight = imagesy($section);

        // 크기가 다르면 리사이즈
        if ($srcWidth !== $width || $srcHeight !== $sectionHeight) {
            $resized = imagecreatetruecolor($width, $sectionHeight);
            imagecopyresampled($resized, $section, 0, 0, 0, 0,
                $width, $sectionHeight, $srcWidth, $srcHeight);
            imagedestroy($section);
            $section = $resized;
        }

        $yOffset = $index * $sectionHeight;
        imagecopy($canvas, $section, 0, $yOffset, 0, 0, $width, $sectionHeight);
        imagedestroy($section);
    }

    imagepng($canvas, $outputPath);
    imagedestroy($canvas);

    return filesize($outputPath);
}

// 실행
$frontSections = [
    "sections/section_01_hero.png",
    "sections/section_02_menu.png",
    "sections/section_03_promo.png"
];
$backSections = [
    "sections/section_04_features.png",
    "sections/section_05_gallery.png",
    "sections/section_06_location.png"
];

stitchSections($frontSections, "front_page.png");  // 794x3369
stitchSections($backSections, "back_page.png");     // 794x3369
```

---

## SSE 진행률 업데이트

```php
// 파이프라인 전체 진행률을 SSE로 클라이언트에 전달
function sendSSE($stage, $section, $progress, $message) {
    echo "data: " . json_encode([
        'stage' => $stage,           // 'prompter'
        'section' => $section,       // 1~6
        'progress' => $progress,     // 0~100
        'message' => $message,       // "hero 이미지 생성 중..."
        'timestamp' => microtime(true)
    ]) . "\n\n";
    ob_flush();
    flush();
}

// 사용 예시
sendSSE('prompter', 1, 55, 'hero 이미지 생성 중...');
sendSSE('prompter', 2, 63, 'menu 이미지 생성 중...');
sendSSE('prompter', 3, 72, 'promo_contact 이미지 생성 중...');
sendSSE('prompter', 4, 80, 'features 이미지 생성 중...');
sendSSE('prompter', 5, 88, 'gallery 이미지 생성 중...');
sendSSE('prompter', 6, 95, 'location 이미지 생성 중...');
sendSSE('prompter', 0, 98, '앞면/뒷면 합치는 중...');
sendSSE('complete', 0, 100, '전단지 생성 완료!');
```

---

## 메타데이터 출력: `metadata.json`

```json
{
  "job_id": "flyer_20260302_abc123",
  "business_name": "맛나분식",
  "industry_code": "korean",
  "generated_at": "2026-03-02T12:00:00+09:00",
  "pipeline_version": "1.0",
  "model": "gemini-3-pro-image-preview",
  "total_sections": 6,
  "successful": 6,
  "failed": 0,
  "fallback_used": 0,
  "total_generation_time_seconds": 95,
  "total_api_calls": 6,
  "images": {
    "section_size": "794x1123",
    "front_page": {
      "filename": "front_page.png",
      "size": "794x3369",
      "sections": [1, 2, 3]
    },
    "back_page": {
      "filename": "back_page.png",
      "size": "794x3369",
      "sections": [4, 5, 6]
    }
  },
  "sections": [
    {
      "id": 1,
      "name": "hero",
      "side": "front",
      "filename": "section_01_hero.png",
      "prompt_length": 487,
      "generation_time_ms": 12400,
      "retries": 0,
      "status": "success"
    },
    {
      "id": 2,
      "name": "menu",
      "side": "front",
      "filename": "section_02_menu.png",
      "prompt_length": 523,
      "generation_time_ms": 14200,
      "retries": 0,
      "status": "success"
    },
    {
      "id": 3,
      "name": "promo_contact",
      "side": "front",
      "filename": "section_03_promo.png",
      "prompt_length": 412,
      "generation_time_ms": 11800,
      "retries": 0,
      "status": "success"
    },
    {
      "id": 4,
      "name": "features",
      "side": "back",
      "filename": "section_04_features.png",
      "prompt_length": 498,
      "generation_time_ms": 13600,
      "retries": 0,
      "status": "success"
    },
    {
      "id": 5,
      "name": "gallery",
      "side": "back",
      "filename": "section_05_gallery.png",
      "prompt_length": 556,
      "generation_time_ms": 15100,
      "retries": 0,
      "status": "success"
    },
    {
      "id": 6,
      "name": "location",
      "side": "back",
      "filename": "section_06_location.png",
      "prompt_length": 478,
      "generation_time_ms": 12900,
      "retries": 0,
      "status": "success"
    }
  ]
}
```

---

## 비용 관리
- 이미지 1장: ~$0.04 (Gemini Pro image)
- 6장: ~$0.24
- 재시도 포함 최대: ~$0.48
- 일일 예산 한도: $10 (약 250건 전단지)
- 예상 처리 시간: ~120초 (6장 x 10초 간격 + 생성 시간)
