<?
$HomeDir="../../";
$PageCode="PrintAuto";
$MultyUploadDir="../../PHPClass/MultyUpload";

include"$HomeDir/db.php";
if(!$page){$page="sticker";}
include"../MlangPrintAutoTop.php";
include "../../lib/func.php"; 
$connect = dbconn(); 
include"../../shop/view.php";

?>
<?

$PrintTextBox_left=260+$DhtmlLeftFos;
$PrintTextBox_top="$DhtmlTopFos";
$PrintTextBox_width="360";
$PrintTextBox_height="100";
?>

<?
include"../DhtmlText.php";
?>

<?
include"../MlangPrintAutoDown.php";
?>