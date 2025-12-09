<?php
$HomeDir="../";
$PageCode="PrintAuto";
include"$HomeDir/db.php";
include"$DOCUMENT_ROOT/mlangprintauto/mlangprintautotop.php";
?>

<?php
if($mode=="SubmitOk"){
include"../db.php";

$Table_result = mysql_query("SELECT max(no) FROM mlangorder_printauto");
	if (!$Table_result) {
		echo "
			<script>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysql_fetch_row($Table_result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	} 
	
// �ڷḦ ���ε��� ������ ���� �����ش�.. /////////////////////////////////////////////////////////////////////////////////
$dir = "upload/$new_no"; 
$dir_handle = is_dir("$dir");
if(!$dir_handle){mkdir("$dir", 0755);  exec("chmod 777 $dir");}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
 $zip = "$sample6_postcode"; 
 $zip1 = "$sample6_address";
 $zip2 = "$sample6_detailAddress"; 
 $address3 = "$sample6_extraAddress";
	
	
// ��� ���� �ڷ� ����
if($PageSS=="OrderOne"){$PageSSOk="2";}
if($PageSS=="OrderTwo"){$PageSSOk="1";}

$date=date("Y-m-d H:i:s");
$dbinsert ="insert into mlangorder_printauto values('$new_no',
'$Type', 
'$ImgFolder', 
'$Type_1
$Type_2
$Type_3
$Type_4
$Type_5
$Type_6',
'$money_1',
'$money_2',	
'$money_3',	
'$money_4',	
'$money_5',	
'$name',   
'$email',
'$zip', 
'$zip1',
'$zip2',
'$phone',   
'$Hendphone',
'$delivery', 
'$bizname',
'$bank',
'$bankname',
'$cont', 
'$date',
'$PageSSOk',
'',
'$phone',
'$Gensu',
''
)";

//echo $dbinsert; exit;
$result_insert= mysql_query($dbinsert,$db);

if($result){
echo ("<html>
<meta http-equiv='Refresh' content='0; URL=OrderResult.php?OrderSytle=$OrderSytle&name=$OrderName&money1=$money_1&&money2=$money_2&money3=$money_3&money4=$money_4&&money5=$money_5&standard=$standard&page=$page&PageSS=$PageSS'>
</html>		");
		exit;
}else{
echo ("<html>
<meta http-equiv='Refresh' content='0; URL=OrderResult.php?OrderSytle=$OrderSytle&name=$OrderName&money1=$money_1&&money2=$money_2&money3=$money_3&money4=$money_4&&money5=$money_5&standard=$standard&page=$page&PageSS=$PageSS'>
</html>		");
		exit;
}

} ///////////////////////////////////////////////////////////////////////////////////////////////////////////

include"../db.php";
?>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<!---------------- ��ü�� ���Ѵ�. ------------------------>
<table border=0 align=center width=100% cellpadding=0 cellspacing=0>
<tr>
<td align=center> 
<?include"OrderForm${SubmitMode}.php";?>
</td>
       </tr>
     </table>

</body>
</html>

<?php
include"$DOCUMENT_ROOT/mlangprintauto/MlangPrintAutoDown.php";
?>
