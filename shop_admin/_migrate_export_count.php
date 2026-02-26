<?php
// 1회성 마이그레이션: logen_export_count 컬럼 추가
// 실행 후 즉시 삭제할 것
header('Content-Type: text/plain; charset=utf-8');

include "../db.php";
$connect = $db;

// 컬럼 존재 여부 확인
$check = mysqli_query($connect, "SHOW COLUMNS FROM mlangorder_printauto LIKE 'logen_export_count'");
if (mysqli_num_rows($check) > 0) {
    echo "OK: logen_export_count column already exists.\n";
} else {
    $result = mysqli_query($connect, "ALTER TABLE mlangorder_printauto ADD COLUMN logen_export_count INT NOT NULL DEFAULT 0");
    if ($result) {
        echo "OK: logen_export_count column added successfully.\n";
    } else {
        echo "ERROR: " . mysqli_error($connect) . "\n";
    }
}
