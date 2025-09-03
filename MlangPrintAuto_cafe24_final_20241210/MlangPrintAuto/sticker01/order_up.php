<? 
   session_start(); 
   $session_id = session_id(); 
$HomeDir="../../";
include"../MlangPrintAutoTop.php";
include "../../lib/func.php"; 
$connect = dbconn(); 
 
$PageCode="PrintAuto";
$MultyUploadDir="../../PHPClass/MultyUpload";

//include"$HomeDir/db.php";
if(!$page){$page="sticker";}

$Ttable="$page";

include"../ConDb.php";
//include"inc.php";

$log_url=eregi_replace("/", "_", $PHP_SELF);
$log_y=date("Y");                  // 연도
$log_md=date("md");            //월일
$log_ip="$REMOTE_ADDR";  // 접속 ip
$log_time = time();               //  접속 로그타임
?><head>
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
<script language="JavaScript">

function small_window(myurl) {
var newWindow;
var props = 'scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=400,height=200';
newWindow = window.open("<?=$MultyUploadDir?>/"+myurl+"&Mode=tt", "Add_from_Src_to_Dest", props);
}


function addToParentList(sourceList) {
destinationList = window.document.forms[0].parentList;
for(var count = destinationList.options.length - 1; count >= 0; count--) {
destinationList.options[count] = null;
}
for(var i = 0; i < sourceList.options.length; i++) {
if (sourceList.options[i] != null)
destinationList.options[i] = new Option(sourceList.options[i].text, sourceList.options[i].value );
   }
}


function selectList(sourceList) {
sourceList = window.document.forms[0].parentList;
for(var i = 0; i < sourceList.options.length; i++) {
if (sourceList.options[i] != null)
sourceList.options[i].selected = true;
}
return true;
}


function deleteSelectedItemsFromList(sourceList) {
var maxCnt = sourceList.options.length;
for(var i = maxCnt - 1; i >= 0; i--) { 

if ((sourceList.options[i] != null) && (sourceList.options[i].selected == true)) {
window.open('<?=$MultyUploadDir?>/FileDelete.php?FileDelete=ok&Turi=<?=$log_url?>&Ty=<?=$log_y?>&Tmd=<?=$log_md?>&Tip=<?=$log_ip?>&Ttime=<?=$log_time?>&FileName='+sourceList.options[i].text,'','scrollbars=no,resizable=no,width=100,height=100,top=2000,left=2000');
sourceList.options[i] = null;
      }
   }


}

function FormCheckField()
{
var f=document.choiceForm;

//if (f.myList.value == "#" || f.myList.value == "==============") {
//alert("장비 분류을 선택하여주세요!!");
//f.RadOne.focus();
//return false;
//}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

var winopts = "width=780,height=590,toolbar=no,location=no,directories=no,status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes";
var popup = window.open('','MlangMulty<?=$log_y?><?=$log_md?><?=$log_time?>', winopts);
popup.focus();
}

function MlangWinExit() {
if(document.choiceForm.OnunloadChick.value == "on") {
window.open("<?=$MultyUploadDir?>/FileDelete.php?DirDelete=ok&Turi=<?=$log_url?>&Ty=<?=$log_y?>&Tmd=<?=$log_md?>&Tip=<?=$log_ip?>&Ttime=<?=$log_time?>","MlangWinExitsdf","width=100,height=100,top=2000,left=2000,toolbar=no,location=no,directories=no,status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes");
}
}
window.onunload = MlangWinExit;

</script>
</head>

						
<style type="text/css">
.boldB {
	font-family: "돋움";
	font-size: 9pt;
	font-weight: bold;
	color: #06F;
}
</style> 
<img src="img/info.gif" width="150" height="35" />&quot; *&quot;표시는 필수 사항입니다
<form action=./order_post.php method=post enctype=multipart/form-data name=order_info onsubmit="return chk_form();">
<input type="hidden" name="OnunloadChick" value="on">
<INPUT TYPE="hidden" name='Turi' value='<?=$log_url?>'>
<INPUT TYPE="hidden" name='Ty' value='<?=$log_y?>'>
<INPUT TYPE="hidden" name='Tmd' value='<?=$log_md?>'>
<INPUT TYPE="hidden" name='Tip' value='<?=$log_ip?>'>
<INPUT TYPE="hidden" name='Ttime' value='<?=$log_time?>'>
<input type="hidden" name="ImgFolder" value="<?=$log_url?>/<?=$log_y?>/<?=$log_md?>/<?=$log_ip?>/<?=$log_time?>">   

										<input type='hidden' name=OrderSytle value='<?=$View_TtableC?>'>   
                                        <input type='hidden' name=StyleForm>                              
                                        <input type='hidden' name=SectionForm>       
                                        <input type='hidden' name=QuantityForm>    
                                        <input type='hidden' name=DesignForm>
										<input type='hidden' name=PriceForm>
										<input type='hidden' name=DS_PriceForm>
									    <input type='hidden' name=Order_PriceForm>
									    <input type='hidden' name=VAT_PriceForm>
									    <input type='hidden' name=Total_PriceForm>
										<input type='hidden' name='page' value='<?=$page?>'>
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
    <td bgcolor="#E1E1FF">주소(택배)<td bgcolor="#FFFFFF">

<input type="text" id="sample6_postcode" name="sample6_postcode" placeholder="우편번호">
<input type="button" onclick="sample6_execDaumPostcode()" value="우편번호 찾기"><br>
<input type="text" id="sample6_address" name="sample6_address" size=48 placeholder="주소"><br>
<input type="text" id="sample6_detailAddress" name="sample6_detailAddress" placeholder="상세주소">
<input type="text" id="sample6_extraAddress" name="sample6_extraAddress" placeholder="참고항목">

<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
    function sample6_execDaumPostcode() {
        new daum.Postcode({
            oncomplete: function(data) {
                // 팝업에서 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.

                // 각 주소의 노출 규칙에 따라 주소를 조합한다.
                // 내려오는 변수가 값이 없는 경우엔 공백('')값을 가지므로, 이를 참고하여 분기 한다.
                var addr = ''; // 주소 변수
                var extraAddr = ''; // 참고항목 변수

                //사용자가 선택한 주소 타입에 따라 해당 주소 값을 가져온다.
                if (data.userSelectedType === 'R') { // 사용자가 도로명 주소를 선택했을 경우
                    addr = data.roadAddress;
                } else { // 사용자가 지번 주소를 선택했을 경우(J)
                    addr = data.jibunAddress;
                }

                // 사용자가 선택한 주소가 도로명 타입일때 참고항목을 조합한다.
                if(data.userSelectedType === 'R'){
                    // 법정동명이 있을 경우 추가한다. (법정리는 제외)
                    // 법정동의 경우 마지막 문자가 "동/로/가"로 끝난다.
                    if(data.bname !== '' && /[동|로|가]$/g.test(data.bname)){
                        extraAddr += data.bname;
                    }
                    // 건물명이 있고, 공동주택일 경우 추가한다.
                    if(data.buildingName !== '' && data.apartment === 'Y'){
                        extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                    }
                    // 표시할 참고항목이 있을 경우, 괄호까지 추가한 최종 문자열을 만든다.
                    if(extraAddr !== ''){
                        extraAddr = ' (' + extraAddr + ')';
                    }
                    // 조합된 참고항목을 해당 필드에 넣는다.
                    document.getElementById("sample6_extraAddress").value = extraAddr;
                
                } else {
                    document.getElementById("sample6_extraAddress").value = '';
                }

                // 우편번호와 주소 정보를 해당 필드에 넣는다.
                document.getElementById('sample6_postcode').value = data.zonecode;
                document.getElementById("sample6_address").value = addr;
                // 커서를 상세주소 필드로 이동한다.
                document.getElementById("sample6_detailAddress").focus();
            }
        }).open();
    }
</script>
     
    
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
    <td bgcolor="#FFFFFF"> <input type=file name=img size=10>
    2M이상의 큰파일은 웹하드나 메일을 이용해주세요
		<table border=0 align=center width=300 cellpadding=2 cellspacing=0>
	   <tr>
          <td colspan=2><img src="/images/sub3_img_10.gif" width="262" height="24"></td>
		</tr>
       <tr>
         <td width=100%>
		    <select size='3' style="width:245; font-size:10pt; color:#336666; font:bold;" name='parentList' multiple>
		    </select>
		  </td>
		 <td width=30%>
<input type='button' onClick="javascript:small_window('FileUp.php?Turi=<?=$log_url?>&Ty=<?=$log_y?>&Tmd=<?=$log_md?>&Tip=<?=$log_ip?>&Ttime=<?=$log_time?>');" value=' 파일올리기 ' style="width:80; height:25;"><BR>
<input type='button' onclick="javascript:deleteSelectedItemsFromList(parentList);" value=' 삭 제 ' style="width:80; height:25;">
		 </td>
       </tr>
     </table>
  <tr>     
    <td  height="50" width="150" align="center"  bgcolor="#E1E1FF"><strong> <a href="http://www.dsp114.com/sub/pri_info.html" target="_blank">개인정보수집및이용</a></strong></td>
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
<?
include"../DhtmlText.php";
?>
<?
include"../MlangPrintAutoDown.php";
?> 
