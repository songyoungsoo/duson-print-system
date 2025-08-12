<?php
session_start();

$HomeDir = "../../";
$PageCode = "PrintAuto";
$MultyUploadDir = "../../PHPClass/MultyUpload";

include "$HomeDir/db.php";
if (!$page) {
    $page = "inserted";
}
include "../MlangPrintAutoTop.php";

$Ttable = "$page";
include "../ConDb.php";
include "inc.php";

$log_url = preg_replace("/\//", "_", $_SERVER['PHP_SELF']);
$log_y = date("Y");                  // 연도
$log_md = date("md");            // 월일
$log_ip = $_SERVER['REMOTE_ADDR'];  // 접속 ip
$log_time = time();               // 접속 로그타임
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>여러개 업로드</title>

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
    border: 0;
    solid #FFFFFF;
    font: bold;
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
</style>
<script type="text/javascript"><?php include "DbZip.php"; ?></script>
</head>
<body>
<script>
function small_window(myurl) {
    var newWindow;
    var props = 'scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=400,height=200';
    newWindow = window.open("<?=$MultyUploadDir?>/"+myurl+"&Mode=tt", "Add_from_Src_to_Dest", props);
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
            window.open('<?=$MultyUploadDir?>/FileDelete.php?FileDelete=ok&Turi=<?=$log_url?>&Ty=<?=$log_y?>&Tmd=<?=$log_md?>&Tip=<?=$log_ip?>&Ttime=<?=$log_time?>&FileName='+sourceList.options[i].text,'','scrollbars=no,resizable=no,width=100,height=100,top=2000,left=2000');
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
        window.open("<?=$MultyUploadDir?>/FileDelete.php?DirDelete=ok&Turi=<?=$log_url?>&Ty=<?=$log_y?>&Tmd=<?=$log_md?>&Tip=<?=$log_ip?>&Ttime=<?=$log_time?>","MlangWinExitsdf","width=100,height=100,top=2000,left=2000,toolbar=no,location=no,directories=no,status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes");
    }
}
window.onunload = MlangWinExit;
</script>

<input type="hidden" name="OnunloadChick" value="on">
<input type="hidden" name="Turi" value="<?=$log_url?>">
<input type="hidden" name="Ty" value="<?=$log_y?>">
<input type="hidden" name="Tmd" value="<?=$log_md?>">
<input type="hidden" name="Tip" value="<?=$log_ip?>">
<input type="hidden" name="Ttime" value="<?=$log_time?>">
<input type="hidden" name="ImgFolder" value="<?=$log_url?>/<?=$log_y?>/<?=$log_md?>/<?=$log_ip?>/<?=$log_time?>">   
<input type="hidden" name="OrderSytle" value="<?=$View_TtableC?>">

<table border="0" align="center" width="300" cellpadding="2" cellspacing="0">
    <tr>
        <td colspan="2"><img src="/images/sub3_img_10.gif" width="262" height="24"></td>
    </tr>
    <tr>
        <td width="100%">
            <select size="3" style="width:245; font-size:10pt; color:#336666; font:bold;" name="parentList" multiple>
            </select>
        </td>
        <td width="30%">
            <input type="button" onClick="javascript:small_window('FileUp.php?Turi=<?=$log_url?>&Ty=<?=$log_y?>&Tmd=<?=$log_md?>&Tip=<?=$log_ip?>&Ttime=<?=$log_time?>');" value=" 파일올리기 " style="width:80; height:25;"><br>
            <input type="button" onclick="javascript:deleteSelectedItemsFromList(parentList);" value=" 삭 제 " style="width:80; height:25;">
        </td>
    </tr>
</table>

<table width="300" border="0" align="center" cellpadding="0" cellspacing="0">
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
        <td colspan="2" align="center"><textarea name="textarea" cols="47" rows="5"></textarea></td>
    </tr>
    <tr>
        <td height="5" colspan="2" align="center"></td>
    </tr>
</table>
</body>
</html>
