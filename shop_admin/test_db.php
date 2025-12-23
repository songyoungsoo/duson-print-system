<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. Starting test...<br>";

echo "2. Including db.php...<br>";
include "../db.php";

echo "3. Connection result: ";
var_dump($db);
echo "<br>";

echo "4. Testing safe_mysqli_query function...<br>";
if (function_exists('safe_mysqli_query')) {
    echo "safe_mysqli_query exists!<br>";
} else {
    echo "safe_mysqli_query does NOT exist!<br>";
}

echo "5. Testing query...<br>";
$query = "select count(*) from mlangorder_printauto where (zip1 like '%구%' ) or (zip2 like '%-%')";
echo "Query: $query<br>";

$result = safe_mysqli_query($db, $query);
echo "6. Result: ";
var_dump($result);
echo "<br>";

if ($result) {
    $data = mysqli_fetch_array($result);
    echo "7. Count: " . $data[0] . "<br>";
} else {
    echo "7. Error: " . mysqli_error($db) . "<br>";
}

echo "8. Test complete!<br>";
?>
