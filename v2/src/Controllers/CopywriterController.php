<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Core\CSRF;
use App\Services\AI\GeminiService;

class CopywriterController
{
    private GeminiService $ai;
    
    public function __construct()
    {
        $this->ai = new GeminiService();
    }
    
    public function index(Request $request, array $params): Response
    {
        $content = View::render('pages.copywriter.index', [
            'title' => '카피라이터 - AI 카피 생성기',
            'isConfigured' => $this->ai->isConfigured(),
        ]);
        
        $html = View::layout('main', $content, [
            'title' => '카피라이터 - AI 카피 생성기',
        ]);
        
        return Response::html($html);
    }
    
    public function generate(Request $request, array $params): Response
    {
        $token = $request->post('csrf_token') ?? $request->post('_token');
        if (!CSRF::verify($token)) {
            return Response::json(['error' => 'CSRF 토큰이 유효하지 않습니다.'], 403);
        }
        
        $input = [
            'product' => trim($request->post('product', '')),
            'target' => trim($request->post('target', '')),
            'keywords' => trim($request->post('keywords', '')),
            'tone' => $request->post('tone', '전문적'),
            'category' => $request->post('category', '일반'),
        ];
        
        if (empty($input['product'])) {
            return Response::json(['error' => '제품/서비스명을 입력해주세요.'], 400);
        }
        
        $result = $this->ai->generateCopy($input);
        
        if (isset($result['error'])) {
            return Response::json(['error' => $result['error']], 500);
        }
        
        return Response::json($result);
    }
}
