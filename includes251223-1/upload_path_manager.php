<?php
/**
 * ============================================================
 * @deprecated 2025-12-16
 * 이 파일은 더 이상 사용되지 않습니다.
 * 대신 UploadPathHelper.php를 사용하세요.
 *
 * 마이그레이션 방법:
 * - include "upload_path_manager.php";
 * + require_once "UploadPathHelper.php";
 *
 * 모든 함수가 UploadPathHelper 클래스에 통합되었습니다:
 * - generateUploadPath() -> UploadPathHelper::generateUploadPath()
 * - generateUniqueFilename() -> UploadPathHelper::generateUniqueFilename()
 * - createUploadDirectory() -> UploadPathHelper::createDirectory()
 * - getCurrentDomain() -> UploadPathHelper::getCurrentDomain()
 * - getFileDownloadUrl() -> UploadPathHelper::getFileDownloadUrl()
 * ============================================================
 *
 * Image Upload Path Manager (DEPRECATED)
 * 레거시 MlangPrintAuto 경로 구조와의 하위 호환성 유지
 *
 * @package DusonPrint
 * @version 1.0.0
 * @date 2025-01-14
 * @deprecated Use UploadPathHelper.php instead
 */

// 보안: 직접 접근 방지 (웹 브라우저에서 직접 접근 시에만 차단)
if (!defined('ALLOW_ACCESS') && php_sapi_name() !== 'cli') {
    // $_SERVER['SCRIPT_FILENAME']이 이 파일 자체를 가리키면 직접 접근
    if (isset($_SERVER['SCRIPT_FILENAME']) && 
        realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
        die('Direct access not permitted');
    }
}

/**
 * 로그 정보 생성 (upload_path_manager 전용)
 * 경로 생성에 필요한 연도, 월일, IP, 시간 정보 반환
 *
 * @return array 경로 생성 정보 ['y' => 연도, 'md' => 월일, 'ip' => IP, 'time' => 시분초]
 */
if (!function_exists('generateLogInfo')) {
    function generateLogInfo() {
        return [
            'y' => date('Y'),
            'md' => date('md'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'time' => date('His')
        ];
    }
}

/**
 * 품목별 레거시 경로 매핑
 * 소문자 품목명 → 대문자 레거시 경로 형식
 */
define('PRODUCT_PATH_MAP', [
    'inserted'        => '_MlangPrintAuto_inserted_index.php',
    'namecard'        => '_MlangPrintAuto_NameCard_index.php',
    'sticker_new'     => '_MlangPrintAuto_sticker_new_index.php',
    'sticker'         => '_MlangPrintAuto_sticker_new_index.php', // 별칭
    'envelope'        => '_MlangPrintAuto_envelope_index.php',
    'cadarok'         => '_MlangPrintAuto_cadarok_index.php',
    'littleprint'     => '_MlangPrintAuto_littleprint_index.php',
    'ncrflambeau'     => '_MlangPrintAuto_ncrflambeau_index.php',
    'merchandisebond' => '_MlangPrintAuto_merchandisebond_index.php',
    'msticker'        => '_MlangPrintAuto_msticker_index.php'
]);

/**
 * Document Root 경로 가져오기 (환경별 대응)
 *
 * @return string Document root 경로
 */
function getDocumentRoot() {
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        return $_SERVER['DOCUMENT_ROOT'];
    }
    // CLI 또는 DOCUMENT_ROOT가 없는 경우 기본 경로
    return file_exists('/www') ? '/www' : '/var/www/html';
}

/**
 * 레거시 호환 업로드 경로 생성
 *
 * @param string $product_type 품목 타입 (소문자)
 * @return array 업로드 경로 정보
 * @throws Exception 알 수 없는 품목 타입인 경우
 */
function generateUploadPath($product_type) {
    if (!isset(PRODUCT_PATH_MAP[$product_type])) {
        error_log("Unknown product type: {$product_type}");
        throw new Exception("알 수 없는 품목 타입입니다: {$product_type}");
    }

    $log_info = generateLogInfo();
    $legacy_prefix = PRODUCT_PATH_MAP[$product_type];

    // 레거시 형식: _MlangPrintAuto_{품목}_index.php/YYYY/MMDD/IP/HHMMSS
    $img_folder = "{$legacy_prefix}/{$log_info['y']}/{$log_info['md']}/{$log_info['ip']}/{$log_info['time']}";

    // 실제 파일 시스템 경로 (dsp1830.shop 서버 기준)
    $document_root = getDocumentRoot();
    $physical_path = $document_root . "/ImgFolder/{$img_folder}/";

    // 웹 접근 가능 경로
    $web_path = "/ImgFolder/{$img_folder}/";

    return [
        'img_folder' => $img_folder,           // DB 저장용
        'physical_path' => $physical_path,     // 파일 작업용
        'web_path' => $web_path,               // URL 생성용
        'legacy_prefix' => $legacy_prefix,      // 레거시 품목 접두사
        'timestamp' => $log_info['time'],       // 파일명 생성용
        'date_info' => [
            'year' => $log_info['y'],
            'month_day' => $log_info['md'],
            'ip' => $log_info['ip']
        ]
    ];
}

/**
 * 타임스탬프 기반 고유 파일명 생성
 * 레거시 형식: {랜덤2자리}{YYYYMMDDHHMMSS}.{확장자}
 *
 * @param string $original_filename 원본 파일명
 * @param string $timestamp 업로드 경로의 타임스탬프 (선택)
 * @return string 고유 파일명
 */
function generateUniqueFilename($original_filename, $timestamp = null) {
    $extension = pathinfo($original_filename, PATHINFO_EXTENSION);

    // 확장자가 없는 경우 처리
    if (empty($extension)) {
        $extension = 'file';
    }

    // 레거시 형식: {랜덤}{YYYYMMDDHHMMSS}.{확장자}
    // 예: 4820251105180650.jpg
    $random_prefix = rand(10, 99);
    $datestamp = date('YmdHis');

    return "{$random_prefix}{$datestamp}.{$extension}";
}

/**
 * 업로드 디렉토리 생성
 *
 * @param string $physical_path 전체 물리 경로
 * @return bool 성공 여부
 */
function createUploadDirectory($physical_path) {
    if (file_exists($physical_path)) {
        return true;
    }

    // 재귀적으로 디렉토리 생성 (0755 권한)
    if (mkdir($physical_path, 0755, true)) {
        error_log("Upload directory created: {$physical_path}");
        return true;
    }

    error_log("Failed to create upload directory: {$physical_path}");
    return false;
}

/**
 * 현재 도메인 동적 감지
 * 로컬, 테스트, 운영 서버 자동 대응
 *
 * @return string 현재 접속 중인 도메인 (프로토콜 포함)
 */
function getCurrentDomain() {
    // CLI 환경에서는 기본값 반환
    if (php_sapi_name() === 'cli' || !isset($_SERVER['HTTP_HOST'])) {
        return "http://dsp1830.shop";
    }

    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];

    return "{$protocol}://{$host}";
}

/**
 * 파일 다운로드 URL 생성
 *
 * @param string $img_folder DB의 ImgFolder 필드값
 * @param string $filename DB의 ThingCate 필드값 (파일명)
 * @return string 전체 다운로드 URL
 */
function getFileDownloadUrl($img_folder, $filename) {
    // URL 인코딩 (한글 파일명 지원)
    $encoded_filename = rawurlencode($filename);

    // 동적 도메인 감지 (로컬, 테스트, 운영 서버 자동 대응)
    $domain = getCurrentDomain();

    return "{$domain}/ImgFolder/{$img_folder}/{$encoded_filename}";
}

/**
 * 업로드된 파일 존재 확인
 *
 * @param string $img_folder DB의 ImgFolder 필드값
 * @param string $filename DB의 ThingCate 필드값
 * @return bool 파일 존재 여부
 */
function verifyUploadedFile($img_folder, $filename) {
    $document_root = getDocumentRoot();
    $physical_path = $document_root . "/ImgFolder/{$img_folder}/{$filename}";

    $exists = file_exists($physical_path);

    if (!$exists) {
        error_log("File not found: {$physical_path}");
    }

    return $exists;
}

/**
 * 업로드된 파일 크기 조회 (읽기 쉬운 형식)
 *
 * @param string $img_folder DB의 ImgFolder 필드값
 * @param string $filename DB의 ThingCate 필드값
 * @return string|null 파일 크기 또는 null
 */
function getUploadedFileSize($img_folder, $filename) {
    $document_root = getDocumentRoot();
    $physical_path = $document_root . "/ImgFolder/{$img_folder}/{$filename}";

    if (!file_exists($physical_path)) {
        return null;
    }

    $bytes = filesize($physical_path);

    if ($bytes === false) {
        return null;
    }

    // 단위 변환
    $units = ['B', 'KB', 'MB', 'GB'];
    $factor = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
    $factor = min($factor, count($units) - 1);

    return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
}

/**
 * 파일 MIME 타입 조회
 *
 * @param string $img_folder DB의 ImgFolder 필드값
 * @param string $filename DB의 ThingCate 필드값
 * @return string|null MIME 타입 또는 null
 */
function getUploadedFileMimeType($img_folder, $filename) {
    $document_root = getDocumentRoot();
    $physical_path = $document_root . "/ImgFolder/{$img_folder}/{$filename}";

    if (!file_exists($physical_path)) {
        return null;
    }

    // finfo 사용 (PHP 5.3+)
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $physical_path);
        finfo_close($finfo);
        return $mime;
    }

    // mime_content_type 사용 (대체)
    if (function_exists('mime_content_type')) {
        return mime_content_type($physical_path);
    }

    // 확장자 기반 추측 (최후 수단)
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $mime_map = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'pdf' => 'application/pdf',
        'ai' => 'application/postscript',
        'psd' => 'image/vnd.adobe.photoshop',
        'zip' => 'application/zip'
    ];

    return $mime_map[$extension] ?? 'application/octet-stream';
}

/**
 * 업로드 경로 정보 검증
 *
 * @param array $upload_path_info generateUploadPath() 반환값
 * @return array 검증 결과 ['valid' => bool, 'errors' => array]
 */
function validateUploadPathInfo($upload_path_info) {
    $errors = [];

    // 필수 키 검증
    $required_keys = ['img_folder', 'physical_path', 'web_path', 'legacy_prefix', 'timestamp'];
    foreach ($required_keys as $key) {
        if (!isset($upload_path_info[$key])) {
            $errors[] = "필수 키 누락: {$key}";
        }
    }

    // img_folder 형식 검증 (레거시 형식 확인)
    if (isset($upload_path_info['img_folder'])) {
        if (!preg_match('/^_MlangPrintAuto_\w+_index\.php\/\d{4}\/\d{4}\/[\d.]+\/\d{6}$/', $upload_path_info['img_folder'])) {
            $errors[] = "img_folder 형식이 레거시 형식과 일치하지 않습니다: {$upload_path_info['img_folder']}";
        }
    }

    // 디렉토리 쓰기 권한 검증
    if (isset($upload_path_info['physical_path'])) {
        $parent_dir = dirname($upload_path_info['physical_path']);
        if (file_exists($parent_dir) && !is_writable($parent_dir)) {
            $errors[] = "디렉토리 쓰기 권한 없음: {$parent_dir}";
        }
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * 디버그: 업로드 경로 정보 로깅
 *
 * @param array $upload_path_info generateUploadPath() 반환값
 * @param string $context 컨텍스트 정보
 */
function debugUploadPathInfo($upload_path_info, $context = '') {
    $prefix = $context ? "[{$context}] " : '';
    error_log($prefix . "Upload Path Debug Info:");
    error_log("  - img_folder: " . ($upload_path_info['img_folder'] ?? 'N/A'));
    error_log("  - physical_path: " . ($upload_path_info['physical_path'] ?? 'N/A'));
    error_log("  - web_path: " . ($upload_path_info['web_path'] ?? 'N/A'));
    error_log("  - legacy_prefix: " . ($upload_path_info['legacy_prefix'] ?? 'N/A'));
    error_log("  - timestamp: " . ($upload_path_info['timestamp'] ?? 'N/A'));
}

/**
 * 레거시 경로에서 품목 타입 추출
 *
 * @param string $img_folder DB의 ImgFolder 필드값
 * @return string|null 품목 타입 또는 null
 */
function extractProductTypeFromPath($img_folder) {
    // 경로에서 레거시 접두사 추출
    // 예: _MlangPrintAuto_NameCard_index.php/2025/... → namecard
    if (preg_match('/^_MlangPrintAuto_(\w+)_index\.php/', $img_folder, $matches)) {
        $legacy_name = $matches[1];

        // 레거시 이름 → 소문자 품목명 매핑
        $reverse_map = [
            'inserted' => 'inserted',
            'NameCard' => 'namecard',
            'sticker_new' => 'sticker_new',
            'envelope' => 'envelope',
            'cadarok' => 'cadarok',
            'littleprint' => 'littleprint',
            'ncrflambeau' => 'ncrflambeau',
            'merchandisebond' => 'merchandisebond',
            'msticker' => 'msticker'
        ];

        return $reverse_map[$legacy_name] ?? null;
    }

    return null;
}

// 사용 예시 주석
/*
사용 예시:

// 1. 업로드 경로 생성
$upload_info = generateUploadPath('namecard');
// 결과: [
//   'img_folder' => '_MlangPrintAuto_NameCard_index.php/2025/0114/112.185.73.148/171759',
//   'physical_path' => '/www/ImgFolder/_MlangPrintAuto_NameCard_index.php/2025/0114/112.185.73.148/171759/',
//   'web_path' => '/ImgFolder/_MlangPrintAuto_NameCard_index.php/2025/0114/112.185.73.148/171759/',
//   'legacy_prefix' => '_MlangPrintAuto_NameCard_index.php',
//   'timestamp' => '171759'
// ]

// 2. 디렉토리 생성
createUploadDirectory($upload_info['physical_path']);

// 3. 고유 파일명 생성
$unique_filename = generateUniqueFilename('명함 도안.pdf', $upload_info['timestamp']);
// 결과: '4820250114171759.pdf'

// 4. 파일 업로드 후 DB 저장
$img_folder = $upload_info['img_folder'];
$thing_cate = $unique_filename;

// 5. 다운로드 URL 생성
$download_url = getFileDownloadUrl($img_folder, $thing_cate);
// 결과: 'http://dsp114.com/ImgFolder/_MlangPrintAuto_NameCard_index.php/2025/0114/112.185.73.148/171759/4820250114171759.pdf'
*/
