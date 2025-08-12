<?php
if (isset($mode) && $mode === "list") {
    include "./top.php";
    include "./bbs/list.php";
    include "./down.php";
}

if (isset($mode) && $mode === "submit") {
    include "./bbs/submit.php";
}
?>