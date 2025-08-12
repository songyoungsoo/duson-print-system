<?php
// 디비에서 합당한 가격을 검색하여 보내준다..
include "inc.php";
include "../../db.php";
$TABLE = "MlangPrintAuto_envelope";
include "../ConDb.php";

// GET 방식으로 데이터 가져오기
$MY_type = $_GET['MY_type'];
$PN_type = $_GET['PN_type'];
$MY_Fsd = $_GET['MY_Fsd'];
$MY_amount = $_GET['MY_amount'];
$POtype = $_GET['POtype'];
$ordertype = $_GET['ordertype'];
// 필요한 모든 매개변수가 전달되었는지 확인
// if ($MY_type === null || $PN_type === null || $MY_Fsd === null || $MY_amount === null || $POtype === null || $ordertype === null) {
//     die("Error: Missing required parameters.");
// }

// 합당한 가격을 검색하여 보내기
$query = "select * from $TABLE where style='$MY_type' and Section='$PN_type' and quantity='$MY_amount' and POtype='$POtype'";
$result = mysqli_query($db, $query);
$row = mysqli_fetch_array($result);

if ($row) {
    if ($ordertype == "print") {
        $Price = $row['money'];  // 인쇄비
        $DesignMoneyOk = 0;  // 디자인편집비
        $Order_PricOk = $Price + $DesignMoneyOk; // 합계
        $VAT_PriceOk = $Order_PricOk / 10;  // 부가세 10%
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;  // 토탈
        $ViewquantityTwo = $row['quantityTwo'];  // 전단지 연수 옆에 장수
    } elseif ($ordertype == "design") {
        $Price = 0;  // 인쇄비
        $DesignMoneyOk = $row['DesignMoney'];  // 디자인편집비
        $Order_PricOk = $Price + $DesignMoneyOk; // 합계
        $VAT_PriceOk = $Order_PricOk / 10;  // 부가세 10%
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;  // 토탈
        $ViewquantityTwo = $row['quantityTwo'];  // 전단지 연수 옆에 장수
    } else {
        $Price = $row['money'];  // 인쇄비
        $DesignMoneyOk = $row['DesignMoney'];
        $Order_PricOk = $Price + $DesignMoneyOk; // 합계
        $VAT_PriceOk = $Order_PricOk / 10;  // 부가세 10%
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;  // 토탈
        $ViewquantityTwo = $row['quantityTwo'];  // 전단지 연수 옆에 장수
    }
} else {
    echo "<script language='javascript'>";
    echo "parent.document.forms['choiceForm'].Price.value='';";                               
    echo "parent.document.forms['choiceForm'].DS_Price.value='';";       
    echo "parent.document.forms['choiceForm'].Order_Price.value='';";
    echo "parent.document.forms['choiceForm'].PriceForm.value='';";                                
    echo "parent.document.forms['choiceForm'].DS_PriceForm.value='';";           
    echo "parent.document.forms['choiceForm'].Order_PriceForm.value='';";       
    echo "parent.document.forms['choiceForm'].VAT_PriceForm.value='';";            
    echo "parent.document.forms['choiceForm'].Total_PriceForm.value='';";  
    echo "parent.document.forms['choiceForm'].StyleForm.value='';";                               
    echo "parent.document.forms['choiceForm'].SectionForm.value='';";          
    echo "parent.document.forms['choiceForm'].QuantityForm.value='';";       
    echo "parent.document.forms['choiceForm'].DesignForm.value='';"; 
    echo "window.alert('견적을 수행할 관련 정보가 없습니다.\\n\\n다른 항목으로 견적을 해주시기 바랍니다.');";
    echo "</script>";
    exit;
}
mysqli_close($db);
?>

<script>
parent.document.forms["choiceForm"].Price.value = "<?= number_format($Price) ?>";                               
parent.document.forms["choiceForm"].DS_Price.value = "<?= number_format($DesignMoneyOk) ?>";       
parent.document.forms["choiceForm"].Order_Price.value = "<?= number_format($Order_PricOk) ?>";    

parent.document.forms["choiceForm"].PriceForm.value = <?= $Price ?>;                               
parent.document.forms["choiceForm"].Order_PriceForm.value = <?= $Order_PricOk ?>;       
parent.document.forms["choiceForm"].VAT_PriceForm.value = <?= $VAT_PriceOk ?>;            
parent.document.forms["choiceForm"].Total_PriceForm.value = <?= $Total_PriceOk ?>; 

parent.document.forms["choiceForm"].StyleForm.value = "<?= $MY_type ?>";                               
parent.document.forms["choiceForm"].SectionForm.value = "<?= $PN_type ?>";          
parent.document.forms["choiceForm"].QuantityForm.value = "<?= $MY_amount ?>";       
parent.document.forms["choiceForm"].DesignForm.value = "<?= $ordertype ?>";  
</script>
