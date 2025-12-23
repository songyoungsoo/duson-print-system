<?php
// Simple test page to verify is_admin functionality on production
include "../db.php";

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Admin Check</title></head><body>";
echo "<h2>🔍 Admin Column Verification</h2>";

// Check if is_admin column exists
$result = mysqli_query($db, "SHOW COLUMNS FROM users LIKE 'is_admin'");
if (mysqli_num_rows($result) > 0) {
    echo "✅ is_admin column exists<br>";
    $row = mysqli_fetch_assoc($result);
    echo "Type: " . $row['Type'] . ", Default: " . $row['Default'] . "<br><br>";
} else {
    echo "❌ is_admin column NOT FOUND<br><br>";
}

// Check admin users
$result = mysqli_query($db, "SELECT id, username, name, is_admin FROM users WHERE is_admin = 1");
echo "<h3>Admin Users (is_admin=1):</h3>";
if (mysqli_num_rows($result) > 0) {
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<li>ID: {$row['id']}, Username: {$row['username']}, Name: {$row['name']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No admin users found</p>";
}

// Check session admin status
session_start();
echo "<h3>Current Session:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "Logged in as: User ID " . $_SESSION['user_id'];
    if (isset($_SESSION['is_admin'])) {
        echo " (is_admin: " . $_SESSION['is_admin'] . ")";
    } else {
        echo " (is_admin not set in session)";
    }
} else {
    echo "Not logged in";
}

echo "</body></html>";
?>
