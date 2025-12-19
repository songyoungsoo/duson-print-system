<?
if($mode=="delete"){

include"../../db.php";
include"../config.php";

$result = mysql_query("DELETE FROM $page_big_table WHERE no='$no'");
mysql_close();

echo ("<script language=javascript>
window.alert('자료를 정상적으로 [삭제] 하였습니다.!!');
opener.parent.location=\"./page_menu_list.php\"; 
window.self.close();
</script>
");
exit;

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="modify"){

include"../../db.php";
include"../config.php";
include"../title.php";

$result= mysql_query("select * from $page_big_table where no='$no'",$db);
$rows=mysql_num_rows($result);
if($rows){

while($row= mysql_fetch_array($result)) 
{ 
$TT_no="$row[no]";
$TT_title="$row[title]";
}

}else{
	echo ("
		<script language=javascript>
		alert('이미삭제된 자료이거나 등록된 자료가 없습니다.');
        opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;
}

mysql_close($db); 
?>

<script language=javascript>

var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;

////////////////////////////////////////////////////////////////////////////////
function TypeCheck (s, spc) {
var i;

for(i=0; i< s.length; i++) {
if (spc.indexOf(s.substring(i, i+1)) < 0) {
return false;
}
}        
return true;
}

/////////////////////////////////////////////////////////////////////////////////

function AdminCheckField()
{
var f=document.AdminInfo;

if (f.menu.value == "") {
alert("메뉴에 대한 내용을 입력하여주세요");
return false;
}

}
</script>
</head>

<table border=0 align=center width=600 cellpadding='0' cellspacing='0'>
<tr><td align=center>
<BR><BR>
* {공백허용, 30자 이내} 로 입력 해주세요
<BR><BR>
	
<table border=0 align=center width=420 cellpadding='10' cellspacing='1' bgcolor='#000000'>
<form name='AdminInfo' method='post' OnSubmit='javascript:return AdminCheckField()' action='<?echo("$PHP_SELF");?>'>
<input type='hidden' name='mode' value='modify_ok'>
</tr>
<tr>
<td bgcolor='#393839' width=100 align=center>
<font color='#FFFFFF'>수정할 내용입력:</font>
</td>
<td bgcolor='#FFFFFF'>
<input type='hidden' name='no' value='<?echo("$TT_no");?>'>
<input type='text' size='40' name='menu' maxLength='30' value='<?echo("$TT_title");?>'>
</td>
</tr>
</table>

<p align=center>
<input type='submit' value=' 수정 합니다.'>
</p>
</form>
<BR><BR>
</td></tr>

</table>


<?
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="modify_ok"){

include"../../db.php";
include"../config.php";

$query ="UPDATE $page_big_table SET title='$menu' WHERE no='$no'";
$result= mysql_query($query,$db);
	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		alert('\\n정보를 정상적으로 [수정]하였습니다.\\n');
        opener.parent.location=\"./page_menu_list.php\"; 
        window.self.close();
		</script>
	");
		exit;

}

mysql_close($db);

}

?>