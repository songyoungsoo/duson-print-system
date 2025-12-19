<?php
declare(strict_types=1);


// ⚠️  XSS 보호 권장: echo 시 htmlspecialchars() 사용을 고려하세요
// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';
$TreeSelect = $_GET['TreeSelect'] ?? $_POST['TreeSelect'] ?? '';
$ACate = $_GET['ACate'] ?? $_POST['ACate'] ?? '';
$ATreeNo = $_GET['ATreeNo'] ?? $_POST['ATreeNo'] ?? '';
$title = $_POST['title'] ?? '';
$BigNo = $_POST['BigNo'] ?? '';
$Ttable = $_GET['Ttable'] ?? $_POST['Ttable'] ?? '';
$PHP_SELF = $_SERVER['PHP_SELF'] ?? '';

////////////////// 관리자 로그인 ////////////////////
include"../../db.php";
include"../config.php";
include"../../mlangprintauto/ConDb.php";
include"CateAdmin_title.php";	
////////////////////////////////////////////////////
?>

<?php if($mode=="form"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////

include"../title.php";

$Bgcolor1="408080";

if($code=="modify"){include"cateview.php";}
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script language=javascript>
window.moveTo(screen.width/5, screen.height/5); 

function MemberXCheckField(){
var f=document.FrmUserXInfo;

if (f.title.value == "") {
alert("TITLE 을 입력하여주세요!!");
f.title.focus();
return false;
}

}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding=0 cellspacing=5>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?=$PHP_SELF?>'>
<INPUT TYPE="hidden" name='Ttable' value='<?=$Ttable?>'>
<INPUT TYPE="hidden" name='TreeSelect' value='<?=$TreeSelect?>'>
<?php if($ACate){?><INPUT TYPE="hidden" name='ACate' value='<?=$ACate?>'><?php }?>
<?php if($ATreeNo){?><INPUT TYPE="hidden" name='ATreeNo' value='<?=$ATreeNo?>'><?php }?>

<INPUT TYPE="hidden" name='mode' value='<?php if($code=="modify"){?>modify_ok<?php }else{?>form_ok<?php }?>'>
<?php if($code=="modify"){?><INPUT TYPE="hidden" name='no' value='<?=$no?>'><?php }?>

<tr>
<td class='coolBar' colspan=4 height=25>
<b>&nbsp;&nbsp;(<b><?=$View_TtableC?></b>)
<?php if(!$TreeSelect){echo("$DF_Tatle_1");}
if($TreeSelect=="1"){echo("$DF_Tatle_2");}
if($TreeSelect=="2"){echo("$DF_Tatle_3");}
?>
<?if($code=="modify"){?>수정<?}else{?>입력<?}?></b><BR>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>상위메뉴&nbsp;&nbsp;</td>
<td colspan=3>
<SELECT NAME="BigNo">

<?if(!$TreeSelect){?>
<option value='0'>◆ 최상의 TITLE로 등록 ◆</option>
<?}else{?>
<?php $Cate_result= mysqli_query($db, "select * from $GGTABLE where Ttable='$Ttable' and BigNo='0'");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$Cate_rows=mysqli_num_rows($Cate_result);
if($Cate_rows){

while($Cate_row= mysqli_fetch_array($Cate_result)) 
{
?>

<option value='<?=$Cate_row['no']?>' <?if($code=="modify"){
	 if($ACate==$Cate_row['no']){echo("style='background-color:green; color:#FFFFFF;' selected");}
     if($ATreeNo==$Cate_row['no']){echo("style='background-color:blue; color:#FFFFFF;' selected");}
 }?>><?=$Cate_row['title']?></option>

<?php }

}else{}

mysqli_close($db); 
?>
<?}?>

</SELECT>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>TITLE&nbsp;&nbsp;</td>
<td colspan=3><INPUT TYPE="text" NAME="title" size=50 maxLength='80' value='<?php if($code=="modify"){echo(htmlspecialchars($View_title));}?>'></td>
</tr>

<tr>
<td colspan=4 align=center>
<input type='submit' value=' <?php if($code=="modify"){?>수정<?php }else{?>저장<?php }?> 합니다.'>
</td>
</tr>

</table>

<?php } //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="delete"){

$result= mysqli_query($db, "select * from $GGTABLE where no='$no'");
$row= mysqli_fetch_array($result);

if($row['BigNo']=="0"){

mysqli_query($db, "DELETE FROM $GGTABLE WHERE BigNo='$no'");
mysqli_query($db, "DELETE FROM $GGTABLE WHERE no='$no'");
mysqli_close($db);

}else{
mysqli_query($db, "DELETE FROM $GGTABLE WHERE no='$no'");
mysqli_close($db);
}



echo ("
<html>
<script language=javascript>
window.alert('$no번 자료을 삭제 처리 하였습니다.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="form_ok"){


if($TreeSelect=="1"){
$dbinsert ="insert into $GGTABLE values('',
'$Ttable',
'$BigNo',
'$title',
''
)";
}

else if($TreeSelect=="2"){
$dbinsert ="insert into $GGTABLE values('',
'$Ttable',
'',
'$title',
'$BigNo'
)";
                }else{
$dbinsert ="insert into $GGTABLE values('',
'$Ttable',
'$BigNo',
'$title',
''
)";
                }

$result_insert= mysqli_query($db, $dbinsert);

	echo ("
		<script language=javascript>
		alert('\\nCATEGORY[$View_TtableC] 자료를 정상적으로 저장 하였습니다.\\n');
        opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&Ttable=$Ttable&TreeSelect=$TreeSelect&ACate=$ACate&ATreeNo=$ATreeNo'>
	");
		exit;


} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>


<?php if($mode=="modify_ok"){

             if($TreeSelect=="2"){
$query ="UPDATE $GGTABLE SET  
title='$title',
TreeNo='$BigNo'
WHERE no='$no'";
                }else{

$query ="UPDATE $GGTABLE SET 
BigNo='$BigNo',  
title='$title'
WHERE no='$no'";
                }

$result= mysqli_query($db, $query);

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
		alert('\\n정보를 정상적으로 수정하였습니다.\\n');
		opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&code=modify&no=$no&Ttable=$Ttable&TreeSelect=$TreeSelect&ACate=$ACate&ATreeNo=$ATreeNo'>
	");
		exit;

}
mysqli_close($db);


}
?>