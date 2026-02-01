<?php
/**
 * 테이블명 대소문자 자동 변환 매퍼
 * 모든 대문자 테이블명을 소문자로 자동 변환
 * Created: 2025-08-29
 */

$GLOBALS['__TABLE_MAP'] = [
  'Member' => 'member',
  'Shop_Temp' => 'shop_temp',
  'mlangorder_printauto' => 'mlangorder_printauto',
  'mlangprintauto_Cadarok' => 'mlangprintauto_cadarok',
  'mlangprintauto_Envelope' => 'mlangprintauto_envelope',
  'mlangprintauto_Inserted' => 'mlangprintauto_inserted',
  'mlangprintauto_LittlePrint' => 'mlangprintauto_littleprint',
  'mlangprintauto_MerchandiseBond' => 'mlangprintauto_merchandisebond',
  'mlangprintauto_Msticker' => 'mlangprintauto_msticker',
  'mlangprintauto_Namecard' => 'mlangprintauto_namecard',
  'mlangprintauto_NcrFlambeau' => 'mlangprintauto_ncrflambeau',
  'mlangprintauto_Sticker' => 'mlangprintauto_sticker',
  'mlangprintauto_TransactionCate' => 'mlangprintauto_transactioncate',
  'mlangprintauto_transactionCate' => 'mlangprintauto_transactioncate', // 변형 추가
  'mlangprintauto_transactioncate' => 'mlangprintauto_transactioncate', // 변형 추가
];

/**
 * SQL 쿼리에서 테이블명을 자동으로 매핑
 * @param string $sql 원본 SQL 쿼리
 * @return string 변환된 SQL 쿼리
 */
function map_table_names($sql) {
    global $__TABLE_MAP;
    
    if (empty($__TABLE_MAP) || !is_array($__TABLE_MAP)) {
        return $sql;
    }
    
    foreach ($__TABLE_MAP as $old => $new) {
        // 백틱으로 감싸진 테이블명 변환
        $sql = preg_replace('/`' . preg_quote($old, '/') . '`/i', '`' . $new . '`', $sql);
        // 백틱 없는 테이블명 변환 (단어 경계 체크)
        $sql = preg_replace('/\b' . preg_quote($old, '/') . '\b/i', $new, $sql);
    }
    
    return $sql;
}

/**
 * 쿼리 실행 래퍼 함수 (mysqli용)
 * @param mysqli $db 데이터베이스 연결 객체
 * @param string $sql SQL 쿼리
 * @return mysqli_result|bool 쿼리 결과
 */
function q($db, $sql) {
    $converted_sql = map_table_names($sql);
    $result = $db->query($converted_sql);
    
    if (!$result) {
        error_log('[SQL ERROR] ' . $db->error . ' | Query: ' . $converted_sql);
    }
    
    return $result;
}

/**
 * Prepared statement용 쿼리 변환 함수
 * @param string $sql SQL 쿼리
 * @return string 변환된 SQL 쿼리
 */
function prepare_sql($sql) {
    return map_table_names($sql);
}

/**
 * mysqli_query 대체 함수
 * @param mysqli $db 데이터베이스 연결 객체
 * @param string $sql SQL 쿼리
 * @return mysqli_result|bool 쿼리 결과
 */
function safe_query($db, $sql) {
    return q($db, $sql);
}

/**
 * mysqli_prepare 대체 함수
 * @param mysqli $db 데이터베이스 연결 객체
 * @param string $sql SQL 쿼리
 * @return mysqli_stmt|bool prepared statement
 */
function safe_prepare($db, $sql) {
    $converted_sql = map_table_names($sql);
    $stmt = mysqli_prepare($db, $converted_sql);
    
    if (!$stmt) {
        error_log('[PREPARE ERROR] ' . $db->error . ' | Query: ' . $converted_sql);
    }
    
    return $stmt;
}