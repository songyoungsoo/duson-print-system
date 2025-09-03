<?php
// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "inc.php";
include "../../db.php";
$TABLE = "MlangPrintAuto_cadarokTwo";
include "../ConDb.php";

// GET 방식으로 데이터 가져오기
$MY_type = $_GET['MY_type'] ?? '';
$PN_type = $_GET['PN_type'] ?? '';
$MY_Fsd = $_GET['MY_Fsd'] ?? '';
$MY_amount = $_GET['MY_amount'] ?? '';
$ordertype = $_GET['ordertype'] ?? '';

// 예: 값을 출력하여 확인
echo "MY_type: " . htmlspecialchars($MY_type, ENT_QUOTES, 'UTF-8') . "<br>";
echo "PN_type: " . htmlspecialchars($PN_type, ENT_QUOTES, 'UTF-8') . "<br>";
echo "MY_Fsd: " . htmlspecialchars($MY_Fsd, ENT_QUOTES, 'UTF-8') . "<br>";
echo "MY_amount: " . htmlspecialchars($MY_amount, ENT_QUOTES, 'UTF-8') . "<br>";
echo "ordertype: " . htmlspecialchars($ordertype, ENT_QUOTES, 'UTF-8') . "<br>";

// Ensure the global $db variable is available and used for database operations
global $db;

if (!$db) {
    die("Database connection error: " . mysqli_connect_error());
}

// 합당한 가격을 검색하여 보내기
$query = "SELECT * FROM $TABLE WHERE style=? AND Section=? AND quantity=? AND TreeSelect=?";
$stmt = $db->prepare($query);
$stmt->bind_param('ssss', $MY_type, $MY_Fsd, $MY_amount, $PN_type);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    if ($ordertype == "print") {
        $Price = $row['money'];  // 인쇄비
        $DesignMoneyOk = 0;  // 디자인편집비
        $Order_PricOk = $Price + $DesignMoneyOk; // 합계
        $VAT_PriceOk = $Order_PricOk / 10;  // 부가세 10%
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;  // 총액
        $ViewquantityTwo = $row['quantityTwo'];  // 전단지 연수 옆에 장수
    } elseif ($ordertype == "design") {
        $Price = 0;  // 인쇄비
        $DesignMoneyOk = $row['DesignMoney'];  // 디자인편집비
        $Order_PricOk = $Price + $DesignMoneyOk; // 합계
        $VAT_PriceOk = $Order_PricOk / 10;  // 부가세 10%
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;  // 총액
        $ViewquantityTwo = $row['quantityTwo'];  // 전단지 연수 옆에 장수
    } else {
        $Price = $row['money'];  // 인쇄비
        $DesignMoneyOk = $row['DesignMoney'];
        $Order_PricOk = $Price + $DesignMoneyOk; // 합계
        $VAT_PriceOk = $Order_PricOk / 10;  // 부가세 10%
        $Total_PriceOk = $Order_PricOk + $VAT_PriceOk;  // 총액
        $ViewquantityTwo = $row['quantityTwo'];  // 전단지 연수 옆에 장수
    }
} else {
    echo ("<script language=javascript>");
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
    $stmt->close();
    $db->close();
    exit;
}

$stmt->close();
$db->close();
?>

<script>
parent.document.forms["choiceForm"].Price.value = "<?php $TPrice = number_format($Price); echo($TPrice); ?>";                               
parent.document.forms["choiceForm"].DS_Price.value = "<?php $TDesignMoneyOk = number_format($DesignMoneyOk); echo($TDesignMoneyOk); ?>";       
parent.document.forms["choiceForm"].Order_Price.value = "<?php $TOrder_PricOk = number_format($Order_PricOk); echo($TOrder_PricOk); ?>";    

parent.document.forms["choiceForm"].PriceForm.value = <?php echo $Price; ?>;                               
parent.document.forms["choiceForm"].DS_PriceForm.value = <?php echo $DesignMoneyOk; ?>;          
parent.document.forms["choiceForm"].Order_PriceForm.value = <?php echo $Order_PricOk; ?>;       
parent.document.forms["choiceForm"].VAT_PriceForm.value = <?php echo $VAT_PriceOk; ?>;            
parent.document.forms["choiceForm"].Total_PriceForm.value = <?php echo $Total_PriceOk; ?>; 

parent.document.forms["choiceForm"].StyleForm.value = "<?php echo $MY_type; ?>";                               
parent.document.forms["choiceForm"].SectionForm.value = "<?php echo $PN_type; ?>";          
parent.document.forms["choiceForm"].QuantityForm.value = "<?php echo $MY_amount; ?>";       
parent.document.forms["choiceForm"].DesignForm.value = "<?php echo $ordertype; ?>";  
</script>
