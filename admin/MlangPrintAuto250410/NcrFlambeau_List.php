<?php
include "../../db.php";
$TIO_CODE = "NcrFlambeau";
$table = "MlangPrintAuto_{$TIO_CODE}";
$GGTABLE = "MlangPrintAuto_{$TIO_CODE}_Cate";

// 변수 초기화
$PHP_SELF = $_SERVER['PHP_SELF'];
$mode             = isset($_REQUEST['mode'])             ? trim($_REQUEST['mode'])             : '';
$no               = isset($_REQUEST['no'])               ? (int)$_REQUEST['no']                : 0;
$search           = isset($_REQUEST['search'])           ? trim($_REQUEST['search'])           : '';
$RadOne           = isset($_REQUEST['RadOne'])           ? trim($_REQUEST['RadOne'])           : '';
$myListTreeSelect = isset($_REQUEST['myListTreeSelect']) ? trim($_REQUEST['myListTreeSelect']) : '';
$myList           = isset($_REQUEST['myList'])           ? trim($_REQUEST['myList'])           : '';
$offset           = isset($_REQUEST['offset'])           ? (int)$_REQUEST['offset']            : 0;
$cate             = isset($_REQUEST['cate'])             ? (int)$_REQUEST['cate']              : 0;
$title_search     = isset($_REQUEST['title_search'])     ? trim($_REQUEST['title_search']) : '';


if ($mode == "delete") {
    $db=new mysqli($host,$user,$password,$dataname);
    $result = mysqli_query($db, "DELETE FROM $table WHERE no='$no'");
    mysqli_close($db);

    echo ("<script language='javascript'>
    window.alert('테이블명: $table - $no 번 자료 삭제 완료');
    opener.parent.location.reload();
    window.self.close();
    </script>");
    exit;
}

$M123 = "..";
include "../top.php";

$T_DirUrl = "../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";

// 검색 여부에 따라 쿼리 작성
$search = $search ?? '';
include "../../db.php";
$db = mysqli_connect($host, $user, $password, $dataname);
if ($search === "yes") {
    $Mlang_query = "SELECT * FROM {$table} WHERE style=? AND TreeSelect=? AND Section=?";
    $stmt = $db->prepare($Mlang_query);
    $stmt->bind_param("sss", $RadOne, $myListTreeSelect, $myList);
} else {
    $Mlang_query = "SELECT * FROM {$table}";
    $stmt = $db->prepare($Mlang_query);
}

$stmt->execute();
$result = $stmt->get_result();
$recordsu = $result->num_rows;
$total = $recordsu;

$listcut = 15;
$offset = $offset ?? 0;
?>

<!-- HTML 출력 시작 -->
<head>
    <script>
        function WomanMember_Admin_Del(no) {
            if (confirm(no + '번 자료를 삭제 처리 하시겠습니까?\n삭제 후에는 복구할 수 없습니다.')) {
                let str = '<?php echo  htmlspecialchars($PHP_SELF) ?>?no=' + no + '&mode=delete';
                let popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50");
                popup.location.href = str;
                popup.focus();
            }
        }
    </script>
</head>

<table width="100%" cellpadding="8" cellspacing="3" class="coolBar">
    <tr>
        <td align="left"><?php include "ListSearchBox.php"; ?></td>

        <?php
        include "../../db.php";
        $db = new mysqli($host, $user, $password, $dataname);
        if ($search == "yes") { // 검색모드일때
            $Mlang_query = "SELECT * FROM $table WHERE style='$RadOne' AND TreeSelect='$myListTreeSelect' AND Section='$myList'";
        } else { // 일반모드 일때
            $Mlang_query = "SELECT * FROM $table";
        }

        $query = mysqli_query($db, $Mlang_query);
        $recordsu = mysqli_num_rows($query);
        $total = mysqli_affected_rows($db);

        $listcut = 15;  // 한 페이지당 보여줄 목록 게시물수. 
        if (!$offset) $offset = 0;
        ?>		
        <td align="right">
            <input type="button" onClick="window.open('CateList.php?Ttable=<?php echo  $TIO_CODE ?>&TreeSelect=ok', '<?php echo  $table ?>_FormCate','width=600,height=650');" value=" 구분 관리 ">
            <input type="button" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=IncForm', '<?php echo  $table ?>_Form1','width=820,height=600');" value=" 가격/설명 관리 ">
            <input type="button" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2','width=300,height=250');" value=" 신 자료 입력 ">
            <br><br>
            전체자료수 - <span style="color:blue;"><b><?php echo  $total ?></b></span> 개
        </td>
    </tr>
</table>

<!------------------------------------------- 리스트 시작----------------------------------------->
<table border="0" width="100%" cellpadding="5" cellspacing="1" class="coolBar">
    <tr>
        <td align="center">등록번호</td>
        <td align="center">구분</td>
        <td align="center">규격</td>
        <td align="center">색상 및 재질</td>
        <td align="center">수량(옆)</td>
        <td align="center">가격</td>
        <td align="center">관리기능</td>
    </tr>

    <?php
    $result = mysqli_query($db, "$Mlang_query ORDER BY NO DESC LIMIT $offset, $listcut");
    $rows = mysqli_num_rows($result);
    if ($rows) {
        while ($row = mysqli_fetch_array($result)) {
    ?>
        <tr bgcolor="#575757">
            <td align="center"><font color="white"><?php echo  htmlspecialchars($row['no']) ?></font></td>
            <td align="center"><font color="white">
                <?php
                $res1 = mysqli_query($db, "SELECT title FROM {$GGTABLE} WHERE no = '{$row['style']}'");
                echo htmlspecialchars(mysqli_fetch_assoc($res1)['title'] ?? '');
                ?>
            </font></td>
            <td align="center"><font color="white">
                <?php
                $res2 = mysqli_query($db, "SELECT title FROM {$GGTABLE} WHERE no = '{$row['Section']}'");
                echo htmlspecialchars(mysqli_fetch_assoc($res2)['title'] ?? '');
                ?>
            </font></td>
            <td align="center"><font color="white">
                <?php
                $res3 = mysqli_query($db, "SELECT title FROM {$GGTABLE} WHERE no = '{$row['TreeSelect']}'");
                echo htmlspecialchars(mysqli_fetch_assoc($res3)['title'] ?? '');
                ?>
            </font></td>
            <td align="center"><font color="white"><?php echo  htmlspecialchars($row['quantity']) ?> (<?php echo  htmlspecialchars($row['quantityTwo']) ?>)</font></td>
            <td align="center"><font color="white"><?php echo  number_format((int)$row['money']) ?> 원</font></td>
            <td align="center">
                <input type="button" onClick="window.open('<?php echo  $TIO_CODE ?>_admin.php?mode=form&code=Modify&no=<?php echo  $row['no'] ?>&Ttable=<?php echo  $TIO_CODE ?>', '<?php echo  $table ?>_Form2Modify','width=300,height=250');" value=" 수정 ">
                <input type="button" onClick="WomanMember_Admin_Del('<?php echo  $row['no'] ?>');" value=" 삭제 ">
            </td>
        </tr>
        <?php
    }
} else {
    echo "<tr><td colspan='10' align='center'><br><br>자료 없음</td></tr>";
}
?>
</table>

<p align='center'>

<?php
if($rows){

if($search=="yes"){ $mlang_pagego="search=$search&cate=$cate&title_search=$title_search&RadOne=$RadOne&myListTreeSelect=$myListTreeSelect&myList=$myList";
}else{
  $mlang_pagego="cate=$cate&title_search=$title_search"; // 필드속성들 전달값
}

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

<?php include "../down.php"; ?>