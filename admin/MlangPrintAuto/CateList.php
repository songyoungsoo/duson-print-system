<?php

function getTtableTitle($code) {
    $titles = [
        "inserted" => "전단지",
        "namecard" => "명함",
        "cadarok" => "리플렛",
        "msticker" => "스티커",
        "merchandisebond" => "상품권",
        "envelope" => "봉투",
        "ncrflambeau" => "양식지",
        "littleprint" => "소량인쇄",
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
include "../../mlangprintauto/ConDb.php";
$GGTABLE = $TABLE; // This is "mlangprintauto_transactioncate"

// $ToTitle == "전단지" ? $View_TtableC = "전단지" : $View_TtableC = $ToTitle;
// $TtableTitles = [
//   "inserted" => ["전단지", "스티카", "명함"],
//   "msticker" => ["스티카", "전단지", "명함"],
//   "namecard" => ["명함", "스티카", "전단지"],
//   "merchandisebond" => ["상품권", "스티카", "전단지"],
//   "envelope" => ["봉투", "스티카", "전단지"],
//   "ncrflambeau" => ["양식지", "스티카", "전단지"],
//   "cadarok" => ["리플렛", "스티카", "전단지"],
//   "littleprint" => ["소량인쇄", "스티카", "전단지"],
//   "cadarokTwo" => ["카다로그", "스티카", "전단지"]
// ];

// 변수 초기화 (방지용)
$ACate = $_GET['ACate'] ?? null;
$ATreeNo = $_GET['ATreeNo'] ?? null;
$Ttable = $_GET['Ttable'] ?? null;
$offset = $_GET['offset'] ?? 0;
$Cate = $_GET['Cate'] ?? null;
$search = $_GET['search'] ?? null;
$TreeSelect = $_GET['TreeSelect'] ?? null;
$PHP_SELF = htmlspecialchars($_SERVER['PHP_SELF'] ?? '');
$no = isset($_GET['no']) ? (int)$_GET['no'] : (isset($_POST['no']) ? (int)$_POST['no'] : 0);

// 예시로 설정된 값들 (정확한 값은 기존 코드에 맞게 조정 필요)
$View_TtableB = $Ttable;

$View_TtableC = getTtableTitle($Ttable); // 이건 실제 테이블 한글명이라면 따로 정의 필요
$PageCode = "Category";
$DF_Tatle_1 = $TtableTitles[$Ttable][0] ?? '';
$DF_Tatle_2 = $TtableTitles[$Ttable][1] ?? '';
$DF_Tatle_3 = $TtableTitles[$Ttable][2] ?? '';
include "CateAdmin_title.php";
?>

<head>
<script>
self.moveTo(0, 0);
self.resizeTo(window.screen.availWidth, window.screen.availHeight);

function clearField(field) {
  if (field.value === field.defaultValue) field.value = "";
}

function checkField(field) {
  if (!field.value) field.value = field.defaultValue;
}

function WebOffice_customer_Del(no) {
  if (confirm(no + '번 자료를 삭제 하시겠습니까..?\n\n최상위일 경우 하위 항목까지 삭제됩니다.\n\n복구가 불가능하니 신중히 진행해주세요.')) {
    let str = './CateAdmin.php?no=' + no + '&mode=delete';
    let popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
    popup.document.location.href = str;
    popup.focus();
  }
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<?php
include "../../db.php";

if ($ACate) {
  $Mlang_query = "SELECT * FROM $GGTABLE WHERE Ttable='$View_TtableB' AND BigNo='$ACate'";
} elseif ($ATreeNo) {
  $Mlang_query = "SELECT * FROM $GGTABLE WHERE Ttable='$View_TtableB' AND TreeNo='$ATreeNo'";
} else {
  $Mlang_query = "SELECT * FROM $GGTABLE WHERE Ttable='$View_TtableB'";
}

$query = mysqli_query($db, $Mlang_query);
$recordsu = mysqli_num_rows($query);
$listcut = 30;
$offset = $offset ?? 0;
?>

<table border=0 align=center width=100% cellpadding='5' cellspacing='3' class='coolBar'>
<tr>
<td align=left colspan=2>

(<b><?php echo $View_TtableC?></b>) CATEGORY LIST<BR>
* 상위 CATEGORY는 최상 분야를 의미합니다. (예: <?php echo $View_TtableC?> >> 수입명함 >> TITLE)
<?php if ($TreeSelect == "ok") { ?><br>* 3단 CATEGORY는 선택 시 TITLE과 함께 종이종류도 호출됩니다.<?php } ?>
</td>
</tr>
<tr>
<td>
  <table border=0 width=100% cellpadding=0 cellspacing=0>
    <tr>
      <td align=left>
        <script>
        function MM_jumpMenu(targ,selObj,restore){
          eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
          if (restore) selObj.selectedIndex=0;
        }
        </script>

        <select onChange="MM_jumpMenu('parent',this,0)">
        <option value='<?php echo $PHP_SELF?>?Ttable=<?php echo $Ttable?>'>→ 전체자료</option>
        <?php
        $Cate_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$Ttable' AND BigNo='0'");
        while ($Cate_row = mysqli_fetch_assoc($Cate_result)) {
          $selected = ($ACate == $Cate_row['no']) ? "style='background-color:#429EB2; color:#FFFFFF;' selected" : "";
          echo "<option value='$PHP_SELF?ACate={$Cate_row['no']}&Ttable=$Ttable' $selected>{$Cate_row['title']}-($DF_Tatle_2)</option>";

          $Sub_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE TreeNo='{$Cate_row['no']}'");
          $Sub_row = mysqli_fetch_assoc($Sub_result);

          if ($Sub_row['TreeNo']) {
            $selectedTree = ($ATreeNo == $Cate_row['no']) ? "style='background-color:#429EB2; color:#FFFFFF;' selected" : "";
            echo "<option value='$PHP_SELF?ATreeNo={$Sub_row['TreeNo']}&Ttable=$Ttable' $selectedTree>{$Cate_row['title']}-($DF_Tatle_3)</option>";
          }
        }
        ?>
        </select>
      </td>
    </tr>
  </table>
</td>
<td align=right valign=bottom>
  <?php include "CateList_Title.php"; ?>
</td>
</tr>
</table>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>등록NO</td>
<td align=center>상위CATEGORY(번호)</td>
<td align=center>TITLE</td>
<td align=center>관리기능</td>
</tr>
<?php
$result = mysqli_query($db, "$Mlang_query ORDER BY NO DESC LIMIT $offset, $listcut");
$rows = mysqli_num_rows($result);

if ($rows) {
  while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr bgcolor='#575757'>
    <td align=center><font color=white>{$row['no']}</font></td>
    <td><font color=white>";
    if ($row['TreeNo']) {
      $BigNo_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$View_TtableB' AND no='{$row['TreeNo']}'");
    } else {
      $BigNo_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$View_TtableB' AND no='{$row['BigNo']}'");
    }
    $BigNo_row = mysqli_fetch_assoc($BigNo_result);
    if ($BigNo_row) echo $BigNo_row['title'];

    echo "</font><font color=#A2A2A2>(";
    if ($row['BigNo'] == "0") echo $DF_Tatle_1;
    if ($row['TreeNo']) echo $DF_Tatle_3;
    if ($row['BigNo']) echo $DF_Tatle_2;
    echo ")</font></td>
    <td><font color=white>{$row['title']}</font></td>
    <td align=center>
    <input type='button' onClick=\"popup=window.open('./CateAdmin.php?mode=form&code=modify&no={$row['no']}&Ttable=$Ttable";
    if ($row['TreeNo']) echo "&TreeSelect=2";
    elseif ($row['BigNo'] != "0") echo "&TreeSelect=1";
    if ($Cate) echo "&Cate=$Cate";
    if ($ATreeNo) echo "&ATreeNo=$ATreeNo";
    if ($ACate) echo "&ACate=$ACate";
    echo "', 'WebOffice_{$PageCode}Modify','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();\" value=' 수정 '>
    <input type='button' onClick=\"WebOffice_customer_Del('{$row['no']}');\" value=' 삭제 '>
    </td>
    </tr>";
  }
} else {
  echo "<tr><td colspan=10><p align=center><br><br>등록 자료없음</p></td></tr>";
}
?>
</table>

<p align='center'>
<?php
if ($rows) {
  $mlang_pagego = "ACate=$ACate&Ttable=$Ttable&ATreeNo=$ATreeNo";
  $pagecut = 7;
  $one_bbs = $listcut * $pagecut;
  $start_offset = intval($offset / $one_bbs) * $one_bbs;
  $end_offset = intval($recordsu / $one_bbs) * $one_bbs;
  $start_page = intval($start_offset / $listcut) + 1;
  $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut);

  if ($start_offset != 0) {
    $apoffset = $start_offset - $one_bbs;
    echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;";
  }

  for ($i = $start_page; $i < $start_page + $pagecut; $i++) {
    $newoffset = ($i - 1) * $listcut;
    if ($offset != $newoffset) {
      echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;";
    } else {
      echo "&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;";
    }
    if ($i == $end_page) break;
  }

  if ($start_offset != $end_offset) {
    $nextoffset = $start_offset + $one_bbs;
    echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
  }
  echo " 총목록갯수: $end_page 개";
}
mysqli_close($db);
?>
</p>
