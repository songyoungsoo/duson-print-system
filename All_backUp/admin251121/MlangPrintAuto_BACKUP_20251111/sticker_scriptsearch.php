<?php
declare(strict_types=1);

// ✅ PHP 7.4 호환: 입력 변수 초기화
$Ttable = $Ttable ?? 'sticker';
$GGTABLE = 'mlangprintauto_transactioncate';
$RadOne = $_GET['RadOne'] ?? $_POST['RadOne'] ?? '';
$myListTreeSelect = $_GET['myListTreeSelect'] ?? $_POST['myListTreeSelect'] ?? '';
$myList = $_GET['myList'] ?? $_POST['myList'] ?? '';
$PHP_SELF = $_SERVER['PHP_SELF'] ?? '';
?>
<HEAD>
<SCRIPT LANGUAGE="JavaScript">
function Activity(name, list){
this.name = name;
this.list = list;
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////

var acts = new Array();

<?php
// ✅ db.php는 이미 sticker_List.php에서 include되었으므로 제거
// include"../../db.php";
$result= mysqli_query($db, "select * from $GGTABLE where Ttable='$Ttable' and BigNo='0' order by no asc");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$rows=mysqli_num_rows($result);
if($rows){
	$g=0;
while($row= mysqli_fetch_array($result)) {
?>
acts[<?php echo $g?>] = new Activity('<?php echo $row['no']?>', [<?php
              $result_Two= mysqli_query($db, "select * from $GGTABLE where BigNo='{$row['no']}' order by no asc");
                $rows_Two=mysqli_num_rows($result_Two);
                  if($rows_Two){
                     while($row_Two= mysqli_fetch_array($result_Two)) {
                                echo("'" . htmlspecialchars($row_Two['title']) . "',");
                               }  }
      ?>'==================']);

<?php 	$g++;
}

}else{echo("<option>등록자료  없음</option>");}
// ✅ mysqli_close는 sticker_List.php에서 처리하므로 제거
// mysqli_close($db);
?>


var VL = new Array();

<?php
// ✅ db.php는 이미 sticker_List.php에서 include되었으므로 제거
// include"../../db.php";
$result= mysqli_query($db, "select * from $GGTABLE where Ttable='$Ttable' and BigNo='0' order by no asc");
$rows=mysqli_num_rows($result);
if($rows){
	$g=0;
while($row= mysqli_fetch_array($result)) {
?>
VL[<?php echo $g?>] = new Activity('<?php echo $row['no']?>', [<?php
              $result_Two= mysqli_query($db, "select * from $GGTABLE where BigNo='{$row['no']}' order by no asc");
                $rows_Two=mysqli_num_rows($result_Two);
                  if($rows_Two){
                     while($row_Two= mysqli_fetch_array($result_Two)) {
                                echo("'" . $row_Two['no'] . "',");
                               }  }
      ?>'==================']);

<?php 	$g++;
}

}else{echo("<option>등록자료  없음</option>");}
// ✅ mysqli_close는 sticker_List.php에서 처리하므로 제거
// mysqli_close($db);
?>


function updateList(str){

var frm = document.myForm;
var oriLen = frm.myList.length;
var numActs;

for (var i = 0; i < acts.length; i++){
if (str == acts[i].name) {
numActs = acts[i].list.length;
for (var j = 0; j < numActs; j++)
frm.myList.options[j] = new Option(acts[i].list[j], VL[i].list[j]);
for (var j = numActs; j < oriLen; j++)
frm.myList.options[numActs] = null;
}
}

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////

</SCRIPT>

</HEAD>

<!-- ✅ FORM 태그는 ListSearchBox.php에서 정의되므로 제거 -->

<select name=RadOne onChange='updateList(this.value)'>
<option value='#'>:::::: 선택하세요 ::::::</option>
<?php
// ✅ db.php는 이미 sticker_List.php에서 include되었으므로 제거
// include"../../db.php";
$result= mysqli_query($db, "select * from $GGTABLE where Ttable='$Ttable' and BigNo='0' order by no asc");
$rows=mysqli_num_rows($result);
if($rows){
$r=0;
while($row= mysqli_fetch_array($result))
{
?>
<option value='<?php echo $row['no']?>' <?php if($RadOne==$row['no']){echo("selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'");}?>><?php echo htmlspecialchars($row['title'])?></option>
<?php 	$r++;
}

}else{echo("<option>등록자료  없음</option>");}
// ✅ mysqli_close는 sticker_List.php에서 처리하므로 제거
// mysqli_close($db);
?>
</select>

<select name=myList>
<option value='#'>:::::: 선택하세요 ::::::</option>
<?php if($myList){echo("<option value='$myList' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>");
$result=mysqli_query($db, "select * from $GGTABLE where no='$myList'");
$row= mysqli_fetch_array($result);
if($row){ echo(htmlspecialchars($row['title'])); }
echo("</option>");}?>
</select>