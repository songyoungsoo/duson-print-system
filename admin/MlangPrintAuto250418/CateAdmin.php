<?php
// 📌 GET 값 초기화
$code            = $_GET['code']         ?? $_POST['code']         ?? '';
$ACate           = $_GET['ACate']        ?? $_POST['ACate']        ?? '';
$ATreeNo         = $_GET['ATreeNo']      ?? $_POST['ATreeNo']      ?? '';
$TreeSelect      = $_GET['TreeSelect']   ?? $_POST['TreeSelect']   ?? '';
$mode            = $_GET['mode']         ?? $_POST['mode']         ?? '';
$Cate            = $_GET['Cate']         ?? $_POST['Cate']         ?? '';
$PageCode        = $_GET['PageCode']     ?? $_POST['PageCode']     ?? '';
$Ttable          = $_GET['Ttable']       ?? $_POST['Ttable']       ?? '';
$TIO_CODE        = $_GET['TIO_CODE']     ?? $_POST['TIO_CODE']     ?? '';
$Ttable          = $Ttable ?: $TIO_CODE; // fallback 설정
$search          = $_GET['search']       ?? $_POST['search']       ?? '';
$RadOne          = $_GET['RadOne']       ?? $_POST['RadOne']       ?? '';
$myListTreeSelect= $_GET['myListTreeSelect'] ?? $_POST['myListTreeSelect'] ?? '';
$myList          = $_GET['myList']       ?? $_POST['myList']       ?? '';
$offset          = isset($_GET['offset']) ? (int)$_GET['offset'] : (isset($_POST['offset']) ? (int)$_POST['offset'] : 0);
$no              = isset($_GET['no']) ? (int)$_GET['no'] : (isset($_POST['no']) ? (int)$_POST['no'] : 0);
$PHP_SELF        = htmlspecialchars($_SERVER['PHP_SELF'] ?? '');


function getTtableTitle($code) {
    $titles = [
        "inserted" => "전단지",
        "NameCard" => "명함",
        "cadarok" => "리플렛",
        "msticker" => "스티커",
        "MerchandiseBond" => "상품권",
        "envelope" => "봉투",
        "NcrFlambeau" => "양식지",
        "LittlePrint" => "소량인쇄",
        "cadarokTwo" => "카다로그",
        "hakwon" => "학원",
        "food" => "음식",
        "company" => "기업체",
        "cloth" => "의류",
        "commerce" => "상업",
        "church" => "교회",
        "nonprofit" => "비영리",
        "etc" => "기타"
    ];
    return $titles[$code] ?? $code;
}

include "../title.php";
include "../../MlangPrintAuto/ConDb.php";


$View_TtableB = $Ttable;

$View_TtableC = getTtableTitle($Ttable); // 이건 실제 테이블 한글명이라면 따로 정의 필요
$PageCode = "Category";
$DF_Tatle_1 = $TtableTitles[$Ttable][0] ?? '';
$DF_Tatle_2 = $TtableTitles[$Ttable][1] ?? '';
$DF_Tatle_3 = $TtableTitles[$Ttable][2] ?? '';



//타이틀 변수 초기화
$DF_Tatle_1 = $DF_Tatle_2 = $DF_Tatle_3 = '';

if (isset($TtableTitles[$Ttable])) {
    $DF_Tatle_1 = $TtableTitles[$Ttable][0] ?? '';
    $DF_Tatle_2 = $TtableTitles[$Ttable][1] ?? '';
    $DF_Tatle_3 = $TtableTitles[$Ttable][2] ?? '';
}

////////////////// 관리자 로그인 ////////////////////
include "../../db.php";
include "../config.php";
include "../../MlangPrintAuto/ConDb.php";
include "CateAdmin_title.php";	
////////////////////////////////////////////////////



if($mode=="form"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////

include "../title.php";

$Bgcolor1="408080";

if($code=="modify"){include "CateView.php";}
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script language=javascript>
window.moveTo(screen.width/5, screen.height/5); 

function MemberXCheckField(){
var f=document.FrmUserXInfo;

if (f.title.value == "") {
alert("TITLE 을 입력하여주세요!!");
f.title.focus();
return false;
}

}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding=0 cellspacing=5>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>'>
<INPUT TYPE="hidden" name='Ttable' value='<?php echo $Ttable?>'>
<INPUT TYPE="hidden" name='TreeSelect' value='<?php echo $TreeSelect?>'>
<?php if($ACate){?><INPUT TYPE="hidden" name='ACate' value='<?php echo $ACate?>'><?php }?>
<?php if($ATreeNo){?><INPUT TYPE="hidden" name='ATreeNo' value='<?php echo $ATreeNo?>'><?php }?>

<INPUT TYPE="hidden" name='mode' value='<?php if($code=="modify"){?>modify_ok<?php }else{?>form_ok<?php }?>'>
<?php if($code=="modify"){?><INPUT TYPE="hidden" name='no' value='<?php echo $no?>'><?php }?>

<tr>
<td class='coolBar' colspan=4 height=25>
<b>&nbsp;&nbsp;(<b><?php echo $View_TtableC?></b>) 
<!--if ($ListXTtable == "0" || $Ttable == "inserted" || $ToTitle == "전단지") {
    $View_TtableA = "0";
    $View_TtableB = "inserted";
    $View_TtableC = "전단지";
    MlangPrintAuto\ConDb.php -->
    <!-- admin\MlangPrintAuto\CateAdmin_title.php
     if($Ttable=="inserted"){//////////////////////////////////////////////////////////////////////////////////////////

        $DF_Tatle_1="인쇄규격";
        $DF_Tatle_2="종이규격";
        $DF_Tatle_3="종이종류"; -->
<?php
if(!$TreeSelect){echo("$DF_Tatle_1");}
if($TreeSelect=="1"){echo("$DF_Tatle_2");}
if($TreeSelect=="2"){echo("$DF_Tatle_3");}
?>
<?php if($code=="modify"){?>수정<?php }else{?>입력<?php }?></b><BR>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>상위메뉴&nbsp;&nbsp;</td>
<td colspan=3>

<SELECT NAME="BigNo">
        <!-- mlangprintauto_transactioncate -->
<?php

if (empty($TreeSelect)) {
    echo "<option value='0'>◆ 최상의 TITLE로 등록 ◆</option>";
} else {
    $Cate_result = mysqli_query($db, "SELECT * FROM  mlangprintauto_inserted WHERE Ttable='$Ttable' AND BigNo='0'"); //$GGTABLE
    if ($Cate_result && mysqli_num_rows($Cate_result) > 0) {
        while($Cate_row = mysqli_fetch_assoc($Cate_result)) {
            // 각 행의 내용을 출력
            echo "No: " . $Cate_row['no'] . "<br>";
            echo "Title: " . $Cate_row['title'] . "<br>";
            // 필요한 다른 필드도 출력할 수 있습니다.
            echo "<hr>"; // 구분선
        }
    } else {
        echo "결과가 없습니다.";
    }
    if ($Cate_result && mysqli_num_rows($Cate_result) > 0) {
        while($Cate_row = mysqli_fetch_assoc($Cate_result)) {
            $selected = '';
            if ($code === "modify") {
                if ($ACate == $Cate_row['no']) {
                    $selected = "selected style='background-color:green; color:#fff;'";
                } elseif ($ATreeNo == $Cate_row['no']) {
                    $selected = "selected style='background-color:blue; color:#fff;'";
                }
            }
            echo "<option value='{$Cate_row['no']}' $selected>{$Cate_row['title']}</option>";
        }
    } else {
        echo "<option value=''>※ 상위 카테고리 없음 ※</option>";
    }
}
?>
</SELECT>


</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>TITLE&nbsp;&nbsp;</td>
<td colspan=3><INPUT TYPE="text" NAME="title" size=50 maxLength='80' value='<?php if($code=="modify"){echo("$View_title");}?>'></td>
</tr>

<tr>
<td colspan=4 align=center>
<input type='submit' value=' <?php if($code=="modify"){?>수정<?php }else{?>저장<?php }?> 합니다.'>
</td>
</tr>

</table>

<?php } //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="delete"){

$result= mysqli_query($db, "select * from $GGTABLE where no='$no'");
$row= mysqli_fetch_array($result);


if ($row['BigNo'] == "0") {
    mysqli_query($db, "DELETE FROM $GGTABLE WHERE BigNo='$no'");
    mysqli_query($db, "DELETE FROM $GGTABLE WHERE no='$no'");
} else {
    mysqli_query($db, "DELETE FROM $GGTABLE WHERE no='$no'");
}
mysqli_close($db);



echo ("
<html>
<script language=javascript>
window.alert('$no번 자료을 삭제 처리 하였습니다.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="form_ok"){


if($TreeSelect=="1"){
$dbinsert ="insert into $GGTABLE values('',
'$Ttable',
'$BigNo',
'$title',
''
)";
}

else if($TreeSelect=="2"){
$dbinsert ="insert into $GGTABLE values('',
'$Ttable',
'',
'$title',
'$BigNo'
)";
                }else{
$dbinsert ="insert into $GGTABLE values('',
'$Ttable',
'$BigNo',
'$title',
''
)";
                }

$result_insert= mysqli_query($db, $dbinsert);

$url = htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=form&Ttable=$Ttable&TreeSelect=$TreeSelect&ACate=$ACate&ATreeNo=$ATreeNo";

echo ("
	<script language='javascript'>
	alert('\\nCATEGORY[$View_TtableC] 자료를 정상적으로 저장 하였습니다.\\n');
	opener.parent.location.reload();
	</script>
	<meta http-equiv='Refresh' content='0; URL=$url'>
");
exit;


} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>


<?php
if($mode=="modify_ok"){

             if($TreeSelect=="2"){
$query ="UPDATE $GGTABLE SET  
title='$title',
TreeNo='$BigNo'
WHERE no='$no'";
                }else{

$query ="UPDATE $GGTABLE SET 
BigNo='$BigNo',  
title='$title'
WHERE no='$no'";
                }

$result= mysqli_query($db, $query);

	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	$url = htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=form&code=modify&no=$no&Ttable=$Ttable&TreeSelect=$TreeSelect&ACate=$ACate&ATreeNo=$ATreeNo";

	echo ("
		<script language='javascript'>
		alert('\\n정보를 정상적으로 수정하였습니다.\\n');
		opener.parent.location.reload();
		</script>
		<meta http-equiv='Refresh' content='0; URL=$url'>
	");
	exit;

}
mysqli_close($db);


}
?>