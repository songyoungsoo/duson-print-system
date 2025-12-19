<?php
/**
 * SQL Structure Verification Script
 * This script verifies the OnlineOrder.php INSERT query structure
 * Requirements: 1.2, 2.2, 2.5
 */

echo "=== SQL Query Structure Verification ===\n\n";

// Read the OnlineOrder.php file
$file_content = file_get_contents('OnlineOrder.php');

// Check 1: Verify explicit column names are used
echo "Check 1: Verify INSERT uses explicit column names\n";
if (preg_match('/INSERT INTO MlangOrder_PrintAuto\s*\([^)]+\)\s*VALUES/s', $file_content)) {
    echo "✓ PASS: INSERT statement uses explicit column names\n";
    
    // Extract the column list
    preg_match('/INSERT INTO MlangOrder_PrintAuto\s*\(([^)]+)\)/s', $file_content, $matches);
    $columns = $matches[1];
    
    // Count columns
    $column_array = array_map('trim', explode(',', $columns));
    $column_count = count($column_array);
    
    echo "  Found $column_count columns:\n";
    foreach ($column_array as $idx => $col) {
        echo "  " . ($idx + 1) . ". $col\n";
    }
} else {
    echo "✗ FAIL: INSERT statement does NOT use explicit column names\n";
}

echo "\n";

// Check 2: Verify column count matches table schema (30 columns)
echo "Check 2: Verify column count matches table schema (30 columns)\n";
if ($column_count == 30) {
    echo "✓ PASS: Column count is exactly 30\n";
} else {
    echo "✗ FAIL: Column count is $column_count (expected 30)\n";
}

echo "\n";

// Check 3: Verify all expected columns are present
echo "Check 3: Verify all expected columns are present\n";
$expected_columns = [
    'no', 'Type', 'ImgFolder', 'Type_1',
    'money_1', 'money_2', 'money_3', 'money_4', 'money_5',
    'name', 'email', 'zip', 'zip1', 'zip2',
    'phone', 'Hendphone', 'delivery', 'bizname',
    'bank', 'bankname', 'cont', 'date',
    'OrderStyle', 'ThingCate', 'pass', 'Gensu',
    'Designer', 'logen_box_qty', 'logen_delivery_fee', 'logen_fee_type'
];

$all_present = true;
foreach ($expected_columns as $expected) {
    if (!in_array($expected, $column_array)) {
        echo "✗ FAIL: Missing column: $expected\n";
        $all_present = false;
    }
}

if ($all_present) {
    echo "✓ PASS: All 30 expected columns are present\n";
}

echo "\n";

// Check 4: Verify NULL values for optional fields
echo "Check 4: Verify NULL values for optional logistics fields\n";
preg_match('/VALUES\s*\(([^)]+)\)/s', $file_content, $value_matches);
if (isset($value_matches[1])) {
    $values_section = $value_matches[1];
    
    // Check for NULL values (should appear 5 times: ThingCate, Designer, logen_box_qty, logen_delivery_fee, logen_fee_type)
    $null_count = substr_count($values_section, 'NULL');
    
    if ($null_count >= 5) {
        echo "✓ PASS: Found $null_count NULL values for optional fields\n";
        echo "  Expected NULL for: ThingCate, Designer, logen_box_qty, logen_delivery_fee, logen_fee_type\n";
    } else {
        echo "✗ FAIL: Found only $null_count NULL values (expected at least 5)\n";
    }
}

echo "\n";

// Check 5: Verify only mysql_* functions are used (PHP 5.2 compatibility)
echo "Check 5: Verify PHP 5.2 compatibility (mysql_* functions only)\n";
$mysql_functions_used = [];
if (preg_match_all('/\b(mysql_[a-z_]+)\s*\(/i', $file_content, $func_matches)) {
    $mysql_functions_used = array_unique($func_matches[1]);
}

$mysqli_functions = [];
if (preg_match_all('/\b(mysqli_[a-z_]+)\s*\(/i', $file_content, $mysqli_matches)) {
    $mysqli_functions = array_unique($mysqli_matches[1]);
}

$pdo_usage = preg_match('/\bnew\s+PDO\s*\(/i', $file_content);

echo "  mysql_* functions found: " . implode(', ', $mysql_functions_used) . "\n";

if (empty($mysqli_functions) && !$pdo_usage) {
    echo "✓ PASS: Only mysql_* functions used (PHP 5.2 compatible)\n";
} else {
    if (!empty($mysqli_functions)) {
        echo "✗ FAIL: mysqli_* functions found: " . implode(', ', $mysqli_functions) . "\n";
    }
    if ($pdo_usage) {
        echo "✗ FAIL: PDO usage found\n";
    }
}

echo "\n";

// Check 6: Verify debug line is present
echo "Check 6: Verify SQL debug output capability\n";
if (preg_match('/\/\/\s*echo\s+\$dbinsert\s*;\s*exit\s*;/i', $file_content)) {
    echo "✓ PASS: Debug line '//echo \$dbinsert; exit;' is present\n";
    echo "  To debug SQL, uncomment this line in OnlineOrder.php\n";
} else {
    echo "✗ FAIL: Debug line not found or not properly commented\n";
}

echo "\n";

// Check 7: Verify error handling for INSERT failures
echo "Check 7: Verify error handling for INSERT failures\n";
if (preg_match('/mysql_error\s*\(/i', $file_content)) {
    echo "✓ PASS: mysql_error() is used for error handling\n";
} else {
    echo "✗ FAIL: mysql_error() not found in error handling\n";
}

if (preg_match('/error_log\s*\(/i', $file_content)) {
    echo "✓ PASS: error_log() is used for logging errors\n";
} else {
    echo "  WARNING: error_log() not found (errors may not be logged)\n";
}

echo "\n";

// Check 8: Verify input validation
echo "Check 8: Verify input validation for required fields\n";
$required_fields = ['name', 'phone', 'email'];
$validation_found = 0;

foreach ($required_fields as $field) {
    if (preg_match('/empty\s*\(\s*\$' . $field . '\s*\)/i', $file_content)) {
        $validation_found++;
    }
}

if ($validation_found == count($required_fields)) {
    echo "✓ PASS: Validation found for all required fields (name, phone, email)\n";
} else {
    echo "  WARNING: Validation found for $validation_found/" . count($required_fields) . " required fields\n";
}

echo "\n";
echo "=== Verification Complete ===\n";
echo "\nNext Steps:\n";
echo "1. To view the actual SQL query, uncomment line: //echo \$dbinsert; exit;\n";
echo "2. Submit a test order to verify the query executes successfully\n";
echo "3. Check the database to confirm all 30 fields are populated correctly\n";
?>
