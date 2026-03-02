<?php
/**
 * 전단지 AI 텍스트 생성 API
 * 
 * POST: industry_key, industry_label, business_name
 * Returns: JSON { success: true, data: { tagline, features, menu, promotion, hours } }
 * 
 * @since 2026-03-02
 */

header('Content-Type: application/json; charset=utf-8');

// POST만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST 요청만 허용됩니다.']);
    exit;
}

// Rate Limiter
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/ai_rate_limiter.php';
$limiter = new AIRateLimiter();
$rateCheck = $limiter->checkAndIncrement();

if (!$rateCheck['allowed']) {
    http_response_code(429);
    echo json_encode([
        'error' => getAIRateLimitMessage($rateCheck['reason']),
        'reason' => $rateCheck['reason'],
    ]);
    exit;
}

// 입력 검증
$industryKey = trim($_POST['industry_key'] ?? '');
$industryLabel = trim($_POST['industry_label'] ?? '');
$businessName = trim($_POST['business_name'] ?? '');

if (empty($industryKey) || empty($businessName)) {
    http_response_code(400);
    echo json_encode(['error' => '업종과 상호명을 입력해주세요.']);
    exit;
}

if (empty($industryLabel)) {
    $industryLabel = $industryKey; // fallback
}

// AI 서비스 호출
require_once __DIR__ . '/../includes/FlyerAIService.php';
$service = new FlyerAIService();

if (!$service->isConfigured()) {
    http_response_code(500);
    echo json_encode(['error' => 'AI 서비스가 설정되지 않았습니다. 관리자에게 문의하세요.']);
    exit;
}

$result = $service->generateContent($industryKey, $industryLabel, $businessName);

if (isset($result['error'])) {
    http_response_code(500);
    echo json_encode($result);
    exit;
}

echo json_encode($result);
