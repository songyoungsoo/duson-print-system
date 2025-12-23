function change_Field(getVal) {

		var objOne = document.choiceForm.MY_Fsd;
        var objTwo = document.choiceForm.PN_type;
		var i;


		for (i = document.choiceForm.MY_Fsd.options.length; i >= 0; i--) {
			      document.choiceForm.MY_Fsd.options[i] = null; 
		}


		 for (i = document.choiceForm.PN_type.options.length; i >= 0; i--) {
			      document.choiceForm.PN_type.options[i] = null; 
		}


		

        switch (getVal) {


<?
include "../../db.php";
$Cate_result= mysqli_query($db, "select * from $GGTABLE where Ttable='$page' and BigNo='0'",);
$Cate_rows=mysqli_num_rows($Cate_result);
if($Cate_rows){
$m=1;
while($Cate_row= mysqli_fetch_array($Cate_result)) 
{
?>

case '<?=$Cate_row[no]?>': 


<?
$result= mysqli_query($db, "select * from $GGTABLE where Ttable='$page' and TreeNo='$Cate_row[no]' order by no asc");
$rows=mysqli_num_rows($result);
if($rows){
	$g=0;
while($row= mysqli_fetch_array($result)) { 
?>

objTwo.options[<?=$g?>] = new Option ('<?=$row[title]?>','<?=$row[no]?>');

<?
	$g++;
}    }
?>


<?
$result= mysqli_query($db, "select * from $GGTABLE where Ttable='$page' and BigNo='$Cate_row[no]' order by no asc");
$rows=mysqli_num_rows($result);
if($rows){
	$g=0;
while($row= mysqli_fetch_array($result)) { 
?>

objOne.options[<?=$g?>] = new Option ('<?=$row[title]?>','<?=$row[no]?>');

<?
	$g++;
}    }
?>

 return;

<?
$m++;
}   } 
mysqli_close($db); 
?>

            //default:
			//obj.length = 1;
            // return;
        }



  } 

//////////----------------------------------------------------------------------------------------------------////////////////
function calc_re(){
var T = document.choiceForm;
var TY = T.MY_type.options[T.MY_type.selectedIndex].value;
var TYU = T.PN_type.options[T.PN_type.selectedIndex].value;
var TYUO= T.MY_Fsd.options[T.MY_Fsd.selectedIndex].value;
var TYUOK= T.POtype.options[T.POtype.selectedIndex].value;

Tcal.document.location.href='Tprice_cal.php?TypeOne='+TY+'&TypeTwo='+TYU+'&TypeTree='+TYUO+'&TypeFour='+TYUOK+'&TRYCobe=ok';

}