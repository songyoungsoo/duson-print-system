<?php
$regdate = date("Y");
$i=$regdate-5;
$t=$regdate+5;

if($YMode=="input"){

echo("<select name='Y8y_year'><option value='0'>�� ������ ���� ��</option>");

while( $i < $t) 
{ 
?>

<option value='<?php echo $i?>' 
<?php if ($code=="modify"){?>
<?php if ($View_BigNo=="$i"){?>selected style='background-color:#3399CC; color:#FFFFFF;'<?php } ?>><?php echo $i?> ��</option>
<?}else{?>
<?php if ($regdate=="$i"){?>selected style='background-color:#336699; color:#FFFFFF;'<?php } ?>><?php echo $i?> ��</option>
<?php } ?>

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

<option value='<?$PHP_SELF?>?HomePage_YearCate=<?php echo $i?>' 
<?php if ($HomePage_YearCate){?>
<?php if ($HomePage_YearCate=="$i"){?>selected style='background-color:#3399CC; color:#FFFFFF;'<?php } ?>><?php echo $i?> ��</option>
<?}else{?>
<?php if ($regdate=="$i"){?>selected style='background-color:#A1A1A1; color:#FFFFFF;'<?php } ?>><?php echo $i?> ��</option>
<?php } ?>

<?php
$i=$i+1;
}

echo("
<option value='$PHP_SELF'>�� ��ü��� ����</option>
</select>
");

} ////////////////////////////////////////////////////////////////////////////////////////////////////////
?>