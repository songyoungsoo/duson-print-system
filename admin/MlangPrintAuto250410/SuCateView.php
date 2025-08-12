<?php
include "../../db.php";

$no = isset($_GET['no']) ? $_GET['no'] : (isset($_POST['no']) ? $_POST['no'] : '');

$db = new mysqli($host, $user, $password, $dataname);
if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}
$db->set_charset("utf8");

$stmt = $db->prepare("SELECT * FROM $GGTABLESu WHERE no=?");
$stmt->bind_param("i", $no);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    $View_Ttable = $row['Ttable'];
    $View_style = $row['style'];
    $View_BigNo = $row['BigNo'];
    $View_title = $row['title'];
} else {
    echo ("<script language='javascript'>
            window.alert('ERROR - No record found for no: $no.');
           </script>");
    exit;
}

$stmt->close();
$db->close();
?>
