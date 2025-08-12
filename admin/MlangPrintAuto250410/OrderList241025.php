<?php
// 파일 경로 설정
$configPath = '../config.php';
$adminMenuPath = '../admin_menu.php';
$dbPath = '../../db.php';

// 파일 존재 여부 확인 및 포함
if (file_exists($configPath)) {
    include_once($configPath);
} else {
    die("Error: Config file not found at $configPath");
}

if (file_exists($adminMenuPath)) {
    include_once($adminMenuPath);
} else {
    die("Error: Admin menu file not found at $adminMenuPath");
}

if (file_exists($dbPath)) {
    include_once($dbPath);
} else {
    die("Error: Database file not found at $dbPath");
}

// 변수 초기화
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$check = isset($_REQUEST['check']) ? $_REQUEST['check'] : [];
$no = isset($_REQUEST['no']) ? $_REQUEST['no'] : '';
$YearOne = isset($_REQUEST['YearOne']) ? $_REQUEST['YearOne'] : '';
$YearTwo = isset($_REQUEST['YearTwo']) ? $_REQUEST['YearTwo'] : '';
$Type = isset($_REQUEST['Type']) ? $_REQUEST['Type'] : '';
$Cate = isset($_REQUEST['Cate']) ? $_REQUEST['Cate'] : '';
$TDsearchValue = isset($_REQUEST['TDsearchValue']) ? $_REQUEST['TDsearchValue'] : '';
$offset = isset($_REQUEST['offset']) ? intval($_REQUEST['offset']) : 0;
$CountWW = isset($_REQUEST['CountWW']) ? $_REQUEST['CountWW'] : '';
$ModifyCode = isset($_REQUEST['ModifyCode']) ? $_REQUEST['ModifyCode'] : ''; // ModifyCode 변수 추가

if ($mode == "ChickBoxAll") {
    if (empty($check)) {
        echo ("<script language='javascript'>
        window.alert('삭제 [처리]할 체크항목이 없습니다.\\n\\n[삭제] 처리할 것을 체크하여 주십시요.');
        history.go(-1);
        </script>");
        exit;
    }

    $db = new mysqli($host, $user, $password, $dataname);
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }
    
    foreach ($check as $id) {
        $stmt = $db->prepare("DELETE FROM MlangOrder_PrintAuto WHERE no = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    $db->close();

    echo ("<script language='javascript'>
          window.alert('체크한 항목을 정상적으로 [삭제] 처리 하였습니다..');
           </script>
           <meta http-equiv='Refresh' content='0; URL=$_SERVER[PHP_SELF]'>
            ");
    exit;
}

if ($mode == "sendback") {
    include("../title.php");
?>
<head>
<script src="../js/coolbar.js" type="text/javascript"></script>
<script language="javascript">
window.moveTo(screen.width/5, screen.height/5);

function MemberCheckField() {
    var f = document.FrmUserInfo;
    if (f.cont.value == "") {
        alert("반송이유를 적어 주셔야 처리할 수 있습니다....");
        f.cont.focus();
        return false;
    }
    return true;
}
</script>
<style>
    body {
        background-color: #F5F5F5; /* 엷은 회색 배경색 추가 */
    }
</style>
</head>

<body LEFTMARGIN='5' TOPMARGIN='5' MARGINWIDTH='5' MARGINHEIGHT='5' class='coolBar'>
<table border='0' align='center' width='100%' cellpadding='10' cellspacing='5'>
<tr><td bgcolor='#336699'>
<font style='font-size:10pt; line-height:130%; color:#FFFFFF;'>
반송 이유(송장번호 등...)을 적어 주세요....<BR>
<font style='font-size:8pt;'><font color='red'>*</font> 반송 처리하면 PM 회원 적립금 총합계에서 자동 차감됩니다.</font>
</font>
</td></tr>
<form name='FrmUserInfo' method='post' OnSubmit='javascript:return MemberCheckField()' action='<?php echo  $_SERVER['PHP_SELF'] ?>'>
<tr><td>
<INPUT TYPE="hidden" name='mode' value='sendback_ok'>
<INPUT TYPE="hidden" name='no' value='<?php echo  $no ?>'>
<INPUT TYPE="text" NAME="cont" size=50>
<input type='submit' value='처리하기'>
</td></tr>
</form>
</table>
</body>
</html>

<?php
    exit;
}

if ($mode == "sendback_ok") {
    include_once($dbPath);
    $db = new mysqli($host, $user, $password, $dataname);
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }
    $date = date("Y-m-d H:i:s");

    $stmt = $db->prepare("SELECT * FROM MlangOrder_PrintAuto WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row) {
        // 회원전체 적립금 디비 차감 MlangPM_MemberTotalMoney
        // 회원 현재 적립금 파악 TotalMoney
        $stmt = $db->prepare("SELECT * FROM MlangPM_MemberTotalMoney WHERE id = ? ORDER BY no DESC LIMIT 0, 1");
        $stmt->bind_param("s", $row['PMmember']);
        $stmt->execute();
        $result_Pluse = $stmt->get_result();
        $row_Pluse = $result_Pluse->fetch_assoc();
        if ($row_Pluse) {
            $SS_TotalMoney = $row_Pluse['TotalMoney'];
            $TotalMoneyNo = $row_Pluse['no'];
        } else {
            echo ("<script language='javascript'>window.alert('DataBase 에러 입니다.'); opener.parent.location.reload(); window.self.close();</script>");
            exit;
        }
        
        $stmt = $db->prepare("SELECT * FROM MlangPM_MemberMoney WHERE PMThingOrderNo = ?");
        $stmt->bind_param("i", $row['no']);
        $stmt->execute();
        $result_MemberMoney = $stmt->get_result();
        $row_MemberMoney = $result_MemberMoney->fetch_assoc();
        if ($row_MemberMoney) {
            $SS_MemberMoney = $row_MemberMoney['Money_2'];
        } else {
            echo ("<script language='javascript'>window.alert('DataBase 에러 입니다.'); opener.parent.location.reload(); window.self.close();</script>");
            exit;
        }

        // 회원전체 적립금 디비 차감
        $SS_TotalMoney_ok = $SS_TotalMoney - $SS_MemberMoney;
        $stmt = $db->prepare("UPDATE MlangPM_MemberTotalMoney SET TotalMoney = ? WHERE no = ?");
        $stmt->bind_param("ii", $SS_TotalMoney_ok, $TotalMoneyNo);
        $stmt->execute();

        // 주문 테이블 반송으로 변경 MlangOrder_PrintAuto OrderStyle 반송 처리 6번 ok
        $stmt = $db->prepare("UPDATE MlangOrder_PrintAuto SET OrderStyle = '6' WHERE no = ?");
        $stmt->bind_param("i", $no);
        $stmt->execute();
        
        // MlangPM_MemberMoney 테이블 sendback필드에 반송 기록 저장 TakingStyle필드에 반송 기록 ok
        $stmt = $db->prepare("UPDATE MlangPM_MemberMoney SET TakingStyle = '반송', sendback = ?, sendback_date = ? WHERE PMThingOrderNo = ?");
        $stmt->bind_param("ssi", $cont, $date, $no);
        $stmt->execute();

        echo ("<script language='javascript'>
        alert('$no 번의 자료를 정상적으로 반송 처리하였습니다.');
        opener.parent.location.reload();
        window.self.close();
        </script>");
    } else {
        echo ("<script language='javascript'>window.alert('DataBase 에러 입니다.'); opener.parent.location.reload(); window.self.close();</script>");
        exit;
    }
    $stmt->close();
    $db->close();
    exit;
}

if ($mode == "delete") {
    include_once($dbPath);
    $db = new mysqli($host, $user, $password, $dataname);
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    $stmt = $db->prepare("DELETE FROM MlangOrder_PrintAuto WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();

    echo ("<script language='javascript'>
    alert('$no 번의 자료를 정상적으로 삭제하였습니다.');
    opener.parent.location.reload();
    window.self.close();
    </script>");

    $stmt->close();
    $db->close();
    exit;
}

if ($mode == "OrderStyleModify") {
    include_once($dbPath);
    $db = new mysqli($host, $user, $password, $dataname);
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    $stmt = $db->prepare("UPDATE MlangOrder_PrintAuto SET OrderStyle = ? WHERE no = ?");
    $stmt->bind_param("ii", $JK, $no);
    $stmt->execute();

    echo ("<script language='javascript'>
    alert('$no 번의 자료를 정상적으로 변경 처리하였습니다.');
    </script>
    <meta http-equiv='Refresh' content='0; URL=$_SERVER[PHP_SELF]'>");

    $stmt->close();
    $db->close();
    exit;
}
?>

<?php
$M123 = "..";
// include '../top.php';
?>

<head>
<style>
    /* Add your custom styles here */
    .coolBar {
        font-size: 10pt;
        background-color: #429EB2;
        color: #FFFFFF;
    }
    .coolBar td {
        padding: 8px;
    }
    .menu-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
<script>
function popUp(L, e) {
    if (n4) {
        var barron = document.layers[L];
        barron.left = e.pageX;
        barron.top = e.pageY + 5;
        barron.visibility = "visible";
    } else if (e4) {
        var barron = document.all[L];
        barron.style.pixelLeft = event.clientX + document.body.scrollLeft;
        barron.style.pixelTop = event.clientY + document.body.scrollTop + 5;
        barron.style.visibility = "visible";
    }
}
function popDown(L) {
    if (n4) document.layers[L].visibility = "hidden";
    else if (e4) document.all[L].style.visibility = "hidden";
}
var n4 = (document.layers) ? 1 : 0;
var e4 = (document.all) ? 1 : 0;
</script>

<script>
function allcheck(MemoPlusecheckForm) { 
    for(var i = 0; i < MemoPlusecheckForm.elements.length; i++) { 
        var check = MemoPlusecheckForm.elements[i]; 
        check.checked = true; 
    } 
    return; 
} 

function uncheck(MemoPlusecheckForm) { 
    for(var i = 0; i < MemoPlusecheckForm.elements.length; i++) { 
        var check = MemoPlusecheckForm.elements[i]; 
        check.checked = false; 
    } 
    return; 
} 

function DelGCheckField() {
    if (confirm('자료를 삭제처리 하시려 하십니다....\n\n한번 삭제한 자료는 복구되지 않으니 신중을 기해 주세요.............!!')) {
        document.MemoPlusecheckForm.action = "<?php echo  $_SERVER['PHP_SELF'] ?>";
        document.MemoPlusecheckForm.submit(); 
    } 
}
</script>

<SCRIPT LANGUAGE="JAVASCRIPT" src='../js/exchange.js'></SCRIPT>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr class="menu-container">
<td align=left>
<font color=red>*</font> 주문정보를 보시면 자동으로 접수완료로 처리됩니다.<BR>
<font color=red>*</font> 시안제출을 누르시면 시안 자료를 직접 올리실 수 있습니다.<BR>
<font color=red>*</font> 날짜로 검색 시 - 을 넣어 주셔야 합니다. (예: 2005-03-03 ~ 2006-11-21)<BR>
</td>
<td align=right><BR>
<input type='button' onClick="javascript:popup=window.open('admin.php?mode=OrderView', 'MViertWSubmitr','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' Order 신자료 입력'>
</td>
</tr>
<tr>
<td align=left colspan=2>
<?php $CateFF = "style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;' selected"; ?>
<table border=0 cellpadding=2 cellspacing=0 width=100%>
<tr>
<form method='post' name='TDsearch' OnSubmit='javascript:return TDsearchCheckField()' action='<?php echo  $_SERVER['PHP_SELF'] ?>'>
<td align=left>
<select name='Type'>
<option value='total'>전체</option>
<option value='inserted' <?php if($Type == "inserted") echo("$CateFF"); ?>>전단지</option>
<option value='sticker' <?php if($Type == "sticker") echo("$CateFF"); ?>>스티카</option>
<option value='NameCard' <?php if($Type == "NameCard") echo("$CateFF"); ?>>명함</option>
<option value='MerchandiseBond' <?php if($Type == "MerchandiseBond") echo("$CateFF"); ?>>상품권</option>
<option value='envelope' <?php if($Type == "envelope") echo("$CateFF"); ?>>봉투</option>
<option value='NcrFlambeau' <?php if($Type == "NcrFlambeau") echo("$CateFF"); ?>>양식지</option>
<option value='cadarok' <?php if($Type == "cadarok") echo("$CateFF"); ?>>리플렛</option>
<option value='cadarokTwo' <?php if($Type == "cadarokTwo") echo("$CateFF"); ?>>카다로그</option>
<option value='LittlePrint' <?php if($Type == "LittlePrint") echo("$CateFF"); ?>>소량인쇄</option>
</select>
<select name='Cate'>
<option value='name' <?php if($Cate == "name") echo("$CateFF"); ?>>상호/성명</option>
<option value='phone' <?php if($Cate == "phone") echo("$CateFF"); ?>>전화번호</option>
<option value='Hendphone' <?php if($Cate == "Hendphone") echo("$CateFF"); ?>>휴대폰</option>
<option value='bizname' <?php if($Cate == "bizname") echo("$CateFF"); ?>>인쇄내용</option>
<option value='OrderStyle' <?php if($Cate == "OrderStyle") echo("$CateFF"); ?>>진행상태</option>
</select>
&nbsp;날짜검색 :&nbsp;
<input type='text' name='YearOne' size='14' onClick="Calendar(this);">
~
<input type='text' name='YearTwo' size='14' onClick="Calendar(this);">
&nbsp;&nbsp;<b>검색어 :&nbsp;</b>
<input type='text' name='TDsearchValue' size='45'>
<input type='submit' value=' 검 색 '>
<?php if($Type) { ?>
<input type='button' onClick="javascript:window.location='<?php echo  $_SERVER['PHP_SELF'] ?>';" value='처음으로..'>
<?php } ?>
</td>
</form>
</tr>
</table>
</td>
</tr>
</table>

<!------------------------------------------- 리스트 시작----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>번호</td>
<td align=center>분야</td>
<td align=center>주문인성함</td>
<td align=center>주문날짜</td>
<td align=center>결과처리</td>
<td align=center>시안</td>
<td align=center>주문자정보</td>
</tr>

<form method='post' name='MemoPlusecheckForm'>
<INPUT TYPE="hidden" name='mode' value='ChickBoxAll'>

<?php
$db = new mysqli($host, $user, $password, $dataname);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$table = "MlangOrder_PrintAuto";
$Mlang_query = "";

if ($Type) {
    if (!$YearTwo) {
        $msg = "날짜 검색을 하시려면 ~ 이전의 값을 입력해 주셔야 합니다.";
        echo "<script>alert('$msg');</script>";
    }

    if ($YearTwo) {
        if (!$YearOne) {
            $msg = "날짜 검색을 하시려면 ~ 이후의 값을 입력해 주셔야 합니다.";
            echo "<script>alert('$msg');</script>";
        }
    }

    if ($Type == "total") {
        $TypeOk = "";
    } else {
        $TypeOk = "and Type='$Type'";
    }

    if ($YearOne || $YearTwo) {
        $YearOneOk = $YearOne." 00:00:00";
        $YearTwoOk = $YearTwo." 00:00:00";
        $Mlang_query = "select * from $table where date > '$YearOneOk' and date < '$YearTwoOk' $TypeOk and $Cate like '%$TDsearchValue%'";
    } else {
        $Mlang_query = "select * from $table where $Cate like '%$TDsearchValue%' $TypeOk";
    }

} else {  
    $Mlang_query = "select * from $table";
}

$query = $db->query($Mlang_query);
$recordsu = $query->num_rows;
$total = $db->affected_rows;

$listcut = 20;
if (!$offset) $offset = 0;

if ($CountWW) {
    $result = $db->query("$Mlang_query order by $CountWW $s limit $offset, $listcut");
} else {
    $result = $db->query("$Mlang_query order by NO desc limit $offset, $listcut");
}

$rows = $result->num_rows;
if ($rows) {
    while ($row = $result->fetch_assoc()) {
?>
<tr bgcolor='#575757'>
<td align=center>
&nbsp;
<?php if ($row['OrderStyle'] != "5") { ?>
<input type=checkbox name=check[] value='<?php echo  $row['no'] ?>'>
<?php } ?>
<font color=white><?php echo  $row['no'] ?></font>
&nbsp;
</td>
<td align=center><font color=white>
<?php
switch ($row['Type']) {
    case "inserted":
        echo "전단지";
        break;
    case "sticker":
        echo "스티카";
        break;
    case "NameCard":
        echo "명함";
        break;
    case "MerchandiseBond":
        echo "상품권";
        break;
    case "envelope":
        echo "봉투";
        break;
    case "NcrFlambeau":
        echo "양식지";
        break;
    case "cadarok":
        echo "리플렛";
        break;
    case "cadarokTwo":
        echo "카다로그";
        break;
    case "LittlePrint":
        echo "소량인쇄";
        break;
    default:
        echo $row['Type'];
}
?>
</font></td>
<td align=center><font color=white><?php echo  htmlspecialchars($row['name']) ?></font></td>
<td align=center><font color=white><?php echo  htmlspecialchars($row['date']) ?></font></td>
<td align=center>
<script>
function MM_jumpMenuYY_<?php echo  $row['no'] ?>G(targ,selObj,restore) {
    eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
    if (restore) selObj.selectedIndex=0;
}
</script>
<select onChange="MM_jumpMenuYY_<?php echo  $row['no'] ?>G('parent',this,0)">
<option value='<?php echo  $_SERVER['PHP_SELF'] ?>?mode=OrderStyleModify&JK=1&no=<?php echo  $row['no'] ?>' <?php if($row['OrderStyle'] == "1") echo("selected style='font-size:10pt; background-color:#6600FF; color:#FFFFFF;'"); ?>>견적접수</option>
<option value='<?php echo  $_SERVER['PHP_SELF'] ?>?mode=OrderStyleModify&JK=2&no=<?php echo  $row['no'] ?>' <?php if($row['OrderStyle'] == "2") echo("selected style='font-size:10pt; background-color:#6600FF; color:#FFFFFF;'"); ?>>주문접수</option>
<option value='<?php echo  $_SERVER['PHP_SELF'] ?>?mode=OrderStyleModify&JK=3&no=<?php echo  $row['no'] ?>' <?php if($row['OrderStyle'] == "3") echo("selected style='font-size:10pt; background-color:#6633CC; color:#FFFFFF;'"); ?>>접수완료</option>
<option value='<?php echo  $_SERVER['PHP_SELF'] ?>?mode=OrderStyleModify&JK=4&no=<?php echo  $row['no'] ?>' <?php if($row['OrderStyle'] == "4") echo("selected style='font-size:10pt; background-color:#CC0066; color:#FFFFFF;'"); ?>>입금대기</option>
<option value='<?php echo  $_SERVER['PHP_SELF'] ?>?mode=OrderStyleModify&JK=5&no=<?php echo  $row['no'] ?>' <?php if($row['OrderStyle'] == "5") echo("selected style='font-size:10pt; background-color:#993333; color:#FFFFFF;'"); ?>>시안제작중</option>
<option value='<?php echo  $_SERVER['PHP_SELF'] ?>?mode=OrderStyleModify&JK=6&no=<?php echo  $row['no'] ?>' <?php if($row['OrderStyle'] == "6") echo("selected style='font-size:10pt; background-color:#333300; color:#FFFFFF;'"); ?>>시안</option>
<option value='<?php echo  $_SERVER['PHP_SELF'] ?>?mode=OrderStyleModify&JK=7&no=<?php echo  $row['no'] ?>' <?php if($row['OrderStyle'] == "7") echo("selected style='font-size:10pt; background-color:#336600; color:#FFFFFF;'"); ?>>교정</option>
<option value='<?php echo  $_SERVER['PHP_SELF'] ?>?mode=OrderStyleModify&JK=8&no=<?php echo  $row['no'] ?>' <?php if($row['OrderStyle'] == "8") echo("selected style='font-size:10pt; background-color:#000000; color:#FFFFFF;'"); ?>>작업완료</option>
<option value='<?php echo  $_SERVER['PHP_SELF'] ?>?mode=OrderStyleModify&JK=9&no=<?php echo  $row['no'] ?>' <?php if($row['OrderStyle'] == "9") echo("selected style='font-size:10pt; background-color:#333399; color:#FFFFFF;'"); ?>>작업중</option>
<option value='<?php echo  $_SERVER['PHP_SELF'] ?>?mode=OrderStyleModify&JK=10&no=<?php echo  $row['no'] ?>' <?php if($row['OrderStyle'] == "10") echo("selected style='font-size:10pt; background-color:#660000; color:#FFFFFF;'"); ?>>교정작업중</option>
</select>
</td>
<td align=center>
<?php $modifyCode = $row['ThingCate'] ? "&ModifyCode=ok" : ""; ?>
<input type='button' onClick="javascript:popup=window.open('admin.php?mode=SinForm&coe&no=<?php echo  $row['no'] ?><?php echo  $modifyCode ?>', 'SinHH','width=600,height=200,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='교정/시안 <?php echo  $row['ThingCate'] ? "수정" : "등록" ?>'>
</td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('admin.php?mode=OrderView&no=<?php echo  $row['no'] ?>', 'MViertW','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='주문정보보기'>
</td>
</tr>
<?php
    }
} else {
    if ($TDsearchValue) {
        echo "<tr><td colspan=10><p align=center><BR><BR>$TDsearch 로 검색되는 $TDsearchValue - 관련 검색 자료 없음</p></td></tr>";
    } elseif ($OrderCate) {
        echo "<tr><td colspan=10><p align=center><BR><BR>$cate 로 검색되는 - 관련 검색 자료 없음</p></td></tr>";
    } else {
        echo "<tr><td colspan=10><p align=center><BR><BR>등록 자료 없음</p></td></tr>";
    }
}
?>
<tr><td colspan=12 height=10></td></tr>
</table>

<table border=0 align=center width=100% cellpadding=0 cellspacing=0>
<tr>
<td>
<input type='button' onClick="javascript:allcheck(MemoPlusecheckForm);" value=' 전 체 선 택 '>
<input type='button' onClick="javascript:uncheck(MemoPlusecheckForm);" value=' 선 택 해 제 '>
<input type='button' onClick="javascript:DelGCheckField();" value=' 체크항목 삭 제 '>
</td>
</tr>
</form>
</table>

<p align='center'>
<?php
if ($rows) {
    if ($TDsearchValue) {
        $mlang_pagego = "TDsearch=$TDsearch&TDsearchValue=$TDsearchValue";
    } elseif (isset($OrderStyleYU9OK)) {
        $mlang_pagego = "OrderStyleYU9OK=$OrderStyleYU9OK";
    } elseif (isset($OrderCate)) {
        $mlang_pagego = "OrderCate=$OrderCate";
    } else {
        $mlang_pagego = "";
    }

    $pagecut = 7;
    $one_bbs = $listcut * $pagecut;
    $start_offset = intval($offset / $one_bbs) * $one_bbs;
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs;
    $start_page = intval($start_offset / $listcut) + 1;
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

    if ($start_offset != 0) {
        $apoffset = $start_offset - $one_bbs;
        echo "<a href='$_SERVER[PHP_SELF]?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;";
    }

    for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
        $newoffset = ($i - 1) * $listcut;
        if ($offset != $newoffset) {
            echo "&nbsp;<a href='$_SERVER[PHP_SELF]?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
        } else {
            echo "&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;";
        }
        if ($i == $end_page) break;
    }

    if ($start_offset != $end_offset) {
        $nextoffset = $start_offset + $one_bbs;
        echo "&nbsp;<a href='$_SERVER[PHP_SELF]?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
    }
    echo "총목록갯수: $end_page 개";
}

$db->close();
?>
</p>
<!------------------------------------------- 리스트 끝----------------------------------------->

<?php
include(__DIR__ . '/../down.php');
?>

<!-- <script>
document.querySelectorAll("input[type='button']").forEach(button => {
    button.addEventListener("click", function() {
        const popup = window.open(this.getAttribute('onClick').match(/'(.*?)'/)[1], 'popup', 'width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');
        popup.focus();
        const interval = setInterval(() => {
            if (popup.document.body) {
                popup.document.body.style.backgroundColor = '#F5F5F5'; // 엷은 회색 배경색
                clearInterval(interval);
            }
        }, 100);
    });
});
</script> -->

