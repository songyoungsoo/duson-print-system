<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Core\CSRF;
use App\Services\AI\ChatbotService;

class ChatbotController
{
    private ChatbotService $chatbot;
    
    public function __construct()
    {
        $this->chatbot = new ChatbotService();
    }
    
    public function index(Request $request, array $params): Response
    {
        $content = View::render('pages.chatbot.index', [
            'title' => '가격상담 챗봇',
            'isConfigured' => $this->chatbot->isConfigured(),
        ]);
        
        $html = View::layout('main', $content, [
            'title' => '가격상담 챗봇 - 두손기획인쇄',
        ]);
        
        return Response::html($html);
    }
    
    public function chat(Request $request, array $params): Response
    {
        $token = $request->post('csrf_token') ?? $request->post('_token');
        if (!CSRF::verify($token)) {
            return Response::json(['error' => 'CSRF 토큰이 유효하지 않습니다.'], 403);
        }
        
        $message = trim($request->post('message', ''));
        
        if (empty($message)) {
            return Response::json(['error' => '메시지를 입력해주세요.'], 400);
        }
        
        $historyJson = $request->post('history', '[]');
        $history = json_decode($historyJson, true) ?? [];
        
        $result = $this->chatbot->chat($message, $history);
        
        if (isset($result['error'])) {
            return Response::json(['error' => $result['error']], 500);
        }
        
        // TTS 요청이면 안내 문구만 추출해서 음성 생성 (한 번의 응답으로 처리)
        $wantTts = $request->post('tts', '');
        if ($wantTts === '1' && !empty($result['message'])) {
            $guideText = $this->extractGuideText($result['message']);
            if (!empty($guideText)) {
                $ttsResult = $this->chatbot->textToSpeech($guideText);
                if (!empty($ttsResult['success'])) {
                    $result['audio'] = $ttsResult['audio'];
                    $result['audioMime'] = $ttsResult['mimeType'] ?? 'audio/wav';
                }
            }
        }
        
        return Response::json($result);
    }
    
    /**
     * 봇 응답에서 안내 문구만 추출 (선택지 목록 제외)
     */
    private function extractGuideText(string $message): string
    {
        $lines = explode("\n", $message);
        $guide = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            // 번호 목록 건너뜀
            if (preg_match('/^\d+[\.\)]\s/', $trimmed)) continue;
            if (empty($trimmed)) continue;
            // 이모지 제거
            $clean = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}]/u', '', $trimmed);
            $clean = trim($clean);
            if (!empty($clean)) $guide[] = $clean;
        }
        return implode('. ', $guide);
    }
    
    /**
     * POST /chatbot/tts - Gemini TTS 음성 생성
     */
    public function tts(Request $request, array $params): Response
    {
        $token = $request->post('csrf_token') ?? $request->post('_token');
        if (!CSRF::verify($token)) {
            return Response::json(['error' => 'CSRF 토큰이 유효하지 않습니다.'], 403);
        }
        
        $text = trim($request->post('text', ''));
        if (empty($text)) {
            return Response::json(['error' => '텍스트를 입력해주세요.'], 400);
        }
        
        // 텍스트 길이 제한 (비용 절약)
        if (mb_strlen($text) > 500) {
            $text = mb_substr($text, 0, 500);
        }
        
        $result = $this->chatbot->textToSpeech($text);
        
        return Response::json($result);
    }
}
