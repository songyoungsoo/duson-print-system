<?php
declare(strict_types=1);


// ⚠️  XSS 보호 권장: echo 시 htmlspecialchars() 사용을 고려하세요
// ✅ PHP 7.4 호환: 입력 변수 초기화
$PHP_SELF = $_SERVER['PHP_SELF'];
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';
$Ttable = $_GET['Ttable'] ?? $_POST['Ttable'] ?? '';

// Form field variables
$RadOne = $_POST['RadOne'] ?? '';
$myList = $_POST['myList'] ?? '';
$myListTreeSelect = $_POST['myListTreeSelect'] ?? '';
$quantity = $_POST['quantity'] ?? '';
$quantityTwo = $_POST['quantityTwo'] ?? '';
$money = $_POST['money'] ?? '';
$TDesignMoney = $_POST['TDesignMoney'] ?? '';
$POtype = $_POST['POtype'] ?? '';

include"../../db.php";
include"../config.php";


$T_DirUrl="../../mlangprintauto";
$T_TABLE="merchandisebond";

// condb.php에서 사용할 Ttable을 소문자로 설정
if(!$Ttable) $Ttable = "merchandisebond";

include"./condb.php";
$GGTABLE = "mlangprintauto_transactioncate"; // 명시적으로 설정
$T_DirFole="$T_DirUrl/$T_TABLE/inc.php";
$TABLE="mlangprintauto_merchandisebond";

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="form"){

include"../title.php";
include"$T_DirFole";
$Bgcolor1="408080";

if($code=="Modify"){include"./merchandisebond_nofild.php";}
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script language=javascript>
var NUM = "0123456789."; 
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

if (f.RadOne.value == "#" || f.RadOne.value == "==================") {
alert("<?=$View_TtableC?> [종류] 을 선택하여주세요!!");
f.RadOne.focus();
return false;
}

if (f.quantity.value == "") {
alert("수량을 입력하여주세요!!");
f.quantity.focus();
return false;
}
if (!TypeCheck(f.quantity.value, NUM)) {
alert("수량은 숫자로만 입력해 주셔야 합니다.");
f.quantity.focus();
return false;
}

if (f.myList.value == "#" || f.myList.value == "==================") {
alert("<?=$View_TtableC?>[후가공] 을 선택하여주세요!!");
f.myList.focus();
return false;
}

if (f.money.value == "") {
alert("가격을 입력하여주세요!!");
f.money.focus();
return false;
}
if (!TypeCheck(f.money.value, NUM)) {
alert("가격은 숫자로만 입력해 주셔야 합니다.");
f.money.focus();
return false;
}

if (f.TDesignMoney.value == "") {
alert("디자인비 을 입력하여주세요!!");
f.TDesignMoney.focus();
return false;
}
if (!TypeCheck(f.TDesignMoney.value, NUM)) {
alert("디자인비 은 숫자로만 입력해 주셔야 합니다.");
f.TDesignMoney.focus();
return false;
}

return true;
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<?if($code=="Modify"){?>
<b>&nbsp;&nbsp;▒ <?=$View_TtableC?> 자료 수정 ▒▒▒▒▒</b><BR>
<?}else{?>
<b>&nbsp;&nbsp;▒ <?=$View_TtableC?> 신 자료 입력 ▒▒▒▒▒</b><BR>
<?}?>

<table border=0 align=center width=100% cellpadding=0 cellspacing=5>

<?include"merchandisebond_script.php";?>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>수량&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="quantity" size=20 maxLength='20' <?if($code=="Modify"){echo("value='$MlangPrintAutoFildView_quantity'");}?>></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>인쇄면&nbsp;&nbsp;</td>
<td>
<select name="POtype">
<option value='1' <?if($MlangPrintAutoFildView_POtype=="1"){echo("selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'");}?>>단면</option>
<option value='2' <?if($MlangPrintAutoFildView_POtype=="2"){echo("selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'");}?>>양면</option>
</select>
</td>
</tr>		

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>후가공&nbsp;&nbsp;</td>
<td>
<select name=myList>
<option value='#'>:::::: 선택하세요 ::::::</option>
<?php if($code=="Modify"){if($MlangPrintAutoFildView_Section){echo("<option value='$MlangPrintAutoFildView_Section' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>");
$result=mysqli_query($db, "select * from $GGTABLE where no='$MlangPrintAutoFildView_Section'");
if($result) {
    $row= mysqli_fetch_array($result);
    if($row){ echo($row['title']); }
} else {
    echo "쿼리 에러: " . mysqli_error($db);
}
echo("</option>");}}?>
</select>
</SELECT>
</td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>가격&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="money" size=20 maxLength='20' <?if($code=="Modify"){echo("value='$MlangPrintAutoFildView_money'");}?>></td>
</tr>

<tr>
<td bgcolor='#<?=$Bgcolor1?>' width=100 class='Left1' align=right>디자인비&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="TDesignMoney" size=20 maxLength='20' <?if($code=="Modify"){echo("value='$MlangPrintAutoFildView_DesignMoney'");}else{echo("value='$DesignMoney'");}?>></td>
</tr>

<tr>
<td>&nbsp;&nbsp;</td>
<td>
<?if($code=="Modify"){?>
<input type='submit' value=' 수정 합니다.'>
<?}else{?>
<input type='submit' value=' 저장 합니다.'>
<?}?>
</td>
</tr>
</FORM>
</table>

<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="form_ok"){

$dbinsert ="insert into $TABLE values('',
'$RadOne',
'$myList',
'$quantity',
'$money',
'$TDesignMoney',
'$POtype'
)";
$result_insert= mysqli_query($db, $dbinsert);
if(!$result_insert) {
    echo "
        <script language=javascript>
            window.alert(\"DB 저장 에러입니다!\\n" . mysqli_error($db) . "\")
            history.go(-1);
        </script>";
    exit;
}

	echo ("
		<script language=javascript>
		alert('\\n자료를 정상적으로 저장 하였습니다.\\n');
		opener.parent.location.reload();
		</script>
	<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&Ttable=$Ttable'>
	");
		exit;


} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="Modify_ok"){

$query ="UPDATE $TABLE SET style='$RadOne', Section='$myList', quantity='$quantity', money='$money', DesignMoney='$TDesignMoney', POtype='$POtype' WHERE no='$no'";
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
		if(opener && opener.parent) {
			opener.parent.location.reload();
			window.self.close();
		} else {
			window.location.href = '$PHP_SELF?mode=form&code=Modify&no=$no&Ttable=$Ttable';
		}
		</script>
	");
		exit;

}
mysqli_close($db);


} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="delete"){

$result = mysqli_query($db, "DELETE FROM $TABLE WHERE no='$no'");
mysqli_close($db);

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


} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>


<?php if($mode=="IncForm"){ // inc 파일을 수정하는폼
include"$T_DirFole";

include"../title.php";
?>

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

function AdminPassKleCheckField()
{
var f=document.AdminPassKleInfo;

if (f.moeny.value == "") {
alert("디자인 가격을 입력하여주세요?");
f.moeny.focus();
return false;
}
if (!TypeCheck(f.moeny.value, NUM)) {
alert("디자인 가격은 숫자로만 입력해 주셔야 합니다.");
f.moeny.focus();
return false;
}

}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<BR>
<p align=center>
<form name='AdminPassKleInfo' method='post' OnSubmit='javascript:return AdminPassKleCheckField()' action='<?=$PHP_SELF?>' enctype='multipart/form-data'>
<INPUT TYPE="hidden" name='mode' value='IncFormOk'>

<table border=1 width=100% align=center cellpadding='5' cellspacing='0'>

<tr><td bgcolor='#6699CC' class='td11' colspan=2>
아래의 가격을 숫자로 변경 가능합니다.
</td></tr>
<tr>
<td align=center>디자인 가격</td>
<td><input type='text' name='moeny' maxLength='10' size='15' value='<?=$DesignMoney?>'> 원</td>
</tr>

<tr><td bgcolor='#6699CC' class='td11' colspan=2>
<font style='color:#FFFFFF; line-height:130%;'>
아래의 내용은 마우스를 대면 나오는 설명글 입니다, 사진/내용 을 입력하지 않으면 자동으로 호출되지 않습니다,
<BR>
기존 사진자료가 있을경우 자료을 지우려면 사진 미입력후 체크버튼에 체크만 하시면 자료가 지워집니다.
<BR>
<font color=red>*</font>
문구 입력시 HTML을 인식, 엔터을 치면 자동 br 로 처리, # 입력시 공백하나 ##(두개)입력시 공백2칸식으로 처리됨
</font>
</td></tr>
<tr>
<td align=center>종류</td>
<td>
     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center><TEXTAREA NAME="Section1" ROWS="4" COLS="50"><?=$SectionOne?></TEXTAREA></td>
		 <td align=center>
		       <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                  <tr>
                     <td align=center>
					 <input type='file' name='File1' size='20'>
					 <?if($ImgOne){?><BR>
                       <INPUT TYPE="checkbox" NAME="ImeOneChick">이미지을 변경하시려면 체크를 해주세요
					   <INPUT TYPE="hidden" name='File1_Y' value='<?=$ImgOne?>'>
                     <?}?>
					 </td>
                     <?if($ImgOne){?>
					 <td align=center>
                       <img src='<?=$upload_dir?>/<?=$ImgOne?>' width=80 height=95 border=0>
					 </td>
                     <?}?>
                 </tr>
               </table>
		 </td>
       </tr>
     </table>
</td>
</tr>

<tr>
<td align=center>수량</td>
<td>
     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center><TEXTAREA NAME="Section2" ROWS="4" COLS="50"><?=$SectionTwo?></TEXTAREA></td>
		 <td align=center>
		       <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                  <tr>
                     <td align=center>
					 <input type='file' name='File2' size='20'>
					 <?if($ImgTwo){?><BR>
                       <INPUT TYPE="checkbox" NAME="ImeTwoChick">이미지을 변경하시려면 체크를 해주세요
					   <INPUT TYPE="hidden" name='File2_Y' value='<?=$ImgTwo?>'>
                     <?}?>
					 </td>
                     <?if($ImgTwo){?>
					 <td align=center>
                       <img src='<?=$upload_dir?>/<?=$ImgTwo?>' width=80 height=95 border=0>
					 </td>
                     <?}?>
                 </tr>
               </table>
		 </td>
       </tr>
     </table>
</td>
</tr>

<tr>
<td align=center>인쇄면</td>
<td>
     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center><TEXTAREA NAME="Section3" ROWS="4" COLS="50"><?=$SectionTree?></TEXTAREA></td>
		 <td align=center>
		       <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                  <tr>
                     <td align=center>
					 <input type='file' name='File3' size='20'>
					 <?if($ImgTree){?><BR>
                       <INPUT TYPE="checkbox" NAME="ImeTreeChick">이미지을 변경하시려면 체크를 해주세요
					   <INPUT TYPE="hidden" name='File3_Y' value='<?=$ImgTree?>'>
                     <?}?>
					 </td>
                     <?if($ImgTree){?>
					 <td align=center>
                       <img src='<?=$upload_dir?>/<?=$ImgTree?>' width=80 height=95 border=0>
					 </td>
                     <?}?>
                 </tr>
               </table>
		 </td>
       </tr>
     </table>
</td>
</tr>

<tr>
<td align=center>후가공</td>
<td>
     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center><TEXTAREA NAME="Section4" ROWS="4" COLS="50"><?=$SectionFour?></TEXTAREA></td>
		 <td align=center>
		       <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                  <tr>
                     <td align=center>
					 <input type='file' name='File4' size='20'>
					 <?if($ImgFour){?><BR>
                       <INPUT TYPE="checkbox" NAME="ImeFourChick">이미지을 변경하시려면 체크를 해주세요
					   <INPUT TYPE="hidden" name='File4_Y' value='<?=$ImgFour?>'>
                     <?}?>
					 </td>
                     <?if($ImgFour){?>
					 <td align=center>
                       <img src='<?=$upload_dir?>/<?=$ImgFour?>' width=80 height=95 border=0>
					 </td>
                     <?}?>
                 </tr>
               </table>
		 </td>
       </tr>
     </table>
</td>
</tr>

<tr>
<td align=center>디자인편집</td>
<td>
     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center><TEXTAREA NAME="Section5" ROWS="4" COLS="50"><?=$SectionFive?></TEXTAREA></td>
		 <td align=center>
		       <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                  <tr>
                     <td align=center>
					 <input type='file' name='File5' size='20'>
					 <?if($ImgFive){?><BR>
                       <INPUT TYPE="checkbox" NAME="ImeFiveChick">이미지을 변경하시려면 체크를 해주세요
					   <INPUT TYPE="hidden" name='File5_Y' value='<?=$ImgFive?>'>
                     <?}?>
					 </td>
                     <?if($ImgFive){?>
					 <td align=center>
                       <img src='<?=$upload_dir?>/<?=$ImgFive?>' width=80 height=95 border=0>
					 </td>
                     <?}?>
                 </tr>
               </table>
		 </td>
       </tr>
     </table>
</td>
</tr>

</table>

<BR>
<input type='submit' value='수정합니다'>
<input type='button' value='창 닫기' onClick='javascript:window.self.close();'>
</p>
</form>

<?php exit;
} //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="IncFormOk"){  // inc 파일 결과 처리 

if($ImeOneChick=="on"){
           if($File1){ if($File1_Y){ unlink("$upload_dir/$File1_Y"); }    include"$T_DirUrl/Upload_1.php"; 
		   }else{  
			   if($File1_Y){ unlink("$upload_dir/$File1_Y"); }
			   }
}else{ if($File1_Y){$File1NAME="$File1_Y";}else{ if($File1){include"$T_DirUrl/Upload_1.php";}} }

if($ImeTwoChick=="on"){
           if($File2){ if($File2_Y){ unlink("$upload_dir/$File2_Y"); }    include"$T_DirUrl/Upload_2.php"; 
		   }else{  
			   if($File2_Y){ unlink("$upload_dir/$File2_Y"); }
			   }
}else{ if($File2_Y){$File2NAME="$File2_Y";}else{ if($File2){include"$T_DirUrl/Upload_2.php";}}  }

if($ImeTreeChick=="on"){
           if($File3){ if($File3_Y){ unlink("$upload_dir/$File3_Y"); }    include"$T_DirUrl/Upload_3.php"; 
		   }else{  
			   if($File3_Y){ unlink("$upload_dir/$File3_Y"); }
			   }
}else{ if($File3_Y){$File3NAME="$File3_Y";}else{ if($File3){include"$T_DirUrl/Upload_3.php";}}  }

if($ImeFourChick=="on"){
           if($File4){ if($File4_Y){ unlink("$upload_dir/$File4_Y"); }    include"$T_DirUrl/Upload_4.php"; 
		   }else{  
			   if($File4_Y){ unlink("$upload_dir/$File4_Y"); }
			   }
}else{ if($File4_Y){$File4NAME="$File4_Y";}else{ if($File4){include"$T_DirUrl/Upload_4.php";}}  }

if($ImeFiveChick=="on"){
           if($File5){ if($File5_Y){ unlink("$upload_dir/$File5_Y"); }    include"$T_DirUrl/Upload_5.php"; 
		   }else{  
			   if($File5_Y){ unlink("$upload_dir/$File5_Y"); }
			   }
}else{ if($File5_Y){$File5NAME="$File5_Y";}else{ if($File5){include"$T_DirUrl/Upload_5.php";}}  }

	$fp = fopen("$T_DirFole", "w");
	fwrite($fp, "<?php\n");
	fwrite($fp, "\$DesignMoney=\"$moeny\";\n");
	fwrite($fp, "\$SectionOne=\"$Section1\";\n");
	fwrite($fp, "\$SectionTwo=\"$Section2\";\n");
	fwrite($fp, "\$SectionTree=\"$Section3\";\n");
	fwrite($fp, "\$SectionFour=\"$Section4\";\n");
	fwrite($fp, "\$SectionFive=\"$Section5\";\n");
	fwrite($fp, "\$ImgOne=\"$File1NAME\";\n");
	fwrite($fp, "\$ImgTwo=\"$File2NAME\";\n");
	fwrite($fp, "\$ImgTree=\"$File3NAME\";\n");
	fwrite($fp, "\$ImgFour=\"$File4NAME\";\n");
	fwrite($fp, "\$ImgFive=\"$File5NAME\";\n");
	fwrite($fp, "?>");
	fclose($fp);

echo ("<script language=javascript>
window.alert('수정 완료....*^^*\\n\\n$WebSoftCopyright');
</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=IncForm'>
");
exit;

}
?>