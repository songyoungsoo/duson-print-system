<?
$result= mysql_query("select * from $GGTABLE where no='$no'",$db);
$row= mysql_fetch_array($result);
$View_Ttable="$row[Ttable]";
$View_style="$row[style]";
$View_BigNo="$row[BigNo]";
$View_title="$row[title]";
$View_TreeNo="$row[TreeNo]";
?>