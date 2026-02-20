<?php
/**
 * dsp114.com 데이터 내보내기 API
 * PHP 5.2 호환 (mysql_* 함수, EUC-KR)
 * 
 * 이 파일은 dsp114.com 서버에 배치됨
 * dsp114.co.kr의 sync 스크립트가 HTTP로 호출
 */

$SYNC_SECRET = 'duson_migration_sync_2026_xK9m';

if (!isset($_GET['key']) || $_GET['key'] !== $SYNC_SECRET) {
    header('HTTP/1.0 403 Forbidden');
    die('Unauthorized');
}

$table = isset($_GET['table']) ? $_GET['table'] : '';
$since = isset($_GET['since']) ? $_GET['since'] : '';
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 500;
if ($limit > 2000) $limit = 2000;

$ALLOWED_TABLES = array(
    'member',
    'users',
    'MlangOrder_PrintAuto',
    'MlangPrintAuto_NameCard',
    'MlangPrintAuto_inserted',
    'MlangPrintAuto_sticker',
    'MlangPrintAuto_msticker',
    'MlangPrintAuto_envelope',
    'MlangPrintAuto_LittlePrint',
    'MlangPrintAuto_MerchandiseBond',
    'MlangPrintAuto_cadarok',
    'MlangPrintAuto_NcrFlambeau',
    'MlangPrintAuto_transactionCate',
    'shop_order',
    'shop_list',
    'shop_list01',
    'shop_temp',
    'orderDB',
    'orderDB2',
    'qna',
    'Mlang_board_bbs',
    'Mlang_portfolio_bbs',
);

// ============================================================
// 파일 관련 API (교정파일 + 원고파일)
// ============================================================

// 교정파일 목록: ?key=...&table=_files&type=upload&offset=0&limit=100
// 원고파일 목록: ?key=...&table=_files&type=shop&offset=0&limit=100
if ($table === '_files') {
    header('Content-Type: application/json; charset=utf-8');
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    
    // 파일 필터 파라미터 읽기 (트래픽 과부하 방지 핵심!)
    // min_no: 교정파일(upload)에서 이 주문번호 이상만 (dsp114.co.kr: 84574, NAS: 0)
    // min_year: 원고파일(shop/imgfolder)에서 이 연도 이상만 (dsp114.co.kr: 2026, NAS: 2000)
    $min_no = isset($_GET['min_no']) ? intval($_GET['min_no']) : 0;
    $min_year = isset($_GET['min_year']) ? intval($_GET['min_year']) : 0;
    
    $db = mysql_connect("localhost", "duson1830", "du1830");
    if (!$db) { echo json_encode(array('error' => 'DB failed')); exit; }
    mysql_select_db("duson1830", $db);
    mysql_query("SET NAMES 'euckr'", $db);
    
    if ($type === 'upload') {
        // 교정파일: /www/MlangOrder_PrintAuto/upload/{no}/
        $base_dir = '/home/neo_web2/duson1830/www/MlangOrder_PrintAuto/upload';
        $where = '';
        // min_no 필터: 주문번호 기준 하한선 (예: 84574 이상만)
        if ($min_no > 0) {
            $where .= " AND no >= " . $min_no;
        }
        if ($since !== '') {
            $since_esc = mysql_real_escape_string($since, $db);
            $where .= " AND date >= '$since_esc'";
        }
        
        $cnt_res = mysql_query("SELECT COUNT(*) FROM MlangOrder_PrintAuto WHERE no > 0" . $where, $db);
        $cnt_row = mysql_fetch_row($cnt_res);
        $total = intval($cnt_row[0]);
        
        $query = "SELECT no FROM MlangOrder_PrintAuto WHERE no > 0" . $where . " ORDER BY no ASC LIMIT $offset, $limit";
        $res = mysql_query($query, $db);
        
        $items = array();
        while ($row = mysql_fetch_assoc($res)) {
            $no = intval($row['no']);
            $dir = $base_dir . '/' . $no;
            if (!is_dir($dir)) continue;
            
            $files = array();
            $dh = opendir($dir);
            if ($dh) {
                while (($f = readdir($dh)) !== false) {
                    if ($f === '.' || $f === '..') continue;
                    $fp = $dir . '/' . $f;
                    if (is_file($fp)) {
                        $fn_utf8 = @iconv('EUC-KR', 'UTF-8//IGNORE', $f);
                        if ($fn_utf8 === false) $fn_utf8 = $f;
                        $files[] = array(
                            'name' => $fn_utf8,
                            'size' => filesize($fp),
                            'path' => 'upload/' . $no . '/' . rawurlencode($f)
                        );
                    }
                }
                closedir($dh);
            }
            if (count($files) > 0) {
                $items[] = array('order_no' => $no, 'files' => $files);
            }
        }
        
        echo json_encode(array(
            'type' => 'upload',
            'total_orders' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'has_more' => ($offset + $limit) < $total,
            'items' => $items
        ));
        
    } elseif ($type === 'shop') {
        // 원고파일 (스티커): shop/data/ (DB의 ImgFolder에서 참조)
        $where = " WHERE ImgFolder LIKE '%shop/data/%'";
        // min_year 필터: 연도 기준 하한선 (예: 2026년 이상만)
        if ($min_year > 0) {
            $where .= " AND date >= '" . intval($min_year) . "-01-01'";
        }
        if ($since !== '') {
            $since_esc = mysql_real_escape_string($since, $db);
            $where .= " AND date >= '$since_esc'";
        }
        
        $cnt_res = mysql_query("SELECT COUNT(*) FROM MlangOrder_PrintAuto" . $where, $db);
        $cnt_row = mysql_fetch_row($cnt_res);
        $total = intval($cnt_row[0]);
        
        $query = "SELECT no, ImgFolder FROM MlangOrder_PrintAuto" . $where . " ORDER BY no ASC LIMIT $offset, $limit";
        $res = mysql_query($query, $db);
        
        $items = array();
        $base_dir = '/home/neo_web2/duson1830/www/shop/data';
        while ($row = mysql_fetch_assoc($res)) {
            $no = intval($row['no']);
            $img_raw = $row['ImgFolder'];
            // ../shop/data/ 제거하여 파일명만 추출
            $img = str_replace('../shop/data/', '', $img_raw);
            if (empty($img) || $img === $img_raw) {
                $img = basename($img_raw);
            }
            
            $fp = $base_dir . '/' . $img;
            $size = file_exists($fp) ? filesize($fp) : 0;
            
            $img_utf8 = @iconv('EUC-KR', 'UTF-8//IGNORE', $img);
            if ($img_utf8 === false) $img_utf8 = $img;
            
            if ($size > 0) {
                $items[] = array(
                    'order_no' => $no,
                    'files' => array(array(
                        'name' => $img_utf8,
                        'size' => $size,
                        'path' => 'shop/' . rawurlencode($img)
                    ))
                );
            }
        }
        
        echo json_encode(array(
            'type' => 'shop',
            'total_orders' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'has_more' => ($offset + $limit) < $total,
            'items' => $items
        ));
        
    } elseif ($type === 'imgfolder') {
        // 원고파일 (전단지,명함,봉투 등): ImgFolder/_MlangPrintAuto_* 경로
        $where = " WHERE ImgFolder LIKE '_MlangPrintAuto_%'";
        // min_year 필터: 연도 기준 하한선 (예: 2026년 이상만)
        if ($min_year > 0) {
            $where .= " AND date >= '" . intval($min_year) . "-01-01'";
        }
        if ($since !== '') {
            $since_esc = mysql_real_escape_string($since, $db);
            $where .= " AND date >= '$since_esc'";
        }
        
        $cnt_res = mysql_query("SELECT COUNT(*) FROM MlangOrder_PrintAuto" . $where, $db);
        $cnt_row = mysql_fetch_row($cnt_res);
        $total = intval($cnt_row[0]);
        
        $query = "SELECT no, ImgFolder FROM MlangOrder_PrintAuto" . $where . " ORDER BY no ASC LIMIT $offset, $limit";
        $res = mysql_query($query, $db);
        
        $items = array();
        $base_dir = '/home/neo_web2/duson1830/www/ImgFolder';
        while ($row = mysql_fetch_assoc($res)) {
            $no = intval($row['no']);
            $folder_path = $row['ImgFolder'];
            $dir = $base_dir . '/' . $folder_path;
            
            if (!is_dir($dir)) continue;
            
            $files = array();
            $dh = opendir($dir);
            if ($dh) {
                while (($f = readdir($dh)) !== false) {
                    if ($f === '.' || $f === '..') continue;
                    $fp = $dir . '/' . $f;
                    if (is_file($fp)) {
                        $fn_utf8 = @iconv('EUC-KR', 'UTF-8//IGNORE', $f);
                        if ($fn_utf8 === false) $fn_utf8 = $f;
                        $files[] = array(
                            'name' => $fn_utf8,
                            'size' => filesize($fp),
                            'path' => 'imgfolder/' . $folder_path . '/' . rawurlencode($f)
                        );
                    }
                }
                closedir($dh);
            }
            if (count($files) > 0) {
                $items[] = array('order_no' => $no, 'files' => $files);
            }
        }
        
        echo json_encode(array(
            'type' => 'imgfolder',
            'total_orders' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'has_more' => ($offset + $limit) < $total,
            'items' => $items
        ));
        
    } else {
        echo json_encode(array('error' => 'Invalid type. Use: upload or shop'));
    }
    exit;
}

// 파일 다운로드: ?key=...&table=_file_download&path=upload/84510/xxx.jpg 또는 shop/xxx.pdf
if ($table === '_file_download') {
    $path = isset($_GET['path']) ? $_GET['path'] : '';
    if (empty($path) || strpos($path, '..') !== false) {
        header('HTTP/1.0 400 Bad Request');
        die('Invalid path');
    }
    
    $base = '/home/neo_web2/duson1830/www';
    
    // URL-decode된 path에서 파일명 부분만 rawurldecode
    if (strpos($path, 'upload/') === 0) {
        // upload/{no}/{encoded_filename}
        $parts = explode('/', $path, 3);
        $decoded_path = $parts[0] . '/' . $parts[1] . '/' . rawurldecode(isset($parts[2]) ? $parts[2] : '');
        $filepath = $base . '/MlangOrder_PrintAuto/' . $decoded_path;
    } elseif (strpos($path, 'shop/') === 0) {
        $filepath = $base . '/shop/data/' . rawurldecode(substr($path, 5));
    } elseif (strpos($path, 'imgfolder/') === 0) {
        // imgfolder/{folder_path}/{encoded_filename} - 마지막 부분만 decode
        $rel = substr($path, 10);
        $last_slash = strrpos($rel, '/');
        if ($last_slash !== false) {
            $dir_part = substr($rel, 0, $last_slash);
            $file_part = rawurldecode(substr($rel, $last_slash + 1));
            $filepath = $base . '/ImgFolder/' . $dir_part . '/' . $file_part;
        } else {
            $filepath = $base . '/ImgFolder/' . rawurldecode($rel);
        }
    } else {
        header('HTTP/1.0 400 Bad Request');
        die('Invalid path prefix');
    }
    
    if (!file_exists($filepath)) {
        header('HTTP/1.0 404 Not Found');
        die('File not found');
    }
    
    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . filesize($filepath));
    header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
    readfile($filepath);
    exit;
}

// ============================================================
// DB 테이블 관련 API (기존)
// ============================================================

header('Content-Type: application/json; charset=utf-8');

if ($table === '_tables') {
    $db = mysql_connect("localhost", "duson1830", "du1830");
    mysql_select_db("duson1830", $db);
    $result = array();
    foreach ($ALLOWED_TABLES as $tbl) {
        $res = mysql_query("SELECT COUNT(*) FROM `$tbl`", $db);
        if ($res) {
            $row = mysql_fetch_row($res);
            $result[] = array('table' => $tbl, 'count' => intval($row[0]));
        }
    }
    echo json_encode(array('tables' => $result));
    exit;
}

if (!in_array($table, $ALLOWED_TABLES)) {
    echo json_encode(array('error' => 'Table not allowed: ' . $table));
    exit;
}

$db = mysql_connect("localhost", "duson1830", "du1830");
if (!$db) { echo json_encode(array('error' => 'DB failed')); exit; }
mysql_select_db("duson1830", $db);
mysql_query("SET NAMES 'euckr'", $db);

// 테이블별 PK 매핑 (no가 아닌 테이블)
$TABLE_PK = array(
    'users' => 'id',
    'qna' => 'id',
    'Mlang_board_bbs' => 'Mlang_bbs_no',
    'Mlang_portfolio_bbs' => 'Mlang_bbs_no',
);
$pk_col = isset($TABLE_PK[$table]) ? $TABLE_PK[$table] : 'no';

$where = '';
if ($since !== '' && $table === 'member') {
    $since_esc = mysql_real_escape_string($since, $db);
    $where = " WHERE date >= '$since_esc'";
} elseif ($since !== '' && $table === 'MlangOrder_PrintAuto') {
    $since_esc = mysql_real_escape_string($since, $db);
    $where = " WHERE date >= '$since_esc'";
}

$cnt_res = mysql_query("SELECT COUNT(*) FROM `$table`" . $where, $db);
$cnt_row = mysql_fetch_row($cnt_res);
$total = intval($cnt_row[0]);

$query = "SELECT * FROM `$table`" . $where . " ORDER BY `$pk_col` ASC LIMIT $offset, $limit";
$res = mysql_query($query, $db);
if (!$res) {
    echo json_encode(array('error' => mysql_error($db)));
    exit;
}

$rows = array();
while ($row = mysql_fetch_assoc($res)) {
    $converted = array();
    foreach ($row as $k => $v) {
        if ($v !== null) {
            $u = @iconv('EUC-KR', 'UTF-8//IGNORE', $v);
            $converted[$k] = ($u !== false) ? $u : $v;
        } else {
            $converted[$k] = null;
        }
    }
    $rows[] = $converted;
}

echo json_encode(array(
    'table' => $table,
    'total' => $total,
    'offset' => $offset,
    'limit' => $limit,
    'count' => count($rows),
    'has_more' => ($offset + $limit) < $total,
    'rows' => $rows
));
?>
