<?php
function ensure_order_table_columns($db, $table = 'mlangorder_printauto') {
    static $checked = [];
    
    if (isset($checked[$table])) {
        return;
    }
    $checked[$table] = true;
    
    $required_columns = [
        'product_type' => "VARCHAR(50) DEFAULT 'sticker'",
        'is_custom_product' => 'TINYINT(1) DEFAULT 0',
        'uploaded_files' => 'TEXT',
        'proofreading_confirmed' => 'TINYINT(1) DEFAULT 0',
        'proofreading_date' => 'DATETIME DEFAULT NULL',
        'proofreading_by' => 'VARCHAR(100) DEFAULT NULL',
        'coating_enabled' => 'TINYINT(1) DEFAULT 0',
        'coating_type' => 'VARCHAR(20) DEFAULT NULL',
        'coating_price' => 'INT DEFAULT 0',
        'folding_enabled' => 'TINYINT(1) DEFAULT 0',
        'folding_type' => 'VARCHAR(20) DEFAULT NULL',
        'folding_price' => 'INT DEFAULT 0',
        'creasing_enabled' => 'TINYINT(1) DEFAULT 0',
        'creasing_lines' => 'INT DEFAULT 0',
        'creasing_price' => 'INT DEFAULT 0',
        'additional_options_total' => 'INT DEFAULT 0',
        'envelope_additional_options' => 'TEXT',
        'envelope_options_price' => 'INT DEFAULT 0',
        'premium_options' => 'TEXT',
        'premium_options_total' => 'INT DEFAULT 0',
        'envelope_tape_enabled' => 'TINYINT(1) DEFAULT 0',
        'envelope_tape_quantity' => 'INT DEFAULT 0',
        'envelope_tape_price' => 'INT DEFAULT 0',
        'envelope_additional_options_total' => 'INT DEFAULT 0',
        'is_member' => 'TINYINT(1) DEFAULT 0',
        'quote_id' => 'INT DEFAULT NULL',
        'quote_no' => 'VARCHAR(50) DEFAULT NULL',
        'quote_item_id' => 'INT DEFAULT NULL',
        'custom_product_name' => 'VARCHAR(200) DEFAULT NULL',
        'custom_specification' => 'TEXT',
        'quantity' => 'VARCHAR(50) DEFAULT NULL',
        'unit' => "VARCHAR(10) DEFAULT '매'",
        'order_group_id' => 'VARCHAR(50) DEFAULT NULL',
        'order_group_seq' => 'INT DEFAULT NULL',
        'logen_box_qty' => 'TINYINT DEFAULT NULL',
        'logen_delivery_fee' => 'INT DEFAULT NULL',
        'logen_fee_type' => 'VARCHAR(10) DEFAULT NULL',
        'logen_tracking_no' => 'VARCHAR(50) DEFAULT NULL',
        'waybill_date' => 'DATETIME DEFAULT NULL',
        'delivery_company' => "VARCHAR(20) DEFAULT '로젠택배'",
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
        'migrated_at' => 'DATETIME DEFAULT NULL',
    ];
    
    $existing_columns = [];
    $result = mysqli_query($db, "SHOW COLUMNS FROM `{$table}`");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $existing_columns[$row['Field']] = true;
        }
        mysqli_free_result($result);
    } else {
        error_log("ensure_order_table_columns: {$table} 조회 실패 - " . mysqli_error($db));
        return;
    }
    
    foreach ($required_columns as $column_name => $column_definition) {
        if (!isset($existing_columns[$column_name])) {
            $alter_sql = "ALTER TABLE `{$table}` ADD COLUMN `{$column_name}` {$column_definition}";
            if (mysqli_query($db, $alter_sql)) {
                error_log("ensure_order_table_columns: {$table}.{$column_name} 추가 완료");
            } else {
                error_log("ensure_order_table_columns: {$table}.{$column_name} 추가 실패 - " . mysqli_error($db));
            }
        }
    }
}
