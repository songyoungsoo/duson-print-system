<?php
declare(strict_types=1);

// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

if($mode=="delete"){
include"../../db.php";
include"../config.php";

$result = mysqli_query($db, "DELETE FROM $page_table WHERE no='$no'");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

mysqli_close($db);

echo ("<script language=javascript>
window.alert('자료를 정상적으로 [삭제] 하였습니다.!!');
opener.parent.location=\"./page_page_list.php\"; 
window.self.close();
</script>
");
exit;

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="modify"){

$M123="..";
include"../top.php"; 

$result= mysqli_query($db, "select * from $page_table where no='$no'");
$rows=mysqli_num_rows($result);
if($rows){

while($row= mysqli_fetch_array($result)) 
{ 
$TT_no="$row[no]";
$TT_title="$row[title]";
$TT_style="$row[style]";
$TT_connent="$row[connent]";
$TT_cate="$row[cate]";
}

}else{
	echo ("
		<script language=javascript>
		alert('이미삭제된 자료이거나 등록된 자료가 없습니다.');
         history.go(-1);
		</script>
	");
		exit;
}

mysqli_close($db); 
?>

<script src="../js/coolbar.js" type="text/javascript"></script>

<table border=0 align=center width=600 cellpadding='0' cellspacing='0'>

<tr><td align=center>
<BR><BR>
* 페이지의 형식을 바꾼후 자료를 입력 수정하시면 기존의 자료는 자동 갱신됩니다.<BR>
* 수정후 이전후 자료는 보관되지 않음으로 신중을 기하여 수정을 요합니다.<BR>

<!------------------------------------------------------------->
<table border="0" cellpadding="5" cellspacing="0" width="561" class='coolBar'>
<tr><td>
	
<?php 
if($code=="br"){include"./html_edit_modify_br.php";}else if($code=="html"){include"./html_edit_modify_html.php";}else if($code=="file"){include"./html_edit_modify_file.php";}else if($code=="edit"){include"./html_edit_modify_edit.php";}else{include"./html_edit_modify_$TT_style.php";}
?>

</td></tr>
</table>
<!------------------------------------------------------------->


<BR><BR>
</td></tr>

</table>


<?php }
?>