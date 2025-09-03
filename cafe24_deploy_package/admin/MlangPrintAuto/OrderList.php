<?php
// 보안 상수 정의 후 데이터베이스 연결
include "../../includes/db_constants.php";
include "../../db.php";

// db.php에서 생성된 $db 연결을 사용
$mysqli = $db;
if (!$mysqli) {
    die("Connection failed: Database connection not established");
}

$mode = $_POST['mode'] ?? $_GET['mode'] ?? null;
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
$JK = isset($_GET['JK']) ? $_GET['JK'] : ''; // GET 방식으로 전달되는 경우
$PHP_SELF   = $_SERVER['PHP_SELF'] ?? '';

if ($mode === "ChickBoxAll") {
    $check = $_POST['check'] ?? [];

    if (empty($check)) {
        echo "<script>
            alert('삭제 [처리]할 체크항목이 없습니다.\\n\\n[삭제] 처리할 것을 체크하여 주십시요.');
            history.go(-1);
        </script>";
        exit;
    }

    foreach ($check as $id) {
        $id = intval($id);
        $stmt = $mysqli->prepare("DELETE FROM MlangOrder_PrintAuto WHERE no = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    $mysqli->close();

    echo "<script>
        alert('체크한 항목을 정상적으로 [삭제] 처리 하였습니다.');
        location.href = '$PHP_SELF';
    </script>";
    exit;
}

// 반송 입력 폼
if ($mode === "sendback") {
    $no = intval($_GET['no'] ?? 0);
    ?>
    <head>
    <script src="../js/coolbar.js"></script>
    <script>
        window.moveTo(screen.width / 5, screen.height / 5);
        function MemberCheckField() {
            const f = document.FrmUserInfo;
            if (f.cont.value.trim() === "") {
                alert("반송이유를 입력해 주세요.");
                f.cont.focus();
                return false;
            }
            return true;
        }
    </script>
    </head>

    <body class='coolBar'>
    <form name='FrmUserInfo' method='post' onsubmit='return MemberCheckField()' action='<?php echo  $PHP_SELF ?>'>
        <input type='hidden' name='mode' value='sendback_ok'>
        <input type='hidden' name='no' value='<?php echo  $no ?>'>
        <table align='center' cellpadding='10' cellspacing='5' width='100%'>
            <tr><td bgcolor='#336699'>
                <font style='font-size:11pt; color:#fff;'>
                    반송 이유(송장번호 등)를 입력해 주세요.<br>
                    <span style='font-size:9pt; color:red;'>* 반송 처리 시 회원 적립금에서 자동 차감됩니다.</span>
                </font>
            </td></tr>
            <tr><td>
                <input type='text' name='cont' size='50'>
                <input type='submit' value='처리하기'>
            </td></tr>
        </table>
    </form>
    </body>
    </html>
    <?php
    exit;
}

// 반송 처리 실행
if ($mode === "sendback_ok") {
    $no = intval($_POST['no'] ?? 0);
    $cont = trim($_POST['cont'] ?? '');
    $date = date("Y-m-d H:i:s");

    if (!$no || $cont === '') {
        echo "<script>alert('잘못된 접근입니다.'); window.close();</script>";
        exit;
    }

    // 주문 정보 확인
    $stmt = $mysqli->prepare("SELECT PMmember FROM MlangOrder_PrintAuto WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if (!$row) {
        echo "<script>alert('주문 정보를 찾을 수 없습니다.'); window.close();</script>";
        exit;
    }
    $pmMember = $row['PMmember'];
    $stmt->close();

    // 적립금 정보 조회
    $stmt = $mysqli->prepare("SELECT no, TotalMoney FROM MlangPM_MemberTotalMoney WHERE id = ? ORDER BY no DESC LIMIT 1");
    $stmt->bind_param("s", $pmMember);
    $stmt->execute();
    $result = $stmt->get_result();
    $memberTotal = $result->fetch_assoc();
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT Money_2 FROM MlangPM_MemberMoney WHERE PMThingOrderNo = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $result = $stmt->get_result();
    $memberMoney = $result->fetch_assoc();
    $stmt->close();

    if (!$memberTotal || !$memberMoney) {
        echo "<script>alert('적립금 정보를 확인할 수 없습니다.'); window.close();</script>";
        exit;
    }

    $newTotal = $memberTotal['TotalMoney'] - $memberMoney['Money_2'];

    // 적립금 차감
    $stmt = $mysqli->prepare("UPDATE MlangPM_MemberTotalMoney SET TotalMoney = ? WHERE no = ?");
    $stmt->bind_param("di", $newTotal, $memberTotal['no']);
    $stmt->execute();
    $stmt->close();

    // 주문 반송 처리
    $stmt = $mysqli->prepare("UPDATE MlangOrder_PrintAuto SET OrderStyle = 6 WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $stmt->close();

    // 적립금 로그 기록
    $stmt = $mysqli->prepare("UPDATE MlangPM_MemberMoney SET TakingStyle = '반송', sendback = ?, sendback_date = ? WHERE PMThingOrderNo = ?");
    $stmt->bind_param("ssi", $cont, $date, $no);
    $stmt->execute();
    $stmt->close();

    $mysqli->close();

    echo "<script>
        alert('$no 번의 자료를 정상적으로 반송 처리하였습니다.');
        opener.parent.location.reload();
        window.close();
    </script>";
    exit;
}
?>
<?php

$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$JK = $_POST['JK'] ?? '';

include "../../db.php";

if ($mode === "delete") {
    $no = intval($no);
    $stmt = $mysqli->prepare("DELETE FROM MlangOrder_PrintAuto WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('$no 번의 자료를 정상적으로 삭제하였습니다.');opener.parent.location.reload();window.close();</script>";
    exit;
}

if ($mode === "OrderStyleModify") {
    $no = intval($no);
    $stmt = $mysqli->prepare("UPDATE MlangOrder_PrintAuto SET OrderStyle=? WHERE no=?");
    $stmt->bind_param("si", $JK, $no);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('$no 번의 자료를 정상적으로 변경 처리하였습니다.');</script><meta http-equiv='Refresh' content='0; URL=$PHP_SELF'>";
    exit;
}

$M123 = "..";
include "../top.php";
?>

<head>
<script>
function popUp(L, e) {
    if (document.layers) {
        var barron = document.layers[L];
        barron.left = e.pageX;
        barron.top = e.pageY + 5;
        barron.visibility = "visible";
    } else if (document.all) {
        var barron = document.all[L];
        barron.style.left = event.clientX + document.body.scrollLeft + 'px';
        barron.style.top = event.clientY + document.body.scrollTop + 5 + 'px';
        barron.style.visibility = "visible";
    }
}

function popDown(L) {
    if (document.layers) document.layers[L].visibility = "hidden";
    else if (document.all) document.all[L].style.visibility = "hidden";
}

function allcheck(form) {
    for (var i = 0; i < form.elements.length; i++) {
        var check = form.elements[i];
        if (check.type === 'checkbox') check.checked = true;
    }
}

function uncheck(form) {
    for (var i = 0; i < form.elements.length; i++) {
        var check = form.elements[i];
        if (check.type === 'checkbox') check.checked = false;
    }
}

function DelGCheckField() {
    if (confirm('자료를 삭제처리 하시겠습니까?\n\n한번 삭제한 자료는 복구되지 않으니 신중히 결정해 주세요.')) {
        document.MemoPlusecheckForm.action = "<?php echo  $PHP_SELF ?>";
        document.MemoPlusecheckForm.submit();
    }
}
</script>
<script src='../js/exchange.js'></script>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left>
<font color=red>*</font> 주문정보를 보시면 자동으로 접수완료로 처리 됩니다.<br>
<font color=red>*</font> 시안제출 을 누르시면 시안 자료를 직접 올리실수 있습니다.<br>
<font color=red>*</font> 날짜로 검색시 - 을 넣어주셔야 합니다. ( 예: 2005-03-03 ~ 2006-11-21 )<br>
</td>
<td align=right><br>
<input type='button' onClick="window.open('admin.php?mode=OrderView', 'MViertWSubmitr','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');" value=' Order 신자료 입력'>
</td>
</tr>
<tr>
<td align=left colspan=2>
<?php $CateFF = "style='font-size:11pt; background-color:#429EB2; color:#FFFFFF;' selected"; ?>
<table border=0 cellpadding=2 cellspacing=0 width=100%>
<tr>
<form method='post' name='TDsearch' onsubmit='return TDsearchCheckField()' action='<?php echo  $PHP_SELF ?>'>
<td align=left>
<select name='Type'>
<option value='total'>전체</option>
<option value='inserted' <?php echo  $Type == "inserted" ? $CateFF : "" ?>>전단지</option>
<option value='sticker' <?php echo  $Type == "sticker" ? $CateFF : "" ?>>스티카</option>
<option value='NameCard' <?php echo  $Type == "NameCard" ? $CateFF : "" ?>>명함</option>
<option value='MerchandiseBond' <?php echo  $Type == "MerchandiseBond" ? $CateFF : "" ?>>상품권</option>
<option value='envelope' <?php echo  $Type == "envelope" ? $CateFF : "" ?>>봉투</option>
<option value='NcrFlambeau' <?php echo  $Type == "NcrFlambeau" ? $CateFF : "" ?>>양식지</option>
<option value='cadarok' <?php echo  $Type == "cadarok" ? $CateFF : "" ?>>리플렛</option>
<option value='cadarokTwo' <?php echo  $Type == "cadarokTwo" ? $CateFF : "" ?>>카다로그</option>
<option value='LittlePrint' <?php echo  $Type == "LittlePrint" ? $CateFF : "" ?>>소량인쇄</option>
</select>
<select name='Cate'>
<option value='name' <?php echo  $Cate == "name" ? $CateFF : "" ?>>상호/성명</option>
<option value='phone' <?php echo  $Cate == "phone" ? $CateFF : "" ?>>전화번호</option>
<option value='Hendphone' <?php echo  $Cate == "Hendphone" ? $CateFF : "" ?>>휴대폰</option>
<option value='bizname' <?php echo  $Cate == "bizname" ? $CateFF : "" ?>>인쇄내용</option>
<option value='OrderStyle' <?php echo  $Cate == "OrderStyle" ? $CateFF : "" ?>>진행상태</option>
</select>
&nbsp;날짜검색 :&nbsp;
<input type='text' name='YearOne' size='14' onclick="Calendar(this);"> ~ <input type='text' name='YearTwo' size='14' onclick="Calendar(this);">
&nbsp;&nbsp;<b>검색어 :&nbsp;</b>
<input type='text' name='TDsearchValue' size='45'>
<input type='submit' value=' 검 색 '>
<?php if ($Type) { ?>
<input type='button' onClick="window.location='<?php echo  $PHP_SELF ?>';" value='처음으로..'>
<?php } ?>
</td>
</form>
</tr>
</table>
</td>
</tr>
</table>

<!-- 리스트 시작 -->
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
<input type='hidden' name='mode' value='ChickBoxAll'>
<input type='hidden' name='Ttable' value='<?php echo $TIO_CODE?>'> 
<input type='hidden' name='Cate' value='<?php echo $Cate?>'>
<input type='hidden' name='Type' value='<?php echo $Type?>'>
<input type='hidden' name='YearOne' value='<?php echo $YearOne?>'>
<input type='hidden' name='YearTwo' value='<?php echo $YearTwo?>'>

<?php
function Error($msg) {
  echo ("<script language=javascript>
  window.alert('$msg');
  history.go(-1);
  </script>");
  exit;
}
?>
<?php
$offset = $_GET['offset'] ?? $_POST['offset'] ?? 0;	
include "../../db.php";
$table = "MlangOrder_PrintAuto";

if ($Type) {
  if ($YearOne && !$YearTwo) {
    $msg = "날짜 검색을 하시려면  ~ 이전 의 값을 입력해 주셔야 합니다.";
    Error($msg);
  }
  if ($YearTwo && !$YearOne) {
    $msg = "날짜 검색을 하시려면  ~ 이후 의 값을 입력해 주셔야 합니다.";
    Error($msg);
  }

  $TypeOk = ($Type == "total") ? "" : "and Type='$Type'";

  if ($YearOne || $YearTwo) {
    $YearOneOk = $YearOne . " 00:00:00";
    $YearTwoOk = $YearTwo . " 00:00:00";
    $Mlang_query = "select * from $table where date > '$YearOneOk' and date < '$YearTwoOk' $TypeOk and $Cate like '%$TDsearchValue%'";
  } else {
    $Mlang_query = "select * from $table where $Cate like '%$TDsearchValue%' $TypeOk";
  }
} else {
  $Mlang_query = "select * from $table";
}

$query = mysqli_query($db, "$Mlang_query");
$recordsu = mysqli_num_rows($query);
$total = mysqli_affected_rows($db);

$listcut = 20;
if (!$offset) $offset = 0;

if ($CountWW) {
  $result = mysqli_query($db, "$Mlang_query order by $CountWW $s limit $offset,$listcut");
} else {
  $result = mysqli_query($db, "$Mlang_query order by NO desc limit $offset,$listcut");
}

$rows = mysqli_num_rows($result);
if ($rows) {
  while ($row = mysqli_fetch_array($result)) {
?>
<tr bgcolor='#575757'>
<td align=center>
&nbsp;
<?php if ($row["OrderStyle"] == "5") {} else { ?>
<input type=checkbox name=check[] value='<?php echo  $row["no"] ?>'>
<?php } ?>
<font color=white><?php echo  $row["no"] ?></font>
&nbsp;
</td>
<td align=center><font color=white>
<?php
switch ($row["Type"]) {
  case "inserted": echo "전단지"; break;
  case "sticker": echo "스티카"; break;
  case "NameCard": echo "명함"; break;
  case "MerchandiseBond": echo "상품권"; break;
  case "envelope": echo "봉투"; break;
  case "NcrFlambeau": echo "양식지"; break;
  case "cadarok": echo "리플렛"; break;
  case "cadarokTwo": echo "카다로그"; break;
  case "LittlePrint": echo "소량인쇄"; break;
  default: echo $row["Type"];
}
?>
</font></td>
<td align=center><font color=white><?php echo  htmlspecialchars($row["name"]) ?></font></td>
<td align=center><font color=white><?php echo  htmlspecialchars($row["date"]) ?></font></td>
<td align=center>
<script>
function MM_jumpMenuYY_<?php echo  $row["no"] ?>G(targ,selObj,restore){
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>
<select onChange="MM_jumpMenuYY_<?php echo  $row["no"] ?>G('parent',this,0)">
<?php
$orderStyles = [
  1 => "견적접수", 2 => "주문접수", 3 => "접수완료", 4 => "입금대기",
  5 => "시안제작중", 6 => "시안", 7 => "교정", 8 => "작업완료",
  9 => "작업중", 10 => "교정작업중"
];
foreach ($orderStyles as $key => $label) {
  $selected = ($row["OrderStyle"] == $key) ? "selected style='font-size:11pt; background-color:#6600FF; color:#FFFFFF;'" : "";
  echo "<option value='$PHP_SELF?mode=OrderStyleModify&JK=$key&no={$row['no']}' $selected>$label</option>";
}
?>
</select>
</td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('admin.php?mode=SinForm&coe&no=<?php echo  $row['no'] ?><?php if ($row['ThingCate']) { ?>&ModifyCode=ok<?php } ?>', 'SinHH','width=600,height=100,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='교정/시안 <?php if ($row['ThingCate']) { ?>수정<?php } else { ?>등록<?php } ?>'>
</td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('admin.php?mode=OrderView&no=<?php echo  $row['no'] ?>', 'MViertW','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='주문정보보기'>
</td>
</tr>
<?php
$i = 0;
$i = $i + 1;
  }
} else {
  if ($TDsearchValue) {
    echo "<tr><td colspan=10><p align=center><br><br>$Cate 로 검색되는 $TDsearchValue - 관련 검색 자료없음</p></td></tr>";
  } elseif ($OrderCate) {
    echo "<tr><td colspan=10><p align=center><br><br>$cate 로 검색되는 - 관련 검색 자료없음</p></td></tr>";
  } else {
    echo "<tr><td colspan=10><p align=center><br><br>등록 자료없음</p></td></tr>";
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
$mlang_pagego = isset($_POST['mlang_pagego']) ? $_POST['mlang_pagego'] : '';
$OrderCate = isset($_POST['OrderCate']) ? $_POST['OrderCate'] : '';
$OrderStyleYU9OK = isset($_POST['OrderStyleYU9OK']) ? $_POST['OrderStyleYU9OK'] : '';
if($rows){

if($TDsearchValue){
$mlang_pagego="Cate=$Cate&TDsearchValue=$TDsearchValue"; // 필드속성들 전달값
}else if($OrderStyleYU9OK){
$mlang_pagego="OrderStyleYU9OK=$OrderStyleYU9OK"; // 필드속성들 전달값
}else if($OrderCate){
$mlang_pagego="OrderCate=$OrderCate"; // 필드속성들 전달값
}else{}

$pagecut= 7;  //한 장당 보여줄 페이지수 
$one_bbs= $listcut*$pagecut;  //한 장당 실을 수 있는 목록(게시물)수 
$start_offset= intval($offset/$one_bbs)*$one_bbs;  //각 장에 처음 페이지의 $offset값. 
$end_offset= intval($recordsu/$one_bbs)*$one_bbs;  //마지막 장의 첫페이지의 $offset값. 
$start_page= intval($start_offset/$listcut)+1; //각 장에 처음 페이지의 값. 
$end_page= ($recordsu%$listcut>0)? intval($recordsu/$listcut)+1: intval($recordsu/$listcut); 
//마지막 장의 끝 페이지. 
if($start_offset!= 0) 
{ 
  $apoffset= $start_offset- $one_bbs; 
  echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;"; 
} 

for($i= $start_page; $i< $start_page+$pagecut; $i++) 
{ 
$newoffset= ($i-1)*$listcut; 

if($offset!= $newoffset){
  echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;"; 
}else{echo("&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;"); } 

if($i==$end_page) break; 
} 

if($start_offset!= $end_offset) 
{ 
  $nextoffset= $start_offset+ $one_bbs; 
  echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>"; 
} 
echo "총목록갯수: $end_page 개"; 

}

mysqli_close($db); 
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->

<?php
include "../down.php";
?>