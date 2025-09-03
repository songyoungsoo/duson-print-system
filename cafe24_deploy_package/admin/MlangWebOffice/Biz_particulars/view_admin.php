<?php
$ViewBiz_particulars_admin= mysql_query("select * from MlangWebOffice_Biz_particulars_admin where no='$no'",$db);
$RowBiz_particulars_admin= mysql_fetch_array($ViewBiz_particulars_admin);
  $Viewid="$RowBiz_particulars_admin[id]";
  $Viewbiz_name="$RowBiz_particulars_admin[biz_name]";
  $Viewa_name="$RowBiz_particulars_admin[a_name]";
  $Viewb_name="$RowBiz_particulars_admin[b_name]";
  $Viewtel_1="$RowBiz_particulars_admin[tel_1]";
  $Viewtel_2="$RowBiz_particulars_admin[tel_2]";
  $Viewtel_3="$RowBiz_particulars_admin[tel_3]"; 
?>