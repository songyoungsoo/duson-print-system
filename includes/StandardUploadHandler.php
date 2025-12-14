<?php
/**
 * StandardUploadHandler - 통합 파일 업로드 핸들러
 *
 * 모든 제품에서 사용하는 표준화된 파일 업로드 시스템
 * - 레거시 DSP114 경로 구조 100% 호환
 * - UploadPathHelper 사용
 * - JSON 메타데이터 저장
 * - 안전한 에러 처리
 *
 * @version 1.0
 * @date 2025-11-19
 */

require_once __DIR__ . '/UploadPathHelper.php';

class StandardUploadHandler {

    /**
     * 허용되는 파일 확장자
     */
    const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp',  // 이미지
        'pdf', 'ai', 'psd', 'eps', 'cdr',           // 디자인
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', // 오피스
        'zip', 'rar', '7z',                         // 압축
        'txt', 'csv'                                // 텍스트
    ];

    /**
     * 최대 파일 크기 (15MB)
     */
    const MAX_FILE_SIZE = 15 * 1024 * 1024;

    /**
     * 파일 업로드 처리
     *
     * @param string $product 제품 코드 (예: 'namecard', 'inserted')
     * @param array $files $_FILES 배열 또는 특정 파일 배열
     * @param array $options 추가 옵션
     * @return array ['success' => bool, 'files' => array, 'img_folder' => string, 'thing_cate' => string, 'error' => string]
     */
    public static function processUpload($product, $files, $options = []) {
        $result = [
            'success' => false,
            'files' => [],
            'img_folder' => '',
            'thing_cate' => '',
            'error' => ''
        ];

        try {
            // 1. 파일 배열 정규화
            $normalized_files = self::normalizeFilesArray($files);

            if (empty($normalized_files)) {
                $result['success'] = true;  // 파일 없음은 에러가 아님
                error_log("StandardUploadHandler: No files to upload for product: $product");
                return $result;
            }

            // 2. 업로드 경로 생성 (UploadPathHelper 사용)
            $paths = UploadPathHelper::generateUploadPath($product);
            $upload_dir = $paths['full_path'];
            $db_path = $paths['db_path'];

            // 3. 디렉토리 생성
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    throw new Exception("디렉토리 생성 실패: $upload_dir");
                }
                error_log("StandardUploadHandler: Created directory: $upload_dir");
            }

            // 4. 파일 업로드 처리
            $uploaded_files = [];
            $upload_count = 0;
            $used_filenames = []; // 🆕 이미 사용된 파일명 추적 (중복 방지)

            foreach ($normalized_files as $file_info) {
                $file_name = $file_info['name'];
                $file_tmp = $file_info['tmp_name'];
                $file_error = $file_info['error'];
                $file_size = $file_info['size'];

                // 4.1 파일 에러 체크
                if ($file_error !== UPLOAD_ERR_OK) {
                    error_log("StandardUploadHandler: Upload error for $file_name - Error code: $file_error");
                    continue;
                }

                // 4.2 파일 크기 검증
                if ($file_size > self::MAX_FILE_SIZE) {
                    error_log("StandardUploadHandler: File too large: $file_name ($file_size bytes)");
                    continue;
                }

                // 4.3 파일 확장자 검증
                $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                    error_log("StandardUploadHandler: Invalid extension: $extension for file $file_name");
                    continue;
                }

                // 4.4 안전한 파일명 생성 (중복 체크 포함)
                $safe_filename = self::generateSafeFilename($file_name);

                // 🆕 중복 파일명 처리: 같은 이름이 이미 사용되었으면 순차 번호 추가
                $original_safe_filename = $safe_filename;
                $counter = 1;
                while (in_array($safe_filename, $used_filenames)) {
                    $filename_without_ext = pathinfo($original_safe_filename, PATHINFO_FILENAME);
                    $extension_part = pathinfo($original_safe_filename, PATHINFO_EXTENSION);
                    $safe_filename = $filename_without_ext . '_' . $counter . '.' . $extension_part;
                    $counter++;
                    error_log("StandardUploadHandler: Duplicate filename detected, using: $safe_filename");
                }

                // 사용된 파일명 목록에 추가
                $used_filenames[] = $safe_filename;

                $destination = $upload_dir . '/' . $safe_filename;

                // 4.5 파일 이동
                error_log("StandardUploadHandler: Attempting to move file...");
                error_log("  - Source (tmp): $file_tmp");
                error_log("  - Destination: $destination");
                error_log("  - Upload dir exists: " . (is_dir($upload_dir) ? "YES" : "NO"));
                error_log("  - Upload dir writable: " . (is_writable($upload_dir) ? "YES" : "NO"));
                error_log("  - Temp file exists: " . (file_exists($file_tmp) ? "YES" : "NO"));

                if (move_uploaded_file($file_tmp, $destination)) {
                    // 권한 설정 (644 - 소유자 읽기/쓰기, 그룹/기타 읽기만)
                    chmod($destination, 0644);

                    $uploaded_files[] = [
                        'original_name' => $file_name,
                        'saved_name' => $safe_filename,
                        'path' => $destination,
                        'size' => $file_size,
                        'web_url' => '/ImgFolder/' . $db_path . '/' . $safe_filename
                    ];

                    $upload_count++;
                    error_log("StandardUploadHandler: ✅ Successfully uploaded: $file_name → $safe_filename");
                } else {
                    $error_details = error_get_last();
                    error_log("StandardUploadHandler: ❌ Failed to move file: $file_name");
                    if ($error_details) {
                        error_log("  - PHP Error: " . $error_details['message']);
                    }
                }
            }

            // 5. 결과 반환
            $result['success'] = true;
            $result['files'] = $uploaded_files;
            $result['img_folder'] = $db_path;
            $result['thing_cate'] = !empty($uploaded_files) ? $uploaded_files[0]['saved_name'] : '';

            error_log("StandardUploadHandler: Upload complete - $upload_count files uploaded for product: $product");

        } catch (Exception $e) {
            $result['success'] = false;
            $result['error'] = $e->getMessage();
            error_log("StandardUploadHandler: Exception - " . $e->getMessage());
        }

        return $result;
    }

    /**
     * 파일 배열 정규화
     *
     * $_FILES의 다양한 형식을 통일된 배열로 변환
     *
     * @param array $files $_FILES 배열
     * @return array 정규화된 파일 배열
     */
    private static function normalizeFilesArray($files) {
        $normalized = [];

        // uploaded_files 키 확인
        if (isset($files['uploaded_files'])) {
            $files = $files['uploaded_files'];
        }

        // files 키 확인 (sticker_new 등)
        if (isset($files['files'])) {
            $files = $files['files'];
        }

        // 파일이 없는 경우
        if (empty($files) || !isset($files['name'])) {
            return [];
        }

        // 단일 파일 처리
        if (!is_array($files['name'])) {
            if ($files['error'] !== UPLOAD_ERR_NO_FILE) {
                $normalized[] = [
                    'name' => $files['name'],
                    'tmp_name' => $files['tmp_name'],
                    'error' => $files['error'],
                    'size' => $files['size']
                ];
            }
            return $normalized;
        }

        // 다중 파일 처리
        $file_count = count($files['name']);
        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                $normalized[] = [
                    'name' => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
            }
        }

        return $normalized;
    }

    /**
     * 안전한 파일명 생성
     *
     * 원본 파일명을 안전하게 변환하되, 가능한 한 유지
     * 한글 파일명 지원
     *
     * @param string $original_name 원본 파일명
     * @return string 안전한 파일명
     */
    private static function generateSafeFilename($original_name) {
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $filename = pathinfo($original_name, PATHINFO_FILENAME);

        // 특수문자 제거 (한글, 영문, 숫자, 하이픈, 언더스코어만 허용)
        $filename = preg_replace('/[^\p{L}\p{N}_-]/u', '', $filename);

        // 파일명이 너무 긴 경우 자르기 (최대 100자)
        if (mb_strlen($filename) > 100) {
            $filename = mb_substr($filename, 0, 100);
        }

        // 파일명이 비어있으면 타임스탬프 사용
        if (empty($filename)) {
            $filename = time();
        }

        return $filename . '.' . $extension;
    }

    /**
     * 주문 확정 시 파일 복사
     *
     * ImgFolder 경로에서 MlangOrder_PrintAuto/upload/{주문번호}/ 로 파일 복사
     *
     * @param int $order_no 주문 번호
     * @param string $img_folder 원본 ImgFolder 경로
     * @param array $uploaded_files 업로드된 파일 JSON 배열
     * @return array ['success' => bool, 'copied_files' => array, 'error' => string]
     */
    public static function copyFilesForOrder($order_no, $img_folder, $uploaded_files) {
        $result = [
            'success' => false,
            'copied_files' => [],
            'error' => ''
        ];

        try {
            // uploaded_files JSON 디코딩
            if (is_string($uploaded_files)) {
                $uploaded_files = json_decode($uploaded_files, true);
            }

            if (empty($uploaded_files)) {
                $result['success'] = true;  // 파일 없음은 에러가 아님
                return $result;
            }

            // 주문 디렉토리 생성
            $order_dir = __DIR__ . '/../mlangorder_printauto/upload/' . $order_no . '/';
            if (!file_exists($order_dir)) {
                if (!mkdir($order_dir, 0755, true)) {
                    throw new Exception("주문 디렉토리 생성 실패: $order_dir");
                }
                error_log("StandardUploadHandler: Created order directory: $order_dir");
            }

            // 파일 복사
            $copied_files = [];
            foreach ($uploaded_files as $file) {
                $source = $file['path'];
                $filename = $file['saved_name'];
                $destination = $order_dir . $filename;

                if (file_exists($source)) {
                    if (copy($source, $destination)) {
                        chmod($destination, 0644);
                        $copied_files[] = $filename;
                        error_log("StandardUploadHandler: Copied file for order $order_no: $filename");
                    } else {
                        error_log("StandardUploadHandler: Failed to copy file: $source → $destination");
                    }
                } else {
                    error_log("StandardUploadHandler: Source file not found: $source");
                }
            }

            $result['success'] = true;
            $result['copied_files'] = $copied_files;

        } catch (Exception $e) {
            $result['success'] = false;
            $result['error'] = $e->getMessage();
            error_log("StandardUploadHandler: Copy exception - " . $e->getMessage());
        }

        return $result;
    }
}
