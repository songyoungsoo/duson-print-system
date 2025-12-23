<?php
include "db.php";

$no = 84080;
$stmt = $db->prepare("SELECT no, ImgFolder, ThingCate, Type, name FROM mlangorder_printauto WHERE no = ?");
$stmt->bind_param("i", $no);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo "<pre>";
echo "주문번호: " . $row['no'] . "\n";
echo "ImgFolder: " . ($row['ImgFolder'] ?: '(비어있음)') . "\n";
echo "ThingCate: " . ($row['ThingCate'] ?: '(비어있음)') . "\n";
echo "Type: " . $row['Type'] . "\n";
echo "주문자: " . $row['name'] . "\n";
echo "</pre>";

$stmt->close();
$db->close();
?>
