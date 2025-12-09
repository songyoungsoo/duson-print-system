<HEAD>
<SCRIPT LANGUAGE="JavaScript">
function Activity(name, list){
this.name = name;
this.list = list;
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////

var acts = new Array();

<?
include"../../db.php";
$result= mysql_query("select * from $GGTABLE where Ttable='$Ttable' and BigNo='0' order by no asc",$db);
$rows=mysql_num_rows($result);
if($rows){
	$g=0;
while($row= mysql_fetch_array($result)) { 
?>
acts[<?=$g?>] = new Activity('<?=$row[no]?>', [<?

              $result_Two= mysql_query("select * from $GGTABLE where BigNo='$row[no]' order by no asc",$db);
                $rows_Two=mysql_num_rows($result_Two);
                  if($rows_Two){
                     while($row_Two= mysql_fetch_array($result_Two)) { 
                                echo("'$row_Two[title]',");
                               }  }
      ?>'==================']);

<?
	$g++;
}

}else{echo("<option>등록자료  없음</option>");}
mysql_close($db); 
?>


var VL = new Array();

<?
include"../../db.php";
$result= mysql_query("select * from $GGTABLE where Ttable='$Ttable' and BigNo='0' order by no asc",$db);
$rows=mysql_num_rows($result);
if($rows){
	$g=0;
while($row= mysql_fetch_array($result)) { 
?>
VL[<?=$g?>] = new Activity('<?=$row[no]?>', [<?

              $result_Two= mysql_query("select * from $GGTABLE where BigNo='$row[no]' order by no asc",$db);
                $rows_Two=mysql_num_rows($result_Two);
                  if($rows_Two){
                     while($row_Two= mysql_fetch_array($result_Two)) { 
                                echo("'$row_Two[no]',");
                               }  }
      ?>'==================']);

<?
	$g++;
}

}else{echo("<option>등록자료  없음</option>");}
mysql_close($db); 
?>

 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

var actsmyListTreeSelect = new Array();

<?
include"../../db.php";
$result= mysql_query("select * from $GGTABLE where Ttable='$Ttable' and BigNo='0' order by no asc",$db);
$rows=mysql_num_rows($result);
if($rows){
	$g=0;
while($row= mysql_fetch_array($result)) { 
?>
actsmyListTreeSelect[<?=$g?>] = new Activity('<?=$row[no]?>', [<?

              $result_Two= mysql_query("select * from $GGTABLE where TreeNo='$row[no]' order by no asc",$db);
                $rows_Two=mysql_num_rows($result_Two);
                  if($rows_Two){
                     while($row_Two= mysql_fetch_array($result_Two)) { 
                                echo("'$row_Two[title]',");
                               }  }
      ?>'==================']);

<?
	$g++;
}

}else{echo("<option>등록자료  없음</option>");}
mysql_close($db); 
?>

var VLmyListTreeSelect = new Array();

<?
include"../../db.php";
$result= mysql_query("select * from $GGTABLE where Ttable='$Ttable' and BigNo='0' order by no asc",$db);
$rows=mysql_num_rows($result);
if($rows){
	$g=0;
while($row= mysql_fetch_array($result)) { 
?>
VLmyListTreeSelect [<?=$g?>] = new Activity('<?=$row[no]?>', [<?

              $result_Two= mysql_query("select * from $GGTABLE where TreeNo='$row[no]' order by no asc",$db);
                $rows_Two=mysql_num_rows($result_Two);
                  if($rows_Two){
                     while($row_Two= mysql_fetch_array($result_Two)) { 
                                echo("'$row_Two[no]',");
                               }  }
      ?>'==================']);

<?
	$g++;
}

}else{echo("<option>등록자료  없음</option>");}
mysql_close($db); 
?>


function updateList(str){

var frm = document.myForm;
var oriLen = frm.myList.length;
var numActs;

for (var i = 0; i < acts.length; i++){
if (str == acts[i].name) {
numActs = acts[i].list.length;
for (var j = 0; j < numActs; j++)
frm.myList.options[j] = new Option(acts[i].list[j], VL[i].list[j]);
for (var j = numActs; j < oriLen; j++)
frm.myList.options[numActs] = null;
}
}

/////////////////////////

var myListTreeSelectfrm = document.myForm;
var myListTreeSelectoriLen = myListTreeSelectfrm.myListTreeSelect.length;
var nummyListTreeSelectActs;

for (var i = 0; i < actsmyListTreeSelect .length; i++){
if (str == actsmyListTreeSelect[i].name) {
nummyListTreeSelectActs = actsmyListTreeSelect[i].list.length;
for (var j = 0; j < nummyListTreeSelectActs; j++)
myListTreeSelectfrm.myListTreeSelect.options[j] = new Option(actsmyListTreeSelect[i].list[j], VLmyListTreeSelect[i].list[j]);
for (var j = nummyListTreeSelectActs; j < myListTreeSelectoriLen; j++)
myListTreeSelectfrm.myListTreeSelect.options[nummyListTreeSelectActs] = null;
}
}



}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////

</SCRIPT>

</HEAD>

<FORM NAME="myForm" method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?=$PHP_SELF?>'> 

<select name=RadOne onChange='updateList(this.value)'>
<option value='#'>:::::: 선택하세요 ::::::</option>
<?
include"../../db.php";
$result= mysql_query("select * from $GGTABLE where Ttable='$Ttable' and BigNo='0' order by no asc",$db);
$rows=mysql_num_rows($result);
if($rows){
$r=0;
while($row= mysql_fetch_array($result)) 
{ 
?>
<option value='<?=$row[no]?>' <?if($RadOne=="$row[no]"){echo("selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'");}?>><?=$row[title]?></option>
<?
	$r++;
}

}else{echo("<option>등록자료  없음</option>");}
mysql_close($db); 
?>
</select>

<select name=myList>
<option value='#'>:::::: 선택하세요 ::::::</option>
<?if($myList){echo("<option value='$myList' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>");?><? include"../../db.php";
$result=mysql_query("select * from $GGTABLE where no='$myList'",$db);
$row= mysql_fetch_array($result);
if($row){ echo("$row[title]"); } mysql_close($db); 
?><?echo("</option>");}?>
</select>

<select name=myListTreeSelect>
<option value='#'>:::::: 선택하세요 ::::::</option>
<?if($myListTreeSelect){echo("<option value='$myListTreeSelect' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>");?><? include"../../db.php";
$result=mysql_query("select * from $GGTABLE where no='$myListTreeSelect'",$db);
$row= mysql_fetch_array($result);
if($row){ echo("$row[title]"); } mysql_close($db); 
?><?echo("</option>");}?>
</select>
