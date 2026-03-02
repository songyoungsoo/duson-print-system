<?php
/**
 * 전단지 AI 서비스 — 텍스트 카피라이팅 + 이미지 생성
 * 
 * 텍스트: Gemini 3 Pro (gemini-3-pro-preview) — 한글 품질 최적화
 * 이미지: Nano Banana Pro (gemini-3-pro-image-preview) — 최고 품질 이미지 생성
 * 
 * ⚠️ FALLBACK 전략 (2026-03-02):
 *   gemini-3-pro-preview가 Discontinuing 상태.
 *   종료 시 textModel을 'gemini-3.1-pro-preview'로 변경.
 *   이미지 모델(gemini-3-pro-image-preview)은 별도 확인 필요.
 * 
 * @since 2026-03-02
 * @updated 2026-03-02 fallback 전략 문서화
 */

class FlyerAIService
{
    private string $apiKey;
    private string $textModel = 'gemini-3-pro-preview';
    private string $imageModel = 'gemini-3-pro-image-preview';
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private string $uploadDir;
    
    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? $this->loadApiKey();
        $this->uploadDir = __DIR__ . '/../uploads/ai/';
        
        if (!is_dir($this->uploadDir)) {
            @mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * .env에서 GEMINI_API_KEY 로드
     */
    private function loadApiKey(): string
    {
        // 1) PHP 환경변수 우선
        $key = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '';
        if ($key) return $key;
        
        // 2) .env 파일 직접 파싱
        $envFile = $_SERVER['DOCUMENT_ROOT'] . '/.env';
        if (!$envFile || !file_exists($envFile)) {
            // fallback: 프로젝트 루트에서 찾기
            $envFile = realpath(__DIR__ . '/../../../../.env');
        }
        
        if ($envFile && file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') continue;
                if (strpos($line, 'GEMINI_API_KEY=') === 0) {
                    return trim(substr($line, strlen('GEMINI_API_KEY=')));
                }
            }
        }
        
        return '';
    }
    
    /**
     * API 키가 설정되어 있는지 확인
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
    
    // ════════════════════════════════════════════════════════════
    //  텍스트 생성 (카피라이팅)
    // ════════════════════════════════════════════════════════════
    
    /**
     * 전단지 콘텐츠 자동 생성
     * 
     * @param string $industryKey  업종 키 (예: korean_food)
     * @param string $industryLabel 업종 한글명 (예: 한식 음식점)
     * @param string $businessName 상호명 (예: 맛나분식)
     * @return array ['success' => true, 'data' => [...]] or ['error' => '...']
     */
    public function generateContent(string $industryKey, string $industryLabel, string $businessName): array
    {
        if (!$this->isConfigured()) {
            return ['error' => 'API 키가 설정되지 않았습니다.'];
        }
        
        $prompt = $this->buildTextPrompt($industryKey, $industryLabel, $businessName);
        
        $data = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => 0.85,
                'maxOutputTokens' => 8192,
                'responseMimeType' => 'application/json',
            ]
        ];
        
        $result = $this->callAPI($this->textModel, $data, 120);
        
        if (isset($result['error'])) {
            return $result;
        }
        
        // JSON 응답 파싱
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $text = trim($text);
        
        // ```json ... ``` 래핑 제거
        $text = preg_replace('/^```json\s*/s', '', $text);
        $text = preg_replace('/\s*```$/s', '', $text);
        
        $parsed = json_decode($text, true);
        if (!$parsed) {
            return ['error' => 'AI 응답을 파싱할 수 없습니다.', 'raw' => $text];
        }
        
        return ['success' => true, 'data' => $parsed];
    }
    
    /**
     * 텍스트 프롬프트 생성
     */
    private function buildTextPrompt(string $industryKey, string $industryLabel, string $businessName): string
    {
        $menuGuide = $this->getMenuGuide($industryKey);
        $tonGuide = $this->getToneGuide($industryKey);
        
        return <<<PROMPT
당신은 대한민국에서 가장 잘 나가는 전단지 카피라이터입니다.
실제 동네 가게의 A4 홍보 전단지를 만들어야 합니다.
마치 쿠팡/배달의민족 상세페이지처럼 — 고객이 읽자마자 "여기 가봐야겠다" 느끼게 만드세요.

■ 의뢰 정보
- 업종: {$industryLabel}
- 상호명: {$businessName}

■ 전단지 레이아웃 (A4 앞면 — 상단→하단 순서)

① 캐치프레이즈 (tagline)
   - 상호명 바로 아래에 큼직하게 들어갈 한 줄 카피
   - 15자 이내. 운율감/리듬감 있게
   - {$tonGuide}
   - 금지: "최고의", "No.1", "최상의", "완벽한", "엄선된", "프리미엄"
   - 좋은 예: "밥심이 필요할 땐", "오늘도 한 상 가득", "골목 끝 그 집"

② 특장점 배지 3개 (features)
   - 가로 배너 3칸에 들어갈 짧은 강점
   - 각 15자 이내 (배지 안에 들어가야 하니까 짧게!)
   - 고객이 "오" 하고 끄덕일 구체적 사실만
   - 좋은 예: "30년 전통", "국내산 재료", "매일 새벽 직접 손질"
   - 나쁜 예: "최상의 서비스", "고객 만족 100%"

③ 메뉴/서비스 목록 (menu)
   - {$menuGuide}
   - 가격은 반드시 2025-2026년 대한민국 실제 시세 반영
   - 가격 형식: "8,000원", "15,000원" (숫자+원)
   - 음식점: 대표 메뉴 7~10개. 시그니처 메뉴를 맨 위에
   - 학원: 주요 프로그램 5~7개 + 월 수강료
   - 피트니스: 프로그램 5~7개 + 월/회 가격
   - 뷰티: 시술 메뉴 7~10개 + 가격
   - 일반: 주요 상품/서비스 5~7개 + 가격

④ 프로모션 배너 (promotion)
   - 전단지 중간의 컬러 띠에 들어갈 프로모션 문구 1줄
   - 30자 이내. 긴급성+혜택을 동시에
   - 현실적인 할인. "50% 할인" 같은 비현실적 수치 금지
   - 좋은 예: "3월 한정! 첫 방문 고객 20% 할인"
   - 좋은 예: "친구 동반 시 음료 서비스"

⑤ 영업시간 (hours)
   - 이 업종의 실제 표준 영업시간 추천
   - 형식: "매일 11:00~22:00 (연중무휴)" 또는 "평일 10:00~21:00 / 토 10:00~18:00"

⑥ 부제목/소개 문구 (subtitle)
   - 메뉴 목록 위에 한 줄로 들어갈 가게 소개
   - 20자 이내. 가게의 성격을 한마디로
   - 예: "동네 주민이 사랑하는 분식집", "강남역 3번 출구 도보 1분"

■ 작성 규칙 (반드시 준수)
- 모든 텍스트는 한국어만 사용
- 상호명 "{$businessName}"의 느낌/뉘앙스를 살린 카피 작성
- 과장 없이 신뢰감 있게. 실제 동네 가게 전단지 느낌
- 가격은 현실적으로 (너무 싸거나 비싸면 안 됨)
- 전단지 인쇄용이므로 이모지/특수문자 사용 금지

■ 응답 형식 (반드시 아래 JSON만 출력)
{"tagline":"...","subtitle":"...","features":["...","...","..."],"menu":[{"name":"...","price":"..."},{"name":"...","price":"..."}],"promotion":"...","hours":"..."}

menu 배열의 항목 수: 7~10개를 목표로 하되, 업종에 맞게 조절.
PROMPT;
    }
    
    /**
     * 업종별 메뉴 가이드 반환
     */
    private function getMenuGuide(string $industryKey): string
    {
        $guides = [
            'restaurant_korean' => '한식 대표 메뉴 7~10개 + 가격 (찌개 9,000~11,000원, 정식 11,000~15,000원, 반찬류 포함 구성)',
            'restaurant_japanese' => '일식 메뉴 7~10개 + 가격 (초밥세트 15,000~25,000원, 라멘 10,000~13,000원, 돈카츠 12,000~16,000원)',
            'restaurant_chinese' => '중식 메뉴 7~10개 + 가격 (짜장면 7,000~8,000원, 짬뽕 8,000~10,000원, 탕수육 소 18,000~22,000원)',
            'restaurant_western' => '양식/카페 메뉴 7~10개 + 가격 (파스타 14,000~18,000원, 스테이크 25,000~45,000원, 커피 4,500~6,000원)',
            'restaurant_chicken' => '치킨/호프 메뉴 7~10개 + 가격 (후라이드 18,000~20,000원, 양념 19,000~21,000원, 맥주 4,000~5,000원)',
            'academy_english' => '영어 프로그램 5~7개 + 월 수강료 (초등 15~25만원, 중등 20~35만원, 성인 20~40만원)',
            'academy_math' => '수학 프로그램 5~7개 + 월 수강료 (초등 15~25만원, 중등 25~40만원, 고등 30~50만원)',
            'academy_general' => '교습 과목 5~7개 + 월 수강료 (종합반 25~40만원)',
            'fitness_gym' => '헬스/PT 프로그램 5~7개 + 가격 (월 회원권 5~8만원, PT 1회 50,000~80,000원, 3개월권 할인가)',
            'fitness_golf' => '골프 프로그램 5~7개 + 가격 (타석 이용 1시간 15,000~25,000원, 레슨 월 20~40만원)',
            'fitness_yoga' => '요가/필라테스 프로그램 5~7개 + 가격 (월 회원권 10~18만원, 개인레슨 1회 50,000~80,000원)',
            'beauty_hair' => '헤어 시술 7~10개 + 가격 (커트 15,000~25,000원, 펌 80,000~150,000원, 염색 60,000~120,000원)',
            'beauty_skin' => '피부 관리 프로그램 5~7개 + 가격 (기본관리 50,000~80,000원, 스페셜 100,000~150,000원)',
            'beauty_massage' => '마사지/스파 코스 5~7개 + 가격 (전신 60분 60,000~90,000원, 스페셜 120분 100,000~150,000원)',
            'general_store' => '주요 상품/서비스 5~7개 + 현실적인 가격',
        ];
        
        return $guides[$industryKey] ?? '주요 상품/서비스 5~7개 + 현실적인 가격 (해당 업종 시세 반영)';
    }
    
    /**
     * 업종별 톤 가이드 반환
     */
    private function getToneGuide(string $industryKey): string
    {
        $tones = [
            'restaurant_korean' => '따뜻하고 정감 있는 톤. 어머니 손맛, 시골밥상 같은 편안함',
            'restaurant_japanese' => '깔끔하고 절제된 톤. 장인정신, 신선함 강조',
            'restaurant_chinese' => '활기차고 풍성한 톤. 불맛, 양 많음, 가성비 어필',
            'restaurant_western' => '세련되고 감성적인 톤. 분위기, 특별한 날, 데이트',
            'restaurant_chicken' => '친근하고 활기찬 톤. 바삭함, 시원한 맥주, 모임',
            'academy_english' => '신뢰감 있고 전문적인 톤. 성과, 실력 향상 강조',
            'academy_math' => '논리적이고 확신 있는 톤. 성적 향상, 체계적 관리',
            'academy_general' => '안정감 있고 체계적인 톤. 종합 관리, 학부모 신뢰',
            'fitness_gym' => '에너지 넘치고 동기부여하는 톤. 변화, 도전, 건강',
            'fitness_golf' => '여유 있고 고급스러운 톤. 취미, 실력 향상, 라운딩 준비',
            'fitness_yoga' => '차분하고 힐링하는 톤. 균형, 건강, 마음 챙김',
            'beauty_hair' => '트렌디하고 감각적인 톤. 스타일 변화, 자신감',
            'beauty_skin' => '부드럽고 전문적인 톤. 피부 고민 해결, 관리',
            'beauty_massage' => '편안하고 릴렉싱한 톤. 휴식, 힐링, 재충전',
            'general_store' => '친근하고 실용적인 톤',
        ];
        
        return $tones[$industryKey] ?? '친근하고 신뢰감 있는 톤';
    }
    
    // ════════════════════════════════════════════════════════════
    //  이미지 생성
    // ════════════════════════════════════════════════════════════
    
    /**
     * 전단지용 이미지 생성
     * 
     * @param string $industryKey   업종 키
     * @param string $industryLabel 업종 한글명
     * @param string $businessName  상호명
     * @param string $imageType     이미지 유형 (hero, background, logo_mark)
     * @return array ['success' => true, 'path' => '...', 'url' => '...'] or ['error' => '...']
     */
    public function generateImage(
        string $industryKey,
        string $industryLabel,
        string $businessName,
        string $imageType = 'hero'
    ): array {
        if (!$this->isConfigured()) {
            return ['error' => 'API 키가 설정되지 않았습니다.'];
        }
        
        $prompt = $this->buildImagePrompt($industryKey, $industryLabel, $businessName, $imageType);
        
        $data = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'responseModalities' => ['IMAGE'],
            ]
        ];
        
        $result = $this->callAPI($this->imageModel, $data, 120);
        
        if (isset($result['error'])) {
            return $result;
        }
        
        // 이미지 데이터 추출
        $parts = $result['candidates'][0]['content']['parts'] ?? [];
        
        foreach ($parts as $part) {
            if (isset($part['inlineData'])) {
                $mimeType = $part['inlineData']['mimeType'] ?? 'image/jpeg';
                $base64Data = $part['inlineData']['data'] ?? '';
                
                if (empty($base64Data)) continue;
                
                $imageData = base64_decode($base64Data);
                if ($imageData === false) continue;
                
                // 파일 저장
                $ext = ($mimeType === 'image/png') ? 'png' : 'jpg';
                $filename = 'ai_' . $imageType . '_' . uniqid() . '.' . $ext;
                $filePath = $this->uploadDir . $filename;
                
                if (file_put_contents($filePath, $imageData) === false) {
                    return ['error' => '이미지 저장에 실패했습니다.'];
                }
                
                return [
                    'success' => true,
                    'path' => $filePath,
                    'filename' => $filename,
                    'url' => 'uploads/ai/' . $filename,
                    'mime_type' => $mimeType,
                    'size' => strlen($imageData),
                ];
            }
        }
        
        return ['error' => 'AI가 이미지를 생성하지 못했습니다. 다시 시도해주세요.'];
    }
    
    /**
     * 이미지 프롬프트 생성
     */
    private function buildImagePrompt(
        string $industryKey,
        string $industryLabel,
        string $businessName,
        string $imageType
    ): string {
        $sceneDesc = $this->getSceneDescription($industryKey, $industryLabel);
        
        if ($imageType === 'hero') {
            return <<<PROMPT
Create a high-quality promotional photograph for a Korean {$industryLabel} called "{$businessName}".

Scene: {$sceneDesc}

Requirements:
- Professional commercial photography style
- Warm, inviting atmosphere with natural lighting
- Vibrant colors that make the viewer want to visit
- Clean composition suitable for an A4 flyer
- Landscape orientation (wider than tall)
- NO text, NO letters, NO words, NO watermarks in the image
- NO logos or brand names
- Photorealistic quality
PROMPT;
        }
        
        // background type
        return <<<PROMPT
Create a subtle, elegant background pattern for a Korean {$industryLabel} promotional flyer.

Style: Soft, muted colors with gentle abstract shapes related to {$sceneDesc}.
Requirements:
- Soft pastel or neutral tones
- Subtle pattern that won't interfere with text overlay
- Clean and professional
- NO text, NO letters, NO watermarks
- Suitable as background for A4 printed flyer
PROMPT;
    }
    
    /**
     * 업종별 이미지 장면 설명
     */
    private function getSceneDescription(string $industryKey, string $industryLabel): string
    {
        $scenes = [
            'restaurant_korean' => 'A beautifully arranged spread of Korean dishes on a wooden table - steaming jjigae, colorful banchan side dishes, and rice. Warm restaurant interior with traditional Korean decor.',
            'restaurant_japanese' => 'Elegant Japanese cuisine presentation - fresh sushi and sashimi on ceramic plates, with wooden counter and subtle Japanese interior design.',
            'restaurant_chinese' => 'Vibrant Chinese dishes on a round table - spicy mapo tofu, sweet and sour pork, noodles with steam rising. Rich red and gold restaurant atmosphere.',
            'restaurant_western' => 'Sophisticated Western dining setup - perfectly plated steak or pasta with wine glass, modern restaurant ambiance with ambient lighting.',
            'restaurant_chicken' => 'Crispy golden Korean fried chicken on a plate with beer glasses, casual pub atmosphere with warm lighting.',
            'academy_english' => 'Bright, modern study environment with English learning materials, whiteboards, and positive classroom atmosphere.',
            'academy_math' => 'Clean, well-organized classroom with math materials, equations on whiteboard, focused learning environment.',
            'academy_general' => 'Modern academy study room with diverse educational materials, desks arranged for small groups.',
            'fitness_gym' => 'Modern gym interior with professional equipment, energetic atmosphere. Clean, well-lit fitness space.',
            'fitness_golf' => 'Indoor golf practice facility with simulator screens, putting green. Modern, upscale sports environment.',
            'fitness_yoga' => 'Serene yoga studio with mats, natural light, plants, and calming minimalist decor.',
            'beauty_hair' => 'Stylish hair salon interior with modern chairs, professional tools, and elegant mirrors.',
            'beauty_skin' => 'Luxurious spa treatment room with calming atmosphere, clean white towels, and professional products.',
            'beauty_massage' => 'Peaceful spa environment with candles, aromatherapy oils, warm towels, and relaxation space.',
            'general_store' => 'Well-organized retail store with attractive product displays, clean shelves, and welcoming entrance.',
        ];
        
        return $scenes[$industryKey] ?? "A professional, inviting scene related to {$industryLabel} business in Korea.";
    }
    
    // ════════════════════════════════════════════════════════════
    //  공통 API 호출
    // ════════════════════════════════════════════════════════════
    
    /**
     * Gemini API 호출 (공통)
     */
    private function callAPI(string $model, array $data, int $timeout = 120): array
    {
        $url = $this->baseUrl . $model . ':generateContent?key=' . $this->apiKey;
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => $timeout,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            return ['error' => '네트워크 오류: ' . $curlError];
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? 'API 오류 (HTTP ' . $httpCode . ')';
            return ['error' => $errorMsg];
        }
        
        $result = json_decode($response, true);
        if (!$result) {
            return ['error' => 'API 응답 파싱 실패'];
        }
        
        if (isset($result['error'])) {
            return ['error' => $result['error']['message'] ?? 'API 오류'];
        }
        
        return $result;
    }
    
    /**
     * 오래된 AI 생성 이미지 정리 (24시간 이상)
     */
    public function cleanupOldImages(int $maxAgeSeconds = 86400): int
    {
        $count = 0;
        $files = glob($this->uploadDir . 'ai_*');
        if (!$files) return 0;
        
        $cutoff = time() - $maxAgeSeconds;
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                @unlink($file);
                $count++;
            }
        }
        
        return $count;
    }
}
