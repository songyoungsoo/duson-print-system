<?
function ERROR(){
echo("<script>alert('정상적으로 선택해 주시기 바랍니다.');</script>");
}

if($TRYCobe=="ok"){
if($TypeOne=="========"){  ERROR(); }
else if($TypeTwo=="========"){  ERROR(); }
else if($TypeTree=="========"){  ERROR(); }

$Ttable="cadarok";
include"../../db.php";
$result= mysqli_query("select * from MlangPrintAuto_${Ttable} where style='$TypeOne' and Section='$TypeTwo' and TreeSelect='$TypeTree' order by quantity asc",$db);
$rows=mysqli_num_rows($result);
?>

<script>
        var obj = parent.document.forms["choiceForm"].MY_amount;
		var i;

		for (i = parent.document.forms["choiceForm"].MY_amount.options.length; i >= 0; i--) {
		parent.document.forms["choiceForm"].MY_amount.options[i] = null; 
		}

<?php     
if($rows){
$g=0;
while($row= mysqli_fetch_array($result)) { 
?>

<?if($row[quantity]=="9999"){?>
obj.options[<?=$g?>] = new Option ('기타','<?=$row[quantity]?>');
<?}else{?>
obj.options[<?=$g?>] = new Option ('<?=$row[quantity]?>부','<?=$row[quantity]?>');
<?}?>

<?
$g++;  } 
}else{
}?> 
</script>

<?
mysql_close($db); 
}
?>