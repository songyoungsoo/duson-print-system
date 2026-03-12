<?php
/**
 * generate.php — AJAX POST Handler for Poster Factory
 * 
 * Receives form data, creates brief.json, launches poster_generator.py --auto
 * Returns JSON: { success: true, job_id: "...", job_dir: "..." }
 */

header('Content-Type: application/json; charset=utf-8');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST 요청만 허용됩니다.']);
    exit;
}

// ── 1. Extract form data ──
$businessName = trim($_POST['business_name'] ?? '');
$category     = trim($_POST['category'] ?? '');
$customCat    = trim($_POST['custom_category'] ?? '');
$phone        = trim($_POST['phone'] ?? '');
$address      = trim($_POST['address'] ?? '');
$hours        = trim($_POST['hours'] ?? '');
$feature1     = trim($_POST['feature1'] ?? '');
$feature2     = trim($_POST['feature2'] ?? '');
$feature3     = trim($_POST['feature3'] ?? '');
$promoTitle   = trim($_POST['promo_title'] ?? '');
$promoDetail  = trim($_POST['promo_detail'] ?? '');
$purpose      = trim($_POST['purpose'] ?? '메뉴 홍보');
$targetAud    = trim($_POST['target_audience'] ?? '');
$tone         = trim($_POST['tone'] ?? '');
$layout       = trim($_POST['layout'] ?? 'auto');

// ── 2. Validation ──
if ($businessName === '') {
    echo json_encode(['success' => false, 'error' => '가게명을 입력해주세요.']);
    exit;
}

if ($category === '') {
    echo json_encode(['success' => false, 'error' => '업종을 선택해주세요.']);
    exit;
}

// If "기타", use custom category
$industry = ($category === '기타' && $customCat !== '') ? $customCat : $category;

// ── 3. Build items array ──
$itemNames  = $_POST['item_name'] ?? [];
$itemDescs  = $_POST['item_desc'] ?? [];
$itemPrices = $_POST['item_price'] ?? [];

$items = [];
for ($i = 0; $i < count($itemNames); $i++) {
    $name = trim($itemNames[$i] ?? '');
    if ($name === '') continue;
    
    $items[] = [
        'name'        => $name,
        'description' => trim($itemDescs[$i] ?? ''),
        'price'       => trim($itemPrices[$i] ?? ''),
    ];
}

if (count($items) === 0) {
    echo json_encode(['success' => false, 'error' => '최소 1개 이상의 메뉴를 입력해주세요.']);
    exit;
}

// ── 4. Build features array ──
$features = [];
if ($feature1 !== '') $features[] = $feature1;
if ($feature2 !== '') $features[] = $feature2;
if ($feature3 !== '') $features[] = $feature3;

// ── 5. Build contact ──
$contact = [];
if ($phone !== '')   $contact['phone']   = $phone;
if ($address !== '') $contact['address'] = $address;
if ($hours !== '')   $contact['hours']   = $hours;

// ── 6. Build promo ──
$promo = [];
if ($promoTitle !== '' || $promoDetail !== '') {
    $promo['title']  = $promoTitle;
    $promo['detail'] = $promoDetail;
}

// ── 7. Construct brief.json ──
$brief = [
    'business_name'   => $businessName,
    'industry'        => $industry,
    'category'        => $category,
    'features'        => $features,
    'target_audience' => $targetAud ?: '일반 고객',
    'tone'            => $tone ?: '',
    'purpose'         => $purpose,
    'items'           => $items,
    'contact'         => $contact,
];

if (!empty($promo)) {
    $brief['promo'] = $promo;
}

// ── 8. Create job directory ──
$safeName  = preg_replace('/[^a-zA-Z0-9가-힣_-]/u', '', $businessName);
$safeName  = mb_substr($safeName, 0, 30);
if ($safeName === '') $safeName = 'poster';

$timestamp = date('Ymd_His');
$jobId     = 'web_' . $safeName . '_' . $timestamp;

$outputBase = realpath(__DIR__ . '/../_poster_factory/output');
if (!$outputBase) {
    // Create output directory if it doesn't exist
    $outputBase = __DIR__ . '/../_poster_factory/output';
    if (!is_dir($outputBase)) {
        mkdir($outputBase, 0755, true);
    }
    $outputBase = realpath($outputBase);
}

$jobDir = $outputBase . '/' . $jobId;

if (!mkdir($jobDir, 0755, true)) {
    echo json_encode(['success' => false, 'error' => '작업 디렉토리 생성에 실패했습니다.']);
    exit;
}

// ── 9. Save brief.json ──
$briefJson = json_encode($brief, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
$briefPath = $jobDir . '/brief.json';

if (file_put_contents($briefPath, $briefJson) === false) {
    echo json_encode(['success' => false, 'error' => 'brief.json 저장에 실패했습니다.']);
    exit;
}

// ── 10. Launch Python background process ──
$scriptPath = realpath(__DIR__ . '/../_poster_factory/scripts/poster_generator.py');
$logPath    = $jobDir . '/process.log';

// Build command
$layoutFlag = '';
$validLayouts = ['classic_grid', 'hero_dominant', 'magazine_split', 'bold_typo', 'side_by_side'];
if ($layout !== 'auto' && in_array($layout, $validLayouts)) {
    $layoutFlag = ' --layout ' . escapeshellarg($layout);
}

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
$shContent .= "nohup python3 '" . $scriptPath . "' --workdir '" . $jobDir . "' --auto" . $layoutFlag . " > '" . $logPath . "' 2>&1 &\n";

file_put_contents($wrapperPath, $shContent);
chmod($wrapperPath, 0777);

// 쉘 래퍼 실행 (완벽하게 백그라운드로 떨어져나감)
exec($wrapperPath . ' > /dev/null 2>&1');

// ── 11. Return success ──
echo json_encode([
    'success' => true,
    'job_id'  => $jobId,
    'job_dir' => '_poster_factory/output/' . $jobId,
], JSON_UNESCAPED_UNICODE);
