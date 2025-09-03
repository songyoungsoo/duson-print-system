<?php
// lowercase_sql.php
$inputFile = "duson1830_need.sql";
$outputFile = "duson1830_lower.sql";

$sql = file_get_contents($inputFile);

// 정규식으로 CREATE TABLE, INSERT INTO, REFERENCES 등 테이블명 추출 후 소문자 변환
$sql = preg_replace_callback(
    '/\b(CREATE TABLE|INSERT INTO|REFERENCES|ALTER TABLE|DROP TABLE)\s+`?([A-Za-z0-9_]+)`?/i',
    function ($matches) {
        return $matches[1] . " `" . strtolower($matches[2]) . "`";
    },
    $sql
);

// 저장
file_put_contents($outputFile, $sql);

echo "변환 완료: $outputFile\n";
