<?php
// 에러 표시 활성화 (디버깅용)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 디비에서 합당한 가격을 검색하여 보내준다..
include "inc.php";
include "../../db_auto.php";  // 자동 환경 감지 데이터베이스 연결

// 디버깅: 연결 상태 확인
if (!$db) {
    echo "<script>console.log('DB 연결 실패: " . mysqli_connect_error() . "');</script>";
    exit;
}
$TABLE="MlangPrintAuto_LittlePrint";
include "../ConDb.php";

// MY_type         명함종류  style
// PN_type         인쇄면    Section
// MY_Fsd         용지   TreeSelect
// MY_amount    수량   quantity 
// ordertype       주문인 주문형태 
// GET 방식으로 데이터 가져오기 (안전하게)
$MY_type = $_GET['MY_type'] ?? '';
$PN_type = $_GET['PN_type'] ?? '';
$MY_Fsd = $_GET['MY_Fsd'] ?? '';
$MY_amount = $_GET['MY_amount'] ?? '';
$POtype = $_GET['POtype'] ?? '';
$ordertype = $_GET['ordertype'] ?? '';

// 디버깅: 받은 파라미터 확인
echo "<script>console.log('LittlePrint 받은 파라미터: MY_type=$MY_type, PN_type=$PN_type, MY_Fsd=$MY_Fsd, MY_amount=$MY_amount, POtype=$POtype, ordertype=$ordertype');</script>";

// 안전한 prepared statement 사용
$query = "SELECT * FROM $TABLE WHERE style=? AND Section=? AND quantity=? AND TreeSelect=? AND POtype=?";
echo "<script>console.log('실행할 쿼리: $query');</script>";
echo "<script>console.log('파라미터: style=$MY_type, Section=$PN_type, quantity=$MY_amount, TreeSelect=$MY_Fsd, POtype=$POtype');</script>";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, 'sssss', $MY_type, $PN_type, $MY_amount, $MY_Fsd, $POtype);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

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


		parent.document.forms["posterForm"].Price.value="";                               
		parent.document.forms["posterForm"].DS_Price.value="";       
		parent.document.forms["posterForm"].Order_Price.value="";    

		parent.document.forms["posterForm"].PriceForm.value = "";                                
		parent.document.forms["posterForm"].DS_PriceForm.value = "";           
		parent.document.forms["posterForm"].Order_PriceForm.value = "";       
		parent.document.forms["posterForm"].VAT_PriceForm.value = "";            
		parent.document.forms["posterForm"].Total_PriceForm.value = "";  

		parent.document.forms["posterForm"].StyleForm.value="";                               
		parent.document.forms["posterForm"].SectionForm.value="";          
		parent.document.forms["posterForm"].QuantityForm.value="";       
		parent.document.forms["posterForm"].DesignForm.value="";            


<?

                                  echo("window.alert('견적을 수행할 관련 정보가 없습니다.\\n\\n다른 항목으로 견적을 해주시기 바랍니다.');
                                   </script>
								   ");
                                     exit;
                  }
                         mysqli_close($db); 
?>

	<script>
		console.log('포스터 계산 결과 업데이트 시작');
		
		parent.document.forms["posterForm"].Price.value="<?php echo number_format($Price); ?>";                               
		parent.document.forms["posterForm"].DS_Price.value="<?php echo number_format($DesignMoneyOk); ?>";       
		parent.document.forms["posterForm"].Order_Price.value="<?php echo number_format($Order_PricOk); ?>";    
		
		parent.document.forms["posterForm"].PriceForm.value = <?=$Price?>;                               
		parent.document.forms["posterForm"].DS_PriceForm.value = <?=$DesignMoneyOk?>;          
		parent.document.forms["posterForm"].Order_PriceForm.value = <?=$Order_PricOk?>;       
		parent.document.forms["posterForm"].VAT_PriceForm.value = <?=$VAT_PriceOk?>;            
		parent.document.forms["posterForm"].Total_PriceForm.value = <?=$Total_PriceOk?>; 

		parent.document.forms["posterForm"].StyleForm.value="포스터";                               
		parent.document.forms["posterForm"].SectionForm.value="<?=$PN_type?>";          
		parent.document.forms["posterForm"].QuantityForm.value="<?=$MY_amount?>매";       
		parent.document.forms["posterForm"].DesignForm.value="<?php echo ($ordertype == 'print') ? '인쇄만' : (($ordertype == 'design') ? '디자인만' : '디자인+인쇄'); ?>";  
		
		console.log('포스터 계산 결과 업데이트 완료');
		console.log('인쇄비: <?=$Price?>, 디자인비: <?=$DesignMoneyOk?>, 합계: <?=$Order_PricOk?>');
		
		//parent.document.forms["choiceForm"].MY_amountRight.value="<?=$ViewquantityTwo?>장";   // 전단지 옆 장수
	</script>