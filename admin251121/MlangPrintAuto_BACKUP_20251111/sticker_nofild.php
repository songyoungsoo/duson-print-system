<?php
declare(strict_types=1);
   
// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

$MlangPrintAutoFild_result= mysqli_query($db, "select * from mlangprintauto_sticker where no='$no'");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

   $MlangPrintAutoFild_row= mysqli_fetch_array($MlangPrintAutoFild_result);
     if($MlangPrintAutoFild_row){

$MlangPrintAutoFildView_style = $MlangPrintAutoFild_row['style'] ?? '';
$MlangPrintAutoFildView_Section = $MlangPrintAutoFild_row['Section'] ?? '';
$MlangPrintAutoFildView_quantity = $MlangPrintAutoFild_row['quantity'] ?? '';
$MlangPrintAutoFildView_money = $MlangPrintAutoFild_row['money'] ?? '';
$MlangPrintAutoFildView_TreeSelect = $MlangPrintAutoFild_row['TreeSelect'] ?? '';
$MlangPrintAutoFildView_DesignMoney = $MlangPrintAutoFild_row['DesignMoney'] ?? '';
$MlangPrintAutoFildView_POtype = $MlangPrintAutoFild_row['POtype'] ?? '';
$MlangPrintAutoFildView_quantityTwo = $MlangPrintAutoFild_row['quantityTwo'] ?? '';

         }else{
                         echo ("<script language=javascript>
                                      window.alert('▒ ERROR - 등록번호: $no 번에 관련된 자료가 없거나 DB 에러일수 있습니다.');
                                      window.self.close();
                                   </script>");
                                     exit;
                  }
?>