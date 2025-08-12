<?php
// 현재 파일의 목적
// 홈페이지구축후 관리자 로그인후 페이지의 바로 html 수정을 할수 있게끔 하기위해서 
?>

<?php if($HTTP_COOKIE_VARS['id_login_ok'] && $_COOKIE['id_login_ok']){
if($HTTP_COOKIE_VARS['id_login_ok']){$WebtingMemberLogin_id="$HTTP_COOKIE_VARS['id_login_ok']";}else if($_COOKIE['id_login_ok']){$WebtingMemberLogin_id="$_COOKIE['id_login_ok']";	}else{
echo ("<script language=javascript>
window.alert('정상적으로 로그인 처리되어 있지 않은 상황입니다.\\n\\n문제1) 사용자의 인터넷 쿠키설정을 변경해주십시요.\\n\\n문제2) 웹팅의 DataBase에러일수 있습니다.\\n\\n문제1은 사용자가 해결해주셔야 하며 문제2는 윈도우를 재접속하시면 해결 처리 됩니다.');
history.go(-1);
</script>
");
exit;
}

}

/////////////////////////// 관리자 모드 호출 START //////////////////
include "../db.php";
$AdminChickTYyj= mysqli_query("select * from member where no='1'",$db);
$row_AdminChickTYyj= mysqli_fetch_array($AdminChickTYyj);
$BBSAdminloginKK="$row_AdminChickTYyj['id']";
if(isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK){}else{
  echo ("<script language=javascript>
                          window.alert('현페이지는 관리자만 진입할수 있습니다.');
                          window.self.close();
                        </script>");
                          exit;
}
/////////////////////////// 관리자 모드 호출 END    //////////////////


$FileUrl="$DOCUMENT_ROOT/$PageFlder/${page}"; // 파일의 위치와 파일명
?>


<?php
if($mode=="form"){ // 페이지 수정 form
include $_SERVER['DOCUMENT_ROOT'] ."/admin/title.php";
$CONTENT = join ('', file ("$FileUrl"));
?>

<html>
<head>
<title>MlangPageEdit</title>
<meta http-equiv="Content-type" content="text/html; charset=UTF-8">
<!---------------------------------------------------------
     사이트명: script.ne.kr
     디자인 제작툴-포토샵6.0
     프로그램 제작툴-에디터플러스2
     프로그램언어: PHP3, javascript, DHTML, html
     제작자: Mlang - 메일: webmaster@script.ne.kr
     URL: http://www.script.ne.kr
------------------------------------------------------------>

<style>
body,td,table {color:black; font-size:9pt; FONT-FAMILY:굴림; word-break:break-all;}
</style>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=1050,availHeight=780);
</script>

<script language=javascript>
function MemberCheckField()
{
var f=document.FrmUserInfo;

if (f.connent9.value.length < 3) {
alert("수정할 페이지의 내용을 입력하세요...?");
return false;
}

}

function Mlamg_ahrefview(ahrefview) {

Mlangwindow = window.open("","Mlang_isao_html","width=800,height=600,top=0,left=0,statusbar=yes,resizable=yes,scrollbars=yes,toolbar=no");
Mlangwindow.document.open();
Mlangwindow.document.write("<STYLE>body,td,input,select{color:black; font-size:9pt; FONT-FAMILY:굴림; line-height:130%; word-break:break-all;}</STYLE>");
Mlangwindow.document.write("" + ahrefview + "");
Mlangwindow.document.close();
  
}

</script>


<script src="/admin/js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<form name='FrmUserInfo' method='post' OnSubmit='javascript:return MemberCheckField()' action='<?php echo ("$PHP_SELF");?>'>
<input type='hidden' name='mode' value='formok'>
<input type='hidden' name='page' value='<?php echo $page?>'>
<input type='hidden' name='PageFlder' value='<?php echo $PageFlder?>'>

<p align=center>
<?php
$CONTENT = preg_replace("\\\\", "", $CONTENT);
?>
<textarea name='connent9' rows='42' cols='135'><?php echo $CONTENT?></textarea>
</p>

<p align=center>
<INPUT type="submit" value="수정하기"> <INPUT type="reset" value=" 다시쓰기 ">
<INPUT TYPE='button' VALUE='미리보기' \" + \" onClick='Mlamg_ahrefview(document.forms[0].connent9.value)'>
<input type='button' value='창 닫기' onClick='javascript:window.self.close();'>
</p>

</form>

</body>
</html>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="formok"){

$CONTENTOK = preg_replace("\\\\", "", $connent9);

  if(!$connent9){
  echo ("<script language=javascript>
  window.alert('페이지 수정을 위한 꼭필요한 정보가 없습니다.');
  history.go(-1);
  </script>
    ");
    exit;
  }

	$fp = fopen("$FileUrl", "w");
	fwrite($fp, "$CONTENTOK");
	fclose($fp);

echo ("<script language=javascript>
alert('정상적으로 페이지을 수정하였습니다.')
opener.parent.location.reload();
window.self.close();
</script>
");
exit;

}
?>