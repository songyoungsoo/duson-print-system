<?php
// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "db_ajax.php";

$TABLE = "mlangprintauto_namecard";
$GGTABLE = "mlangprintauto_transactioncate";

// 명함 옵션 매핑
// NC_type         명함 종류    style
// NC_paper        용지 종류    Section  
// NC_amount       수량        quantity 
// ordertype       주문 형태

// GET 방식으로 데이터 가져오기
$NC_type = $_GET['NC_type'] ?? '';      // 명함 종류 (일반명함, 고급수입지, 카드명함)
$NC_paper = $_GET['NC_paper'] ?? '';    // 용지 종류 (칼라코팅, 칼라비코팅 등)
$NC_amount = $_GET['NC_amount'] ?? '';  // 수량
$POtype = $_GET['POtype'] ?? '1';       // 기본값: 단면
$ordertype = $_GET['ordertype'] ?? 'total';

// 수량 매핑 (명함은 보통 500부, 1000부 단위)
$quantity_map = [
    '500' => '500',
    '1000' => '1000', 
    '2000' => '2000',
    '3000' => '3000',
    '4000' => '4000',
    '5000' => '5000',
    '기타' => '1000' // 기본값
];

$mapped_quantity = $quantity_map[$NC_amount] ?? $NC_amount;

// 디버깅 정보
error_log("NameCard price calculation - NC_type: $NC_type, NC_paper: $NC_paper, NC_amount: $NC_amount, mapped_quantity: $mapped_quantity, POtype: $POtype");

// 명함 가격 검색 (명함은 TreeSelect 필드가 없음)
$query = "SELECT * FROM $TABLE WHERE style='$NC_type' AND Section='$NC_paper' AND quantity='$mapped_quantity' AND POtype='$POtype'";
error_log("SQL Query: $query");

$result = mysqli_query($db, $query);
$row = mysqli_fetch_array($result);

if ($row) {
    if ($ordertype == "print") {
        $Price = $row['money'];  // 인쇄비
        $DesignMoneyOk = 0;  // 디자인편집비
        $Order_PricOk = $Price + $DesignMoneyOk; // 합계
        $VAT_PriceOk = $Order_PricOk / 10;  // 부가세 10%
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;  // 토탈
    } elseif ($ordertype == "design") {
        $Price = 0;  // 인쇄비
        $DesignMoneyOk = $row['DesignMoney'] ?? 30000;  // 디자인편집비
        $Order_PricOk = $Price + $DesignMoneyOk; // 합계
        $VAT_PriceOk = $Order_PricOk / 10;  // 부가세 10%
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;  // 토탈
    } else {
        $Price = $row['money'];  // 인쇄비
        $DesignMoneyOk = $row['DesignMoney'] ?? 30000; // 디자인편집비
        $Order_PricOk = $Price + $DesignMoneyOk; // 합계
        $VAT_PriceOk = $Order_PricOk / 10;  // 부가세 10%
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;  // 토탈
    }
} else {
    // 디버깅을 위한 로그
    error_log("No namecard price data found for: style=$NC_type, Section=$NC_paper, quantity=$mapped_quantity, POtype=$POtype");
    
    echo "<script language='javascript'>";
    echo "parent.document.forms['namecardForm'].NC_Price.value='';";                               
    echo "parent.document.forms['namecardForm'].NC_DS_Price.value='';";       
    echo "parent.document.forms['namecardForm'].NC_Order_Price.value='';";
    echo "parent.document.forms['namecardForm'].PriceForm.value='';";                                
    echo "parent.document.forms['namecardForm'].DS_PriceForm.value='';";           
    echo "parent.document.forms['namecardForm'].Order_PriceForm.value='';";       
    echo "parent.document.forms['namecardForm'].VAT_PriceForm.value='';";            
    echo "parent.document.forms['namecardForm'].Total_PriceForm.value='';";           
    echo "parent.document.forms['namecardForm'].StyleForm.value='';";           
    echo "parent.document.forms['namecardForm'].SectionForm.value='';";           
    echo "parent.document.forms['namecardForm'].QuantityForm.value='';";           
    echo "parent.document.forms['namecardForm'].DesignForm.value='';";;  
    echo "console.log('명함 가격 정보 없음 - 종류: $NC_type, 용지: $NC_paper, 수량: $mapped_quantity');";
    echo "window.alert('해당 명함 조건의 가격 정보가 없습니다.\\n\\n다른 옵션으로 선택해주시기 바랍니다.');";
    echo "</script>";
    exit;
}

mysqli_close($db);
?>

<script>
    console.log("명함 가격 계산 성공 - 인쇄비: <?php echo number_format($Price); ?>원, 편집비: <?php echo number_format($DesignMoneyOk); ?>원");
    
    parent.document.forms["namecardForm"].NC_Price.value = "<?php echo number_format($Price); ?>";                               
    parent.document.forms["namecardForm"].NC_DS_Price.value = "<?php echo number_format($DesignMoneyOk); ?>";       
    parent.document.forms["namecardForm"].NC_Order_Price.value = "<?php echo number_format($Order_PricOk); ?>";    
    parent.document.forms["namecardForm"].PriceForm.value = <?php echo $Price; ?>;                               
    parent.document.forms["namecardForm"].DS_PriceForm.value = <?php echo $DesignMoneyOk; ?>;          
    parent.document.forms["namecardForm"].Order_PriceForm.value = <?php echo $Order_PricOk; ?>;       
    parent.document.forms["namecardForm"].VAT_PriceForm.value = <?php echo $VAT_PriceOk; ?>;            
    parent.document.forms["namecardForm"].Total_PriceForm.value = <?php echo $Total_PriceOk; ?>; 
    parent.document.forms["namecardForm"].StyleForm.value = "<?php 
        $type_query = "SELECT title FROM $GGTABLE WHERE no='$NC_type'";
        $type_result = mysqli_query($db, $type_query);
        $type_row = mysqli_fetch_array($type_result);
        echo $type_row['title'] ?? $NC_type;
    ?>";
    parent.document.forms["namecardForm"].SectionForm.value = "<?php 
        $paper_query = "SELECT title FROM $GGTABLE WHERE no='$NC_paper'";
        $paper_result = mysqli_query($db, $paper_query);
        $paper_row = mysqli_fetch_array($paper_result);
        echo $paper_row['title'] ?? $NC_paper;
    ?>";
    parent.document.forms["namecardForm"].QuantityForm.value = "<?php echo $NC_amount; ?>매";
    parent.document.forms["namecardForm"].DesignForm.value = "<?php 
        $design_text = '';
        if ($ordertype == 'total') $design_text = '디자인+인쇄';
        else if ($ordertype == 'print') $design_text = '인쇄만 의뢰';
        else if ($ordertype == 'design') $design_text = '디자인만 의뢰';
        else $design_text = $ordertype;
        echo $design_text;
    ?>";
    parent.document.forms["namecardForm"].SidesForm.value = "<?php echo ($POtype == '2') ? '양면' : '단면'; ?>";  
</script>