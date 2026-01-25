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
        
        return Response::json($result);
    }
}
