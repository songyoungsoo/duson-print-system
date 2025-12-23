<?php
/**
 * 파일 업로드 경로 표준화 헬퍼 클래스
 * 전체 9개 품목의 파일 업로드 경로를 통일된 규칙으로 관리
 *
 * 경로 구조: /ImgFolder/_MlangPrintAuto_{product}_index.php/{year}/{mmdd}/{ip}/{timestamp}/{filename}
 */

class UploadPathHelper {

    /**
     * 품목별 디렉토리명 매핑
     *
     * @var array
     */
    private static $productPaths = [
        'inserted'        => '_MlangPrintAuto_inserted_index.php',
        'namecard'        => '_MlangPrintAuto_namecard_index.php',
        'envelope'        => '_MlangPrintAuto_envelope_index.php',
        'sticker'         => '_MlangPrintAuto_sticker_new_index.php',
        'msticker'        => '_MlangPrintAuto_msticker_index.php',
        'cadarok'         => '_MlangPrintAuto_cadarok_index.php',
        'littleprint'     => '_MlangPrintAuto_littleprint_index.php',
        'ncrflambeau'     => '_MlangPrintAuto_ncrflambeau_index.php',
        'merchandisebond' => '_MlangPrintAuto_merchandisebond_index.php'
    ];

    /**
     * 기본 루트 경로 (동적으로 설정됨)
     *
     * @var string|null
     */
    private static $baseRoot = null;

    /**
     * 웹 접근 기본 경로
     *
     * @var string
     */
    private static $webRoot = '/ImgFolder';

    /**
     * Document Root 기반 절대 경로 반환 (환경 자동 감지)
     * - Local: /var/www/html/ImgFolder
     * - Production: /dsp1830/www/ImgFolder
     *
     * @return string
     */
    private static function getBaseRoot() {
        if (self::$baseRoot === null) {
            self::$baseRoot = $_SERVER['DOCUMENT_ROOT'] . self::$webRoot;
            error_log("UploadPathHelper: DOCUMENT_ROOT = " . $_SERVER['DOCUMENT_ROOT']);
            error_log("UploadPathHelper: Base Root = " . self::$baseRoot);
        }
        return self::$baseRoot;
    }

    /**
     * 품목별 업로드 경로 생성
     *
     * @param string $product 품목 코드 (inserted, namecard, envelope 등)
     * @param string $ip 클라이언트 IP 주소
     * @param int|null $timestamp Unix 타임스탬프 (null이면 현재 시간 사용)
     * @return array ['full_path' => 절대경로, 'web_path' => 웹경로, 'db_path' => DB저장경로]
     */
    public static function generateUploadPath($product, $ip = null, $timestamp = null) {
        // 품목 검증
        if (!isset(self::$productPaths[$product])) {
            throw new Exception("Invalid product: $product");
        }

        // IP 주소 처리
        if ($ip === null) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }

        // IPv6 주소 변환 로직 (파일시스템 안전 변환)
        if ($ip === '::1') {
            $ip = 'ipv6_1';
        } elseif (strpos($ip, ':') !== false) {
            // 일반 IPv6 주소 (2001:db8::1 등)
            $ip = 'ipv6_' . str_replace(':', '_', $ip);
        }

        // 파일시스템 안전 문자로 정규화
        $ip = preg_replace('/[^a-zA-Z0-9._-]/', '_', $ip);

        // 타임스탬프 처리
        if ($timestamp === null) {
            $timestamp = time();
        }

        // 날짜 정보 생성
        $year = date('Y', $timestamp);
        $mmdd = date('md', $timestamp);

        // 경로 구성 요소
        $productDir = self::$productPaths[$product];
        $pathComponents = [
            $productDir,
            $year,
            $mmdd,
            $ip,
            $timestamp
        ];

        // 경로 조합
        $relativePath = implode('/', $pathComponents);
        $fullPath = self::getBaseRoot() . '/' . $relativePath;
        $webPath = self::$webRoot . '/' . $relativePath;

        return [
            'full_path' => $fullPath,      // /var/www/html/ImgFolder/_MlangPrintAuto_inserted_index.php/2025/1113/...
            'web_path'  => $webPath,       // /ImgFolder/_MlangPrintAuto_inserted_index.php/2025/1113/...
            'db_path'   => $relativePath,  // _MlangPrintAuto_inserted_index.php/2025/1113/... (DB 저장용)
            'product_dir' => $productDir,
            'year' => $year,
            'mmdd' => $mmdd,
            'ip' => $ip,
            'timestamp' => $timestamp
        ];
    }

    /**
     * 디렉토리 생성 (존재하지 않으면 생성)
     *
     * @param string $path 생성할 디렉토리 경로
     * @param int $permissions 권한 (기본: 0755)
     * @return bool 성공 여부
     */
    public static function createDirectory($path, $permissions = 0755) {
        if (!file_exists($path)) {
            return mkdir($path, $permissions, true);
        }
        return true;
    }

    /**
     * 파일 업로드 처리 (통합)
     *
     * @param string $product 품목 코드
     * @param array $file $_FILES 배열의 개별 파일 정보
     * @param string|null $customFilename 커스텀 파일명 (null이면 원본 파일명 사용)
     * @return array ['success' => bool, 'path' => 저장경로, 'filename' => 파일명, 'error' => 에러메시지]
     */
    public static function uploadFile($product, $file, $customFilename = null) {
        // 파일 업로드 에러 체크
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => 'File upload error: ' . $file['error']
            ];
        }

        // 경로 생성
        $paths = self::generateUploadPath($product);

        // 디렉토리 생성
        if (!self::createDirectory($paths['full_path'])) {
            return [
                'success' => false,
                'error' => 'Failed to create directory: ' . $paths['full_path']
            ];
        }

        // 파일명 처리
        $filename = $customFilename ?? basename($file['name']);
        $targetPath = $paths['full_path'] . '/' . $filename;

        // 파일 이동
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [
                'success' => true,
                'full_path' => $targetPath,
                'web_path' => $paths['web_path'] . '/' . $filename,
                'db_img_folder' => $paths['db_path'],  // mlangorder_printauto.ImgFolder 컬럼값
                'db_thing_cate' => $filename,           // mlangorder_printauto.ThingCate 컬럼값
                'filename' => $filename
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to move uploaded file'
            ];
        }
    }

    /**
     * DB에서 저장된 경로 정보로 실제 파일 경로 복원 (레거시 경로 호환)
     *
     * @param string $imgFolder ImgFolder 컬럼값
     * @param string $filename ThingCate 컬럼값 (파일명)
     * @return array ['full_path' => 절대경로, 'web_path' => 웹경로, 'exists' => 파일존재여부]
     */
    public static function getFilePathFromDB($imgFolder, $filename) {
        $fullPath = self::getBaseRoot() . '/' . $imgFolder . '/' . $filename;
        $webPath = self::$webRoot . '/' . $imgFolder . '/' . $filename;

        // 파일이 존재하면 바로 반환
        if (file_exists($fullPath)) {
            return [
                'full_path' => $fullPath,
                'web_path' => $webPath,
                'exists' => true,
                'url' => $webPath
            ];
        }

        // ✅ 레거시 경로 호환: 대문자/소문자 변형 시도
        // 예: _MlangPrintAuto_NameCard_index.php ↔ _MlangPrintAuto_namecard_index.php
        $legacyMappings = [
            '_MlangPrintAuto_NameCard_index.php' => '_MlangPrintAuto_namecard_index.php',
            '_MlangPrintAuto_namecard_index.php' => '_MlangPrintAuto_NameCard_index.php',
            // 필요시 다른 제품 추가
        ];

        foreach ($legacyMappings as $oldPattern => $newPattern) {
            if (strpos($imgFolder, $oldPattern) !== false) {
                $alternativePath = str_replace($oldPattern, $newPattern, $imgFolder);
                $alternativeFullPath = self::getBaseRoot() . '/' . $alternativePath . '/' . $filename;
                $alternativeWebPath = self::$webRoot . '/' . $alternativePath . '/' . $filename;

                if (file_exists($alternativeFullPath)) {
                    return [
                        'full_path' => $alternativeFullPath,
                        'web_path' => $alternativeWebPath,
                        'exists' => true,
                        'url' => $alternativeWebPath
                    ];
                }
            }
        }

        // 파일을 찾지 못한 경우 원래 경로 반환 (exists = false)
        return [
            'full_path' => $fullPath,
            'web_path' => $webPath,
            'exists' => false,
            'url' => $webPath
        ];
    }

    /**
     * 품목 코드 검증
     *
     * @param string $product 품목 코드
     * @return bool 유효 여부
     */
    public static function isValidProduct($product) {
        return isset(self::$productPaths[$product]);
    }

    /**
     * 지원하는 모든 품목 목록 반환
     *
     * @return array 품목 코드 배열
     */
    public static function getAllProducts() {
        return array_keys(self::$productPaths);
    }

    /**
     * 품목별 디렉토리명 반환
     *
     * @param string $product 품목 코드
     * @return string|null 디렉토리명
     */
    public static function getProductDirectory($product) {
        return self::$productPaths[$product] ?? null;
    }

    /**
     * 다중 파일 업로드 처리
     *
     * @param string $product 품목 코드
     * @param array $files $_FILES 배열 (multiple upload)
     * @param array|null $customFilenames 커스텀 파일명 배열 (null이면 원본 파일명 사용)
     * @return array ['success' => bool, 'uploaded' => array, 'failed' => array, 'total' => int]
     */
    public static function uploadMultipleFiles($product, $files, $customFilenames = null) {
        $results = [
            'success' => true,
            'uploaded' => [],
            'failed' => [],
            'total' => 0
        ];

        // 파일 배열 정규화 (단일 파일 또는 다중 파일 처리)
        $normalizedFiles = self::normalizeFilesArray($files);
        $results['total'] = count($normalizedFiles);

        foreach ($normalizedFiles as $index => $file) {
            $customFilename = null;
            if ($customFilenames && isset($customFilenames[$index])) {
                $customFilename = $customFilenames[$index];
            }

            $result = self::uploadFile($product, $file, $customFilename);

            if ($result['success']) {
                $results['uploaded'][] = $result;
            } else {
                $results['failed'][] = [
                    'filename' => $file['name'] ?? 'unknown',
                    'error' => $result['error']
                ];
                $results['success'] = false;
            }
        }

        return $results;
    }

    /**
     * $_FILES 배열 정규화 (다양한 형식 지원)
     *
     * @param array $files $_FILES 배열
     * @return array 정규화된 파일 배열
     */
    private static function normalizeFilesArray($files) {
        $normalized = [];

        // 단일 파일: ['name' => 'file.jpg', 'type' => '...', ...]
        if (isset($files['name']) && !is_array($files['name'])) {
            return [$files];
        }

        // 다중 파일: ['name' => ['file1.jpg', 'file2.jpg'], 'type' => [...], ...]
        if (isset($files['name']) && is_array($files['name'])) {
            $fileCount = count($files['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                $normalized[] = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
            }
            return $normalized;
        }

        // 이미 정규화된 배열
        return $files;
    }

    /**
     * 주문 번호로 모든 파일 조회
     *
     * @param mysqli $db 데이터베이스 연결
     * @param int $orderNo 주문 번호
     * @return array 파일 정보 배열
     */
    public static function getOrderFiles($db, $orderNo) {
        $files = [];

        // mlangorder_printauto 테이블에서 파일 정보 조회
        $stmt = mysqli_prepare($db, "
            SELECT no, ImgFolder, ThingCate, product_type
            FROM mlangorder_printauto
            WHERE no = ?
        ");

        if (!$stmt) {
            return $files;
        }

        mysqli_stmt_bind_param($stmt, "i", $orderNo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['ImgFolder'] && $row['ThingCate']) {
                $fileInfo = self::getFilePathFromDB($row['ImgFolder'], $row['ThingCate']);
                $files[] = [
                    'order_no' => $row['no'],
                    'filename' => $row['ThingCate'],
                    'product_type' => $row['product_type'],
                    'full_path' => $fileInfo['full_path'],
                    'web_path' => $fileInfo['web_path'],
                    'exists' => $fileInfo['exists'],
                    'url' => $fileInfo['url']
                ];
            }
        }

        mysqli_stmt_close($stmt);
        return $files;
    }

    /**
     * 다중 파일을 ZIP으로 압축하여 다운로드
     *
     * @param array $files 파일 정보 배열 (getOrderFiles 결과)
     * @param string $zipFilename 생성할 ZIP 파일명
     * @return array ['success' => bool, 'zip_path' => string, 'error' => string]
     */
    public static function createZipArchive($files, $zipFilename = 'download.zip') {
        // 임시 디렉토리 생성
        $tempDir = sys_get_temp_dir() . '/mlang_downloads';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . '/' . $zipFilename;

        // 기존 ZIP 파일 삭제
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
            return [
                'success' => false,
                'error' => 'ZIP 파일 생성 실패'
            ];
        }

        $addedCount = 0;
        foreach ($files as $file) {
            if ($file['exists'] && file_exists($file['full_path'])) {
                // 중복 방지를 위해 파일명에 인덱스 추가
                $filename = $file['filename'];
                $counter = 1;
                while ($zip->locateName($filename) !== false) {
                    $pathInfo = pathinfo($file['filename']);
                    $filename = $pathInfo['filename'] . '_' . $counter . '.' . $pathInfo['extension'];
                    $counter++;
                }

                $zip->addFile($file['full_path'], $filename);
                $addedCount++;
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            unlink($zipPath);
            return [
                'success' => false,
                'error' => '압축할 파일이 없습니다'
            ];
        }

        return [
            'success' => true,
            'zip_path' => $zipPath,
            'file_count' => $addedCount
        ];
    }

    /**
     * ZIP 파일 다운로드 전송
     *
     * @param string $zipPath ZIP 파일 경로
     * @param string $downloadName 다운로드 파일명
     * @return void
     */
    public static function sendZipDownload($zipPath, $downloadName = 'files.zip') {
        if (!file_exists($zipPath)) {
            header('HTTP/1.1 404 Not Found');
            echo 'File not found';
            return;
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Length: ' . filesize($zipPath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');

        readfile($zipPath);

        // 다운로드 후 임시 파일 삭제
        unlink($zipPath);
    }

    // ========================================
    // upload_path_manager.php 통합 함수들
    // ========================================

    /**
     * 타임스탬프 기반 고유 파일명 생성
     * 레거시 형식: {랜덤2자리}{YYYYMMDDHHMMSS}.{확장자}
     *
     * @param string $originalFilename 원본 파일명
     * @param string|null $timestamp 업로드 경로의 타임스탬프 (선택)
     * @return string 고유 파일명
     */
    public static function generateUniqueFilename($originalFilename, $timestamp = null) {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);

        if (empty($extension)) {
            $extension = 'file';
        }

        $randomPrefix = rand(10, 99);
        $datestamp = date('YmdHis');

        return "{$randomPrefix}{$datestamp}.{$extension}";
    }

    /**
     * 현재 도메인 동적 감지
     * 로컬, 테스트, 운영 서버 자동 대응
     *
     * @return string 현재 접속 중인 도메인 (프로토콜 포함)
     */
    public static function getCurrentDomain() {
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
     * @param string $imgFolder DB의 ImgFolder 필드값
     * @param string $filename DB의 ThingCate 필드값 (파일명)
     * @return string 전체 다운로드 URL
     */
    public static function getFileDownloadUrl($imgFolder, $filename) {
        $encodedFilename = rawurlencode($filename);
        $domain = self::getCurrentDomain();

        return "{$domain}/ImgFolder/{$imgFolder}/{$encodedFilename}";
    }

    /**
     * 업로드된 파일 존재 확인
     *
     * @param string $imgFolder DB의 ImgFolder 필드값
     * @param string $filename DB의 ThingCate 필드값
     * @return bool 파일 존재 여부
     */
    public static function verifyUploadedFile($imgFolder, $filename) {
        $fullPath = self::getBaseRoot() . '/' . $imgFolder . '/' . $filename;
        $exists = file_exists($fullPath);

        if (!$exists) {
            error_log("UploadPathHelper: File not found - {$fullPath}");
        }

        return $exists;
    }

    /**
     * 업로드된 파일 크기 조회 (읽기 쉬운 형식)
     *
     * @param string $imgFolder DB의 ImgFolder 필드값
     * @param string $filename DB의 ThingCate 필드값
     * @return string|null 파일 크기 또는 null
     */
    public static function getUploadedFileSize($imgFolder, $filename) {
        $fullPath = self::getBaseRoot() . '/' . $imgFolder . '/' . $filename;

        if (!file_exists($fullPath)) {
            return null;
        }

        $bytes = filesize($fullPath);
        if ($bytes === false) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $factor = min($factor, count($units) - 1);

        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * 파일 MIME 타입 조회
     *
     * @param string $imgFolder DB의 ImgFolder 필드값
     * @param string $filename DB의 ThingCate 필드값
     * @return string|null MIME 타입 또는 null
     */
    public static function getUploadedFileMimeType($imgFolder, $filename) {
        $fullPath = self::getBaseRoot() . '/' . $imgFolder . '/' . $filename;

        if (!file_exists($fullPath)) {
            return null;
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $fullPath);
            finfo_close($finfo);
            return $mime;
        }

        if (function_exists('mime_content_type')) {
            return mime_content_type($fullPath);
        }

        // 확장자 기반 추측 (최후 수단)
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'ai' => 'application/postscript',
            'psd' => 'image/vnd.adobe.photoshop',
            'zip' => 'application/zip'
        ];

        return $mimeMap[$extension] ?? 'application/octet-stream';
    }

    /**
     * 레거시 경로에서 품목 타입 추출
     *
     * @param string $imgFolder DB의 ImgFolder 필드값
     * @return string|null 품목 타입 또는 null
     */
    public static function extractProductTypeFromPath($imgFolder) {
        if (preg_match('/^_MlangPrintAuto_(\w+)_index\.php/', $imgFolder, $matches)) {
            $legacyName = $matches[1];

            // 레거시 이름 → 소문자 품목명 매핑
            $reverseMap = [
                'inserted' => 'inserted',
                'NameCard' => 'namecard',
                'namecard' => 'namecard',
                'sticker_new' => 'sticker',
                'envelope' => 'envelope',
                'cadarok' => 'cadarok',
                'littleprint' => 'littleprint',
                'ncrflambeau' => 'ncrflambeau',
                'merchandisebond' => 'merchandisebond',
                'msticker' => 'msticker'
            ];

            return $reverseMap[$legacyName] ?? null;
        }

        return null;
    }

    /**
     * 디버그: 업로드 경로 정보 로깅
     *
     * @param array $pathInfo generateUploadPath() 반환값
     * @param string $context 컨텍스트 정보
     */
    public static function debugUploadPathInfo($pathInfo, $context = '') {
        $prefix = $context ? "[{$context}] " : '';
        error_log($prefix . "UploadPathHelper Debug Info:");
        error_log("  - full_path: " . ($pathInfo['full_path'] ?? 'N/A'));
        error_log("  - web_path: " . ($pathInfo['web_path'] ?? 'N/A'));
        error_log("  - db_path: " . ($pathInfo['db_path'] ?? 'N/A'));
        error_log("  - product_dir: " . ($pathInfo['product_dir'] ?? 'N/A'));
        error_log("  - timestamp: " . ($pathInfo['timestamp'] ?? 'N/A'));
    }
}
