<?php
/**
 * AI 상세페이지 관리 API
 * 
 * Actions:
 *   GET  ?action=status           - 전체 품목/버전 상태
 *   GET  ?action=history          - 교체 히스토리
 *   POST ?action=switch_version   - A↔B 수동 전환 (전체 또는 품목별)
 *   POST ?action=pin              - 품목별 버전 고정
 *   POST ?action=unpin            - 품목별 버전 고정 해제
 *   POST ?action=toggle_auto      - 자동 로테이션 ON/OFF
 *   POST ?action=set_schedule     - 로테이션 스케줄 변경
 *   POST ?action=generate         - AI 새 버전 생성 (백그라운드)
 *   POST ?action=promote          - Staging → V_A 또는 V_B 승격
 */

require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

// ─── Constants ───
define('IMG_BASE', '/var/www/html/ImgFolder');
define('LIVE_DIR', IMG_BASE . '/detail_page');
define('STAGING_DIR', IMG_BASE . '/detail_page_staging');
define('VER_A_DIR', IMG_BASE . '/detail_page_v_a');
define('VER_B_DIR', IMG_BASE . '/detail_page_v_b');
define('AB_STATE_FILE', '/var/www/html/scripts/ab_rotation.json');
define('CONFIG_FILE', '/var/www/html/scripts/detail_page_config.json');
define('ROTATION_STATE_FILE', '/var/www/html/scripts/detail_page_rotation.json');
define('ORCHESTRATOR_DIR', '/var/www/html/_detail_page');
define('ORCHESTRATOR_OUTPUT', ORCHESTRATOR_DIR . '/output');

// .env에서 Gemini API 키 로드 (exec 환경변수 전달용)
$GEMINI_KEY = '';
$envFile = '/var/www/html/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, 'GEMINI_API_KEY=') === 0) {
            $GEMINI_KEY = trim(substr($line, strlen('GEMINI_API_KEY=')));
            break;
        }
    }
}

$SECTION_LABELS = [
    1 => '긴급성 헤더', 2 => '공감', 3 => '문제 정의', 4 => '솔루션 제시',
    5 => '제품 소개', 6 => '스펙 상세', 7 => '비포&애프터', 8 => '가격 안내',
    9 => '제작 과정', 10 => '고객 후기', 11 => 'FAQ', 12 => '신뢰 배지',
    13 => '최종 CTA',
];

$PRODUCTS = [
    'namecard'        => ['label' => '명함',       'icon' => '🪪'],
    'inserted'        => ['label' => '전단지',     'icon' => '📄'],
    'sticker_new'     => ['label' => '스티커',     'icon' => '🏷️'],
    'msticker'        => ['label' => '자석스티커', 'icon' => '🧲'],
    'envelope'        => ['label' => '봉투',       'icon' => '✉️'],
    'littleprint'     => ['label' => '포스터',     'icon' => '🖼️'],
    'merchandisebond' => ['label' => '상품권',     'icon' => '🎫'],
    'cadarok'         => ['label' => '카다록',     'icon' => '📚'],
    'ncrflambeau'     => ['label' => 'NCR양식지',  'icon' => '📋'],
];

// ─── Helpers ───

function loadJson(string $path): array {
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function saveJson(string $path, array $data): bool {
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

function getVersionInfo(string $dir, string $product): array {
    $path = $dir . '/' . $product;
    if (!is_dir($path)) {
        return ['exists' => false, 'images' => 0, 'has_html' => false, 'has_copy' => false, 'modified' => null];
    }
    $images = count(glob($path . '/section_*.{jpg,png}', GLOB_BRACE));
    $hasHtml = file_exists($path . '/detail.html');
    $hasCopy = file_exists($path . '/copies.json') || file_exists($path . '/copy.json');
    
    // Last modified
    $mtime = null;
    $files = glob($path . '/*');
    if ($files) {
        $mtime = max(array_map('filemtime', $files));
    }
    
    return [
        'exists' => $images > 0 || $hasHtml,
        'images' => $images,
        'has_html' => $hasHtml,
        'has_copy' => $hasCopy,
        'modified' => $mtime ? date('Y-m-d H:i', $mtime) : null,
    ];
}

function copyVersionFiles(string $srcDir, string $dstDir, string $product): int {
    $src = $srcDir . '/' . $product;
    $dst = $dstDir . '/' . $product;
    if (!is_dir($src)) return 0;
    if (!is_dir($dst)) mkdir($dst, 0755, true);
    
    $count = 0;
    foreach (glob($src . '/*') as $file) {
        if (is_file($file)) {
            copy($file, $dst . '/' . basename($file));
            $count++;
        }
    }
    return $count;
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonError(string $message, int $code = 400): void {
    jsonResponse(['success' => false, 'error' => $message], $code);
}

// ─── Action Router ───

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'status':
        handleStatus();
        break;
    case 'history':
        handleHistory();
        break;
    case 'switch_version':
        handleSwitchVersion();
        break;
    case 'pin':
        handlePin();
        break;
    case 'unpin':
        handleUnpin();
        break;
    case 'toggle_auto':
        handleToggleAuto();
        break;
    case 'set_schedule':
        handleSetSchedule();
        break;
    case 'generate':
        handleGenerate();
        break;
    case 'promote':
        handlePromote();
        break;
    case 'get_sections':
        handleGetSections();
        break;
    case 'update_section':
        handleUpdateSection();
        break;
    case 'regen_section':
        handleRegenSection();
        break;
    case 'regen_status':
        handleRegenStatus();
        break;
    case 'deploy_output':
        handleDeployOutput();
        break;
    default:
        jsonError('Unknown action: ' . $action);
}

// ─── Action Handlers ───

/**
 * GET ?action=status
 * 전체 품목/버전 상태 반환
 */
function handleStatus(): void {
    global $PRODUCTS;
    
    $abState = loadJson(AB_STATE_FILE);
    $config = loadJson(CONFIG_FILE);
    $activeVer = strtoupper($abState['active'] ?? 'A');
    
    $products = [];
    foreach ($PRODUCTS as $code => $info) {
        $pinned = $config['product_pins'][$code] ?? null;
        
        // 실제 적용 중인 버전 결정
        $effectiveVer = $pinned ?: $activeVer;
        
        $products[$code] = [
            'label' => $info['label'],
            'icon' => $info['icon'],
            'pinned' => $pinned,
            'effective_version' => $effectiveVer,
            'versions' => [
                'A' => getVersionInfo(VER_A_DIR, $code),
                'B' => getVersionInfo(VER_B_DIR, $code),
                'staging' => getVersionInfo(STAGING_DIR, $code),
                'live' => getVersionInfo(LIVE_DIR, $code),
            ],
        ];
    }
    
    jsonResponse([
        'success' => true,
        'active_version' => $activeVer,
        'last_switch' => $abState['last_switch'] ?? null,
        'cycle' => $abState['cycle'] ?? 0,
        'auto_rotation' => $config['auto_rotation'] ?? ['enabled' => true, 'schedule' => 'weekly', 'day' => 1, 'hour' => 9],
        'products' => $products,
    ]);
}

/**
 * GET ?action=history
 * 교체 히스토리 반환
 */
function handleHistory(): void {
    $abState = loadJson(AB_STATE_FILE);
    $config = loadJson(CONFIG_FILE);
    
    // ab_rotation.json + config의 history 병합 후 날짜 역순 정렬
    $abHistory = $abState['history'] ?? [];
    $configHistory = $config['history'] ?? [];
    
    $merged = [];
    foreach ($abHistory as $h) {
        $merged[] = array_merge($h, ['source' => 'auto_rotation']);
    }
    foreach ($configHistory as $h) {
        $merged[] = array_merge($h, ['source' => 'dashboard']);
    }
    
    usort($merged, function ($a, $b) {
        return strcmp($b['date'] ?? '', $a['date'] ?? '');
    });
    
    jsonResponse([
        'success' => true,
        'history' => array_slice($merged, 0, 50),
    ]);
}

/**
 * POST ?action=switch_version
 * body: { product?: string }  (없으면 전체 전환)
 */
function handleSwitchVersion(): void {
    global $PRODUCTS;
    
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $targetProduct = $input['product'] ?? null;
    
    $abState = loadJson(AB_STATE_FILE);
    $config = loadJson(CONFIG_FILE);
    $current = strtoupper($abState['active'] ?? 'A');
    $next = $current === 'A' ? 'B' : 'A';
    $srcBase = $next === 'B' ? VER_B_DIR : VER_A_DIR;
    
    $switched = [];
    $skipped = [];
    
    if ($targetProduct) {
        // 개별 품목 전환 — 해당 품목만 반대 버전으로 복사
        if (!isset($PRODUCTS[$targetProduct])) {
            jsonError('Unknown product: ' . $targetProduct);
        }
        $info = getVersionInfo($srcBase, $targetProduct);
        if ($info['exists']) {
            copyVersionFiles($srcBase, LIVE_DIR, $targetProduct);
            $switched[] = $targetProduct;
            
            // 품목별 핀 설정
            $config['product_pins'][$targetProduct] = $next;
        } else {
            $skipped[] = $targetProduct;
        }
    } else {
        // 전체 전환
        foreach ($PRODUCTS as $code => $pinfo) { // $pinfo used for label
            // 핀 고정된 품목은 건너뛰기
            $pinned = $config['product_pins'][$code] ?? null;
            if ($pinned) {
                $skipped[] = $code;
                continue;
            }
            
            $info = getVersionInfo($srcBase, $code);
            if ($info['exists']) {
                copyVersionFiles($srcBase, LIVE_DIR, $code);
                $switched[] = $code;
            } else {
                $skipped[] = $code;
            }
        }
        
        // 전체 전환 시 AB 상태 업데이트
        $abState['active'] = $next;
        $abState['last_switch'] = date('Y-m-d');
        $abState['cycle'] = ($abState['cycle'] ?? 0) + 1;
        $abState['history'][] = [
            'date' => date('Y-m-d H:i:s'),
            'switched_to' => $next,
            'switched' => count($switched),
            'skipped' => $skipped,
            'trigger' => 'dashboard_manual',
        ];
        saveJson(AB_STATE_FILE, $abState);
    }
    
    // 히스토리 기록
    $config['history'][] = [
        'date' => date('Y-m-d H:i:s'),
        'action' => $targetProduct ? 'switch_product' : 'switch_all',
        'product' => $targetProduct,
        'from' => $current,
        'to' => $next,
        'switched' => $switched,
        'skipped' => $skipped,
        'user' => $_SESSION['admin_username'] ?? 'admin',
    ];
    saveJson(CONFIG_FILE, $config);
    
    jsonResponse([
        'success' => true,
        'from' => $current,
        'to' => $next,
        'switched' => $switched,
        'skipped' => $skipped,
    ]);
}

/**
 * POST ?action=pin
 * body: { product: string, version: "A"|"B" }
 */
function handlePin(): void {
    global $PRODUCTS;
    
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $product = $input['product'] ?? '';
    $version = strtoupper($input['version'] ?? '');
    
    if (!isset($PRODUCTS[$product])) jsonError('Unknown product');
    if (!in_array($version, ['A', 'B'])) jsonError('Version must be A or B');
    
    $config = loadJson(CONFIG_FILE);
    $config['product_pins'][$product] = $version;
    
    // 즉시 적용: 해당 버전의 파일을 live에 복사
    $srcBase = $version === 'A' ? VER_A_DIR : VER_B_DIR;
    copyVersionFiles($srcBase, LIVE_DIR, $product);
    
    $config['history'][] = [
        'date' => date('Y-m-d H:i:s'),
        'action' => 'pin',
        'product' => $product,
        'version' => $version,
        'user' => $_SESSION['admin_username'] ?? 'admin',
    ];
    saveJson(CONFIG_FILE, $config);
    
    jsonResponse([
        'success' => true,
        'product' => $product,
        'pinned_to' => $version,
    ]);
}

/**
 * POST ?action=unpin
 * body: { product: string }
 */
function handleUnpin(): void {
    global $PRODUCTS;
    
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $product = $input['product'] ?? '';
    
    if (!isset($PRODUCTS[$product])) jsonError('Unknown product');
    
    $config = loadJson(CONFIG_FILE);
    unset($config['product_pins'][$product]);
    
    // 전역 활성 버전으로 복원
    $abState = loadJson(AB_STATE_FILE);
    $activeVer = strtoupper($abState['active'] ?? 'A');
    $srcBase = $activeVer === 'A' ? VER_A_DIR : VER_B_DIR;
    copyVersionFiles($srcBase, LIVE_DIR, $product);
    
    $config['history'][] = [
        'date' => date('Y-m-d H:i:s'),
        'action' => 'unpin',
        'product' => $product,
        'restored_to' => $activeVer,
        'user' => $_SESSION['admin_username'] ?? 'admin',
    ];
    saveJson(CONFIG_FILE, $config);
    
    jsonResponse([
        'success' => true,
        'product' => $product,
        'restored_to' => $activeVer,
    ]);
}

/**
 * POST ?action=toggle_auto
 * body: { enabled: bool }
 */
function handleToggleAuto(): void {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $enabled = filter_var($input['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    
    $config = loadJson(CONFIG_FILE);
    if (!isset($config['auto_rotation'])) {
        $config['auto_rotation'] = ['enabled' => true, 'schedule' => 'weekly', 'day' => 1, 'hour' => 9, 'minute' => 0];
    }
    $config['auto_rotation']['enabled'] = $enabled;
    
    $config['history'][] = [
        'date' => date('Y-m-d H:i:s'),
        'action' => 'toggle_auto',
        'enabled' => $enabled,
        'user' => $_SESSION['admin_username'] ?? 'admin',
    ];
    saveJson(CONFIG_FILE, $config);
    
    // 크론잡 활성/비활성 처리
    updateCrontab($enabled, $config['auto_rotation']);
    
    jsonResponse([
        'success' => true,
        'auto_rotation_enabled' => $enabled,
    ]);
}

/**
 * POST ?action=set_schedule
 * body: { schedule: "weekly"|"biweekly"|"monthly", day: 0-6, hour: 0-23 }
 */
function handleSetSchedule(): void {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $schedule = $input['schedule'] ?? 'weekly';
    $day = intval($input['day'] ?? 1);
    $hour = intval($input['hour'] ?? 9);
    
    if (!in_array($schedule, ['weekly', 'biweekly', 'monthly'])) jsonError('Invalid schedule');
    if ($day < 0 || $day > 6) jsonError('Day must be 0-6');
    if ($hour < 0 || $hour > 23) jsonError('Hour must be 0-23');
    
    $config = loadJson(CONFIG_FILE);
    $config['auto_rotation'] = [
        'enabled' => $config['auto_rotation']['enabled'] ?? true,
        'schedule' => $schedule,
        'day' => $day,
        'hour' => $hour,
        'minute' => 0,
    ];
    
    $config['history'][] = [
        'date' => date('Y-m-d H:i:s'),
        'action' => 'set_schedule',
        'schedule' => $schedule,
        'day' => $day,
        'hour' => $hour,
        'user' => $_SESSION['admin_username'] ?? 'admin',
    ];
    saveJson(CONFIG_FILE, $config);
    
    if ($config['auto_rotation']['enabled']) {
        updateCrontab(true, $config['auto_rotation']);
    }
    
    jsonResponse([
        'success' => true,
        'auto_rotation' => $config['auto_rotation'],
    ]);
}

/**
 * POST ?action=generate
 * body: { product: string, engine: 'fast'|'quality' }
 */
function handleGenerate(): void {
    global $PRODUCTS, $GEMINI_KEY;
    
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $product = $input['product'] ?? '';
    $engine = $input['engine'] ?? 'fast';
    
    if (!isset($PRODUCTS[$product])) jsonError('Unknown product');
    if (!in_array($engine, ['fast', 'quality'])) jsonError('engine must be fast or quality');
    
    $envExport = $GEMINI_KEY ? sprintf('export GEMINI_API_KEY=%s && export PYTHONPATH=/home/ysung/.local/lib/python3.12/site-packages:$PYTHONPATH && ', escapeshellarg($GEMINI_KEY)) : 'export PYTHONPATH=/home/ysung/.local/lib/python3.12/site-packages:$PYTHONPATH && ';
    
    // 엔진별 백그라운드 실행
    if ($engine === 'quality') {
        $cmd = sprintf(
            '%scd %s && python3 scripts/orchestrator.py --product %s > /tmp/detail_gen_%s.log 2>&1 &',
            $envExport,
            escapeshellarg(ORCHESTRATOR_DIR),
            escapeshellarg($product),
            $product
        );
    } else {
        $cmd = sprintf(
            '%scd /var/www/html && python3 scripts/ai_detail_page.py generate-builtin %s > /tmp/detail_gen_%s.log 2>&1 &',
            $envExport,
            escapeshellarg($product),
            $product
        );
    }
    exec($cmd);
    
    $engineLabel = $engine === 'quality' ? '고품질' : '빠른';
    
    $config = loadJson(CONFIG_FILE);
    $config['history'][] = [
        'date' => date('Y-m-d H:i:s'),
        'action' => 'generate',
        'product' => $product,
        'engine' => $engine,
        'user' => $_SESSION['admin_username'] ?? 'admin',
    ];
    saveJson(CONFIG_FILE, $config);
    
    jsonResponse([
        'success' => true,
        'product' => $product,
        'engine' => $engine,
        'message' => $PRODUCTS[$product]['label'] . ' ' . $engineLabel . ' AI 생성이 백그라운드에서 시작되었습니다.',
        'log_file' => '/tmp/detail_gen_' . $product . '.log',
    ]);
}

/**
 * POST ?action=promote
 * body: { product: string, target: "A"|"B" }
 */
function handlePromote(): void {
    global $PRODUCTS;
    
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $product = $input['product'] ?? '';
    $target = strtoupper($input['target'] ?? '');
    
    if (!isset($PRODUCTS[$product])) jsonError('Unknown product');
    if (!in_array($target, ['A', 'B'])) jsonError('Target must be A or B');
    
    $stagingInfo = getVersionInfo(STAGING_DIR, $product);
    if (!$stagingInfo['exists']) jsonError('Staging에 파일이 없습니다.');
    
    $dstDir = $target === 'A' ? VER_A_DIR : VER_B_DIR;
    $count = copyVersionFiles(STAGING_DIR, $dstDir, $product);
    
    $config = loadJson(CONFIG_FILE);
    $config['history'][] = [
        'date' => date('Y-m-d H:i:s'),
        'action' => 'promote',
        'product' => $product,
        'from' => 'staging',
        'to' => 'V_' . $target,
        'files' => $count,
        'user' => $_SESSION['admin_username'] ?? 'admin',
    ];
    saveJson(CONFIG_FILE, $config);
    
    jsonResponse([
        'success' => true,
        'product' => $product,
        'promoted_to' => 'V_' . $target,
        'files_copied' => $count,
    ]);
}

// ─── Section Editor Handlers ───

/**
 * GET ?action=get_sections&product=namecard&engine=fast|quality
 * 품목의 copy.json 섹션 목록 + 이미지 정보 (엔진별 포맷 정규화)
 */
function handleGetSections(): void {
    global $PRODUCTS, $SECTION_LABELS;
    
    $product = $_GET['product'] ?? '';
    $engine = $_GET['engine'] ?? 'quality';
    if (!isset($PRODUCTS[$product])) jsonError('Unknown product');
    
    // 엔진별 copy.json 경로 및 이미지 경로
    if ($engine === 'fast') {
        $outputDir = STAGING_DIR . '/' . $product;
        $copyFile = $outputDir . '/copy.json';
        $imgExt = 'jpg';
        $imgBaseUrl = '/ImgFolder/detail_page_staging/' . $product . '/';
    } else {
        $outputDir = ORCHESTRATOR_OUTPUT . '/' . $product;
        $copyFile = $outputDir . '/copy.json';
        $imgExt = 'png';
        $imgBaseUrl = '/_detail_page/output/' . $product . '/sections/';
    }
    
    if (!file_exists($copyFile)) {
        jsonError('copy.json을 찾을 수 없습니다. ' . ($engine === 'fast' ? '빠른' : '고품질') . ' 엔진으로 전체 생성을 먼저 실행하세요.');
    }
    
    $data = json_decode(file_get_contents($copyFile), true);
    $rawSections = $data['sections'] ?? [];
    $sections = [];
    
    foreach ($rawSections as $section) {
        if ($engine === 'fast') {
            // fast 포맷: section.copy.headline/subtext → 정규화
            $id = $section['section'] ?? 0;
            $copy = $section['copy'] ?? [];
            $normalized = [
                'id' => $id,
                'name' => $section['theme'] ?? '',
                'headline' => $copy['headline'] ?? '',
                'body' => $copy['subtext'] ?? '',
                'subtext' => $copy['subtext'] ?? '',
                'cta' => $copy['cta'] ?? '',
            ];
        } else {
            // quality 포맷: section.id, headline, body 그대로
            $id = $section['id'] ?? 0;
            $normalized = $section;
        }
        
        $imgFile = sprintf('section_%02d.%s', $id, $imgExt);
        if ($engine === 'fast') {
            $imgPath = $outputDir . '/' . $imgFile;
        } else {
            $imgPath = $outputDir . '/sections/' . $imgFile;
        }
        $normalized['image_url'] = file_exists($imgPath)
            ? $imgBaseUrl . $imgFile . '?t=' . filemtime($imgPath)
            : null;
        $normalized['image_exists'] = file_exists($imgPath);
        $normalized['label'] = $SECTION_LABELS[$id] ?? '섹션 ' . $id;
        
        $sections[] = $normalized;
    }
    
    // can_regen: quality 엔진은 design.json+product_brief.json 필요, fast는 copy.json만 있으면 가능
    if ($engine === 'fast') {
        $canRegen = file_exists($copyFile);
    } else {
        $canRegen = file_exists($outputDir . '/design.json')
            && file_exists($outputDir . '/product_brief.json')
            && file_exists($copyFile);
    }
    
    jsonResponse([
        'success' => true,
        'product' => $product,
        'engine' => $engine,
        'sections' => $sections,
        'can_regen' => $canRegen,
    ]);
}

/**
 * POST ?action=update_section
 * body: { product, section_id, fields: { headline, body, ... }, engine: 'fast'|'quality' }
 */
function handleUpdateSection(): void {
    global $PRODUCTS;
    
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $product = $input['product'] ?? '';
    $sectionId = (int)($input['section_id'] ?? 0);
    $fields = $input['fields'] ?? [];
    $engine = $input['engine'] ?? 'quality';
    
    if (!isset($PRODUCTS[$product])) jsonError('Unknown product');
    if ($sectionId < 1 || $sectionId > 13) jsonError('섹션 번호는 1~13 사이여야 합니다');
    if (empty($fields)) jsonError('수정할 필드가 없습니다');
    
    // 엔진별 copy.json 경로
    if ($engine === 'fast') {
        $copyFile = STAGING_DIR . '/' . $product . '/copy.json';
    } else {
        $copyFile = ORCHESTRATOR_OUTPUT . '/' . $product . '/copy.json';
    }
    
    if (!file_exists($copyFile)) {
        jsonError('copy.json을 찾을 수 없습니다.');
    }
    
    $data = json_decode(file_get_contents($copyFile), true);
    $updated = false;
    
    foreach ($data['sections'] as &$section) {
        // 엔진별 ID 매칭 요소
        $secId = ($engine === 'fast') ? ($section['section'] ?? 0) : ($section['id'] ?? 0);
        
        if ($secId === $sectionId) {
            if ($engine === 'fast') {
                // fast 포맷: section.copy.headline/subtext 에 저장
                if (!isset($section['copy'])) $section['copy'] = [];
                if (isset($fields['headline'])) $section['copy']['headline'] = $fields['headline'];
                if (isset($fields['body'])) $section['copy']['subtext'] = $fields['body'];
                if (isset($fields['subtext'])) $section['copy']['subtext'] = $fields['subtext'];
                if (isset($fields['cta'])) $section['copy']['cta'] = $fields['cta'];
            } else {
                // quality 포맷: section.headline/body 등에 직접 저장
                $allowed = ['headline', 'body', 'subtext', 'badge', 'highlight', 'button_text'];
                foreach ($fields as $key => $value) {
                    if (in_array($key, $allowed)) {
                        $section[$key] = $value;
                    }
                }
            }
            $updated = true;
            break;
        }
    }
    
    if (!$updated) jsonError('섹션 ' . $sectionId . '을 찾을 수 없습니다.');
    
    file_put_contents($copyFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    $config = loadJson(CONFIG_FILE);
    $config['history'][] = [
        'date' => date('Y-m-d H:i:s'),
        'action' => 'update_section',
        'product' => $product,
        'section_id' => $sectionId,
        'engine' => $engine,
        'user' => $_SESSION['admin_username'] ?? 'admin',
    ];
    saveJson(CONFIG_FILE, $config);
    
    jsonResponse([
        'success' => true,
        'message' => '섹션 ' . $sectionId . ' 텍스트가 저장되었습니다.',
    ]);
}

/**
 * POST ?action=regen_section
 * body: { product, section_id, engine: 'fast'|'quality' }
 */
function handleRegenSection(): void {
    global $PRODUCTS, $GEMINI_KEY;
    
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $product = $input['product'] ?? '';
    $sectionId = (int)($input['section_id'] ?? 0);
    $engine = $input['engine'] ?? 'quality';
    
    if (!isset($PRODUCTS[$product])) jsonError('Unknown product');
    if ($sectionId < 1 || $sectionId > 13) jsonError('섹션 번호는 1~13 사이여야 합니다');
    
    // 엔진별 전제 파일 검증
    if ($engine === 'fast') {
        $outputDir = STAGING_DIR . '/' . $product;
        if (!file_exists($outputDir . '/copy.json')) {
            jsonError('copy.json이 없습니다. 빠른 엔진으로 전체 생성을 먼저 실행하세요.');
        }
    } else {
        $outputDir = ORCHESTRATOR_OUTPUT . '/' . $product;
        foreach (['copy.json', 'design.json', 'product_brief.json'] as $file) {
            if (!file_exists($outputDir . '/' . $file)) {
                jsonError($file . '이 없습니다. 고품질 엔진으로 전체 생성을 먼저 실행하세요.');
            }
        }
    }
    
    $logFile = '/tmp/regen_' . $product . '_s' . $sectionId . '.log';
    if (file_exists($logFile)) unlink($logFile);
    
    $envExport = $GEMINI_KEY ? sprintf('export GEMINI_API_KEY=%s && export PYTHONPATH=/home/ysung/.local/lib/python3.12/site-packages:$PYTHONPATH && ', escapeshellarg($GEMINI_KEY)) : 'export PYTHONPATH=/home/ysung/.local/lib/python3.12/site-packages:$PYTHONPATH && ';
    
    // 엔진별 명령 실행
    if ($engine === 'fast') {
        $cmd = sprintf(
            '%scd /var/www/html && python3 scripts/ai_detail_page.py regen-section-builtin %s %d > %s 2>&1 &',
            $envExport,
            escapeshellarg($product),
            $sectionId,
            escapeshellarg($logFile)
        );
    } else {
        $cmd = sprintf(
            '%scd %s && python3 scripts/orchestrator.py --product %s --section %d > %s 2>&1 &',
            $envExport,
            escapeshellarg(ORCHESTRATOR_DIR),
            escapeshellarg($product),
            $sectionId,
            escapeshellarg($logFile)
        );
    }
    exec($cmd);
    
    $config = loadJson(CONFIG_FILE);
    $config['history'][] = [
        'date' => date('Y-m-d H:i:s'),
        'action' => 'regen_section',
        'product' => $product,
        'section_id' => $sectionId,
        'engine' => $engine,
        'user' => $_SESSION['admin_username'] ?? 'admin',
    ];
    saveJson(CONFIG_FILE, $config);
    
    $engineLabel = $engine === 'quality' ? '고품질' : '빠른';
    jsonResponse([
        'success' => true,
        'message' => '섹션 ' . $sectionId . ' ' . $engineLabel . ' 재생성이 시작되었습니다. (~30초 소요)',
        'log_file' => $logFile,
    ]);
}

/**
 * GET ?action=regen_status&product=namecard&section_id=3&engine=fast|quality
 * 재생성 진행 상태 확인 (polling)
 */
function handleRegenStatus(): void {
    $product = $_GET['product'] ?? '';
    $sectionId = (int)($_GET['section_id'] ?? 0);
    $engine = $_GET['engine'] ?? 'quality';
    
    $logFile = '/tmp/regen_' . $product . '_s' . $sectionId . '.log';
    $logContent = file_exists($logFile) ? file_get_contents($logFile) : '';
    
    $completed = strpos($logContent, '재생성 완료') !== false
        || strpos($logContent, '재생성을 완료') !== false;
    $failed = (strpos($logContent, '실패') !== false || strpos($logContent, 'Error') !== false) && !$completed;
    
    // 엔진별 이미지 경로
    if ($engine === 'fast') {
        $imgPath = STAGING_DIR . '/' . $product . '/' . sprintf('section_%02d.jpg', $sectionId);
    } else {
        $imgPath = ORCHESTRATOR_OUTPUT . '/' . $product . '/sections/' . sprintf('section_%02d.png', $sectionId);
    }
    
    jsonResponse([
        'success' => true,
        'completed' => $completed,
        'failed' => $failed,
        'image_mtime' => file_exists($imgPath) ? filemtime($imgPath) : 0,
        'log_tail' => mb_substr($logContent, -500),
    ]);
}

/**
 * POST ?action=deploy_output
 * body: { product, target: "staging"|"v_a"|"v_b"|"live" }
 * _detail_page/output/ → ImgFolder 대상 디렉토리에 복사
 */
function handleDeployOutput(): void {
    global $PRODUCTS;
    
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $product = $input['product'] ?? '';
    $target = $input['target'] ?? '';
    
    if (!isset($PRODUCTS[$product])) jsonError('Unknown product');
    
    $targetMap = [
        'staging' => STAGING_DIR,
        'v_a' => VER_A_DIR,
        'v_b' => VER_B_DIR,
        'live' => LIVE_DIR,
    ];
    $targetDir = $targetMap[$target] ?? null;
    if (!$targetDir) jsonError('Target must be: staging, v_a, v_b, live');
    
    $srcDir = ORCHESTRATOR_OUTPUT . '/' . $product;
    $sectionsDir = $srcDir . '/sections';
    
    if (!is_dir($sectionsDir)) {
        jsonError('출력 디렉토리에 섹션 이미지가 없습니다.');
    }
    
    $dstDir = $targetDir . '/' . $product;
    if (!is_dir($dstDir)) mkdir($dstDir, 0755, true);
    
    $count = 0;
    
    // Copy section images (sections/ → target root)
    foreach (glob($sectionsDir . '/section_*.png') as $file) {
        copy($file, $dstDir . '/' . basename($file));
        $count++;
    }
    
    // Copy copy.json
    if (file_exists($srcDir . '/copy.json')) {
        copy($srcDir . '/copy.json', $dstDir . '/copy.json');
        $count++;
    }
    
    // Copy final stitched image
    if (file_exists($srcDir . '/final_detail_page.png')) {
        copy($srcDir . '/final_detail_page.png', $dstDir . '/final_detail_page.png');
        $count++;
    }
    
    $labelMap = ['staging' => 'Staging', 'v_a' => 'VER A', 'v_b' => 'VER B', 'live' => 'Live'];
    $targetLabel = $labelMap[$target] ?? $target;
    
    $config = loadJson(CONFIG_FILE);
    $config['history'][] = [
        'date' => date('Y-m-d H:i:s'),
        'action' => 'deploy_output',
        'product' => $product,
        'target' => $targetLabel,
        'files' => $count,
        'user' => $_SESSION['admin_username'] ?? 'admin',
    ];
    saveJson(CONFIG_FILE, $config);
    
    jsonResponse([
        'success' => true,
        'message' => $PRODUCTS[$product]['label'] . '의 편집 결과가 ' . $targetLabel . '에 배포되었습니다. (' . $count . '개 파일)',
        'files_copied' => $count,
    ]);
}

// ─── Crontab Management ───

function updateCrontab(bool $enabled, array $schedule): void {
    $cronLine = 'python3 scripts/ai_detail_page.py ab-rotate >> /var/log/detail_page_ab.log 2>&1';
    $marker = '# AI 상세페이지 A/B 주간 로테이션';
    
    // 현재 crontab 읽기
    exec('crontab -l 2>/dev/null', $lines, $ret);
    $existingLines = ($ret === 0) ? $lines : [];
    
    // 기존 라인 제거
    $filtered = [];
    $skipNext = false;
    foreach ($existingLines as $line) {
        if (strpos($line, $marker) !== false) {
            $skipNext = true;
            continue;
        }
        if ($skipNext && strpos($line, 'ai_detail_page') !== false) {
            $skipNext = false;
            continue;
        }
        $skipNext = false;
        $filtered[] = $line;
    }
    
    // 새 크론잡 추가 (enabled일 때만)
    if ($enabled) {
        $hour = $schedule['hour'] ?? 9;
        $day = $schedule['day'] ?? 1;
        $cronSchedule = '';
        
        switch ($schedule['schedule'] ?? 'weekly') {
            case 'biweekly':
                // 격주: 홀수 주에만 실행 (test 조건)
                $cronSchedule = "0 {$hour} * * {$day}";
                $cronLine = "test \$(( \$(date +\\%W) \\% 2 )) -eq 1 && " . $cronLine;
                break;
            case 'monthly':
                // 월간: 매월 첫째 해당 요일
                $cronSchedule = "0 {$hour} 1-7 * {$day}";
                break;
            default: // weekly
                $cronSchedule = "0 {$hour} * * {$day}";
                break;
        }
        
        $filtered[] = $marker;
        $filtered[] = "{$cronSchedule} {$cronLine}";
    }
    
    // crontab 업데이트
    $tmpFile = tempnam(sys_get_temp_dir(), 'cron_');
    file_put_contents($tmpFile, implode("\n", $filtered) . "\n");
    exec("crontab {$tmpFile} 2>&1", $output, $ret);
    unlink($tmpFile);
}
