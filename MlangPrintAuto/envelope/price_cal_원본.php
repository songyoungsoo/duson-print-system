<?
// ��񿡼� �մ��� ������ �˻��Ͽ� �����ش�..
include"inc.php";
include"../../db.php";
$TABLE="MlangPrintAuto_envelope";
include"../ConDb.php";

// MY_type         ��������  style
// PN_type         �μ��    Section
// MY_Fsd         ����   TreeSelect
// MY_amount    ����   quantity 
// ordertype       �ֹ��� �ֹ����� 

   $result= mysql_query("select * from $TABLE where style='$MY_type' and Section='$PN_type' and quantity='$MY_amount' and POtype='$POtype'",$db);
   $row= mysql_fetch_array($result);
     if($row){
            									
    
	                             $Price="$row[money]";  // �μ��  
                                 $DesignMoneyOk="$row[DesignMoney]";
							     $Order_PricOk=$Price+$DesignMoneyOk; // �հ�
                                 $VAT_PriceOk=$Order_PricOk/10;  // �ΰ��� 10%
							     $Total_PriceOk=$Order_PricOk+$VAT_PriceOk;  // ��Ż
								 $ViewquantityTwo="$row[quantityTwo]";  // ������ ���� ���� ���
					

         }else{
                         echo ("<script language=javascript>");
?>


		parent.document.forms["choiceForm"].Price.value="";                               
		parent.document.forms["choiceForm"].DS_Price.value="";       
		parent.document.forms["choiceForm"].Order_Price.value="";    

		//parent.document.forms["choiceForm"].VAT_Price.value="";     
		//parent.document.forms["choiceForm"].Total_Price.value=""; 
		
		parent.document.forms["choiceForm"].PriceForm.value = "";                                
		//parent.document.forms["choiceForm"].DS_PriceForm.value = "";           
		parent.document.forms["choiceForm"].Order_PriceForm.value = "";       
		parent.document.forms["choiceForm"].VAT_PriceForm.value = "";            
		parent.document.forms["choiceForm"].Total_PriceForm.value = "";  

		parent.document.forms["choiceForm"].StyleForm.value="";                               
		parent.document.forms["choiceForm"].SectionForm.value="";          
		parent.document.forms["choiceForm"].QuantityForm.value="";       
		parent.document.forms["choiceForm"].DesignForm.value="";            


<?

                                  echo("window.alert('������ ������ ���� ������ �����ϴ�.\\n\\n�ٸ� �׸����� ������ ���ֽñ� �ٶ��ϴ�.');
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
		//parent.document.forms["choiceForm"].DS_PriceForm.value = <?=$DesignMoneyOk?>;          
		parent.document.forms["choiceForm"].Order_PriceForm.value = <?=$Order_PricOk?>;       
		parent.document.forms["choiceForm"].VAT_PriceForm.value = <?=$VAT_PriceOk?>;            
		parent.document.forms["choiceForm"].Total_PriceForm.value = <?=$Total_PriceOk?>; 

		parent.document.forms["choiceForm"].StyleForm.value="<?=$MY_type?>";                               
		parent.document.forms["choiceForm"].SectionForm.value="<?=$PN_type?>";          
		parent.document.forms["choiceForm"].QuantityForm.value="<?=$MY_amount?>";       
		parent.document.forms["choiceForm"].DesignForm.value="<?=$ordertype?>";  
		
		//parent.document.forms["choiceForm"].MY_amountRight.value="<?=$ViewquantityTwo?>��";   // ������ �� ���
	</script>