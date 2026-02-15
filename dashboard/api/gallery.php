<?php
/**
 * 갤러리 관리 API
 * Actions: stats, list, upload, delete, get_settings, save_settings
 */
require_once __DIR__ . '/base.php';

// 제품키 → 실제 폴더명 매핑
$GALLERY_FOLDER_MAP = [
    'namecard'        => 'namecard',
    'sticker'         => 'sticker_new',
    'inserted'        => 'inserted',
    'envelope'        => 'envelope',
    'littleprint'     => 'littleprint',
    'merchandisebond' => 'merchandisebond',
    'cadarok'         => 'cadarok',
    'ncrflambeau'     => 'ncrflambeau',
    'msticker'        => 'msticker',
];

$ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
$SAMPLE_BASE = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/sample/';
$SAFEGALLERY_BASE = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/samplegallery/';
$SETTINGS_FILE = $_SERVER['DOCUMENT_ROOT'] . '/config/gallery_settings.json';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'stats':
        handleStats();
        break;
    case 'list':
        handleList();
        break;
    case 'upload':
        handleUpload();
        break;
    case 'delete':
        handleDelete();
        break;
    case 'replace':
        handleReplace();
        break;
    case 'get_settings':
        handleGetSettings();
        break;
    case 'save_settings':
        handleSaveSettings();
        break;
    default:
        jsonResponse(false, '유효하지 않은 액션입니다.');
}

// --- 설정 관리 ---

function loadSettings() {
    global $SETTINGS_FILE, $GALLERY_FOLDER_MAP;
    $defaults = [];
    foreach (array_keys($GALLERY_FOLDER_MAP) as $key) {
        $defaults[$key] = [
            'order_enabled' => true,
            'order_date_from' => '2024-01-01',
            'order_date_to' => '',
        ];
    }
    if (!file_exists($SETTINGS_FILE)) {
        return $defaults;
    }
    $json = file_get_contents($SETTINGS_FILE);
    $settings = json_decode($json, true);
    if (!is_array($settings)) {
        return $defaults;
    }
    // 누락된 제품 키 보충
    foreach ($defaults as $key => $def) {
        if (!isset($settings[$key])) {
            $settings[$key] = $def;
        }
    }
    return $settings;
}

function saveSettings($settings) {
    global $SETTINGS_FILE;
    $dir = dirname($SETTINGS_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return file_put_contents($SETTINGS_FILE, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

function handleGetSettings() {
    jsonResponse(true, 'OK', loadSettings());
}

function handleSaveSettings() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        jsonResponse(false, '유효하지 않은 데이터입니다.');
    }
    $current = loadSettings();
    foreach ($input as $key => $val) {
        if (!isset($current[$key])) continue;
        if (isset($val['order_enabled'])) {
            $current[$key]['order_enabled'] = (bool)$val['order_enabled'];
        }
        if (array_key_exists('order_date_from', $val)) {
            $current[$key]['order_date_from'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $val['order_date_from']) ? $val['order_date_from'] : '';
        }
        if (array_key_exists('order_date_to', $val)) {
            $current[$key]['order_date_to'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $val['order_date_to']) ? $val['order_date_to'] : '';
        }
    }
    if (saveSettings($current)) {
        jsonResponse(true, '설정이 저장되었습니다.', $current);
    } else {
        jsonResponse(false, '설정 저장에 실패했습니다.');
    }
}

// --- 기존 핸들러 (확장) ---

/**
 * 9개 제품별 샘플/안전갤러리/주문 이미지 수 + 설정
 */
function handleStats() {
    global $GALLERY_FOLDER_MAP, $SAMPLE_BASE, $SAFEGALLERY_BASE, $PRODUCT_TYPES, $db;

    $settings = loadSettings();
    $stats = [];
    foreach ($GALLERY_FOLDER_MAP as $key => $folder) {
        // 샘플 이미지
        $sampleDir = $SAMPLE_BASE . $folder . '/';
        $sampleCount = 0;
        $thumbnail = null;
        if (is_dir($sampleDir)) {
            $files = glob($sampleDir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            $sampleCount = count($files);
            if ($sampleCount > 0) {
                $thumbnail = '/ImgFolder/sample/' . $folder . '/' . rawurlencode(basename($files[0]));
            }
        }

        // 안전 갤러리 이미지
        $safeDir = $SAFEGALLERY_BASE . $folder . '/';
        $safeCount = 0;
        if (is_dir($safeDir)) {
            $safeFiles = glob($safeDir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            $safeCount = count($safeFiles);
            if (!$thumbnail && $safeCount > 0) {
                $thumbnail = '/ImgFolder/samplegallery/' . $folder . '/' . rawurlencode(basename($safeFiles[0]));
            }
        }

        // 주문 이미지 수 (DB) - 설정 반영
        $orderCount = 0;
        $productSetting = $settings[$key] ?? ['order_enabled' => true, 'order_date_from' => '', 'order_date_to' => ''];
        if ($productSetting['order_enabled'] && isset($PRODUCT_TYPES[$key])) {
            $orderCount = countOrderImages($key, $productSetting);
        }

        $stats[] = [
            'key'            => $key,
            'name'           => $PRODUCT_TYPES[$key]['name'] ?? $key,
            'folder'         => $folder,
            'sampleCount'    => $sampleCount,
            'safeCount'      => $safeCount,
            'orderCount'     => $orderCount,
            'orderEnabled'   => $productSetting['order_enabled'],
            'thumbnail'      => $thumbnail,
        ];
    }

    jsonResponse(true, 'OK', ['stats' => $stats, 'settings' => $settings]);
}

/**
 * 주문 이미지 수 카운트 (설정 기반 날짜 필터)
 */
function countOrderImages($product, $setting) {
    global $db;
    $productTypeMap = getProductTypeMap();
    if (!isset($productTypeMap[$product])) return 0;

    $conditions = array_map(function($t) use ($db) {
        return "Type LIKE '%" . mysqli_real_escape_string($db, $t) . "%'";
    }, $productTypeMap[$product]);
    $where = implode(' OR ', $conditions);

    $dateFrom = !empty($setting['order_date_from']) ? $setting['order_date_from'] : '2020-01-01';
    $dateTo = !empty($setting['order_date_to']) ? $setting['order_date_to'] : date('Y-m-d');

    $sql = "SELECT COUNT(*) as cnt FROM mlangorder_printauto
            WHERE ($where) AND ThingCate IS NOT NULL AND ThingCate != ''
            AND date >= '" . mysqli_real_escape_string($db, $dateFrom) . "'
            AND date <= '" . mysqli_real_escape_string($db, $dateTo) . " 23:59:59'";
    $r = mysqli_query($db, $sql);
    if ($r) {
        return (int)mysqli_fetch_assoc($r)['cnt'];
    }
    return 0;
}

function getProductTypeMap() {
    return [
        'inserted' => ['전단지', 'inserted'],
        'namecard' => ['명함', 'namecard'],
        'sticker'  => ['스티커', 'sticker'],
        'littleprint' => ['포스터', 'littleprint'],
        'merchandisebond' => ['상품권', 'merchandisebond'],
        'envelope' => ['봉투', 'envelope'],
        'cadarok'  => ['카탈로그', 'cadarok'],
        'ncrflambeau' => ['양식지', 'ncrflambeau'],
        'msticker' => ['자석스티커', 'msticker'],
    ];
}

/**
 * 특정 제품의 이미지 목록
 * GET params: product, source (sample|safegallery|order|all)
 */
function handleList() {
    global $GALLERY_FOLDER_MAP, $SAMPLE_BASE, $SAFEGALLERY_BASE, $db;

    $product = $_GET['product'] ?? '';
    $source  = $_GET['source'] ?? 'all';

    if (!isset($GALLERY_FOLDER_MAP[$product])) {
        jsonResponse(false, '유효하지 않은 제품입니다.');
    }

    $folder = $GALLERY_FOLDER_MAP[$product];
    $settings = loadSettings();
    $productSetting = $settings[$product] ?? ['order_enabled' => true, 'order_date_from' => '', 'order_date_to' => ''];
    $items = [];

    // 샘플 이미지
    if ($source === 'sample' || $source === 'all') {
        $dir = $SAMPLE_BASE . $folder . '/';
        if (is_dir($dir)) {
            $files = glob($dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            usort($files, function($a, $b) { return filemtime($b) - filemtime($a); });
            foreach ($files as $file) {
                $filename = basename($file);
                $items[] = [
                    'src'      => '/ImgFolder/sample/' . $folder . '/' . rawurlencode($filename),
                    'filename' => $filename,
                    'size'     => filesize($file),
                    'date'     => date('Y-m-d H:i', filemtime($file)),
                    'source'   => 'sample',
                ];
            }
        }
    }

    // 안전 갤러리 이미지 (수정일 최신순 — 제품 페이지 표시 순서와 동일)
    if ($source === 'safegallery' || $source === 'all') {
        $dir = $SAFEGALLERY_BASE . $folder . '/';
        if (is_dir($dir)) {
            $files = glob($dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            usort($files, function($a, $b) { return filemtime($b) - filemtime($a); });
            foreach ($files as $file) {
                $filename = basename($file);
                $items[] = [
                    'src'      => '/ImgFolder/samplegallery/' . $folder . '/' . rawurlencode($filename),
                    'filename' => $filename,
                    'size'     => filesize($file),
                    'date'     => date('Y-m-d H:i', filemtime($file)),
                    'source'   => 'safegallery',
                ];
            }
        }
    }

    // 주문 이미지 (설정 반영)
    if (($source === 'order' || $source === 'all') && $productSetting['order_enabled']) {
        $productTypeMap = getProductTypeMap();
        if (isset($productTypeMap[$product])) {
            $conditions = array_map(function($t) use ($db) {
                return "Type LIKE '%" . mysqli_real_escape_string($db, $t) . "%'";
            }, $productTypeMap[$product]);
            $where = implode(' OR ', $conditions);

            $dateFrom = !empty($productSetting['order_date_from']) ? $productSetting['order_date_from'] : '2020-01-01';
            $dateTo = !empty($productSetting['order_date_to']) ? $productSetting['order_date_to'] : date('Y-m-d');

            $sql = "SELECT no, ThingCate, name, date FROM mlangorder_printauto
                    WHERE ($where) AND ThingCate IS NOT NULL AND ThingCate != '' AND LENGTH(ThingCate) > 3
                    AND date >= '" . mysqli_real_escape_string($db, $dateFrom) . "'
                    AND date <= '" . mysqli_real_escape_string($db, $dateTo) . " 23:59:59'
                    ORDER BY no DESC LIMIT 100";
            $r = mysqli_query($db, $sql);
            if ($r) {
                while ($row = mysqli_fetch_assoc($r)) {
                    $imgPath = '/mlangorder_printauto/upload/' . $row['no'] . '/' . $row['ThingCate'];
                    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imgPath;
                    if (file_exists($fullPath)) {
                        $maskedName = !empty($row['name']) ? mb_substr($row['name'], 0, 1) . '***' : '고객';
                        $items[] = [
                            'src'      => $imgPath,
                            'filename' => $row['ThingCate'],
                            'size'     => filesize($fullPath),
                            'date'     => $row['date'],
                            'source'   => 'order',
                            'orderNo'  => $row['no'],
                            'customer' => $maskedName,
                        ];
                    }
                }
            }
        }
    }

    jsonResponse(true, 'OK', [
        'product' => $product,
        'source'  => $source,
        'total'   => count($items),
        'items'   => $items,
    ]);
}

/**
 * 이미지 업로드 (multipart)
 * POST params: product, target (sample|safegallery), files[]
 */
function handleUpload() {
    global $GALLERY_FOLDER_MAP, $SAMPLE_BASE, $SAFEGALLERY_BASE, $ALLOWED_EXTENSIONS, $MAX_FILE_SIZE;

    $product = $_POST['product'] ?? '';
    $target  = $_POST['target'] ?? 'sample';

    if (!isset($GALLERY_FOLDER_MAP[$product])) {
        jsonResponse(false, '유효하지 않은 제품입니다.');
    }
    if (!in_array($target, ['sample', 'safegallery'])) {
        jsonResponse(false, '유효하지 않은 업로드 대상입니다.');
    }

    if (empty($_FILES['files'])) {
        jsonResponse(false, '파일이 선택되지 않았습니다.');
    }

    $folder = $GALLERY_FOLDER_MAP[$product];
    $baseDir = ($target === 'safegallery') ? $SAFEGALLERY_BASE : $SAMPLE_BASE;
    $urlPrefix = ($target === 'safegallery') ? '/ImgFolder/samplegallery/' : '/ImgFolder/sample/';
    $targetDir = $baseDir . $folder . '/';

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $uploaded = [];
    $errors = [];
    $files = $_FILES['files'];
    $fileCount = is_array($files['name']) ? count($files['name']) : 1;

    for ($i = 0; $i < $fileCount; $i++) {
        $name    = is_array($files['name']) ? $files['name'][$i] : $files['name'];
        $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
        $error   = is_array($files['error']) ? $files['error'][$i] : $files['error'];
        $size    = is_array($files['size']) ? $files['size'][$i] : $files['size'];

        if ($error !== UPLOAD_ERR_OK) {
            $errors[] = "{$name}: 업로드 에러 (code: {$error})";
            continue;
        }
        if ($size > $MAX_FILE_SIZE) {
            $errors[] = "{$name}: 파일 크기 초과 (최대 10MB)";
            continue;
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $ALLOWED_EXTENSIONS)) {
            $errors[] = "{$name}: 허용되지 않는 확장자 ({$ext})";
            continue;
        }

        $imageInfo = @getimagesize($tmpName);
        if ($imageInfo === false) {
            $errors[] = "{$name}: 유효한 이미지 파일이 아닙니다.";
            continue;
        }

        $safeName = preg_replace('/[^a-zA-Z0-9가-힣ㄱ-ㅎㅏ-ㅣ_\-\.]/', '_', $name);
        $targetPath = $targetDir . $safeName;
        if (file_exists($targetPath)) {
            $baseName = pathinfo($safeName, PATHINFO_FILENAME);
            $safeName = $baseName . '_' . date('His') . '.' . $ext;
            $targetPath = $targetDir . $safeName;
        }

        $realTargetDir = realpath($targetDir);
        $realBaseDir = realpath($baseDir);
        if ($realTargetDir === false || $realBaseDir === false || strpos($realTargetDir, $realBaseDir) !== 0) {
            $errors[] = "{$name}: 잘못된 저장 경로";
            continue;
        }

        if (move_uploaded_file($tmpName, $targetPath)) {
            $uploaded[] = [
                'filename' => $safeName,
                'src'      => $urlPrefix . $folder . '/' . rawurlencode($safeName),
                'size'     => $size,
            ];
        } else {
            $errors[] = "{$name}: 파일 저장 실패";
        }
    }

    $msg = count($uploaded) . '개 파일 업로드 완료';
    if (!empty($errors)) {
        $msg .= ', ' . count($errors) . '개 실패';
    }

    jsonResponse(true, $msg, [
        'uploaded' => $uploaded,
        'errors'   => $errors,
    ]);
}

/**
 * 이미지 삭제 (sample + safegallery)
 * POST params: product, filename, source (sample|safegallery)
 */
function handleDelete() {
    global $GALLERY_FOLDER_MAP, $SAMPLE_BASE, $SAFEGALLERY_BASE;

    $product  = $_POST['product'] ?? '';
    $filename = $_POST['filename'] ?? '';
    $source   = $_POST['source'] ?? 'sample';

    if (!isset($GALLERY_FOLDER_MAP[$product])) {
        jsonResponse(false, '유효하지 않은 제품입니다.');
    }
    if (empty($filename)) {
        jsonResponse(false, '파일명이 지정되지 않았습니다.');
    }
    if (!in_array($source, ['sample', 'safegallery'])) {
        jsonResponse(false, '유효하지 않은 소스입니다.');
    }

    $folder = $GALLERY_FOLDER_MAP[$product];
    $baseDir = ($source === 'safegallery') ? $SAFEGALLERY_BASE : $SAMPLE_BASE;
    $targetPath = $baseDir . $folder . '/' . $filename;

    $realPath = realpath($targetPath);
    $realBase = realpath($baseDir);
    if ($realPath === false || $realBase === false) {
        jsonResponse(false, '파일을 찾을 수 없습니다.');
    }
    if (strpos($realPath, $realBase) !== 0) {
        jsonResponse(false, '잘못된 파일 경로입니다.');
    }

    $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        jsonResponse(false, '이미지 파일만 삭제 가능합니다.');
    }

    if (unlink($realPath)) {
        jsonResponse(true, '삭제 완료', ['filename' => $filename]);
    } else {
        jsonResponse(false, '파일 삭제에 실패했습니다.');
    }
}

/**
 * 이미지 교체 (기존 파일 삭제 + 새 파일 업로드)
 * POST params: product, source (sample|safegallery), old_filename, file
 */
function handleReplace() {
    global $GALLERY_FOLDER_MAP, $SAMPLE_BASE, $SAFEGALLERY_BASE, $ALLOWED_EXTENSIONS, $MAX_FILE_SIZE;

    $product = $_POST['product'] ?? '';
    $source = $_POST['source'] ?? 'sample';
    $oldFilename = $_POST['old_filename'] ?? '';

    if (!isset($GALLERY_FOLDER_MAP[$product])) {
        jsonResponse(false, '유효하지 않은 제품입니다.');
    }
    if (!in_array($source, ['sample', 'safegallery'])) {
        jsonResponse(false, '유효하지 않은 소스입니다.');
    }
    if (empty($oldFilename)) {
        jsonResponse(false, '기존 파일명이 지정되지 않았습니다.');
    }
    if (empty($_FILES['file'])) {
        jsonResponse(false, '파일이 선택되지 않았습니다.');
    }

    $folder = $GALLERY_FOLDER_MAP[$product];
    $baseDir = ($source === 'safegallery') ? $SAFEGALLERY_BASE : $SAMPLE_BASE;
    $targetDir = $baseDir . $folder . '/';
    $targetPath = $targetDir . $oldFilename;

    // 경로 검증 (directory traversal 방지)
    $realPath = realpath($targetPath);
    $realBase = realpath($baseDir);
    if ($realPath === false || $realBase === false) {
        jsonResponse(false, '파일을 찾을 수 없습니다.');
    }
    if (strpos($realPath, $realBase) !== 0) {
        jsonResponse(false, '잘못된 파일 경로입니다.');
    }

    // 업로드 파일 검증
    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(false, "업로드 에러 (code: {$file['error']})");
    }
    if ($file['size'] > $MAX_FILE_SIZE) {
        jsonResponse(false, '파일 크기 초과 (최대 10MB)');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $ALLOWED_EXTENSIONS)) {
        jsonResponse(false, "허용되지 않는 확장자 ({$ext})");
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        jsonResponse(false, '유효한 이미지 파일이 아닙니다.');
    }

    // 기존 파일 삭제
    if (!unlink($realPath)) {
        jsonResponse(false, '기존 파일 삭제 실패');
    }

    // 새 파일명 생성 (안전한 문자만)
    $safeName = preg_replace('/[^a-zA-Z0-9가-힣ㄱ-ㅎㅏ-ㅣ_\-\.]/u', '_', $file['name']);
    $newPath = $targetDir . $safeName;

    // 파일명 충돌 시 타임스탬프 추가
    if (file_exists($newPath)) {
        $baseName = pathinfo($safeName, PATHINFO_FILENAME);
        $safeName = $baseName . '_' . date('His') . '.' . $ext;
        $newPath = $targetDir . $safeName;
    }

    $urlPrefix = ($source === 'safegallery') ? '/ImgFolder/samplegallery/' : '/ImgFolder/sample/';

    if (move_uploaded_file($file['tmp_name'], $newPath)) {
        chmod($newPath, 0644);
        jsonResponse(true, '이미지 교체 완료', [
            'old_filename' => $oldFilename,
            'filename' => $safeName,
            'src' => $urlPrefix . $folder . '/' . rawurlencode($safeName),
            'size' => $file['size'],
            'date' => date('Y-m-d H:i')
        ]);
    } else {
        jsonResponse(false, '파일 저장 실패');
    }
}
