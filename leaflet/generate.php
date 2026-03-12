<?php
/**
 * generate.php — V2 Leaflet Factory AJAX POST Handler
 * 
 * 폼 데이터를 받고, 이미지를 업로드 폴더에 저장한 뒤
 * Python 오케스트레이터 에이전트를 백그라운드에서 실행합니다.
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST 요청만 허용됩니다.']);
    exit;
}

// ── 1. 입력 데이터 수집 ──
$businessName = trim($_POST['business_name'] ?? '');
$category     = trim($_POST['category'] ?? '');
$features     = trim($_POST['features'] ?? '');
$phone        = trim($_POST['phone'] ?? '');
$address      = trim($_POST['address'] ?? '');
$hours        = trim($_POST['hours'] ?? '');
$imageUsage   = trim($_POST['image_usage'] ?? 'ai_generate');
$purpose      = trim($_POST['purpose'] ?? '홍보/알림');
$targetAud    = trim($_POST['target_audience'] ?? '일반 고객');
$layoutStyle  = trim($_POST['layout_style'] ?? 'auto');

if ($businessName === '' || $category === '') {
    echo json_encode(['success' => false, 'error' => '가게명과 업종은 필수입니다.']);
    exit;
}

// 메뉴 데이터 수집
$items = [];
$itemNames = $_POST['item_name'] ?? [];
$itemDescs = $_POST['item_desc'] ?? [];
$itemPrices = $_POST['item_price'] ?? [];

for ($i = 0; $i < count($itemNames); $i++) {
    $name = trim($itemNames[$i]);
    if ($name !== '') {
        $items[] = [
            'name' => $name,
            'description' => trim($itemDescs[$i] ?? ''),
            'price' => trim($itemPrices[$i] ?? '')
        ];
    }
}

// ── 2. 작업 디렉토리 생성 (고유 ID) ──
$safeName = preg_replace('/[^a-zA-Z0-9가-힣_-]/u', '', $businessName);
$safeName = mb_substr($safeName, 0, 20);
$jobId = 'leaflet_' . $safeName . '_' . date('Ymd_His');

$outputBase = realpath(__DIR__ . '/../_leaflet_factory/output');
if (!$outputBase) {
    mkdir(__DIR__ . '/../_leaflet_factory/output', 0777, true);
    $outputBase = realpath(__DIR__ . '/../_leaflet_factory/output');
}

$jobDir = $outputBase . '/' . $jobId;
$old_umask = umask(0); 
mkdir($jobDir, 0777, true);
chmod($jobDir, 0777);
mkdir($jobDir . '/uploads', 0777, true); 
chmod($jobDir . '/uploads', 0777);

// ── 3. 이미지 업로드 처리 ──
$uploadedFiles = [];
if (isset($_FILES['assets']) && is_array($_FILES['assets']['tmp_name'])) {
    foreach ($_FILES['assets']['tmp_name'] as $key => $tmpName) {
        if ($tmpName !== '') {
            $fileName = basename($_FILES['assets']['name'][$key]);
            $fileName = preg_replace('/[^a-zA-Z0-9.-]/', '_', $fileName);
            $targetPath = $jobDir . '/uploads/' . time() . '_' . $fileName;
            
            if (move_uploaded_file($tmpName, $targetPath)) {
                chmod($targetPath, 0666); 
                $uploadedFiles[] = $targetPath;
            }
        }
    }
}

if (count($uploadedFiles) === 0) {
    $imageUsage = 'ai_generate';
}

// ── 4. 기획 데이터 (brief.json) 저장 ──
$brief = [
    'job_id' => $jobId,
    'business_name' => $businessName,
    'category' => $category,
    'features' => array_map('trim', explode(',', $features)),
    'contact' => [
        'phone' => $phone,
        'address' => $address,
        'hours' => $hours
    ],
    'items' => $items,
    'direction' => [
        'purpose' => $purpose,
        'target_audience' => $targetAud,
        'image_usage' => $imageUsage,
        'layout_style' => $layoutStyle
    ],
    'uploaded_images' => $uploadedFiles
];

file_put_contents($jobDir . '/brief.json', json_encode($brief, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
chmod($jobDir . '/brief.json', 0666);

// ── 5. Python Orchestrator 백그라운드 실행 ──
$scriptPath = realpath(__DIR__ . '/../_leaflet_factory/scripts/orchestrator.py');
$logPath = $jobDir . '/process.log';
$statusPath = $jobDir . '/status.json';

$initialStatus = [
    'status' => 'running',
    'progress' => 5,
    'current_step_name' => 'Python 에이전트 초기화 중...',
    'logs' => ['[System] 작업을 큐에 등록했습니다.']
];

touch($statusPath);
chmod($statusPath, 0666);
file_put_contents($statusPath, json_encode($initialStatus, JSON_UNESCAPED_UNICODE));

touch($logPath);
chmod($logPath, 0666);

umask($old_umask); 

// .env 파일에서 API 키 직접 읽기
$apiKey = '';
if (file_exists('/var/www/html/.env')) {
    $lines = file('/var/www/html/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), 'GEMINI_API_KEY=') === 0) {
            $apiKey = substr(trim($line), 15);
            $apiKey = trim($apiKey, '"\'');
            break;
        }
    }
}

// 확실하게 백그라운드로 분리되도록 실행하는 쉘 래퍼 스크립트를 임시로 생성하여 실행합니다.
$wrapperPath = $jobDir . '/run.sh';
$pythonPath = '/home/ysung/.local/lib/python3.12/site-packages';

$shContent = "#!/bin/bash\n";
$shContent .= "export GEMINI_API_KEY='" . $apiKey . "'\n";
$shContent .= "export PYTHONPATH='" . $pythonPath . ":\$PYTHONPATH'\n";
$shContent .= "nohup python3 '" . $scriptPath . "' --workdir '" . $jobDir . "' > '" . $logPath . "' 2>&1 &\n";

file_put_contents($wrapperPath, $shContent);
chmod($wrapperPath, 0777);

// 쉘 래퍼 실행 (완벽하게 백그라운드로 떨어져나감)
exec($wrapperPath . ' > /dev/null 2>&1');

// ── 6. 응답 ──
echo json_encode([
    'success' => true,
    'job_id' => $jobId
]);
