<?
function ERROR(){
    echo("<script>alert('정상적으로 선택해 주시기 바랍니다.');</script>");
}

if($TRYCobe=="ok"){
if($TypeOne=="========"){  ERROR(); }
else if($TypeTwo=="========"){  ERROR(); }
else if($TypeTree=="========"){  ERROR(); }

$Ttable="littleprint";
include"../../db.php";
$result= mysqli_query($db, "select * from MlangPrintAuto_{$Ttable} where style='$TypeOne' and Section='$TypeTwo' and TreeSelect='$TypeTree' and POtype='$TypeFour' order by quantity asc");
$rows=mysqli_num_rows($result);
?>

<script>
        var obj = parent.document.forms["choiceForm"].MY_amount;
		var i;

		for (i = parent.document.forms["choiceForm"].MY_amount.options.length; i >= 0; i--) {
		parent.document.forms["choiceForm"].MY_amount.options[i] = null; 
		}

<?     
if($rows){
$g=0;
while($row= mysqli_fetch_array($result)) { 
?>
obj.options[<?=$g?>] = new Option ('<?=$row[quantity]?>매','<?=$row[quantity]?>');
<?
$g++;  } 
}else{
}?> 

</script>

<?
mysqli_close($db); 
}
?>