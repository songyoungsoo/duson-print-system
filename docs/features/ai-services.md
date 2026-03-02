# Duson AI 서비스 개발 가이드 (AI Service Development Guide)

## 1. 개요
본 문서는 두손 인쇄 이커머스 시스템에 통합된 AI 에이전트 팀의 아키텍처와 개발 지침을 설명합니다. AI 서비스는 인쇄 주문 프로세스의 자동화, 고객 상담 효율화, 그리고 콘텐츠 생성 지원을 목적으로 합니다.

### AI 서비스 구성 (5개 핵심 파일)
1. **ChatbotService.php**: 야간 및 휴일 대응 AI 상담 챗봇
2. **ChatbotKnowledge.php**: 챗봇 전용 지식 베이스 (FAQ 및 회사 정보)
3. **FlyerAIService.php**: 전단지 카피 및 이미지 자동 생성 엔진
4. **GeminiService.php**: 제품 홍보용 카피라이팅 서비스
5. **ClaudeService.php**: [DEPRECATED] 레거시 카피라이팅 서비스

---

## 2. 서비스 상세

### 2.1 AI 챗봇 (ChatbotService.php)
- **위치**: `/var/www/html/v2/src/Services/AI/ChatbotService.php` (약 1,562줄)
- **모델**: `gemini-2.5-flash`
- **주요 기능**:
    - 야간/휴일 실시간 고객 상담 및 9개 주요 제품 가격 자동 안내
    - 지식 기반 FAQ 응답 및 TTS(Text-to-Speech) 음성 지원
- **상태 머신 (State Machine)**:
    - 제품 선택 → 옵션 단계별 안내 → 가격 조회/계산 → 결과 표시 및 주문 링크 제공
- **스티커 가격 계산 (CRITICAL)**:
    - 스티커는 DB lookup 방식이 아닌 **수학 공식**으로 계산합니다.
    - **SSOT**: `sticker_new/calculate_price_ajax.php` 로직을 내부적으로 구현.
- **지식 베이스**: `ChatbotKnowledge.php` 연동 (회사정보, 작업규약, 디자인비, 파일가이드, FAQ 등)
- **TTS 구현**: `gemini-2.5-flash-preview-tts` + `Kore` 음성 사용 → PCM 데이터를 WAV 포맷으로 변환하여 브라우저 재생.
- **Rate Limit**: 전체 300회/일, IP당 20회/일 제한 (`includes/ai_rate_limiter.php` 적용).

### 2.2 전단지 AI 생성기 (FlyerAIService.php)
- **위치**: `/var/www/html/flyer/maker/includes/FlyerAIService.php` (약 452줄)
- **모델**: `gemini-3-pro-preview` (텍스트) + `gemini-3-pro-image-preview` (이미지)
- **주요 기능**: 업종과 상호명 입력 시 전단지 카피와 배경 이미지를 자동 생성.
- **데이터 구조**:
    - **텍스트**: JSON 포맷 (tagline, subtitle, features[3], menu[7-10], promotion, hours)
    - **이미지**: JPEG (300DPI, 1200px 이상, 인쇄 적합 품질)
- **프롬프트 전략**: 상세페이지 스타일의 구조화된 프롬프트 (존별 글자수 제한, 업종별 톤 가이드).
- **업종 프리셋 (15종)**: 음식점(5), 학원(3), 피트니스(3), 뷰티(3), 일반매장 등.
- **⚠️ 주의사항**: `gemini-3-pro-preview`는 서비스 종료 예정으로, 장애 시 `gemini-3.1-pro-preview`로 fallback 처리.

### 2.3 카피라이터 (GeminiService.php)
- **위치**: `/var/www/html/v2/src/Services/AI/GeminiService.php` (약 138줄)
- **모델**: `gemini-2.5-flash` (기존 2.0에서 업그레이드됨)
- **기능**: 제품명, 타겟, 키워드, 톤 설정을 기반으로 헤드카피와 서브카피 5세트 생성.
- **컨트롤러**: `CopywriterController.php`에서 호출.

### 2.4 Claude 서비스 (ClaudeService.php) — DEPRECATED
- **위치**: `/var/www/html/v2/src/Services/AI/ClaudeService.php` (약 141줄)
- **상태**: 현재 미사용 (Deprecated)
- **용도**: Gemini API 장애 시를 대비한 백업용 카피라이팅 서비스로 유지.

---

## 3. 공통 구현 패턴

### 3.1 API 호출 패턴 (cURL)
모든 AI 서비스는 오토로더가 없는 환경을 고려하여 독립적인 cURL 호출 로직을 가집니다.
```php
$url = $this->baseUrl . $model . ':generateContent?key=' . $this->apiKey;
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => $timeout,
]);
```

### 3.2 JSON 응답 파싱
Gemini 모델이 응답에 마크다운 코드 블록(```json)을 포함하는 경우를 대비한 정규식 처리:
```php
$text = preg_replace('/^```json\s*/s', '', $text);
$text = preg_replace('/\s*```$/s', '', $text);
$parsed = json_decode($text, true);
```

### 3.3 API 키 로딩
- `.env` 파일의 `GEMINI_API_KEY`를 사용합니다.
- 로딩 순서: `$_ENV` → `getenv()` → `.env` 파일 직접 파싱 (환경에 따른 호환성 확보).

---

## 4. 개발 가이드라인

### 4.1 새 AI 서비스 추가 시
1. **독립성 유지**: `BaseAIService` 추상 클래스를 만들지 마십시오 (오토로더 부재). 기존 파일의 `callAPI()` 패턴을 복사하여 독립 파일로 생성합니다.
2. **보안**: API 키는 반드시 `.env`에서 로드하며 코드에 하드코딩하지 않습니다.
3. **안정성**: `ai_rate_limiter.php`를 적용하여 무분별한 API 호출을 방지하고 적절한 타임아웃을 설정합니다.

### 4.2 모델 및 프롬프트 관리
1. **독립적 변경**: 각 서비스는 독립적이므로 특정 서비스의 모델 변경이 다른 서비스에 영향을 주지 않습니다.
2. **검증**: 모델 변경 시 한글 출력 품질과 `responseMimeType` 지원 여부를 반드시 확인하십시오.
3. **비용**: 유료 플랜 API 키를 사용 중이므로 토큰 소모량을 모니터링해야 합니다.

### 4.3 절대 금지 사항 (CRITICAL)
- **챗봇 리팩토링 금지**: `ChatbotService.php`의 1,500줄이 넘는 상태머신 로직은 매우 복잡하므로 구조적 리팩토링을 지양하십시오.
- **네임스페이스 추가 금지**: `FlyerAIService.php` 등 독립 모듈에 네임스페이스를 추가하면 기존 호출부에서 에러가 발생할 수 있습니다.
- **스티커 로직 분리 금지**: 챗봇 내부의 스티커 가격 계산 로직은 해당 서비스의 SSOT이므로 외부로 이동시키지 마십시오.

---

## 5. 모델 비용 및 성능 참고

| 모델 | Input (1M) | Output (1M) | 비고 |
|------|------------|-------------|------|
| gemini-2.5-flash | $0.15 | $0.60 | 가장 경제적이며 빠름 |
| gemini-3-pro-preview | 미공개 | 미공개 | 서비스 종료 예정 (Discontinuing) |
| gemini-3-pro-image-preview | 미공개 | 미공개 | 이미지 생성 전용 모델 |

---

## 6. 파일 구조 요약

```
v2/src/Services/AI/
├── ChatbotService.php       # AI 챗봇 핵심 로직 (gemini-2.5-flash)
├── ChatbotKnowledge.php     # 챗봇 지식 베이스 데이터
├── GeminiService.php        # 카피라이터 서비스 (gemini-2.5-flash)
└── ClaudeService.php        # [DEPRECATED] 백업용 클로드 서비스

flyer/maker/includes/
└── FlyerAIService.php       # 전단지 생성 AI 엔진 (gemini-3-pro)

flyer/maker/api/
├── generate_content.php     # 텍스트 생성 AJAX 엔드포인트
└── generate_image.php       # 이미지 생성 AJAX 엔드포인트

includes/
└── ai_rate_limiter.php      # AI 호출 횟수 제한 모듈
```

마지막 업데이트: 2026-03-02
