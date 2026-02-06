<?php
// admin/create_quotes_table.php
require_once __DIR__ . '/../db.php';
// Simple auth check. In a real app, use a proper session-based role check.
if (!isset($_SESSION['ss_id']) || strpos($_SESSION['ss_id'], 'admin') === false) {
    header('HTTP/1.1 403 Forbidden');
    die('Access denied. You must be an administrator to run this script.');
}

header('Content-Type: text/plain; charset=utf-8');

$table_name = 'quotes';

echo "Checking for '{$table_name}' table...\n";

try {
    $check_query = "SHOW TABLES LIKE '{$table_name}'";
    $result = mysqli_query($db, $check_query);

    if (mysqli_num_rows($result) > 0) {
        echo "Table '{$table_name}' already exists. No action taken.\n";
        exit;
    }

    echo "Table '{$table_name}' not found. Creating table...\n";

    $create_sql = "
    CREATE TABLE `{$table_name}` (
      `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      `quote_number` VARCHAR(50) NULL,
      `user_id` BIGINT UNSIGNED NULL,
      `customer_name` VARCHAR(255) NOT NULL,
      `quote_details` JSON NULL,
      `total_amount` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
      `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `quote_number` (`quote_number`),
      KEY `user_id` (`user_id`),
      KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    if (mysqli_query($db, $create_sql)) {
        echo "Table '{$table_name}' created successfully!\n";

        // Set a higher starting number for quotes to make them look more professional
        $alter_sql = "ALTER TABLE `{$table_name}` AUTO_INCREMENT = 10001;";
        mysqli_query($db, $alter_sql);
        echo "Set AUTO_INCREMENT starting value to 10001.\n";

    } else {
        echo "Error creating table '{$table_name}': " . mysqli_error($db) . "\n";
    }

} catch (Throwable $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
}

?>