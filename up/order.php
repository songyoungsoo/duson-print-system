<? 
   session_start(); 
   $session_id = session_id(); 
$HomeDir="../../";
include "../MlangPrintAuto/MlangPrintAutoTop.php";
include "../lib/func.php"; 
$connect = dbconn(); 
?> 
<script> 
   function chk_form(){ 
       var f = document.order_info; 

       if(!f.name.value){ 
           alert('상호/이름을 입력해주세요.'); 
           f.name.focus(); 
           return false; 
       } 
	   
       if(!f.phone1.value){ 
           alert('전화번호를 입력해주세요.'); 
           f.phone1.focus(); 
           return false; 
       } 

       if(!f.phone2.value){ 
           alert('전화번호를 입력해주세요.'); 
           f.phone2.focus(); 
           return false; 
       } 

       if(!f.phone3.value){ 
           alert('전화번호를 입력해주세요.'); 
           f.phone3.focus(); 
           return false; 
       } 	   

	   	 
   return true; 
   } 

</script>
<style type="text/css">
<!--
.boldB {
	font-family: "돋움";
	font-size: 9pt;
	font-weight: bold;
	color: #06F;
}
-->
</style> 
<class="style1"><img src="img/info.gif" width="150" height="35" />&quot; *&quot;표시는 필수 사항입니다
<form action=./upload.php method=post enctype=multipart/form-data name=order_info onsubmit="return chk_form();" target="test">
<table width=100% border="0" cellspacing="1" cellpadding="3" bgcolor="#c5c5c5" > 
  <tr> 
    <td bgcolor="#E1E1FF"> 상호/이름 
      <span class="style1">*</span>
    <td bgcolor="#FFFFFF"> <input type=text name=name size=10> 
  <tr> 
    <td bgcolor="#E1E1FF"> 비밀번호
    <td bgcolor="#FFFFFF"> <input type=password name=password size=10>
  <tr> 
    <td bgcolor="#E1E1FF"> 담당이메일 
    <td bgcolor="#FFFFFF"> <input type=text name=email size=30 >
    *<span class="boldB">세금계산서발행 시 필요</span>합니다.
    <tr> 
    <td bgcolor="#E1E1FF"> 전화번호 
      <span class="style1">*</span>
    <td bgcolor="#FFFFFF"> 
      <input name=phone1 type=text size=3 maxlength="3"> - 
      <input name=phone2 type=text size=4 maxlength="4"> - 
      <input name=phone3 type=text size=4 maxlength="4"> 
  <tr> 
    <td bgcolor="#E1E1FF"> 휴대폰 
    <td bgcolor="#FFFFFF"> 
      <input name=hphone1 type=text size=3 maxlength="3"> - 
      <input name=hphone2 type=text size=4 maxlength="4"> - 
      <input name=hphone3 type=text size=4 maxlength="4"> 
  <tr> 
    <td bgcolor="#E1E1FF"> 물품수령방법 
    <td bgcolor="#FFFFFF"><input type=radio name="delivery" value="택배" checked="checked">
      택배 &nbsp; &nbsp;
         <input type=radio name="delivery" value="방문">
      방문(방문시 전화)   &nbsp; &nbsp;
         <input type=radio name="delivery" value="퀵">
      오토바이 &nbsp; &nbsp;
         <input type=radio name="delivery" value="다마스">
	  다마스 
  <tr> 
    <td bgcolor="#E1E1FF"> 우편번호 
    <td bgcolor="#FFFFFF"> <input type=text name=zip1 size=3> - 
          <input type=text name=zip2 size=3>
    <tr> 
    <td bgcolor="#E1E1FF"> 주소 
    <td bgcolor="#FFFFFF"> <input type=text name=address1 size=50><tr> 
    <td bgcolor="#E1E1FF"> 상세주소 
    <td bgcolor="#FFFFFF"> <input type=text name=address2 size=50>
       
       ※ 정확히기재
    <tr> 
    <td bgcolor="#E1E1FF"> 두손입금은행 
    <td bgcolor="#FFFFFF"><input type=radio name="bank" value="국민">
      국민은행 &nbsp; &nbsp;
         <input type=radio name="bank" value="신한">
      신한은행 &nbsp; &nbsp;
         <input type=radio name="bank" value="농협">
      농협은행 &nbsp; &nbsp;
         <input type=radio name="bank" value="카드">
	  기타 
  (카드결제시 전화)
  <tr> 
    <td bgcolor="#E1E1FF"> 입금자명 
    <td bgcolor="#FFFFFF"> <input type=text name=ipkeum size=30>    
  ※ 입금자명이 다르면 전화요망<tr> 
    <td align="right" bgcolor="#E1E1FF"><p>기타사항:<br />
      <br />
      세금발행할 때<br />
      사업자등록번호:<br />
    상호:<br />
    성명:<br />
    업태:<br />
    종목:<br />
    사업장주소:<br />
    담당자성명:<br />
    <br />
      </p>
    <td bgcolor="#FFFFFF">
      <p>
        <textarea name=memo cols=40
       rows=10></textarea>
        세금계산서발행시기입<br />
      </p>
    <tr> 
    <td bgcolor="#E1E1FF"> 자료올리기 
    <td bgcolor="#FFFFFF"> 
		<input type="file" name="file[]" multiple="multiple" onchange="this.form.submit()">
<input type="hidden" name="nickname" value="<?php echo  $_SESSION['nickname'] ?>">
<input type="hidden" name="time" value="<?php echo  $_GET['starttime'] ?>">
</form>
<iframe name="test"></iframe>
    2M이상의 큰파일은 웹하드나 메일을 이용해주세요
  <tr>     
    <td  height="50" width="150" align="center"  bgcolor="#E1E1FF"><strong> <a href="http://localhost/sub/pri_info.php" target="_blank">개인정보수집및이용</a></strong></td>
    <td width=" " align="left" bgcolor="#FFFFFF"><p><input type=radio name=priv value='1' checked="checked"> 
      동의합니다.
          <input type=radio name=priv value='' >
          거부합니다.(좌측의 개인정보 수집 및 이용에관한 내용을 확인하시기 바랍니다,)</p>
    </td>
      </tr>  
</table>
 <div align="center"><input name="submit" type="image" value= img src="img/order.gif" width="99" height="31" border="0" >
</div>     
</form> 
<?php
include "../MlangPrintAuto/DhtmlText.php";
?>
<?php
include "../MlangPrintAuto/MlangPrintAutoDown.php";
?> 
