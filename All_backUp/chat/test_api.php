<?php
// API 테스트 스크립트
error_reporting(E_ALL);
ini_set('display_errors', 1);

$_GET['action'] = 'get_staff_rooms';
$_GET['staff_id'] = 'staff1';

echo "Testing API...\n";
echo "Action: " . $_GET['action'] . "\n";
echo "Staff ID: " . $_GET['staff_id'] . "\n\n";

include 'api.php';
?>
