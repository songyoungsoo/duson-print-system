<?php
// 에러 표시 활성화 (디버깅용)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "inc.php";
include "../../db_auto.php";  // 자동 환경 감지 데이터베이스 연결
$TABLE = "MlangPrintAuto_MerchandiseBond";
include "../ConDb.php";

// 디버깅: 연결 상태 확인
if (!$db) {
    echo "<script>console.log('DB 연결 실패: " . mysqli_connect_error() . "');</script>";
    exit;
}

// Retrieve and sanitize input
// MY_type         명함종류  style
// PN_type         인쇄면    Section
// MY_Fsd         용지   TreeSelect
// MY_amount    수량   quantity 
// ordertype       주문인 주문형태 

// GET 방식으로 데이터 가져오기
$MY_type = $_GET['MY_type'] ?? '';
$PN_type = $_GET['PN_type'] ?? '';
$MY_Fsd = $_GET['MY_Fsd'] ?? '';
$MY_amount = $_GET['MY_amount'] ?? '';
$POtype = $_GET['POtype'] ?? '';
$ordertype = $_GET['ordertype'] ?? '';

// 디버깅: 받은 파라미터 확인
echo "<script>console.log('받은 파라미터: MY_type=$MY_type, PN_type=$PN_type, MY_amount=$MY_amount, POtype=$POtype, ordertype=$ordertype');</script>";

// 기존 $db 연결 사용 (새로 생성하지 않음)
// Query the database
$query = "SELECT * FROM $TABLE WHERE style='$MY_type' AND Section='$PN_type' AND quantity='$MY_amount' AND POtype='$POtype'";
echo "<script>console.log('실행할 쿼리: $query');</script>";

$result = mysqli_query($db, $query);

// 디버깅: 쿼리 결과 확인
if (!$result) {
    echo "<script>console.log('쿼리 실행 실패: " . mysqli_error($db) . "');</script>";
} else {
    $num_rows = mysqli_num_rows($result);
    echo "<script>console.log('쿼리 결과 행 수: $num_rows');</script>";
}
$row = mysqli_fetch_array($result);

if($row){
            									
    if($ordertype=="print"){
              $Price="$row[money]";  // 인쇄비  
              $DesignMoneyOk="0";  // 디자인편집비
              $Order_PricOk=$Price+$DesignMoneyOk; // 합계
              $VAT_PriceOk=$Order_PricOk/10;  // 부가세 10%
              $Total_PriceOk=$Order_PricOk+$VAT_PriceOk;  // 토탈
              $ViewquantityTwo="$row[quantityTwo]";  // 전단지 연수 옆에 장수

    }else if($ordertype=="design"){
                  $Price="0";  // 인쇄비  
                  $DesignMoneyOk="$row[DesignMoney]";  // 디자인편집비
                  $Order_PricOk=$Price+$DesignMoneyOk; // 합계
                  $VAT_PriceOk=$Order_PricOk/10;  // 부가세 10%
                  $Total_PriceOk=$Order_PricOk+$VAT_PriceOk;  // 토탈
                  $ViewquantityTwo="$row[quantityTwo]";  // 전단지 연수 옆에 장수

          }else{
                $Price="$row[money]";  // 인쇄비  
                $DesignMoneyOk="$row[DesignMoney]";
                $Order_PricOk=$Price+$DesignMoneyOk; // 합계
                $VAT_PriceOk=$Order_PricOk/10;  // 부가세 10%
                $Total_PriceOk=$Order_PricOk+$VAT_PriceOk;  // 토탈
                $ViewquantityTwo="$row[quantityTwo]";  // 전단지 연수 옆에 장수
           }

}else{
        echo ("<script language=javascript>");
?>


parent.document.forms["choiceForm"].Price.value="";                               
parent.document.forms["choiceForm"].DS_Price.value="";       
parent.document.forms["choiceForm"].Order_Price.value="";    

//parent.document.forms["choiceForm"].VAT_Price.value="";     
//parent.document.forms["choiceForm"].Total_Price.value=""; 

parent.document.forms["choiceForm"].PriceForm.value = "";                                
parent.document.forms["choiceForm"].DS_PriceForm.value = "";           
parent.document.forms["choiceForm"].Order_PriceForm.value = "";       
parent.document.forms["choiceForm"].VAT_PriceForm.value = "";            
parent.document.forms["choiceForm"].Total_PriceForm.value = "";  

parent.document.forms["choiceForm"].StyleForm.value="";                               
parent.document.forms["choiceForm"].SectionForm.value="";          
parent.document.forms["choiceForm"].QuantityForm.value="";       
parent.document.forms["choiceForm"].DesignForm.value="";            


<?

                 echo("window.alert('견적을 수행할 관련 정보가 없습니다.\\n\\n다른 항목으로 견적을 해주시기 바랍니다.');
                  </script>
                  ");
                    exit;
 }
        mysqli_close($db); 
?>

<script>
parent.document.forms["choiceForm"].Price.value="<?$TPrice = "$Price"; $TPrice = number_format($TPrice);  echo("$TPrice"); $TPrice = str_replace(",","",$TPrice);?>";                               
parent.document.forms["choiceForm"].DS_Price.value="<?$TDesignMoneyOk = "$DesignMoneyOk"; $TDesignMoneyOk = number_format($TDesignMoneyOk);  echo("$TDesignMoneyOk"); $TDesignMoneyOk = str_replace(",","",$TDesignMoneyOk);?>";       
parent.document.forms["choiceForm"].Order_Price.value="<?$TOrder_PricOk = "$Order_PricOk"; $TOrder_PricOk = number_format($TOrder_PricOk);  echo("$TOrder_PricOk"); $TOrder_PricOk = str_replace(",","",$TOrder_PricOk);?>";    

//parent.document.forms["choiceForm"].VAT_Price.value="<?$TVAT_PriceOk = "$VAT_PriceOk"; $TVAT_PriceOk = number_format($TVAT_PriceOk);  echo("$TVAT_PriceOk"); $TVAT_PriceOk = str_replace(",","",$TVAT_PriceOk);?>";     
//parent.document.forms["choiceForm"].Total_Price.value="<?$TTotal_PriceOk = "$Total_PriceOk"; $TTotal_PriceOk = number_format($TTotal_PriceOk);  echo("$TTotal_PriceOk"); $TTotal_PriceOk = str_replace(",","",$TTotal_PriceOk);?>"; 

parent.document.forms["choiceForm"].PriceForm.value = <?=$Price?>;                               
parent.document.forms["choiceForm"].DS_PriceForm.value = <?=$DesignMoneyOk?>;          
parent.document.forms["choiceForm"].Order_PriceForm.value = <?=$Order_PricOk?>;       
parent.document.forms["choiceForm"].VAT_PriceForm.value = <?=$VAT_PriceOk?>;            
parent.document.forms["choiceForm"].Total_PriceForm.value = <?=$Total_PriceOk?>; 

parent.document.forms["choiceForm"].StyleForm.value="<?=$MY_type?>";                               
parent.document.forms["choiceForm"].SectionForm.value="<?=$PN_type?>";          
parent.document.forms["choiceForm"].QuantityForm.value="<?=$MY_amount?>";       
parent.document.forms["choiceForm"].DesignForm.value="<?=$ordertype?>";  

//parent.document.forms["choiceForm"].MY_amountRight.value="<?=$ViewquantityTwo?>장";   // 전단지 옆 장수
</script>

<?php
// 파일 끝
?>
