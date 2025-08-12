<?php
// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "inc.php";
include "../../db.php";

$TABLE = "MlangPrintAuto_inserted";
include "../ConDb.php";

// MY_type         명함종류  style
// PN_type         인쇄면    Section
// MY_Fsd         용지   TreeSelect
// MY_amount    수량   quantity 
// ordertype       주문인 주문형태 

// GET 방식으로 데이터 가져오기
$MY_type = $_GET['MY_type'];
$PN_type = $_GET['PN_type'];
$MY_Fsd = $_GET['MY_Fsd'];
$MY_amount = $_GET['MY_amount'];
$POtype = $_GET['POtype'];
$ordertype = $_GET['ordertype'];

// 합당한 가격을 검색하여 보내기
$result = mysqli_query($db, "SELECT * FROM $TABLE WHERE style='$MY_type' AND Section='$PN_type' AND quantity='$MY_amount' AND TreeSelect='$MY_Fsd' AND POtype='$POtype'");
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
    parent.document.forms["choiceForm"].Price.value = "<?php echo number_format($Price); ?>";                               
    parent.document.forms["choiceForm"].DS_Price.value = "<?php echo number_format($DesignMoneyOk); ?>";       
    parent.document.forms["choiceForm"].Order_Price.value = "<?php echo number_format($Order_PricOk); ?>";    
    parent.document.forms["choiceForm"].PriceForm.value = <?php echo $Price; ?>;                               
    parent.document.forms["choiceForm"].DS_PriceForm.value = <?php echo $DesignMoneyOk; ?>;          
    parent.document.forms["choiceForm"].Order_PriceForm.value = <?php echo $Order_PricOk; ?>;       
    parent.document.forms["choiceForm"].VAT_PriceForm.value = <?php echo $VAT_PriceOk; ?>;            
    parent.document.forms["choiceForm"].Total_PriceForm.value = <?php echo $Total_PriceOk; ?>; 
    parent.document.forms["choiceForm"].StyleForm.value = "<?php echo $MY_type; ?>";                               
    parent.document.forms["choiceForm"].SectionForm.value = "<?php echo $PN_type; ?>";          
    parent.document.forms["choiceForm"].QuantityForm.value = "<?php echo $MY_amount; ?>";       
    parent.document.forms["choiceForm"].DesignForm.value = "<?php echo $ordertype; ?>";  
    parent.document.forms["choiceForm"].MY_amountRight.value = "<?php echo $ViewquantityTwo; ?>장";   // 전단지 옆 장수
</script>
