<?php
// 간단한 테스트용 계산 시스템 (데이터베이스 없이)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// GET 파라미터 받기
$MY_type = $_GET['MY_type'] ?? '';
$PN_type = $_GET['PN_type'] ?? '';
$MY_amount = $_GET['MY_amount'] ?? '';
$POtype = $_GET['POtype'] ?? '';
$ordertype = $_GET['ordertype'] ?? '';

echo "<script>console.log('받은 파라미터: MY_type=$MY_type, PN_type=$PN_type, MY_amount=$MY_amount, POtype=$POtype, ordertype=$ordertype');</script>";

// 임시 가격 데이터 (실제 데이터베이스 대신)
$price_data = array(
    // 기본 가격표
    '500' => array('money' => 50000, 'DesignMoney' => 30000, 'quantityTwo' => '500매'),
    '1000' => array('money' => 80000, 'DesignMoney' => 30000, 'quantityTwo' => '1000매'),
    '1500' => array('money' => 110000, 'DesignMoney' => 30000, 'quantityTwo' => '1500매'),
    '2000' => array('money' => 140000, 'DesignMoney' => 30000, 'quantityTwo' => '2000매'),
);

// 수량에 따른 가격 찾기
if (isset($price_data[$MY_amount])) {
    $row = $price_data[$MY_amount];
    
    // 주문 타입에 따른 계산
    if ($ordertype == "print") {
        $Price = $row['money'];  // 인쇄비  
        $DesignMoneyOk = 0;  // 디자인편집비
    } else if ($ordertype == "design") {
        $Price = 0;  // 인쇄비  
        $DesignMoneyOk = $row['DesignMoney'];  // 디자인편집비
    } else {
        $Price = $row['money'];  // 인쇄비  
        $DesignMoneyOk = $row['DesignMoney'];  // 디자인편집비
    }
    
    $Order_PricOk = $Price + $DesignMoneyOk; // 합계
    $VAT_PriceOk = $Order_PricOk / 10;  // 부가세 10%
    $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;  // 토탈
    $ViewquantityTwo = $row['quantityTwo'];  // 수량 표시
    
    echo "<script>console.log('계산 완료: Price=$Price, DesignMoney=$DesignMoneyOk, Total=$Order_PricOk');</script>";
    
} else {
    // 데이터가 없을 경우
    echo "<script>";
    echo "console.log('해당 수량($MY_amount)에 대한 가격 데이터가 없습니다.');";
    echo "parent.document.forms['choiceForm'].Price.value='';";
    echo "parent.document.forms['choiceForm'].DS_Price.value='';";
    echo "parent.document.forms['choiceForm'].Order_Price.value='';";
    echo "alert('견적을 수행할 관련 정보가 없습니다.\\n\\n다른 항목으로 견적을 해주시기 바랍니다.');";
    echo "</script>";
    exit;
}
?>

<script>
// 계산 결과를 부모 창의 폼에 입력
parent.document.forms["choiceForm"].Price.value="<?php echo number_format($Price); ?>";
parent.document.forms["choiceForm"].DS_Price.value="<?php echo number_format($DesignMoneyOk); ?>";
parent.document.forms["choiceForm"].Order_Price.value="<?php echo number_format($Order_PricOk); ?>";

parent.document.forms["choiceForm"].PriceForm.value = <?php echo $Price; ?>;
parent.document.forms["choiceForm"].DS_PriceForm.value = <?php echo $DesignMoneyOk; ?>;
parent.document.forms["choiceForm"].Order_PriceForm.value = <?php echo $Order_PricOk; ?>;
parent.document.forms["choiceForm"].VAT_PriceForm.value = <?php echo $VAT_PriceOk; ?>;
parent.document.forms["choiceForm"].Total_PriceForm.value = <?php echo $Total_PriceOk; ?>;

parent.document.forms["choiceForm"].StyleForm.value="쿠폰/상품권";
parent.document.forms["choiceForm"].SectionForm.value="<?php echo $PN_type; ?>";
parent.document.forms["choiceForm"].QuantityForm.value="<?php echo $ViewquantityTwo; ?>";
parent.document.forms["choiceForm"].DesignForm.value="<?php echo ($ordertype == 'print') ? '인쇄만' : (($ordertype == 'design') ? '디자인만' : '디자인+인쇄'); ?>";

console.log('가격 계산 완료 및 폼 업데이트 완료');
</script>