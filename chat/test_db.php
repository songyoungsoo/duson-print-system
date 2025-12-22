<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing database connection...<br><br>";

try {
    require_once __DIR__ . '/../db.php';
    echo "✓ Database connection successful!<br>";
    echo "Database: " . $dataname . "<br><br>";

    // Test chatrooms table
    $result = mysqli_query($db, "SELECT COUNT(*) as count FROM chatrooms");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "✓ chatrooms table exists: " . $row['count'] . " rooms<br>";
    } else {
        echo "✗ chatrooms table error: " . mysqli_error($db) . "<br>";
    }

    // Test chatparticipants table
    $result = mysqli_query($db, "SELECT COUNT(*) as count FROM chatparticipants");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "✓ chatparticipants table exists: " . $row['count'] . " participants<br>";
    } else {
        echo "✗ chatparticipants table error: " . mysqli_error($db) . "<br>";
    }

    // Test chatmessages table
    $result = mysqli_query($db, "SELECT COUNT(*) as count FROM chatmessages");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "✓ chatmessages table exists: " . $row['count'] . " messages<br>";
    } else {
        echo "✗ chatmessages table error: " . mysqli_error($db) . "<br>";
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}
?>
