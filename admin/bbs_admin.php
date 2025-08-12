<?php
if (isset($_GET['mode'])) {
    $mode = $_GET['mode'];
    if ($mode == "list") {
        include "top.php";
        include "bbs/list.php";
        include "down.php";
    } elseif ($mode == "submit") {
        include "bbs/submit.php";
    }
}
?>