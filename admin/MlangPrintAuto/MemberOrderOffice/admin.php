<?php
////////////////// 관리자 로그인 ////////////////////
include "../../../db.php";
include "../../config.php";
////////////////////////////////////////////////////
$PHP_SELF = $_SERVER['PHP_SELF'];
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$code = isset($_GET['code']) ? $_GET['code'] : '';
if($mode=="MlangFileOk"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////

$MAXFSIZE="99999";
$upload_dir="./upload";
	if($check){
		if($photofile_4){ include "upload_4.php"; $fileOk="$photofile_4Name"; }else{ if($file){unlink("$upload_dir/$file");} }
	}else{
		$fileOk="$file";
	}

$query ="UPDATE MlangPrintAuto_MemberOrderOffice SET $code='$fileOk' WHERE no='$no'";
$result= mysqli_query($db,$query);
	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 에러입니다!\");
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		alert('\\n정보를 정상적으로 수정하였습니다.\\n');
		opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;

}
mysqli_close($db);


} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="MlangFile"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<html>
<title>자료첨부 수정</title>

<head>
<style>
td, table{BORDER-COLOR:#000000; border-collapse:collapse; color:#000000; font-size:10pt; FONT-FAMILY:돋움; word-break:break-all;}	</style>
<?php
echo "<option value='{$row['no']}'>{$row['title']}</option>"; ?>
<style>
input, TEXTAREA {
	color: #000000;
	font-size: 9pt;
	border: 1px solid #444444;
	vertical-align: middle;
}
TEXTAREA {
	overflow: hidden;
}
</style>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=400,availHeight=250);
</script>

</head>

<body>

 <table border=1 align=center cellpadding=5 cellspacing=0 width=340>
 <form method='post' enctype='multipart/form-data' action='<?php echo $PHP_SELF?>'>
 <input type='hidden' name='file' value='<?php echo $file?>'>
 <input type='hidden' name='no' value='<?php echo $no?>'>
 <input type='hidden' name='code' value='<?php echo $code?>'>
 <input type='hidden' name='mode' value='MlangFileOk'>
   <tr>
     <td colspan=2>* 파일 수정페이지</td>
   </tr>
   <tr>
     <td align=center>현재파일명</td>
	 <td><?php echo $file?></td>
   </tr>
   <tr>
     <td align=center>변경</td>
	 <td><INPUT TYPE="checkbox" NAME="check"> 파일을수정하려면 체크을 해주세요<BR>
	 <font style='font-family:돋움; font-size: 8pt; color:#336699;'>* 체크후 업로드을 안하시면 기존자료만 삭제됨.</font>
	 </td>
   </tr>
    <tr>
     <td align=center>업로드</td>
	 <td><input type='file' name='photofile_4' size='23'></td>
   </tr>
 </table>

 <p align=center>
		 <input type='submit' value=' 저장합니다.. '>
		 <input type='button' onclick="javascript:window.self.close();" value='창닫기'>
 </p>
 </form>

</body>
</html>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<?php
if($mode=="form"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////
$Bgcolor1="408080";

if($code=="modify" || $code=="Print" || $code=="fff"){include"View.php";

 function str_cutting($str, $len){ 
       preg_match('/([\x00-\x7e]|..)*/', substr($str, 0, $len), $rtn); 
       if ( $len < strlen($str) ) $rtn[0].=".."; 
        return $rtn[0]; 
    } 
}
?>

<html>
<title>"웹실디자인"</title>

<head>

<style>
td, table {
  border-color: #707070;
  border-collapse: collapse;
  color: #000000;
  font-size: 9pt;
  font-family: 굴림;
  line-height: 130%;
  word-break: break-word; /* better support */
}

input:not([type="checkbox"]),
textarea {
  color: #000000;
  font-size: 9pt;
  border: 1px solid #444444;
  vertical-align: middle;
}

textarea {
  overflow: hidden;
}
</style>

<SCRIPT LANGUAGE=JAVASCRIPT src='/admin/js/exchange.js'></SCRIPT>

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

function MemberXCheckField()
{
var f=document.FrmUserXInfo;

if (f.One_1.value == "") {
alert("작성자을 입력하여주세요!!");
f.One_1.focus();
return false;
}

if (f.One_3.value == "") {
alert("업체명을 입력하여주세요!!");
f.One_3.focus();
return false;
}

if (f.One_4.value == "") {
alert("담당자을 입력하여주세요!!");
f.One_4.focus();
return false;
}

if (f.One_6.value == "") {
alert("연락처을 입력하여주세요!!");
f.One_6.focus();
return false;
}

if (f.One_7.value == "") {
alert("핸드폰을 입력하여주세요!!");
f.One_7.focus();
return false;
}

if (f.One_9.value == "") {
alert("택배지을 입력하여주세요!!");
f.One_9.focus();
return false;
}

}
</script>

</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<?php if ($mode=="form"){?>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?php echo $PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='<?php if ($code=="modify") { echo "modify_ok"; } else { echo "form_ok"; } ?>'>
<?php if ($code=="modify"){?><INPUT TYPE="hidden" name='no' value='<?php echo $no?>'><?php } ?>
<?php } ?>

<?php
if($mode=="modify_ok"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////

$query ="UPDATE MlangPrintAuto_MemberOrderOffice SET 
One_1='$One_1',
One_2='$One_2',
One_3='$One_3',
One_4='$One_4',
One_5='$One_5',
One_6='$One_6',
One_7='$One_7',
One_8='$One_8',
One_9='$One_9',
Two_1='$Two_1',
Two_2='$Two_2',
Two_3='$Two_3',
Two_4='$Two_4',
Two_5='$Two_5',
Two_6='$Two_6',
Two_7_1='$Two_7_1',
Two_7_2='$Two_7_2',
Two_7_3='$Two_7_3',
Two_9='$Two_9',
Two_10='$Two_10',
Two_11='$Two_11',
Two_12='$Two_12',
Two_13='$Two_13',
Two_14='$Two_14',
Two_15='$Two_15',
Two_16='$Two_16',
Two_17='$Two_17',
Two_18='$Two_18',
Two_19='$Two_19',
Two_20='$Two_20',
Two_21_1='$Two_21_1',
Two_21_2='$Two_21_2',
Two_21_3='$Two_21_3',
Two_21_4='$Two_21_4',
Two_22='$Two_22',
Two_23='$Two_23',
Two_24='$Two_24',
Two_25='$Two_25',
Two_26='$Two_26',
Two_27='$Two_27',
Two_28='$Two_28',
Two_29='$Two_29',
Two_30='$Two_30',
Two_31='$Two_31',
Two_32='$Two_32',
Two_33_1='$Two_33_1',
Two_33_2='$Two_33_2',
Two_33_3='$Two_33_3',
Two_34='$Two_34',
Two_35='$Two_35',
Two_36='$Two_36',
Two_37='$Two_37',
Two_38='$Two_38',
Two_39='$Two_39',
Two_40='$Two_40',
Two_41='$Two_41',
Two_42='$Two_42',
Two_43='$Two_43',
Two_44='$Two_44',
Two_45='$Two_45',
Two_46='$Two_46',
Two_47='$Two_47',
Two_48='$Two_48',
Two_49='$Two_49',
Two_50='$Two_50',
Two_51='$Two_51',
Two_52='$Two_52',
Two_53='$Two_53',
Two_54='$Two_54',
Two_55='$Two_55',
Four_1='$Four_1',
Four_2='$Four_2',
Four_3='$Four_3',
Four_4='$Four_4',
Four_5='$Four_5',
Four_6='$Four_6',
Four_7='$Four_7',
Four_8='$Four_8',
Four_9='$Four_9',
Five_1='$Five_1',
Five_2='$Five_2',
Five_3='$Five_3',
Five_4='$Five_4',
Five_5='$Five_5',
Five_6='$Five_6',
Five_7='$Five_7',
Five_8='$Five_8',
Five_9='$Five_9',
Five_10='$Five_10',
Five_11='$Five_11',
Five_12='$Five_12',
Five_13='$Five_13',
Five_14='$Five_14',
Five_15='$Five_15',
Five_16='$Five_16',
Five_17='$Five_17',
Five_18='$Five_18',
Five_19='$Five_19',
Five_20='$Five_20',
Five_21='$Five_21',
Five_22='$Five_22',
Five_23='$Five_23',
Five_24='$Five_24',
Five_25='$Five_25',
Five_26='$Five_26',
Five_27='$Five_27',
Five_28='$Five_28',
Five_29='$Five_29'
WHERE no='$no'";
$result= mysqli_query($db,$query);


	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 에러입니다!\");
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		alert('\\n정보를 정상적으로 수정하였습니다.\\n');
		opener.parent.location.reload();
		</script>
		<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&code=modify&no=$no'>
	");
		exit;

}
mysqli_close($db);


}
?>