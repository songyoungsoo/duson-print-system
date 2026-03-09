<?php
/**
 * status.php — Progress Polling Endpoint for Poster Factory
 * 
 * GET /poster/status.php?job_id=xxx
 * Returns JSON with progress steps and overall status.
 */

header('Content-Type: application/json; charset=utf-8');

// Only accept GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'error' => 'GET 요청만 허용됩니다.']);
    exit;
}

$jobId = trim($_GET['job_id'] ?? '');

if ($jobId === '') {
    echo json_encode(['success' => false, 'error' => 'job_id가 필요합니다.']);
    exit;
}

// Sanitize job_id to prevent directory traversal
$jobId = basename($jobId);

$outputBase = realpath(__DIR__ . '/../_poster_factory/output');
if (!$outputBase) {
    echo json_encode(['success' => false, 'error' => '출력 디렉토리를 찾을 수 없습니다.']);
    exit;
}

$jobDir = $outputBase . '/' . $jobId;

if (!is_dir($jobDir)) {
    echo json_encode(['success' => false, 'error' => '작업을 찾을 수 없습니다: ' . $jobId]);
    exit;
}

// ── Check file existence for progress steps ──

$steps = [];
$completedSteps = 0;
$totalSteps = 5;
$currentMessage = '준비 중...';
$hasError = false;
$errorMessage = '';

// Step 1: copy.json
$copyExists = file_exists($jobDir . '/copy.json');
if ($copyExists) {
    $steps[] = ['name' => '카피 생성', 'status' => 'done'];
    $completedSteps++;
} else {
    // Check if process has started
    $logExists = file_exists($jobDir . '/process.log');
    if ($logExists) {
        $steps[] = ['name' => '카피 생성', 'status' => 'running'];
        $currentMessage = '카피 생성 중...';
    } else {
        $steps[] = ['name' => '카피 생성', 'status' => 'pending'];
        $currentMessage = '프로세스 시작 대기 중...';
    }
}

// Step 2: design.json
$designExists = file_exists($jobDir . '/design.json');
if ($designExists) {
    $steps[] = ['name' => '디자인 생성', 'status' => 'done'];
    $completedSteps++;
} else {
    if ($copyExists) {
        $steps[] = ['name' => '디자인 생성', 'status' => 'running'];
        $currentMessage = '디자인 생성 중...';
    } else {
        $steps[] = ['name' => '디자인 생성', 'status' => 'pending'];
    }
}

// Step 3: layout_spec.json
$layoutExists = file_exists($jobDir . '/layout_spec.json');
if ($layoutExists) {
    $steps[] = ['name' => '아트디렉팅', 'status' => 'done'];
    $completedSteps++;
} else {
    if ($designExists) {
        $steps[] = ['name' => '아트디렉팅', 'status' => 'running'];
        $currentMessage = '아트디렉팅 진행 중...';
    } else {
        $steps[] = ['name' => '아트디렉팅', 'status' => 'pending'];
    }
}

// Step 4: images — check hero + items
$heroExists = file_exists($jobDir . '/images/hero.png');
$itemImages = glob($jobDir . '/images/item_*.png');
$itemCount  = is_array($itemImages) ? count($itemImages) : 0;

// Determine expected item count from brief.json
$expectedItems = 0;
$briefPath = $jobDir . '/brief.json';
if (file_exists($briefPath)) {
    $briefData = json_decode(file_get_contents($briefPath), true);
    if ($briefData && isset($briefData['items'])) {
        $expectedItems = count($briefData['items']);
    }
}

$totalExpected = $expectedItems + 1; // hero + items
$totalGenerated = ($heroExists ? 1 : 0) + $itemCount;
$allImagesReady = $heroExists && ($itemCount >= $expectedItems) && ($expectedItems > 0);

if ($allImagesReady) {
    $steps[] = ['name' => '이미지 생성', 'status' => 'done', 'detail' => $totalGenerated . '/' . $totalExpected];
    $completedSteps++;
} else {
    if ($layoutExists) {
        $detail = $totalGenerated . '/' . $totalExpected;
        $steps[] = ['name' => '이미지 생성', 'status' => 'running', 'detail' => $detail];
        $currentMessage = '이미지 생성 중... (' . $detail . ')';
    } else {
        $steps[] = ['name' => '이미지 생성', 'status' => 'pending', 'detail' => '0/' . $totalExpected];
    }
}

// Step 5: poster.svg
$svgExists = file_exists($jobDir . '/poster.svg');
if ($svgExists) {
    $steps[] = ['name' => 'SVG 조립', 'status' => 'done'];
    $completedSteps++;
} else {
    if ($allImagesReady) {
        $steps[] = ['name' => 'SVG 조립', 'status' => 'running'];
        $currentMessage = 'SVG 포스터 조립 중...';
    } else {
        $steps[] = ['name' => 'SVG 조립', 'status' => 'pending'];
    }
}

// ── Check for errors in process.log ──
$logPath = $jobDir . '/process.log';
if (file_exists($logPath)) {
    $logContent = file_get_contents($logPath);
    
    // Check for error indicators
    if (preg_match('/\bERROR\b|실패|Traceback|Exception|Fatal/i', $logContent)) {
        // Only flag as error if process seems to have stopped (no new progress for a while)
        // Check if SVG was not created and no images are being generated
        if (!$svgExists && $completedSteps < $totalSteps) {
            // Check if process is still running
            $logAge = time() - filemtime($logPath);
            if ($logAge > 30) { // No log update for 30+ seconds and error in log
                $hasError = true;
                // Extract last error line
                $lines = explode("\n", trim($logContent));
                for ($i = count($lines) - 1; $i >= 0; $i--) {
                    if (preg_match('/ERROR|실패|Exception/i', $lines[$i])) {
                        $errorMessage = trim($lines[$i]);
                        break;
                    }
                }
                if ($errorMessage === '') {
                    $errorMessage = '포스터 생성 중 오류가 발생했습니다.';
                }
            }
        }
    }
}

// ── Calculate progress ──
// Weighted progress: copy(10) + design(10) + artdirect(15) + images(50) + svg(15) = 100
$progress = 0;
if ($copyExists)   $progress += 10;
if ($designExists) $progress += 10;
if ($layoutExists) $progress += 15;

// Images: partial progress based on generated count
if ($totalExpected > 0) {
    $imgProgress = ($totalGenerated / $totalExpected) * 50;
    $progress += (int) $imgProgress;
}

if ($svgExists) $progress += 15;

// Clamp
$progress = min(100, max(0, $progress));

// ── Determine overall status ──
$status = 'running';
if ($svgExists) {
    $status = 'completed';
    $progress = 100;
    $currentMessage = '포스터 생성 완료!';
} elseif ($hasError) {
    $status = 'error';
    $currentMessage = $errorMessage;
}

// ── Elapsed time ──
$elapsed = 0;
$briefCreated = file_exists($briefPath) ? filemtime($briefPath) : time();
$elapsed = time() - $briefCreated;

// ── Output ──
echo json_encode([
    'status'   => $status,
    'progress' => $progress,
    'steps'    => $steps,
    'elapsed'  => $elapsed,
    'message'  => $currentMessage,
], JSON_UNESCAPED_UNICODE);
