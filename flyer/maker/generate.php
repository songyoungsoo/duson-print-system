<?php
/**
 * 전단지 PDF 생성기
 *
 * POST 데이터(업종, 상호, 메뉴, 이미지 등)를 받아
 * mPDF로 A4 전단지 PDF를 생성하여 다운로드합니다.
 *
 * Expected POST fields:
 *   industry_key     — e.g. "restaurant_korean"
 *   business_name    — 상호명
 *   tagline          — 캐치프레이즈
 *   phone            — 전화번호
 *   address          — 주소
 *   hours            — 영업시간
 *   website_url      — 웹사이트 URL (QR 코드 생성용)
 *   features[]       — 특장점 배열 (최대 3개)
 *   menu_name[]      — 메뉴 이름 배열
 *   menu_price[]     — 메뉴 가격 배열
 *   promotion        — 프로모션 텍스트
 *
 * File uploads:
 *   logo             — 로고 이미지
 *   photos[]         — 사진 (최대 4장)
 *   map_image        — 약도 이미지
 */

// Prevent any HTML output before PDF binary
error_reporting(E_ALL);
ini_set('display_errors', '0');

// ===================================================================
// Only accept POST
// ===================================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

// Load industry presets
require_once __DIR__ . '/templates/presets.php';

// Track temp files for cleanup
$tempFiles = [];

// ===================================================================
// 1. Validate required fields
// ===================================================================
$businessName = trim($_POST['business_name'] ?? '');
$phone        = trim($_POST['phone'] ?? '');
$address      = trim($_POST['address'] ?? '');
$industryKey  = trim($_POST['industry_key'] ?? 'general_store');
$tagline      = trim($_POST['tagline'] ?? '');
$hours        = trim($_POST['hours'] ?? '');
$websiteUrl   = trim($_POST['website_url'] ?? '');
$promotion    = trim($_POST['promotion'] ?? '');

// Features: filter empty, limit to 3
$features = array_slice(
    array_filter(array_map('trim', $_POST['features'] ?? [])),
    0,
    3
);

$errors = [];
if ($businessName === '') {
    $errors[] = '상호명을 입력해주세요.';
}
if ($phone === '') {
    $errors[] = '전화번호를 입력해주세요.';
}
if ($address === '') {
    $errors[] = '주소를 입력해주세요.';
}
if (empty($features)) {
    $errors[] = '특장점을 1개 이상 입력해주세요.';
}

if (!empty($errors)) {
    showError(implode('<br>', $errors));
}

// ===================================================================
// 2. Load industry preset
// ===================================================================
$preset = getPreset($industryKey);
if (!$preset) {
    $preset = getPreset('general_store');
}

// ===================================================================
// 3. Process uploaded images
// ===================================================================
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Logo
$logoPath = null;
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $logoPath = saveUploadedImage($_FILES['logo'], $uploadDir, $tempFiles);
}

// Photos (up to 4)
$photoPaths = [];
if (isset($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
    $photoCount = min(4, count($_FILES['photos']['name']));
    for ($i = 0; $i < $photoCount; $i++) {
        if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
            $singleFile = [
                'name'     => $_FILES['photos']['name'][$i],
                'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                'type'     => $_FILES['photos']['type'][$i],
                'error'    => $_FILES['photos']['error'][$i],
                'size'     => $_FILES['photos']['size'][$i],
            ];
            $path = saveUploadedImage($singleFile, $uploadDir, $tempFiles);
            if ($path !== null) {
                $photoPaths[] = $path;
            }
        }
    }
}

// Map image
$mapImagePath = null;
if (isset($_FILES['map_image']) && $_FILES['map_image']['error'] === UPLOAD_ERR_OK) {
    $mapImagePath = saveUploadedImage($_FILES['map_image'], $uploadDir, $tempFiles);
}

// ===================================================================
// 4. Generate QR code from website URL
// ===================================================================
$qrCodePath = null;
if ($websiteUrl !== '') {
    $qrApiUrl = 'https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl='
              . urlencode($websiteUrl);

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'Mozilla/5.0',
        ],
    ]);
    $qrData = @file_get_contents($qrApiUrl, false, $ctx);

    if ($qrData !== false && strlen($qrData) > 100) {
        $qrCodePath = $uploadDir . uniqid('qr_', true) . '.png';
        file_put_contents($qrCodePath, $qrData);
        $tempFiles[] = $qrCodePath;
    }
}

// ===================================================================
// 5. Build menu items array
// ===================================================================
$menuNames  = $_POST['menu_name'] ?? [];
$menuPrices = $_POST['menu_price'] ?? [];
$menuItems  = [];

$menuCount = max(count($menuNames), count($menuPrices));
for ($i = 0; $i < $menuCount; $i++) {
    $name  = trim($menuNames[$i] ?? '');
    $price = trim($menuPrices[$i] ?? '');
    if ($name !== '') {
        $menuItems[] = ['name' => $name, 'price' => $price];
    }
}

// ===================================================================
// 6. Page 2 decision logic
// ===================================================================
$overflowMenuItems = array_slice($menuItems, 12);
$needsPage2 = false;
if (count($photoPaths) > 0) {
    $needsPage2 = true;
}
if (!empty($mapImagePath)) {
    $needsPage2 = true;
}
if (count($menuItems) > 12) {
    $needsPage2 = true;
}

// ===================================================================
// 7. Render HTML from template
// ===================================================================
ob_start();
include __DIR__ . '/templates/pdf_template.php';
$html = ob_get_clean();

// ===================================================================
// 8. Generate PDF with mPDF
// ===================================================================
try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

    // Font configuration (following quote/standard/pdf.php pattern)
    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new \Mpdf\Mpdf([
        'mode'           => 'utf-8',
        'format'         => 'A4',
        'margin_left'    => 8,
        'margin_right'   => 8,
        'margin_top'     => 8,
        'margin_bottom'  => 8,
        'default_font'   => 'nanumgothic',
        'tempDir'        => sys_get_temp_dir() . '/mpdf',
        'fontDir'        => $fontDirs,
        'fontdata'       => $fontData + [
            'nanumgothic' => [
                'R' => 'NanumGothic.ttf',
                'B' => 'NanumGothicBold.ttf',
            ],
        ],
    ]);

    $mpdf->WriteHTML($html);

    // Build safe filename
    $safeName = preg_replace('/[^\w가-힣]/u', '_', $businessName);
    $safeName = preg_replace('/_+/', '_', $safeName);
    $safeName = trim($safeName, '_');
    if ($safeName === '') {
        $safeName = 'Flyer';
    }
    $filename = 'Flyer_' . $safeName . '_' . date('Ymd_His') . '.pdf';

    $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);

} catch (\Exception $e) {
    cleanupTempFiles($tempFiles);
    showError('PDF 생성 중 오류가 발생했습니다: ' . htmlspecialchars($e->getMessage()));
}

// ===================================================================
// 9. Clean up temp files
// ===================================================================
cleanupTempFiles($tempFiles);
exit;


// ===================================================================
// Helper Functions
// ===================================================================

/**
 * Save an uploaded image file with a unique prefix.
 *
 * @param  array  $file       Single $_FILES entry
 * @param  string $uploadDir  Target directory (with trailing slash)
 * @param  array  &$tempFiles Reference to temp-file tracking array
 * @return string|null        Absolute path on success, null on failure
 */
function saveUploadedImage(array $file, string $uploadDir, array &$tempFiles): ?string
{
    $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    if (!in_array($file['type'], $allowedTypes, true)) {
        return null;
    }

    // Max 10 MB
    if ($file['size'] > 10 * 1024 * 1024) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        $ext = 'jpg';
    }

    $destName = uniqid('flyer_', true) . '.' . $ext;
    $destPath = $uploadDir . $destName;

    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        $tempFiles[] = $destPath;
        return $destPath;
    }

    return null;
}

/**
 * Clean up temporary files created during PDF generation.
 *
 * @param array $files List of absolute file paths
 */
function cleanupTempFiles(array $files): void
{
    foreach ($files as $filePath) {
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }
}

/**
 * Show an error page or redirect back with error message, then exit.
 *
 * @param string $message Error message (may contain HTML)
 */
function showError(string $message): void
{
    // Attempt redirect to referring page
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer !== '') {
        $sep = (strpos($referer, '?') !== false) ? '&' : '?';
        header('Location: ' . $referer . $sep . 'error=' . urlencode(strip_tags($message)));
        exit;
    }

    // Fallback: simple error page
    http_response_code(400);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="ko"><head><meta charset="UTF-8">';
    echo '<title>전단지 생성 오류</title>';
    echo '<style>';
    echo 'body{font-family:"Malgun Gothic",sans-serif;padding:40pt;text-align:center;background:#f5f5f5;}';
    echo '.box{background:#fff;border:1px solid #e74c3c;padding:20pt;display:inline-block;border-radius:8pt;max-width:400pt;text-align:left;}';
    echo 'h3{color:#c0392b;margin-top:0;}';
    echo 'a{color:#2980b9;}';
    echo '</style></head><body>';
    echo '<div class="box">';
    echo '<h3>전단지 생성 오류</h3>';
    echo '<p>' . $message . '</p>';
    echo '<p><a href="javascript:history.back()">돌아가기</a></p>';
    echo '</div></body></html>';
    exit;
}
