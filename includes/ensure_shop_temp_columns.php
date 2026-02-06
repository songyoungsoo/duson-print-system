<?php
/**
 * shop_temp 테이블 컬럼 자동 체크 및 추가
 * 
 * 새 서버(dsp1830.ipdisk.co.kr)에서 shop_temp 테이블 스키마가 구버전일 경우
 * 필요한 컬럼을 자동으로 추가합니다.
 * 
 * 사용법: require_once __DIR__ . '/../../includes/ensure_shop_temp_columns.php';
 *         ensure_shop_temp_columns($db);
 * 
 * @param mysqli $db 데이터베이스 연결
 * @param string $table 테이블명 (기본: shop_temp)
 */
function ensure_shop_temp_columns($db, $table = 'shop_temp') {
    static $checked = [];
    
    // 같은 요청에서 같은 테이블을 중복 체크하지 않음
    if (isset($checked[$table])) {
        return;
    }
    $checked[$table] = true;
    
    $required_columns = [
        // 레거시 필드
        'Section' => 'VARCHAR(50) DEFAULT NULL',
        'TreeSelect' => 'VARCHAR(50) DEFAULT NULL',
        'work_memo' => 'TEXT',
        'upload_method' => "VARCHAR(20) DEFAULT 'upload'",
        'uploaded_files' => 'TEXT',
        'uploaded_files_info' => 'TEXT',
        'upload_folder' => 'VARCHAR(255) DEFAULT NULL',
        'ThingCate' => 'VARCHAR(255) DEFAULT NULL',
        'ImgFolder' => 'VARCHAR(255) DEFAULT NULL',
        'customer_name' => 'VARCHAR(100) DEFAULT NULL',
        'customer_phone' => 'VARCHAR(50) DEFAULT NULL',
        'original_filename' => 'TEXT',
        // 추가 옵션 필드
        'additional_options' => 'TEXT',
        'additional_options_total' => 'INT DEFAULT 0',
        'coating_enabled' => 'TINYINT(1) DEFAULT 0',
        'coating_type' => 'VARCHAR(20) DEFAULT NULL',
        'coating_price' => 'INT DEFAULT 0',
        'folding_enabled' => 'TINYINT(1) DEFAULT 0',
        'folding_type' => 'VARCHAR(20) DEFAULT NULL',
        'folding_price' => 'INT DEFAULT 0',
        'creasing_enabled' => 'TINYINT(1) DEFAULT 0',
        'creasing_lines' => 'INT DEFAULT 0',
        'creasing_price' => 'INT DEFAULT 0',
        'selected_options' => 'TEXT',
        'premium_options' => 'TEXT',
        'premium_options_total' => 'INT DEFAULT 0',
        // 봉투 전용
        'envelope_tape_enabled' => 'TINYINT(1) DEFAULT 0',
        'envelope_tape_quantity' => 'INT DEFAULT 0',
        'envelope_tape_price' => 'INT DEFAULT 0',
        'envelope_additional_options_total' => 'INT DEFAULT 0',
        // 옵션명
        'MY_type_name' => 'VARCHAR(100) DEFAULT NULL',
        'Section_name' => 'VARCHAR(100) DEFAULT NULL',
        'POtype_name' => 'VARCHAR(100) DEFAULT NULL',
        // Phase 2 표준 필드
        'spec_type' => 'VARCHAR(50) DEFAULT NULL',
        'spec_material' => 'VARCHAR(50) DEFAULT NULL',
        'spec_size' => 'VARCHAR(100) DEFAULT NULL',
        'spec_sides' => 'VARCHAR(20) DEFAULT NULL',
        'spec_design' => 'VARCHAR(20) DEFAULT NULL',
        'quantity_value' => 'DECIMAL(10,2) DEFAULT NULL',
        'quantity_unit' => "VARCHAR(10) DEFAULT '매'",
        'quantity_sheets' => 'INT DEFAULT NULL',
        'quantity_display' => 'VARCHAR(100) DEFAULT NULL',
        'price_supply' => 'INT NOT NULL DEFAULT 0',
        'price_vat' => 'INT NOT NULL DEFAULT 0',
        'price_vat_amount' => 'INT NOT NULL DEFAULT 0',
        'product_data_json' => 'TEXT',
        'data_version' => 'TINYINT DEFAULT 1',
    ];
    
    // 현재 테이블의 컬럼 목록을 한번에 가져옴
    $existing_columns = [];
    $result = mysqli_query($db, "SHOW COLUMNS FROM `{$table}`");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $existing_columns[$row['Field']] = true;
        }
        mysqli_free_result($result);
    } else {
        error_log("ensure_shop_temp_columns: 테이블 {$table} 조회 실패 - " . mysqli_error($db));
        return;
    }
    
    // 누락된 컬럼만 추가
    foreach ($required_columns as $column_name => $column_definition) {
        if (!isset($existing_columns[$column_name])) {
            $alter_sql = "ALTER TABLE `{$table}` ADD COLUMN `{$column_name}` {$column_definition}";
            if (mysqli_query($db, $alter_sql)) {
                error_log("ensure_shop_temp_columns: {$table}.{$column_name} 추가 완료");
            } else {
                error_log("ensure_shop_temp_columns: {$table}.{$column_name} 추가 실패 - " . mysqli_error($db));
            }
        }
    }
}
