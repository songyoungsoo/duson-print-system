<?php
/**
 * 프리미엄 전단지 생성 SSE 엔드포인트
 * 
 * POST: JSON body with form data
 * Returns: Server-Sent Events stream with progress updates
 * 
 * Flow:
 *   1. Validate input
 *   2. Create FlyerPremiumService
 *   3. Generate with progress callback → SSE events
 *   4. Send final result (pdf_url + preview_images)
 * 
 * @since 2026-03-02
 */

// ===================================================================
// SSE headers — must be sent before any output
// ===================================================================
header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable nginx buffering (Plesk)

// Disable output buffering completely
while (ob_get_level() > 0) {
    ob_end_flush();
}

// Long-running: allow up to 5 minutes
set_time_limit(300);
ini_set('max_execution_time', '300');

// Start session for uploads path consistency
session_start();

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendSSEError('POST 요청만 허용됩니다.');
    exit;
}

// ===================================================================
// Rate Limiter
// ===================================================================
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/ai_rate_limiter.php';
$limiter = new AIRateLimiter();
$rateCheck = $limiter->checkAndIncrement();

if (!$rateCheck['allowed']) {
    sendSSEError(getAIRateLimitMessage($rateCheck['reason']));
    exit;
}

// ===================================================================
// Parse JSON body
// ===================================================================
$rawInput = file_get_contents('php://input');
$formData = json_decode($rawInput, true);

if (!is_array($formData)) {
    sendSSEError('잘못된 요청 형식입니다.');
    exit;
}

// ===================================================================
// Validate required fields
// ===================================================================
$businessName = trim($formData['business_name'] ?? '');
$phone        = trim($formData['phone'] ?? '');
$address      = trim($formData['address'] ?? '');
$industryKey  = trim($formData['industry_key'] ?? '');

$errors = [];
if ($businessName === '') $errors[] = '상호명';
if ($phone === '')        $errors[] = '전화번호';
if ($address === '')      $errors[] = '주소';
if ($industryKey === '')  $errors[] = '업종';

if (!empty($errors)) {
    sendSSEError('필수 항목을 입력해주세요: ' . implode(', ', $errors));
    exit;
}

// Features: need at least 1
$features = array_filter(array_map('trim', $formData['features'] ?? []));
if (empty($features)) {
    sendSSEError('특장점을 최소 1개 입력해주세요.');
    exit;
}

// ===================================================================
// Load FlyerPremiumService
// ===================================================================
$premiumServicePath = __DIR__ . '/../includes/FlyerPremiumService.php';
if (!file_exists($premiumServicePath)) {
    sendSSEError('프리미엄 서비스 모듈이 아직 준비되지 않았습니다.');
    exit;
}

require_once $premiumServicePath;

// Load API key (same pattern as FlyerAIService)
$apiKey = loadGeminiApiKey();
if (empty($apiKey)) {
    sendSSEError('AI 서비스가 설정되지 않았습니다. 관리자에게 문의하세요.');
    exit;
}

// ===================================================================
// Send initial progress
// ===================================================================
sendSSEProgress('init', '프리미엄 전단지 생성을 시작합니다...', 0);

// ===================================================================
// Create service and generate

// ===================================================================
// Normalize field names for FlyerPremiumService compatibility
// JS sends industry_key, features[], menu_items[{name,price}]
// Service expects industry (string), features (string), menu_items (string)
// ===================================================================
$formData['industry'] = $formData['industry_key'] ?? 'general';

// Convert features array to newline-separated string
if (is_array($formData['features'] ?? null)) {
    $formData['features'] = implode("\n", array_filter(array_map('trim', $formData['features'])));
}

// Convert menu_items array of objects to "name - price" string
if (is_array($formData['menu_items'] ?? null)) {
    $menuStrings = [];
    foreach ($formData['menu_items'] as $item) {
        if (is_array($item) && !empty($item['name'])) {
            $menuStrings[] = $item['name'] . (isset($item['price']) && $item['price'] !== '' ? ' - ' . $item['price'] : '');
        } elseif (is_string($item)) {
            $menuStrings[] = $item;
        }
    }
    $formData['menu_items'] = implode("\n", $menuStrings);
}

// ===================================================================
try {
    $service = new FlyerPremiumService($apiKey);

    $result = $service->generate($formData, function ($stage, $message, $progress) {
        sendSSEProgress($stage, $message, $progress);
    });

    // Send completion
    if (isset($result['error'])) {
        sendSSEError($result['error']);
    } else {
        echo "data: " . json_encode([
            'type'           => 'complete',
            'success'        => true,
            'pdf_url'        => $result['pdf_url'] ?? '',
            'preview_images' => $result['preview_images'] ?? [],
        ], JSON_UNESCAPED_UNICODE) . "\n\n";
        flush();
    }

} catch (\Throwable $e) {
    sendSSEError('생성 중 오류가 발생했습니다: ' . $e->getMessage());
}

exit;


// ===================================================================
// Helper Functions
// ===================================================================

/**
 * Send an SSE progress event.
 */
function sendSSEProgress(string $stage, string $message, int $progress): void
{
    echo "data: " . json_encode([
        'type'     => 'progress',
        'stage'    => $stage,
        'message'  => $message,
        'progress' => min(100, max(0, $progress)),
    ], JSON_UNESCAPED_UNICODE) . "\n\n";

    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();
}

/**
 * Send an SSE error event and terminate.
 */
function sendSSEError(string $message): void
{
    echo "data: " . json_encode([
        'type'    => 'error',
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE) . "\n\n";

    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();
}

/**
 * Load GEMINI_API_KEY from environment or .env file.
 * Same logic as FlyerAIService::loadApiKey().
 */
function loadGeminiApiKey(): string
{
    // 1) PHP environment variable
    $key = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '';
    if ($key) return $key;

    // 2) Parse .env file directly
    $envFile = $_SERVER['DOCUMENT_ROOT'] . '/.env';
    if (!$envFile || !file_exists($envFile)) {
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
