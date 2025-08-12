<?php
session_start();
$session_id = session_id();

include "../lib/func.php";
$connect = dbconn();

$no = isset($_GET['no']) ? intval($_GET['no']) : 0;
if ($no > 0) {
    $query = "DELETE FROM shop_temp WHERE no='$no' AND session_id='$session_id'";
    $result = mysqli_query($connect, $query);
    if (!$result) {
        die(mysqli_error($connect));
    }
}
?>

<script>
  location.href = 'basket.php';
</script>  
