<?php
$HomeDir = "../../";
$PageCode = "PrintAuto";
$MultyUploadDir = "../../PHPClass/MultyUpload";

include "$HomeDir/db.php";
if (!isset($page)) {
    $page = "msticker";
}
include "../mlangprintautotop.php";

$Ttable = $page;
include "../ConDb.php";
include "inc.php";

$log_url = str_replace("/", "_", $_SERVER["PHP_SELF"]);
$log_y = date("Y");                  // 연도
$log_md = date("md");            //월일
$log_ip = $_SERVER["REMOTE_ADDR"];  // 접속 ip
$log_time = time();               //  접속 로그타임
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
</STYLE>

</head>

<script>
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

							function CheckTotal(mode){
								var f=document.choiceForm;

								 if (f.StyleForm.value == "") {
                                        alert("상품 종류를 선택해주세요.\n\n왼쪽 옵션에서 원하시는 상품 종류를 선택해주세요.");
                                            return false;
                                     }

								if (f.SectionForm.value == "") {
                                        alert("상품 옵션을 선택해주세요.\n\n재질, 사이즈 등 필요한 옵션을 선택해주세요.");
                                            return false;
                                     }

								if (f.Order_PriceForm.value == "") {
                                        alert("가격 계산이 완료되지 않았습니다.\n\n모든 옵션 선택 후 가격이 표시되면 주문해주세요.");
                                            return false;
                                     }

							   if (f.Total_PriceForm.value == "") {
                                        alert("총 금액이 계산되지 않았습니다.\n\n옵션을 다시 선택하시거나 페이지를 새로고침해주세요.");
                                            return false;
                                     }
								
                                  f.action="/mlangorder_printauto/OnlineOrder.php?SubmitMode="+mode;
                                  f.submit(); 
							}


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

							function calc(){
						       asd=document.forms["choiceForm"];
cal.document.location.href='price_cal.php?MY_type='+asd.MY_type.value+'&PN_type='+asd.PN_type.value+'&MY_amount='+asd.MY_amount.value+'&ordertype='+asd.ordertype.value;
							} // END function

							function calc_ok()
								{
							asd=document.forms["choiceForm"];				
                              cal.document.location.href='price_cal.php?MY_type='+asd.MY_type.value+'&PN_type='+asd.PN_type.value+'&MY_amount='+asd.MY_amount.value+'&ordertype='+asd.ordertype.value;
							} // END function
						</script>

						<head><script type="text/javascript"><?include"DbZip.php"?></script></head>
						



<iframe name=Tcal frameborder=0 width=0 height=0></iframe>
<iframe name=cal frameborder=0 width=0 height=0></iframe>
<!----------------- 박스 시작 -------------------->
<table width="692"   bgcolor="#CCCCCC" border="0" bordercolor="#CCCCCC" align="center" cellpadding="10" cellspacing="1">
<tr>
<td bgcolor="#FFFFFF">
<table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0">

 <form name='choiceForm' method='post'>

  <tr><!--1-->
    <td height="5" align="center"> </td>
  </tr>
  <tr><!--2-->
    <td align="center" valign="top">
	<!------------------------------------------select메누----------------------------------------->
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td width="40%" align="left" valign="top"><table width="100%"  border="0" align="center" cellpadding="1" cellspacing="1">
          <tr onMouseOver="MM_showHideLayers('print01','','show','print02','','hide','print03','','hide','print04','','hide','print05','','hide')">
            <td align="left" class='LeftText'><li><B>종류</B></td>
            <td bgcolor="#FFFFFF"><select class="input" name='MY_type' onchange='change_Field(this.value)'>
            <?php
include "../../db.php";
$Cate_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no DESC");
$Cate_rows = mysqli_num_rows($Cate_result);
if ($Cate_rows) {
    while ($Cate_row = mysqli_fetch_array($Cate_result)) {
?>
        <option value='<?= $Cate_row['no'] ?>' selected="selected">
            <?= $Cate_row['title'] ?>
        </option>
<?php
    }
}
?>
</select>
</td>
</tr>

<?php
$result_CV = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC LIMIT 0, 1");
$row_CV = mysqli_fetch_array($result_CV);
$CV_no = $row_CV['no'];
$CV_Ttable = $row_CV['Ttable'];
$CV_BigNo = $row_CV['BigNo'];
$CV_title = $row_CV['title'];
$CV_TreeNo = $row_CV['TreeNo'];
?>

<tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','show','print03','','hide','print04','','hide','print05','','hide')">
    <td align="left" class='LeftText'><li><B>규격</B></td>
    <td bgcolor="#FFFFFF"><select name="PN_type" onchange="calc_re();">
            <?php
            $result_CV_Two = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE BigNo='$CV_no' ORDER BY no ASC");
            $rows_CV_Two = mysqli_num_rows($result_CV_Two);
            if ($rows_CV_Two) {
                while ($row_CV_Two = mysqli_fetch_array($result_CV_Two)) {
                    echo("<option value='" . $row_CV_Two['no'] . "'>" . $row_CV_Two['title'] . "</option>");
                }
            }
            ?>
        </select>
    </td>
</tr>


          <tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','hide','print03','','show','print04','','hide','print05','','hide')">
            <td align="left" class='LeftText'><li><B>수량</B></td>
            <td bgcolor="#FFFFFF"><select name="MY_amount" onchange="calc_ok();">
              <option value='1000'>1000매</option>
              <option value='2000'>2000매</option>
              <option value='3000'>3000매</option>
              <option value='4000'>4000매</option>
              <option value='5000'>5000매</option>
              <option value='6000'>6000매</option>
              <option value='7000'>7000매</option>
              <option value='8000'>8000매</option>
              <option value='9000'>9000매</option>
              <option value='10000'>10000매</option>
            </select></td>

<?php
mysqli_close($db); 
?>
          </tr>
          <tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','hide','print03','','hide','print04','','show','print05','','hide')">
            <td align="left" class='LeftText'><li><B>편집비</B></td>
            <td bgcolor="#FFFFFF">
								  <select name=ordertype onChange="calc_ok();">
										<option value='total'>디자인+인쇄</option>
										<option value='print'>인쇄만 의뢰</option>
										</select>            </td>
          </tr>
        </table>          
        <!------------------------------------------select메뉴끝-----------------------------------------></td>
        <td width="60%" align="left" valign="top">
            <table width="100%"  border="0" cellpadding="3" cellspacing="0">
             <tr>
              <td width="7%" align="left" valign="top">&nbsp;</td>
              <td width="93%" align="left" valign="top">
             <!-----------------------------------------분류별 설명공간 -레이어 ------------------------------------------------>
             <div style="position:relative; left:0px; top:0px;">           
<?php
$PrintTextBox_left="0";
$PrintTextBox_top="-18";
$PrintTextBox_width="380";
$PrintTextBox_height="120";
?>
<?php
include"../DhtmlText.php";
?>
              </div>
<!-----------------------------------------분류별 설명공간 -레이어------------------------------------------------>                  
			옆의 항목을 선택 하시면 고객님께서 원하는 방식으로<BR>
			견적안내 금액을 보실수 있습니다.<BR><BR>
			<b>바로 주문을 하시려면 주문하기를 클릭하세요.</b><BR><BR>
			두손기획-고객센터: 02-2632-1830
			  </td>
             </tr>
            </table>
		<!-----------------------------------------제품설명공간------------------------------------------------>
		<!-----------------------------------------제품설명공간------------------------------------------------></td>
      </tr>
    </table></td>
  </tr>
    <tr><!--3-->
    <td height="15" align="center"> </td>
  </tr>
  <tr><!--4-->
        <td height="1" colspan="3" background="../images/dot2.gif"></td>
  </tr>
  <tr><!--5-->
    <td height="10" align="center"> </td>
  </tr>
  <tr><!--6-->
    <td>
    <table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#e4e4e4">
      <tr>
        <td width="305" align="left" valign="top" bgcolor="#FFFFFF">
        <table width="100%"  border="0" cellspacing="0" cellpadding="3">
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
		      <!------   결과값 보여주기 시작 -------------->
                                       <table border="0" cellspacing="1" cellpadding="2" align=center width=100%>  
									   
                                        <tr> 
                                          <td class='MlangAutoTd44'><li><B>인쇄비</B></td>
										     <td class='MlangAutoTd44'>
                                            <input type="text" size="10" name='Price' readonly style='height:18; font:bold; text-align:center;'>원
											</td>
                                        </tr>
                                        <tr> 
                                        <td class='MlangAutoTd44'><li><B>디자인</B></td>
										     <td class='MlangAutoTd44'>
                                            <input type="text" size="10" name='DS_Price' readonly style='height:18; font:bold; text-align:center;'>원
											</td>
                                        </tr>
                                        <tr>
                                            <td class='MlangAutoTd44'><li><B>금액</B></td>
										     <td class='MlangAutoTd44'>
                                            <input type="text" size="10" name='Order_Price' readonly style='height:18; font:bold; text-align:center;'>원
											</td>
                                        </tr>
                                       <!------ <tr> 
                                          <td>* 부가세
                                            <input type="text" class="inputOk" size="16"  name='VAT_Price' readonly value=''>원
											</td>
                                        </tr>
                                        <tr> 
                                          <td>* <font color="#FF0000"><b>총액</b></font>
                                            <input name="Total_Price" type="text" class="inputOk" size="16"  readonly value=''>원
											</td>
                                        </tr>----->
						               </table>
              <!------   결과값 보여주기 끄읕 -------------->
             </td>
          </tr>
          <tr>
            <td align="center" class="radi">세금별도. 배송비는 착불입니다. </td>
          </tr>
          <tr>
            <td align="center" class="radicolor">
<?php
$Ttable="$page";
include "../ConDb.php";
include "../../admin/mlangprintauto/int/info.php";

$View_temp = "View_ContText_".$View_TtableC ; 
$CONTENT_OK=$$View_temp;

include "../../mlangorder_printauto/OrderDownText.php";
?>
			</td>
          </tr>
        </table>          
		<!-----------------------------------------주문금액보기폼------------------------------------------------>
      
        </td>
        
        <td width="458" align="left" valign="top" bgcolor="#FFFFFF">
          <!-----------------------------------------파일첨부폼 시작 ------------------------------------------------>
     <!------------------------- 파일 올리기 -------------------------------->

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
	 <!------------------------- 파일 올리기 -------------------------------->
            
		 <table width="350"  border="0" align="center" cellpadding="0" cellspacing="0">
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
              <td colspan="2" align="center"><textarea name="textarea" cols="47" rows="6"></textarea></td>
            </tr>
            <tr>
              <td height="5" colspan="2" align="center"> </td>
            </tr>
		 </table>

          <!-----------------------------------------파일첨부폼 끝------------------------------------------------>
        </td>
      </tr>
    </table>
    </td>
  </tr>
    <tr><!--7-->
    <td height="15" align="center"> </td>
  </tr>
  <tr><!--8-->
        <td height="1" colspan="3" background="../images/dot2.gif"></td>
  </tr>
  <tr><!--9-->
    <td height="15" align="center"> </td>
  </tr>
  <tr><!--10-->
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
<!----------------- 박스 끄읕 -------------------->

<?php
include "../MlangPrintAutoDown.php";
?>