<?php
/**
 * ImagePathResolver - 레거시/신규 이미지 경로 통합 해석 클래스
 *
 * dsp114.com → dsp1830.shop 마이그레이션 지원
 * 날짜 기반 필터링 포함 (2025-12-06)
 *
 * 필터링 규칙:
 * - 교정용 이미지 (ThingCate): 2018년 이후만 표시
 * - 고객 원고 파일 (uploaded_files): 2024년 이후만 표시
 */
class ImagePathResolver {
    // 레거시/신규 구분 기준 주문번호
    const LEGACY_CUTOFF_NO = 103700;

    // 날짜 기반 필터링 기준
    const PROOF_IMAGE_CUTOFF = '2018-01-01';      // 교정용 이미지: 2018년 이후만
    const CUSTOMER_FILE_CUTOFF = '2024-01-01';   // 고객 원고: 2024년 이후만

    /**
     * 주문 정보에서 이미지 경로 해석
     * @param int $order_no 주문번호
     * @param string $filename 파일명
     * @param array $row DB 레코드 (uploaded_files, ImgFolder, ThingCate 포함)
     * @return string|null 실제 파일 경로 또는 null
     */
    public static function resolve($order_no, $filename, $row = []) {
        $paths_to_try = [];

        // 1순위: 신규 시스템 JSON 메타데이터
        if (!empty($row['uploaded_files']) && $row['uploaded_files'] !== '0') {
            $files = json_decode($row['uploaded_files'], true);
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (($file['saved_name'] ?? '') === $filename ||
                        ($file['original_name'] ?? '') === $filename) {
                        if (isset($file['path']) && file_exists($file['path'])) {
                            return $file['path'];
                        }
                        if (isset($file['web_url'])) {
                            $path = $_SERVER['DOCUMENT_ROOT'] . $file['web_url'];
                            if (file_exists($path)) return $path;
                        }
                    }
                }
            }
        }

        // 2순위: 신규 ImgFolder 경로
        if (!empty($row['ImgFolder']) && strpos($row['ImgFolder'], '_MlangPrintAuto_') !== false) {
            $paths_to_try[] = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $row['ImgFolder'] . '/' . $filename;
        }

        // 3순위: 레거시 경로 (소문자 - 현재 dsp1830.shop)
        $paths_to_try[] = $_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/upload/' . $order_no . '/' . $filename;

        // 4순위: 레거시 경로 (대문자 - 과거 dsp114.com)
        $paths_to_try[] = $_SERVER['DOCUMENT_ROOT'] . '/MlangOrder_PrintAuto/upload/' . $order_no . '/' . $filename;

        // 첫 번째로 존재하는 경로 반환
        foreach ($paths_to_try as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // 5순위: 대소문자 무시 검색
        return self::findCaseInsensitive($order_no, $filename);
    }

    /**
     * 이미지 웹 URL 생성
     * @param int $order_no 주문번호
     * @param array $row DB 레코드
     * @return string|null 웹 URL 또는 null
     */
    public static function getWebUrl($order_no, $row) {
        // 신규 시스템 우선
        if (!empty($row['uploaded_files']) && $row['uploaded_files'] !== '0') {
            $files = json_decode($row['uploaded_files'], true);
            if (is_array($files) && !empty($files[0]['web_url'])) {
                return $files[0]['web_url'];
            }
        }

        // 신규 ImgFolder 경로
        if (!empty($row['ImgFolder']) && strpos($row['ImgFolder'], '_MlangPrintAuto_') !== false) {
            $filename = $row['ThingCate'] ?? '';
            if ($filename) {
                return '/ImgFolder/' . $row['ImgFolder'] . '/' . $filename;
            }
        }

        // 레거시 경로 (ThingCate 사용)
        if (!empty($row['ThingCate'])) {
            // 대소문자 양쪽 확인
            $legacy_lower = '/mlangorder_printauto/upload/' . $order_no . '/' . $row['ThingCate'];
            $legacy_upper = '/MlangOrder_PrintAuto/upload/' . $order_no . '/' . $row['ThingCate'];

            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $legacy_lower)) {
                return $legacy_lower;
            }
            return $legacy_upper; // 기본값
        }

        return null;
    }

    /**
     * 대소문자 무시 파일 검색
     * @param int $order_no 주문번호
     * @param string $filename 파일명
     * @return string|null 파일 경로 또는 null
     */
    private static function findCaseInsensitive($order_no, $filename) {
        $dirs = [
            $_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/upload/' . $order_no . '/',
            $_SERVER['DOCUMENT_ROOT'] . '/MlangOrder_PrintAuto/upload/' . $order_no . '/'
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) continue;
            $files = scandir($dir);
            $filename_lower = strtolower($filename);
            foreach ($files as $file) {
                if (strtolower($file) === $filename_lower) {
                    return $dir . $file;
                }
            }
        }
        return null;
    }

    /**
     * 레거시 주문인지 확인
     * @param int $order_no 주문번호
     * @return bool true면 레거시 주문
     */
    public static function isLegacyOrder($order_no) {
        return $order_no < self::LEGACY_CUTOFF_NO;
    }

    // ========================================
    // 날짜 기반 필터링 메서드 (2025-12-06 추가)
    // ========================================

    /**
     * 날짜 필터링 적용 여부 확인
     * @param string $file_type 'proof' (교정용) 또는 'customer' (고객 원고)
     * @param string|null $date_str 날짜 문자열 (YYYY-MM-DD 형식 또는 YYYY-MM-DD HH:MM:SS)
     * @return bool 표시 여부 (true = 표시, false = 제외)
     */
    public static function shouldDisplay($file_type, $date_str) {
        if (empty($date_str)) {
            return true; // 날짜 없으면 표시 (안전 모드)
        }

        $file_date = strtotime($date_str);
        if ($file_date === false) {
            return true; // 파싱 실패시 표시
        }

        if ($file_type === 'proof') {
            // 교정용 이미지: 2018년 이후만
            return $file_date >= strtotime(self::PROOF_IMAGE_CUTOFF);
        } elseif ($file_type === 'customer') {
            // 고객 원고: 2024년 이후만
            return $file_date >= strtotime(self::CUSTOMER_FILE_CUTOFF);
        }

        return true; // 기본값: 표시
    }

    /**
     * ImgFolder 경로에서 연도 추출
     * 예: "_MlangPrintAuto_namecard_index.php/2024/1119/..." → "2024"
     * @param string $img_folder ImgFolder 경로
     * @return string|null 연도 또는 null
     */
    public static function extractYearFromPath($img_folder) {
        if (empty($img_folder)) {
            return null;
        }

        // 패턴: /년도/ 형식 (예: /2024/, /2018/)
        if (preg_match('/\/(\d{4})\//', $img_folder, $matches)) {
            return $matches[1];
        }

        // 패턴: _년도/ 또는 .php/년도 형식
        if (preg_match('/[._\/](\d{4})\//', $img_folder, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * 파일 목록 필터링 적용
     * @param array $files 파일 배열 (JSON에서 파싱된)
     * @param string|null $order_date 주문 날짜 (폴백용)
     * @param string $file_type 'proof' 또는 'customer'
     * @return array 필터링된 파일 배열
     */
    public static function filterFilesByDate($files, $order_date, $file_type = 'proof') {
        if (!is_array($files) || empty($files)) {
            return [];
        }

        $filtered = [];
        foreach ($files as $file) {
            // 파일별 날짜 결정 (경로에서 추출 우선, 없으면 주문 날짜)
            $file_date = $order_date;

            // 1. 경로에서 연도 추출 시도
            if (isset($file['path'])) {
                $year = self::extractYearFromPath($file['path']);
                if ($year) {
                    $file_date = $year . '-01-01';
                }
            }

            // 2. web_url에서 연도 추출 시도
            if ($file_date === $order_date && isset($file['web_url'])) {
                $year = self::extractYearFromPath($file['web_url']);
                if ($year) {
                    $file_date = $year . '-01-01';
                }
            }

            // 날짜 필터 적용
            if (self::shouldDisplay($file_type, $file_date)) {
                $filtered[] = $file;
            }
        }
        return $filtered;
    }

    /**
     * DB 레코드에서 파일 목록 가져오기 (필터링 적용)
     * @param array $row DB 레코드
     * @param bool $apply_date_filter 날짜 필터 적용 여부
     * @return array 파일 정보 배열 ['files' => [], 'proof_filtered' => bool, 'customer_filtered' => bool]
     */
    public static function getFilesFromRow($row, $apply_date_filter = true) {
        $result = [
            'files' => [],
            'proof_excluded' => false,
            'customer_excluded' => false,
            'proof_excluded_count' => 0,
            'customer_excluded_count' => 0
        ];

        $order_no = $row['no'] ?? 0;
        $order_date = $row['date'] ?? null;

        // 1. 교정용 이미지 (ThingCate)
        if (!empty($row['ThingCate'])) {
            if (!$apply_date_filter || self::shouldDisplay('proof', $order_date)) {
                $path = self::resolve($order_no, $row['ThingCate'], $row);
                if ($path && file_exists($path)) {
                    $result['files'][] = [
                        'name' => $row['ThingCate'],
                        'saved_name' => $row['ThingCate'],
                        'size' => filesize($path),
                        'type' => 'proof',  // 교정용 이미지
                        'path' => $path
                    ];
                }
            } else {
                $result['proof_excluded'] = true;
                $result['proof_excluded_count']++;
            }
        }

        // 2. 고객 원고 파일 (uploaded_files JSON)
        if (!empty($row['uploaded_files']) && $row['uploaded_files'] !== '0') {
            $files = json_decode($row['uploaded_files'], true);
            if (is_array($files)) {
                if ($apply_date_filter) {
                    $before_count = count($files);
                    $files = self::filterFilesByDate($files, $order_date, 'customer');
                    $filtered_count = $before_count - count($files);
                    if ($filtered_count > 0) {
                        $result['customer_excluded'] = true;
                        $result['customer_excluded_count'] = $filtered_count;
                    }
                }

                foreach ($files as $file) {
                    $file['type'] = 'customer';  // 고객 원고
                    $result['files'][] = $file;
                }
            }
        }

        // 3. ../shop/data/파일명 패턴 (ImgFolder에 파일명이 직접 포함)
        if (empty($result['files']) && !empty($row['ImgFolder'])) {
            $img_folder = $row['ImgFolder'];
            if (preg_match('/^\.\.\/shop\/data\/(.+)$/u', $img_folder, $fm) && $fm[1] !== '') {
                $filename = $fm[1];
                $file_path = $_SERVER['DOCUMENT_ROOT'] . '/shop/data/' . $filename;
                if (file_exists($file_path)) {
                    $result['files'][] = [
                        'name' => $filename,
                        'saved_name' => $filename,
                        'size' => filesize($file_path),
                        'type' => 'customer',
                        'path' => $file_path,
                        'download_path' => 'shop/data',
                    ];
                }
            }
        }

        // 4. 폴백: 디렉토리 스캔 (ImgFolder)
        if (empty($result['files']) && !empty($row['ImgFolder'])) {
            // 날짜 필터 확인
            $year = self::extractYearFromPath($row['ImgFolder']);
            $year_int = $year ? intval($year) : 0;

            // 고객 원고 폴더면 2024년 기준 적용
            $is_customer_folder = strpos($row['ImgFolder'], '_MlangPrintAuto_') !== false;
            if ($apply_date_filter && $is_customer_folder && $year_int > 0 && $year_int < 2024) {
                $result['customer_excluded'] = true;
                $result['customer_excluded_count']++;
            } else {
                // 디렉토리 스캔
                $dir = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $row['ImgFolder'] . '/';
                if (is_dir($dir)) {
                    $scanned_files = scandir($dir);
                    foreach ($scanned_files as $file) {
                        if ($file !== '.' && $file !== '..' && is_file($dir . $file)) {
                            $result['files'][] = [
                                'name' => $file,
                                'saved_name' => $file,
                                'size' => filesize($dir . $file),
                                'type' => $is_customer_folder ? 'customer' : 'unknown',
                                'path' => $dir . $file,
                                'web_url' => '/ImgFolder/' . $row['ImgFolder'] . '/' . $file
                            ];
                        }
                    }
                }
            }
        }

        // 5. 추가 폴백: 레거시 폴더 스캔
        if (empty($result['files'])) {
            $legacy_dirs = [
                $_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/upload/' . $order_no . '/',
                $_SERVER['DOCUMENT_ROOT'] . '/MlangOrder_PrintAuto/upload/' . $order_no . '/'
            ];

            foreach ($legacy_dirs as $dir) {
                if (is_dir($dir)) {
                    $scanned_files = scandir($dir);
                    foreach ($scanned_files as $file) {
                        if ($file !== '.' && $file !== '..' && is_file($dir . $file)) {
                            $result['files'][] = [
                                'name' => $file,
                                'saved_name' => $file,
                                'size' => filesize($dir . $file),
                                'type' => 'legacy',
                                'path' => $dir . $file
                            ];
                        }
                    }
                    break; // 첫 번째 존재하는 디렉토리에서만 스캔
                }
            }
        }

        return $result;
    }

    /**
     * 필터 제외 메시지 생성
     * @param array $filter_result getFilesFromRow() 결과
     * @return string HTML 메시지 또는 빈 문자열
     */
    public static function getFilterMessage($filter_result) {
        $messages = [];

        if ($filter_result['proof_excluded']) {
            $count = $filter_result['proof_excluded_count'];
            $messages[] = "교정 이미지: 2018년 이전 데이터 {$count}개 제외";
        }

        if ($filter_result['customer_excluded']) {
            $count = $filter_result['customer_excluded_count'];
            $messages[] = "고객 원고: 2024년 이전 데이터 {$count}개 제외";
        }

        if (empty($messages)) {
            return '';
        }

        return '<div class="alert alert-secondary" style="font-size: 12px; padding: 5px 10px; margin: 5px 0;">'
             . implode(' | ', $messages)
             . '</div>';
    }
}
