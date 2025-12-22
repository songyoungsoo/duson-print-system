<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>API 직접 테스트</h2>";

// GET 파라미터 설정
$_GET['action'] = 'get_staff_rooms';
$_GET['staff_id'] = 'staff1';

echo "<p>Action: " . $_GET['action'] . "</p>";
echo "<p>Staff ID: " . $_GET['staff_id'] . "</p>";
echo "<hr>";

// API 파일 포함
include 'api.php';
?>
