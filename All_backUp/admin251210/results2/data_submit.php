<script language='javascript'>
self.moveTo(0,0)
self.resizeTo(availWidth=650,screen.availHeight)
</script>

<?php // 2006년3월18일 업데이트 (현프로그램은  기획없이 만들어져 테이블들이 꼬여잇다)
//그리하여 현글을 적는다 이전에는 Mlang_bbs_link에 가테고리값을 저장 햇지만 
// Mlang_bbs_secret 에 그값을 저장시킨다...


// 서울 화약의 요청으로 관리자 페이지을 업그레이드 한다.


if($mode=="modify_ok"){

include"../../db.php";

if($HH_code=="text"){ // -------------------------------------------------------------------------------------//
$query ="UPDATE Mlang_${id}_Results SET Mlang_bbs_member='$main', Mlang_bbs_link='$Mlang_bbs_link', Mlang_bbs_title='$title', Mlang_bbs_connent='$connent', Mlang_bbs_secret='$Mlang_bbs_cateTr', Mlang_bbs_reply='$Y8y_year' WHERE Mlang_bbs_no='$no'";
$result= mysqli_query($db, $query);
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

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
        window.self.close();
		</script>
	");
		exit;

}

mysqli_close($db);

}else{ // -------------------------------------------------------------------------------------//


// 파일 들을 수정 처리한다....////////////////////////////////////////
if($Sofileset=="yes"){
// 신규로 들어온 자료는 새로이 저장 시켜버린다.
include "../int/upload.inc";                 // 업로드 함수 include

$forbid_ext = array("php","asp","jsp","inc","c","cpp","sh");

// 그겟판의폴더를 생성시켜서 그걸 호출해버리장 즉 데이타베이스에는 저장할 필요가 읍다.
$result=func_multi_upload($upfile, $upfile_name, $upfile_size, $upfile_type, "../../results/upload/$id/$no/", $forbid_ext);
}else{
$result="빵";
}


if ($result) {


if($checkbox_bigfile){ //^^---------------------^^
 
// 원본 자료을 입력 처리한다....
if($MlangFriendSiteInfo=="file"){
$upload_dir="../../results/upload/$id";
include "../int/upload.php";  
}else{$BigUPFILENAME="$BigupfileLink";}

}else{$BigUPFILENAME="$bigfile_name";} //^^---------------------------------------------------------^^

if($checkbox_bigfileTwo){ //^^---------------------^^
// 원본 자료을 입력 처리한다....
if($MlangFriendSiteInfoTwo=="file"){
$upload_dir="../../results/upload/$id";
include "../int/uploadTwo.php";  
}else{$BigUPFILENAMETwo="$BigupfileTwoLink";}

}else{$BigUPFILENAMETwo="$bigfile_nameTwo";} //^^---------------------------------------------------------^^
	
// 따로 따러 두개의 값이 있당 이걸 잘 처리 해주어야 한다 수정시 충돌이 일어난다.
$query ="UPDATE Mlang_${id}_Results SET Mlang_bbs_member='$main', Mlang_bbs_link='$BigUPFILENAMETwo', Mlang_bbs_file='$BigUPFILENAME', Mlang_bbs_title='$title', Mlang_bbs_connent='$connent', Mlang_bbs_secret='$Mlang_bbs_cateTr', Mlang_bbs_reply='$Y8y_year' WHERE Mlang_bbs_no='$no'";


$result_date= mysqli_query($db, $query);
	if(!$result_date) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1);
			</script>";
		exit;

} else {}

mysqli_close($db);


// 체크한 자료는 삭제 처리 해버리고  사진 자료가 100개는 안넘겄지 ㅋㄷㅋㄷ
$i=0;
while( $i < 100) 
{ 
$temp = "checkbox_".$i; $get_temp=$$temp;
$Filetemp = "file_".$i; $Fileget_temp=$$Filetemp;
if($get_temp){ unlink("../../results/upload/$id/$no/$Fileget_temp"); }
$i=$i+1;
}

// bigfile_name 을 삭제처리한다...
if($MlangFriendSiteInfo=="file"){
if($checkbox_bigfile){ unlink("../../results/upload/$id/$bigfile_name"); }
}

//완료 메세지를 보인후 페이지를 이동 시킨다
echo ("
		<script language=javascript>
		alert('\\n$result 개의 파일을 새로 업로드 하고 자료를 정상적으로 수정 하였습니다.\\n\\n')
        opener.parent.location.reload();
		</script>
		<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=modify&id=$id&no=$no'>
	");
		exit;


} else {
     echo "
     <script language='javascript'>
     alert('정상적으로 파일이 업로드되지 않았습니다.\\n\\n재 실행하여 주시기 바랍니다..');
     history.go(-1)
     </script>";
exit;
}

// 파일 업로드 땡//////////////////////////////////////////////////////





} // -------------------------------------------------------------------------------------//

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="submit_ok"){

include"../../db.php";


if($HH_code=="text"){ // -------------------------------------------------------------------------------------//
	$result = mysqli_query($db, "SELECT max(Mlang_bbs_no) FROM Mlang_${id}_Results");
	if (!$result) {
		echo "
			<script>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysqli_fetch_row($result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   
############################################
//정보 입력
$date=date("Y-m-d H:i;s");
$dbinsert ="insert into Mlang_${id}_Results values('$new_no',
'$main',
'$title',
'',
'$connent',
'$Mlang_bbs_link',
'',
'',
'0',
'0',
'$Mlang_bbs_cateTr',
'$Y8y_year', 
'$date'
)";
$result_insert= mysqli_query($db, $dbinsert);

//완료 메세지를 보인후 페이지를 이동 시킨다
echo ("
		<script language=javascript>
		alert('\\n정상적으로 정보가 저장 되었습니다.\\n\\n')
        opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=submit&id=$id'>
		");
		exit;

}else{ // -------------------------------------------------------------------------------------//

// 테이타 베이스에 자료를 저장 한다...........................
$result = mysqli_query($db, "SELECT max(Mlang_bbs_no) FROM Mlang_${id}_Results");
	if (!$result) {
		echo "
			<script>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysqli_fetch_row($result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   

// 자료를 업로드할 폴더를 생성 시켜준다.. /////////////////////////////////////////////////////////////////////////////////
$dir = "../../results/upload/$id/$new_no"; 
$dir_handle = is_dir("$dir");
if(!$dir_handle){mkdir("$dir", 0755);  exec("chmod 777 $dir");}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($Sofileset1=="yes"){

include "../int/upload.inc";                 // 업로드 함수 include

$forbid_ext = array("php","asp","jsp","inc","c","cpp","sh");

// 그겟판의폴더를 생성시켜서 그걸 호출해버리장 즉 데이타베이스에는 저장할 필요가 읍다.
$result=func_multi_upload($upfile, $upfile_name, $upfile_size, $upfile_type, "$dir/", $forbid_ext);

if ($result) {


// 원본 자료을 입력 처리한다....
if($MlangFriendSiteInfo=="file"){
$upload_dir="../../results/upload/$id";
include "../int/upload.php";  
}else{$BigUPFILENAME="$BigupfileLink";}

// 원본 자료을 입력 처리한다....MlangFriendSiteInfoTwo
if($MlangFriendSiteInfoTwo=="file"){
$upload_dir="../../results/upload/$id";
include "../int/uploadTwo.php";  
}else{$BigUPFILENAMETwo="$BigupfileTwoLink";}

############################################
$date=date("Y-m-d H:i;s");
$dbinsert ="insert into Mlang_${id}_Results values('$new_no',
'$main',
'$title',
'',
'$connent',
'$BigUPFILENAMETwo',
'$BigUPFILENAME',
'',
'0',
'0',
'$Mlang_bbs_cateTr',
'$Y8y_year',
'$date'
)";
$result_insert= mysqli_query($db, $dbinsert);
############################################

//완료 메세지를 보인후 페이지를 이동 시킨다
echo ("
		<script language=javascript>
		alert('\\n$result 개의 파일을 업로드 하고 자료를 정상적으로 저장 하였습니다.\\n\\n')
        opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=submit&id=$id'>
		");
		exit;

} else {
     echo "
     <script language='javascript'>
     alert('정상적으로 파일이 업로드되지 않았습니다.\\n\\n재 실행하여 주시기 바랍니다..');
     history.go(-1)
     </script>";
exit;
}

}else{ 

// 원본 사진을 입력 처리한다....
$upload_dir="../../results/upload/$id";
include "../int/upload.php";  

if($MlangFriendSiteInfo=="file"){
$upload_dir="../../results/upload/$id";
include "../int/upload.php";  
}else{$BigUPFILENAME="$Bigupfile";}

// 원본 자료을 입력 처리한다....
if($MlangFriendSiteInfoTwo=="file"){
$upload_dir="../../results/upload/$id";
include "../int/uploadTwo.php";  
}else{$BigUPFILENAMETwo="$BigupfileTwoLink";}

############################################
$date=date("Y-m-d H:i;s");
$dbinsert ="insert into Mlang_${id}_Results values('$new_no',
'$main',
'$title',
'',
'$connent',
'$BigUPFILENAMETwo',
'$BigUPFILENAME',
'',
'0',
'0',
'$Mlang_bbs_cateTr',
'$Y8y_year',
'$date'
)";
$result_insert= mysqli_query($db, $dbinsert);
############################################

//완료 메세지를 보인후 페이지를 이동 시킨다
echo ("
		<script language=javascript>
		alert('\\n원본 파일을 업로드 하고 자료를 정상적으로 저장 하였습니다.\\n\\n')
        opener.parent.location.reload();
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=submit&id=$id'>
		");
		exit;

}

} // -------------------------------------------------------------------------------------//


}


//////////////////////////////////// 입력값 처리 끄--------읕 ////////////////////////////////////////////////////////////////////////////////////
?>


<?php include"data_admin_fild.php";

if($mode=="modify"){$DbDir="../.."; include"../../results/view_fild.php";}

include"../title.php";
?>

<script src="../js/coolbar.js" type="text/javascript"></script>

<head>
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

function MemberCheckField()
{
var f=document.FrmUserInfo;

<?php if ( $DataAdminFild_celect ) {echo("
if (f.Mlang_bbs_cateTr.value == \"0\") {
alert(\"항목을 선택 해주세요..\");
return false;
}

");}
?>


<?php 
if($DataAdminFild_item=="text"){
?>

if (f.title.value == "") {
alert("제목 을 입력하여주세요?");
return false;
}
if (f.connent.value == "") {
alert("내용 을 입력하여주세요?");
return false;
}

<?php }?>


}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

function MlangShowLayerOne(Code) {

      if(Code=="1"){
         document.all.MlangLayerOne_1.style.visibility = "visible";
		 document.all.MlangLayerOne_2.style.visibility = "hidden";
         }
	  if(Code=="2"){
         document.all.MlangLayerOne_2.style.visibility = "visible";
		 document.all.MlangLayerOne_1.style.visibility = "hidden";
         }
}

function MlangShowLayerTwo(Code) {

      if(Code=="1"){
         document.all.MlangLayerTwo_1.style.visibility = "visible";
		 document.all.MlangLayerTwo_2.style.visibility = "hidden";
         }
	  if(Code=="2"){
         document.all.MlangLayerTwo_2.style.visibility = "visible";
		 document.all.MlangLayerTwo_1.style.visibility = "hidden";
         }
}

 
function calc_re(){   // 항목전달
asd=document.forms["FrmUserInfo"];
asd.VateIIIO.value = asd.Mlang_bbs_cateTr.options[asd.Mlang_bbs_cateTr.selectedIndex].value;
} 

function VateIIIO_Submit(){   // 입력
asd=document.forms["FrmUserInfo"];

      if (asd.VateIIIO.value == "") {
      alert("새롭게 입력할 항목을 적어 해주세요..");
	  asd.VateIIIO.focus();
      return false;
        }
		 if(asd.VateIIIO.value.lastIndexOf(" ") > -1) {
      alert("새롭게 입력할 항목에 공백은 허용되지 않습니다.");
	  asd.VateIIIO.focus();
      return false;
        }
		if(asd.VateIIIO.value.lastIndexOf("\"") > -1) {
      alert("새롭게 입력할 항목에 쌍따옴표은 허용되지 않습니다.");
	  asd.VateIIIO.focus();
      return false;
        }
		if(asd.VateIIIO.value.lastIndexOf("'") > -1) {
      alert("새롭게 입력할 항목에 외따옴표은 허용되지 않습니다.");
	  asd.VateIIIO.focus();
      return false;
        }
		if(asd.VateIIIO.value.lastIndexOf(":") > -1) {
      alert("새롭게 입력할 항목에 : 은 허용되지 않습니다.");
	  asd.VateIIIO.focus();
      return false;
        }

var str;
		str='Mlang_bbs_cateTr.php?id=<?=$id?>&mode=Submit&F='+asd.VateIIIO.value;
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
}  /////////////////////////////////////////////////////////////////////////////

function VateIIIO_Delete(){   // 삭제
asd=document.forms["FrmUserInfo"];

      if (asd.VateIIIO.value == "") {
      alert("삭제할 항목을 선택 해주세요..");
      return false;
        }

var str;
		if (confirm("항목을 삭제하시면 항목관련 모든 자료가 동시에 지워집니다.\n\n삭제한 자료는 두번 다시 복구 되지 않습니다.\n\n삭제할 자료가 확실하시면 실행하십시요!!")) {
		str='Mlang_bbs_cateTr.php?id=<?=$id?>&mode=Delete&F='+asd.VateIIIO.value;
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}

} 

</script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>
<iframe name=Tcal frameborder=0 width=0 height=0></iframe>
<table border=0 align=center width=100% cellpadding='5' cellspacing='0' bgcolor='#999933'>

<form name='FrmUserInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return MemberCheckField()' action='<?=$PHP_SELF?>'>

<tr>
<td colspan=2>&nbsp;&nbsp;
<font color=white><b>
<?=$DataAdminFild_title?> -
<big>
<?php if($mode=="submit"){echo("자료 등록 하기");}
if($mode=="modify"){echo("자료 수정 하기");}
?>
</font></b></big>
</td>
</tr>

<?php if ( $DataAdminFild_celect ) {

echo("
<tr>
<td class='coolBar' height=30 width=20% align=right><b>항목</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%>
&nbsp;<select name='Mlang_bbs_cateTr' onChange='calc_re();'>
<OPTION VALUE='0' selected>▒선택 하데염▒</OPTION>
");

	$CATEGORY_LIST_script = split(":", $DataAdminFild_celect );
	$k = 0;
	while($k < sizeof($CATEGORY_LIST_script)) {

if($BbsViewMlang_bbs_secret=="$CATEGORY_LIST_script[$k]"){
		echo "<OPTION VALUE='$CATEGORY_LIST_script[$k]' selected style='background-color:#3399CC; color:#FFFFFF;'>$CATEGORY_LIST_script[$k]</OPTION>";	
}else{		echo "<OPTION VALUE='$CATEGORY_LIST_script[$k]'>$CATEGORY_LIST_script[$k]</OPTION>";}

		$k++;
	} 
	echo("</select>");

} 
?>

<INPUT TYPE="text" NAME="VateIIIO" name='40'>
<input type='button' onClick='VateIIIO_Submit();' value='입력'><input type='button' onClick='VateIIIO_Delete();' value='삭제'>
</td></tr>

<?php ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($DataAdminFild_item=="text"){
?>

<tr>
<td class='coolBar' height=30 width=20% align=right><b>제작사</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%>&nbsp;<Input type='text' name='title' size='50' <?if($mode=="modify"){echo("value='$BbsViewMlang_bbs_title'");}?>></td>
</tr>

<tr>
<td class='coolBar' height=30 width=20% align=right><b>제작물</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%>&nbsp;<Input type='text' name='connent' size='70' <?if($mode=="modify"){echo("value='$BbsViewMlang_bbs_connent'");}?>></td>
</tr>

<?php }else{?>

<?php echo("
<tr>
<td class='coolBar' height=60 width=20% align=right><b>리스트박스</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%>");

if($mode=="modify"){
	echo("<INPUT TYPE='checkbox' NAME='checkbox_bigfile'><INPUT TYPE='hidden' NAME='bigfile_name' value='$BbsViewMlang_bbs_file'> 원본: $BbsViewMlang_bbs_file &nbsp;<BR>&nbsp;&nbsp;&nbsp;<font style='color:#6600CC;'>(자료을변경하시려면체크후재입력/체크만 하고 자료 미입력하면 기존자료 삭제됩니다.)</font><BR>");	
	}
?>

<INPUT TYPE="radio" NAME="MlangFriendSiteInfo" value='file' onClick="javascript:MlangShowLayerOne('1');">파일 업로드
<INPUT TYPE="radio" NAME="MlangFriendSiteInfo" value='link' onClick="javascript:MlangShowLayerOne('2');">파일 링크
<BR>
<?$DivS_Width=""; $DivS_height="22";?>
<div id='MlangLayerOne_1' class='coolBar' style="position:absolute; width:<?=$DivS_Width?>; height:<?=$DivS_height?>; visibility:hidden;">
<input type='file' name='Bigupfile' size=60>
</div>
<div id='MlangLayerOne_2' class='coolBar' style="position:absolute; width:<?=$DivS_Width?>; height:<?=$DivS_height?>; visibility:hidden;">
<input type='text' name='BigupfileLink' size=75>
</div>

<font style='color:#828282; font-size:8pt; FONT-FAMILY:돋음; line-height:180%;'>
&nbsp;&nbsp;&nbsp;* 업로드나 파일링크 중 하나만 입력하셔야 정상적으로 호출됩니다..!!
</td>
</tr>

<?php echo("
<tr>
<td class='coolBar' height=60 width=20% align=right><b>창뛰웠을때</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%>");

if($mode=="modify"){
	echo("<INPUT TYPE='checkbox' NAME='checkbox_bigfileTwo'><INPUT TYPE='hidden' NAME='bigfile_nameTwo' value='$BbsViewMlang_bbs_link'> 원본: $BbsViewMlang_bbs_link &nbsp;<BR>&nbsp;&nbsp;&nbsp;<font style='color:#6600CC;'>(자료을변경하시려면체크후재입력/체크만 하고 자료 미입력하면 기존자료 삭제됩니다.)</font><BR>");	
	}
?>

<INPUT TYPE="radio" NAME="MlangFriendSiteInfoTwo" value='file' onClick="javascript:MlangShowLayerTwo('1');">파일 업로드
<INPUT TYPE="radio" NAME="MlangFriendSiteInfoTwo" value='link' onClick="javascript:MlangShowLayerTwo('2');">파일 링크
<BR>
<div id='MlangLayerTwo_1' class='coolBar' style="position:absolute; width:<?=$DivS_Width?>; height:<?=$DivS_height?>; visibility:hidden;">
<input type='file' name='BigupfileTwo' size=60>
</div>
<div id='MlangLayerTwo_2' class='coolBar' style="position:absolute; width:<?=$DivS_Width?>; height:<?=$DivS_height?>; visibility:hidden;">
<input type='text' name='BigupfileTwoLink' size=75>
</div>
<font style='color:#828282; font-size:8pt; FONT-FAMILY:돋음; line-height:180%;'>
&nbsp;&nbsp;&nbsp;* 업로드나 파일링크 중 하나만 입력하셔야 정상적으로 호출됩니다..!!
</td>
</tr>

<?php echo("
<tr>
<td class='coolBar' height=30 width=20% align=right><b>마우스되었을때</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%><input type='hidden' name='Sofileset1' value='yes'>
");

if($mode=="modify"){ // 수정할 사진 자료를 출력 한다............................................................

echo("<input type='hidden' name='Sofileset1' value='yes'>");

// 전체 파일 를 연다. //////////////////////////////////////////////////////////
$dir_path = "../../results/upload/$id/$no";
$dir_handle = opendir($dir_path);

// 전체 디렉토리 내용을 출력한다.
$WQ=0;
while($tmp = readdir($dir_handle))
{
if(($tmp != ".") && ($tmp != "..")) {
echo (is_file($dir_path.$tmp) ? "" : "
<table border=1 align=center width=90% cellpadding='5' cellspacing='0'><tr>
<td width=50%><INPUT TYPE='checkbox' NAME='checkbox_$WQ'><INPUT TYPE='hidden' NAME='file_$WQ' value='$tmp'>&nbsp;&nbsp;파일명: $tmp</td>
<td width=50%><img src='$dir_path/$tmp' width=100></td>
</tr></table>
");
}

	$WQ=$WQ+1;
}

closedir($dir_handle);

////////////////////////////////////////////////////////////////////////////////////

echo("<BR>");

} //..............................................................................................................................

?>


<script language="javascript">
function AddFile()
{
var objTbl = document.all["tblAttFiles"];
var objRow = objTbl.insertRow();
var objCell = objRow.insertCell();
objCell.innerHTML =
  "<img src=/img/12345.gif align=absbottom>\n" +
  "<input type=file onChange='CkImageVal()' name=upfile[] size=40>";
document.recalc();
}

function CkImageVal() {
var oInput = event.srcElement;
var fname = oInput.value;
if((/(.jpg|.jpeg|.gif|.png)$/i).test(fname))
  oInput.parentElement.children[0].src = fname;
else
  alert('이미지는 gif, jpg, png 파일만 가능합니다.');
}
</script>

<table id=tblAttFiles cellspacing=0 border=0>
<tr><td>
   <img src=/img/12345.gif align=absbottom>
   <input type=file name=upfile[] size=40  onChange="CkImageVal()">
</td></tr>
</table>
<input type=hidden value='사진이미지 입력 추가' onclick="AddFile()">

</td></tr>

<tr>
<td class='coolBar' height=30 width=20% align=right><b>제목</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%>&nbsp;<Input type='text' name='title' size='50' <?if($mode=="modify"){echo("value='$BbsViewMlang_bbs_title'");}?>></td>
</tr>

<tr>
<td class='coolBar' height=30 width=20% align=right><b>내용</b>&nbsp;&nbsp;</td>	
<td class='coolBar' width=80%>&nbsp;<TEXTAREA NAME="connent" ROWS="5" COLS="50"><?if($mode=="modify"){echo("$BbsViewMlang_bbs_connent");}?></TEXTAREA>
</td>
</tr>


<?php } ?>

</table>

<INPUT TYPE='hidden' name='HH_code' value='<?=$DataAdminFild_item?>'>
<?php if($mode=="submit"){echo("
<INPUT TYPE='hidden' name='mode' value='submit_ok'>
<INPUT TYPE='hidden' name='id' value='$id'>
");
}
if($mode=="modify"){echo("
<INPUT TYPE='hidden' name='mode' value='modify_ok'>
<INPUT TYPE='hidden' name='id' value='$id'>
<INPUT TYPE='hidden' name='no' value='$no'>
");
}
?>

<p align=center>
<input type='submit' value='<?if($mode=="submit"){echo("입력합니다..");}if($mode=="modify"){echo("수정합니다..");}?>'>
<input type='button' onClick='javascript:window.close();' value='창닫기-CLOSE'>
<BR><BR>
</p>

</body>
</html>