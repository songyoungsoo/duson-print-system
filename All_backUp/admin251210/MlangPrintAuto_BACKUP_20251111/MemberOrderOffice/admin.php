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

////////////////// 관리자 로그인 ////////////////////
include"../../../db.php";
include"../../config.php";
////////////////////////////////////////////////////

if($mode=="MlangFileOk"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////

$MAXFSIZE="99999";
$upload_dir="./upload";
	if($check){
		if($photofile_4){ include"upload_4.php"; $fileOk="$photofile_4Name"; }else{ if($file){unlink("$upload_dir/$file");} }
	}else{
		$fileOk="$file";
	}

$query ="UPDATE mlangprintauto_MemberOrderOffice SET $code='$fileOk' WHERE no='$no'";
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


} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="MlangFile"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<html>
<title>자료첨부 수정</title>

<head>
<style>
td, table{BORDER-COLOR:#000000; border-collapse:collapse; color:#000000; font-size:10pt; FONT-FAMILY:돋움; word-break:break-all;}
input, TEXTAREA {color:#000000; font-size:9pt; border: expression( (this.type=='checkbox'||this.type=='checkbox')?'':'1px solid #444444' ); vertical-align:middle;} 
TEXTAREA {overflow:hidden} 
</style>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=400,availHeight=250);
</script>

</head>

<body>

 <table border=1 align=center cellpadding=5 cellspacing=0 width=340>
 <form method='post' enctype='multipart/form-data' action='<?=$PHP_SELF?>'>
 <input type='hidden' name='file' value='<?=$file?>'>
 <input type='hidden' name='no' value='<?=$no?>'>
 <input type='hidden' name='code' value='<?=$code?>'>
 <input type='hidden' name='mode' value='MlangFileOk'>
   <tr>
     <td colspan=2>* 파일 수정페이지</td>
   </tr>
   <tr>
     <td align=center>현재파일명</td>
	 <td><?=$file?></td>
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

<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<?php if($mode=="form"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////
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
td, table{BORDER-COLOR:#707070; border-collapse:collapse; color:#000000; font-size:9pt; FONT-FAMILY:굴림; line-height:130%; word-break:break-all;}
input, TEXTAREA {color:#000000; font-size:9pt; border: expression( (this.type=='checkbox'||this.type=='checkbox')?'':'1px solid #444444' ); vertical-align:middle;} 
TEXTAREA {overflow:hidden} 
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
//////////////// 이미지 미리보기 //////////////////////////////////
/* 소스제작: http://www.script.ne.kr - Mlang */
function Mlamg_image(image) {
alert("입력하신 파일은 ["+ image +"] 입니다.");
}

//////////////// 금액 연산 ///////////////////////////////////////////
function MlangMoneyTotal() {
var f=document.FrmUserXInfo;

var Tree_3=f.Tree_3.value; 
var Tree_10=f.Tree_10.value; 
var Tree_6=f.Tree_6.value; 
var Tree_13=f.Tree_13.value; 
if(!Tree_3){Tree_3=0;}
if(!Tree_10){Tree_10=0;}
if(!Tree_6){Tree_6=0;}
if(!Tree_13){Tree_13=0;}

f.Tree_7.value = eval(Tree_3)+eval(Tree_10)+eval(Tree_6)+eval(Tree_13);

}

</script>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=660,availHeight=680);

function MlangWindowOne(code){
	var f=document.FrmUserXInfo;
	var money=f.Tree_7.value;
	if(!code){ alert("인쇄모드에서만 사용이 가능합니다.")
		}else{
           if (confirm("거래명세표에 부가세을 포함시키시면 [확인]을\n\n부가세을 포함 하지않으시려면 [취소]을\n\n선택하여주세요")) {
		      window.open("int.php?EEE=1&mode=One&code="+code+"&momey="+money,"MlangWindowOne","scrollbars=yes,resizable=no,width=400,height=50,top=0,left=0");
		   }else{
			  window.open("int.php?EEE=2&mode=One&code="+code+"&momey="+money,"MlangWindowOne","scrollbars=yes,resizable=no,width=400,height=50,top=0,left=0");
		   }

	}
}

function MlangWindowTwo(code){
	if(!code){ alert("인쇄모드에서만 사용이 가능합니다.")
		}else{
		popup = window.open("int.php?mode=Two&code="+code,"MlangWindowTwo","scrollbars=no,resizable=yes,width=400,height=50,top=0,left=0");
        popup.focus();
	}
}
</script>

</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding=5 cellspacing=0>

<?if($mode=="form"){?>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?=$PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='<?if($code=="modify"){?>modify_ok<?}else{?>form_ok<?}?>'>
<?if($code=="modify"){?><INPUT TYPE="hidden" name='no' value='<?=$no?>'><?}?>
<?}?>

<!---------- One 시작 -------------------->
<tr>
<td>
      <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=left>
		 <font style='font-size:10pt; font:bold;'>작성자:</font>&nbsp;<INPUT TYPE="text" NAME="One_1" size=30 <?if($View_One_1){echo("value='$View_One_1'");}?> style='font-size:10pt; font:bold; height:22;'>
		 </td>
		 <td align=right>
		 업체구분:
		 <INPUT TYPE="checkbox" NAME="One_2"  value='1' <?if($View_One_2=="1"){echo("checked");}?>>신규업체
		 <INPUT TYPE="checkbox" NAME="One_2"  value='2' <?if($View_One_2=="2"){echo("checked");}?>>거래업체
		 <INPUT TYPE="checkbox" NAME="One_2"  value='3' <?if($View_One_2=="3"){echo("checked");}?>>하청
		 </td>
       </tr>
     </table>
</td>
</tr>

<tr>
<td class='coolBar'>
      <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center width=50><font color=red>업체명</font></td>
		 <td><INPUT TYPE="text" NAME="One_3" size=24 <?if($View_One_3){echo("value='$View_One_3'");}?>></td>
		 <td align=center width=50><font color=red>담당자</font></td>
		 <td><INPUT TYPE="text" NAME="One_4" size=24 <?if($View_One_4){echo("value='$View_One_4'");}?>></td>
		 <td align=center width=50>E-mail</td>
		 <td><INPUT TYPE="text" NAME="One_5" size=24 <?if($View_One_5){echo("value='$View_One_5'");}?>></td>
       </tr>
	   <tr>
         <td align=center><font color=red>연락처</font></td>
		 <td><INPUT TYPE="text" NAME="One_6" size=24 <?if($View_One_6){echo("value='$View_One_6'");}?>></td>
		 <td align=center><font color=red>핸드폰</font></td>
		 <td><INPUT TYPE="text" NAME="One_7" size=24 <?if($View_One_7){echo("value='$View_One_7'");}?>></td>
		 <td align=center>FAX</td>
		 <td><INPUT TYPE="text" NAME="One_8" size=24 <?if($View_One_8){echo("value='$View_One_8'");}?>></td>
       </tr>
	   <tr>
         <td align=center><font color=red>택배지</font></td>
		 <td colspan=6><INPUT TYPE="text" NAME="One_9" size=68 <?if($View_One_9){echo("value='$View_One_9'");}?>></td>
       </tr>
     </table>
</td>
</tr>
<!---------- One 끄읕 -------------------->

<!---------- Two 시작 -------------------->
<tr>
<td><b>■주문의뢰상황</b></td>
</tr>

<tr>
<td class='coolBar'>

     <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center width=80>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?if($View_Two_1 || $View_Two_2){?>bgcolor='#000000'<?}?> height=22><?if($View_Two_1 || $View_Two_2){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?}?>상품권</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		    <INPUT TYPE="checkbox" NAME="Two_1"  value='1' <?if($View_Two_1=="1"){echo("checked");}?>>머니빌지
            <INPUT TYPE="text" NAME="Two_2" size=8 <?if($View_Two_2){echo("value='$View_Two_2'");}?>>
			&nbsp;&nbsp;
			디자인:<INPUT TYPE="checkbox" NAME="Two_3"  value='1' <?if($View_Two_3=="1"){echo("checked");}?>>유
			<INPUT TYPE="checkbox" NAME="Two_3"  value='2' <?if($View_Two_3=="2"){echo("checked");}?>>무
			수량:<INPUT TYPE="text" NAME="Two_4" size=8 <?if($View_Two_4){echo("value='$View_Two_4'");}?>>
			건수:<INPUT TYPE="text" NAME="Two_5" size=8 <?if($View_Two_5){echo("value='$View_Two_5'");}?>>
		 </td>
		 <td width=90 align=center><INPUT TYPE="text" NAME="Two_47" size=10 <?if($View_Two_47){echo("value='$View_Two_47'");}?>>원</td>
       </tr>
	   <tr>
		 <td colspan=2 nowrap>
		 &nbsp;&nbsp;&nbsp;
		 인쇄:<INPUT TYPE="checkbox" NAME="Two_6"  value='1' <?if($View_Two_6=="1"){echo("checked");}?>>양
			<INPUT TYPE="checkbox" NAME="Two_6"  value='2' <?if($View_Two_6=="2"){echo("checked");}?>>단
			&nbsp;
		 후가공:<INPUT TYPE="checkbox" NAME="Two_7_1"  value='1' <?if($View_Two_7_1=="1"){echo("checked");}?>>넘버링
			<INPUT TYPE="checkbox" NAME="Two_7_2"  value='1' <?if($View_Two_7_2=="1"){echo("checked");}?>>난수
			<INPUT TYPE="checkbox" NAME="Two_7_3"  value='1' <?if($View_Two_7_3=="1"){echo("checked");}?>>스크래치
		 </td>
		 <td align=center><INPUT TYPE="text" NAME="Two_48" size=10 <?if($View_Two_48){echo("value='$View_Two_48'");}?>>원</td>
       </tr>
	   <tr>
         <td align=center>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?if($View_Two_9 || $View_Two_10){?>bgcolor='#000000'<?}?> height=22><?if($View_Two_9 || $View_Two_10){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?}?>포스터</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		    <INPUT TYPE="checkbox" NAME="Two_9"  value='1' <?if($View_Two_9=="1"){echo("checked");}?>>A2
			<INPUT TYPE="checkbox" NAME="Two_9"  value='2' <?if($View_Two_9=="2"){echo("checked");}?>>4절
			<INPUT TYPE="checkbox" NAME="Two_9"  value='3' <?if($View_Two_9=="3"){echo("checked");}?>>2절
			<INPUT TYPE="text" NAME="Two_10" size=6 <?if($View_Two_10){echo("value='$View_Two_10'");}?>>
			&nbsp;
			수량:<INPUT TYPE="text" NAME="Two_11" size=5 <?if($View_Two_11){echo("value='$View_Two_11'");}?>>
			&nbsp;
			디자인:<INPUT TYPE="checkbox" NAME="Two_12"  value='1' <?if($View_Two_12=="1"){echo("checked");}?>>유
			<INPUT TYPE="checkbox" NAME="Two_12"  value='2' <?if($View_Two_12=="2"){echo("checked");}?>>무
			&nbsp;
			기타:<INPUT TYPE="text" NAME="Two_13" size=6 <?if($View_Two_13){echo("value='$View_Two_13'");}?>>
		 </td>
		 <td align=center><INPUT TYPE="text" NAME="Two_49" size=10 <?if($View_Two_49){echo("value='$View_Two_49'");}?>>원</td>
       </tr>
	   <tr>
         <td align=center width=80>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?if($View_Two_14 || $View_Two_15){?>bgcolor='#000000'<?}?> height=22><?if($View_Two_14 || $View_Two_15){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?}?>전단,리플렛</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		    <INPUT TYPE="checkbox" NAME="Two_14"  value='1' <?if($View_Two_14=="1"){echo("checked");}?>>A4
			<INPUT TYPE="checkbox" NAME="Two_14"  value='2' <?if($View_Two_14=="2"){echo("checked");}?>>16절
			<INPUT TYPE="checkbox" NAME="Two_14"  value='3' <?if($View_Two_14=="3"){echo("checked");}?>>A3
			<INPUT TYPE="text" NAME="Two_15" size=6 <?if($View_Two_15){echo("value='$View_Two_15'");}?>>
			&nbsp;
			수량:<INPUT TYPE="text" NAME="Two_16" size=5 <?if($View_Two_16){echo("value='$View_Two_16'");}?>>
			&nbsp;
			디자인:<INPUT TYPE="checkbox" NAME="Two_17"  value='1' <?if($View_Two_17=="1"){echo("checked");}?>>유
			<INPUT TYPE="checkbox" NAME="Two_17"  value='2' <?if($View_Two_17=="2"){echo("checked");}?>>무
		 </td>
		 <td width=90 align=center><INPUT TYPE="text" NAME="Two_50" size=10 <?if($View_Two_50){echo("value='$View_Two_50'");}?>>원</td>
       </tr>
	   <tr>
		 <td colspan=2 nowrap>
		 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		 구분:<INPUT TYPE="checkbox" NAME="Two_18"  value='1' <?if($View_Two_18=="1"){echo("checked");}?>>합판
			<INPUT TYPE="checkbox" NAME="Two_18"  value='2' <?if($View_Two_18=="2"){echo("checked");}?>>독판
			&nbsp;
		 후가공:
               <INPUT TYPE="text" NAME="Two_19" size=6 <?if($View_Two_19){echo("value='$View_Two_19'");}?>>
			   &nbsp;
               <INPUT TYPE="text" NAME="Two_20" size=38 <?if($View_Two_20){echo("value='$View_Two_20'");}?>>
		 </td>
		 <td align=center>&nbsp;</td>
       </tr>
	   <tr>
         <td align=center width=80>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?if($View_Two_21_1 || $View_Two_21_2 || $View_Two_21_3 || $View_Two_21_4 || $View_Two_22){?>bgcolor='#000000'<?}?> height=22><?if($View_Two_21_1 || $View_Two_21_2 || $View_Two_21_3 || $View_Two_21_4 || $View_Two_22){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?}?>명함,쿠폰</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		    <INPUT TYPE="checkbox" NAME="Two_21_1"  value='1' <?if($View_Two_21_1=="1"){echo("checked");}?>>코팅
			<INPUT TYPE="checkbox" NAME="Two_21_2"  value='1' <?if($View_Two_21_2=="1"){echo("checked");}?>>무코
			<INPUT TYPE="checkbox" NAME="Two_21_3"  value='1' <?if($View_Two_21_3=="1"){echo("checked");}?>>반누
			<INPUT TYPE="checkbox" NAME="Two_21_4"  value='1' <?if($View_Two_21_4=="1"){echo("checked");}?>>휘나
			&nbsp;
			수량:<INPUT TYPE="text" NAME="Two_22" size=5 <?if($View_Two_22){echo("value='$View_Two_22'");}?>>
			건수:<INPUT TYPE="text" NAME="Two_23" size=5 <?if($View_Two_23){echo("value='$View_Two_23'");}?>>
			&nbsp;
			인쇄:<INPUT TYPE="checkbox" NAME="Two_24"  value='1' <?if($View_Two_24=="1"){echo("checked");}?>>양
			<INPUT TYPE="checkbox" NAME="Two_24"  value='2' <?if($View_Two_24=="2"){echo("checked");}?>>단
		 </td>
		 <td width=90 align=center><INPUT TYPE="text" NAME="Two_51" size=10 <?if($View_Two_51){echo("value='$View_Two_51'");}?>>원</td>
       </tr>
	   <tr>
		 <td colspan=2 nowrap>
		 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		 구분:<INPUT TYPE="checkbox" NAME="Two_25"  value='1' <?if($View_Two_25=="1"){echo("checked");}?>>합판
			<INPUT TYPE="checkbox" NAME="Two_25"  value='2' <?if($View_Two_25=="2"){echo("checked");}?>>독판
			&nbsp;
		 후가공:
               <INPUT TYPE="text" NAME="Two_26" size=6 <?if($View_Two_26){echo("value='$View_Two_26'");}?>>
			   &nbsp;
               <INPUT TYPE="text" NAME="Two_27" size=38 <?if($View_Two_27){echo("value='$View_Two_27'");}?>>
		 </td>
		 <td align=center>&nbsp;</td>
       </tr>
	   <tr>
         <td align=center width=80>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?if($View_Two_28 || $View_Two_29){?>bgcolor='#000000'<?}?> height=22><?if($View_Two_28 || $View_Two_29){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?}?>복권,넘버링</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		    <INPUT TYPE="checkbox" NAME="Two_28"  value='1' <?if($View_Two_28=="1"){echo("checked");}?>>A4
			<INPUT TYPE="text" NAME="Two_29" size=6 <?if($View_Two_29){echo("value='$View_Two_29'");}?>>
			&nbsp;
			수량:<INPUT TYPE="text" NAME="Two_30" size=5 <?if($View_Two_30){echo("value='$View_Two_30'");}?>>
			&nbsp;
			인쇄:<INPUT TYPE="checkbox" NAME="Two_31"  value='1' <?if($View_Two_31=="1"){echo("checked");}?>>양
			<INPUT TYPE="checkbox" NAME="Two_31"  value='2' <?if($View_Two_31=="2"){echo("checked");}?>>단
		 </td>
		 <td width=90 align=center><INPUT TYPE="text" NAME="Two_52" size=10 <?if($View_Two_52){echo("value='$View_Two_52'");}?>>원</td>
       </tr>
	   <tr>
		 <td colspan=2 nowrap>
		 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		 구분:<INPUT TYPE="checkbox" NAME="Two_32"  value='1' <?if($View_Two_32=="1"){echo("checked");}?>>합판
			<INPUT TYPE="checkbox" NAME="Two_32"  value='2' <?if($View_Two_32=="2"){echo("checked");}?>>독판
			&nbsp;
		 후가공:<INPUT TYPE="checkbox" NAME="Two_33_1"  value='1' <?if($View_Two_33_1=="1"){echo("checked");}?>>넘버링
			<INPUT TYPE="checkbox" NAME="Two_33_2"  value='1' <?if($View_Two_33_2=="1"){echo("checked");}?>>난수
			<INPUT TYPE="checkbox" NAME="Two_33_3"  value='1' <?if($View_Two_33_3=="1"){echo("checked");}?>>스크래치
			&nbsp;
               <INPUT TYPE="text" NAME="Two_34" size=17 <?if($View_Two_34){echo("value='$View_Two_34'");}?>>
		 </td>
		 <td align=center>&nbsp;</td>
       </tr>
	   <tr>
         <td align=center width=80>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?if($View_Two_35 || $View_Two_36){?>bgcolor='#000000'<?}?> height=22><?if($View_Two_35 || $View_Two_36){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?}?>카다로그</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		 	<INPUT TYPE="checkbox" NAME="Two_35"  value='1' <?if($View_Two_35=="1"){echo("checked");}?>>부수
			<INPUT TYPE="text" NAME="Two_36" size=8 <?if($View_Two_36){echo("value='$View_Two_36'");}?>>&nbsp;&nbsp;
            페이지:<INPUT TYPE="text" NAME="Two_37" size=8 <?if($View_Two_37){echo("value='$View_Two_37'");}?>>&nbsp;
			지질:<INPUT TYPE="text" NAME="Two_38" size=8 <?if($View_Two_38){echo("value='$View_Two_38'");}?>>&nbsp;
			두께:<INPUT TYPE="text" NAME="Two_39" size=8 <?if($View_Two_39){echo("value='$View_Two_39'");}?>>
		 </td>
		 <td width=90 align=center><INPUT TYPE="text" NAME="Two_53" size=10 <?if($View_Two_53){echo("value='$View_Two_53'");}?>>원</td>
       </tr>
	   <tr>
		 <td colspan=2 nowrap>
		 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            제본<INPUT TYPE="checkbox" NAME="Two_40"  value='1' <?if($View_Two_40=="1"){echo("checked");}?>>중철
			<INPUT TYPE="text" NAME="Two_41" size=58 <?if($View_Two_41){echo("value='$View_Two_41'");}?>>
		 </td>
		 <td align=center>&nbsp;</td>
       </tr>
	   <tr>
         <td align=center width=80>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?if($View_Two_42 || $View_Two_43){?>bgcolor='#000000'<?}?> height=22><?if($View_Two_42 || $View_Two_43){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?}?>스티카</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		 &nbsp;수량:<INPUT TYPE="text" NAME="Two_42" size=8 <?if($View_Two_42){echo("value='$View_Two_42'");}?>>
		 크기:<INPUT TYPE="text" NAME="Two_43" size=8 <?if($View_Two_43){echo("value='$View_Two_43'");}?>>
		 &nbsp;
		 <INPUT TYPE="checkbox" NAME="Two_44"  value='1' <?if($View_Two_44=="1"){echo("checked");}?>>코팅
		 <INPUT TYPE="checkbox" NAME="Two_44"  value='2' <?if($View_Two_44=="2"){echo("checked");}?>>무코
		 </td>
		 <td width=90 align=center><INPUT TYPE="text" NAME="Two_54" size=10 <?if($View_Two_54){echo("value='$View_Two_54'");}?>>원</td>
       </tr>
	   <tr>
         <td align=center width=80>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?if($View_Two_45 || $View_Two_46){?>bgcolor='#000000'<?}?> height=22><?if($View_Two_45 || $View_Two_46){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?}?>기타</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		 &nbsp;<INPUT TYPE="text" NAME="Two_45" size=12 <?if($View_Two_45){echo("value='$View_Two_45'");}?>>&nbsp;
		 <INPUT TYPE="text" NAME="Two_46" size=55 <?if($View_Two_46){echo("value='$View_Two_46'");}?>>
		 </td>
		 <td width=90 align=center><INPUT TYPE="text" NAME="Two_55" size=10 <?if($View_Two_55){echo("value='$View_Two_55'");}?>>원</td>
       </tr>
     </table>

</td>
</tr>
<!---------- Two 끄읕 -------------------->


<!---------- Four 시작 -------------------->
<tr>
<td><b>■결제,배송상황</b></td>
</tr>

<tr>
<td class='coolBar'>
     <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center><font color=red>&nbsp;&nbsp;은&nbsp;행&nbsp;명&nbsp;</font></td>
		 <td align=center><INPUT TYPE="text" NAME="Four_1" size=19 <?if($View_Four_1){echo("value='$View_Four_1'");}?>></td>
		 <td align=center><font color=red>&nbsp;&nbsp;입&nbsp;금&nbsp;자&nbsp&nbsp;</font></td>
		 <td align=center><INPUT TYPE="text" NAME="Four_2" size=26 <?if($View_Four_2){echo("value='$View_Four_2'");}?>></td>
		 <td align=center>&nbsp;&nbsp;세금계산서&nbsp;&nbsp;</td>
		 <td>
		 <INPUT TYPE="checkbox" NAME="Four_3"  value='1' <?if($View_Four_3=="1"){echo("checked");}?>>발행
		 <INPUT TYPE="checkbox" NAME="Four_3"  value='2' <?if($View_Four_3=="2"){echo("checked");}?>>미발행
		 </td>
       </tr>
	   <tr>
         <td align=center>&nbsp;입금총액&nbsp;<BR>&nbsp;<font style='font-family:돋움; font-size:8pt;'>(부가세포함)</font>&nbsp;</td>
		 <td align=center><INPUT TYPE="text" NAME="Four_4" size=19 <?if($View_Four_4){echo("value='$View_Four_4'");}?>></td>
		 <td align=center>&nbsp;비&nbsp;&nbsp;고&nbsp;</td>
		 <td align=center><INPUT TYPE="text" NAME="Four_5" size=26 <?if($View_Four_5){echo("value='$View_Four_5'");}?>></td>
		 <td align=center>&nbsp;&nbsp;배송요금&nbsp;&nbsp;</td>
		 <td>
		 <INPUT TYPE="checkbox" NAME="Four_6"  value='1' <?if($View_Four_6=="1"){echo("checked");}?>>선불
		 <INPUT TYPE="checkbox" NAME="Four_6"  value='2' <?if($View_Four_6=="2"){echo("checked");}?>>착불
		 </td>
       </tr>
	   <tr>
         <td align=center>&nbsp;부&nbsp;가&nbsp;세</td>
		 <td align=center><INPUT TYPE="text" NAME="Four_7" size=19 <?if($View_Four_7){echo("value='$View_Four_7'");}?>></td>
		 <td align=center>배&nbsp;&nbsp;송</td>
		 <td align=center><font style='font-family:돋움; font-size:8pt;'>
		 <INPUT TYPE="checkbox" NAME="Four_8"  value='1' <?if($View_Four_8=="1"){echo("checked");}?>>택배
		 <INPUT TYPE="checkbox" NAME="Four_8"  value='2' <?if($View_Four_8=="2"){echo("checked");}?>>퀵
		 <INPUT TYPE="checkbox" NAME="Four_8"  value='3' <?if($View_Four_8=="3"){echo("checked");}?>>화물
		 <INPUT TYPE="checkbox" NAME="Four_8"  value='4' <?if($View_Four_8=="4"){echo("checked");}?>>방문
		 </font></td>
		 <td align=center>&nbsp;&nbsp;완불확인&nbsp;&nbsp;</td>
		 <td align=center><INPUT TYPE="text" NAME="Four_9" size=15 <?if($View_Four_9){echo("value='$View_Four_9'");}?>></td>
       </tr>
     </table>
</td>
</tr>
<!----------  -------------------->

<!----------  -------------------->
<tr>
<td><b>■작업진행상황</b></td>
</tr>

<tr>
<td class='coolBar'>
      <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
	  <tr>
         <td align=center>&nbsp;진행방법&nbsp</td>
		 <td colspan=2>
         <INPUT TYPE="checkbox" NAME="Five_1"  value='1' <?if($View_Five_1=="1"){echo("checked");}?>>합판
		 <INPUT TYPE="checkbox" NAME="Five_1"  value='2' <?if($View_Five_1=="2"){echo("checked");}?>>독판
		 <INPUT TYPE="checkbox" NAME="Five_1"  value='3' <?if($View_Five_1=="3"){echo("checked");}?>>기타
		 <INPUT TYPE="text" NAME="Five_2" size=16 <?if($View_Five_2){echo("value='$View_Five_2'");}?>>
		 </td>
		 <td align=center>&nbsp;진행담당자&nbsp</td>
		 <td colspan=2>&nbsp;<INPUT TYPE="text" NAME="Five_3" size=16 <?if($View_Five_3){echo("value='$View_Five_3'");}?>></td>
       </tr>
       <tr>
         <td align=center>&nbsp;배&nbsp송&nbsp일&nbsp</td>
		 <td>&nbsp;<INPUT TYPE="text" NAME="Five_4" size=16 <?if($View_Five_4){echo("value='$View_Five_4'");}?> onClick="Calendar(this);"></td>
		 <td align=center>&nbsp;도&nbsp착&nbsp일&nbsp</td>
		 <td>&nbsp;<INPUT TYPE="text" NAME="Five_5" size=16 <?if($View_Five_5){echo("value='$View_Five_5'");}?> onClick="Calendar(this);"></td>
		 <td align=center>&nbsp;유&nbsp;&nbsp;형&nbsp</td>
		 <td>&nbsp;<INPUT TYPE="text" NAME="Five_6" size=16 <?if($View_Five_6){echo("value='$View_Five_6'");}?>></td>
       </tr>
     </table>
</td>
</tr>
<!---------- -------------------->

<!----------  -------------------->

<tr>
<td class='coolBar'>

     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center width=32% valign=top>
          <!-----^^^^^^^------->
		    <table border=1 align=center width=100% cellpadding=0 cellspacing=0 height=188>
              <tr>
                <td align=center colspan=2 height=22><b>합판인쇄 의뢰처</b></td>
              </tr>
			  <tr>
                <td colspan=2>
		 <INPUT TYPE="checkbox" NAME="Five_7"  value='1' <?if($View_Five_7=="1"){echo("checked");}?>>OO
		 <INPUT TYPE="checkbox" NAME="Five_7"  value='2' <?if($View_Five_7=="2"){echo("checked");}?>>OO
		 <INPUT TYPE="checkbox" NAME="Five_7"  value='3' <?if($View_Five_7=="3"){echo("checked");}?>>기타
		 <INPUT TYPE="text" NAME="Five_8" size=8 <?if($View_Five_8){echo("value='$View_Five_8'");}?>>
				</td>
              </tr>
			  <tr>
                <td align=center>&nbsp;&nbsp;금액&nbsp;&nbsp;</td>
				<td>&nbsp;<INPUT TYPE="text" NAME="Five_9" size=24 <?if($View_Five_9){echo("value='$View_Five_9'");}?>></td>
              </tr>
			  <tr>
                <td align=center>접수<BR>파일</td>
				<td>&nbsp;<TEXTAREA NAME="Five_10" ROWS="3" COLS="25"><?if($View_Five_10){echo("$View_Five_10");}?></TEXTAREA></td>
              </tr>
			  <tr>
                <td align=center>번호</td>
				<td>&nbsp;<INPUT TYPE="text" NAME="Five_11" size=24 <?if($View_Five_11){echo("value='$View_Five_11'");}?>></td>
              </tr>
			  <tr>
                <td align=center>비고</td>
				<td>&nbsp;<TEXTAREA NAME="Five_12" ROWS="3" COLS="25"><?if($View_Five_12){echo("$View_Five_12");}?></TEXTAREA></td>
              </tr>
            </table>
		  <!-----^^^^^^^------->
         </td>
		 <td></td>
		 <td align=center width=32% valign=top>
          <!-----^^^^^^^------->
		    <table border=1 align=center width=100% cellpadding=0 cellspacing=0 height=188>
              <tr>
                <td align=center colspan=2 height=22><b>독판인쇄 의뢰처</b></td>
              </tr>
			   <tr>
                <td align=center rowspan=2>&nbsp;&nbsp;지업사&nbsp;&nbsp;</td>
				<td>
                <INPUT TYPE="checkbox" NAME="Five_13"  value='1' <?if($View_Five_13=="1"){echo("checked");}?>>OO
				<INPUT TYPE="text" NAME="Five_14" size=15 <?if($View_Five_14){echo("value='$View_Five_14'");}?>>
				</td>
              </tr>
			  <tr>
				<td>&nbsp;용지대:&nbsp;<INPUT TYPE="text" NAME="Five_15" size=15 <?if($View_Five_15){echo("value='$View_Five_15'");}?>></td>
              </tr>
			  <tr>
                <td align=center rowspan=2>&nbsp;&nbsp;인쇄처&nbsp;&nbsp;</td>
				<td height=25>
                <INPUT TYPE="checkbox" NAME="Five_16"  value='1' <?if($View_Five_16=="1"){echo("checked");}?>>OO
				<INPUT TYPE="text" NAME="Five_17" size=15 <?if($View_Five_17){echo("value='$View_Five_17'");}?>>
				</td>
              </tr>
			  <tr>
				<td height=25>&nbsp;인쇄대:&nbsp;<INPUT TYPE="text" NAME="Five_18" size=15 <?if($View_Five_18){echo("value='$View_Five_18'");}?>></td>
              </tr>
			  <tr>
                <td align=center>후가공</td>
				<td>&nbsp;<INPUT TYPE="text" NAME="Five_19" size=22 <?if($View_Five_19){echo("value='$View_Five_19'");}?>></td>
              </tr>
			  <tr>
                <td align=center>비고</td>
				<td>&nbsp;<TEXTAREA NAME="Five_20" ROWS="3" COLS="23"><?if($View_Five_20){echo("$View_Five_20");}?></TEXTAREA></td>
              </tr>
            </table>
		  <!-----^^^^^^^------->
         </td>
		 <td></td>
		 <td align=center width=32% valign=top>
           <!-----^^^^^^^------->
		    <table border=1 align=center width=100% cellpadding=0 cellspacing=0 height=188>
              <tr>
                <td align=center height=22>작업자 지원</td>
				<td align=center>자료 첨부</td>
              </tr>
			  <tr>
                <td align=center height=22><TEXTAREA NAME="Five_21" ROWS="6" COLS="14"><?if($View_Five_21){echo("$View_Five_21");}?></TEXTAREA></td>
				<td>

<script>
function ImgMlangGo(fileurl,code){
	var str;
		if (confirm("[확인]누르시면 파일을 다운로드가능한 창이 뜨고\n\n[최소]을 누르시면 파일을 수정하실수 있습니다.")) {
		window.open("./upload/"+fileurl,"fileurlged","scrollbars=no,resizable=yes,width=600,height=500,top=0,left=0");
	   }else{
        popup = window.open("<?=$PHP_SELF?>?mode=MlangFile&file="+fileurl+"&code="+code+"&no=<?=$no?>","Mlangdhdimodu","scrollbars=no,resizable=no,width=400,height=150,top=0,left=0");
        popup.focus();
	   }
}
</script>

<?if($View_Five_22){?>&nbsp;<a href="javascript:ImgMlangGo('<?=$View_Five_22?>','Five_22');"><?=str_cutting("$View_Five_22",16)?></a><BR><?}else{?>
<INPUT TYPE="file" NAME="photofile_1" size=1 onChange="Mlamg_image(this.value)"><BR>
<?}?>
<?if($View_Five_23){?>&nbsp;<a href="javascript:ImgMlangGo('<?=$View_Five_23?>','Five_23');"><?=str_cutting("$View_Five_23",16)?></a><BR><?}else{?>
<INPUT TYPE="file" NAME="photofile_2" size=1 onChange="Mlamg_image(this.value)"><BR>
<?}?>
<?if($View_Five_24){?>&nbsp;<a href="javascript:ImgMlangGo('<?=$View_Five_24?>','Five_24');"><?=str_cutting("$View_Five_24",16)?></a><BR><?}else{?>
<INPUT TYPE="file" NAME="photofile_3" size=1 onChange="Mlamg_image(this.value)"><BR>
<?}?>
<?if($View_Five_25){?>&nbsp;<a href="javascript:ImgMlangGo('<?=$View_Five_25?>','Five_25');"><?=str_cutting("$View_Five_25",16)?></a><BR><?}else{?>
<INPUT TYPE="file" NAME="photofile_4" size=1 onChange="Mlamg_image(this.value)"><BR>
<?}?>

				</td>
              </tr>
			  <tr>
                <td colspan=2>
				<p align=left style='text-indent:0; margin-right:5pt; margin-left:5pt; margin-top:6pt; margin-bottom:6pt;'>
				<font style='font-family:돋움; font-size:8pt;'>
				작업자가필요한자료등을첨부하여사용<BR>
				(그림, 난수자료, 일러파일 등)<BR>
				작업중에도 자료을 올릴수 있습니다.)
				</font></p>
				</td>
              </tr>
            </table>
		  <!-----^^^^^^^------->
         </td>
	  </tr>
    </table>

</td>
</tr>
<!---------- Five 끄읕 -------------------->


<tr>
<td align=right>
         <INPUT TYPE="checkbox" NAME="Five_26"  value='1' <?if($View_Five_26=="1"){echo("checked");}?>>디자인중
		 <INPUT TYPE="checkbox" NAME="Five_26"  value='2' <?if($View_Five_26=="2"){echo("checked");}?>>교정중
		 <INPUT TYPE="checkbox" NAME="Five_26"  value='3' <?if($View_Five_26=="3"){echo("checked");}?>>인쇄중
		 <INPUT TYPE="checkbox" NAME="Five_26"  value='4' <?if($View_Five_26=="4"){echo("checked");}?>>가공중
		 <INPUT TYPE="checkbox" NAME="Five_26"  value='5' <?if($View_Five_26=="5"){echo("checked");}?>>납품
</td>
</tr>

<?if($code=="Print"){}else{if($mode=="form"){?>
<tr>
<td align=center>
<input type='submit' value=' <?if($code=="modify"){?>수정<?}else{?>저장<?}?> 합니다.'>
<BR><BR>
</td>
</tr>
<?}}?>

</table>

<?php } //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="form_ok"){

	$result = mysqli_query($db, "SELECT max(no) FROM mlangprintauto_MemberOrderOffice");
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

$MAXFSIZE="99999";
$upload_dir="./upload";

if($photofile_1){ include"upload_1.php"; }
if($photofile_2){ include"upload_2.php"; }
if($photofile_3){ include"upload_3.php"; }
if($photofile_4){ include"upload_4.php"; }


$date=date("Y-m-d H:i;s");

$Two_7="${Two_7_1}-${Two_7_2}-${Two_7_3}";
$Two_21="${Two_21_1}-${Two_21_2}-${Two_21_3}-${Two_21_4}";
$Two_33="${Two_33_1}-${Two_33_2}-${Two_33_3}";
$dbinsert ="insert into mlangprintauto_MemberOrderOffice values('$new_no',
'$One_1',
'$One_2',
'$One_3',
'$One_4',
'$One_5',
'$One_6',
'$One_7',
'$One_8',
'$One_9',
'$One_10',
'$One_11',
'$One_12', 
'$Two_1',
'$Two_2',
'$Two_3',
'$Two_4',
'$Two_5',
'$Two_6',
'$Two_7',
'$Two_8',
'$Two_9',
'$Two_10',
'$Two_11',
'$Two_12',
'$Two_13',
'$Two_14',
'$Two_15',
'$Two_16',
'$Two_17',
'$Two_18',
'$Two_19',
'$Two_20',
'$Two_21',
'$Two_22',
'$Two_23',
'$Two_24',
'$Two_25',
'$Two_26',
'$Two_27',
'$Two_28',
'$Two_29',
'$Two_30',
'$Two_31',
'$Two_32',
'$Two_33',
'$Two_34',
'$Two_35',
'$Two_36',
'$Two_37',
'$Two_38',
'$Two_39',
'$Two_40',
'$Two_41',
'$Two_42',
'$Two_43',
'$Two_44',
'$Two_45',
'$Two_46',
'$Two_47',
'$Two_48',
'$Two_49',
'$Two_50',
'$Two_51',
'$Two_52',
'$Two_53',
'$Two_54',
'$Two_55',
'$Two_56',
'$Two_57',
'$Two_58',
'$Tree_1',
'$Tree_2',
'$Tree_3',
'$Tree_4',
'$Tree_5',
'$Tree_6',
'$Tree_7',
'$Tree_8',
'$Tree_9',
'$Tree_10',
'$Tree_11',
'$Tree_12',
'$Tree_13',
'$Tree_14',
'$Tree_15', 
'$Four_1',
'$Four_2',
'$Four_3',
'$Four_4',
'$Four_5',
'$Four_6',
'$Four_7',
'$Four_8',
'$Four_9',
'$Four_10',
'$Four_11',
'$Four_12', 
'$Five_1',
'$Five_2',
'$Five_3',
'$Five_4',
'$Five_5',
'$Five_6',
'$Five_7',
'$Five_8',
'$Five_9',
'$Five_10',
'$Five_11',
'$Five_12',
'$Five_13',
'$Five_14',
'$Five_15',
'$Five_16',
'$Five_17',
'$Five_18',
'$Five_19',
'$Five_20',
'$Five_21',
'$photofile_1Name',
'$photofile_2Name',
'$photofile_3Name',
'$photofile_4Name',
'$Five_26',
'$Five_27',
'$Five_28',
'$Five_29',
'$cont',
'$date'
)";
$result_insert= mysqli_query($db, $dbinsert);

	echo ("
		<script language=javascript>
		alert('\\n자료를 정상적으로 저장 하였습니다.\\n\\n자료를 새로 등록하시려면 창을 다시 여세요\\n');
        opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;


} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="modify_ok"){

$MAXFSIZE="99999";
$upload_dir="./upload";

if($photofile_1){ include"upload_1.php";  $Five_22KKok="Five_22='$photofile_1Name',"; }
if($photofile_2){ include"upload_2.php";  $Five_23KKok="Five_23='$photofile_2Name',";  }
if($photofile_3){ include"upload_3.php";  $Five_24KKok="Five_24='$photofile_3Name',";  }
if($photofile_4){ include"upload_4.php";  $Five_25KKok="Five_25='$photofile_4Name',";  }

$Two_7="${Two_7_1}-${Two_7_2}-${Two_7_3}";
$Two_21="${Two_21_1}-${Two_21_2}-${Two_21_3}-${Two_21_4}";
$Two_33="${Two_33_1}-${Two_33_2}-${Two_33_3}";
$query ="UPDATE mlangprintauto_MemberOrderOffice SET
One_1='$One_1',
One_2='$One_2',
One_3='$One_3',
One_4='$One_4',
One_5='$One_5',
One_6='$One_6',
One_7='$One_7',
One_8='$One_8',
One_9='$One_9',
One_10='$One_10',
One_11='$One_11',
One_12='$One_12',
Two_1='$Two_1',
Two_2='$Two_2',
Two_3='$Two_3',
Two_4='$Two_4',
Two_5='$Two_5',
Two_6='$Two_6',
Two_7='$Two_7',
Two_8='$Two_8',
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
Two_21='$Two_21',
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
Two_33='$Two_33',
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
Two_56='$Two_56',
Two_57='$Two_57',
Two_58='$Two_58',
Tree_1='$Tree_1',
Tree_2='$Tree_2',
Tree_3='$Tree_3',
Tree_4='$Tree_4',
Tree_5='$Tree_5',
Tree_6='$Tree_6',
Tree_7='$Tree_7',
Tree_8='$Tree_8',
Tree_9='$Tree_9',
Tree_10='$Tree_10',
Tree_11='$Tree_11',
Tree_12='$Tree_12',
Tree_13='$Tree_13',
Tree_14='$Tree_14',
Tree_15='$Tree_15',
Four_1='$Four_1',
Four_2='$Four_2',
Four_3='$Four_3',
Four_4='$Four_4',
Four_5='$Four_5',
Four_6='$Four_6',
Four_7='$Four_7',
Four_8='$Four_8',
Four_9='$Four_9',
Four_10='$Four_10',
Four_11='$Four_11',
Four_12='$Four_12',
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
Five_21='$Five_21', $Five_22KKok $Five_23KKok $Five_24KKok $Five_25KKok
Five_26='$Five_26',
Five_27='$Five_27',
Five_28='$Five_28',
Five_29='$Five_29'
WHERE no='$no'";
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
		<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&code=modify&no=$no'>
	");
		exit;

}
mysqli_close($db);


}
?>