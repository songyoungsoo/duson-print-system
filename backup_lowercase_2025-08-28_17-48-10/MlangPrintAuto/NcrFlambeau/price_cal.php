<?php
// 디비에서 합당한 가격을 검색하여 보내준다..
include "inc.php";
include "../../db.php";
$TABLE = "mlangprintauto_ncrflambeau";
include "../ConDb.php";

// $_GET을 사용하여 값 가져오기
echo $MY_type = isset($_GET['MY_type']) ? $_GET['MY_type'] : '';
echo $PN_type = isset($_GET['PN_type']) ? $_GET['PN_type'] : '';
echo $MY_Fsd = isset($_GET['MY_Fsd']) ? $_GET['MY_Fsd'] : '';
echo $MY_amount = isset($_GET['MY_amount']) ? $_GET['MY_amount'] : '';
echo $ordertype = isset($_GET['ordertype']) ? $_GET['ordertype'] : '';

// 데이터베이스에서 관련 정보 조회
$result = mysqli_query($db, "SELECT * FROM $TABLE WHERE style='$MY_type' AND Section='$MY_Fsd' AND quantity='$MY_amount' AND TreeSelect='$PN_type'");
$row = mysqli_fetch_array($result);

if ($row) {
    if ($ordertype == "print") {
        $Price = $row['money'];  // 인쇄비  
        $DesignMoneyOk = "0";    // 디자인편집비
        $Order_PricOk = $Price + $DesignMoneyOk; // 합계
        $VAT_PriceOk = $Order_PricOk / 10;  // 부가세 10%
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;  // 총액
        $ViewquantityTwo = $row['quantityTwo'];  // 추가 수량
    } elseif ($ordertype == "design") {
        $Price = "0";  // 인쇄비  
        $DesignMoneyOk = $row['DesignMoney'];  // 디자인편집비
        $Order_PricOk = $Price + $DesignMoneyOk; // 합계
        $VAT_PriceOk = $Order_PricOk / 10;  // 부가세 10%
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;  // 총액
        $ViewquantityTwo = $row['quantityTwo'];  // 추가 수량
    } else {
        $Price = $row['money'];  // 인쇄비  
        $DesignMoneyOk = $row['DesignMoney'];
        $Order_PricOk = $Price + $DesignMoneyOk; // 합계
        $VAT_PriceOk = $Order_PricOk / 10;  // 부가세 10%
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;  // 총액
        $ViewquantityTwo = $row['quantityTwo'];  // 추가 수량
    }
} else {
    echo ("<script language='javascript'>
    parent.document.forms['choiceForm'].Price.value='';                               
    parent.document.forms['choiceForm'].DS_Price.value='';       
    parent.document.forms['choiceForm'].Order_Price.value='';    
    parent.document.forms['choiceForm'].PriceForm.value = '';                                
    parent.document.forms['choiceForm'].DS_PriceForm.value = '';           
    parent.document.forms['choiceForm'].Order_PriceForm.value = '';       
    parent.document.forms['choiceForm'].VAT_PriceForm.value = '';            
    parent.document.forms['choiceForm'].Total_PriceForm.value = '';  
    parent.document.forms['choiceForm'].StyleForm.value='';                               
    parent.document.forms['choiceForm'].SectionForm.value='';          
    parent.document.forms['choiceForm'].QuantityForm.value='';       
    parent.document.forms['choiceForm'].DesignForm.value='';            

    window.alert('견적을 수행할 관련 정보가 없습니다.\\n\\n다른 항목으로 견적을 해주시기 바랍니다.');
    </script>");
    exit;
}

mysqli_close($db);
?>

<script>
    parent.document.forms["choiceForm"].Price.value = "<?= number_format($Price) ?>";                               
    parent.document.forms["choiceForm"].DS_Price.value = "<?= number_format($DesignMoneyOk) ?>";       
    parent.document.forms["choiceForm"].Order_Price.value = "<?= number_format($Order_PricOk) ?>";    

    parent.document.forms["choiceForm"].PriceForm.value = <?= $Price ?>;                               
    parent.document.forms["choiceForm"].DS_PriceForm.value = <?= $DesignMoneyOk ?>;          
    parent.document.forms["choiceForm"].Order_PriceForm.value = <?= $Order_PricOk ?>;       
    parent.document.forms["choiceForm"].VAT_PriceForm.value = <?= $VAT_PriceOk ?>;            
    parent.document.forms["choiceForm"].Total_PriceForm.value = <?= $Total_PriceOk ?>; 

    parent.document.forms["choiceForm"].StyleForm.value = "<?= $MY_type ?>";                               
    parent.document.forms["choiceForm"].SectionForm.value = "<?= $PN_type ?>"; 
	parent.document.forms["choiceForm"].TreeSelectForm.value = "<?= $MY_Fsd ?>";         
    parent.document.forms["choiceForm"].QuantityForm.value = "<?= $MY_amount ?>";       
    parent.document.forms["choiceForm"].DesignForm.value = "<?= $ordertype ?>";  
    parent.document.forms["choiceForm"].MY_amountRight.value = "<?= $ViewquantityTwo ?>";   // 추가 수량 정보
</script>
