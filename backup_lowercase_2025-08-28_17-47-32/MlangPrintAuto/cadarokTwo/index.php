<?php
$HomeDir = "../../";
$PageCode = "PrintAuto";
$MultyUploadDir = "../../PHPClass/MultyUpload";

include "$HomeDir/db.php";
$page = $_GET['page'] ?? "cadarokTwo";
include "../MlangPrintAutoTop.php";

$Ttable = $page;
include "../ConDb.php";
include "inc.php";

$log_url = str_replace("/", "_", $_SERVER['PHP_SELF']);
$log_y = date("Y"); // 연도
$log_md = date("md"); // 월일
$log_ip = $_SERVER['REMOTE_ADDR']; // 접속 ip
$log_time = time(); // 접속 로그타임

// Ensure the global $db variable is available and used for database operations
global $db;

if (!$db) {
    die("Database connection error: " . mysqli_connect_error());
}
?>
<head>
<script language="JavaScript" type="text/JavaScript">
function MM_reloadPage(init) { 
    if (init==true) with (navigator) {
        if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
            document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage;
        }
    } else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) {
        location.reload();
    }
}
MM_reloadPage(true);

function MM_findObj(n, d) {
    var p, i, x;  
    if (!d) d=document;
    if ((p=n.indexOf("?"))>0 && parent.frames.length) {
        d=parent.frames[n.substring(p+1)].document;
        n=n.substring(0,p);
    }
    if (!(x=d[n]) && d.all) x=d.all[n];
    for (i=0; !x && i<d.forms.length; i++) x=d.forms[i][n];
    for (i=0; !x && d.layers && i<d.layers.length; i++) x=MM_findObj(n,d.layers[i].document);
    if (!x && d.getElementById) x=d.getElementById(n);
    return x;
}

function MM_showHideLayers() { 
    var i, p, v, obj, args=MM_showHideLayers.arguments;
    for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) {
        v=args[i+2];
        if (obj.style) {
            obj=obj.style;
            v=(v=='show')?'visible':(v=='hide')?'hidden':v;
        }
        obj.visibility=v;
    }
}
</script>

<style>
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
</style>
</head>

<script>
function CheckTotal(mode) {
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

function calc() {
    var asd = document.forms["choiceForm"];
    cal.document.location.href = 'price_cal.php?ordertype=' + asd.ordertype.value + '&MY_type=' + asd.MY_type.value + '&PN_type=' + asd.PN_type.value + '&MY_Fsd=' + asd.MY_Fsd.value + '&MY_amount=' + asd.MY_amount.value;
}

function calc_ok() {
    var asd = document.forms["choiceForm"];
    cal.document.location.href = 'price_cal.php?ordertype=' + asd.ordertype.value + '&MY_type=' + asd.MY_type.value + '&PN_type=' + asd.PN_type.value + '&MY_Fsd=' + asd.MY_Fsd.value + '&MY_amount=' + asd.MY_amount.value;
}
</script>

<script type="text/javascript"><?php include "DbZip.php"; ?></script>

<iframe name="Tcal" frameborder="0" width="0" height="0"></iframe>
<iframe name="cal" frameborder="0" width="0" height="0"></iframe>

<!----------------- 박스 시작 -------------------->
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
<form name='choiceForm' method='post'>
<tr>
    <td height="5" align="center"></td>
</tr>
<tr>
    <td align="center" valign="top">
    <!------------------------------------------select 메뉴----------------------------------------->
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td width="40%" align="left" valign="top">
        <table width="103%" border="0" align="center" cellpadding="1" cellspacing="1">
          <tr onMouseOver="MM_showHideLayers('print01','','show','print02','','hide','print03','','hide','print04','','hide','print05','','hide')">
            <td align="center" class='LeftText'>구분</td>
            <td bgcolor="#FFFFFF">
              <select class="input" name='MY_type' onchange='change_Field(this.value)'>
<?php
$Cate_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC");
if (!$Cate_result) {
    die("Query Failed: " . mysqli_error($db));
}
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
$result_CV = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0' ORDER BY no ASC LIMIT 0, 1");
if ($result_CV) {
    $row_CV = mysqli_fetch_array($result_CV);
    $CV_no = htmlspecialchars($row_CV['no'], ENT_QUOTES, 'UTF-8');
} else {
    echo "Query Failed: " . mysqli_error($db);
}
?>

          <tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','hide','print03','','show','print04','','hide','print05','','hide')">
            <td align="center" class='LeftText'>규격</td>
            <td bgcolor="#FFFFFF">
              <select name="MY_Fsd" onchange="calc_ok();">
<?php
$result_CV_Two = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE BigNo='$CV_no' ORDER BY no ASC");
if ($result_CV_Two) {
    while ($row_CV_Two = mysqli_fetch_array($result_CV_Two)) {
        echo "<option value='" . htmlspecialchars($row_CV_Two['no'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row_CV_Two['title'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
} else {
    echo "Query Failed: " . mysqli_error($db);
}
?>
              </select>
            </td>
          </tr>
          <tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','show','print03','','hide','print04','','hide','print05','','hide')">
            <td align="center" class='LeftText'>종이종류</td>
            <td bgcolor="#FFFFFF">
              <select name="PN_type" onChange="calc_ok();">
<?php
$result_CV_One = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE TreeNo='$CV_no' ORDER BY no ASC");
if ($result_CV_One) {
    while ($row_CV_One = mysqli_fetch_array($result_CV_One)) {
        echo "<option value='" . htmlspecialchars($row_CV_One['no'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row_CV_One['title'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
} else {
    echo "Query Failed: " . mysqli_error($db);
}
?>
              </select>
            </td>
          </tr>
          <tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','hide','print03','','hide','print04','','show','print05','','hide')">
            <td align="center" class='LeftText'>수량</td>
            <td bgcolor="#FFFFFF">
              <select name="MY_amount" onChange="calc_ok();">
                <option value='1000'>1000부</option>
                <option value='2000'>2000부</option>
                <option value='3000'>3000부</option>
                <option value='4000'>4000부</option>
                <option value='5000'>5000부</option>
                <option value='기타'>기타</option>
              </select>
            </td>
          </tr>
          <tr onMouseOver="MM_showHideLayers('print01','','hide','print02','','hide','print03','','hide','print04','','hide','print05','','show')">
            <td align="center" class='LeftText'>주문방법</td>
            <td bgcolor="#FFFFFF">
              <select name=ordertype onChange="calc_ok();">
                <option value='print'>인쇄만 의뢰</option>
              </select>
            </td>
          </tr>
        </table>
        </td>
        <td width="60%" align="left" valign="top">
          <table width="100%" border="0" cellpadding="3" cellspacing="0">
            <tr>
              <td width="2%" align="left" valign="top">&nbsp;</td>
              <td width="98%" align="left" valign="top">
                옆의 항목을 선택 하시면 고객님께서 원하는 방식으로<br>
                견적안내 금액을 보실수 있습니다.<br><br>
                <b>바로 주문을 하시려면 주문하기를 클릭하세요.</b><br><br>
                두손기획-고객센터: 02-2632-1830
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td height="15" align="center"></td>
  </tr>
  <tr>
    <td height="1" colspan="3" background="../../images/dot2.gif"></td>
  </tr>
  <tr>
    <td height="10" align="center"></td>
  </tr>
  <tr>
    <td>
      <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#e4e4e4">
        <tr>
          <td width="305" align="left" valign="top" bgcolor="#FFFFFF">
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
              <tr>
                <td width="172" align="center">
                  <a href="javascript:calc();">
                    <img src="/images/estimate.gif" width="99" height="31" border=0>
                  </a>
                </td>
              </tr>
              <tr>
                <td height="5" align="center"></td>
              </tr>

              <head>
                <script language="JavaScript">
function small_window(myurl) {
    var newWindow;
    var props = 'scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=400,height=200';
    newWindow = window.open("<?=$MultyUploadDir?>/" + myurl + "&Mode=tt", "Add_from_Src_to_Dest", props);
}

function addToParentList(sourceList) {
    destinationList = window.document.forms[0].parentList;
    for (var count = destinationList.options.length - 1; count >= 0; count--) {
        destinationList.options[count] = null;
    }
    for (var i = 0; i < sourceList.options.length; i++) {
        if (sourceList.options[i] != null)
            destinationList.options[i] = new Option(sourceList.options[i].text, sourceList.options[i].value);
    }
}

function selectList(sourceList) {
    sourceList = window.document.forms[0].parentList;
    for (var i = 0; i < sourceList.options.length; i++) {
        if (sourceList.options[i] != null)
            sourceList.options[i].selected = true;
    }
    return true;
}

function deleteSelectedItemsFromList(sourceList) {
    var maxCnt = sourceList.options.length;
    for (var i = maxCnt - 1; i >= 0; i--) {
        if ((sourceList.options[i] != null) && (sourceList.options[i].selected == true)) {
            window.open('<?=$MultyUploadDir?>/FileDelete.php?FileDelete=ok&Turi=<?=htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8')?>&Ty=<?=htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8')?>&Tmd=<?=htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8')?>&Tip=<?=htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8')?>&Ttime=<?=htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8')?>&FileName=' + sourceList.options[i].text, '', 'scrollbars=no,resizable=no,width=100,height=100,top=2000,left=2000');
            sourceList.options[i] = null;
        }
    }
}

function FormCheckField() {
    var f = document.choiceForm;
    var winopts = "width=780,height=590,toolbar=no,location=no,directories=no,status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes";
    var popup = window.open('', 'MlangMulty<?=$log_y?><?=$log_md?><?=$log_time?>', winopts);
    popup.focus();
}

function MlangWinExit() {
    if (document.choiceForm.OnunloadChick.value == "on") {
        window.open("<?=$MultyUploadDir?>/FileDelete.php?DirDelete=ok&Turi=<?=htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8')?>&Ty=<?=htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8')?>&Tmd=<?=htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8')?>&Tip=<?=htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8')?>&Ttime=<?=htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8')?>", "MlangWinExitsdf", "width=100,height=100,top=2000,left=2000,toolbar=no,location=no,directories=no,status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes");
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
              <input type='hidden' name='page' value='<?=htmlspecialchars($Ttable, ENT_QUOTES, 'UTF-8')?>'>  

              <tr>
                <td align="center">
                <!------   결과값 보여주기 시작 -------------->
                <table border="0" cellspacing="1" cellpadding="2" align="center" width="100%">  
                  <tr> 
                    <td class='MlangAutoTd44'>인쇄비</td>
                    <td class='MlangAutoTd44'>
                      <input type="text" size="10" name='Price' readonly style='height:18px; font-weight:bold; text-align:center;'>원
                    </td>
                  </tr>
                  <tr> 
                    <td class='MlangAutoTd44'>금액</td>
                    <td class='MlangAutoTd44'>
                      <input type="text" size="10" name='Order_Price' readonly style='height:18px; font-weight:bold; text-align:center;'>원
                    </td>
                  </tr>
                </table>
                <!------   결과값 보여주기 끄읕 -------------->
                </td>
              </tr>
              <tr>
                <td align="center" class="radi">세금별도. 배송비는 착불입니다.</td>
              </tr>
              <tr>
                <td align="center" class="radicolor">
<?php
include "../ConDb.php";
include "../../admin/mlangprintauto/int/info.php";

// $View_temp = "View_ContText_" . $View_TtableA;
// $CONTENT_OK = $$View_temp;
$View_ContText_ = isset($View_ContText_) ? $View_ContText_ : '';

$View_temp = "View_ContText_" . $View_TtableA; 
$CONTENT_OK = $$View_temp;

include "../../MlangOrder_PrintAuto/OrderDownText.php";
?>
                </td>
              </tr>
            </table>          
          </td>
          
          <td width="458" align="left" valign="top" bgcolor="#FFFFFF">
          <!-----------------------------------------파일첨부폼 시작 ------------------------------------------------>
          <table border="0" align="center" width="300" cellpadding="2" cellspacing="0">
            <tr>
              <td colspan="2"><img src="/images/sub3_img_10.gif" width="262" height="24"></td>
            </tr>
            <tr>
              <td width="100%">
                <select size="3" style="width:245px; font-size:10pt; color:#336666; font-weight:bold;" name="parentList" multiple>
                </select>
              </td>
              <td width="30%">
                <input type="button" onClick="javascript:small_window('FileUp.php?Turi=<?=htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8')?>&Ty=<?=htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8')?>&Tmd=<?=htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8')?>&Tip=<?=htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8')?>&Ttime=<?=htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8')?>');" value=" 파일올리기 " style="width:80px; height:25px;"><br>
                <input type="button" onclick="javascript:deleteSelectedItemsFromList(parentList);" value=" 삭제 " style="width:80px; height:25px;">
              </td>
            </tr>
          </table>

          <table width="350" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td height="5" colspan="2"></td>
            </tr>
            <tr>
              <td colspan="2"><img src="/images/sub3_img_13.gif" width="93" height="21"></td>
            </tr>
            <tr>
              <td height="2" colspan="2" align="center" background="<?=$SoftUrl?>images/dot.gif"></td>
            </tr>
            <tr>
              <td colspan="2" align="center"><textarea name="textarea" cols="47" rows="6"></textarea></td>
            </tr>
            <tr>
              <td height="5" colspan="2" align="center"></td>
            </tr>
          </table>
          <!-----------------------------------------파일첨부폼 끝------------------------------------------------>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td height="15" align="center"></td>
  </tr>
  <tr>
    <td height="1" colspan="3" background="../../images/dot2.gif"></td>
  </tr>
  <tr>
    <td height="15" align="center"></td>
  </tr>
  <tr>
    <td align="center">
      <input type="image" onClick="javascript:return CheckTotal('OrderOne');" src="/images/sub3_img_17.gif" width="99" height="31">
    </td>
  </tr>
</form>
</table>
<!----------------- 박스 끄읕 -------------------->

<?php
$PrintTextBox_left = 230 + $DhtmlLeftFos;
$PrintTextBox_top = $DhtmlTopFos;
$PrintTextBox_width = "360";
$PrintTextBox_height = "100";
?>

<?php include "../DhtmlText.php"; ?>

<?php include "../MlangPrintAutoDown.php"; ?>
