<?php

class MigrationSync {
    private $db;
    private $source_url = 'http://dsp114.com/export_api.php';
    private $sync_key = 'duson_migration_sync_2026_xK9m';
    private $log_file;
    
    // 파일 필터 설정 (서버별로 다르게 설정)
    // dsp114.co.kr: min_no=84574, min_year=2026 (용량 제한)
    // NAS: min_no=0, min_year=2000 (전체 백업)
    private $file_filter_min_no = 84574;    // 교정파일: 이 번호 이상만
    private $file_filter_min_year = 2026;   // 원고파일: 이 연도 이상만

    private $table_map = array(
        'member' => array(
            'target' => 'member',
            'pk' => 'no',
            'dedup_fields' => array('id'),
            'phone_dedup' => true,
        ),
        'users' => array(
            'target' => 'users',
            'pk' => 'id',
            'dedup_fields' => array('username'),
            'phone_dedup' => false,
        ),
        'MlangOrder_PrintAuto' => array(
            'target' => 'mlangorder_printauto',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'MlangPrintAuto_NameCard' => array(
            'target' => 'mlangprintauto_namecard',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'MlangPrintAuto_inserted' => array(
            'target' => 'mlangprintauto_inserted',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'MlangPrintAuto_sticker' => array(
            'target' => 'mlangprintauto_sticker',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'MlangPrintAuto_msticker' => array(
            'target' => 'mlangprintauto_msticker',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'MlangPrintAuto_envelope' => array(
            'target' => 'mlangprintauto_envelope',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'MlangPrintAuto_LittlePrint' => array(
            'target' => 'mlangprintauto_littleprint',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'MlangPrintAuto_MerchandiseBond' => array(
            'target' => 'mlangprintauto_merchandisebond',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'MlangPrintAuto_cadarok' => array(
            'target' => 'mlangprintauto_cadarok',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'MlangPrintAuto_NcrFlambeau' => array(
            'target' => 'mlangprintauto_ncrflambeau',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'MlangPrintAuto_transactionCate' => array(
            'target' => 'mlangprintauto_transactioncate',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'shop_order' => array(
            'target' => 'shop_order',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'shop_list' => array(
            'target' => 'shop_list',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'shop_list01' => array(
            'target' => 'shop_list01',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'shop_temp' => array(
            'target' => 'shop_temp',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'orderDB' => array(
            'target' => 'orderdb',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'orderDB2' => array(
            'target' => 'orderdb2',
            'pk' => 'no',
            'dedup_fields' => array('no'),
            'phone_dedup' => false,
        ),
        'qna' => array(
            'target' => 'qna',
            'pk' => 'id',
            'dedup_fields' => array('id'),
            'phone_dedup' => false,
        ),
        'Mlang_board_bbs' => array(
            'target' => 'mlang_board_bbs',
            'pk' => 'Mlang_bbs_no',
            'dedup_fields' => array('Mlang_bbs_no'),
            'phone_dedup' => false,
        ),
        'Mlang_portfolio_bbs' => array(
            'target' => 'mlang_portfolio_bbs',
            'pk' => 'Mlang_bbs_no',
            'dedup_fields' => array('Mlang_bbs_no'),
            'phone_dedup' => false,
        ),
    );

    private $last_sync_file;

    public function __construct($db) {
        $this->db = $db;
        $this->log_file = __DIR__ . '/logs/sync_' . date('Y-m-d') . '.log';
        $this->last_sync_file = __DIR__ . '/logs/last_sync.json';
        $log_dir = __DIR__ . '/logs';
        if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);
    }

    /**
     * 마지막 동기화 시점 읽기
     * @return array ['last_sync' => '2026-02-02 12:00:00', 'last_sync_tables' => [...], ...]
     */
    public function getLastSync() {
        if (!file_exists($this->last_sync_file)) {
            return array('last_sync' => '', 'last_sync_display' => '없음');
        }
        $data = json_decode(file_get_contents($this->last_sync_file), true);
        if (!$data) return array('last_sync' => '', 'last_sync_display' => '없음');
        $data['last_sync_display'] = $data['last_sync'] ?: '없음';
        return $data;
    }

    /**
     * 마지막 동기화 시점 저장
     * @param array $results 동기화 결과 요약
     */
    public function saveLastSync($results = array()) {
        $data = array(
            'last_sync' => date('Y-m-d H:i:s'),
            'results_summary' => $results,
        );
        file_put_contents($this->last_sync_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * 동기화 대상 테이블 목록 (users, qna, BBS 제외)
     * @return array 소스 테이블명 배열
     */
    public function getSyncTargetTables() {
        $exclude = array('users', 'qna', 'Mlang_board_bbs', 'Mlang_portfolio_bbs');
        $tables = array();
        foreach ($this->table_map as $source_name => $config) {
            if (!in_array($source_name, $exclude)) {
                $tables[] = $source_name;
            }
        }
        return $tables;
    }

    public function getSourceTables() {
        $url = $this->source_url . '?key=' . urlencode($this->sync_key) . '&table=_tables';
        $json = $this->httpGet($url);
        if (!$json) return array('error' => 'Failed to connect to source');
        $data = json_decode($json, true);
        if (!$data) return array('error' => 'Invalid JSON from source');
        return $data;
    }

    public function syncTable($table, $since = '') {
        $start_time = microtime(true);
        $this->log("=== Sync start: $table (since: " . ($since ?: 'all') . ") ===");

        $offset = 0;
        $batch_size = 500;
        $total_inserted = 0;
        $total_skipped = 0;
        $total_errors = 0;
        $error_messages = array();

        $existing = $this->loadExistingKeys($table);

        do {
            $url = $this->source_url . '?key=' . urlencode($this->sync_key)
                . '&table=' . urlencode($table)
                . '&offset=' . $offset
                . '&limit=' . $batch_size;
            if ($since !== '') $url .= '&since=' . urlencode($since);

            $json = $this->httpGet($url);
            if (!$json) {
                $err = "HTTP fetch failed at offset $offset";
                $this->log("ERROR: $err");
                $error_messages[] = $err;
                break;
            }

            $data = json_decode($json, true);
            if (!$data || !isset($data['rows'])) {
                $err = "Invalid JSON at offset $offset";
                $this->log("ERROR: $err");
                $error_messages[] = $err;
                break;
            }

            foreach ($data['rows'] as $row) {
                $result = $this->insertRow($table, $row, $existing);
                if ($result === 'inserted') {
                    $total_inserted++;
                } elseif ($result === 'skipped') {
                    $total_skipped++;
                } else {
                    $total_errors++;
                    $error_messages[] = $result;
                }
            }

            $has_more = isset($data['has_more']) && $data['has_more'];
            $offset += $batch_size;

        } while ($has_more);

        $elapsed = round(microtime(true) - $start_time, 2);
        $summary = array(
            'table' => $table,
            'inserted' => $total_inserted,
            'skipped' => $total_skipped,
            'errors' => $total_errors,
            'error_messages' => array_slice($error_messages, 0, 20),
            'elapsed_seconds' => $elapsed,
        );

        $this->log("Result: inserted=$total_inserted, skipped=$total_skipped, errors=$total_errors ({$elapsed}s)");
        return $summary;
    }

    private function loadExistingKeys($table) {
        $existing = array('pk' => array(), 'id' => array(), 'phone' => array());
        $config = isset($this->table_map[$table]) ? $this->table_map[$table] : null;
        $target = $config ? $config['target'] : strtolower($table);
        $pk = $config ? $config['pk'] : 'no';

        $res = safe_mysqli_query($this->db, "SELECT * FROM `$target` LIMIT 0");
        if (!$res) return $existing;
        mysqli_free_result($res);

        $res = safe_mysqli_query($this->db, "SELECT `$pk` FROM `$target`");
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $existing['pk'][$row[$pk]] = true;
            }
        }

        if ($table === 'member') {
            $res = safe_mysqli_query($this->db, "SELECT id, CONCAT(IFNULL(hendphone1,''),'-',IFNULL(hendphone2,''),'-',IFNULL(hendphone3,'')) as hp FROM member");
            if ($res) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $existing['id'][$row['id']] = true;
                    if ($row['hp'] !== '--' && $row['hp'] !== '') {
                        $existing['phone'][$row['hp']] = true;
                    }
                }
            }
        }

        return $existing;
    }

    private function insertRow($table, $row, &$existing) {
        $config = isset($this->table_map[$table]) ? $this->table_map[$table] : null;
        $target = $config ? $config['target'] : strtolower($table);
        $pk = $config ? $config['pk'] : 'no';

        $pk_val = isset($row[$pk]) ? $row[$pk] : null;
        if ($pk_val !== null && isset($existing['pk'][$pk_val])) return 'skipped';

        if ($table === 'member') {
            if (isset($row['id']) && isset($existing['id'][$row['id']])) return 'skipped';
            $hp = trim(($row['hendphone1'] ?? '') . '-' . ($row['hendphone2'] ?? '') . '-' . ($row['hendphone3'] ?? ''));
            if ($hp !== '--' && $hp !== '' && isset($existing['phone'][$hp])) return 'skipped';
        }

        if ($config && !empty($config['dedup_fields'])) {
            foreach ($config['dedup_fields'] as $field) {
                if ($field === 'no' || $field === $pk) continue;
                if (isset($row[$field]) && isset($existing['id'][$row[$field]])) return 'skipped';
            }
        }

        $columns = array();
        $placeholders = array();
        $types = '';
        $values = array();

        foreach ($row as $col => $val) {
            $columns[] = '`' . $col . '`';
            $placeholders[] = '?';
            if ($val === null) {
                $types .= 's';
                $values[] = null;
            } elseif (is_int($val) || (is_string($val) && ctype_digit($val) && strlen($val) < 10)) {
                $types .= 'i';
                $values[] = intval($val);
            } else {
                $types .= 's';
                $values[] = $val;
            }
        }

        $sql = "INSERT IGNORE INTO `$target` (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            return "Prepare error ($target, pk=$pk_val): " . mysqli_error($this->db);
        }

        $bind_params = array();
        $bind_params[] = &$types;
        for ($i = 0; $i < count($values); $i++) {
            $bind_params[] = &$values[$i];
        }
        call_user_func_array(array($stmt, 'bind_param'), $bind_params);

        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$ok) {
            return "Insert error ($target, pk=$pk_val): " . mysqli_error($this->db);
        }

        if ($pk_val !== null) $existing['pk'][$pk_val] = true;
        if ($table === 'member') {
            if (isset($row['id'])) $existing['id'][$row['id']] = true;
            $hp = trim(($row['hendphone1'] ?? '') . '-' . ($row['hendphone2'] ?? '') . '-' . ($row['hendphone3'] ?? ''));
            if ($hp !== '--' && $hp !== '') $existing['phone'][$hp] = true;
        }

        return 'inserted';
    }

    private function httpGet($url) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $result = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code >= 200 && $code < 300) return $result;
            return false;
        }
        $ctx = stream_context_create(array('http' => array('timeout' => 60)));
        return @file_get_contents($url, false, $ctx);
    }

    private function log($msg) {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
        file_put_contents($this->log_file, $line, FILE_APPEND);
    }

    // ============================================================
    // 파일 동기화 (교정파일 + 원고파일)
    // ============================================================

    /**
     * 소스 서버에서 파일 목록 가져오기
     * @param string $type 'upload' (교정파일) 또는 'shop' (원고파일)
     * @param string $since 날짜 필터
     * @param int $offset
     * @param int $limit
     * @return array
     */
    /**
     * 파일 필터 설정 (NAS 전체 백업용)
     */
    public function setFileFilters($min_no = 84574, $min_year = 2026) {
        $this->file_filter_min_no = $min_no;
        $this->file_filter_min_year = $min_year;
    }
    
    /**
     * 현재 파일 필터 설정 반환
     */
    public function getFileFilters() {
        return array(
            'min_no' => $this->file_filter_min_no,
            'min_year' => $this->file_filter_min_year
        );
    }
    
    public function getFileList($type, $since = '', $offset = 0, $limit = 100) {
        $url = $this->source_url . '?key=' . urlencode($this->sync_key)
            . '&table=_files'
            . '&type=' . urlencode($type)
            . '&offset=' . $offset
            . '&limit=' . $limit;
        if ($since !== '') $url .= '&since=' . urlencode($since);
        
        // 파일 필터 적용
        $url .= '&min_no=' . $this->file_filter_min_no;
        $url .= '&min_year=' . $this->file_filter_min_year;

        $json = $this->httpGet($url);
        if (!$json) return array('error' => 'Failed to connect to source');
        $data = json_decode($json, true);
        if (!$data) return array('error' => 'Invalid JSON from source');
        return $data;
    }

    /**
     * 파일 동기화 실행
     * @param string $type 'upload' or 'shop'
     * @param string $since 날짜 필터
     * @return array 결과 요약
     */
    public function syncFiles($type, $since = '') {
        $start_time = microtime(true);
        $this->log("=== File sync start: type=$type (since: " . ($since ?: 'all') . ") ===");

        $offset = 0;
        $batch_size = 100;
        $total_downloaded = 0;
        $total_skipped = 0;
        $total_errors = 0;
        $error_messages = array();

        // 로컬 저장 기본 경로 (운영서버 웹루트 기준)
        if ($type === 'upload') {
            $local_base = dirname(dirname(__DIR__)) . '/mlangorder_printauto/upload';
        } elseif ($type === 'imgfolder') {
            $local_base = dirname(dirname(__DIR__)) . '/ImgFolder';
        } else {
            $local_base = dirname(dirname(__DIR__)) . '/shop/data';
        }

        do {
            $data = $this->getFileList($type, $since, $offset, $batch_size);
            if (isset($data['error'])) {
                $error_messages[] = $data['error'];
                break;
            }

            $items = isset($data['items']) ? $data['items'] : array();
            if (empty($items)) break;

            foreach ($items as $item) {
                // imgfolder uses 'folder' key, others use 'order_no'
                $identifier = isset($item['folder']) ? $item['folder'] : $item['order_no'];
                foreach ($item['files'] as $file) {
                    $result = $this->downloadFile($file['path'], $local_base, $type, $identifier);
                    if ($result === 'downloaded') {
                        $total_downloaded++;
                    } elseif ($result === 'skipped') {
                        $total_skipped++;
                    } else {
                        $total_errors++;
                        $error_messages[] = "#{$identifier}: " . $result;
                        // Log first 50 errors for debugging
                        if ($total_errors <= 50) {
                            $this->log("File error #{$identifier}: " . $result);
                        }
                    }
                }
            }

            $has_more = isset($data['has_more']) && $data['has_more'];
            $offset += $batch_size;

        } while ($has_more);

        $elapsed = round(microtime(true) - $start_time, 2);
        $type_labels = array(
            'upload' => '교정파일',
            'shop' => '원고파일(스티커)',
            'imgfolder' => '원고파일(일반)'
        );
        $summary = array(
            'type' => $type,
            'type_label' => isset($type_labels[$type]) ? $type_labels[$type] : $type,
            'downloaded' => $total_downloaded,
            'skipped' => $total_skipped,
            'errors' => $total_errors,
            'error_messages' => array_slice($error_messages, 0, 30),
            'elapsed_seconds' => $elapsed,
        );

        $this->log("File sync result: downloaded=$total_downloaded, skipped=$total_skipped, errors=$total_errors ({$elapsed}s)");
        return $summary;
    }

    /**
     * 개별 파일 다운로드
     */
    private function downloadFile($remote_path, $local_base, $type, $order_no) {
        // 로컬 저장 경로 결정
        if ($type === 'upload') {
            $local_dir = $local_base . '/' . $order_no;
            $filename = basename($remote_path);
            $local_file = $local_dir . '/' . $filename;
        } elseif ($type === 'imgfolder') {
            // imgfolder/경로/파일명 → ImgFolder/경로/파일명
            $rel_path = substr($remote_path, 10); // 'imgfolder/' 제거
            $local_file = $local_base . '/' . $rel_path;
            $local_dir = dirname($local_file);
        } else {
            $local_dir = $local_base;
            $filename = basename($remote_path);
            $local_file = $local_dir . '/' . $filename;
        }

        // 이미 존재하면 스킵
        if (file_exists($local_file) && filesize($local_file) > 0) {
            return 'skipped';
        }

        // 디렉토리 생성
        if (!is_dir($local_dir)) {
            if (!mkdir($local_dir, 0755, true)) {
                return "mkdir failed: $local_dir";
            }
        }

        // 소스에서 다운로드
        $url = $this->source_url . '?key=' . urlencode($this->sync_key)
            . '&table=_file_download'
            . '&path=' . urlencode($remote_path);

        $content = $this->httpGetBinary($url);
        if ($content === false || strlen($content) === 0) {
            return "download failed: $remote_path";
        }

        // 저장
        $written = file_put_contents($local_file, $content);
        if ($written === false) {
            return "write failed: $local_file";
        }

        return 'downloaded';
    }

    /**
     * 바이너리 HTTP GET (파일 다운로드용)
     */
    private function httpGetBinary($url) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            $result = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code >= 200 && $code < 300) return $result;
            return false;
        }
        $ctx = stream_context_create(array('http' => array('timeout' => 120)));
        return @file_get_contents($url, false, $ctx);
    }

    /**
     * 파일 동기화 현황 조회
     */
    public function getFileStats() {
        $stats = array(
            'upload' => array('total' => 0, 'local' => 0),
            'shop' => array('total' => 0, 'local' => 0),
            'imgfolder' => array('total' => 0, 'local' => 0),
        );

        // 교정파일 로컬 현황
        $upload_dir = dirname(dirname(__DIR__)) . '/mlangorder_printauto/upload';
        if (is_dir($upload_dir)) {
            $dirs = glob($upload_dir . '/*', GLOB_ONLYDIR);
            if ($dirs) {
                foreach ($dirs as $d) {
                    $files = glob($d . '/*');
                    if ($files) $stats['upload']['local'] += count($files);
                }
            }
        }

        // 원고파일(스티커) 로컬 현황
        $shop_dir = dirname(dirname(__DIR__)) . '/shop/data';
        if (is_dir($shop_dir)) {
            $files = glob($shop_dir . '/*');
            if ($files) $stats['shop']['local'] = count($files);
        }

        // 원고파일(일반) 로컬 현황
        $imgfolder_dir = dirname(dirname(__DIR__)) . '/ImgFolder';
        if (is_dir($imgfolder_dir)) {
            $count = 0;
            $dirs = glob($imgfolder_dir . '/_MlangPrintAuto_*', GLOB_ONLYDIR);
            if ($dirs) {
                foreach ($dirs as $d) {
                    $subdirs = glob($d . '/*/*/*', GLOB_ONLYDIR); // year/date/ip/timestamp
                    if ($subdirs) {
                        foreach ($subdirs as $sd) {
                            $files = glob($sd . '/*');
                            if ($files) $count += count($files);
                        }
                    }
                }
            }
            $stats['imgfolder']['local'] = $count;
        }

        // 소스 서버 현황 (첫 페이지만 조회)
        $upload_list = $this->getFileList('upload', '', 0, 1);
        if (isset($upload_list['total_orders'])) {
            $stats['upload']['total_orders'] = $upload_list['total_orders'];
        }
        $shop_list = $this->getFileList('shop', '', 0, 1);
        if (isset($shop_list['total_orders'])) {
            $stats['shop']['total_orders'] = $shop_list['total_orders'];
        }
        $imgfolder_list = $this->getFileList('imgfolder', '', 0, 1);
        if (isset($imgfolder_list['total_orders'])) {
            $stats['imgfolder']['total_orders'] = $imgfolder_list['total_orders'];
        }

        return $stats;
    }

    public function getLogFiles() {
        $log_dir = __DIR__ . '/logs';
        if (!is_dir($log_dir)) return array();
        $files = glob($log_dir . '/sync_*.log');
        rsort($files);
        return array_slice($files, 0, 30);
    }

    public function getLogContent($filename) {
        $path = __DIR__ . '/logs/' . basename($filename);
        if (!file_exists($path)) return '';
        return file_get_contents($path);
    }
}
