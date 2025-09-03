<?php
include 'db.php';

echo "<h2>Database Table Analysis</h2>";
echo "<h3>Current Database: duson1830</h3>";

// Check all tables
$tables = mysqli_query($db, "SHOW TABLES");
echo "<h4>All Tables:</h4><ul>";
while ($table = mysqli_fetch_row($tables)) {
    echo "<li>" . $table[0] . "</li>";
}
echo "</ul>";

// Check member table structure if exists
$member_check = mysqli_query($db, "SHOW TABLES LIKE 'member'");
if (mysqli_num_rows($member_check) > 0) {
    echo "<h4>MEMBER Table Structure:</h4>";
    $member_desc = mysqli_query($db, "DESCRIBE member");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($member_desc)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show sample data
    $member_data = mysqli_query($db, "SELECT * FROM member LIMIT 5");
    echo "<h4>MEMBER Sample Data:</h4>";
    echo "<table border='1'>";
    $first_row = true;
    while ($row = mysqli_fetch_assoc($member_data)) {
        if ($first_row) {
            echo "<tr>";
            foreach (array_keys($row) as $column) {
                echo "<th>" . $column . "</th>";
            }
            echo "</tr>";
            $first_row = false;
        }
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p><strong>MEMBER table does not exist</strong></p>";
}

// Check users table structure if exists
$users_check = mysqli_query($db, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($users_check) > 0) {
    echo "<h4>USERS Table Structure:</h4>";
    $users_desc = mysqli_query($db, "DESCRIBE users");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($users_desc)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show sample data
    $users_data = mysqli_query($db, "SELECT * FROM users LIMIT 5");
    echo "<h4>USERS Sample Data:</h4>";
    echo "<table border='1'>";
    $first_row = true;
    while ($row = mysqli_fetch_assoc($users_data)) {
        if ($first_row) {
            echo "<tr>";
            foreach (array_keys($row) as $column) {
                echo "<th>" . $column . "</th>";
            }
            echo "</tr>";
            $first_row = false;
        }
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p><strong>USERS table does not exist</strong></p>";
}
?>