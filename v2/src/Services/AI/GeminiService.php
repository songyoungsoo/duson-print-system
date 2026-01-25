<?php
declare(strict_types=1);

namespace App\Services\AI;

class GeminiService
{
    private string $apiKey;
    private string $model = 'gemini-2.0-flash';
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    
    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '';
    }
    
    public function generateCopy(array $input): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'API 키가 설정되지 않았습니다. .env 파일에 GEMINI_API_KEY를 설정해주세요.'];
        }
        
        $prompt = $this->buildPrompt($input);
        $response = $this->callApi($prompt);
        
        if (isset($response['error'])) {
            return $response;
        }
        
        return $this->parseResponse($response);
    }
    
    private function buildPrompt(array $input): string
    {
        $product = $input['product'] ?? '';
        $target = $input['target'] ?? '';
        $keywords = $input['keywords'] ?? '';
        $tone = $input['tone'] ?? '전문적';
        $category = $input['category'] ?? '일반';
        
        $toneMap = [
            '친근함' => '친근하고 편안한 말투로, 고객과 대화하듯이',
            '유머러스' => '재치있고 유머러스하게, 미소를 짓게 만드는',
            '감성적' => '감성적이고 따뜻한 느낌으로, 마음을 울리는',
            '고급스러움' => '세련되고 고급스러운 톤으로, 프리미엄 이미지를 전달하는',
        ];
        $toneGuide = $toneMap[$tone] ?? '전문적이고 신뢰감 있는 톤으로';
        
        return <<<PROMPT
당신은 대한민국 최고의 카피라이터입니다.
다음 정보를 바탕으로 헤드카피(메인 타이틀)와 서브카피(부제목)를 생성해주세요.

[입력 정보]
- 제품/서비스: {$product}
- 타겟 고객: {$target}
- 핵심 키워드: {$keywords}
- 업종: {$category}
- 톤앤매너: {$toneGuide}

[요청사항]
1. 헤드카피 5개를 생성하세요
2. 각 헤드카피에 어울리는 서브카피 1개씩 생성하세요
3. 헤드카피는 짧고 임팩트 있게 (10자 내외)
4. 서브카피는 헤드카피를 보완하는 설명 (20-30자)
5. 한국어로 작성하세요

[출력 형식]
반드시 아래 JSON 형식으로만 응답하세요. 다른 설명 없이 JSON만 출력하세요:
{"copies":[{"head":"헤드카피1","sub":"서브카피1"},{"head":"헤드카피2","sub":"서브카피2"},{"head":"헤드카피3","sub":"서브카피3"},{"head":"헤드카피4","sub":"서브카피4"},{"head":"헤드카피5","sub":"서브카피5"}]}
PROMPT;
    }
    
    private function callApi(string $prompt): array
    {
        $url = $this->baseUrl . $this->model . ':generateContent?key=' . $this->apiKey;
        
        $data = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature' => 0.9,
                'maxOutputTokens' => 1024,
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 60
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
        
        return json_decode($response, true) ?? ['error' => '응답 파싱 실패'];
    }
    
    private function parseResponse(array $response): array
    {
        $content = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        $content = trim($content);
        $content = preg_replace('/^```json\s*/', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);
        
        if (preg_match('/\{[\s\S]*"copies"[\s\S]*\}/m', $content, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json && isset($json['copies'])) {
                return ['success' => true, 'copies' => $json['copies']];
            }
        }
        
        return ['error' => '응답 형식 파싱 실패', 'raw' => $content];
    }
    
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}
