<?php
require_once '../../db.php';

if (php_sapi_name() !== 'cli' && !isset($_GET['confirm'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Fix Password Reset Tables</title>
        <style>
            body { font-family: monospace; padding: 20px; }
            .error { color: red; }
            .success { color: green; }
            .warning { background: yellow; padding: 10px; }
        </style>
    </head>
    <body>
        <h1>Password Reset Tables Fix</h1>
        <div class="warning">
            <strong>⚠️ WARNING:</strong> This will modify the database structure.<br>
            Make sure you have a backup before proceeding.
        </div>
        <p>This script will:</p>
        <ol>
            <li>Create `password_resets` table if not exists</li>
            <li>Add `reset_token` and `reset_expires` columns to `member` table</li>
            <li>Clean up expired tokens</li>
        </ol>
        <p>
            <a href="?confirm=1" onclick="return confirm('Are you sure you want to run this migration?')">
                ▶️ Run Migration
            </a>
        </p>
    </body>
    </html>
    <?php
    exit;
}

echo "<pre>\n";
echo "=== Password Reset Tables Fix ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

$errors = [];
$success = [];

try {
    $query = "CREATE TABLE IF NOT EXISTS `password_resets` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `email` VARCHAR(200) NOT NULL,
        `token` VARCHAR(255) NOT NULL UNIQUE,
        `expires_at` DATETIME NOT NULL,
        `used` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_token` (`token`),
        INDEX `idx_email` (`email`),
        INDEX `idx_expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (mysqli_query($db, $query)) {
        $success[] = "✅ Created/verified password_resets table";
    } else {
        $errors[] = "❌ Failed to create password_resets table: " . mysqli_error($db);
    }

} catch (Exception $e) {
    $errors[] = "❌ Exception creating password_resets: " . $e->getMessage();
}

$check_query = "SELECT COLUMN_NAME 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'member' 
                AND COLUMN_NAME = 'reset_token'";
$result = mysqli_query($db, $check_query);
if (mysqli_num_rows($result) == 0) {
    $alter_query = "ALTER TABLE `member` ADD COLUMN `reset_token` VARCHAR(255) DEFAULT NULL";
    if (mysqli_query($db, $alter_query)) {
        $success[] = "✅ Added reset_token column to member table";
    } else {
        $errors[] = "❌ Failed to add reset_token column: " . mysqli_error($db);
    }
} else {
    $success[] = "ℹ️ reset_token column already exists";
}

$check_query = "SELECT COLUMN_NAME 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'member' 
                AND COLUMN_NAME = 'reset_expires'";
$result = mysqli_query($db, $check_query);
if (mysqli_num_rows($result) == 0) {
    $alter_query = "ALTER TABLE `member` ADD COLUMN `reset_expires` DATETIME DEFAULT NULL";
    if (mysqli_query($db, $alter_query)) {
        $success[] = "✅ Added reset_expires column to member table";
    } else {
        $errors[] = "❌ Failed to add reset_expires column: " . mysqli_error($db);
    }
} else {
    $success[] = "ℹ️ reset_expires column already exists";
}

$cleanup_query = "DELETE FROM `password_resets` WHERE `expires_at` < NOW()";
if (mysqli_query($db, $cleanup_query)) {
    $affected = mysqli_affected_rows($db);
    $success[] = "✅ Cleaned up $affected expired tokens from password_resets";
} else {
    $errors[] = "⚠️ Could not clean expired tokens: " . mysqli_error($db);
}

$cleanup_query = "UPDATE `member` SET `reset_token` = NULL, `reset_expires` = NULL WHERE `reset_expires` < NOW()";
if (mysqli_query($db, $cleanup_query)) {
    $affected = mysqli_affected_rows($db);
    $success[] = "✅ Cleaned up $affected expired tokens from member table";
} else {
    $errors[] = "⚠️ Could not clean member tokens: " . mysqli_error($db);
}

echo "=== Results ===\n\n";

if (!empty($success)) {
    echo "Success:\n";
    foreach ($success as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "Errors:\n";
    foreach ($errors as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

$verify_query = "DESCRIBE `password_resets`";
$result = mysqli_query($db, $verify_query);
if ($result) {
    echo "=== password_resets Table Structure ===\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo sprintf("  %-20s %-20s %-10s %s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'],
            $row['Key'] ? "KEY:" . $row['Key'] : ""
        );
    }
    echo "\n";
}

$verify_query = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
                 FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = DATABASE() 
                 AND TABLE_NAME = 'member' 
                 AND COLUMN_NAME IN ('reset_token', 'reset_expires')";
$result = mysqli_query($db, $verify_query);
if ($result && mysqli_num_rows($result) > 0) {
    echo "=== member Table Reset Columns ===\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo sprintf("  %-20s %-20s %s\n", 
            $row['COLUMN_NAME'], 
            $row['DATA_TYPE'], 
            $row['IS_NULLABLE']
        );
    }
}

echo "\n=== Migration Complete ===\n";

if (empty($errors)) {
    echo "✅ All migrations successful!\n";
    echo "\nYou can now test the password reset at:\n";
    echo "https://dsp114.co.kr/member/password_reset_request.php\n";
} else {
    echo "⚠️ Migration completed with some errors. Please review above.\n";
}

echo "</pre>\n";

mysqli_close($db);
?>