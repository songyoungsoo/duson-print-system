<?php
echo ("<script language=javascript>
window.alert('수동견적은 프로그램 3.0 이상에서만 지원함으로\\n\\n02-2264-7118로 별도로 문의해 주시기 바랍니다.');
history.go(-1);
</script>
");
exit;

if ($mode == "view") {

    include "../title.php";
    include "../../db.php";

    $stmt = mysqli_prepare($mysqli, "SELECT * FROM MlangOrder_PrintAuto_OfferOrder WHERE no=?");
    mysqli_stmt_bind_param($stmt, "i", $no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result)) {

        while ($row = mysqli_fetch_assoc($result)) {
            $BBAdminSelect = $row['AdminSelect'];
            ?>

            <script>
            window.resizeTo(680, 730);
            </script>

            <style>
            .td1 {font-family:굴림; font-size: 9pt; color:#FFFFFF; font-weight:bold; line-height: normal;}
            .td2 {font-family:굴림; font-size: 9pt; color:#008080; line-height:130%;}
            </style>

            </head>

            <table border=0 align=center width=100% cellpadding='8' cellspacing='1' bgcolor='#FFFFFF'>
            <tr>
            <td bgcolor='#FFFFFF' class='td2'>
            <?php echo  $row['cont_2']; ?>
            </td>
            </tr>
            </table>

            <p align=center>
            <input type='button' onClick='javascript:window.close();' value='창닫기-CLOSE' style='font-size:10pt; background-color:#429EB2; color:#FFFFFF; border-style:solid; height:24px; border:2 solid #84D0E0;'>
            </p>

            <?php
        }

    } else {
        echo "<p align=center><b>등록 자료가 없음.</b></p>";
    }

    if ($BBAdminSelect == "no") {
        $stmt = mysqli_prepare($mysqli, "UPDATE MlangOrder_PrintAuto_OfferOrder SET AdminSelect='yes' WHERE no=?");
        mysqli_stmt_bind_param($stmt, "i", $no);
        $updateResult = mysqli_stmt_execute($stmt);

        if (!$updateResult) {
            echo "
                <script language=javascript>
                    window.alert(\"DB 접속 에러입니다!\")
                    history.go(-1);
                </script>";
            exit;
        } else {
            echo ("<script language=javascript>
                opener.parent.location.reload();
            </script>");
            exit;
        }
    }

    mysqli_close($mysqli);
    exit;
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "delete") {

    include "../../db.php";

    $stmt = mysqli_prepare($mysqli, "DELETE FROM MlangOrder_PrintAuto_OfferOrder WHERE no=?");
    mysqli_stmt_bind_param($stmt, "i", $no);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($mysqli);

    echo ("<script language=javascript>
        alert('\\n정보를 정상적으로 삭제하였습니다.\\n');
        opener.parent.location.reload();
        window.self.close();
    </script>");
    exit;
}
?>

<?php
$M123 = "..";
include "../top.php";
?>

<head>
<script>
function Member_Admin_Del(no) {
  if (confirm(no + '번 의 상담 자료를 삭제 하시겠습니까..?\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
    const str = '<?php echo $PHP_SELF?>?no=' + no + '&mode=delete';
    const popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
    popup.document.location.href = str;
    popup.focus();
  }
}
</script>
</head>

<table border="0" align="center" width="100%" cellpadding="8" cellspacing="3" class="coolBar">
<tr>
<td align="left" colspan="12">
<font color="red">*</font> 관리자가 자료를 들여다 본 자료는 확인으로 자동으로 갱신됩니다.<br>
</td>
</tr>
</table>

<table border="0" align="center" width="100%" cellpadding="5" cellspacing="1" class="coolBar">
<tr>
<td align="center">등록번호</td>
<td align="center">이름</td>
<td align="center">접수Time</td>
<td align="center">확인여부</td>
<td align="center">자세한정보보기</td>
<td align="center">관리</td>
</tr>

<?php
include "../../db.php";
$table = "MlangOrder_PrintAuto_OfferOrder";

$Mlang_query = "SELECT * FROM $table";
$result = mysqli_query($mysqli, "$Mlang_query ORDER BY no DESC");
$recordsu = mysqli_num_rows($result);
$total = $recordsu;

$listcut = 12;
$offset = isset($offset) ? $offset : 0;
$result = mysqli_query($mysqli, "$Mlang_query ORDER BY no DESC LIMIT $offset, $listcut");
$rows = mysqli_num_rows($result);

if ($rows) {
  while ($row = mysqli_fetch_assoc($result)) {
?>
<tr bgcolor="#575757">
  <td align="center"><font color="white"><?php echo $row['no']?></font></td>
  <td align="center"><font color="white"><?php echo $row['name']?></font></td>
  <td align="center"><font color="white"><?php echo $row['date']?></font></td>
  <td align="center">
    <?php
    if ($row['AdminSelect'] == "no") {
      echo "<b><font color='red'>미확인</font></b>";
    } else {
      echo "<font color='white'>확인</font>";
    }
    ?>
  </td>
  <td align="center">
    <input type="button" onClick="javascript:popup=window.open('<?php echo $PHP_SELF?>?mode=view&no=<?php echo $row['no']?>', 'MlangOrder_PrintAuto_OfferOrder','width=600,height=430,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=" 자세한정보보기 ">
  </td>
  <td align="center">
    <input type="button" onClick="javascript:Member_Admin_Del('<?php echo $row['no']?>');" value=" 삭제 ">
  </td>
</tr>
<?php
  }
} else {
  echo "<tr><td colspan='10'><p align='center'><br><br>등록 자료없음</p></td></tr>";
}
?>
</table>

<p align="center">
<?php
if ($rows) {
  $mlang_pagego = "cate=$cate&title_search=$title_search";

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
      echo "&nbsp;<font style='font-weight:bold; color:green;'>($i)</font>&nbsp;";
    }
    if ($i == $end_page) break;
  }

  if ($start_offset != $end_offset) {
    $nextoffset = $start_offset + $one_bbs;
    echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>";
  }
  echo "총목록갯수: $end_page 개";
}
mysqli_close($mysqli);
?>
</p>

<?php
include "../down.php";
?>
