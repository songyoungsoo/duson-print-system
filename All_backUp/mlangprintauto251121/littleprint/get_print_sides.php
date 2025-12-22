<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

// 인쇄면 옵션은 고정값
$print_sides = [
    ['value' => '1', 'text' => '단면인쇄'],
    ['value' => '2', 'text' => '양면인쇄']
];

echo json_encode($print_sides);
?>