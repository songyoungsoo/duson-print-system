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

include"../../db.php";
include"../config.php";

$T_DirUrl="../../mlangprintauto";
include"$T_DirUrl/ConDb.php";

$T_DirFole="./int/info.php";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="ModifyOk"){ ////////////////////////////////////////////////////////////////////////////////////////////////////

$query ="UPDATE mlangorder_printauto SET Type_1='$TypeOne', name='$name', email='$email', zip='$zip', zip1='$zip1', zip2='$zip2', phone='$phone', Hendphone='$Hendphone', delivery='$delivery', bizname='$bizname', bank='$bank', bankname='$bankname', cont='$cont', Gensu='$Gensu' WHERE no='$no'";
$result= mysqli_query($db, $query);
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

	if(!$result) {
		echo "
			<script language=javascript>
			<meta charset='euc-kr'>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		<meta charset='euc-kr'>
		alert('\\n정보를 정상적으로 수정하였습니다.\\n');
		opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$no'>
<meta charset='euc-kr'>
	");
		exit;

}

mysqli_close($db);

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="SubmitOk"){ ////////////////////////////////////////////////////////////////////////////////////////////////////

$Table_result = mysqli_query($db, "SELECT max(no) FROM mlangorder_printauto");
	if (!$Table_result) {
		echo "
			<script>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysqli_fetch_row($Table_result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   

// 자료를 업로드할 폴더를 생성 시켜준다.. /////////////////////////////////////////////////////////////////////////////////
$dir = "../../mlangorder_printauto/upload/$new_no"; 
$dir_handle = is_dir("$dir");
if(!$dir_handle){mkdir("$dir", 0755);  exec("chmod 777 $dir");}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


$date=date("Y-m-d H:i;s");
$dbinsert ="insert into mlangorder_printauto values('$new_no',
'$Type', 
'$ImgFolder', 
'$TypeOne',
'$money_1',
'$money_2',	
'$money_3',	
'$money_4',	
'$money_5',	
'$name',   
'$email',
'$zip', 
'$zip1',
'$zip2',
'$phone',   
'$Hendphone',
'$delivery', 
'$bizname',
'$bank',
'$bankname',
'$cont', 
'$date',
'3',
'',
'$phone',
'$Gensu'
)";
$result_insert= mysqli_query($db, $dbinsert);

	echo ("
		<script language=javascript>
		alert('\\n정보를 정상적으로 [저장] 하였습니다.\\n');
		opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$new_no'>
<meta charset='euc-kr'>
	");
		exit;

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="BankForm"){ ////////////////////////////////////////////////////////////////////////////////////////////////////

include"../title.php";
include"$T_DirFole";
$Bgcolor1="408080";
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script>
self.moveTo(0,0);
self.resizeTo(availWidth=680,availHeight=500);
</script>
<script language=javascript>
self.moveTo(0,0)
self.resizeTo(availWidth=600,screen.availHeight)

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

function MemberXCheckField()
{
var f=document.myForm;

if (f.BankName.value == "") {
alert("은행명을 입력하여주세요!!");
f.BankName.focus();
return false;
}

if (f.TName.value == "") {
alert("예금주을 입력하여주세요!!");
f.TName.focus();
return false;
}

if (f.BankNo.value == "") {
alert("계좌번호 입력하여주세요!!");
f.BankNo.focus();
return false;
}

}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding=5 cellspacing=5>

<form name='myForm' method='post' <?if($code=="Text"){}else{?>OnSubmit='javascript:return MemberXCheckField()'<?}?> action='<?=$PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='BankModifyOk'>

<tr>
<td colspan=2 bgcolor='#484848'>
?>
