<?php
/**
 * POST /api/ai_chat.php — AI 챗봇 독립 API
 * 
 * v2/src/Services/AI/ChatbotService.php를 직접 로드하여
 * composer autoloader 없이 동작.
 * 
 * action=chat (message, history) | action=reset
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST only']);
    exit;
}

// Same-origin check via Referer
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';
if (!empty($referer) && strpos($referer, $host) === false) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

session_start();

// V2_ROOT 상수 정의 (ChatbotService 내부에서 products.php 로드에 사용)
$v2Root = dirname(__DIR__) . '/v2';
if (!defined('V2_ROOT')) {
    define('V2_ROOT', $v2Root);
}

// .env 파일 로드 (Gemini API 키 — 없어도 DB 기반 가격 조회는 동작)
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
        putenv(trim($key) . '=' . trim($value));
    }
}

// ChatbotService 직접 로드 (composer autoloader 불필요)
require_once $v2Root . '/src/Services/AI/ChatbotService.php';

use App\Services\AI\ChatbotService;

try {
    $chatbot = new ChatbotService();
} catch (\Throwable $e) {
    error_log('AI Chat API init error: ' . $e->getMessage());
    echo json_encode(['error' => '챗봇 초기화 실패: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// isConfigured()는 API키 OR DB 연결 중 하나라도 있으면 true
if (!$chatbot->isConfigured()) {
    echo json_encode(['error' => 'DB 또는 API 키가 설정되지 않았습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_POST['action'] ?? 'chat';

switch ($action) {
    case 'chat':
        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            echo json_encode(['error' => '메시지를 입력해주세요.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $historyJson = $_POST['history'] ?? '[]';
        $history = json_decode($historyJson, true) ?? [];
        
        try {
            $result = $chatbot->chat($message, $history);
        } catch (\Throwable $e) {
            error_log('AI Chat error: ' . $e->getMessage());
            $result = ['success' => true, 'message' => "죄송합니다. 오류가 발생했습니다.\n전화(02-2632-1830)로 문의해주세요."];
        }
        
        if (isset($result['error'])) {
            echo json_encode(['error' => $result['error']], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
        
    case 'reset':
        if (isset($_SESSION['chatbot'])) {
            unset($_SESSION['chatbot']);
        }
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['error' => 'Unknown action']);
}
