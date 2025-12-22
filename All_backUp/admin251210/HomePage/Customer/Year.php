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

$regdate = date("Y");
$i=$regdate-5;
$t=$regdate+5;

if($YMode=="input"){

echo("<select name='Y8y_year'><option value='0'>▒ 연도별 선택 ▒</option>");

while( $i < $t) 
{ 
?>

<option value='<?=$i?>' 
<?if($code=="modify"){?>
<?if($View_BigNo=="$i"){?>selected style='background-color:#3399CC; color:#FFFFFF;'<?}?>><?=$i?> 년</option>
<?}else{?>
<?if($regdate=="$i"){?>selected style='background-color:#336699; color:#FFFFFF;'<?}?>><?=$i?> 년</option>
<?}?>

<?php 
$i=$i+1;
}

echo("</select>");

} ////////////////////////////////////////////////////////////////////////////////////////////////////////

if($YMode=="Location"){
?>

<script language="JavaScript">
function HomePage_Yeartds(targ,selObj,restore){
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>
<select onChange="HomePage_Yeartds('parent',this,0)">

<?php 
while( $i < $t) 
{ 
?>

<option value='<?$PHP_SELF?>?HomePage_YearCate=<?=$i?>' 
<?if($HomePage_YearCate){?>
<?if($HomePage_YearCate=="$i"){?>selected style='background-color:#3399CC; color:#FFFFFF;'<?}?>><?=$i?> 년</option>
<?}else{?>
<?if($regdate=="$i"){?>selected style='background-color:#A1A1A1; color:#FFFFFF;'<?}?>><?=$i?> 년</option>
<?}?>

<?php 
$i=$i+1;
}

echo("
<option value='$PHP_SELF'>→ 전체목록 보기</option>
</select>
");

} ////////////////////////////////////////////////////////////////////////////////////////////////////////
?>