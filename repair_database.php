<?php
/**
 * Database repair utility for tablespace issues
 * Run this once to clean up the users table issue
 */

include 'db.php';
$connect = $db;

if (!$connect) {
    die('Database connection failed: ' . mysqli_connect_error());
}

echo "<h2>Database Repair Utility</h2>";
echo "<pre>";

// Step 1: Show current tables
echo "1. Current tables in database:\n";
$tables_result = mysqli_query($connect, "SHOW TABLES");
while ($row = mysqli_fetch_row($tables_result)) {
    echo "   - " . $row[0] . "\n";
}

// Step 2: Drop users table completely
echo "\n2. Dropping users table if exists:\n";
$drop_result = mysqli_query($connect, "DROP TABLE IF EXISTS users");
if ($drop_result) {
    echo "   ✓ Users table dropped successfully\n";
} else {
    echo "   ✗ Error dropping table: " . mysqli_error($connect) . "\n";
}

// Step 3: Flush tables to clear cache
echo "\n3. Flushing table cache:\n";
mysqli_query($connect, "FLUSH TABLES");
echo "   ✓ Tables flushed\n";

// Step 4: Create fresh users table
echo "\n4. Creating fresh users table:\n";
$create_query = "CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$create_result = mysqli_query($connect, $create_query);
if ($create_result) {
    echo "   ✓ Users table created successfully\n";
} else {
    echo "   ✗ Error creating table: " . mysqli_error($connect) . "\n";
}

// Step 5: Create admin account
echo "\n5. Creating admin account:\n";
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_query = "INSERT INTO users (username, password, name, email) VALUES ('admin', ?, '관리자', 'admin@duson.co.kr')";
$stmt = mysqli_prepare($connect, $admin_query);
mysqli_stmt_bind_param($stmt, "s", $admin_password);

if (mysqli_stmt_execute($stmt)) {
    echo "   ✓ Admin account created (username: admin, password: admin123)\n";
} else {
    echo "   ✗ Error creating admin: " . mysqli_error($connect) . "\n";
}

// Step 6: Test the table
echo "\n6. Testing table functionality:\n";
$test_result = mysqli_query($connect, "SELECT id, username, name FROM users");
if ($test_result && mysqli_num_rows($test_result) > 0) {
    echo "   ✓ Table is working correctly\n";
    while ($user = mysqli_fetch_assoc($test_result)) {
        echo "   - User: " . $user['username'] . " (" . $user['name'] . ")\n";
    }
} else {
    echo "   ✗ Table test failed\n";
}

echo "\n=== Database repair completed ===\n";
echo "You can now delete this file and use the main website.\n";
echo "</pre>";

echo '<br><a href="index.php">Go to Main Page</a>';
?>