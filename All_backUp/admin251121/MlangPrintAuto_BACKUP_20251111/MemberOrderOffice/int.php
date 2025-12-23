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

$no="$code";
include"View.php";

if($mode=="bizinfo"){ //사업자정보 처리

   if($form=="ok"){
	   include"../../../db.php";
       $MAXFSIZE="99999";
       $upload_dir="./upload";  
       if($photofile_1){ include"upload_1.php"; }
	   $query ="UPDATE mlangprintauto_BizInfo SET MlangFild_1='$MlangFild_1', MlangFild_2='$MlangFild_2', MlangFild_3='$MlangFild_3', MlangFild_4='$MlangFild_4', MlangFild_5='$MlangFild_5', MlangFild_6='$MlangFild_6', MlangFild_7='$photofile_1Name' WHERE no='1'";
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
		alert('\\n정보를 정상적으로 저장하였습니다.\\n');
		</script>
        <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=bizinfo'>
	");
		exit;

       }
       mysqli_close($db);

   }else{

include"BizInfoView.php";
?>

<html>
<title>사업자정보관리</title>

<head>
<style>
td, table{BORDER-COLOR:#000000; border-collapse:collapse; color:#000000; font-size:10pt; FONT-FAMILY:돋움; word-break:break-all;}
input, TEXTAREA {color:#000000; font-size:9pt; border: expression( (this.type=='checkbox'||this.type=='radio')?'':'1px solid #444444' ); vertical-align:middle;} 
TEXTAREA {overflow:hidden} 
</style>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=400,availHeight=320);
</script>

</head>

<body>

 <table border=1 align=center cellpadding=5 cellspacing=0 width=340>
 <form method='post' enctype='multipart/form-data' action='<?=$PHP_SELF?>'>
 <input type='hidden' name='form' value='ok'>
 <input type='hidden' name='mode' value='bizinfo'>
   <tr>
     <td align=center colspan=4>공&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;급&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;자</td>
   </tr>
   <tr>
     <td align=center>등록번호</td>
	 <td colspan=3><input type='text' name='MlangFild_1' size='39' <?if($View_MlangFild_1){echo("value='$View_MlangFild_1'");}?>></td>
   </tr>
   <tr>
     <td align=center>상호</td>
	 <td align=center><input type='text' name='MlangFild_2' size='15' <?if($View_MlangFild_2){echo("value='$View_MlangFild_2'");}?>></td>
	 <td align=center>성명</td>
	 <td align=center><input type='text' name='MlangFild_3' size='15' <?if($View_MlangFild_3){echo("value='$View_MlangFild_3'");}?>></td>
   </tr>
   <tr>
     <td align=center>사업장주소</td>
	 <td colspan=3><input type='text' name='MlangFild_4' size='39' <?if($View_MlangFild_4){echo("value='$View_MlangFild_4'");}?>></td>
   </tr>
   <tr>
     <td align=center>업태</td>
	 <td align=center><input type='text' name='MlangFild_5' size='15' <?if($View_MlangFild_5){echo("value='$View_MlangFild_5'");}?>></td>
	 <td align=center>종목</td>
	 <td align=center><input type='text' name='MlangFild_6' size='15' <?if($View_MlangFild_6){echo("value='$View_MlangFild_6'");}?>></td>
   </tr>
    <tr>
     <td align=center>도장</td>
	 <td colspan=3><input type='file' name='photofile_1' size='23' <?if($View_MlangFild_7){echo("value='$View_MlangFild_7'");}?>><BR>
	 이미지의 크기을 50X50 픽셀로 해주세요
	 </td>
   </tr>
 </table>

 <p align=center>
		 <input type='submit' value=' 저장합니다.. '>
		 <input type='button' onclick="javascript:window.self.close();" value='창닫기'>
 </p>
 </form>

</body>
</html>



<?php    }
	
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="One"){  //명세표

include"BizInfoView.php";
?>

<html>
<title>"웹실디자인"</title>

<head>
<style>
td, table{BORDER-COLOR:#000000; border-collapse:collapse; color:#000000; font-size:10pt; FONT-FAMILY:돋움; word-break:break-all;}
input, TEXTAREA {color:#000000; font-size:9pt; border: expression( (this.type=='checkbox'||this.type=='radio')?'':'1px solid #444444' ); vertical-align:middle;} 
TEXTAREA {overflow:hidden} 
</style>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=600,availHeight=780);
</script>

</head>

<body LEFTMARGIN='10' TOPMARGIN='10' MARGINWIDTH='10' MARGINHEIGHT='10'>

     <table border=1 align=center cellpadding=0 cellspacing=0 width=540>

       <tr>
         <td>
		      <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
               <tr>
                 <td align=right width=100>&nbsp;권&nbsp;</td>
				 <td align=right width=100>&nbsp;호&nbsp;</td>
				 <td align=center width=340 height=40 colspan=4><font style='font:bold; font-size:16pt;'>거&nbsp;래&nbsp;명&nbsp;세&nbsp;표</font></td>
               </tr>
			   <tr>
                 <td align=right rowspan=2 colspan=2 height=30>
<?php $y=substr($View_date, 0,10);
$b = explode("-", $y);
$z = trim($b[0]);
$x = trim($b[1]);
$c = $b[2];
echo("<font style='font-size:11pt;'>${z}년 ${x}월 ${c}일</font>");
?>&nbsp;
				 </td>
				 <td align=center colspan=4  height=30>공&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;급&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;자</td>
               </tr>
			   <tr>
				 <td align=center height=30>등록번호</td>
				 <td align=center colspan=3><b><?=$View_MlangFild_1?></b></td>
               </tr>
			   <tr>
                 <td align=right rowspan=2 colspan=2 height=30>
				<font style='font-size:11pt;'><?=$View_One_3?><b>&nbsp;귀하</b></font>&nbsp;
				 </td>
				 <td align=center height=30>상호</td>
				 <td align=center><font style='font-size:11pt; font:bold;'><?=$View_MlangFild_2?></font></td>
				 <td align=center>&nbsp;성명&nbsp;</td>
				 <td>&nbsp;<font style='font-size:11pt;'><?=$View_MlangFild_3?></font>&nbsp;
<div style="position:absolute; top:103; visibility:visible;"><img src='./upload/<?=$View_MlangFild_7?>' width=50 height=50></div>
				 &nbsp;&nbsp;
				 </td>
               </tr>
			   <tr>
				 <td align=center height=30>&nbsp;사업장주소&nbsp;</td>
				 <td align=center colspan=3><?=$View_MlangFild_4?></td>
               </tr>
			   <tr>
                 <td align=center colspan=2 height=30>
				 <b>아래와&nbsp;&nbsp;같이&nbsp;&nbsp;계산합니다.</b>
				 </td>
				 <td align=center height=30>업태</td>
				 <td align=center><?=$View_MlangFild_5?></td>
				 <td align=center>종목</td>
				 <td align=center><?=$View_MlangFild_6?></td>
               </tr>
			   <tr>
				 <td colspan=6 height=50>&nbsp;&nbsp;
                 <font style='font-size:11pt;'>
				   <b>합계금액&nbsp;<big><?$T = "$View_Tree_7"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?></big>&nbsp;원</b>
				 <?if($EEE=="1"){?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;부가세: <?$View_Tree_7Ok=$View_Tree_7 * 10/100; $T = "$View_Tree_7Ok"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?>원<?}?></font>
                  </td>
               </tr>
             </table>

		 </td>
       </tr>

	   <tr><td height=5></td></tr>

       <tr>
         <td>
		      <table border=1 align=center width=100% cellpadding=5 cellspacing=0>
               <tr>
                 <td align=center width='35%'>품목</td>
				 <td align=center width='15%'>수량</td>
				 <td align=center width='15%'>단가</td>
				 <td align=center width='20%'>공급가액</td>
				 <td align=center width='15%'>세액</td>
               </tr>
			   <tr>
                 <td align=center><?=$View_Tree_1?></td>
				 <td align=center><?=$View_Tree_2?></td>
				 <td align=center>&nbsp;</td>
				 <td align=right><?$T = "$View_Tree_3"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?></td>
				 <td align=right><?if($EEE=="1"){?><?$View_Tree_3Ok=$View_Tree_3 * 10/100; $T = "$View_Tree_3Ok"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?><?}?></td>
               </tr>
			   <tr>
                 <td align=center><?=$View_Tree_4?></td>
				 <td align=center><?=$View_Tree_5?></td>
				 <td align=center>&nbsp;</td>
				 <td align=right><?$T = "$View_Tree_6"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?></td>
				 <td align=right><?if($EEE=="1"){?><?$View_Tree_6Ok=$View_Tree_6 * 10/100; $T = "$View_Tree_6Ok"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?><?}?></td>
               </tr>
			   <tr>
                 <td align=center><?=$View_Tree_8?></td>
				 <td align=center><?=$View_Tree_9?></td>
				 <td align=center>&nbsp;</td>
				 <td align=right><?$T = "$View_Tree_10"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?></td>
				 <td align=right><?if($EEE=="1"){?><?$View_Tree_10Ok=$View_Tree_10 * 10/100; $T = "$View_Tree_10Ok"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?><?}?></td>
               </tr>
			   <tr>
                 <td align=center><?=$View_Tree_11?></td>
				 <td align=center><?=$View_Tree_12?></td>
				 <td align=center>&nbsp;</td>
				 <td align=right><?$T = "$View_Tree_13"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?></td>
				 <td align=right><?if($EEE=="1"){?><?$View_Tree_13Ok=$View_Tree_13 * 10/100; $T = "$View_Tree_13Ok"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?><?}?></td>
               </tr>
			   <tr>
                 <td align=center>&nbsp;</td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
               </tr>
			   <tr>
                 <td align=center>&nbsp;</td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
               </tr>
			   <tr>
                 <td align=center>&nbsp;</td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
               </tr>
			   <tr>
                 <td align=center>&nbsp;</td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
               </tr>
			   <tr>
                 <td align=center>&nbsp;</td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
               </tr>
			   <tr>
                 <td align=center>&nbsp;</td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
               </tr>
			   <tr>
                 <td align=center>합계</td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=right><?$T = "$View_Tree_7"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?></td>
				 <td align=right><?if($EEE=="1"){?><?$View_Tree_7Ok=$View_Tree_7 * 10/100; $T = "$View_Tree_7Ok"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?><?}?></td>
               </tr>
             </table>
		 </td>
       </tr>

	   <tr>
         <td>&nbsp;&nbsp;<font style='font-size:11pt;'>비고</font>
		 <TEXTAREA NAME="cont" ROWS="10" COLS="81"><?=$View_cont?></TEXTAREA>
		 </td>
       </tr>
     </table>

</body>
</html>

<?php } ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="Two"){  //비고

	if($Formok=="ok"){
include"../../../db.php";
 // 자료를 수정한다..
$query ="UPDATE mlangprintauto_MemberOrderOffice SET cont='$cont' WHERE no='$code'";
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
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=Two&code=$code'>
	");
		exit;

}

mysqli_close($db);


    }else{	
?>

<html>
<title>"우리는 최고의 을지인"</title>

<head>
<style>
td, table{BORDER-COLOR:#707070; border-collapse:collapse; color:#000000; font-size:9pt; FONT-FAMILY:굴림; line-height:130%; word-break:break-all;}
input, TEXTAREA {color:#000000; font-size:9pt; border: expression( (this.type=='checkbox'||this.type=='radio')?'':'1px solid #444444' ); vertical-align:middle;} 
TEXTAREA {overflow:hidden} 
</style>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=560,availHeight=300);
</script>

</head>

<body>

     <table border=0 align=center cellpadding=0 cellspacing=0>
	   <form method='post' action='<?=$PHP_SELF?>'>
	   <input type='hidden' name='Formok' value='ok'>
	   <input type='hidden' name='code' value='<?=$code?>'>
	   <input type='hidden' name='mode' value='Two'>
       <tr>
         <td height=25><b>비고의 내용을 입력하세요</b></td>
       </tr>
	   <tr>
         <td>
		 <TEXTAREA NAME="cont" ROWS="10" COLS="80"><?=$View_cont?></TEXTAREA>
		 </td>
       </tr>
	   <tr>
         <td align=center height=50>
		 <input type='submit' value=' 저장합니다.. '>
		 <input type='button' onclick="javascript:window.self.close();" value='창닫기'>
		 </td>
       </tr>
	   </form>
     </table>

</body>
</html>


<?php 	}

} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>