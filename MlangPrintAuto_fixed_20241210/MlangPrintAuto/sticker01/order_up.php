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
$log_y=date("Y");                  // ����
$log_md=date("md");            //����
$log_ip="$REMOTE_ADDR";  // ���� ip
$log_time = time();               //  ���� �α�Ÿ��
?><head>
<script> 
   function chk_form(){ 
       var f = document.order_info; 

       if(!f.name.value){ 
           alert('��ȣ/�̸��� �Է����ּ���.'); 
           f.name.focus(); 
           return false; 
       } 
	   
       if(!f.phone1.value){ 
           alert('��ȭ��ȣ�� �Է����ּ���.'); 
           f.phone1.focus(); 
           return false; 
       } 

       if(!f.phone2.value){ 
           alert('��ȭ��ȣ�� �Է����ּ���.'); 
           f.phone2.focus(); 
           return false; 
       } 

       if(!f.phone3.value){ 
           alert('��ȭ��ȣ�� �Է����ּ���.'); 
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
//alert("��� �з��� �����Ͽ��ּ���!!");
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
	font-family: "����";
	font-size: 9pt;
	font-weight: bold;
	color: #06F;
}
</style> 
<img src="img/info.gif" width="150" height="35" />&quot; *&quot;ǥ�ô� �ʼ� �����Դϴ�
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
    <td bgcolor="#E1E1FF"> ��ȣ/�̸� 
      <span class="style1">*</span>
    <td bgcolor="#FFFFFF"> <input type=text name=name size=10> 
  <tr> 
    <td bgcolor="#E1E1FF"> ��й�ȣ
    <td bgcolor="#FFFFFF"> <input type=password name=password size=10>
  <tr> 
    <td bgcolor="#E1E1FF"> ����̸��� 
    <td bgcolor="#FFFFFF"> <input type=text name=email size=30 >
    *<span class="boldB">���ݰ�꼭���� �� �ʿ�</span>�մϴ�.
    <tr> 
    <td bgcolor="#E1E1FF"> ��ȭ��ȣ 
      <span class="style1">*</span>
    <td bgcolor="#FFFFFF"> 
      <input name=phone1 type=text size=3 maxlength="3"> - 
      <input name=phone2 type=text size=4 maxlength="4"> - 
      <input name=phone3 type=text size=4 maxlength="4"> 
  <tr> 
    <td bgcolor="#E1E1FF"> �޴��� 
    <td bgcolor="#FFFFFF"> 
      <input name=hphone1 type=text size=3 maxlength="3"> - 
      <input name=hphone2 type=text size=4 maxlength="4"> - 
      <input name=hphone3 type=text size=4 maxlength="4"> 
  <tr> 
    <td bgcolor="#E1E1FF"> ��ǰ���ɹ�� 
    <td bgcolor="#FFFFFF"><input type=radio name="delivery" value="�ù�" checked="checked">
      �ù� &nbsp; &nbsp;
         <input type=radio name="delivery" value="�湮">
      �湮(�湮�� ��ȭ)   &nbsp; &nbsp;
         <input type=radio name="delivery" value="��">
      ������� &nbsp; &nbsp;
         <input type=radio name="delivery" value="�ٸ���">
	  �ٸ��� 
  <tr> 
    <td bgcolor="#E1E1FF">�ּ�(�ù�)<td bgcolor="#FFFFFF">

<input type="text" id="sample6_postcode" name="sample6_postcode" placeholder="�����ȣ">
<input type="button" onclick="sample6_execDaumPostcode()" value="�����ȣ ã��"><br>
<input type="text" id="sample6_address" name="sample6_address" size=48 placeholder="�ּ�"><br>
<input type="text" id="sample6_detailAddress" name="sample6_detailAddress" placeholder="���ּ�">
<input type="text" id="sample6_extraAddress" name="sample6_extraAddress" placeholder="�����׸�">

<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
    function sample6_execDaumPostcode() {
        new daum.Postcode({
            oncomplete: function(data) {
                // �˾����� �˻���� �׸��� Ŭ�������� ������ �ڵ带 �ۼ��ϴ� �κ�.

                // �� �ּ��� ���� ��Ģ�� ���� �ּҸ� �����Ѵ�.
                // �������� ������ ���� ���� ��쿣 ����('')���� �����Ƿ�, �̸� �����Ͽ� �б� �Ѵ�.
                var addr = ''; // �ּ� ����
                var extraAddr = ''; // �����׸� ����

                //����ڰ� ������ �ּ� Ÿ�Կ� ���� �ش� �ּ� ���� �����´�.
                if (data.userSelectedType === 'R') { // ����ڰ� ���θ� �ּҸ� �������� ���
                    addr = data.roadAddress;
                } else { // ����ڰ� ���� �ּҸ� �������� ���(J)
                    addr = data.jibunAddress;
                }

                // ����ڰ� ������ �ּҰ� ���θ� Ÿ���϶� �����׸��� �����Ѵ�.
                if(data.userSelectedType === 'R'){
                    // ���������� ���� ��� �߰��Ѵ�. (�������� ����)
                    // �������� ��� ������ ���ڰ� "��/��/��"�� ������.
                    if(data.bname !== '' && /[��|��|��]$/g.test(data.bname)){
                        extraAddr += data.bname;
                    }
                    // �ǹ����� �ְ�, ���������� ��� �߰��Ѵ�.
                    if(data.buildingName !== '' && data.apartment === 'Y'){
                        extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                    }
                    // ǥ���� �����׸��� ���� ���, ��ȣ���� �߰��� ���� ���ڿ��� �����.
                    if(extraAddr !== ''){
                        extraAddr = ' (' + extraAddr + ')';
                    }
                    // ���յ� �����׸��� �ش� �ʵ忡 �ִ´�.
                    document.getElementById("sample6_extraAddress").value = extraAddr;
                
                } else {
                    document.getElementById("sample6_extraAddress").value = '';
                }

                // �����ȣ�� �ּ� ������ �ش� �ʵ忡 �ִ´�.
                document.getElementById('sample6_postcode').value = data.zonecode;
                document.getElementById("sample6_address").value = addr;
                // Ŀ���� ���ּ� �ʵ�� �̵��Ѵ�.
                document.getElementById("sample6_detailAddress").focus();
            }
        }).open();
    }
</script>
     
    
    <tr> 
      <td bgcolor="#E1E1FF"> �μ��Ա����� 
      <td bgcolor="#FFFFFF"><input type=radio name="bank" value="����">
        �������� &nbsp; &nbsp;
         <input type=radio name="bank" value="����">
        �������� &nbsp; &nbsp;
         <input type=radio name="bank" value="����">
        �������� &nbsp; &nbsp;
         <input type=radio name="bank" value="ī��">
        ��Ÿ 
        (ī������� ��ȭ)
    <tr> 
    <td bgcolor="#E1E1FF"> �Ա��ڸ� 
    <td bgcolor="#FFFFFF"> <input type=text name=ipkeum size=30>    
  �� �Ա��ڸ��� �ٸ��� ��ȭ���<tr> 
    <td align="right" bgcolor="#E1E1FF"><p>��Ÿ����:<br />
      <br />
      ���ݹ����� ��<br />
      ����ڵ�Ϲ�ȣ:<br />
    ��ȣ:<br />
    ����:<br />
    ����:<br />
    ����:<br />
    ������ּ�:<br />
    ����ڼ���:<br />
    <br />
      </p>
    <td bgcolor="#FFFFFF">
      <p>
        <textarea name=memo cols=40
       rows=10></textarea>
        ���ݰ�꼭����ñ���<br />
      </p>
    <tr> 
    <td bgcolor="#E1E1FF"> �ڷ�ø��� 
    <td bgcolor="#FFFFFF"> <input type=file name=img size=10>
    2M�̻��� ū������ ���ϵ峪 ������ �̿����ּ���
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
<input type='button' onClick="javascript:small_window('FileUp.php?Turi=<?=$log_url?>&Ty=<?=$log_y?>&Tmd=<?=$log_md?>&Tip=<?=$log_ip?>&Ttime=<?=$log_time?>');" value=' ���Ͽø��� ' style="width:80; height:25;"><BR>
<input type='button' onclick="javascript:deleteSelectedItemsFromList(parentList);" value=' �� �� ' style="width:80; height:25;">
		 </td>
       </tr>
     </table>
  <tr>     
    <td  height="50" width="150" align="center"  bgcolor="#E1E1FF"><strong> <a href="http://www.dsp114.com/sub/pri_info.html" target="_blank">���������������̿�</a></strong></td>
    <td width=" " align="left" bgcolor="#FFFFFF"><p><input type=radio name=priv value='1' checked="checked"> 
      �����մϴ�.
          <input type=radio name=priv value='' >
          �ź��մϴ�.(������ �������� ���� �� �̿뿡���� ������ Ȯ���Ͻñ� �ٶ��ϴ�,)</p>
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
