<?php
// DB 접근 허용 상수 정의
define('DB_ACCESS_ALLOWED', true);

// 직접 데이터베이스 연결 생성
$host = "localhost";
$user = "dsp1830"; 
$password = "ds701018";
$dataname = "dsp1830";

$db = mysqli_connect($host, $user, $password, $dataname);
if (!$db) {
    die("데이터베이스 연결에 실패했습니다: " . mysqli_connect_error());
}
mysqli_set_charset($db, "utf8mb4");

// 변수 초기화 (방지용)
$cate       = $_GET['cate'] ?? $_POST['cate'] ?? '';
$title_search = $_GET['title_search'] ?? $_POST['title_search'] ?? '';
$PHP_SELF   = $_SERVER['PHP_SELF'];
$TIO_CODE="namecard";
$table="mlangprintauto_{$TIO_CODE}";
$mode       = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no         = isset($_GET['no']) ? (int)$_GET['no'] : 0;
$search     = $_GET['search'] ?? $_POST['search'] ?? '';
$RadOne     = $_GET['RadOne'] ?? $_POST['RadOne'] ?? '';
$myList     = $_GET['myList'] ?? $_POST['myList'] ?? '';
$myListTreeSelect = $_GET['myListTreeSelect'] ?? $_POST['myListTreeSelect'] ?? '';
$offset     = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$listcut = 20; // 기본값 지정

if($mode=="delete"){
    $result = mysqli_query($db, "DELETE FROM $table WHERE no='$no'");

echo ("<script language=javascript>
window.alert('테이블명: $table - $no 번 자료 삭제 완료');
opener.parent.location.reload();
window.close();
</script>
");
exit;

} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$M123 = "..";
include "../top.php";
$T_DirUrl="../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";

// Define GGTABLE from ConDb.php's $TABLE variable
$GGTABLE = $TABLE; // This is "mlangprintauto_transactioncate"
?>

<head>
<script>
function clearField(field)
{
	if (field.value == field.defaultValue) {
		field.value = "";
	}
}
function checkField(field)
{
	if (!field.value) {
		field.value = field.defaultValue;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function WomanMember_Admin_Del(no){
	if (confirm(+no+'번 자료을 삭제 처리 하시겠습니까..?\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
		str='<?php echo $PHP_SELF?>?no='+no+'&mode=delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>명함 관리 - MlangPrintAuto</title>
    <link rel="stylesheet" href="../css/corporate-design-system.css">
    <style>
        .management-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--space-lg);
        }
        .page-header {
            background: var(--bg-primary);
            border: 1px solid var(--bg-tertiary);
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            margin-bottom: var(--space-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .data-table {
            background: var(--bg-primary);
            border: 1px solid var(--bg-tertiary);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-base);
        }
        .table-header {
            background: var(--bg-secondary);
            padding: var(--space-md);
            border-bottom: 1px solid var(--bg-tertiary);
            display: grid;
            grid-template-columns: 80px 120px 120px 100px 100px 100px 100px 160px;
            gap: var(--space-sm);
            font-weight: 600;
            font-size: var(--text-sm);
            color: var(--text-primary);
        }
        .table-row {
            display: grid;
            grid-template-columns: 80px 120px 120px 100px 100px 100px 100px 160px;
            gap: var(--space-sm);
            padding: var(--space-md);
            border-bottom: 1px solid var(--bg-tertiary);
            transition: background-color 0.2s ease;
            align-items: center;
        }
        .table-row:hover {
            background: var(--bg-secondary);
        }
        .table-row:last-child {
            border-bottom: none;
        }
        .table-cell {
            font-size: var(--text-sm);
            color: var(--text-secondary);
            text-align: center;
        }
        .action-buttons {
            display: flex;
            gap: var(--space-xs);
        }
        .btn-sm {
            padding: var(--space-xs) var(--space-sm);
            font-size: var(--text-xs);
            border-radius: var(--radius-sm);
        }
        .empty-state {
            text-align: center;
            padding: var(--space-3xl);
            color: var(--text-secondary);
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: var(--space-sm);
            padding: var(--space-lg);
            font-size: var(--text-sm);
        }
        .pagination a {
            padding: var(--space-xs) var(--space-sm);
            border: 1px solid var(--bg-tertiary);
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--text-secondary);
            transition: all 0.2s ease;
        }
        .pagination a:hover {
            background: var(--primary-color);
            color: var(--text-inverse);
            border-color: var(--primary-color);
        }
        .current-page {
            font-weight: 600;
            color: var(--primary-color);
        }
        .stats-badge {
            background: var(--info-color);
            color: var(--text-inverse);
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-sm);
            font-size: var(--text-xs);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="management-container">

        <?php
        if($search=="yes"){ //검색모드일때
         $Mlang_query="select * from $table where style='$RadOne' and Section='$myList'";
        }else{ // 일반모드 일때
        $Mlang_query="select * from $table";
        }

        $query = mysqli_query($db, $Mlang_query);
        $recordsu = mysqli_num_rows($query);
        $total = mysqli_num_rows($query);

        $listcut= 15;  //한 페이지당 보여줄 목록 게시물수. 
        if(!$offset) $offset=0; 
        ?>

        <div class="page-header">
            <div>
                <?php include "ListSearchBox.php";?>
            </div>
            <div style="display: flex; align-items: center; gap: var(--space-sm);">
                <button type="button" class="btn btn-outline btn-sm" onClick="javascript:popup=window.open('CateList.php?Ttable=<?php echo $TIO_CODE?>&TreeSelect=ok', '<?php echo $table?>_FormCate','width=600,height=650,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();">구분 관리</button>
                <button type="button" class="btn btn-outline btn-sm" onClick="javascript:window.open('<?php echo $TIO_CODE?>_admin.php?mode=IncForm', '<?php echo $table?>_Form1','width=820,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');">가격/설명 관리</button>
                <button type="button" class="btn btn-primary btn-sm" onClick="javascript:popup=window.open('<?php echo $TIO_CODE?>_admin.php?mode=form&Ttable=<?php echo $TIO_CODE?>', '<?php echo $table?>_Form2','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();">신 자료 입력</button>
                <span class="stats-badge"><?php echo $total?>개</span>
            </div>
        </div>

        <div class="data-table">
            <div class="table-header">
                <div>등록번호</div>
                <div>명함종류</div>
                <div>명함재질</div>
                <div>인쇄면</div>
                <div>수량</div>
                <div>가격</div>
                <div>디자인비</div>
                <div>관리기능</div>
            </div>

<?php
// 쿼리 실행
$full_query = $Mlang_query . " order by NO desc limit $offset, $listcut";
$result = mysqli_query($db, $full_query);

if (!$result) {
    echo "<div style='color:red;'>쿼리 실행 실패: " . mysqli_error($db) . "</div>";
    $rows = 0;
} else {
    $rows = mysqli_num_rows($result);
}
if($rows){


while($row= mysqli_fetch_array($result)) 
{ 
?>

            <div class="table-row">
                <div class="table-cell"><?php echo $row['no']?></div>
                <div class="table-cell">
                    <?php 
                    $result_FGTwo=mysqli_query($db, "select * from $GGTABLE where no='$row[style]'");
                    if($result_FGTwo) {
                        $row_FGTwo= mysqli_fetch_array($result_FGTwo);
                    if($row_FGTwo){ echo("$row_FGTwo[title]"); }
                    }
                    ?>
                </div>
                <div class="table-cell">
                    <?php 
                    $result_FGFree=mysqli_query($db, "select * from $GGTABLE where no='$row[Section]'");
                    if($result_FGFree) {
                        $row_FGFree= mysqli_fetch_array($result_FGFree);
                        if($row_FGFree){ echo("$row_FGFree[title]"); }
                    }
                    ?>
                </div>
                <div class="table-cell">
                    <?php if($row['POtype']=="1"){echo("단면");}?>
                    <?php if($row['POtype']=="2"){echo("양면");}?>
                </div>
                <div class="table-cell"><?php echo $row['quantity']?>매</div>
                <div class="table-cell">
                    <?php $sum = "$row[money]"; $sum = number_format($sum);  echo("$sum"); $sum = str_replace(",","",$sum); ?>원
                </div>
                <div class="table-cell">
                    <?php $sumr = "$row[DesignMoney]"; $sumr = number_format($sumr);  echo("$sumr"); $sumr = str_replace(",","",$sumr); ?>원
                </div>
                <div class="table-cell">
                    <div class="action-buttons">
                        <button type="button" class="btn btn-outline btn-sm" onClick="javascript:popup=window.open('<?php echo $TIO_CODE?>_admin.php?mode=form&code=Modify&no=<?php echo $row['no']?>&Ttable=<?php echo $TIO_CODE?>', '<?php echo $table?>_Form2Modify','width=300,height=250,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();">수정</button>
                        <button type="button" class="btn btn-danger btn-sm" onClick="javascript:WomanMember_Admin_Del('<?php echo $row['no']?>');">삭제</button>
                    </div>
                </div>
            </div>

<?php
$i=0;
		$i=$i+1;
} 


}else{
            if($search){
                echo "<div class='empty-state'>관련 검색 자료없음</div>";
            }else{
                echo "<div class='empty-state'>등록 자료없음</div>";
            }
        }
        ?>
        </div>

        <?php if($rows): ?>
        <div class="pagination">
            <?php
            if($search=="yes"){ 
                $mlang_pagego="search=$search&cate=$cate&title_search=$title_search&RadOne=$RadOne&myListTreeSelect=$myListTreeSelect&myList=$myList";
            }else{
                $mlang_pagego="cate=$cate&title_search=$title_search"; // 필드속성들 전달값
            }

            $pagecut= 7;  //한 장당 보여줄 페이지수 
            $one_bbs= $listcut*$pagecut;  //한 장당 실을 수 있는 목록(게시물)수 
            $start_offset= intval($offset/$one_bbs)*$one_bbs;  //각 장에 처음 페이지의 $offset값. 
            $end_offset= intval($recordsu/$one_bbs)*$one_bbs;  //마지막 장의 첫페이지의 $offset값. 
            $start_page= intval($start_offset/$listcut)+1; //각 장에 처음 페이지의 값. 
            $end_page= ($recordsu%$listcut>0)? intval($recordsu/$listcut)+1: intval($recordsu/$listcut); 

            if($start_offset!= 0) { 
                $apoffset= $start_offset- $one_bbs; 
                echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>← 이전</a>"; 
            } 

            for($i= $start_page; $i< $start_page+$pagecut; $i++) { 
                $newoffset= ($i-1)*$listcut; 
                if($offset!= $newoffset){
                    echo "<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>$i</a>"; 
                }else{
                    echo "<span class='current-page'>$i</span>"; 
                } 
                if($i==$end_page) break; 
            } 

            if($start_offset!= $end_offset) { 
                $nextoffset= $start_offset+ $one_bbs; 
                echo "<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>다음 →</a>"; 
            } 
            ?>
            <span style="margin-left: var(--space-lg); color: var(--text-secondary); font-size: var(--text-xs);">
                총 <?php echo $end_page ?>페이지
            </span>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
include "../down.php";
?>