<?php
// 디비에서 합당한 가격을 검색하여 보내준다..
include "inc.php";
include "../../db.php";
$TABLE = "mlangprintauto_cadarok";
include "../ConDb.php";

// MY_type         명함종류  style
// PN_type         인쇄면    Section
// MY_Fsd          용지      TreeSelect
// MY_amount       수량      quantity 
// ordertype       주문인 주문형태 

$MY_type = mysqli_real_escape_string($db, $MY_type);
$PN_type = mysqli_real_escape_string($db, $PN_type);
$MY_Fsd = mysqli_real_escape_string($db, $MY_Fsd);
$MY_amount = mysqli_real_escape_string($db, $MY_amount);
$ordertype = mysqli_real_escape_string($db, $ordertype);

$query = "SELECT * FROM $TABLE WHERE style='$MY_type' AND Section='$MY_Fsd' AND quantity='$MY_amount' AND TreeSelect='$PN_type'";
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
    } else if ($ordertype == "design") {
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
    echo ("<script language='javascript'>");
    echo ("window.alert('견적을 수행할 관련 정보가 없습니다.\\n\\n다른 항목으로 견적을 해주시기 바랍니다.');");
    echo ("</script>");
    exit;
}

mysqli_close($db);
?>

<script>
    parent.document.forms["choiceForm"].Price.value = "<?php $TPrice = number_format($Price); echo $TPrice; ?>";
    parent.document.forms["choiceForm"].DS_Price.value = "<?php $TDesignMoneyOk = number_format($DesignMoneyOk); echo $TDesignMoneyOk; ?>";
    parent.document.forms["choiceForm"].Order_Price.value = "<?php $TOrder_PricOk = number_format($Order_PricOk); echo $TOrder_PricOk; ?>";
    parent.document.forms["choiceForm"].VAT_Price.value = "<?php $TVAT_PriceOk = number_format($VAT_PriceOk); echo $TVAT_PriceOk; ?>";
    parent.document.forms["choiceForm"].Total_Price.value = "<?php $TTotal_PriceOk = number_format($Total_PriceOk); echo $TTotal_PriceOk; ?>";

    parent.document.forms["choiceForm"].PriceForm.value = <?= $Price ?>;
    parent.document.forms["choiceForm"].DS_PriceForm.value = <?= $DesignMoneyOk ?>;
    parent.document.forms["choiceForm"].Order_PriceForm.value = <?= $Order_PricOk ?>;
    parent.document.forms["choiceForm"].VAT_PriceForm.value = <?= $VAT_PriceOk ?>;
    parent.document.forms["choiceForm"].Total_PriceForm.value = <?= $Total_PriceOk ?>;

    parent.document.forms["choiceForm"].StyleForm.value = "<?= $MY_type ?>";
    parent.document.forms["choiceForm"].SectionForm.value = "<?= $PN_type ?>";
    parent.document.forms["choiceForm"].QuantityForm.value = "<?= $MY_amount ?>";
    parent.document.forms["choiceForm"].DesignForm.value = "<?= $ordertype ?>";
</script>
