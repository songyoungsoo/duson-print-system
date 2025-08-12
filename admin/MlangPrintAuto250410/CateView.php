<?php
$result= mysqli_query($db, "select * from $GGTABLE where no='$no'");
$row= mysqli_fetch_array($result);
$View_Ttable="$row[Ttable]";
$View_style="$row[style]";
$View_BigNo="$row[BigNo]";
$View_title="$row[title]";
$View_TreeNo="$row[TreeNo]";
?>