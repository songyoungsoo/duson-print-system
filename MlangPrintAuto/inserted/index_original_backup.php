<div class="calculator-fragment">
<?php
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";
// exit;

$HomeDir = "../../";
$PageCode = "PrintAuto";
$MultyUploadDir = "../../PHPClass/MultyUpload";

include "$HomeDir/db.php";
$page = $_GET['page'] ?? "inserted"; // $page가 설정되지 않은 경우 "inserted"로 설정
// include "../MlangPrintAutoTop.php";

$Ttable = $page;
include "../ConDb.php";
include "inc.php";

function getUserIP() {
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      return $_SERVER['HTTP_CLIENT_IP'];
  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
  } else {
      return $_SERVER['REMOTE_ADDR'];
  }
}

$log_url = preg_replace("/\//", "_", $_SERVER['PHP_SELF']);
$log_y = date("Y");
$log_md = date("md");
// $log_ip = $_SERVER['REMOTE_ADDR'];  // 접속 IP
$log_ip = getUserIP();
$log_time = time();
if ($log_ip === "::1") {
  $log_ip = "127.0.0.1"; // 윈도우에서는 IPv4로 강제 변환
}
?>
<head>
<script type="text/javascript"> 

function CheckTotal(mode){
  var f = document.choiceForm;

  if (f.StyleForm.value == "") {
    alert("주문/견적문의 을 실행 하기 위하여 오류가 있습니다.\n\n다시 실행 시켜 주십시요...!!\n\n(<?=$admin_name?>)");
    return false;
  }

  if (f.SectionForm.value == "") {
    alert("주문/견적문의 을 실행 하기 위하여 오류가 있습니다.\n\n다시 실행 시켜 주십시요...!!");
    return false;
  }

  if (f.Order_PriceForm.value == "") {
    alert("주문/견적문의 을 실행 하기 위하여 오류가 있습니다.\n\n다시 실행 시켜 주십시요...!!");
    return false;
  }

  if (f.Total_PriceForm.value == "") {
    alert("주문/견적문의 을 실행 하기 위하여 오류가 있습니다.\n\n다시 실행 시켜 주십시요...!!");
    return false;
  }

  f.action = "/MlangOrder_PrintAuto/OnlineOrder.php?SubmitMode=" + mode;
  f.submit(); 
}

function calc(){
  var asd = document.forms["choiceForm"];
  cal.document.location.href = 'price_cal.php?MY_type=' + asd.MY_type.value + '&PN_type=' + asd.PN_type.value + '&MY_Fsd=' + asd.MY_Fsd.value + '&MY_amount=' + asd.MY_amount.value + '&ordertype=' + asd.ordertype.value + '&POtype=' + asd.POtype.value;
}

function calc_ok() {
  var asd = document.forms["choiceForm"];
  cal.document.location.href = 'price_cal.php?MY_type=' + asd.MY_type.value + '&PN_type=' + asd.PN_type.value + '&MY_Fsd=' + asd.MY_Fsd.value + '&MY_amount=' + asd.MY_amount.value + '&ordertype=' + asd.ordertype.value + '&POtype=' + asd.POtype.value;
}
</script>
<STYLE>
.input {
  font-size: 10pt;
  background-color: #FFFFFF;
  color: #336699;
  line-height: 130%;
}
.inputOk {
  font-size: 10pt;
  background-color: #FFFFFF;
  color: #429EB2;
  border-style: solid;
  height: 22px;
  border: 0 solid #FFFFFF;
  font-weight: bold;
}
.Td1 {
  font-size: 9pt;
  background-color: #EBEBEB;
  color: #336699;
}
.Td2 {
  font-size: 9pt;
  color: #232323;
}
.style3 {
  color: #33CCFF;
}
.style4 {
  color: #FF0000;
}
</STYLE>

<?php include "DbZip.php"; ?>

<iframe name=Tcal frameborder=0 width=0 height=0></iframe>
<iframe name=cal frameborder=0 width=0 height=0></iframe>

<!----------------- 박스 시작 -------------------->
<table width="692" bgcolor="#CCCCCC" border="0" align="center" cellpadding="10" cellspacing="1">
<tr>
<td bgcolor="#FFFFFF">
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">

<form name='choiceForm' method='post'>

  <tr>
    <td height="5" align="center"> </td>
  </tr>
  <tr>
    <td align="center" valign="top">
    <!------------------------------------------select메뉴----------------------------------------->
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td width="50%" align="left" valign="top">
        <table width="100%" border="0" align="center" cellpadding="1" cellspacing="1">
          <tr onMouseOver="MM_showHideLayers('print01','','show','print02','','hide','print03','','hide','print04','','hide','print05','','hide')">
            <td align="left" class='LeftText'><li><B>인쇄색상</B></td>
            <td bgcolor="#FFFFFF">
              <select class="input" name='MY_type' onchange='change_Field(this.value)' style="width:220px; height: 30px;">
                <?php
                include "../../db.php";
                $Cate_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC");
                $Cate_rows = mysqli_num_rows($Cate_result);
                if ($Cate_rows) {
                  while ($Cate_row = mysqli_fetch_array($Cate_result)) {
                    echo "<option value='" . htmlspecialchars($Cate_row['no'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($Cate_row['title'], ENT_QUOTES, 'UTF-8') . "</option>";
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

          <tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','hide','print03','','show','print04','','hide','print05','','hide')">
            <td align="left" class='LeftText'><li><B>종이종류</B></td>
            <td bgcolor="#FFFFFF">
              <select name="MY_Fsd" onChange="calc_ok();" style="width:220px; height: 30px;">
                <?php 
                $result_CV_One = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE TreeNo='$CV_no' ORDER BY no ASC");
                $rows_CV_One = mysqli_num_rows($result_CV_One);
                if ($rows_CV_One) {
                  while ($row_CV_One = mysqli_fetch_array($result_CV_One)) {
                    echo "<option value='" . htmlspecialchars($row_CV_One['no'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row_CV_One['title'], ENT_QUOTES, 'UTF-8') . "</option>";
                  }
                }
                ?>
              </select>
            </td>
          </tr>

          <tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','show','print03','','hide','print04','','hide','print05','','hide')">
            <td align="left" class='LeftText'><li><B>종이규격</B></td>
            <td bgcolor="#FFFFFF">
              <select name="PN_type" onchange="calc_ok();" style="width:220px; height: 30px;">
                <?php 
                $result_CV_Two = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE BigNo='$CV_no' ORDER BY no ASC");
                $rows_CV_Two = mysqli_num_rows($result_CV_Two);
                if ($rows_CV_Two) {
                  while ($row_CV_Two = mysqli_fetch_array($result_CV_Two)) {
                    echo "<option value='" . htmlspecialchars($row_CV_Two['no'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row_CV_Two['title'], ENT_QUOTES, 'UTF-8') . "</option>";
                  }
                }
                ?>
              </select>
            </td>
          </tr>

          <tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','show','print03','','hide','print04','','hide','print05','','hide')">
            <td align="left" class='LeftText'><li><B>인쇄면</B></td>
            <td bgcolor="#FFFFFF">
              <select name="POtype" onChange="calc_ok();" style="width:170px; height: 30px;">
                <option value='1'>단면</option>
                <option value='2'>양면</option>
              </select>
              <!----------------  접지보기 버튼 등 시작 ------------>
              <a href="#" onClick="javascript:window.open('/MlangPrintAuto/WinDowInfo.php?img=A_1_INFO.jpg&title=절수보기', 'Ejpruuu','width=502,height=725,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');">
                <img src='/MlangPrintAuto/img/A_1.gif' border=0 align=absmiddle>
              </a>
              <!---------------  접지보기 버튼 등 끄읕 ------------>
            </td>
          </tr>

          <tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','hide','print03','','hide','print04','','show','print05','','hide')">
            <td align="left" class='LeftText'><li><B>수량</B></td>
            <td bgcolor="#FFFFFF">
              <select name="MY_amount" onchange="calc_ok();" style="width:170px; height: 30px;">
                <option value='1' selected="selected">1연</option>
                <option value='2'>2연</option>
                <option value='3'>3연</option>
                <option value='4'>4연</option>
                <option value='5'>5연</option>
                <option value='6'>6연</option>
                <option value='7'>7연</option>
                <option value='8'>8연</option>
                <option value='9'>9연</option>
                <option value='10'>10연</option>
                <option value='0.5'>0.5연</option>
              </select>
              <!-- <input type="text" name="MY_amountRight" value='우측참조' style='font-size:9pt; background-color:#FFFFFF; color:#000000; border-style:solid; height:16px; width:50px; border:0 solid #FFFFFF'> -->
              <input type="text" name="MY_amountRight" value="매수" style="font-size:9pt; 
             background-color:#FFFFFF; color:red; border-style:solid; height:16; width:50; border:0 solid #FFFFFF; font-weight: bold;">
            </td>
          </tr>
          <?php mysqli_close($db); ?>
          <tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','hide','print03','','hide','print04','','show','print05','','hide')">
            <td align="left" class='LeftText'><li><B>디자인(편집)</B></td>
            <td bgcolor="#FFFFFF">
              <select name="ordertype" onchange="calc_ok();" style="width:220px; height: 30px;">
                <option value='total'>디자인+인쇄</option>
                <option value='print'>인쇄만 의뢰</option>
              </select>
            </td>
          </tr>
        </table>          
        <!------------------------------------------select메뉴끝-----------------------------------------></td>
        <td width="50%" align="left" valign="top">
          <table border="0" cellpadding="3" cellspacing="0">
            <tr>
              <td width="2%" align="left" valign="top">&nbsp;</td>
              <td width="98%" align="left" valign="top">
                <div style="position:relative; left:0px; top:0px;">           
                  <?php
                  $PrintTextBox_left = "0";
                  $PrintTextBox_top = "0";
                  $PrintTextBox_width = "310";
                  $PrintTextBox_height = "";
                  include "../DhtmlText.php";
                  ?>
                </div>

                옆의 항목을 선택 하시면 고객님께서 원하는 방식으로<br>
                자동견적 금액을 보실수 있습니다.<br>
                <br>
                바로 주문을 하시려면 주문하기를 클릭하세요.</p>
                <p><strong>100At 합판용<br> 
                  A4사이즈나 16절사이즈<span class="style4"> 0.5연은 기간이 3~4일</span> 소요됩니다.<br>
                </strong><br>
                두손기획-고객센터: 02-2632-1830
              </td>
            </tr>
          </table>
          <!-----------------------------------------제품설명공간------------------------------------------------>
          <!-----------------------------------------제품설명공간------------------------------------------------>
        </td>
      </tr>
    </table>
  </td>
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
    <td>
      <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#e4e4e4">
        <tr>
          <td align="center" valign="top" bgcolor="#FFFFFF" colspan="2">
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
                <td width="172" align="center"><a href="javascript:calc();"><img src="/images/estimate.gif" width="99" height="31" border=0></a></td>
              </tr>
              <tr>
                <td height="5" align="center"> </td>
              </tr>

              <!--form2 start-->
              <head>
                <script language="JavaScript">

    function small_window(myurl) {
    var props = 'scrollbars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=400,height=200';
    
    // `myurl`이 `<?=$MultyUploadDir?>/`을 포함하고 있지 않다면 추가
    var fullUrl = myurl.includes("<?=$MultyUploadDir?>/") ? myurl : "<?=$MultyUploadDir?>/" + myurl;
    
    window.open(fullUrl + "&Mode=tt", "Add_from_Src_to_Dest", props);
}
// 파일 업로드 후 `parentList`에 추가
function addUploadedFileToParent(fileName) {
    var parentList = document.getElementsByName("parentList")[0];

    if (!parentList) {
        console.warn("❌ parentList가 존재하지 않습니다.");
        return;
    }

    var newOption = new Option(fileName, fileName);
    parentList.add(newOption);
}
// 부모 창의 `parentList`에 업로드된 파일 추가
function addToParentList(sourceList) {
    var destinationList = document.getElementsByName("parentList")[0]; // `parentList`를 `name` 기반으로 가져오기

    // 기존 목록 비우기
    destinationList.innerHTML = "";

    // `sourceList`의 모든 항목을 `parentList`로 복사
    for (var i = 0; i < sourceList.options.length; i++) {
        if (sourceList.options[i] !== null) {
            var newOption = new Option(sourceList.options[i].text, sourceList.options[i].value);
            destinationList.add(newOption);
        }
    }
}

// `parentList`의 모든 항목을 선택 상태로 변경 (폼 제출 시 필요)
function selectList(sourceList) {
    for (var i = 0; i < sourceList.options.length; i++) {
        if (sourceList.options[i] !== null) {
            sourceList.options[i].selected = true;
        }
    }
    return true;
}

// `parentList`에서 선택된 파일을 삭제
function deleteSelectedItemsFromList(sourceList) {
    var maxCnt = sourceList.options.length;
    for (var i = maxCnt - 1; i >= 0; i--) { 
        if ((sourceList.options[i] != null) && (sourceList.options[i].selected == true)) {
            var fileName = encodeURIComponent(sourceList.options[i].value);
            
            // 삭제 요청을 새 창으로 보냄
            window.open(
                "<?=$MultyUploadDir?>/FileDelete.php?FileDelete=ok&Turi=<?=$log_url?>&Ty=<?=$log_y?>&Tmd=<?=$log_md?>&Tip=<?=$log_ip?>&Ttime=<?=$log_time?>&FileName=" + fileName, 
                "", 
                "scrollbars=no,resizable=no,width=100,height=100,top=1500,left=1500"
            );

            // 삭제된 항목을 `parentList`에서 제거
            sourceList.remove(i);
        }
    }
}
// `parentList` 초기 로드 시 기존 파일 자동 추가
window.onload = function() {
    var parentList = document.getElementsByName("parentList")[0];

    if (!parentList || parentList.options.length === 0) {
        console.warn("❌ parentList가 비어있음, 파일 추가 필요");
    }
};

                  function FormCheckField() {
                    var f = document.choiceForm;
                    var winopts = "width=780,height=590,toolbar=no,location=no,directories=no,status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes";
                    var popup = window.open('', 'MlangMulty<?=$log_y?><?=$log_md?><?=$log_time?>', winopts);
                    popup.focus();
                  }

                  function MlangWinExit() {
                    if (document.choiceForm.OnunloadChick.value == "on") {
                      window.open("<?=$MultyUploadDir?>/FileDelete.php?DirDelete=ok&Turi=<?=$log_url?>&Ty=<?=$log_y?>&Tmd=<?=$log_md?>&Tip=<?=$log_ip?>&Ttime=<?=$log_time?>", "MlangWinExitsdf", "width=100,height=100,top=2000,left=2000,toolbar=no,location=no,directories=no,status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes");
                    }
                  }
                  window.onunload = MlangWinExit;
                </script>
              </head>

              <input type="hidden" name="OnunloadChick" value="on">
              <input type="hidden" name='Turi' value='<?=htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8')?>'>
              <input type="hidden" name='Ty' value='<?=htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8')?>'>
              <input type="hidden" name='Tmd' value='<?=htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8')?>'>
              <input type="hidden" name='Tip' value='<?=htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8')?>'>
              <input type="hidden" name='Ttime' value='<?=htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8')?>'>
              <input type="hidden" name="ImgFolder" value="<?=htmlspecialchars($log_url . "/" . $log_y . "/" . $log_md . "/" . $log_ip . "/" . $log_time, ENT_QUOTES, 'UTF-8')?>">   

              <input type='hidden' name='OrderSytle' value='<?=htmlspecialchars($View_TtableC, ENT_QUOTES, 'UTF-8')?>'>   
              <input type='hidden' name='StyleForm'>                              
              <input type='hidden' name='SectionForm'>       
              <input type='hidden' name='QuantityForm'>    
              <input type='hidden' name='DesignForm'>
              <input type='hidden' name='PriceForm'>
              <input type='hidden' name='DS_PriceForm'>
              <input type='hidden' name='Order_PriceForm'>
              <input type='hidden' name='VAT_PriceForm'>
              <input type='hidden' name='Total_PriceForm'>
              <input type='hidden' name='page' value='<?=htmlspecialchars($page, ENT_QUOTES, 'UTF-8')?>'>	
              					

              <tr>
                <td align="center">
                <!------   결과값 보여주기 시작 -------------->
                <table border="0" cellspacing="1" cellpadding="2" align="center" width="100%">  
                  <tr> 
                    <td class='MlangAutoTd44'><li><B>인쇄비</B></td>
                    <td class='MlangAutoTd44'>
                      <input type="text" name='Price' readonly style='width:130px; height: 25px;; font-weight:bold; text-align:center;'>원
                    </td>
                  </tr>
                  <tr> 
                    <td class='MlangAutoTd44'><li><B>디자인(편집)</B></td>
                    <td class='MlangAutoTd44'>
                      <input type="text" name='DS_Price' readonly style='width:130px; height: 25px;; font-weight:bold; text-align:center;'>원
                    </td>
                  </tr>
                  <tr>
                    <td class='MlangAutoTd44'><li><B>금액</B></td>
                    <td class='MlangAutoTd44'>
                      <input type="text" name='Order_Price' readonly style='width:130px; height: 25px;; font-weight:bold; text-align:center;'>원
                    </td>
                  </tr>
                </table>
                <!------   결과값 보여주기 끄읕 -------------->
              </td>
              </tr>
              <tr>
                <td align="center" class="radi">
                세금별도. 배송비는 착불입니다.
                </td>
              </tr>
              
              <!-- 파일첨부 섹션 시작 -->
              <tr>
                <td align="center">
                  <table border="0" align="center" width="280" cellpadding="2" cellspacing="0" style="margin-top: 15px;">
                    <tr>
                      <td colspan="2" align="center"><img src="/images/sub3_img_10.gif" width="262" height="24"></td>
                    </tr>
                    <tr>
                      <td width="70%" align="center">
                        <select size="3" style="width:200px; height:60px; font-size:10pt; color:#336666; font-weight:bold;" name="parentList" multiple>
                        </select>
                      </td>
                      <td width="30%" align="center" valign="middle">
                        <input type="button" onClick="javascript:small_window('FileUp.php?Turi=<?=htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8')?>&Ty=<?=htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8')?>&Tmd=<?=htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8')?>&Tip=<?=htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8')?>&Ttime=<?=htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8')?>');" value=" 파일올리기 " style="width:70px; height:25px; font-size:9pt;"><br><br>
                        <input type="button" onclick="javascript:deleteSelectedItemsFromList(parentList);" value=" 삭제 " style="width:70px; height:25px; font-size:9pt;">
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <!-- 파일첨부 섹션 끝 -->
              
              <!-- 기타사항 섹션 시작 -->
              <tr>
                <td align="center">
                  <table width="280" border="0" align="center" cellpadding="0" cellspacing="0" style="margin-top: 15px;">
                    <tr>
                      <td height="5"> </td>
                    </tr>
                    <tr>
                      <td align="center"><img src="/images/sub3_img_13.gif" width="93" height="21"></td>
                    </tr>
                    <tr>
                      <td height="2" align="center" background="<?=$SoftUrl?>images/dot.gif"> </td>
                    </tr>
                    <tr>
                      <td align="center"><textarea name="textarea" cols="35" rows="4" style="width:270px; height:70px;"><?=htmlspecialchars($textarea ?? '', ENT_QUOTES, 'UTF-8')?></textarea></td>
                    </tr>
                    <tr>
                      <td height="10"> </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <!-- 기타사항 섹션 끝 -->
              <tr>
                <td align="center" class="radicolor">
                  <?php
                  $Ttable = $page;
                  include "../ConDb.php";
                  include "../../admin/MlangPrintAuto/int/info.php";
                  $View_ContText_ = isset($View_ContText_) ? $View_ContText_ : '';

                  $View_temp = "View_ContText_" . $View_TtableA; 
                  $CONTENT_OK = $View_temp;

                  include "../../MlangOrder_PrintAuto/OrderDownText.php";
                  ?>
                </td>
              </tr>
            </table>          
          <!-----------------------------------------주문금액보기폼------------------------------------------------>
        </td>
        

      </tr>
    </table>
  </td>
  </tr>
    
  <tr>
    <td height="1" colspan="3" background="/images/dot_2.gif"></td>
  </tr>
  
  <tr>
    <td align="center">
      <input type="image" onClick="javascript:return CheckTotal('OrderOne');" src="/images/sub3_img_17.gif" width="99" height="31">
    </td>
  </tr>
</form>
</table>
</td>
</tr>
</table>
<!-- <p align="center"><img src="../img/dechre.png" width="693" height="869" alt=""/></p> -->
<!----------------- 박스 끄읕 -------------------->

<?php
// include "../MlangPrintAutoDown.php";
?>
</div>