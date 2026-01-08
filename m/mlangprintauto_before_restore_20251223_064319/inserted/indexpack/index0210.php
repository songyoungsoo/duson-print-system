<?
$HomeDir="../../";
$PageCode="PrintAuto";
$MultyUploadDir="../../PHPClass/MultyUpload";

include"$HomeDir/db.php";
if(!$page){$page="inserted";}
include"../mlangprintautotop.php";

$Ttable="$page";
include"../ConDb.php";
include"inc.php";

$log_url=eregi_replace("/", "_", $PHP_SELF);
$log_y=date("Y");                  // ����
$log_md=date("md");            //����
$log_ip="$REMOTE_ADDR";  // ���� ip
$log_time = time();               //  ���� �α�Ÿ��
?><head>
<script language="JavaScript" type="text/JavaScript">
function MM_reloadPage(init) { 
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

function MM_findObj(n, d) {
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_showHideLayers() { 
  var i,p,v,obj,args=MM_showHideLayers.arguments;
  for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
    if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
    obj.visibility=v; }
}
</script>

<STYLE>
.input {font-size:10pt; background-color:#FFFFFF; color:#336699; line-height:130%;}
.inputOk {font-size:10pt; background-color:#FFFFFF; color:#429EB2; border-style:solid; height:22px; border:0; solid #FFFFFF; font:bold;}
.Td1{font-size:9pt; background-color:#EBEBEB; color:#336699;}
.Td2{font-size:9pt; color:#232323;}
.style3 {color: #33CCFF}
</STYLE>

</head>

<script>

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

							function CheckTotal(mode){
								var f=document.choiceForm;

								 if (f.StyleForm.value == "") {
                                        alert("�ֹ�/�������� �� ���� �ϱ� ���Ͽ� ������ �ֽ��ϴ�.\n\n�ٽ� ���� ���� �ֽʽÿ�...!!\n\n(<?=$admin_name?>)");
                                            return false;
                                     }

								if (f.SectionForm.value == "") {
                                        alert("�ֹ�/�������� �� ���� �ϱ� ���Ͽ� ������ �ֽ��ϴ�.\n\n�ٽ� ���� ���� �ֽʽÿ�...!!");
                                            return false;
                                     }

								if (f.Order_PriceForm.value == "") {
                                        alert("�ֹ�/�������� �� ���� �ϱ� ���Ͽ� ������ �ֽ��ϴ�.\n\n�ٽ� ���� ���� �ֽʽÿ�...!!");
                                            return false;
                                     }

							   if (f.Total_PriceForm.value == "") {
                                        alert("�ֹ�/�������� �� ���� �ϱ� ���Ͽ� ������ �ֽ��ϴ�.\n\n�ٽ� ���� ���� �ֽʽÿ�...!!");
                                            return false;
                                     }
								
                                  f.action="/mlangorder_printauto/OnlineOrder.php?SubmitMode="+mode;
                                  f.submit(); 
							}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

							function calc(){
						       asd=document.forms["choiceForm"];
cal.document.location.href='price_cal.php?MY_type='+asd.MY_type.value+'&PN_type='+asd.PN_type.value+'&MY_Fsd='+asd.MY_Fsd.value+'&MY_amount='+asd.MY_amount.value+'&ordertype='+asd.ordertype.value+'&POtype='+asd.POtype.value;
							} // END function

							function calc_ok()
								{
							asd=document.forms["choiceForm"];				
                              cal.document.location.href='price_cal.php?MY_type='+asd.MY_type.value+'&PN_type='+asd.PN_type.value+'&MY_Fsd='+asd.MY_Fsd.value+'&MY_amount='+asd.MY_amount.value+'&ordertype='+asd.ordertype.value+'&POtype='+asd.POtype.value;
							} // END function
						</script>

						<head><script type="text/javascript"><?include"DbZip.php"?></script></head>
						

<iframe name=Tcal frameborder=0 width=0 height=0></iframe>
<iframe name=cal frameborder=0 width=0 height=0></iframe>
<!----------------- �ڽ� ���� -------------------->
<table width="692"   bgcolor="#CCCCCC" border="0" bordercolor="#CCCCCC" align="center" cellpadding="10" cellspacing="1">
<tr>
<td bgcolor="#FFFFFF">
<table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0">

 <form name='choiceForm' method='post'>

  <tr>
    <td height="5" align="center"> </td>
  </tr>
  <tr>
    <td align="center" valign="top">
	<!------------------------------------------select�޴�----------------------------------------->
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td width="40%" align="left" valign="top"><table width="103%"  border="0" align="center" cellpadding="1" cellspacing="1">
          <tr onMouseOver="MM_showHideLayers('print01','','show','print02','','hide','print03','','hide','print04','','hide','print05','','hide')">
            <td align="left" class='LeftText'><span class="style3">�� </span>�μ����</td>
<td bgcolor="#FFFFFF">
  								  <select class="input" name='MY_type' onchange='change_Field(this.value)'>
<?
include"../../db.php";
$Cate_result= mysql_query("select * from $GGTABLE where Ttable='$page' and BigNo='0' order by no asc",$db);
$Cate_rows=mysql_num_rows($Cate_result);
if($Cate_rows){

while($Cate_row= mysql_fetch_array($Cate_result)) {
?>

<option value='<?=$Cate_row[no]?>'><?=$Cate_row[title]?></option>

<?
}   } 
?>
                                    </select>            </td>
          </tr>
      
<?
$result_CV= mysql_query("select * from $GGTABLE where Ttable='$page' and BigNo='0' order by no asc limit 0, 1",$db);
$row_CV= mysql_fetch_array($result_CV);
$CV_no="$row_CV[no]";
$CV_Ttable="$row_CV[Ttable]";  
$CV_BigNo="$row_CV[BigNo]";  
$CV_title="$row_CV[title]";  
$CV_TreeNo="$row_CV[TreeNo]"; 
?>

          <tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','hide','print03','','show','print04','','hide','print05','','hide')">
            <td align="left" class='LeftText'><span class="style3">�� </span>��������</td>
<td bgcolor="#FFFFFF">
                                        <select name="MY_Fsd" onChange="calc_ok();">
<? 
$result_CV_One= mysql_query("select * from $GGTABLE where TreeNo='$CV_no' order by no asc",$db);
$rows_CV_One=mysql_num_rows($result_CV_One);
if($rows_CV_One){
while($row_CV_One= mysql_fetch_array($result_CV_One)) { 
echo("<option value='$row_CV_One[no]'>$row_CV_One[title]</option>");
}  }
?>
                                    </select>            </td>
          </tr>
<tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','show','print03','','hide','print04','','hide','print05','','hide')">
            <td align="left" class='LeftText'><span class="style3">�� </span>���̱԰�</td>
            <td bgcolor="#FFFFFF"><select name="PN_type" onchange="calc_ok();">
              <? 
$result_CV_Two= mysql_query("select * from $GGTABLE where BigNo='$CV_no' order by no asc",$db);
$rows_CV_Two=mysql_num_rows($result_CV_Two);
if($rows_CV_Two){
while($row_CV_Two= mysql_fetch_array($result_CV_Two)) { 
echo("<option value='$row_CV_Two[no]'>$row_CV_Two[title]</option>");
}  }
?>
            </select></td>
          </tr>

          
<tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','show','print03','','hide','print04','','hide','print05','','hide')">
            <td align="left" class='LeftText'><span class="style3">�� </span>�μ��</td>
            <td bgcolor="#FFFFFF">
 								  <select name="POtype" onChange="calc_ok();">
								  <option value='1'>�ܸ�</option>
								  <option value='2'>���</option>
								  </select>

<!----------------  �������� ��ư �� ���� ------------>
<a href="#" onClick="javascript:window.open('/mlangprintauto/WinDowInfo.php?img=A_1_INFO.jpg&title=��������', 'Ejpruuu','width=502,height=725,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><img src='/mlangprintauto/img/A_1.gif' border=0 align=absmiddle></a>
<!---------------  �������� ��ư �� ���� ------------>            </td>
          </tr>

          <tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','hide','print03','','hide','print04','','show','print05','','hide')">
            <td align="left" class='LeftText'><span class="style3">�� </span>����</td>
            <td bgcolor="#FFFFFF"><select name="MY_amount" onchange="calc_ok();">
            <option value='0.5'>0.5��</option>
              <option value='1'>1��</option>
              <option value='2'>2��</option>
              <option value='3'>3��</option>
              <option value='4'>4��</option>
              <option value='5'>5��</option>
              <option value='6'>6��</option>
              <option value='7'>7��</option>
              <option value='8'>8��</option>
              <option value='9'>9��</option>
              <option value='10'>10��</option>
            </select>
              <INPUT TYPE="text" NAME="MY_amountRight" value='4000��' style='font-size:9pt; background-color:#FFFFFF; color:#000000; border-style:solid; height:16; width:50; border:0 solid #FFFFFF'>            </td>

<?
mysql_close($db); 
?>
          </tr>
          <tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','hide','print03','','hide','print04','','hide','print05','','show')">
            <td align="left" class='LeftText'><span class="style3">��</span> ����������</td>
            <td bgcolor="#FFFFFF"><select name=ordertype onchange="calc_ok();">
              <option value='total'>������+�μ�</option>
              <option value='print'>�μ⸸ �Ƿ�</option>
            </select></td>
          </tr>
        </table>          
        <!------------------------------------------select�޴���-----------------------------------------></td>
        <td width="60%" align="left" valign="top"><table width="400"  border="0" cellpadding="3" cellspacing="0">
          <tr>
            <td width="8%" align="left" valign="top">&nbsp;</td>
            <td width="92%" align="left" valign="top">
			  <p>���� �׸��� ���� �Ͻø� �����Բ��� ���ϴ� �������<BR>
			    �ڵ����� �ݾ��� ���Ǽ� �ֽ��ϴ�.<BR>
			    <BR>
			    <b>�ٷ� �ֹ��� �Ͻ÷��� �ֹ��ϱ⸦ Ŭ���ϼ���.</b></p>
			  <p>100At A4����� 16�������� 0.5���� �Ⱓ�� 2~3�� �ҿ�˴ϴ�.<BR>
			    <BR>
			    �μձ�ȹ-��������: 02-2632-1830
			    </p></td>
          </tr>
        </table>
		<!-----------------------------------------��ǰ��������------------------------------------------------>
		<!-----------------------------------------��ǰ��������------------------------------------------------></td>
      </tr>
    </table></td>
  </tr>
    <tr>
    <td height="5" align="center"> </td>
  </tr>
  <tr>
        <td height="1" colspan="3" background="/images/dot_2.gif"></td>
      </tr>
  <tr>
    <td height="5" align="center"> </td>
  </tr>
  <tr>
    <td><table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#e4e4e4">
      <tr>
        <td width="305" align="left" valign="top" bgcolor="#FFFFFF"><table width="100%"  border="0" cellspacing="0" cellpadding="3">
          <tr>
            <td width="172" align="center"><a href=javascript:calc();><img src="/images/estimate.gif" width="99" height="31" border=0></a></td>
          </tr>
		            <tr>
            <td height="5" align="center"> </td>
          </tr>
										

<!--form2 start-->
<head>
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

          <tr>
            <td align="center">
		       <!------   ����� �����ֱ� ���� -------------->
                                       <table border="0" cellspacing="1" cellpadding="2" align=center width=100%>  
									   
                                        <tr> 
                                          <td class='MlangAutoTd44'><span class="style3">�� </span>�μ��</td>
										     <td class='MlangAutoTd44'>
                                            <input type="text" size="10" name='Price' readonly style='height:18; font:bold; text-align:center;'>��
											</td>
                                        </tr>
                                        <tr> 
                                        <td class='MlangAutoTd44'><span class="style3">�� </span>������</td>
										     <td class='MlangAutoTd44'>
                                            <input type="text" size="10" name='DS_Price' readonly style='height:18; font:bold; text-align:center;'>��
											</td>
                                        </tr>
                                        <tr>
                                            <td class='MlangAutoTd44'><span class="style3">�� </span>�ݾ�</td>
										     <td class='MlangAutoTd44'>
                                            <input type="text" size="10" name='Order_Price' readonly style='height:18; font:bold; text-align:center;'>��
											</td>
                                        </tr>
                                       <!------ <tr> 
                                          <td>* �ΰ���
                                            <input type="text" class="inputOk" size="16"  name='VAT_Price' readonly value=''>��
											</td>
                                        </tr>
                                        <tr> 
                                          <td>* <font color="#FF0000"><b>�Ѿ�</b></font>
                                            <input name="Total_Price" type="text" class="inputOk" size="16"  readonly value=''>��
											</td>
                                        </tr>----->
						               </table>
              <!------   ����� �����ֱ� ���� -------------->
             </td>
          </tr>
          <tr>
            <td align="center" class="radi">
             ���ݺ���. ��ۺ�� �����Դϴ�.</td>
          </tr>
          <tr>
            <td align="center" class="radicolor">
<?
$Ttable="$page";
include"../ConDb.php";
include"../../admin/mlangprintauto/int/info.php";

$View_temp = "View_ContText_".$View_TtableA ; 
$CONTENT_OK=$$View_temp;

include"../../mlangorder_printauto/OrderDownText.php";
?>
			</td>
          </tr>
        </table>          
		<!-----------------------------------------�ֹ��ݾ׺�����------------------------------------------------>
      
        </td>
        
        <td width="458" align="left" valign="top" bgcolor="#FFFFFF">
          <!-----------------------------------------����÷���� ���� ------------------------------------------------>
     <!------------------------- ���� �ø��� -------------------------------->

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
	 <!------------------------- ���� �ø��� -------------------------------->
            
		 <table width="300"  border="0" align="center" cellpadding="0" cellspacing="0">
		   <tr>
              <td height="5" colspan="2"> </td>
            </tr>
            <tr>
			<tr>
              <td colspan="2"><img src="/images/sub3_img_13.gif" width="93" height="21"></td>
            </tr>
            <tr>
              <td height="2" colspan="2" align="center" background="<?=$SoftUrl?>images/dot.gif"> </td>
            </tr>
			<tr>
              <td colspan="2" align="center"><textarea name="textarea" cols="47" rows="5"></textarea></td>
            </tr>
            <tr>
              <td height="5" colspan="2" align="center"> </td>
            </tr>
		 </table>

          <!-----------------------------------------����÷���� ��------------------------------------------------>
        </td>
      </tr>
    </table></td>
  </tr>
    
  <tr>
        <td height="1" colspan="3" background="/images/dot_2.gif"></td>
      </tr>
  
  <tr>
    <td align="center">
	<input type="image" onClick="javascript:return CheckTotal('OrderOne');" src="/images/sub3_img_17.gif" width="99" height="31">
<!----------
<input type="image" onClick="javascript:return CheckTotal('OrderTwo');" src="/images/sub3_img_19.gif" width="99" height="31">
----------------->
</td>
  </tr>
</form>
</table>
</td>
  </tr>
</table>
<!----------------- �ڽ� ���� -------------------->

<?
$PrintTextBox_left=270+$DhtmlLeftFos;
$PrintTextBox_top="$DhtmlTopFos";;
$PrintTextBox_width="380";
$PrintTextBox_height="100";
?>

<?include"../DhtmlText.php";?>


<?
include"../MlangPrintAutoDown.php";
?>