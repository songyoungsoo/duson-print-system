<?
// 디비에서 합당한 가격을 검색하여 보내준다..
include"inc.php";
include"../../db.php";
$TABLE="MlangPrintAuto_cadarok";
include"../ConDb.php";

// MY_type         명함종류  style
// PN_type         인쇄면    Section
// MY_Fsd         용지   TreeSelect
// MY_amount    수량   quantity 
// ordertype       주문인 주문형태 

   $result= mysqli_query("select * from $TABLE where style='$MY_type' and Section='$MY_Fsd' and quantity='$MY_amount' and TreeSelect='$PN_type'",$db);
   $row= mysqli_fetch_array($result);
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
                         mysql_close($db); 
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